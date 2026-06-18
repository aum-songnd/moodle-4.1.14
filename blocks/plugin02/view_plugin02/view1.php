<?php

require_once(__DIR__ . '/../../../config.php');

require_once('../classes/form/myform.php');

global $OUTPUT, $CFG ,$PAGE ,$DB;
require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);

if ($courseid == SITEID) {
    $courseid = 0;
}

if ($courseid) {
   
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$urlparams = array('rssid' => 1);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}

$title = get_string('pluginname', 'block_plugin02');
$pagetitle = $title;
$url = new moodle_url("/blocks/plugin02/view_plugin02/view1.php");


$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
$mform = new \block_plugin02\form\myform();


if ($mform->is_cancelled()) {
  
} else if ($fromform = $mform->get_data()) {
    $mform->display();
 
} else {
    $mform->display();
}


echo $OUTPUT->footer();