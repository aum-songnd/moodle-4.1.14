<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class th_unanswered_questions_form extends moodleform {

	public function definition() {
		global $DB;
		$mform = $this->_form;

		$sql = "SELECT concat(firstname,' ',lastname) as name , userid FROM {user}
        inner join {user_info_data}
        on {user}.id = {user_info_data}.userid
        where data = 'Giảng Viên';";
        $teachers = $DB->get_records_sql($sql);
		$list_teacher = array();
        foreach ($teachers as $key => $value) {
			$list_teacher[$value->userid] = $value->name;
		}
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$options = array(
			'multiple'          => false,
			'noselectionstring' => get_string('select', 'local_th_unanswered_questions'),
		);

		$mform->addElement('autocomplete', 'giang_vien', get_string('selectteacher', 'local_th_unanswered_questions'), $list_teacher, $options);

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files) {
		return array();
	}
}