<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_course_content_approval', $courseid);
}

require_login($courseid);

$capabilities = array(
    'block/th_course_content_approval:view'
);
        
$context = context_course::instance($id);
if (!has_any_capability($capabilities, $context)) {
    echo $OUTPUT->header();
    echo get_string('no_permission', 'block_th_course_content_approval');
    exit;
    echo $OUTPUT->footer();
}

$pageurl = "/blocks/th_course_content_approval/th_log_course_content_approval.php?id=$id";
$title = get_string('title', 'block_th_course_content_approval');
$PAGE->set_url("/blocks/th_course_content_approval/th_log_course_content_approval.php?id=$id");
$PAGE->set_pagelayout(get_string('standard', 'block_th_course_content_approval'));
$PAGE->set_heading(get_string('approval_history', 'block_th_course_content_approval'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('approval_history', 'block_th_course_content_approval'));

$editurl = new moodle_url("/blocks/th_course_content_approval/th_log_course_content_approval.php?id=$id");
$settingsnode = $PAGE->navbar->add(get_string('approval_history', 'block_th_course_content_approval'), $editurl);
$settingsnode->make_active();

echo $OUTPUT->header();

if($id) {

	$list_approval = $DB->get_records_sql("SELECT * FROM {th_course_content_approval} WHERE course_id = $id ORDER BY timecreated DESC");

	if($list_approval){

		$table = new html_table();
	    $table->head = array(
			get_string('table_stt', 'block_th_course_content_approval'),
			get_string('table_course_name', 'block_th_course_content_approval'),
			get_string('table_resource_name', 'block_th_course_content_approval'),
			get_string('table_approver', 'block_th_course_content_approval'),
			get_string('table_status', 'block_th_course_content_approval'),
			get_string('table_approval_time', 'block_th_course_content_approval')
		);
		
	    $count = 0;

		foreach($list_approval as $approval){
			$cmid = $approval->cmid;
			$course_id = $approval->course_id;
			$courses = $DB->get_record('course', array('id' => $course_id));

			if($courses){

				$modinfo = get_fast_modinfo($courses);
				$mod = $modinfo->cms[$cmid];
				$mod_name = $mod->get_formatted_name();
				$mod_type = $mod->modname;

				$course_name = $courses->fullname;
        		$course_name = html_writer::link(new moodle_url('/blocks/th_course_content_approval/view.php', array('id' => $course_id)), $course_name);

				$teaching = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $approval->teachingid");

				if($teaching && $mod){
					$count++;
					$teachingname = $teaching->firstname . ' ' . $teaching->lastname;
                	$teachingname = html_writer::link(new moodle_url('/user/view.php', array('id' => $approval->teachingid)), $teachingname);

                	if($approval->ishidden == 1){
		                $text = get_string('approve', 'block_th_course_content_approval');
		                $text = html_writer::tag('span', $text, array('class' => 'badge badge-success'));
		            } else {
		                $text = get_string('unapprove', 'block_th_course_content_approval');
		                $text = html_writer::tag('span', $text, array('class' => 'badge badge-warning'));
		            }

		            $mod_url = html_writer::link(new moodle_url("/mod/$mod_type/view.php", array('id' => $cmid)), $mod_name);

                	$row = new html_table_row();
                	$row->cells[] = new html_table_cell($count);
			        $row->cells[] = new html_table_cell($course_name);
			        $row->cells[] = new html_table_cell("<strong>$mod_url</strong>");
			        $row->cells[] = new html_table_cell($teachingname);
			        $row->cells[] = new html_table_cell($text);
			        $row->cells[] = new html_table_cell(date('H:i:s d-m-Y', $approval->timecreated));
			        $table->data[] = $row;
				}
			}
		}

		$table->attributes = array('class' => 'th_course_content_approval_table', 'border' => 1);
		$table->width = '100%';
    	$html = html_writer::table($table);
        $approval_history = get_string('approval_history', 'block_th_course_content_approval');
    	echo $OUTPUT->heading("<center>
    							<p><h1>$approval_history</h1></p> 
    							<p>$course_name</p>
    							</center>"
    						);
    	echo "</br>";
    	echo $html;
    	$lang = current_language();
	    echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
	    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_course_content_approval_table', 'th_course_content_approval_table', $lang));

		
	} else {
		$approval_history = get_string('approval_history', 'block_th_course_content_approval');
		echo $OUTPUT->heading("<center><h1>$approval_history</h1></center>");
		echo "</br>";
		$empty_approval_history = get_string('empty_approval_history', 'block_th_course_content_approval'); 
		echo "<h3>$empty_approval_history</h3>";
	}

} else {
	$approval_history = get_string('approval_history', 'block_th_course_content_approval');
	echo $OUTPUT->heading("<center><h1>$approval_history</h1></center>");
	echo "</br>";
	$invalid_course_id = get_string('invalid_course_id', 'block_th_course_content_approval');
	echo "<h3>$invalid_course_id</h3>";
}

echo $OUTPUT->footer();

?>