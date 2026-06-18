<?php

require_once '../../config.php';
require_once $CFG->dirroot .'/mod/thpage/classes/report_form.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$thpageid = required_param('thpageid', PARAM_INT);
$contextid = required_param('contextid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$coursename = optional_param('coursename', '', PARAM_RAW);
$returnurl = optional_param('returnurl', '', PARAM_URL);

// Check for all required variables.
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse');
}

require_login($COURSE->id);
require_capability('mod/thpage:pdf_create', context_module::instance($cmid));
if (empty($returnurl)) {
    $returnurl = new moodle_url('/mod/thpage/report.php');
}

$PAGE->set_url(new moodle_url('/mod/thpage/confirm.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

if ($confirm and confirm_sesskey()) {
    $check = thpage_create_pdf_adhoc_task($courseid, $cmid, $thpageid, $contextid);

    if ($check) {
        redirect($returnurl, get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect($returnurl, get_string('warning'), null, \core\output\notification::NOTIFY_WARNING);
    }
}
// $strheading = $COURSE->fullname;
$PAGE->navbar->add(get_string('pluginname', 'mod_thpage'));
$PAGE->set_title(get_string('pluginname', 'mod_thpage'));
$PAGE->set_heading($COURSE->fullname);
$params = array(
    'courseid' => $courseid, 
    'confirm' => 1, 
    'sesskey' => sesskey(), 
    'cmid' => $cmid, 
    'thpageid' => $thpageid,
    'contextid' => $contextid,
    'coursename' => $coursename,
    'returnurl' => $returnurl
);
$yesurl = new moodle_url('/mod/thpage/confirm.php', $params);
$message = get_string('create_pdf_course', 'mod_thpage', $coursename);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('create_pdf', 'mod_thpage'));
echo $OUTPUT->confirm($message, $yesurl, $returnurl);
echo $OUTPUT->footer();
