<?php
require_once '../../config.php';
require_once $CFG->dirroot . '/course/lib.php';

global $DB, $COURSE;
$courseid = required_param('id', PARAM_INT);
$status = required_param('status', PARAM_INT);

require_login();

$dataobjects = new stdClass();
$dataobjects->course = $courseid;
$dataobjects->timecreated = time();
$dataobjects->teachingid = $USER->id;
$dataobjects->ishidden = $status;

$DB->insert_record('block_th_grades_approval', $dataobjects, false);

if($status == 0){
	// course_change_visibility($courseid, true);
	$notification = get_string('unapprovegradessuccess', 'block_th_grades_approval');
} else {
	// course_change_visibility($courseid, false);
	$notification = get_string('approvegradessuccess', 'block_th_grades_approval');
}

redirect($CFG->wwwroot . "/blocks/th_grades_approval/view.php?id=$courseid", $notification, null, \core\output\notification::NOTIFY_SUCCESS);
?>