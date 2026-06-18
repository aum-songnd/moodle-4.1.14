<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_report_grading_requests_form extends moodleform {

    public function definition() {

    global $DB;


        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_report_grading_requests'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
        $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} ORDER BY fullname ASC');
        $courses_arr = [];
        foreach ($courses as $course) {
            $courses_arr[$course->id] = $course->fullname;
        }
        
    
        $options_course = [
            'multiple' => true,
            'noselectionstring' => get_string('chooseacourse', 'block_th_report_grading_requests'), // Chuỗi hiển thị khi không chọn gì.
        ];

        $mform->addElement('autocomplete', 'courseid', 'Chọn khóa học', $courses_arr, $options_course);
        $mform->setType('courseid', PARAM_SEQUENCE);

        $from_date = get_string('from_date', 'block_th_report_grading_requests');
        $mform->addElement('date_selector', 'from_date', $from_date);

        $to_date = get_string('to_date', 'block_th_report_grading_requests');
        // $options = array(
        //     0 => get_string('all', 'block_th_report_grading_requests'),
        //     1 => get_string('one', 'block_th_report_grading_requests'),
        //     2 => get_string('multi', 'block_th_report_grading_requests'),
        // );
   
        // $mform->addElement('select', 'courseid', get_string('course', 'block_th_report_grading_requests'), $options);
        // $mform->setType('courseid', PARAM_INT);

        // $courses = $DB->get_records_menu('course', null, 'fullname ASC', 'id, fullname');

        // $mform->addElement('select', 'courseid', get_string('course'), $courses);
        $mform->setType('courseid', PARAM_INT);
                
        $mform->addElement('date_selector', 'to_date', $to_date);
        //$mform->addRule('courseid', null, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('submit', 'block_th_report_grading_requests'));
    }
}
