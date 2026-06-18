<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once 'lib.php';

class th_question_number_report_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform = $this->_form;

		$list_course = th_question_number_report_ds_course();

		$list_shortname = [];
		
		foreach($list_course as $k => $shortname){
			$pos = strpos($shortname, '-');

			if ($pos !== false) {
				$shortname_arr = explode('-', $shortname);
				$shortname = $shortname_arr[0];
			}

			if (!in_array($shortname, $list_shortname)){
				
				$couse_latest = $DB->get_record_sql("SELECT * FROM {course} WHERE NOT id = 1 AND visible <> 0 AND fullname NOT LIKE '% - mẫu' AND shortname LIKE '$shortname%' AND startdate = (SELECT MAX(startdate) FROM {course} WHERE NOT id = 1 AND fullname NOT LIKE '% - mẫu' AND shortname LIKE '$shortname%' AND visible <> 0)");
				$list_shortname[$couse_latest->id] = $couse_latest->fullname;
			}
		}

		$options = array(
			'multiple' => true,
		);
		$mform->addElement('autocomplete', 'course_id', 'Chọn khóa học', $list_shortname, $options);

		$options1 = array(
			'0' => get_string('greater_than', 'block_th_question_number_report'),
			'1' => get_string('less_than', 'block_th_question_number_report'),
			'2' => get_string('equal_to', 'block_th_question_number_report')
		);

		$objs = array();

		$objs['op'] = $mform->createElement('select', 'option', null, $options1);
		$mform->setDefault('option', 0);

		$objs['value'] = $mform->createElement('text', 'so_luong', null);
		$mform->setDefault('so_luong', 0);

		$grp = $mform->addElement('group', 'so_luong_grp', get_string('question_quantity', 'block_th_question_number_report'), $objs, '', false);
		$mform->setType('so_luong', PARAM_INT);

		$this->add_action_buttons(true, get_string('submit'));
	}
	//Custom validation should be added here
	function validation($data, $files){
		return array();
	}
}
?>
