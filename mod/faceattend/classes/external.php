<?php
/**
 * classes/external.php — Web Service functions (AJAX endpoint)
 *
 * File này định nghĩa hàm PHP được gọi từ JavaScript qua Moodle AJAX.
 * Khi JS gọi Ajax.call([{ methodname: 'mod_faceattend_submit_attendance' }])
 * → Moodle tự động route đến hàm submit_attendance::execute() ở đây.
 *
 * Luồng xử lý:
 *   JS gửi ảnh base64 → PHP nhận → Gửi đến Face API → Lưu kết quả vào DB
 */

defined('MOODLE_INTERNAL') || die();

// External API base class của Moodle
require_once($CFG->libdir . '/externallib.php');

class mod_faceattend_external extends external_api {

    // ─────────────────────────────────────────────────────
    // KHAI BÁO THAM SỐ ĐẦU VÀO (Moodle validation)
    // Moodle tự động validate và sanitize các tham số này
    // ─────────────────────────────────────────────────────
    public static function submit_attendance_parameters() {
        return new external_function_parameters([
            'cmid'       => new external_value(PARAM_INT,  'Course module ID'),
            'image_data' => new external_value(PARAM_RAW,  'Base64 encoded JPEG image from camera'),
            'face_token' => new external_value(PARAM_TEXT, 'Face enrollment token from DB', VALUE_OPTIONAL, ''),
        ]);
    }

    // ─────────────────────────────────────────────────────
    // KHAI BÁO CẤU TRÚC KẾT QUẢ TRẢ VỀ
    // ─────────────────────────────────────────────────────
    public static function submit_attendance_returns() {
        return new external_single_structure([
            'success'     => new external_value(PARAM_BOOL,  'Attendance recorded successfully'),
            'message'     => new external_value(PARAM_TEXT,  'Status message for the user'),
            'confidence'  => new external_value(PARAM_FLOAT, 'Face match confidence score', VALUE_OPTIONAL, 0),
            'attend_time' => new external_value(PARAM_TEXT,  'Formatted attendance timestamp', VALUE_OPTIONAL, ''),
        ]);
    }

