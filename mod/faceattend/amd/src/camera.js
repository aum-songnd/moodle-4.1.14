// amd/src/camera.js
// JavaScript module điều khiển camera và gửi ảnh lên server
//
// Moodle dùng AMD (Asynchronous Module Definition) cho JavaScript.
// File này được build bởi Moodle's grunt toolchain,
// nhưng trong dev có thể dùng trực tiếp với ?jsrev=-1 trong URL.

define(['core/ajax', 'core/notification'], function(Ajax, Notification) {

    // ── State của module ──
    let videoStream  = null;  // MediaStream từ camera
    let attemptsLeft = 3;     // Số lần thử còn lại
    let config       = {};    // Cấu hình từ PHP

    /**
     * init() — Hàm khởi tạo, được gọi từ view.php qua:
     *   $PAGE->requires->js_call_amd('mod_faceattend/camera', 'init', [...])
     *
     * @param {Object} cfg - Cấu hình từ PHP
     */
    function init(cfg) {
        config       = cfg;
        attemptsLeft = cfg.maxAttempts || 3;

        // Lấy các element DOM
        const btnStart   = document.getElementById('btn-start-camera');
        const btnCapture = document.getElementById('btn-capture');
        const container  = document.getElementById('faceattend-container');

        if (!container) {
            return; // Không phải trang sinh viên (giảng viên không có camera UI)
        }

        // ── Sự kiện: bật camera ──
        btnStart.addEventListener('click', startCamera);

        // ── Sự kiện: chụp ảnh & gửi lên server ──
        btnCapture.addEventListener('click', captureAndAttend);

        updateStatus('Nhấn "Bật Camera" để bắt đầu điểm danh', 'secondary');
        updateAttemptsDisplay();
    }

    // ─────────────────────────────────────────────────────
    // BẬT CAMERA
    // ─────────────────────────────────────────────────────
    async function startCamera() {
        const video    = document.getElementById('face-video');
        const btnStart = document.getElementById('btn-start-camera');
        const btnCapture = document.getElementById('btn-capture');

        try {
            updateStatus('Đang bật camera...', 'secondary');

            // Yêu cầu quyền truy cập camera
            // constraints: ưu tiên camera trước (facingMode: 'user')
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width:      { ideal: 640 },
                    height:     { ideal: 480 },
                    facingMode: 'user',   // Camera trước trên mobile
                },
                audio: false
            });

            video.srcObject = videoStream;
            await video.play();

            // Cập nhật UI sau khi camera bật thành công
            btnStart.disabled   = true;
            btnCapture.disabled = false;
            btnStart.textContent = '📷 Camera đang hoạt động';

            updateStatus('Camera đã bật! Nhìn thẳng vào camera rồi nhấn "Chụp & Điểm danh"', 'info');

        } catch (err) {
            // Xử lý các loại lỗi camera phổ biến
            let msg = 'Không thể bật camera.';
            if (err.name === 'NotAllowedError') {
                msg = '⛔ Bạn đã từ chối quyền camera. Hãy cấp quyền trong cài đặt trình duyệt rồi thử lại.';
            } else if (err.name === 'NotFoundError') {
                msg = '📷 Không tìm thấy camera trên thiết bị này.';
            } else if (err.name === 'NotReadableError') {
                msg = '📷 Camera đang được ứng dụng khác sử dụng.';
            }
            updateStatus(msg, 'danger');
            console.error('[FaceAttend] Camera error:', err);
        }
    }

    // ─────────────────────────────────────────────────────
    // CHỤP ẢNH & GỬI LÊN SERVER
    // ─────────────────────────────────────────────────────
    async function captureAndAttend() {
        if (!videoStream) {
            updateStatus('Hãy bật camera trước!', 'warning');
            return;
        }

        if (attemptsLeft <= 0) {
            updateStatus('❌ Đã hết số lần thử. Liên hệ giảng viên.', 'danger');
            disableAllButtons();
            return;
        }

        const video  = document.getElementById('face-video');
        const canvas = document.getElementById('face-canvas');
        const btnCapture = document.getElementById('btn-capture');

        // ── Chụp frame hiện tại từ video vào canvas ──
        canvas.width  = video.videoWidth  || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Chuyển canvas thành base64 JPEG (nén 90% để giảm dung lượng)
        const imageBase64 = canvas.toDataURL('image/jpeg', 0.9);

        // ── Gửi lên PHP qua Moodle AJAX ──
        btnCapture.disabled    = true;
        btnCapture.textContent = '⏳ Đang xử lý...';
        attemptsLeft--;
        updateAttemptsDisplay();
        updateStatus('🔍 Đang nhận diện khuôn mặt...', 'info');

        try {
            // Moodle Ajax gọi Web Service function 'mod_faceattend_submit_attendance'
            // được định nghĩa trong classes/external.php
            const result = await Ajax.call([{
                methodname: 'mod_faceattend_submit_attendance',
                args: {
                    cmid:        config.cmid,
                    image_data:  imageBase64,
                    face_token:  getFaceToken(),
                }
            }])[0];

            handleAttendanceResult(result);

        } catch (err) {
            console.error('[FaceAttend] Ajax error:', err);
            updateStatus('⚠️ Lỗi kết nối server. Vui lòng thử lại.', 'danger');

            if (attemptsLeft > 0) {
                setTimeout(() => {
                    btnCapture.disabled    = false;
                    btnCapture.textContent = 'Chụp & Điểm danh';
                }, 2000);
            }
        }
    }

    // ─────────────────────────────────────────────────────
    // XỬ LÝ KẾT QUẢ TỪ SERVER
    // ─────────────────────────────────────────────────────
    function handleAttendanceResult(result) {
        const btnCapture = document.getElementById('btn-capture');

        if (result.success) {
            // ✅ ĐIỂM DANH THÀNH CÔNG
            updateStatus(
                `✅ Điểm danh thành công!<br>
                 🎯 Độ chính xác: <strong>${result.confidence}%</strong><br>
                 🕐 Thời gian: <strong>${result.attend_time}</strong>`,
                'success'
            );
            disableAllButtons();
            stopCamera();

            // Thêm confetti nhỏ cho vui 🎉
            triggerSuccessAnimation();

        } else {
            // ❌ NHẬN DIỆN THẤT BẠI
            let msg = result.message || 'Không nhận diện được khuôn mặt.';

            if (attemptsLeft > 0) {
                msg += `<br><small>Còn ${attemptsLeft} lần thử. Hãy đảm bảo đủ ánh sáng và nhìn thẳng vào camera.</small>`;
                updateStatus(msg, 'warning');

                setTimeout(() => {
                    btnCapture.disabled    = false;
                    btnCapture.textContent = 'Chụp & Điểm danh';
                }, 2000);
            } else {
                msg += '<br><strong>Đã hết số lần thử. Vui lòng liên hệ giảng viên.</strong>';
                updateStatus(msg, 'danger');
                disableAllButtons();
            }
        }
    }

    // ─────────────────────────────────────────────────────
    // HELPER FUNCTIONS
    // ─────────────────────────────────────────────────────

    function updateStatus(message, type) {
        // type: 'success' | 'danger' | 'warning' | 'info' | 'secondary'
        const box    = document.getElementById('face-status-box');
        const status = document.getElementById('face-status');
        if (box) {
            box.className = `alert alert-${type} text-center`;
            box.innerHTML = message;
        }
        if (status) {
            status.innerHTML = '';
        }
    }

    function updateAttemptsDisplay() {
        const el = document.getElementById('attempts-left');
        if (el) {
            el.textContent = attemptsLeft;
            if (attemptsLeft === 1) el.style.color = 'red';
            if (attemptsLeft === 0) el.textContent = '0 (đã hết)';
        }
    }

    function disableAllButtons() {
        ['btn-start-camera', 'btn-capture'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.disabled = true;
        });
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        const video = document.getElementById('face-video');
        if (video) video.srcObject = null;
    }

    function getFaceToken() {
        const container = document.getElementById('faceattend-container');
        return container ? container.dataset.faceToken : '';
    }

    function triggerSuccessAnimation() {
        // Đổi border video thành xanh lá khi thành công
        const video = document.getElementById('face-video');
        if (video) {
            video.style.border = '4px solid #198754';
        }
    }

    // Export public API của module
    return { init: init };
});
