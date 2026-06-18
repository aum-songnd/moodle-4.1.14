<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'libs.php';

class th_teacher_list_report_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('option_startdate', 'block_th_teacher_list_report'), 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('option_course', 'block_th_teacher_list_report'), 1);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('option_teacher', 'block_th_teacher_list_report'), 2);
		$mform->addGroup($radioarray, 'show_option', get_string('filter_by', 'block_th_teacher_list_report'), array(''), false);
		$mform->setDefault('show_option', 0);

		$list_course = ds_course();

		$list_shortname = [];
		
		foreach($list_course as $k => $shortname){
			$pos = strpos($shortname, '-');

			if ($pos !== false) {
				$shortname_arr = explode('-', $shortname);
				$shortname = $shortname_arr[0];
			}

			if (!in_array($shortname, $list_shortname)){
				$pos2 = strrpos($k, ' - ');

				if ($pos2 !== false) {
					$fullname = substr($k, 0, $pos2);
				} else {
					$fullname = $k;
				}
				
				$list_shortname[$shortname] = $fullname;
			}
		}

		$sql = "SELECT u.id,firstname,lastname,email,username,d.data
                FROM {user_info_data} d
                JOIN {user} u ON d.userid = u.id
                JOIN {user_info_field} f ON d.fieldid = f.id
                WHERE d.data LIKE 'Giảng viên' AND u.deleted = 0 AND u.suspended = 0";
        $teachers = $DB->get_records_sql($sql);
        $list_teacher = array('' => '');
        foreach ($teachers as $key => $teacher) {
            $list_teacher[$key] = $teacher->firstname . ' ' . $teacher->lastname . ',' . $teacher->username;
        }

		$options = array(
			'multiple' => true,
			get_string('noselection', 'block_th_teacher_list_report')
		);

		$mform->addElement('autocomplete', 'course_id', get_string('select_course', 'block_th_teacher_list_report'), $list_shortname, $options);
		$mform->addElement('autocomplete', 'giang_vien', get_string('select_teacher', 'block_th_teacher_list_report'), $list_teacher, $options);
		$mform->addElement('date_selector', 'ngay_mo', get_string('select_open_date', 'block_th_teacher_list_report'));
		$mform->addElement('date_selector', 'startdate', get_string('from_date', 'block_th_teacher_list_report'));
		$mform->addElement('date_selector', 'enddate', get_string('to_date', 'block_th_teacher_list_report'));

		$mform->disabledIf('course_id', 'show_option', 'neq', '1');
		$mform->disabledIf('giang_vien', 'show_option', 'neq', '2');
		$mform->disabledIf('ngay_mo', 'show_option', 'neq', '0');
		$mform->disabledIf('startdate', 'show_option', 'eq', '0');
		$mform->disabledIf('enddate', 'show_option', 'eq', '0');

		$mform->hideif('course_id', 'show_option', 'neq', '1');
		$mform->hideif('giang_vien', 'show_option', 'neq', '2');
		$mform->hideif('ngay_mo', 'show_option', 'neq', '0');
		$mform->hideif('startdate', 'show_option', 'eq', '0');
		$mform->hideif('enddate', 'show_option', 'eq', '0');
		
		$this->add_action_buttons(true, 'Submit');
	}

	function validation($data, $files) {
		return array();
	}

}
