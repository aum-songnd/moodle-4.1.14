<?php
require('../../config.php');
require_once 'classes/form/action_form.php';
require_once 'lib.php';

$userid   = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$groupid = optional_param('groupid',0, PARAM_TEXT);

$context = context_course::instance($courseid);
require_login($courseid);
require_capability('local/th_exam_monitor:view', $context);
$actionurl = new moodle_url('/local/th_exam_monitor/action.php', ['userid' => $userid, 'courseid' => $courseid, 'groupid' => $groupid]);
$PAGE->set_context($context);
$PAGE->set_url($actionurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('actionstudent', 'local_th_exam_monitor'));
$PAGE->set_heading(get_string('actionstudent', 'local_th_exam_monitor'));

$viewurl = new moodle_url('/local/th_exam_monitor/view.php', ['id' => $courseid, 'groupid' => $groupid]);
$PAGE->navbar->add(get_string('studentlist', 'local_th_exam_monitor'), $viewurl);
$PAGE->navbar->add(get_string('viewhistorytitle', 'local_th_exam_monitor'), $actionurl);

$form = new \local_th_exam_monitor\form\action_form(
    null,
    [
        'userid' => $userid,
        'courseid' => $courseid,
        'groupid' => $groupid
    ]
);

/* ===== CANCEL ===== */
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/th_exam_monitor/view.php', ['id' => $courseid, 'groupid' => $groupid]));
}

/* ===== SUBMIT ===== */
if ($data = $form->get_data()) {

    // ⭐ GỌI HÀM TRONG LIB
    local_th_exam_monitor_save_action(
        $data->userid,
        $data->courseid,
        $data->action_status,
        $data
    );

    redirect(
        new moodle_url('/local/th_exam_monitor/view.php', ['id' => $courseid, 'groupid' => $data->groupid]),
        get_string('actionsaved', 'local_th_exam_monitor'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/* ===== DISPLAY ===== */
echo $OUTPUT->header();
$user = $DB->get_record('user', ['id' => $userid]);
echo html_writer::tag('h3', get_string('actionstudent', 'local_th_exam_monitor') . ': <strong>' . fullname($user) . '</strong>');

$form->display();
echo $OUTPUT->footer();
