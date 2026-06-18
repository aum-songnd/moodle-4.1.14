<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * This page lists all the instances of certificate in a particular course
 *
 * @package    mod
 * @subpackage certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_th_vmc_sms\event;

defined('MOODLE_INTERNAL') || die();

class sms_sent extends \core\event\base {
	protected function init() {
		$this->data['crud'] = 'c';
		$this->data['edulevel'] = self::LEVEL_OTHER;
	}

	public static function get_name() {
		return 'SMS Sent';
	}

	/**
	 * Returns non-localised event description with id's for admin use only.
	 *
	 * @return string
	 */
	public function get_description() {
		$other = $this->other;

		if (array_key_exists('phone2', $other)) {
			$phone2 = $other['phone2'];
		}

		if (array_key_exists('noidung', $other)) {
			$noidung = $other['noidung'];
		}

		if (array_key_exists('response', $other)) {
			$response = $other['response'];
		}

		return "The user with id '$this->userid' sended sms to the user with id '$this->relateduserid'.<br> <b>Phone2: </b>$phone2.<br> <b>Noi dung: </b> $noidung.<br> <b>Response: </b> $response";
	}

	/**
	 * Get URL related to the action.
	 *
	 * @return \moodle_url
	 */
	public function get_url() {
		return new \moodle_url('/user/view.php', array('id' => $this->objectid));
	}
}
