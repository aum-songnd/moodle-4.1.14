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
 * Version details
 *
 * @package   block_th_course_status
 * @copyright 2021 phamleminh1812@gmail.com (Coventry University)
 * @copyright
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_th_bulk_send_message\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/th_bulk_send_message/classes/th_messagelib.php';
require_once $CFG->dirroot . '/local/th_bulk_send_message/classes/th_manager.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_message_adhoc_task extends \core\task\adhoc_task {

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		global $DB, $CFG;

		mtrace("My ad-hoc task started");

		$data_message = $this->get_custom_data();

		mtrace(json_encode($data_message));

		foreach ($data_message as $data) {

			$message = new \core\message\message();
			$message->courseid = 1;
			$message->component = 'moodle';
			$message->name = 'instantmessage';
			$message->userfrom = $data->userfrom;
			$message->userto = $data->userto;
			$message->subject = $data->subject;
			$message->fullmessage = $data->fullmessage;
			$message->fullmessageformat = FORMAT_HTML;
			$message->fullmessagehtml = $data->fullmessage;
			$message->smallmessage = $data->fullmessage;
			$message->notification = 0;

			message_send1($message);

			$user_from = $DB->get_record('user', array('id' => $data->userfrom));
			$userto = $DB->get_record('user', array('id' => $data->userto));

			$result = email_to_user($userto, $user_from, $data->subject, $data->fullmessage);

			$data_log = $data->data_log;
			require_once($CFG->dirroot . '/local/th_bulk_send_message/lib.php');

			$data_log->date = time();
			$data_log->sender = $user_from->email;
			$data_log->receiver = $userto->email;
			$data_log->receiverusername = $userto->username;
			$data_log->receiverstudentcode = local_th_bulk_send_message_get_studentcode((int)$userto->id);
			$data_log->subject = $data->subject;
			$data_log->body = $data->fullmessage;
			//save log send message
			if (!empty($result)) {
				$data_log->status = 1;
				$DB->insert_record('th_bulk_send_message', $data_log);
			} else {
				$data_log->status = 0;
				$DB->insert_record('th_bulk_send_message', $data_log);
			}
		}

		mtrace("My ad-hoc task started");
	}
}
