<?php

class block_th_scoring_request extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_scoring_request');
	}

	public function get_content() {
		global $CFG,$DB, $COURSE, $USER;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass;
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		
		
		$get_attempt = $DB->get_records_sql("SELECT row_number() OVER() as stt,q.id, q.name, qa.attempt, qa.id as atempt_review 
		FROM {quiz_attempts} as qa 
		JOIN {quiz} as q ON qa.quiz = q.id 
		JOIN {course_modules} as cm ON q.id = cm.instance
		WHERE q.course = $COURSE->id AND qa.userid = $USER->id 
		AND qa.state = 'finished' 
		AND (cm.idnumber LIKE '%rec%' OR cm.idnumber LIKE '%ess%')
		ORDER BY id,attempt;");

		if($get_attempt){
			$this->content->footer = '<p class="title">Danh sách các bài kiểm tra đã nộp:</p>';
			$attemptshow = 0;
			$attemp_data = $DB->get_record_sql("SELECT * FROM {th_assign_turns} WHERE userid = $USER->id");
			$used_turns = $attemp_data->numberturns - $attemp_data->used;

			foreach($get_attempt as $attempt) {
				$attemptshow++;
				if($attemptshow > 5) {
					break;
				}
				$quiz_name = $attempt->name;
				$quiz_number = $attempt->attempt;
				$scoring_request = $DB->get_record_sql("SELECT * FROM {block_th_scoring_request} AS sr 
				WHERE sr.quizid = $attempt->id AND sr.userid = $USER->id AND sr.attemptid = $quiz_number AND sr.atempt_review = $attempt->atempt_review");
				$list_attempts = $DB->get_record_sql("SELECT * FROM {block_th_assign_grading} WHERE attemptid = $attempt->atempt_review");

				if (!empty($attemp_data) && $used_turns > 0) {
					if (empty($scoring_request)) {
						$button = '<a class="th_wait" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/edit.php?quizid='.$attempt->id.'&course='.$COURSE->id.'&attempt='.$attempt->attempt.'&atempt_review='.$attempt->atempt_review.'&redirect=1">Yêu cầu chấm chữa</a>';
					} else {
						if (!empty($list_attempts) && $list_attempts->confirm_grading == 1) {
							$button = '<a class="th_success">Đã chấm xong</a>';
						} else if (!empty($list_attempts)) {
							$button = '<a class="th_wait_grade">Đang chấm bài</a>';
						} else {
							$button = '<a class="th_cancel" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/edit.php?atempt_review='.$attempt->atempt_review.'&course='.$COURSE->id.'&delete=1&redirect=1">Hủy yêu cầu</a>';
						}
					}
				} else if (!empty($attemp_data) && $used_turns <= 0) {
					if (empty($scoring_request)) {
						$button = '';
					} else {
						if (!empty($list_attempts) && $list_attempts->confirm_grading == 1) {
							$button = '<a class="th_success">Đã chấm xong</a>';
						} else if (!empty($list_attempts)) {
							$button = '<a class="th_wait_grade">Đang chấm bài</a>';
						} else {
							$button = '<a class="th_cancel" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/edit.php?atempt_review='.$attempt->atempt_review.'&course='.$COURSE->id.'&delete=1&redirect=1">Hủy yêu cầu</a>';
						}
					}
				}else {
					$button = '';
				}

				$this->content->footer .= '
				<div class="th_quiz_attempt">
					<span>Bài kiểm tra: '.$quiz_name.' , lần làm bài thứ: '.$quiz_number.' </span>
					<div>
						<a class="th_link_attempt" href="'.$CFG->wwwroot.'/mod/quiz/review.php?attempt='.$attempt->atempt_review.'">Xem lại</a>
						'.$button.'
					</div>
				</div>';
			}


			if(!empty($attemp_data)){
				$this->content->footer .= '<span class="th_number">Số lượt yêu cầu chấm còn lại: '.$used_turns.'</span>'; 
			}
			if(COUNT($get_attempt) > 1) {
				$this->content->footer .= '<a class="th_all_quiz" href="'.$CFG->wwwroot.'/blocks/th_scoring_request/view.php?courseid='.$COURSE->id.'">Xem tất cả</a>';
			}
		}else {
			$this->content->footer = '
			<p class="titile">Danh sách các bài kiểm tra đã nộp trống!</p class="titile">';
		}
		
		return $this->content;
	}
}
?>