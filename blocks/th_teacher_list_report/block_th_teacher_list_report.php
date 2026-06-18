<?php

class block_th_teacher_list_report extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_teacher_list_report');
	}

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass();
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_teacher_list_report:view', $context)) {
			$url = new moodle_url('/blocks/th_teacher_list_report/view.php');
			$this->content->footer = html_writer::link($url, get_string('teacher_statistics', 'block_th_teacher_list_report'));
		} else {
			$this->content->footer = get_string('no_permission', 'block_th_teacher_list_report');
		}
		return $this->content;
	}
}
?>