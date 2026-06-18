/**
 * JS cho tiến trình đồng bộ ngân hàng câu hỏi.
 * Hiển thị thanh tiến trình và cập nhật theo thời gian thực.
 *
 * @module local_th_syncexamqbank/sync
 */
define(['jquery'], function($) {
    'use strict';

    return {
        /**
         * Khởi tạo script.
         */
        init: function() {
            if (typeof window.startdate === 'undefined' || typeof M.cfg.sesskey === 'undefined') {
                //console.warn('Thiếu biến startdate hoặc sesskey');
                return;
            }

            let step = 0;
            let stop = false;

            /**
             * Cập nhật thanh tiến trình.
             * @param {number} pct - Phần trăm hoàn thành.
             * @param {string} text - Nội dung trạng thái hiển thị.
             */
            const updateProgress = function(pct, text) {
                $('.sync-progress-bar').css('width', pct + '%');
                $('.sync-progress-text').html(text);
            };

            /**
             * Khi bấm nút dừng.
             */
            $('#stop-sync').on('click', function() {
                stop = true;
                $(this).attr('disabled', true).text('Đang dừng...');
                updateProgress(0, '⛔ Đã yêu cầu dừng đồng bộ...');
            });

            /**
             * Gửi request xử lý từng bước.
             */
            const doStep = function() {
                if (stop) {
                    updateProgress(0, '⛔ Đồng bộ đã bị dừng bởi người dùng.');
                    return;
                }

                $.ajax({
                    url: M.cfg.wwwroot + '/local/th_syncexamqbank/ajax/sync.php',
                    method: 'GET',
                    data: {
                        startdate: window.startdate,
                        step: step,
                        sesskey: M.cfg.sesskey
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.done) {
                            updateProgress(100, res.message || '✅ Hoàn tất đồng bộ.');

                            // Hiện nút reload
                            $('.sync-progress-text').append('<br><a class="btn btn-secondary mt-3" href="">Quay lại</a>');
                            $('#stop-sync').hide();

                            return;
                        }

                        if (res.progress !== undefined && res.message !== undefined) {
                            updateProgress(res.progress, res.message);
                        }

                        // Tiếp tục bước tiếp theo
                        step = res.step || step;
                        setTimeout(doStep, 1000);
                    },
                    error: function(xhr, status, error) {
                        //console.error(error);
                        updateProgress(0, '❌ Lỗi kết nối đến máy chủ: ' + error);
                    }
                });
            };

            // Bắt đầu đồng bộ
            doStep();
        }
    };
});
