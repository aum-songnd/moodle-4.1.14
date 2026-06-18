<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/th_online_exam_manager/classes/form/quiz_select_form.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/quiz_select.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('selectquiz', 'local_th_online_exam_manager'));
$PAGE->set_heading(get_string('selectquiz', 'local_th_online_exam_manager'));

$baseurl = new moodle_url('/local/th_online_exam_manager/quiz_select.php');
$settingsnode = $PAGE->settingsnav->add(get_string('selectquiz', 'local_th_online_exam_manager'), $baseurl);
$settingsnode->make_active();

// Tạo form
$mform = new \local_th_online_exam_manager_quiz_select_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'waiting')));
} else if ($data = $mform->get_data()) {
    // Xử lý dữ liệu sau khi submit
    $courseid = $data->courseid;
    $quizid = $data->quizid;
    $attempts = $data->attemptid;

    $inserted = 0;
    $skipped = 0;
    $rows = []; // dùng để lưu chi tiết từng dòng attempt

    foreach ($attempts as $attemptid) {
        $exists_assign = $DB->record_exists('th_exam_regrade_assign', [
            'quizid' => $quizid,
            'attemptid' => $attemptid
        ]);

        $count = $DB->count_records('th_exam_regrade_requests', [
            'quizid' => $quizid,
            'attemptid' => $attemptid
        ]);

        $exists_requests = ($count == 2);
    
        $userid = $DB->get_field('quiz_attempts', 'userid', ['id' => $attemptid]);
        $username = fullname(core_user::get_user($userid));
    
        if ($exists_assign || $exists_requests) {
            $rows[] = [$attemptid, $username, '⏩ ' . get_string('alreadyexists', 'local_th_online_exam_manager')];
            $skipped++;
            continue;
        }
    
        $DB->insert_record('th_exam_regrade_assign', [
            'quizid' => $quizid,
            'attemptid' => $attemptid,
            'userid' => $userid,
            'timerequested' => time(),
        ]);
    
        $rows[] = [$attemptid, $username, '✅ ' . get_string('assigned', 'local_th_online_exam_manager')];
        $inserted++;
    }    
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectquiz', 'local_th_online_exam_manager'));
$mform->display();
if (!empty($rows)) {
    echo html_writer::tag('h3', get_string('regradeassignmentdetails', 'local_th_online_exam_manager'));

    $table = new html_table();
    $table->head = [
        get_string('attemptid', 'local_th_online_exam_manager'),
        get_string('student', 'local_th_online_exam_manager'),
        get_string('status', 'local_th_online_exam_manager')
    ];
    $table->data = [];

    foreach ($rows as $r) {
        $table->data[] = array_map('format_string', $r);
    }

    echo html_writer::table($table);
}

// Nút quay lại
$backurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'waiting']);
echo html_writer::start_div('mt-3');
echo $OUTPUT->single_button($backurl, get_string('back', 'local_th_online_exam_manager'), 'get');
echo html_writer::end_div();

echo $OUTPUT->footer();
