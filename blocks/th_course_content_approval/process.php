<?php
require_once '../../config.php';
require_once $CFG->dirroot . '/course/lib.php';

global $DB, $COURSE;
$cmid = required_param('cmid', PARAM_INT);
$courseid = required_param('id', PARAM_INT);
$status = required_param('status', PARAM_INT);

require_login();

$dataobjects = new stdClass();
$dataobjects->cmid = $cmid;
$dataobjects->course_id = $courseid;
$dataobjects->timecreated = time();
$dataobjects->teachingid = $USER->id;
$dataobjects->ishidden = $status;

$DB->insert_record('th_course_content_approval', $dataobjects, false);

$courses = $DB->get_record('course', array('id' => $courseid));
$modinfo = get_fast_modinfo($courses);
$mod = $modinfo->cms[$cmid];
$mod_name = $mod->get_formatted_name();
$mod_type = $mod->modname;

if($status == 0){
	// course_change_visibility($courseid, true);
	//$notification = 'Hủy phê duyệt học liệu <strong>'.$mod_name.'</strong> thành công';
	$notification = get_string('cancel_approval_success', 'block_th_course_content_approval', $mod_name);
	redirect($CFG->wwwroot . "/blocks/th_course_content_approval/view.php?id=$courseid", $notification, null, \core\output\notification::NOTIFY_WARNING);
} else {
	// course_change_visibility($courseid, false);
	//$notification = 'Phê duyệt học liệu <strong>'.$mod_name.'</strong> thành công';
	$notification = get_string('approve_material_success', 'block_th_course_content_approval', $mod_name);
	redirect($CFG->wwwroot . "/blocks/th_course_content_approval/view.php?id=$courseid", $notification, null, \core\output\notification::NOTIFY_SUCCESS);
}


?>