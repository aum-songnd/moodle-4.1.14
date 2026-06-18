<?php
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/th_syncexamqbank:manage', $context);
require_sesskey(); // ✅ Thêm bảo vệ CSRF

// Tham số
$startdate = required_param('startdate', PARAM_INT);
$step = optional_param('step', 0, PARAM_INT);

// Đảm bảo output là JSON (không cache)
\core\session\manager::write_close();
header('Content-Type: application/json');

try {
    $progress = \local_th_syncexamqbank\sync_manager::sync_step($startdate, $step);
    echo json_encode($progress, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'done' => true,
        'progress' => 0,
        'message' => '❌ Lỗi hệ thống: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
