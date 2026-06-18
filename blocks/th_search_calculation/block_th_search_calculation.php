<?php

class block_th_search_calculation extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_search_calculation');
	}

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass;
		global $COURSE;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_search_calculation:managepages', $context)) {
			$url = new moodle_url('/blocks/th_search_calculation/view.php');
			$this->content->text = get_string('search_course_formula', 'block_th_search_calculation');
			$this->content->footer = html_writer::link($url, get_string('find_course_formula', 'block_th_search_calculation'));
		} else {
			$this->content->footer = get_string('no_permission', 'block_th_search_calculation');
		}		
		return $this->content;
	}
}
?>