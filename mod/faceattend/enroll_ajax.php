<?php
/**
 * enroll_ajax.php — Nhận ảnh từ enroll.php, gửi lên Face API, lưu token vào DB
 *
 * Được gọi bằng fetch() từ JavaScript trong enroll.php
 * Method: POST
 * Body JSON: { sesskey, user_id, image (base64), api_url }
 */
define('AJAX_SCRIPT', true);  // Báo Moodle đây là AJAX, không render header/footer
require_once('../../config.php');

// ── 1. Đọc và validate JSON body ──────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

header('Content-Type: application/json');

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

// ── 2. Kiểm tra sesskey (chống CSRF) ──────────────────────
require_login();
if (!confirm_sesskey($data['sesskey'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Session không hợp lệ. Hãy tải lại trang.']);
    exit;
}

$user_id  = (int)($data['user_id']  ?? 0);
$image    = $data['image']   ?? '';
$api_url  = $data['api_url'] ?? 'http://localhost:8002';

// Chỉ admin mới enroll cho người khác
if ($user_id !== (int)$USER->id) {
    $context = context_system::instance();
    if (!has_capability('moodle/user:update', $context)) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền đăng ký cho người khác.']);
        exit;
    }
}

if (!$user_id || !$image) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu.']);
    exit;
}

// ── 3. Gửi ảnh lên Face Recognition API ───────────────────
$payload = json_encode([
    'user_id' => (string)$user_id,
    'image'   => $image,
]);

$ch = curl_init($api_url . '/api/v1/face/enroll');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

// ── 4. Xử lý response từ Face API ─────────────────────────
if ($curl_err || !$response) {
    echo json_encode([
        'success' => false,
        'message' => "Không kết nối được Face API ({$api_url}). Kiểm tra server đang chạy chưa.",
    ]);
    exit;
}

$api_result = json_decode($response, true);

if ($http_code !== 200 || !($api_result['success'] ?? false)) {
    $msg = $api_result['detail'] ?? $api_result['message'] ?? 'Đăng ký thất bại.';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$face_token = $api_result['face_token'];

// ── 5. Lưu face_token vào Moodle DB ───────────────────────
$now        = time();
$existing   = $DB->get_record('faceattend_enrollment', ['userid' => $user_id]);

if ($existing) {
    // Cập nhật token mới
    $existing->face_token    = $face_token;
    $existing->is_active     = 1;
    $existing->enrolled_by   = $USER->id;
    $existing->timemodified  = $now;
    $DB->update_record('faceattend_enrollment', $existing);
} else {
    // Tạo mới
    $DB->insert_record('faceattend_enrollment', (object)[
        'userid'       => $user_id,
        'face_token'   => $face_token,
        'enrolled_by'  => $USER->id,
        'is_active'    => 1,
        'timecreated'  => $now,
        'timemodified' => $now,
    ]);
}

echo json_encode([
    'success'    => true,
    'face_token' => $face_token,
    'message'    => 'Đăng ký khuôn mặt thành công!',
]);
