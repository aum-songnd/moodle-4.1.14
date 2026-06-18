<?php

require_once '../../config.php';
require_once 'th_report_grades_approval_form.php';

global $DB,$OUTPUT,$PAGE, $COURSE;

$courseid = $COURSE->id;

require_login($courseid);
require_capability('block/th_grades_approval:myaddinstance', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_grades_approval/th_report_grades_approval.php';
$title = get_string('grade_approval_report', 'block_th_grades_approval');
$PAGE->set_url('/blocks/th_grades_approval/th_report_grades_approval.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($SITE->fullname . ': ' . $title);

$editurl = new moodle_url('/blocks/th_grades_approval/th_report_grades_approval.php');
$settingsnode = $PAGE->navbar->add($title, $editurl);
$settingsnode->make_active();


$th_report_grades_approval = new th_report_grades_approval_form();

if ($th_report_grades_approval->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    $courseurl = new moodle_url('/my');
    redirect($courseurl);
} else if ($fromform = $th_report_grades_approval->get_data()) {

    $startdate = $fromform->startdate;
    $active_grades_approval = get_config('block_th_grades_approval', 'active_grades_approval');

    $list_course_approval = $DB->get_records_sql("SELECT {course}.id, {course}.fullname, {block_th_grades_approval}.ishidden , {block_th_grades_approval}.teachingid
        FROM {course} 
        LEFT JOIN {block_th_grades_approval} ON {course}.id = {block_th_grades_approval}.course
        WHERE {course}.id <> 1 AND {course}.startdate = $startdate
        GROUP BY {course}.id;");

    $table = new html_table();
    $table->head = array(
        get_string('stt', 'block_th_grades_approval'),
        get_string('course_name', 'block_th_grades_approval'),
        get_string('approver', 'block_th_grades_approval'),
        get_string('status', 'block_th_grades_approval')
    );
    
    $count = 0;

    foreach($list_course_approval as $course_approval) {
        
        $count++;
        $row = new html_table_row();

        $course_id = $course_approval->id;
        $course_name = $course_approval->fullname;
        $course_name = html_writer::link(new moodle_url('/blocks/th_grades_approval/view.php', array('id' => $course_id)), $course_name);
        $teachingid = $course_approval->teachingid;
        $ishidden = $course_approval->ishidden;

        if(isset($ishidden)){
            
            $status = $DB->get_record_sql("SELECT * FROM {block_th_grades_approval} WHERE course = $course_id AND timecreated = (SELECT MAX(timecreated) FROM {block_th_grades_approval} WHERE course = $course_id)");
            
            if($status->ishidden == 1){
                $text = get_string('approved', 'block_th_grades_approval');
                $text = html_writer::tag('span', $text, array('class' => 'badge badge-success'));
                $teaching = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $teachingid");
                $teachingname = $teaching->firstname . ' ' . $teaching->lastname;
                $teachingname = html_writer::link(new moodle_url('/user/view.php', array('id' => $teachingid)), $teachingname);
            } else {
                $text = get_string('not_approved', 'block_th_grades_approval');
                $text = html_writer::tag('span', $text, array('class' => 'badge badge-warning'));
                $teachingname = get_string('na', 'block_th_grades_approval');
            }
        } else {
            $text = get_string('not_approved', 'block_th_grades_approval');
            $text = html_writer::tag('span', $text, array('class' => 'badge badge-warning'));
            $teachingname = get_string('na', 'block_th_grades_approval');
        }

        $row->cells[] = new html_table_cell($count);
        $row->cells[] = new html_table_cell($course_name); 
        $row->cells[] = new html_table_cell($teachingname);
        $row->cells[] = new html_table_cell($text);

        $table->data[] = $row;
        
    }

    $table->attributes = array('class' => 'th_grades_approval_table', 'border' => 1);
    $html = html_writer::table($table);
    echo $OUTPUT->header();
    $format_startdate = date('d/m/Y', $startdate);
    $params = ['title' => $title, 'date' => $format_startdate];
    echo $OUTPUT->heading(get_string('course_opening_title', 'block_th_grades_approval', $params));
    echo '</br>';
    if(empty($list_course_approval)){
        $course_not_found = get_string('course_not_found', 'block_th_grades_approval');
        echo "<h4 style='color: red'>$course_not_found $format_startdate</h4>";
        $return = html_writer::link(new moodle_url('/blocks/th_grades_approval/th_report_grades_approval.php', array()), get_string('back', 'block_th_grades_approval'));
        echo "$return";
    } else {
        echo $html;
        $return = html_writer::link(new moodle_url('/blocks/th_grades_approval/th_report_grades_approval.php', array()), get_string('back', 'block_th_grades_approval'));
        echo "$return";
    }
    
    $lang = current_language();
    echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_grades_approval_table', 'th_grades_approval_table', $lang));

    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    $th_report_grades_approval->display();
    echo $OUTPUT->footer();
}