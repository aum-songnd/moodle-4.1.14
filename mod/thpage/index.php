<?php

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

// Trigger instances list viewed event.
$event = \mod_thpage\event\course_module_instance_list_viewed::create(array('context' => context_course::instance($course->id)));
$event->add_record_snapshot('course', $course);
$event->trigger();

$strthpage         = get_string('modulename', 'thpage');
$strthpages        = get_string('modulenameplural', 'thpage');
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/thpage/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strthpages);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strthpages);
echo $OUTPUT->header();
echo $OUTPUT->heading($strthpages);
if (!$thpages = get_all_instances_in_course('thpage', $course)) {
    notice(get_string('thereareno', 'moodle', $strthpages), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($thpages as $thpage) {
    $cm = $modinfo->cms[$thpage->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($thpage->section !== $currentsection) {
            if ($thpage->section) {
                $printsection = get_section_name($course, $thpage->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $thpage->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($thpage->timemodified)."</span>";
    }

    $class = $thpage->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($thpage->name)."</a>",
        format_module_intro('thpage', $thpage, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