    // ─────────────────────────────────────────────────────
    // HÀM CHÍNH: XỬ LÝ ĐIỂM DANH
    // ─────────────────────────────────────────────────────
    public static function submit_attendance($cmid, $image_data, $face_token = '') {
        global $DB, $USER;

        // ── Bước 1: Validate tham số đầu vào (Moodle standard) ──
        $params = self::validate_parameters(
            self::submit_attendance_parameters(),
            ['cmid' => $cmid, 'image_data' => $image_data, 'face_token' => $face_token]
        );

        // ── Bước 2: Kiểm tra context & quyền hạn ──
        $cm      = get_coursemodule_from_id('faceattend', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/faceattend:attend', $context);

        // ── Bước 3: Load activity ──
        $faceattend = $DB->get_record('faceattend', ['id' => $cm->instance], '*', MUST_EXIST);

        // Kiểm tra buổi điểm danh còn mở không
        if (!$faceattend->is_open) {
            return ['success' => false, 'message' => 'Buổi điểm danh đã kết thúc.', 'confidence' => 0, 'attend_time' => ''];
        }

        // ── Bước 4: Kiểm tra đã điểm danh chưa ──
        $already = $DB->record_exists('faceattend_log', [
            'faceattendid' => $faceattend->id,
            'userid'       => $USER->id,
            'status'       => 'present',
        ]);
        if ($already) {
            return ['success' => true, 'message' => 'Bạn đã điểm danh rồi!', 'confidence' => 0, 'attend_time' => ''];
        }

        // ── Bước 5: Lấy/tạo bản ghi log ──
        $log = $DB->get_record('faceattend_log', [
            'faceattendid' => $faceattend->id,
            'userid'       => $USER->id,
        ]);

        if (!$log) {
            $log = (object)[
                'faceattendid' => $faceattend->id,
                'userid'       => $USER->id,
                'status'       => 'absent',
                'confidence'   => 0,
                'attempt_count' => 0,
                'timecreated'  => time(),
                'timeattended' => null,
            ];
            $log->id = $DB->insert_record('faceattend_log', $log);
        }

        // Kiểm tra còn lần thử không
        if ($log->attempt_count >= $faceattend->max_attempts) {
            return [
                'success'     => false,
                'message'     => 'Đã hết số lần thử.',
                'confidence'  => 0,
                'attend_time' => '',
            ];
        }

        // ── Bước 6: Gọi Face Recognition API ──
        $api_result = self::call_face_api(
            $faceattend->api_url,
            $params['image_data'],
            $params['face_token']
        );

        // Cập nhật số lần thử
        $log->attempt_count++;

        if ($api_result['success'] && $api_result['confidence'] >= $faceattend->confidence_threshold) {
            // ✅ NHẬN DIỆN THÀNH CÔNG
            $now = time();
            $log->status       = 'present';
            $log->confidence   = $api_result['confidence'];
            $log->timeattended = $now;

            // Lưu hash của ảnh (không lưu ảnh gốc — bảo mật)
            $log->face_image_hash = hash('sha256', $params['image_data']);

            $DB->update_record('faceattend_log', $log);

            return [
                'success'     => true,
                'message'     => 'Điểm danh thành công!',
                'confidence'  => round($api_result['confidence'], 1),
                'attend_time' => userdate($now, '%H:%M %d/%m/%Y'),
            ];

        } else {
            // ❌ NHẬN DIỆN THẤT BẠI
            $reason = $api_result['reason'] ?? 'unknown';
            $DB->update_record('faceattend_log', $log);

            $msg = match($reason) {
                'no_face'       => 'Không phát hiện khuôn mặt. Hãy nhìn thẳng vào camera.',
                'low_confidence' => "Độ chính xác quá thấp ({$api_result['confidence']}%). Hãy đảm bảo đủ ánh sáng.",
                'liveness_fail' => 'Phát hiện giả mạo. Hãy dùng khuôn mặt thật.',
                'not_found'     => 'Khuôn mặt không khớp với hồ sơ đã đăng ký.',
                default         => 'Nhận diện thất bại. Vui lòng thử lại.',
            };

            return [
                'success'     => false,
                'message'     => $msg,
                'confidence'  => round($api_result['confidence'] ?? 0, 1),
                'attend_time' => '',
            ];
        }
    }

    // ─────────────────────────────────────────────────────
    // GỌI FACE RECOGNITION API (Python server)
    // ─────────────────────────────────────────────────────
    private static function call_face_api($api_url, $image_data, $face_token) {
        // Chuẩn bị payload gửi lên Face API
        $payload = json_encode([
            'image'      => $image_data,   // Base64 JPEG
            'face_token' => $face_token,   // Token để tra cứu embedding
            'check_liveness' => true,      // Bật kiểm tra liveness
        ]);

        // Dùng cURL để gọi HTTP POST
        $ch = curl_init($api_url . '/api/v1/face/verify');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,   // Timeout 10 giây
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response    = curl_exec($ch);
        $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error  = curl_error($ch);
        curl_close($ch);

        // Xử lý lỗi kết nối
        if ($curl_error || $http_code !== 200) {
            // Face API chưa chạy → trả về mock result để dev/test
            // TODO: Xóa đoạn này khi Face API đã sẵn sàng
            return self::mock_face_api_result($image_data);
        }

        // Parse JSON response từ Face API
        $data = json_decode($response, true);
        if (!$data) {
            return ['success' => false, 'confidence' => 0, 'reason' => 'invalid_response'];
        }

        return $data;
    }

    /**
     * Mock Face API — Dùng trong giai đoạn phát triển khi chưa có Face API thật
     * Luôn trả về success=true với confidence ngẫu nhiên để test UI
     *
     * XÓA HÀM NÀY khi deploy production!
     */
    private static function mock_face_api_result($image_data) {
        $fake_confidence = rand(82, 97); // Random 82-97%
        return [
            'success'    => true,
            'confidence' => $fake_confidence,
            'reason'     => 'mock',
            'message'    => "[DEV] Mock result - confidence: {$fake_confidence}%",
        ];
    }
}
