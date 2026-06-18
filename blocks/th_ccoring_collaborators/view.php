<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_ccoring_collaborators', $courseid);
}

require_login($courseid);
require_capability('block/th_ccoring_collaborators:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_ccoring_collaborators/view.php';
$title = get_string('title', 'block_th_ccoring_collaborators');
$PAGE->set_url('/blocks/th_ccoring_collaborators/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_ccoring_collaborators', 'block_th_ccoring_collaborators'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_ccoring_collaborators'));

$editurl = new moodle_url('/blocks/th_ccoring_collaborators/view.php');
$settingsnode = $PAGE->navbar->add('block_th_ccoring_collaborators', $editurl);
$settingsnode->make_active();

$list_attempts = $DB->get_records_sql("SELECT * FROM {block_th_assign_grading} AS ag WHERE ag.ctvid = $USER->id GROUP BY id ORDER BY ag.id DESC");

function containsWord($string, $word) {
	return stripos($string, $word) !== false;
}

if($list_attempts) {
	$table = new html_table();
	$table->head = array('STT','Tên khóa học','Tên bài kiểm tra','Kiểu bài','Tên học viên' ,'Link bài chấm', 'Trạng thái chấm','Trạng thái phê duyệt');
	$stt = 0;

	foreach ($list_attempts  as $k => $attempt) {
		$data = $DB->get_record_sql("SELECT DISTINCT qa.id,qa.attempt,q.name,c.fullname,u.firstname, u.lastname ,cm.idnumber,ag.confirm_grading
		FROM {quiz_attempts} as qa 
		JOIN {quiz} as q ON qa.quiz = q.id 
		JOIN {course} as c ON q.course = c.id 
		JOIN {user} as u ON qa.userid = u.id 
		JOIN {course_modules} as cm ON q.id = cm.instance AND cm.course = c.id
		JOIN {block_th_assign_grading} as ag ON ag.attemptid = qa.id
		WHERE qa.id = $attempt->attemptid AND cm.idnumber <> ''
		;
		");
		
		if(containsWord($data->idnumber, 'Ess')){
			$name_type = 'Writing';
		}else {
			$name_type = 'Speaking';
		}

		$quizz_name = $data->name . '- lần làm bài thứ ' . $data->attempt;
		
		
		if($attempt->status){
			$button = '<p style="color:red">Đã chấm</p>';
		}else {
			$button = '<p>Chưa chấm</p>';
		}

		if($data->confirm_grading) {
			$link_attempt = '';
			$button_confirm = '<a class="th_success">Đã duyệt</a>';
		}else {
			$link_attempt = '<a class="th_link_attempt" href="'.$CFG->wwwroot.'/blocks/th_ccoring_collaborators/review.php?attempt='.$attempt->attemptid.'&id='.$attempt->id.'">
			Xem bài chấm
			</a>';
			$button_confirm = '<a class="th_wait">Chưa duyệt</a>';
		}

		$student_name = $data->firstname . ' ' . $data->lastname;

		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->fullname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($quizz_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell($name_type);
		$row->cells[] = $cell;
		$cell = new html_table_cell($student_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell($link_attempt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($button);
		$row->cells[] = $cell;
		$cell = new html_table_cell($button_confirm);
		$row->cells[] = $cell;
		$table->data[] = $row;

	}

	$html = html_writer::table($table);
	echo $OUTPUT->header();
	echo "<center><h4>Danh sách các bài kiểm tra cần chấm</h4></center>";
	echo $html;
}else {
	echo $OUTPUT->header();
	echo "<center><h4>Danh sách các bài kiểm tra cần chấm trống!</h4></center>";
}


echo $OUTPUT->footer();

