<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class edit_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$mform->addElement('textarea', 'cohorts', get_string('cohorts', 'mod_thpage'));
		$mform->setType('cohorts', PARAM_NOTAGS);
		$mform->addRule('cohorts', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'fullname', get_string('fullname'));
		$mform->setType('fullname', PARAM_NOTAGS);
		$mform->addRule('fullname', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'phone', get_string('phone'));
		$mform->setType('phone', PARAM_NOTAGS);
		$mform->addRule('phone', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'email', get_string('email'));
		$mform->setType('email', PARAM_NOTAGS);
		$mform->addRule('email', '', 'required', null, 'client', false, false);

		$ds_role = array(
			'0' => 'QLHT',
			'1' => 'GVCN',
		);

		$ds_gioi_tinh = array(
			'0' => get_string('female', 'mod_thpage'),
			'1' => get_string('male', 'mod_thpage'),
		);

		$select = $mform->addElement('select', 'role', get_string('role'), $ds_role);
		$select->setSelected('1');

		$select = $mform->addElement('select', 'gender', get_string('gender', 'mod_thpage'), $ds_gioi_tinh);
		$select->setSelected('0');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files)
	{
		global $DB;

		if (strlen($data['fullname']) > 200) {
			return array('fullname' => get_string('fullname_error', 'mod_thpage'));
		}
		if (strlen($data['phone']) > 20) {
			return array('phone' => get_string('phone_error','mod_thpage'));
		} else {
			if (!is_numeric($data['phone'])) {
				return array('phone' => get_string('phone_error2','mod_thpage'));
			}
		}

		if (strlen($data['email']) > 100) {
			return array('email' => get_string('email_error','mod_thpage'));
		} else {
			$email = $data['email'];
			$role = $data['role'];

			if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
				if (empty($data['id'])) {
			        if ($DB->record_exists_sql("SELECT * FROM {th_teacher_infor} WHERE email = '$email' AND role = '$role'")) {
						return array('email' => get_string('email_exists', 'mod_thpage'));
					}
				}
		    } else {
		        return array('email' => get_string('email_format_is_wrong', 'mod_thpage'));
		    }	
		}

		return array();
	}
}