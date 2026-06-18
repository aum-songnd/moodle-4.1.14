<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class local_th_open_course_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Chọn NGÀY mở môn (không chọn giờ) – dùng date_selector.
        $mform->addElement(
            'date_selector',
            'opendate',
            get_string('opendate', 'local_th_daily_grade_update'),
        );

        // Nút.
        $this->add_action_buttons(true, get_string('submit'));
    }


    public function validation($data, $files) {
        return array();
    }
}
