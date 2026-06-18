<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_upload_course_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$link = "<a href='up_mon.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'local_th_register'), $link);

		$mform->addElement('filepicker', 'list_course', get_string('file'));

		$mform->addRule('list_course', get_string('required'), 'required', null, 'client');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files)
	{
		return array();
	}
}

class confirm_upload_course_form extends moodleform
{

	protected function definition()
	{
		global $SESSION;

		$th_upload_course_key = $this->_customdata['th_upload_course_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_upload_course_key);

		$showbutton   = true;
		$check_course = null;
		if (isset($SESSION->local_th_upload_course) && array_key_exists($th_upload_course_key, $SESSION->local_th_upload_course)) {
			$check_course = $SESSION->local_th_upload_course[$th_upload_course_key];
			if (isset($check_course->valid_course_found) && empty($check_course->valid_course_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {

			$buttonstring = get_string('submit');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}

class th_upload_course_has_nganh_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$link = "<a href='up_mon_has_nganh.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'local_th_register'), $link);

		$mform->addElement('filepicker', 'list_course', get_string('file'));

		$mform->addRule('list_course', get_string('required'), 'required', null, 'client');

		$this->add_action_buttons(true, get_string('submit'));
		$mform->addElement('static', 'example',  get_string('note', 'local_th_register'));
	}

	public function validation($data, $files)
	{
		return array();
	}
}

class confirm_upload_course_has_nganh_form extends moodleform
{

	protected function definition()
	{
		global $SESSION;

		$th_upload_course_has_nganh_key = $this->_customdata['th_upload_course_has_nganh_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_upload_course_has_nganh_key);

		$showbutton   = true;
		$check_course = null;
		if (isset($SESSION->local_th_upload_course) && array_key_exists($th_upload_course_has_nganh_key, $SESSION->local_th_upload_course)) {
			$check_course = $SESSION->local_th_upload_course[$th_upload_course_has_nganh_key];
			if (isset($check_course->valid_course_found) && empty($check_course->valid_course_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {

			$buttonstring = get_string('submit');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}

class th_upload_course_has_doi_tuong_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$link = "<a href='up_mon_has_doi_tuong.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'local_th_register'), $link);

		$mform->addElement('filepicker', 'list_course', get_string('file'));

		$mform->addRule('list_course', get_string('required'), 'required', null, 'client');

		$this->add_action_buttons(true, get_string('submit'));
		$mform->addElement('static', 'example', get_string('note', 'local_th_register'));
	}

	public function validation($data, $files)
	{
		return array();
	}
}

class confirm_upload_course_has_doi_tuong_form extends moodleform
{

	protected function definition()
	{
		global $SESSION;

		$th_upload_course_has_doi_tuong_key = $this->_customdata['th_upload_course_has_doi_tuong_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_upload_course_has_doi_tuong_key);

		$showbutton   = true;
		$check_course = null;
		if (isset($SESSION->local_th_upload_course) && array_key_exists($th_upload_course_has_doi_tuong_key, $SESSION->local_th_upload_course)) {
			$check_course = $SESSION->local_th_upload_course[$th_upload_course_has_doi_tuong_key];
			if (isset($check_course->valid_course_found) && empty($check_course->valid_course_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {

			$buttonstring = get_string('submit');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
