<?php
ob_start();

require('../../config.php');
require_once(__DIR__ . '/classes/form/report_form.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once 'lib.php';

$id = required_param('courseid', PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_TEXT);
$context = context_course::instance($id);

require_login($id);
require_capability('local/th_online_exam_manager:exam_results', $context);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/exam_results.php', array('courseid' => $id)));
$PAGE->set_pagelayout('course');
$PAGE->set_heading(get_string('resultreport', 'local_th_online_exam_manager'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('resultreport', 'local_th_online_exam_manager'));

echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('resultreport', 'local_th_online_exam_manager'), [
    'id' => 'report-title',
    'class' => 'text-center mb-4'
]);

$allgroups = groups_get_all_groups($id);
$usergroups = groups_get_user_groups($id, $USER->id)[0]; // [0] là group mode
$canaccessall = has_capability('moodle/course:managegroups', $context);

// Tạo action có courseid.
$action = new moodle_url($PAGE->url);
$action->param('courseid', $id);

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => $action->out(false)
]);

// Thêm hidden để chắc chắn courseid luôn submit theo form.
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'courseid',
    'value' => $id
]);

echo html_writer::label(get_string('selectexamroom', 'local_th_online_exam_manager') . ': ', 'groupid');

// Build options.
$options = ['0' => get_string('pleaseselect', 'local_th_online_exam_manager')];
if ($canaccessall) {
    $options['all'] = get_string('allparticipants');
}
$usergroupset = $canaccessall ? null : array_flip((array)$usergroups);
foreach ($allgroups as $g) {
    if ($canaccessall || isset($usergroupset[$g->id])) {
        $options[(string)$g->id] = format_string($g->name);
    }
}

// Select + auto submit.
echo html_writer::select(
    $options,
    'groupid',
    (string)$groupid,
    null,
    ['id' => 'groupid', 'onchange' => 'this.form.submit()']
);

// Fallback khi tắt JS.
echo html_writer::tag('noscript',
    html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('go')])
);

echo html_writer::end_tag('form');

$quizids = [];
$courseids = [$id];
$quizids = load_quizzes_in_courses($courseids);
    
if (!$groupid) {
    $enrolmap = [];
} else if ($groupid == 'all' && $canaccessall) {
    $enrolmap = load_users_enrolled_in_courses($courseids);
} else {
    $enrolmap = load_users_in_groups($groupid);
}

$userids = array_keys($enrolmap);

// Nếu không có dữ liệu
if (empty($userids)) {
    echo html_writer::div(get_string('nodatafound', 'local_th_online_exam_manager'), 'alert alert-danger');
} else {
    // Nếu có dữ liệu, hiển thị bảng kết quả
    require_once(__DIR__ . '/classes/report/report_result_table.php');
    $table = new \local_th_online_exam_manager\report\report_result_table(
        'report_result_table',
        $userids,
        $courseids,
        '',
        $quizids,
        $enrolmap
    );

    $table->attributes['id'] = 'report_table';
    $table->attributes['style'] = 'width: 100%; text-align: center;';
    $table->attributes['border'] = 1;
    $table->define_baseurl(new moodle_url('/local/th_online_exam_manager/exam_results.php', array('courseid' => $id)));
    $table->setup();
    $table->query_db(0);
    $table->out(0, false);

    // Thêm phần hiển thị bảng dữ liệu
    $lang = current_language();
    echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#report_table', 'Report', $lang));
}

// Form xuất báo cáo
if($groupid) {
    echo html_writer::start_tag('form', [
        'method' => 'get',
        'action' => $action->out(false),
        'style'  => 'margin-top:10px'
    ]);

    echo html_writer::empty_tag('input', [
        'type'  => 'hidden',
        'name'  => 'courseid',
        'value' => $id
    ]);

    echo html_writer::empty_tag('input', [
        'type'  => 'hidden',
        'name'  => 'groupid',
        'value' => $groupid
    ]);

    echo html_writer::empty_tag('input', [
        'type'  => 'hidden',
        'name'  => 'export',
        'value' => 1
    ]);

    echo html_writer::empty_tag('input', [
        'type'  => 'submit',
        'value' => get_string('exportresults', 'local_th_online_exam_manager'),
        'class' => 'btn btn-primary'
    ]);

    echo html_writer::end_tag('form');
}


