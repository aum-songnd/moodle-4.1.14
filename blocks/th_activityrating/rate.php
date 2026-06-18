<?php

require_once('../../config.php');
global $CFG, $DB, $USER;

$id        = required_param('id', PARAM_INT);                 // Course Module ID.
$rating     = required_param('rating', PARAM_INT);          // User selection.
$cmid = required_param('cmid', PARAM_INT);
if (! $course = $DB->get_record("course", array("id" => $id))) {
    print_error("Course ID not found");
}
require_login($course, false);
$context = context_course::instance($course->id);
if (!$context) {
    print_error('nocontext');
}

require_capability('block/th_activityrating:rate', $context);

if ($form = data_submitted()) {
    if ($DB->count_records('block_th_activityrating', array('course' => $COURSE->id, 'cmid' => $cmid, 'user' => $USER->id))) {
        $test = $DB->get_record('block_th_activityrating', array('course' => $COURSE->id, 'cmid' => $cmid, 'user' => $USER->id));
        $DB->update_record('block_th_activityrating', array('id' => $test->id, 'cmid' => $cmid,'rating' => $rating));
    } else {
        $DB->insert_record( 'block_th_activityrating', ['course' => $COURSE->id,'cmid' => $cmid, 'user' => $USER->id, 'rating' => $rating]);
    }
    redirect($CFG->wwwroot.'/course/view.php?id='.$COURSE->id, 
        get_string('rating_success', 'block_th_activityrating'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}