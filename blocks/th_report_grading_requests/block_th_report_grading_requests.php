<?php

class block_th_report_grading_requests extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_report_grading_requests');
	}

	function has_config() {
        return true;
    }

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass;
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_report_grading_requests:managepages', $context)) {
			$url = new moodle_url('/blocks/th_report_grading_requests/view.php');
			$this->content->footer = html_writer::link($url, 'Báo cáo số lượt học viên yêu cầu chấm chữa');
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}


}
?>