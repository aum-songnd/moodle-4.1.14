<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $USER, $DB, $PAGE, $OUTPUT;

$courseid = optional_param('id', 0, PARAM_INT);

// Context & UI
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_exam_monitor/suspended.php', ['id' => $courseid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('suspendedpagetitle', 'local_th_exam_monitor'));
$PAGE->set_heading(get_string('suspendedpagetitle', 'local_th_exam_monitor'));
$PAGE->navbar->add(
    get_string('pluginname', 'local_th_exam_monitor'),
    new moodle_url('/local/th_exam_monitor/suspended.php', ['id' => $courseid])
);

// Render
echo $OUTPUT->header();

// Bản ghi đình chỉ (status=30) của user trong course
$records = $DB->get_records(
    'local_th_exam_monitor',
    ['userid' => $USER->id, 'courseid' => $courseid, 'action_status' => 30],
    'timecreated DESC, id DESC',
    'id, note, timecreated'
);

if ($records) {
    // Thông báo chung
    echo $OUTPUT->notification(
        get_string('suspendednotice', 'local_th_exam_monitor'),
        \core\output\notification::NOTIFY_ERROR
    );

    // Liệt kê lý do (nếu có)
    $items = [];
    foreach ($records as $r) {
        $reason = trim((string)($r->note ?? ''));
        $when = !empty($r->timecreated) ? ' — ' . userdate($r->timecreated) : '';
        if ($reason !== '') {
            $items[] = html_writer::tag('li',
                html_writer::tag('strong', get_string('reasonlabel', 'local_th_exam_monitor') . ' ')
                . format_string($reason) . $when
            );
        }
    }

    if (!empty($items)) {
        echo html_writer::tag('ul', implode('', $items), ['class' => 'mt-3']);
    } else {
        echo html_writer::div(
            get_string('noreasondetails', 'local_th_exam_monitor'),
            'alert alert-info',
            ['role' => 'status']
        );
    }

} else {
    echo html_writer::start_div('box py-4', ['style' => 'max-width:680px;margin:40px auto;']);
    echo $OUTPUT->notification(
        get_string('nosuspensioninfo', 'local_th_exam_monitor'),
        \core\output\notification::NOTIFY_ERROR
    );
    echo html_writer::end_div();
}

// // Nút quay lại
// $backurl = new moodle_url('/my');
// echo html_writer::div(
//     $OUTPUT->single_button($backurl, get_string('back')),
//     'mt-3'
// );

echo $OUTPUT->footer();
