<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';


class th_assign_turns_form extends moodleform {
	public function definition() {
		global $CFG, $DB;

		$mform = $this->_form;

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$users = $DB->get_records_sql("SELECT * FROM {user} WHERE id <> 1 AND deleted = 0 AND suspended = 0 AND username != 'admin'");
		$user_arr = array();
		$user_arr = ["" => ""];
		foreach ($users as $userid => $user) {
			$user_arr[$userid] = fullname($user) . ', ' . $user->username . ', ' . $user->email;
		}

		$options_user = array(
			'multiple' => false,
			'noselectionstring' => 'Chọn một học viên',
		);
		$mform->addElement('autocomplete', 'userid',  'Chọn học viên', $user_arr, $options_user);
		$mform->addRule('userid', '', 'required', null, 'client', false, false);

		$sql = "SELECT id,fullname,shortname FROM {course} WHERE summaryformat=1 AND visible =1";

		$courses = $DB->get_records_sql($sql);
		$course_arr = array('' =>'');
		foreach ($courses as $course) {
			$course_arr[$course->id] = $course->fullname . ', ' . $course->shortname;
		}
		
        $mform->addElement('text', 'count', 'Nhập số lượt chấm');
        $mform->setDefault('count', '');
        $mform->setType('count', PARAM_INT); 
        $mform->addRule('count', 'Nhập số lượt chấm là số nguyên', 'numeric', null, 'client');
        $mform->addRule('count', '', 'required', null, 'client', false, false);
        $mform->addRule('count', 'Nhập số lượt chấm là số nguyên', 'regex', '/^[0-9]+$/', 'client');

		$this->add_action_buttons(true, get_string('submit'));
	}
}