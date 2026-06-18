<?php

class block_th_course_content_approval extends block_base {
	public function init() {
		$this->title = get_string('title', 'block_th_course_content_approval');
	}

	public function get_content() {
		global $CFG, $COURSE;

		// Check the user has update or visibility setting capability within their role.
        $capabilities = array(
            'block/th_course_content_approval:view'
        );
        
        $context = context_course::instance($COURSE->id);
        if (!has_any_capability($capabilities, $context)) {
            return null;
        } else {
        	$this->content = new stdClass;
			$this->content->text = get_string('approve_course_materials', 'block_th_course_content_approval');
			global $COURSE;
			$context = context_course::instance($COURSE->id);
			$url = new moodle_url('/blocks/th_course_content_approval/view.php', array('id' => $COURSE->id));
			$this->content->footer = html_writer::link($url, get_string('approve', 'block_th_course_content_approval'));
			
			return $this->content;
        }
	}
}
?>