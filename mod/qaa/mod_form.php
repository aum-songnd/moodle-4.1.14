<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main mod_qaa configuration form.
 *
 * @package     mod_qaa
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/course/moodleform_mod.php';

/**
 * Module instance settings form.
 *
 * @package    mod_qaa
 * @copyright  2020 phamleminh1812@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_qaa_mod_form extends moodleform_mod {

	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $CFG;

		$mform = $this->_form;

		// Adding the "general" fieldset, where all the common settings are shown.
		$mform->addElement('header', 'general', get_string('general', 'form'));

		// Adding the standard "name" field.
		$mform->addElement('text', 'name', get_string('qaname', 'mod_qaa'), array('size' => '64'));

		if (!empty($CFG->formatstringstriptags)) {
			$mform->setType('name', PARAM_TEXT);
		} else {
			$mform->setType('name', PARAM_CLEANHTML);
		}

		$mform->addRule('name', null, 'required', null, 'client');
		$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
		$mform->addHelpButton('name', 'qaname', 'mod_qaa');

		// Adding the standard "intro" and "introformat" fields.
		if ($CFG->branch >= 29) {
			$this->standard_intro_elements();
		} else {
			$this->add_intro_editor();
		}

		$mform->addElement('header', 'question_close_time_header', get_string('question_close_time', 'mod_qaa'));
		// Completion expected at particular date? (For progress tracking)
		$mform->addElement('date_time_selector', 'question_close_time', get_string('question_close_time', 'mod_qaa'),
		array('optional' => true));

		// Add standard grading elements.
		$this->standard_grading_coursemodule_elements();

		$isupdate = !empty($this->_cm);

		$mform->setDefault('grade', 3.00);

		$mform->setType('gradepass', PARAM_RAW);
		$mform->setDefault('gradepass', 3.00);

		$mform->removeElement('gradecat');

		// Add standard elements.
		$this->standard_coursemodule_elements();
		$this->apply_admin_defaults();
		// Add standard buttons.
		$this->add_action_buttons();
	}
}
