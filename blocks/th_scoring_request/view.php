<?php

require_once '../../config.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid_param = optional_param('courseid', -1, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_scoring_request', $courseid);
}

require_login($courseid);

$pageurl = '/blocks/th_scoring_request/view.php';
$title = get_string('title', 'block_th_scoring_request');
$PAGE->set_url('/blocks/th_scoring_request/view.php?courseid='.$courseid_param.'');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_scoring_request', 'block_th_scoring_request'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_scoring_request'));

$editurl = new moodle_url('/blocks/th_scoring_request/view.php?courseid='.$courseid_param.'');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_scoring_request'), $editurl);
$settingsnode->make_active();

$get_attempt = $DB->get_records_sql("SELECT row_number() OVER() as stt,q.id, q.name, qa.attempt, qa.id as atempt_review 
FROM {quiz_attempts} as qa 
JOIN {quiz} as q ON qa.quiz = q.id 
JOIN {course_modules} as cm ON q.id = cm.instance
WHERE q.course = $courseid_param AND qa.userid = $USER->id 
AND (cm.idnumber LIKE '%rec%' OR cm.idnumber LIKE '%ess%')
AND qa.state = 'finished' 
ORDER BY id,attempt;");

$table = new html_table();
if($get_attempt){
	$table->head = array('STT', 'Tên bài kiểm tra', 'Thực hiện','Số lượt yêu cầu chấm còn lại');
	$stt = 0;

	$attemp_data = $DB->get_record_sql("SELECT * FROM {th_assign_turns} WHERE userid = $USER->id");

	foreach($get_attempt as $attempt) {
		$quiz_name = $attempt->name;
		$quiz_number = $attempt->attempt;
		$name_attempt = 'Bài kiểm tra: ' . $quiz_name . ' , lần làm bài thứ: ' . $quiz_number;
		$scoring_request = $DB->get_record_sql("SELECT * FROM {block_th_scoring_request} AS sr 
		WHERE sr.quizid = $attempt->id AND sr.userid = $USER->id AND sr.attemptid = $quiz_number AND sr.atempt_review = $attempt->atempt_review");
		$list_attempts = $DB->get_record_sql("SELECT * FROM {block_th_assign_grading} WHERE attemptid = $attempt->atempt_review");
	
		$button = '<a class="th_link_attempt" style="margin-right:5px" href="'.$CFG->wwwroot.'/mod/quiz/review.php?attempt='.$attempt->atempt_review.'">Xem lại</a>';
	
		if (!empty($attemp_data)) {
			$used_turns = $attemp_data->numberturns - $attemp_data->used;
			
			if ($used_turns > 0) {
				if (empty($scoring_request)) {
					$button .= '<a class="th_wait" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/edit.php?quizid='.$attempt->id.'&course='.$courseid_param.'&attempt='.$attempt->attempt.'&atempt_review='.$attempt->atempt_review.'&redirect=2">Yêu cầu chấm chữa</a>';
				} else {
					if (!empty($list_attempts) && $list_attempts->confirm_grading == 1) {
						$button .= '<a class="th_success">Đã chấm xong</a>';
					} else if (!empty($list_attempts)) {
						$button .= '<a class="th_wait_grade">Đang chấm bài</a>';
					} else {
						$button .= '<a class="th_cancel" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/edit.php?atempt_review='.$attempt->atempt_review.'&course='.$courseid_param.'&delete=1&redirect=2">Hủy yêu cầu</a>';
					}
				}
			} else if (!empty($attemp_data) && $used_turns <= 0) {
				$used_turns = 0;
				if (empty($scoring_request)) {
					$button .= '';
				} else {
					if (!empty($list_attempts) && $list_attempts->confirm_grading == 1) {
						$button .= '<a class="th_success">Đã chấm xong</a>';
					} else if (!empty($list_attempts)) {
						$button .= '<a class="th_wait_grade">Đang chấm bài</a>';
					} else {
						$button .= '<a class="th_cancel" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/edit.php?atempt_review='.$attempt->atempt_review.'&course='.$COURSE->id.'&delete=1&redirect=2">Hủy yêu cầu</a>';
					}
				}
			}
		} 
	
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($name_attempt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($button);
		$row->cells[] = $cell;
		$cell = new html_table_cell($used_turns);
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	
	
}
	
$html = html_writer::table($table);
echo $OUTPUT->header();
echo "<center><h4>Danh sách các bài kiểm tra đã nộp</h4></center>";
echo $html;
echo $OUTPUT->footer();
