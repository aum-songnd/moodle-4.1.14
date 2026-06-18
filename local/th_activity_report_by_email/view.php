<?php

require_once __DIR__ . '/../../config.php';
require_once 'locallib.php';
require_once $CFG->dirroot . '/local/th_activity_report_by_email/classes/local_th_activity_report_by_email.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'error');
}

require_login($COURSE->id);
require_capability('local/th_activity_report_by_email:view', context_system::instance());

$pageurl = new moodle_url('/local/th_activity_report_by_email/view.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$activity_report = get_string('activity_report', 'local_th_activity_report_by_email');
$PAGE->set_heading($activity_report);
$PAGE->set_title($activity_report);
$PAGE->navbar->add($activity_report, $pageurl);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('activity_report', 'local_th_activity_report_by_email'));

if ($CFG->debug != 0) {
    echo get_string('weekly_activity_report', 'local_th_activity_report_by_email');
    echo "</br>";
    th_activity_report_by_email();
}

echo $OUTPUT->footer();
