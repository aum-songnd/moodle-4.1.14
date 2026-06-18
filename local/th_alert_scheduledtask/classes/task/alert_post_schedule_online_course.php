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
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @copyright
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_th_alert_scheduledtask\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/th_alert_scheduledtask/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class alert_post_schedule_online_course extends \core\task\scheduled_task {

	public static function alert() {
		alert_post_schedule_online_course();
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('alertpostscheduleonlinecourse', 'local_th_alert_scheduledtask');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::alert();
	}
}
