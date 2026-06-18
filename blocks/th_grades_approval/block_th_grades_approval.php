<?php
class block_th_grades_approval extends block_base {
    public function init() {
        $this->title = get_string('th_grades_approval', 'block_th_grades_approval');
    }

    public function get_content() {
        global $CFG, $DB, $COURSE;

        // Check the user has update or visibility setting capability within their role.
        $capabilities = array(
            'moodle/course:update',
            'moodle/course:visibility',
            'moodle/course:viewhiddencourses',
        );
        
        $context = context_course::instance($COURSE->id);
        if (!has_any_capability($capabilities, $context)) {
            return null;
        }

        if ($this->content !== null) {
          return $this->content;
        }

        $link_home = $CFG->wwwroot;
    
        $this->content         =  new stdClass;
        $this->content->text   = 'Bấm vào đây để phê duyệt bảng điểm môn học này';

        if (has_capability('block/th_grades_approval:addinstance', $context)) {
            $this->content->footer = "<a href='$link_home/blocks/th_grades_approval/view.php?id=$COURSE->id'>Phê duyệt</a>";
        } else {
            $this->content->footer = 'No Permission!';
        }   
     
        return $this->content;
    }

    public function applicable_formats() {
		return array(
			'course' => true,
			
		);
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
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.
}