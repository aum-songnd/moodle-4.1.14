<?php
require('../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/th_daily_grade_update:view', $context);

$PAGE->set_url(new moodle_url('/local/th_daily_grade_update/viewlog.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_th_daily_grade_update'));
$PAGE->set_heading(get_string('logheading', 'local_th_daily_grade_update'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('logheading', 'local_th_daily_grade_update') . '</center>');

global $DB;
$logs = $DB->get_records('local_th_daily_grade_log', null, 'timecreated DESC', '*');

if (empty($logs)) {
    echo $OUTPUT->notification(get_string('notfoundlog', 'local_th_daily_grade_update'), 'info');
} else {
    echo html_writer::start_tag('table', ['class' => 'generaltable boxaligncenter table-bordered']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('time', 'local_th_daily_grade_update'));
    echo html_writer::tag('th', get_string('user', 'local_th_daily_grade_update'));
    echo html_writer::tag('th', get_string('course', 'local_th_daily_grade_update'));
    echo html_writer::tag('th', get_string('itemname', 'local_th_daily_grade_update')); // Thêm cột Item Name
    echo html_writer::tag('th', get_string('oldgrade', 'local_th_daily_grade_update'));
    echo html_writer::tag('th', get_string('newgrade', 'local_th_daily_grade_update'));
    echo html_writer::tag('th', get_string('status', 'local_th_daily_grade_update'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');

    echo html_writer::start_tag('tbody');

    foreach ($logs as $log) {
        $user = $DB->get_record('user', ['id' => $log->userid], 'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename');
        $course = $DB->get_record('course', ['id' => $log->courseid], 'id, fullname');

        // Lấy grade item name từ bảng grade_items
        $gradeitem = $DB->get_record('grade_items', ['id' => $log->gradeitemid], 'id, itemname');

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', userdate($log->timecreated));
        echo html_writer::tag('td', fullname($user));
        echo html_writer::tag('td', $course ? $course->fullname : 'N/A');
        echo html_writer::tag('td', $gradeitem ? $gradeitem->itemname : 'N/A'); // Hiển thị item name
        echo html_writer::tag('td', $log->oldgrade);
        echo html_writer::tag('td', $log->newgrade);
        echo html_writer::tag('td', $log->status == 'success' ? get_string('success', 'local_th_daily_grade_update') : get_string('fail', 'local_th_daily_grade_update'));
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.generaltable', get_string('logheading', 'local_th_daily_grade_update'), $lang));

echo $OUTPUT->footer();
