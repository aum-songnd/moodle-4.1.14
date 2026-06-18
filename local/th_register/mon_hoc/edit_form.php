<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/th_register/mon_hoc/locallib.php';

class edit_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$mform->addElement('text', 'ma_mon', get_string('ma_mon', 'local_th_register'));
		$mform->setType('ma_mon', PARAM_NOTAGS);
		$mform->addRule('ma_mon', '', 'required', null, 'client', false, false);

		$mform->addElement('text', 'ten_mon', get_string('ten_mon', 'local_th_register'));
		$mform->setType('ten_mon', PARAM_NOTAGS);
		$mform->addRule('ten_mon', '', 'required', null, 'client', false, false);

		$ds_nganh     = th_ds_nganh();
		$ds_doi_tuong = ds_doi_tuong();
		// $ds_tin_chi = array(
		// 	'1' => '1 tín chỉ',
		// 	'2' => '2 tín chỉ',
		// 	'3' => '3 tín chỉ',
		// 	'4' => '4 tín chỉ',
		// 	'5' => '5 tín chỉ',
		// 	'6' => '6 tín chỉ',
		// 	'7' => '7 tín chỉ',
		// 	'8' => '8 tín chỉ',
		// 	'9' => '9 tín chỉ',
		// 	'10' => '10 tín chỉ'
		// );
		$ds_tin_chi = [];
		for ($i = 1; $i <= 10; $i++) {
			$ds_tin_chi[(string)$i] = get_string('tin_chi', 'local_th_register', $i);
		}

		$options1 = array(
			'multiple'          => true,
			'noselectionstring' =>  get_string('noselectionstring', 'local_th_register'),
		);

		$options2 = array(
			'multiple'          => true,
			'noselectionstring' =>  get_string('noselectionstring', 'local_th_register'),
		);
		$select = $mform->addElement('select', 'so_tin_chi', get_string('so_tin_chi', 'local_th_register'), $ds_tin_chi);
		$select->setSelected('3');

		$mform->addElement('autocomplete', 'ma_nganh_id', get_string('nganh', 'local_th_register'), $ds_nganh, $options1);
		$mform->addRule('ma_nganh_id', '', 'required', null, 'client', false, false);

		$mform->addElement('autocomplete', 'doi_tuong_id',  get_string('doi_tuong', 'local_th_register'), $ds_doi_tuong, $options2);
		$mform->addRule('doi_tuong_id', '', 'required', null, 'client', false, false);

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files)
	{
		global $DB;
		if (strlen($data['ma_mon']) > 10) {
			return array('ma_mon' =>  get_string('error_maxlength_ma_mon', 'local_th_register'));
		}
		if (strlen($data['ten_mon']) > 45) {
			return array('ten_mon' => get_string('error_maxlength_ten_mon', 'local_th_register'));
		}

		if (!empty($data['id'])) {
			$ma_mon = $data['ma_mon'];
			$id     = $data['id'];
			$course = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon' AND NOT id = '$id'");
			if (!empty($course)) {
				return array('ma_mon' => get_string('error_ma_mon_exists', 'local_th_register'));
			}
		}

		if (empty($data['id'])) {
			$ma_mon = $data['ma_mon'];
			$id     = $data['id'];
			$course = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon' AND NOT id = '$id'");
			if (!empty($course)) {
				return array('ma_mon' => get_string('error_ma_mon_exists', 'local_th_register'));
			}
		}
		return array();
	}
}
