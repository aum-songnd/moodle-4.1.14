<?php

    require_once($CFG->dirroot . '/lib/formslib.php');

    class th_question_bank_segregator_form extends moodleform {

        function definition() {
            global $DB;
            $mform = $this->_form;
            $mform->addElement('header', 'displayinfo', get_string('filter'));

            $mform->addElement('hidden', 'courseid');
            $mform->setType('courseid', PARAM_RAW);
            
            // $listcourses = [];
            // $sql = "SELECT * FROM {course} WHERE NOT id = 1 AND fullname LIKE '% - Mẫu'";
            // $courses = $DB->get_records_sql($sql);
            // if (!empty($courses)) {
            //     foreach ($courses as $id => $course) {
            //         $listcourses[$id] = $course->fullname . ',' . $course->shortname;
            //     }
            // }
            
            // $options = array(                                                                                                           
            //     'multiple' => false,                                                  
            //     'noselectionstring' => 'Chưa chọn',                                                                
            // );  
            // $mform->addElement('autocomplete', 'course_id', 'Chọn khóa học', $listcourses, $options);

            $mform->addElement('select', 'ratio', 'Chọn tỷ lệ backup', array('0.1' => '10%', '0.2' => '20%', '0.3' => '30%', '0.4' => '40%', '0.5' => '50%'));
                  
            $this->add_action_buttons(true,  get_string('submit'));
        }
        
        function validation($data, $files) {
            return array();
        }

    }
?>