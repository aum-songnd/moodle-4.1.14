<?php

require_once '../../config.php';
require_once 'lib.php';
require_once 'externallib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_mobile_service', $courseid);
}

require_login($COURSE->id);

$pageurl = "/local/th_mobile_service/activate.php?id=$courseid";
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('activepagetitle', 'local_th_mobile_service'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('activepagetitle', 'local_th_mobile_service'));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$editurl = new moodle_url('/locals/th_mobile_service/active.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'local_th_mobile_service'), $editurl);
$settingsnode->make_active();

echo $OUTPUT->header();

$result = local_th_mobile_service_external::xet_dkdt('PSST113', [
	"20-2-7380107-1684",
	"20-2-7340301-1686",
	"20-2-7480201-1691",
	"20-2-7220201-1404",
	"20-2-7480201-1365",
	"20-2-7340301-1628",
	"20-2-7380107-1615",
	"20-2-7380107-1616",
]);
print_object($result);

echo $OUTPUT->footer();
