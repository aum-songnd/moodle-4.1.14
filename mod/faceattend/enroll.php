<?php
/**
 * enroll.php — Trang đăng ký khuôn mặt trên Moodle web
 *
 * Người dùng vào trang này, chụp ảnh qua camera trình duyệt,
 * ảnh được gửi lên Face API → lưu face_token vào DB.
 *
 * URL: /mod/faceattend/enroll.php
 * URL admin enroll cho user khác: /mod/faceattend/enroll.php?userid=5
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/faceattend/lib.php');

// ── Tham số ──────────────────────────────────────────────
// Admin có thể enroll cho user khác qua ?userid=X
$target_userid = optional_param('userid', $USER->id, PARAM_INT);

// Xác thực đăng nhập
require_login();
$context = context_system::instance();

// Kiểm tra quyền: chỉ admin enroll người khác, user thường chỉ enroll bản thân
if ($target_userid != $USER->id) {
    require_capability('moodle/user:update', $context);
}

$target_user = $DB->get_record('user', ['id' => $target_userid], '*', MUST_EXIST);

// ── Lấy cấu hình API URL từ activity gần nhất hoặc dùng default ──
$api_url = get_config('mod_faceattend', 'api_url') ?: 'http://localhost:8002';

// ── Setup page ───────────────────────────────────────────
$PAGE->set_url('/mod/faceattend/enroll.php', ['userid' => $target_userid]);
$PAGE->set_context($context);
$PAGE->set_title('Đăng ký khuôn mặt — ' . fullname($target_user));
$PAGE->set_heading('Đăng ký khuôn mặt');
$PAGE->set_pagelayout('standard');

// Kiểm tra đã đăng ký chưa
$existing = $DB->get_record('faceattend_enrollment', [
    'userid'    => $target_userid,
    'is_active' => 1,
]);

echo $OUTPUT->header();
?>

<style>
/* ── Layout ── */
.enroll-wrapper {
    max-width: 680px;
    margin: 0 auto;
    padding: 20px;
}
.enroll-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.10);
    overflow: hidden;
}
.enroll-header {
    background: linear-gradient(135deg, #1F4E79, #2E75B6);
    color: white;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.enroll-header .icon { font-size: 2.5rem; }
.enroll-header h2 { margin: 0; font-size: 1.4rem; }
.enroll-header p  { margin: 4px 0 0; opacity: 0.85; font-size: 0.9rem; }
.enroll-body { padding: 28px; }

/* ── Camera ── */
.camera-container {
    position: relative;
    background: #0a0a0a;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 4/3;
    max-height: 360px;
    display: flex;
    align-items: center;
    justify-content: center;
}
#enroll-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1); /* Mirror effect */
    display: none;
}
#camera-placeholder {
    color: #666;
    text-align: center;
    padding: 40px;
}
#camera-placeholder .icon { font-size: 3rem; display: block; margin-bottom: 8px; }

/* Overlay guide khi camera bật */
.face-guide {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 180px; height: 220px;
    border: 3px dashed rgba(255,255,255,0.6);
    border-radius: 50%;
    pointer-events: none;
    display: none;
}
.face-guide.active { display: block; animation: pulse 2s infinite; }
@keyframes pulse {
    0%,100% { border-color: rgba(255,255,255,0.6); }
    50%      { border-color: rgba(46,117,182,0.9); }
}

/* Countdown overlay */
#countdown-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 6rem;
    color: white;
    font-weight: bold;
}

/* ── Preview ── */
#preview-container { display: none; text-align: center; }
#preview-img {
    max-width: 100%;
    border-radius: 12px;
    border: 3px solid #2E75B6;
    transform: scaleX(-1);
}

/* ── Status ── */
#enroll-status {
    border-radius: 10px;
    padding: 14px 18px;
    margin: 16px 0;
    display: none;
    font-size: 0.95rem;
}

