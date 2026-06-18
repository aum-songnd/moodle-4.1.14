<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class th_question_bank_report_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
    
        // Lấy danh sách khóa học
        $sql = "SELECT id, CONCAT(fullname, ' (', shortname, ')') AS coursename FROM {course} WHERE id != 1";
        $courses = $DB->get_records_sql_menu($sql);
    
        // Thêm các phần tử vào form với get_string()
        $mform->addElement('autocomplete', 'selectedcourses', get_string('select_courses', 'local_th_question_bank_report'), $courses, array('multiple' => true));
        $mform->setDefault('selectedcourses', array());
    
        $mform->addElement('date_selector', 'startdate', get_string('from_date', 'local_th_question_bank_report'), array('optional' => true));
        $mform->addElement('date_selector', 'enddate', get_string('to_date', 'local_th_question_bank_report'), array('optional' => true));
    
        $this->add_action_buttons(); 
    }
    
    function validation($data, $files) {
        $errors = array();
        if (!empty($data['startdate']) && !empty($data['enddate']) && $data['startdate'] > $data['enddate']) {
            $errors['enddate'] = get_string('error_enddate', 'local_th_question_bank_report');
        }
        return $errors;
    }
    
}
?>
