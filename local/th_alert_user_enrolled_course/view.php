<?php

require_once '../../config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/externallib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

// Check for all required variables.
$courseid = $COURSE->id;

require_login($courseid);

$pageurl = '/blocks/th_gradereport/view.php';
$PAGE->set_url('/blocks/th_gradereport/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('taskname', 'local_th_alert_user_enrolled_course'));
$PAGE->set_title($SITE->fullname);
$lang = current_language();

echo $OUTPUT->header();

\local_th_alert_user_enrolled_course\task\alert_enrolled::alert();

echo $OUTPUT->footer();
