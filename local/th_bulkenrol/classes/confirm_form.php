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
 * Local plugin "th_bulkenrol" - Confirmation form
 *
 * @package   local_th_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_th_bulkenrol;

use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

/**
 * Class confirm_form
 * @package local_th_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirm_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $SESSION;

		$localth_bulkenrolkey = $this->_customdata['local_th_bulkenrol_key'];
		$courseid = $this->_customdata['courseid'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $localth_bulkenrolkey);

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', $courseid);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checkedmails = null;
		if (isset($SESSION->local_th_bulkenrol) && array_key_exists($localth_bulkenrolkey, $SESSION->local_th_bulkenrol)) {
			$checkedmails = $SESSION->local_th_bulkenrol[$localth_bulkenrolkey];
			if (isset($checkedmails->validemailfound) && empty($checkedmails->validemailfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$identifiertype = $SESSION->local_th_bulkenrol_identifiertype[$localth_bulkenrolkey];
			$option = $SESSION->local_th_bulkenrol_options[$localth_bulkenrolkey];

			$buttonstring = get_string('enrol_users', 'local_th_bulkenrol');
			if ($option == 0) {
				//enrol
				$buttonstring = get_string('enrol_users', 'local_th_bulkenrol');
			} else if ($option == 2) {
				//unenrol
				$buttonstring = get_string('unenrol_users', 'local_th_bulkenrol');
			} else if ($option == 1) {
				//suppend
				$buttonstring = get_string('suspend_users', 'local_th_bulkenrol');
			}

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
