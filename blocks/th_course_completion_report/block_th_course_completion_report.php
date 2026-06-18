<?php
class block_th_course_completion_report extends block_base {

	public function init() {
		$this->title = get_string('pluginname', 'block_th_course_completion_report');
	}

	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}
		global $CFG, $COURSE;
		$this->content = new stdClass;
		$context = context_course::instance($COURSE->id);
		if (has_capability('block/th_course_completion_report:view', $context)) {
			$link = $CFG->wwwroot . '/blocks/th_course_completion_report/view.php';
			$text = get_string('view', 'block_th_course_completion_report');
			$this->content->text = get_string('content', 'block_th_course_completion_report');
			$this->content->footer = html_writer::link($link, $text);
		} else {
			$this->content->footer = 'No Permission!';
		}
		return $this->content;
	}
	/**
	 * Subclasses should override this and return true if the
	 * subclass block has a settings.php file.
	 *
	 * @return boolean
	 */
	function has_config() {
		return true;
	}
}
