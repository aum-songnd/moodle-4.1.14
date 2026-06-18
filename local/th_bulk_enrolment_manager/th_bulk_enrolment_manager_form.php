<?php
    use \local_th_bulk_enrolment_manager\libs;
    require_once($CFG->dirroot . '/lib/formslib.php');

    class th_bulk_enrolment_manager_form extends moodleform {
        function definition() {
            global $DB;
            $mform = $this->_form;
    
            $mform->addElement('header', 'displayinfo', get_string('textfields', 'local_th_bulk_enrolment_manager'));
    
            // Tuỳ chọn theo khoá học hoặc ngày.
            $radioarray = array();
            $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomakhoa', 'local_th_bulk_enrolment_manager'), 0);
            $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiodate', 'local_th_bulk_enrolment_manager'), 1);
            $mform->addGroup($radioarray, 'show_option', get_string('options', 'local_th_bulk_enrolment_manager'), array(''), false);
            $mform->setDefault('show_option', 0);
    
            $libs = new libs();
            $listcourses = $libs->get_list_courses();
            $liststudents = $libs->get_list_students();
            $list_role = $libs->get_list_role();

            $mform->addElement('select', 'action_type', get_string('action_type', 'local_th_bulk_enrolment_manager'), [
                'enrol' => get_string('enroluser', 'local_th_bulk_enrolment_manager'),
                'unenrol' => get_string('unenroluser', 'local_th_bulk_enrolment_manager'),
                'suspend' => get_string('suspenduser', 'local_th_bulk_enrolment_manager'),
            ]);
            $mform->setDefault('action_type', 'enrol');
    
            // Danh sách khoá học
            $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('allcourses', 'local_th_bulk_enrolment_manager'),
            );
            $mform->addElement('autocomplete', 'course_id', get_string('course_id', 'local_th_bulk_enrolment_manager'), $listcourses, $options);
    
            $mform->addElement('date_selector', 'date', get_string('date', 'local_th_bulk_enrolment_manager'));
            $mform->disabledIf('course_id', 'show_option', 'neq', '0');
            $mform->disabledIf('date', 'show_option', 'neq', '1');
            $mform->hideif('course_id', 'show_option', 'neq', '0');
            $mform->hideif('date', 'show_option', 'neq', '1');
    
            // Vai trò
            $options2 = array(
                'multiple' => false,
                'noselectionstring' => get_string('allroles', 'local_th_bulk_enrolment_manager'),
            );
            $mform->addElement('autocomplete', 'role', get_string('role', 'local_th_bulk_enrolment_manager'), $list_role, $options2);
    
            // Danh sách người dùng
            $options3 = array(
                'multiple' => true,
                'noselectionstring' => get_string('allusers', 'local_th_bulk_enrolment_manager'),
            );

            $mform->addElement('autocomplete', 'userid', get_string('userid', 'local_th_bulk_enrolment_manager'), $liststudents, $options3);
            $mform->addRule('userid', '', 'required', null, 'client', false, false);

            // Khoảng thời gian ghi danh
            $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'local_th_bulk_enrolment_manager'));
            $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'local_th_bulk_enrolment_manager'), array('optional' => true));

            $mform->hideIf('startdate', 'action_type', 'neq', 'enrol');
            $mform->hideIf('enddate', 'action_type', 'neq', 'enrol');
            $mform->hideIf('role', 'action_type', 'neq', 'enrol');
    
            $this->add_action_buttons(true, get_string('submit', 'local_th_bulk_enrolment_manager'));
        }
    
        function validation($data, $files) {
            if (empty($data['role']) && $data['action_type'] == 'enrol') {
                return array('role' => get_string('missingroleenrol', 'local_th_bulk_enrolment_manager'));
            }
            
            if (empty($data['course_id']) && $data['show_option'] == 0) {
                return array('course_id' => get_string('missingcourse', 'local_th_bulk_enrolment_manager'));
            }

            if (empty($data['userid']) && $data['show_option'] == 1) {
                return array('userid' => get_string('missinguser', 'local_th_bulk_enrolment_manager'));
            }
            
            if ($data['enddate'] < $data['startdate'] && (!empty($data['enddate']))) {
                return array('enddate' => get_string('endbeforestart', 'local_th_bulk_enrolment_manager'));
            }

            return array();
        }
    }

    class th_excel_bulk_enrolment_manager_form extends moodleform {

        function definition() {
            global $COURSE, $CFG;
            $mform = $this->_form;
            $mform->addElement('header', 'displayinfo', get_string('filter'));
            $link = "<a href='th_bulk_enrolment_manager.csv'>example.csv</a>";
            $mform->addElement('static', 'example', 'Example', $link);
            // Reuse the same options.
            $mform->addElement('filepicker', 'file', get_string('file'), null,
                array(
                    'accepted_types' => array('.csv'),
                    'areamaxbytes' => 100000000,
                    'maxfiles' => 1,
                )
            );
    
            $mform->addRule('file', null, 'required', null, 'client');
            $this->add_action_buttons(true, get_string('submit'));
        }
        function validation($data, $files) {
            return array();
        }
    }
    
    class confirm_form extends moodleform {
    
        protected function definition() {
            global $SESSION;
    
            $localth_bulkenrol_csvkey = $this->_customdata['local_th_bulkenrol_csv_key'];
    
            $mform = $this->_form;
    
            $mform->addElement('hidden', 'key');
            $mform->setType('key', PARAM_RAW);
            $mform->setDefault('key', $localth_bulkenrol_csvkey);
    
            // Check if we want to show the enrol user button.
            $showenrolebutton = true;
            $checkedmails = null;
            if (isset($SESSION->local_th_bulkenrol_csv) && array_key_exists($localth_bulkenrol_csvkey, $SESSION->local_th_bulkenrol_csv)) {
                $checkedmails = $SESSION->local_th_bulkenrol_csv[$localth_bulkenrol_csvkey];
                if (isset($checkedmails->validfound) && empty($checkedmails->validfound)) {
                    $showenrolebutton = false;
                }
            }
    
            // Only show the enrol user button if necessary.
            if ($showenrolebutton) {
    
                $buttonstring = get_string('enrol_users', 'local_th_bulk_enrolment_manager');
    
                $this->add_action_buttons(true, $buttonstring);
            }
        }
    }
?>
