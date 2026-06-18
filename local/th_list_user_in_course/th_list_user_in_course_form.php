<?php

class th_add_group_form extends moodleform {
	public function definition() {
		global $CFG, $DB;

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);
		$mform->addElement('hidden', 'course_id');
		$mform->setType('course_id', PARAM_RAW);
		$mform->addElement('text', 'ten_nhom', 'Tên nhóm');
		$mform->setType('ten_nhom', PARAM_TEXT);
		$mform->addRule('ten_nhom', '', 'required', null, 'client', false, false);
		$mform->addElement('textarea', 'mo_ta', 'Mô tả');
		$mform->setType('mo_ta', PARAM_TEXT);
		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files) {
		return array();
	}
}

class th_add_user_group_form extends moodleform {
	public function definition() {
		global $CFG, $DB;

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);
		$mform->addElement('hidden', 'course_id');
		$mform->setType('course_id', PARAM_RAW);

		if(isset($_GET['course_id'])){
			$course_id = $_GET['course_id'];
			$list_groups = get_list_groups($course_id);
		} else {
			$course_id = $_POST['course_id'];
			$list_groups = get_list_groups($course_id);
		}
		
		$options = array(                                                                                                           
            'multiple' => true,                                                  
            'noselectionstring' => 'Chưa chọn',                                                                
        );  
        $mform->addElement('autocomplete', 'group_id', 'Chọn nhóm', $list_groups, $options);
        // $mform->addRule('group_id', '', 'required', null, 'client', false, false);

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files) {
		return array();
	}
}

?>