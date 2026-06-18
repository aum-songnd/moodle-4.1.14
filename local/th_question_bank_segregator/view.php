<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_question_bank_segregator/lib.php';
require_once $CFG->dirroot . '/lib/questionlib.php';
require_once $CFG->dirroot . '/question/editlib.php';
require_once($CFG->dirroot."/question/category_class.php");
require_once 'th_question_bank_segregator_form.php';

global $DB, $CFG, $COURSE, $USER;

$courseid = optional_param('courseid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($courseid);
require_capability('local/th_question_bank_segregator:view', context_course::instance($courseid));

$PAGE->set_url(new moodle_url("/local/th_question_bank_segregator/view.php?courseid=$courseid"));
$context = context_course::instance($courseid);

$title = get_string('title', 'local_th_question_bank_segregator');
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$th_question_bank_segregator = new th_question_bank_segregator_form();
$th_question_bank_segregator->set_data(array('courseid' => $courseid));
if ($th_question_bank_segregator->is_cancelled()) {
    // Cancelled forms redirect to the course main page.

    $courseurl = new moodle_url('/my');
    redirect($courseurl);
} 
else if ($fromform = $th_question_bank_segregator->get_data()) {

    require_sesskey();
    
    $courseid = $fromform->courseid;
    $ratio = $fromform->ratio;

    $categoriesTree_obj = $DB->get_record('th_course_category_tree', array('course_id' => $courseid));
    if($categoriesTree_obj){
        redirect($CFG->wwwroot . "/local/th_question_bank_segregator/process.php?courseid=$courseid&ratio=$ratio");
    } else {
        redirect(
            $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
            get_string('please_backup_before_segregation', 'local_th_question_bank_segregator'),
            null,
            \core\output\notification::NOTIFY_WARNING
        );        
    }
    
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();

    $renderer = $PAGE->get_renderer('core_question', 'bank');
    echo $renderer->extra_horizontal_navigation();

    echo $OUTPUT->heading($title);

    echo "
        <div>
            <a href='$CFG->wwwroot/local/th_question_bank_segregator/th_save_category_tree.php?courseid=$courseid'><button>Backup NHCH</button></a>
            <a href='$CFG->wwwroot/local/th_question_bank_segregator/th_render_category_tree.php?courseid=$courseid'><button>Restore NHCH</button></a>
        </div>";
    
    $categoriesTree_obj = $DB->get_record('th_course_category_tree', array('course_id' => $courseid));
    if ($categoriesTree_obj) {
        $timecreated = date('H:i:s d-m-Y', $categoriesTree_obj->timecreated);
        echo "<div>
                <span style='font-style: italic;'>" . get_string('latest_backup', 'local_th_question_bank_segregator', $timecreated) . "</span>
            </div>";
    } else {
        echo "<div>
                <span style='font-style: italic;'>" . get_string('no_backup_found', 'local_th_question_bank_segregator') . "</span>
            </div>";
    }    

    $th_question_bank_segregator->display();
    echo $OUTPUT->footer();
}

