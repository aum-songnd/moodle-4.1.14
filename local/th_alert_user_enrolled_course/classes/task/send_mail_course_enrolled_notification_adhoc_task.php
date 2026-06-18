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

namespace local_th_alert_user_enrolled_course\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_mail_course_enrolled_notification_adhoc_task extends \core\task\adhoc_task {

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		global $CFG, $DB, $SITE;
		// require_once "{$CFG->dirroot}/course/lib.php";
		$hotline = get_config('local_thlib')->technical_hotline;

		mtrace(get_string('adhoc_task_started', 'local_th_alert_user_enrolled_course'));

		$data = $this->get_custom_data();
		$relateduserid = $data->relateduserid;
		$courseid = $data->courseid;

		$stringparams = \thlib::get_string_params();

		$universityshortname = $SITE->shortname;

		$supportuser = \core_user::get_support_user();
		$user = $DB->get_record('user', array('id' => $relateduserid));
		$course = $DB->get_record('course', array('id' => $courseid));
		$startdate = $course->startdate;
		$coursefullname = $course->fullname;
		$courselink = $CFG->wwwroot . "/course/view.php?id=" . $course->id;
		$coursevisible = $course->visible;

		$subject = get_string_manager()->get_string('mailsubject', 'local_th_alert_user_enrolled_course', null, $user->lang);

		$stringparams['coursename'] = $coursefullname;
		$stringparams['courselink'] = $courselink;
		$stringparams['username'] = fullname($user);

		$message = get_string_manager()->get_string('mailbody', 'local_th_alert_user_enrolled_course', $stringparams, $user->lang);

		email_to_user($user, $supportuser, $subject, $message);
		mtrace(get_string('adhoc_task_started', 'local_th_alert_user_enrolled_course'));
	}
}
