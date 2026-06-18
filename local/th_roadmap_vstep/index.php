<?php

require_once '../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';
require_once 'lib.php';

$id = required_param('id', PARAM_INT); // course id

$PAGE->set_url('/local/th_roadmap_vstep/index.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);
$context = context_course::instance($id);

// require_capability('moodle/grade:export', $context);
// require_capability('gradeexport/xls:view', $context);
require_capability('local/th_roadmap_vstep:export', $context);

$actionbar = new \core_grades\output\export_action_bar($context, null, 'xls');
th_roadmap_vstep_print_grade_page_head($COURSE->id, 'export', 'xls',
    get_string('exportto', 'grades') . ' ' . get_string('pluginname', 'gradeexport_xls'),
    false, false, true, null, null, null, $actionbar);
export_verify_grades($COURSE->id);

if (!empty($CFG->gradepublishing)) {
    $CFG->gradepublishing = has_capability('gradeexport/xls:publish', $context);
}

$actionurl = new moodle_url('/local/th_roadmap_vstep/export.php');
$formoptions = array(
    'publishing' => true,
    'simpleui' => true,
    'multipledisplaytypes' => true
);

$mform = new grade_export_form($actionurl, $formoptions);

$groupmode    = groups_get_course_groupmode($course);   // Groups are being used
$currentgroup = groups_get_course_group($course, true);
if ($groupmode == SEPARATEGROUPS and !$currentgroup /*and !has_capability('moodle/site:accessallgroups', $context)*/) {
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    die;
}

groups_print_course_menu($course, 'index.php?id='.$id);
echo '<div class="clearer"></div>';

$mform->display();

echo $OUTPUT->footer();
