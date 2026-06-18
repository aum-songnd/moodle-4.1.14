<?php

require_once $CFG->dirroot . '/lib/formslib.php';

class upload_gvcn_qlht_form extends moodleform
{
	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$link = "<a href='example_import.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('file_example', 'mod_thpage'), $link);

		$mform->addElement('filepicker', 'data_file', get_string('file'));

		$mform->addRule('data_file', get_string('required'), 'required', null, 'client');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files)
	{
		return array();
	}
}

class confirm_form2 extends moodleform {

	protected function definition() {
		global $SESSION;

		$thpage_key = $this->_customdata['thpage_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $thpage_key);

		$showbutton = true;
		$checkedtimes = null;
		if (isset($SESSION->thpage) && array_key_exists($thpage_key, $SESSION->thpage)) {
			$checked = $SESSION->thpage[$thpage_key];
			if (isset($checked->valid_found) && empty($checked->valid_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {
			$buttonstring = get_string('add');
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}