/* ── Buttons ── */
.btn-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 20px;
}
.btn-row .btn { flex: 1; min-width: 140px; padding: 12px; font-size: 1rem; border-radius: 10px; }
.btn-primary   { background: #2E75B6; border-color: #2E75B6; }
.btn-success   { background: #198754; border-color: #198754; }

/* ── Already enrolled badge ── */
.enrolled-badge {
    background: #d1e7dd;
    border: 1px solid #a3cfbb;
    border-radius: 10px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
</style>

<div class="enroll-wrapper">
<div class="enroll-card">

    <!-- Header -->
    <div class="enroll-header">
        <span class="icon">🧑‍💻</span>
        <div>
            <h2>Đăng ký khuôn mặt</h2>
            <p>
                <?php if ($target_userid == $USER->id): ?>
                    Tài khoản của bạn: <strong><?= fullname($target_user) ?></strong>
                <?php else: ?>
                    Đăng ký cho: <strong><?= fullname($target_user) ?></strong>
                    (ID: <?= $target_userid ?>)
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="enroll-body">

        <!-- Đã đăng ký rồi thì hiện badge -->
        <?php if ($existing): ?>
        <div class="enrolled-badge">
            <span style="font-size:1.5rem">✅</span>
            <div>
                <strong>Đã đăng ký khuôn mặt</strong><br>
                <small>Đăng ký lúc: <?= userdate($existing->timecreated, '%H:%M %d/%m/%Y') ?></small>
                — Bạn có thể chụp lại để cập nhật.
            </div>
        </div>
        <?php endif; ?>

        <!-- Hướng dẫn -->
        <div style="background:#f0f7ff; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:0.9rem;">
            <strong>📋 Hướng dẫn:</strong>
            <ol style="margin:8px 0 0; padding-left:20px;">
                <li>Nhấn <strong>Bật Camera</strong></li>
                <li>Nhìn thẳng vào camera, đặt mặt vào khung tròn</li>
                <li>Đảm bảo đủ ánh sáng, không đeo kính râm</li>
                <li>Nhấn <strong>Chụp ảnh</strong> → xem preview → nhấn <strong>Xác nhận đăng ký</strong></li>
            </ol>
        </div>

        <!-- Camera view -->
        <div class="camera-container" id="camera-box">
            <div id="camera-placeholder">
                <span class="icon">📷</span>
                Camera chưa bật
            </div>
            <video id="enroll-video" autoplay muted playsinline></video>
            <div class="face-guide" id="face-guide"></div>
            <div id="countdown-overlay"></div>
        </div>

        <!-- Preview ảnh vừa chụp -->
        <div id="preview-container" style="margin-top:16px;">
            <p style="text-align:center; color:#555; margin-bottom:8px;">📸 Xem lại ảnh trước khi đăng ký:</p>
            <img id="preview-img" src="" alt="Preview">
        </div>

        <!-- Canvas ẩn để capture -->
        <canvas id="enroll-canvas" style="display:none"></canvas>

        <!-- Status thông báo -->
        <div id="enroll-status"></div>

        <!-- Buttons -->
        <div class="btn-row">
            <button id="btn-start" class="btn btn-outline-primary btn-lg">
                📷 Bật Camera
            </button>
            <button id="btn-capture" class="btn btn-primary btn-lg" disabled>
                📸 Chụp ảnh
            </button>
            <button id="btn-confirm" class="btn btn-success btn-lg" disabled>
                ✅ Xác nhận đăng ký
            </button>
            <button id="btn-retake" class="btn btn-outline-secondary btn-lg" style="display:none">
                🔄 Chụp lại
            </button>
        </div>

    </div><!-- /.enroll-body -->
</div><!-- /.enroll-card -->
</div><!-- /.enroll-wrapper -->

<script>
(function() {
    // ── Config từ PHP ──
    const CONFIG = {
        userId:  <?= json_encode((string)$target_userid) ?>,
        apiUrl:  <?= json_encode($api_url) ?>,
        sesskey: <?= json_encode(sesskey()) ?>,
        ajaxUrl: <?= json_encode($CFG->wwwroot . '/mod/faceattend/enroll_ajax.php') ?>,
    };

    // ── DOM ──
    const video       = document.getElementById('enroll-video');
    const canvas      = document.getElementById('enroll-canvas');
    const previewImg  = document.getElementById('preview-img');
    const previewBox  = document.getElementById('preview-container');
    const statusEl    = document.getElementById('enroll-status');
    const placeholder = document.getElementById('camera-placeholder');
    const faceGuide   = document.getElementById('face-guide');
    const countdown   = document.getElementById('countdown-overlay');

    const btnStart   = document.getElementById('btn-start');
    const btnCapture = document.getElementById('btn-capture');
    const btnConfirm = document.getElementById('btn-confirm');
    const btnRetake  = document.getElementById('btn-retake');

    let stream      = null;
    let capturedB64 = null;

    // ── Bật Camera ──
    btnStart.addEventListener('click', async () => {
        try {
            showStatus('Đang bật camera...', 'info');
            stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                audio: false
            });
            video.srcObject = stream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
            faceGuide.classList.add('active');

            btnStart.disabled   = true;
            btnCapture.disabled = false;
            btnStart.textContent = '✅ Camera đang hoạt động';
            showStatus('Camera đã bật! Nhìn thẳng vào camera và đặt mặt vào khung tròn.', 'info');

        } catch (err) {
            let msg = 'Không thể bật camera.';
            if (err.name === 'NotAllowedError')  msg = '⛔ Hãy cấp quyền camera cho trình duyệt rồi thử lại.';
            if (err.name === 'NotFoundError')    msg = '📷 Không tìm thấy camera trên thiết bị.';
            if (err.name === 'NotReadableError') msg = '📷 Camera đang được ứng dụng khác sử dụng.';
            showStatus(msg, 'danger');
        }
    });

    // ── Chụp ảnh (có đếm ngược 3s) ──
    btnCapture.addEventListener('click', async () => {
        btnCapture.disabled = true;
        faceGuide.classList.remove('active');

        // Đếm ngược 3 → 2 → 1
        for (let i = 3; i >= 1; i--) {
            countdown.style.display = 'flex';
            countdown.textContent = i;
            await sleep(800);
        }
        countdown.textContent = '📸';
        await sleep(300);
        countdown.style.display = 'none';

        // Capture frame từ video
        canvas.width  = video.videoWidth  || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        // Flip lại (video đã mirror, cần flip ngược để lưu ảnh đúng chiều)
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        capturedB64 = canvas.toDataURL('image/jpeg', 0.92);

        // Hiện preview
        previewImg.src = capturedB64;
        previewBox.style.display = 'block';

        // Tắt camera stream
        stopCamera();
        video.style.display = 'none';
        placeholder.style.display = 'none';

        // Cập nhật buttons
        btnConfirm.disabled          = false;
        btnRetake.style.display      = 'inline-block';
        btnCapture.style.display     = 'none';

        showStatus('Xem lại ảnh. Nếu ảnh rõ ràng và khuôn mặt nhìn thẳng → nhấn <strong>Xác nhận đăng ký</strong>.', 'warning');
        faceGuide.classList.remove('active');
    });

    // ── Chụp lại ──
    btnRetake.addEventListener('click', async () => {
        previewBox.style.display     = 'none';
        btnConfirm.disabled          = true;
        btnRetake.style.display      = 'none';
        btnCapture.style.display     = 'inline-block';
        btnCapture.disabled          = true;
        btnStart.disabled            = false;
        btnStart.textContent         = '📷 Bật Camera';
        capturedB64                  = null;
        hideStatus();
    });

    // ── Xác nhận đăng ký → gửi lên server ──
    btnConfirm.addEventListener('click', async () => {
        if (!capturedB64) return;

        btnConfirm.disabled      = true;
        btnRetake.style.display  = 'none';
        showStatus('⏳ Đang xử lý nhận diện khuôn mặt...', 'info');

        try {
            const resp = await fetch(CONFIG.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sesskey:  CONFIG.sesskey,
                    user_id:  CONFIG.userId,
                    image:    capturedB64,
                    api_url:  CONFIG.apiUrl,
                })
            });

            const result = await resp.json();

            if (result.success) {
                showStatus(
                    `✅ <strong>Đăng ký thành công!</strong><br>
                     Khuôn mặt của bạn đã được lưu vào hệ thống.<br>
                     Bạn có thể dùng khuôn mặt để điểm danh từ bây giờ.`,
                    'success'
                );
                // Ẩn hết buttons
                btnConfirm.style.display = 'none';
                btnRetake.style.display  = 'none';
                btnStart.style.display   = 'none';

                // Redirect về profile sau 3s
                setTimeout(() => {
                    window.location.href = CONFIG.ajaxUrl.replace('enroll_ajax.php', 'enroll.php') + '?userid=' + CONFIG.userId;
                }, 3000);

            } else {
                showStatus(`❌ ${result.message || 'Đăng ký thất bại. Vui lòng thử lại.'}`, 'danger');
                btnRetake.style.display = 'inline-block';
            }

        } catch (err) {
            showStatus('⚠️ Lỗi kết nối server. Kiểm tra Face API đang chạy chưa.', 'danger');
            btnRetake.style.display = 'inline-block';
            console.error(err);
        }
    });

    // ── Helpers ──
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
    }

    function showStatus(msg, type) {
        statusEl.className = `alert alert-${type}`;
        statusEl.innerHTML = msg;
        statusEl.style.display = 'block';
    }

    function hideStatus() {
        statusEl.style.display = 'none';
    }

    function sleep(ms) {
        return new Promise(r => setTimeout(r, ms));
    }

    // Dọn dẹp khi rời trang
    window.addEventListener('beforeunload', stopCamera);

})();
</script>

<?php
echo $OUTPUT->footer();
