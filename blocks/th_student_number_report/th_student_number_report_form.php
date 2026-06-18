<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'libs.php';

class th_student_number_report_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('subject', 'block_th_student_number_report'), 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('major', 'block_th_student_number_report'), 1);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('opening_batch', 'block_th_student_number_report'), 2);
		$mform->addGroup($radioarray, 'show_option', get_string('statistics_by', 'block_th_student_number_report'), array(''), false);
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

		$ds_nganh = ds_nganh();

		$options = array(
			'multiple' => true,
			'Chưa chọn'
		);

		$element = $mform->addElement('autocomplete', 'course_id', get_string('select_course', 'block_th_student_number_report'), $list_shortname, $options);
		$element2 = $mform->addElement('autocomplete', 'nganh', get_string('select_major', 'block_th_student_number_report'), $ds_nganh, $options);
		$mform->addElement('date_selector', 'startdate', get_string('from_date', 'block_th_student_number_report'));
		$mform->addElement('date_selector', 'enddate', get_string('to_date', 'block_th_student_number_report'));


		$options1 = array(
			'0' => get_string('greater_than', 'block_th_student_number_report'),
			'1' => get_string('less_than', 'block_th_student_number_report'),
			'2' => get_string('equal_to', 'block_th_student_number_report')
		);		

		$this->_name = 'so_luong';
		$this->_label = get_string('student_count', 'block_th_student_number_report');
		$objs = array();

		$objs['op'] = $mform->createElement('select', 'option', null, $options1);
		$mform->setDefault('option', 0);

		$objs['value'] = $mform->createElement('text', $this->_name, null);
		$mform->setDefault($this->_name, 0);

		$grp = $mform->addElement('group', $this->_name . '_grp', $this->_label, $objs, '', false);
		$mform->setType($this->_name, PARAM_INT);

		$mform->disabledIf('course_id', 'show_option', 'neq', '0');
		$mform->disabledIf('nganh', 'show_option', 'neq', '1');

		$mform->hideif('course_id', 'show_option', 'neq', '0');
		$mform->hideif('nganh', 'show_option', 'neq', '1');
		
		$this->add_action_buttons(true, 'Submit');
	}

	function validation($data, $files) {
		return array();
	}

}
