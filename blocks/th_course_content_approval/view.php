<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/course/renderer.php';
require_once $CFG->dirroot . '/course/format/renderer.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

if (isset($USER->editing) == 1) {
    $USER->editing = 0;
}

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
    echo $OUTPUT->footer();
}

$pageurl = "/blocks/th_course_content_approval/view.php?id=$id";
$title = get_string('title', 'block_th_course_content_approval');
$PAGE->set_url("/blocks/th_course_content_approval/view.php?id=$id");
$PAGE->set_pagelayout(get_string('standard', 'block_th_course_content_approval'));
$PAGE->set_heading(get_string('th_course_content_approval', 'block_th_course_content_approval'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_course_content_approval'));

$editurl = new moodle_url("/blocks/th_course_content_approval/view.php?id=$id");
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_course_content_approval'), $editurl);
$settingsnode->make_active();

echo $OUTPUT->header();

if($id) {

	$courses = $DB->get_record('course', array('id' => $id));

	$modinfo = get_fast_modinfo($courses);
	$course = course_get_format($courses)->get_course();

	$context = context_course::instance($course->id);

	$numsections = course_get_format($course)->get_last_section_number();
	$completioninfo = new completion_info($course);

	$renderer = $PAGE->get_renderer('core', 'course');

	echo '<div class="btn_history_content_approval">
	        <a href="'.$CFG->wwwroot.'/blocks/th_course_content_approval/th_log_course_content_approval.php?id='.$id.'"><button id="button_upload">'
	        . get_string('approval_history', 'block_th_course_content_approval') . '</button></a>
    	</div>';

	echo '<div class="th_course_content_approval">
			<ul>';
	foreach ($modinfo->get_section_info_all() as $section => $thissection) {
		if ($thissection->summary or !empty($modinfo->sections[$section])) {

			$section_id = $thissection->id;
			$data_section = $DB->get_record('course_sections', array('id' => $section_id));

			if($section){
				if($data_section->name){
					$name_section = $data_section->name;
				} else {
					//$name_section = 'Chủ đề ' . $data_section->section;
					$name_section = get_string('section_name', 'block_th_course_content_approval', $data_section->section);
				}

				echo "<h3 class='th_name_section'>$name_section</h3>";
			}
			
			if (!empty($modinfo->sections[$section])) {
				foreach ($modinfo->sections[$section] as $modnumber) {
					$mod = $modinfo->cms[$modnumber];
					$mod_type = $mod->modname;

					// $instancename = $mod->get_formatted_name();

					if($mod_type == 'resource' || $mod_type == 'scorm' || $mod_type == 'quiz'){

						if ($modulehtml = $renderer->course_section_cm_list_item($course,
							$completioninfo, $mod, 0, array())) {

							$status = $DB->get_record_sql("SELECT * FROM {th_course_content_approval} WHERE cmid = $modnumber AND timecreated = (SELECT MAX(timecreated) FROM {th_course_content_approval} WHERE cmid = $modnumber)");

							if(empty($status)){
						        $status = 0;
						    } else {
						        $status = $status->ishidden;
						    }

							if($status){
								$approve = get_string('approve', 'block_th_course_content_approval');
								$cancel = get_string('cancel', 'block_th_course_content_approval');
								$buttonstring = "<div>
									              <a class='btn_content_approval_status btn_content_cancel' href='process.php?id=$id&cmid=$modnumber&status=0' id='btn_cancel'>$cancel</a>
									              <a class='btn_content_approval_status btn_content_approve disabled-link' href='process.php?id=$id&cmid=$modnumber&status=1' id='btn_approve'>$approve</a>
									          </div>";
							} else {
								$approve = get_string('approve', 'block_th_course_content_approval');
								$cancel = get_string('cancel', 'block_th_course_content_approval');
								$buttonstring = "<div>
									              <a class='btn_content_approval_status active_content_cancel disabled-link' href='process.php?id=$id&cmid=$modnumber&status=0' id='btn_cancel'>$cancel</a>
									              <a class='btn_content_approval_status active_content_approve' href='process.php?id=$id&cmid=$modnumber&status=1' id='btn_approve'>$approve</a>
									          </div>";
							}

							echo '<div class="th_approval_content">' . $modulehtml . $buttonstring .'</div>';
						}
					}
				}
			}
		}
	}
	echo '</ul>
	</div>';

	echo '<style>
		.th_course_content_approval .actions{
			display: none;
		}

		.th_course_content_approval ul{
			list-style: none;
		}

		.th_course_content_approval ul li{
			margin-bottom: 20px;
			max-width: 60%;
		}

		.th_course_content_approval .availabilityinfo{
			display: none;
		}

		.th_course_content_approval a{
			float: right;
		}

		.th_course_content_approval .aalink{
			float: left;
		}

		.th_approval_content{
			display: flex;
			justify-content: space-between;
		}

		.th_name_section{
			padding: 8px 0;
		    background: #f4f4f4;
		    color: #666;
		    border-color: #EEE #EEE #EEE #007180;
		    border-style: solid;
		    border-width: 1px 1px 1px 3px;
		    padding-left: 4px;
		    margin-top: 0;
		    font-size: 1.385rem;
		    line-height: 1.54rem;
		}

		.btn_content_approval_status {
		    padding: 0px 5px;
		}

		.th_approval_content .no-overflow{
			overflow: visible;
		}

		.btn_content_cancel {
		    background: #f7f7f7;
		    background-image: linear-gradient(to bottom,#f7f7f7,#f2f2f2);
		    border-radius: 0;
		    border: solid #dbdbdb 1px;
		    color: #524e52 !important;
		}

		.btn_content_cancel:hover {
		    background: #ebe8eb;
		    background-image: linear-gradient(to bottom,#ebe8eb,#e3e0e3);
		    border-radius: 0;
		    border: solid #dbdbdb 1px;
		    color: #fff !important;
		}

		.btn_content_approve {
		    background: #80cc76;
		    background-image: linear-gradient(to bottom,#80cc76,#71b569);
		    border: 1px solid #80cc76;
		    color: #fff !important;
		    cursor: default;
		    outline: 0;
		    position: relative;
		}

		.btn_content_approve:hover {
		    background-color: #77ae29!important;
		    background-image: linear-gradient(to bottom,#77ae29,#77ae29);
		    color: #fff !important;
		  
		}

		.active_content_cancel {
		    background: #f28585;
		    background-image: linear-gradient(to bottom,#f28585,#f08484);
		    border: 1px solid #f28585;
		    color: #fff !important;
		    cursor: default;
		    outline: 0;
		}

		.active_content_cancel:hover {
		    background-image: linear-gradient(to bottom,#f28585,#f08484);
		    border: 1px solid #f28585;
		    color: #fff !important;
		}

		.active_content_approve {
		    background: #ebe8eb;
		    background-image: linear-gradient(to bottom,#ebe8eb,#e3e0e3);
		    border-radius: 0;
		    border: solid #dbdbdb 1px;
		    color: #524e52 !important;
		    position: relative;
		}

		.active_content_approve:hover {
		    background: #ebe8eb;
		    background-image: linear-gradient(to bottom,#ebe8eb,#ebe8eb);
		    border-radius: 0;
		    border: solid #dbdbdb 1px;
		    color: #524e52 !important;
		}

		.disabled-link {
		  pointer-events: none;
		}

		.btn_history_content_approval{
			display: flex;
		    justify-content: flex-end;
		    margin-bottom: 30px;
		}

	</style>';


} else {
	echo '<h3>ID Khóa học không hợp lệ</h3>';
}

echo $OUTPUT->footer();
