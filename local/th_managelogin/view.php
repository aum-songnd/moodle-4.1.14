<?php

require_once '../../config.php';
require_once 'lib.php';
require_once 'externallib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_managelogin', $courseid);
}

require_login($COURSE->id);

$pageurl = "/locals/th_managelogin/activate.php";
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('activepagetitle', 'local_th_managelogin'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('activepagetitle', 'local_th_managelogin'));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$editurl = new moodle_url('/locals/th_managelogin/active.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'local_th_managelogin'), $editurl);
$settingsnode->make_active();

echo $OUTPUT->header();

// $result = local_th_managelogin_external::save_userinfo('2', 'eda9e1b72f33d1bd', 1);
// $result = local_th_managelogin_external::save_userinfo('2', '735152f699c89c0a', 1);

echo $OUTPUT->footer();
