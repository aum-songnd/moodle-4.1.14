<?php
require_once(__DIR__ . '/../../config.php');

require_login();

global $DB, $USER;

$format = optional_param('format', 'csv', PARAM_ALPHA);
$format = strtolower($format);
if (!in_array($format, ['csv', 'doc'], true)) {
    $format = 'csv';
}

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/download.php', ['format' => $format]));
$PAGE->set_context(context_user::instance($USER->id));

// Lấy studentcode từ user profile field
$shortname = trim((string)get_config('block_th_exam_schedule', 'student_code'));
$studentcode = '';
if ($shortname !== '') {
    $studentcode = $DB->get_field_sql("
        SELECT uid.data
          FROM {user_info_data} uid
          JOIN {user_info_field} uif ON uif.id = uid.fieldid
         WHERE uid.userid = :userid
           AND uif.shortname = :shortname
    ", [
        'userid' => $USER->id,
        'shortname' => $shortname,
    ]);
    $studentcode = $studentcode ? trim((string)$studentcode) : '';
}

// Query theo studentcode (không dùng userid)
$records = [];
if ($studentcode !== '') {
    $sql = "
    SELECT e.id, e.coursecode, c.coursename, e.examdate, e.examslot
    FROM {block_thexamschedule_exam} e
    LEFT JOIN {block_thexamschedule_course} c ON c.coursecode = e.coursecode
    WHERE e.studentcode = :studentcode
    ORDER BY e.examdate DESC, e.coursecode ASC, e.examslot DESC
";
    $records = $DB->get_records_sql($sql, ['studentcode' => $studentcode]);
}

$base = get_string('examschedule', 'block_th_exam_schedule');

if ($format === 'csv') {
    $filename = $base . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');

    // BOM để Excel mở tiếng Việt chuẩn
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($out, [
        get_string('col_no', 'block_th_exam_schedule'),
        get_string('col_coursecode', 'block_th_exam_schedule'),
        get_string('col_coursename', 'block_th_exam_schedule'),
        get_string('col_examdate', 'block_th_exam_schedule'),
        get_string('col_examslot', 'block_th_exam_schedule'),
    ]);

    $i = 1;
    foreach ($records as $r) {
        fputcsv($out, [
            $i++,
            $r->coursecode,
            $r->coursename ?? '',
            userdate((int)$r->examdate, '%d/%m/%Y'),
            $r->examslot,
        ]);
    }

    fclose($out);
    exit;
}

// ===== DOC (Word) =====
$filename = $base . '.doc';

// Họ tên (Moodle lưu firstname/lastname)
$fullname = trim($USER->firstname . ' ' . $USER->lastname);
if ($fullname === '') {
    $fullname = fullname($USER);
}

header('Content-Type: application/msword; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<html><head><meta charset="utf-8">';
echo '<style>
    body { font-family: "Times New Roman", serif; font-size: 13pt; }
    .hdr { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .hdr td { width: 50%; vertical-align: top; }
    .left { text-align: center; }
    .right { text-align: center; }
    .bold { font-weight: bold; }
    .line { width: 60%; margin: 6px auto 0 auto; border-top: 1px solid #000; height: 1px; }
    .title { text-align: center; font-weight: bold; font-size: 16pt; margin-top: 22px; }
    .info { width: 100%; border-collapse: collapse; margin-top: 14px; table-layout: fixed; }
    .info td { width: 50%; }
    .info .l { text-align: left; }
    .info .r { text-align: right; }
    table.data { border-collapse: collapse; width: 100%; margin-top: 12px; }
    table.data th, table.data td { border: 1px solid #000; padding: 6px 8px; }
    table.data th { font-weight: bold; text-align: center; }
</style>';
echo '</head><body>';

// Header 2 cột
echo '<table class="hdr"><tr>';

echo '<td class="left">';
echo '<div>ỦY BAN NHÂN DÂN THÀNH PHỐ HÀ NỘI</div>';
echo '<div class="bold">TRƯỜNG CAO ĐẲNG</div>';
echo '<div class="bold">THƯƠNG MẠI VÀ DU LỊCH HÀ NỘI</div>';
echo '<div class="line"></div>';
echo '</td>';

echo '<td class="right">';
echo '<div>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>';
echo '<div class="bold">Độc lập – Tự do – Hạnh phúc</div>';
echo '<div class="line"></div>';
echo '</td>';

echo '</tr></table>';

// Title
echo '<div class="title">LỊCH THI CỦA SINH VIÊN</div>';

// Info
echo '<table class="info"><tr>';
echo '<td class="l"><span class="bold">Họ và tên:</span> ' . htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') . '</td>';
echo '<td class="r"><span class="bold">Mã sinh viên:</span> ' . htmlspecialchars($studentcode, ENT_QUOTES, 'UTF-8') . '</td>';
echo '</tr></table>';

// Table
echo '<table class="data">';
echo '<tr>';
echo '<th>' . htmlspecialchars(get_string('col_no', 'block_th_exam_schedule'), ENT_QUOTES, 'UTF-8') . '</th>';
echo '<th>' . htmlspecialchars(get_string('col_coursecode', 'block_th_exam_schedule'), ENT_QUOTES, 'UTF-8') . '</th>';
echo '<th>' . htmlspecialchars(get_string('col_coursename', 'block_th_exam_schedule'), ENT_QUOTES, 'UTF-8') . '</th>';
echo '<th>' . htmlspecialchars(get_string('col_examdate', 'block_th_exam_schedule'), ENT_QUOTES, 'UTF-8') . '</th>';
echo '<th>' . htmlspecialchars(get_string('col_examslot', 'block_th_exam_schedule'), ENT_QUOTES, 'UTF-8') . '</th>';
echo '</tr>';

$i = 1;
foreach ($records as $r) {
    echo '<tr>';
    echo '<td style="text-align:center;">' . $i++ . '</td>';
    echo '<td>' . htmlspecialchars($r->coursecode, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($r->coursename ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="text-align:center;">' . htmlspecialchars(userdate((int)$r->examdate, '%d/%m/%Y'), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="text-align:center;">' . htmlspecialchars($r->examslot, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';
}

echo '</table>';

echo '</body></html>';
exit;
