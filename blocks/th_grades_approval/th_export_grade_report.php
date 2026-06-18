<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/grade/export/lib.php';
require_once $CFG->dirroot . '/grade/export/xls/grade_export_xls.php';

global $DB, $CFG;

$context = context_system::instance();

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid));

$groupid     = groups_get_course_group($course, true);
$actionurl   = new moodle_url('/grade/export/xls/export.php');
$formoptions = array(
    'publishing'           => true,
    'simpleui'             => true,
    'multipledisplaytypes' => true,
);

$switch = grade_get_setting($course->id, 'aggregationposition', $CFG->grade_aggregationposition);

// Grab the grade_seq for this course
$gseq = new grade_seq($course->id, $switch);

if ($grade_items = $gseq->items) {
    $needs_multiselect = false;
    $canviewhidden     = has_capability('moodle/grade:viewhidden', context_course::instance($course->id));

    $formdata                                 = new stdClass();
    $formdata->id                             = 2;
    $formdata->mform_isexpanded_id_gradeitems = 1;
    $formdata->checkbox_controller1           = 1;
    $formdata->mform_isexpanded_id_options    = 0;
    $formdata->export_feedback                = 0;
    $formdata->export_onlyactive              = 1;
    $formdata->decimals                       = 2;
    $formdata->submitbutton                   = 'Download';
    $formdata->display                        = array(
        'real'       => 1,
        'percentage' => 0,
        'letter'     => 0,
    );

    foreach ($grade_items as $key => $grade_item) {
        $formdata->itemids[$key] = 1;
    }
}

$export = new grade_export_xls($course, $groupid, $formdata);

// If the gradepublishing is enabled and user key is selected print the grade publishing link.
if (!empty($CFG->gradepublishing) && !empty($key)) {
    groups_print_course_menu($course, 'index.php?id=' . $id);
    echo $export->get_grade_publishing_url();
    echo $OUTPUT->footer();
} else {
    $event = \gradeexport_xls\event\grade_exported::create(array('context' => $context));
    $event->trigger();
    $export->print_grades();
}
?>