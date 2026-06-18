<?php
/**
 * export.php — Xuất báo cáo điểm danh ra file CSV
 * Giảng viên click "Xuất CSV" → tải về file .csv
 */
require_once('../../config.php');

$id = required_param('id', PARAM_INT);
require_sesskey();

$cm      = get_coursemodule_from_id('faceattend', $id, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$faceattend = $DB->get_record('faceattend', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/faceattend:viewreport', $context);

// Lấy sinh viên enrolled
$enrolled = get_enrolled_users($context, 'mod/faceattend:attend');

// Lấy log điểm danh
$logs = $DB->get_records('faceattend_log',
    ['faceattendid' => $faceattend->id], '', 'userid, status, confidence, timeattended, attempt_count'
);
$logs_by_user = [];
foreach ($logs as $log) {
    $logs_by_user[$log->userid] = $log;
}

// Thiết lập HTTP headers để trình duyệt tải về file CSV
$filename = clean_filename($faceattend->name . '_' . date('Ymd_His') . '.csv');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');

// Thêm BOM UTF-8 để Excel mở đúng tiếng Việt
echo "\xEF\xBB\xBF";

// Mở output buffer dạng CSV
$out = fopen('php://output', 'w');

// Header row
fputcsv($out, ['STT', 'Họ tên', 'Email', 'Mã SV', 'Thời gian điểm danh', 'Độ chính xác (%)', 'Số lần thử', 'Trạng thái']);

$i = 1;
foreach ($enrolled as $user) {
    $log = $logs_by_user[$user->id] ?? null;
    fputcsv($out, [
        $i++,
        fullname($user),
        $user->email,
        $user->idnumber ?? '',
        ($log && $log->timeattended) ? userdate($log->timeattended, '%H:%M %d/%m/%Y') : '',
        ($log && $log->confidence)   ? number_format($log->confidence, 1) : '',
        $log ? $log->attempt_count : 0,
        ($log && $log->status === 'present') ? 'Có mặt' : 'Vắng mặt',
    ]);
}

fclose($out);
exit;
