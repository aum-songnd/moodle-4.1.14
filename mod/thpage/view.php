<?php

require('../../config.php');
require_once($CFG->dirroot.'/mod/thpage/lib.php');
require_once($CFG->dirroot.'/mod/thpage/locallib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->dirroot.'/mod/thpage/thpagepdf.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // thpage instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$thpage = $DB->get_record('thpage', array('id'=>$p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('thpage', $thpage->id, $thpage->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('thpage', $id)) {
        print_error('invalidcoursemodule');
    }
    $thpage = $DB->get_record('thpage', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/thpage:view', $context);

// Completion and trigger events.
thpage_view($thpage, $course, $cm, $context);

$content = $thpage->content = thpage_create_content($course, $thpage, $cm);
$filename = get_string('dcct', 'mod_thpage') . '.pdf';
$fs = get_file_storage();
$fileinfo = [
    'contextid' => $context->id,
    'component' => 'mod_thpage',
    'filearea'  => 'pdf',
    'itemid'    => 0,
    'filepath'  => '/',
    'filename'  => $filename,
];

$file = $fs->get_file(
    $fileinfo['contextid'],
    $fileinfo['component'],
    $fileinfo['filearea'],
    $fileinfo['itemid'],
    $fileinfo['filepath'],
    $fileinfo['filename']
);
$url = '';
if ($file) {
    $url = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename(),
        true                 
    );
}
$url_download = html_writer::link($url, get_string('download'));
$content = $url_download . $content . $url_download;
$PAGE->set_url('/mod/thpage/view.php', array('id' => $cm->id));

$options = empty($thpage->displayoptions) ? array() : unserialize($thpage->displayoptions);

if ($inpopup and $thpage->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_thpagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$thpage->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$thpage->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($thpage);
}
echo $OUTPUT->header();
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(format_string($thpage->name), 2);
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($thpage->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'thpageintro');
        echo format_module_intro('thpage', $thpage, $cm->id);
        echo $OUTPUT->box_end();
    }
}
if (has_capability('mod/thpage:pdf_create', $context)) {
    $output = html_writer::start_tag('form',
        array('action' => new moodle_url('/mod/thpage/confirm.php',
            array('cmid' => $cm->id,
                'thpageid' => $thpage->id, 
                'courseid' => $cm->course, 
                'contextid' => $context->id,
                'coursename' => $course->fullname,
                'returnurl' => new moodle_url('/mod/thpage/view.php', array('id' => $cm->id))
            )
        ), 'method' => 'post')
    );
    $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'th_submit',
        'value' => get_string('create_pdf', 'mod_thpage'), 'class' => 'btn btn-primary'));
    $output .= html_writer::end_tag('form');
    echo $output;
}

$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;

$content = format_text($content, $thpage->contentformat, $formatoptions);
echo $OUTPUT->box($content, "generalbox center clearfix");

if (!isset($options['printlastmodified']) || !empty($options['printlastmodified'])) {
    $strlastmodified = get_string("lastmodified");
    echo html_writer::div("$strlastmodified: " . userdate($thpage->timemodified), 'modified');
}

echo $OUTPUT->footer();
