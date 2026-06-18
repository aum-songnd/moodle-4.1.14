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
 * Event observer.
 *
 * @package    block_recent_activity
 * @copyright  2021 phamleminh1812@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once $CFG->dirroot . '/local/thlib/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer.
 * Stores all actions about modules create/update/delete in plugin own's table.
 * This allows the block to avoid expensive queries to the log table.
 *
 * @package    block_recent_activity
 * @copyright  2021 phamleminh1812@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_th_alert_user_enrolled_course_observer {

	/**
	 * Store all actions about modules create/update/delete in own table.
	 *
	 * @param \core\event\base $event
	 */
	public static function on_user_enrolled(\core\event\base $event) {

		global $DB, $CFG;

		$option = get_config('th_alert_user_enrolled_course', 'alert_user_enrolled');

		if($option == 0) {
			$relateduserid = $event->relateduserid;
			$courseid = $event->courseid;

			$course = $DB->get_record('course', array('id' => $courseid));
			$startdate = $course->startdate;
			$coursevisible = $course->visible;

			if ($coursevisible == 1) {
				$next_runtime = time() + 60;
			} else {
				$next_runtime = $startdate;
			}

			$task = new local_th_alert_user_enrolled_course\task\send_mail_course_enrolled_notification_adhoc_task();
			$task->set_next_run_time($next_runtime);
			$task->set_custom_data(array('courseid' => $courseid, 'relateduserid' => $relateduserid));
			\core\task\manager::reschedule_or_queue_adhoc_task($task);
		}
	}

	public static function on_course_updated(\core\event\base $event) {

		global $DB, $CFG;

		$option = get_config('th_alert_user_enrolled_course', 'alert_user_enrolled');

		if($option == 0) {
			$sql = "SELECT *
			from {task_adhoc}
			where " . $DB->sql_like('customdata', ':data');

			$courseid = $event->courseid;
			$course = $DB->get_record('course', array('id' => $courseid));
			$coursevisible = $course->visible;
			if (!$coursevisible) {
				$startdate = $course->startdate;
				$data = json_encode(array('courseid' => $courseid));
				$data = ltrim($data, "{");
				$data = rtrim($data, "}");

				$params = ['data' => "%$data%"];

				$records = $DB->get_records_sql($sql, $params);
				// print_object($records);
				foreach ($records as $key => $record) {
					$task = \core\task\manager::adhoc_task_from_record($record);
					$task->set_next_run_time($startdate);
					\core\task\manager::reschedule_or_queue_adhoc_task($task);
				}
			}
		}
	}
}
