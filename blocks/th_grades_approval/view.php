<?php

require_once '../../config.php';
require_once($CFG->dirroot.'/enrol/manual/locallib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/user/renderer.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/blocks/th_grades_approval/lib.php');

global $DB,$OUTPUT,$PAGE, $COURSE, $USER;

if (isset($USER->editing) == 1) {
    $USER->editing = 0;
}

$courseid = required_param('id', PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);
$action = 'switch_plus';
$page = 0;
 
require_login($courseid);
require_capability('block/th_grades_approval:myaddinstance', context_course::instance($COURSE->id));

$pageurl = "/blocks/th_grades_approval/view.php?id=$courseid";
$title = get_string('grade_table_course', 'block_th_grades_approval', $COURSE->fullname);
$PAGE->set_url("/blocks/th_grades_approval/view.php?id=$courseid");
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($SITE->fullname . ': ' . $title);

$editurl = new moodle_url("/blocks/th_grades_approval/view.php?id=$courseid");
$settingsnode = $PAGE->navbar->add($title, $editurl);
$settingsnode->make_active();

// form didn't validate or this is the first display
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
  print_error('invalidcourseid');
}
$gpr = new grade_plugin_return(
  array(
      'type' => 'report',
      'plugin' => 'grader',
      'course' => $course,
      'page' => $page
  )
);
$sortitemid = 0;
$context = context_course::instance($courseid);

if (!isset($USER->gradeediting)) {
    $USER->gradeediting = array();
}

if (has_capability('moodle/grade:edit', $context)) {
    if (!isset($USER->gradeediting[$course->id])) {
        $USER->gradeediting[$course->id] = 0;
    }

    if (($edit == 1) and confirm_sesskey()) {
        $USER->gradeediting[$course->id] = 1;
    } else if (($edit == 0) and confirm_sesskey()) {
        $USER->gradeediting[$course->id] = 0;
    }

    // page params for the turn editting on
    $options = $gpr->get_options();
    $options['sesskey'] = sesskey();

    if ($USER->gradeediting[$course->id]) {
        $options['edit'] = 0;
        $string = get_string('turneditingoff');
    } else {
        $options['edit'] = 1;
        $string = get_string('turneditingon');
    }
} else {
    $USER->gradeediting[$course->id] = 0;
}

$report = new th_grade_report_grader1($courseid, $gpr, $context, $page, $sortitemid);
$report->load_users();
$report->load_final_grades();
$numusers = $report->get_numusers(true, true);
$displayaverages = true;
if ($numusers == 0) {
    $displayaverages = false;
}
$reporthtml = $report->get_grade_table1($displayaverages);

$css = file_get_contents('styles.css');

echo "<style>$css</style>";

echo '<div class="scroll">';
echo $reporthtml;
echo '</div>';
$export_text = get_string('export_grade_report', 'block_th_grades_approval');
echo "<div class='export_gradereport' style='margin-top: 10px'>
        <a class='btn btn-secondary' href='th_export_grade_report.php?id=$courseid'>$export_text</a>
    </div>";

$startdate = $course->startdate;

$secondsDiff = time() - $startdate; // Số giây chênh lệch
$daysDiff = floor($secondsDiff / (60 * 60 * 24));


$active_grades_approval = get_config('block_th_grades_approval', 'active_grades_approval');