// Xử lý xuất báo cáo khi nhấn nút "Xuất báo cáo"
$isexport = optional_param('export', 0, PARAM_INT);
if ($isexport) {
    require_once(__DIR__ . '/classes/report/report_result_hcct_table.php');

    $course = $DB->get_record('course', ['id' => $id], 'id, fullname', MUST_EXIST);
    $coursename = clean_filename($course->fullname);

    // Tạo bảng lại trong chế độ export
    $table = new \local_th_online_exam_manager\report\report_result_hcct_table(
        'report_result_table',
        $userids,
        [$id],
        '',
        $quizids,
        $enrolmap
    );

    $table->setup();
    $table->query_db(0);

    // Lấy tên group nếu có chọn group cụ thể
    $groupnamehtml = '';
    if (!empty($groupid) && $groupid !== 'all') {
        $group = $DB->get_record('groups', ['id' => $groupid], 'id, name');
        if ($group) {
            $groupnamehtml = '
            <div style="text-align:center; font-size:12px;">
                <strong>' . s($group->name) . '</strong>
            </div>
            ';
        }
    }

    $headerhtml = '
    <table class="header-table" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align:center; font-size:12px;">
                ỦY BAN NHÂN DÂN THÀNH PHỐ HÀ NỘI<br>
                <strong>TRƯỜNG CAO ĐẲNG</strong><br>
                <strong>THƯƠNG MẠI VÀ DU LỊCH HÀ NỘI</strong><br>
                ──────────────
            </td>
            <td style="text-align:center; font-weight:bold; font-size:14px;">
                DANH SÁCH THI KẾT THÚC MÔN HỌC
            </td>
        </tr>
    </table>

    <br>
    <div style="text-align:center; font-size:12px;">
        <strong>Tên phòng thi:</strong> ' . s($course->fullname) . '
    </div>
    ' . $groupnamehtml . '
    <br>
    ';

    ob_start();
    $table->out(0, false); // In bảng ra buffer
    $tablehtml = ob_get_clean();
    $tablehtml = preg_replace(
        '/<table([^>]*)>/',
        '<table$1 width="100%" cellpadding="0" cellspacing="0">
        <colgroup>
            <col width="30">   <!-- TT -->
            <col width="45">   <!-- SBD -->
            <col width="70">   <!-- Mã HV -->
            <col width="300">  <!-- Họ và tên -->
            <col width="70">   <!-- Ngày sinh -->
            <col width="120">  <!-- Mã lớp -->
            <col width="40">   <!-- Điểm số -->
            <col width="40">   <!-- Điểm chữ -->
            <col width="60">   <!-- Ghi chú -->
        </colgroup>',
        $tablehtml,
        1
    );

    $summaryhtml = $table->get_summary_html();

    $signaturehtml = '
    <br><br>
    <table width="100%" cellpadding="0" cellspacing="0" style="border:none;">
        <tr>
            <td style="border:none; width:55%;"></td>
            <td style="border:none; width:45%; text-align:center;">
                <div style="font-style:italic;">
                    Hà Nội, Ngày........tháng........năm 20.....
                </div>
                <div style="margin-top:10px; font-weight:bold;">
                    Giảng viên chấm điểm trực tiếp
                </div>
                <div style="font-weight:bold;">
                    (hoặc cán bộ nhập điểm)
                </div>
                <div style="margin-top:15px; font-style:italic;">
                    (ký ghi rõ họ tên)
                </div>
            </td>
        </tr>
    </table>
    ';

    $html = '
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            @page { size: A4; margin: 1.5cm; }
            body { font-family: "Times New Roman"; font-size: 12px; }
            table { border-collapse: collapse; }
            th, td {
                border: 1px solid #000;
                padding: 4px;
                text-align: center;
                vertical-align: middle;
            }
            th:nth-child(4), td:nth-child(4) { text-align: left; }
            .header-table, .header-table td, .header-table th {
                border: none !important;
            }
        </style>
    </head>
    <body>
    ' . $headerhtml . '
    ' . $tablehtml . '
    <br>
    ' . $summaryhtml . '
    ' . $signaturehtml . '
    </body>
    </html>';

    ob_end_clean();

    header('Content-Type: application/msword; charset=utf-8');
    header('Content-Disposition: attachment; filename="ket_qua_' . $coursename . '.doc"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    echo $html;
    exit;
}


echo $OUTPUT->footer();