if($daysDiff >= $active_grades_approval) {
    $status = $DB->get_record_sql("SELECT * FROM {block_th_grades_approval} WHERE course = $courseid AND timecreated = (SELECT MAX(timecreated) FROM {block_th_grades_approval} WHERE course = $courseid)");

    if(empty($status)){
        $status = 0;
    } else {
        $status = $status->ishidden;
    }

    $list_user_approval = $DB->get_records_sql("SELECT * FROM {block_th_grades_approval} WHERE course = $courseid");

    echo "</br>";
    $cancel_text = get_string('cancel', 'block_th_grades_approval');
    $approve_text = get_string('approve', 'block_th_grades_approval');
    echo "<div>
              <a class='btn_grades_status' href='process.php?id=$courseid&status=0' id='btn_cancel'>$cancel_text</a>
              <a class='btn_grades_status' href='process.php?id=$courseid&status=1' id='btn_approve'>$approve_text</a>
          </div>";
    echo "</br>";

    if(!empty($list_user_approval)){
        $table = new html_table();
        $table->head = array(
            get_string('approver', 'block_th_grades_approval'),
            get_string('status', 'block_th_grades_approval'),
            get_string('approval_time', 'block_th_grades_approval')
        );
        
        $count = 0;
        foreach ($list_user_approval as $key => $user_approval) {

            $count++;
            $row = new html_table_row();

            $teachingid = $user_approval->teachingid;
            $ishidden = $user_approval->ishidden;
            $timecreated = $user_approval->timecreated;
            if($ishidden == 1){
                $text = get_string('approve', 'block_th_grades_approval');
            } else {
                $text = get_string('cancel', 'block_th_grades_approval');
            }

            $teaching = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $teachingid");
            $teachingname = $teaching->firstname . ' ' . $teaching->lastname;
            $teachingname = html_writer::link(new moodle_url('/user/view.php', array('id' => $teachingid)), $teachingname);

            $row->cells[] = new html_table_cell($teachingname);
            $row->cells[] = new html_table_cell($text);
            $row->cells[] = new html_table_cell(date("Y-m-d H:i:s", $timecreated));
            $table->data[] = $row;
        }

        $html = html_writer::table($table);
        $html = $OUTPUT->container($html, 'th_course_status_table');
        echo $html;
        
    }
} else {
    echo get_string('approval_not_yet', 'block_th_grades_approval');
}

echo $OUTPUT->footer();

?>

<script type="text/javascript">

    var id = <?php echo json_encode($courseid); ?>;
    var status = <?php echo json_encode($status); ?>;

    btn_cancel = document.getElementById('btn_cancel');
    btn_approve = document.getElementById('btn_approve');

    if(status == 0) {
        btn_cancel.classList.add('active_cancel');
        btn_approve.classList.add('active_approve');
        btn_cancel.innerHTML = "<?php echo get_string('cancel', 'block_th_grades_approval'); ?> <i class='fa fa-times-circle'></i>";
    } else {
        btn_approve.classList.add('btn_approve');
        btn_cancel.classList.add('btn_cancel');
        btn_approve.innerHTML = "<?php echo get_string('approve', 'block_th_grades_approval'); ?> <i class='fa fa-check-circle'></i>";
    }

    btn_cancel.onclick = function(){
        
        if(status == 1){
            var confirmation = confirm("<?php echo get_string('confirm_cancel_grade_approval', 'block_th_grades_approval'); ?>");
            if (confirmation) {
                btn_cancel.classList.add('active_cancel');
                btn_cancel.classList.remove('btn_cancel');
                btn_approve.classList.add('active_approve');
                btn_approve.classList.remove('btn_approve');
                btn_cancel.innerHTML = "<?php echo get_string('cancel', 'block_th_grades_approval'); ?> <i class='fa fa-times-circle'></i>";
                btn_approve.innerHTML = "<?php echo get_string('approve', 'block_th_grades_approval'); ?>";

            } else {
                return false;
            }
        }     
    }

    btn_approve.onclick = function(){
        
        if(status == 0){
            var confirmation = confirm("<?php echo get_string('confirm_cancel_grade_approval', 'block_th_grades_approval'); ?>");
            if (confirmation) {
                btn_approve.classList.add('btn_approve');
                btn_approve.classList.remove('active_approve');
                btn_cancel.classList.add('btn_cancel');
                btn_cancel.classList.remove('active_cancel');
                btn_cancel.innerHTML = "<?php echo get_string('cancel', 'block_th_grades_approval'); ?>";
                btn_approve.innerHTML = "<?php echo get_string('approve', 'block_th_grades_approval'); ?> <i class='fa fa-check-circle'></i>";

            } else {
                return false;
            }
        }
    }  
</script>