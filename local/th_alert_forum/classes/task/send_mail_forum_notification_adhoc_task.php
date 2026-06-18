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

namespace local_th_alert_forum\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/th_alert_forum/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_mail_forum_notification_adhoc_task extends \core\task\adhoc_task {

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		global $CFG, $DB;

		mtrace("My ad-hoc task started");

		$data = $this->get_custom_data();
		$userid = $data->userid;
		$courseid = $data->courseid;
		$table = $data->table;
		$objectid = $data->objectid;
		$subject = $data->subject;
		$message = $data->message;
		$discussion = $data->discussion;
		$time = $data->time;

		$sql = "SELECT df.forum
		     FROM {forum_discussions} df JOIN {forum_posts} fp
		     ON df.id=fp.discussion WHERE fp.id=?";
		$params = array($objectid);
		$record = $DB->get_record_sql($sql, $params);
		$forum = $record->forum;

		$userfrom = $DB->get_record('user', array('id' => $userid));
		$course = $DB->get_record('course', array('id' => $courseid));
		$shortname = $course->shortname;

		$link = "<p>
		<a href='" . $CFG->wwwroot . "/course/view.php?id=" . $courseid . "' target='_blank'>" . $shortname . "</a> » <a href='" . $CFG->wwwroot . "/mod/forum/index.php?id=" . $courseid . "' target='_blank'>Các diễn đàn</a> » <a href='" . $CFG->wwwroot . "/mod/forum/view.php?f=" . $forum . "' target='_blank'>Diễn đàn tin tức</a> » <a href='" . $CFG->wwwroot . "/mod/forum/discuss.php?d=" . $discussion . "' target='_blank'>" . $subject . "</a></p>";

		$tt = "<table border='0' cellpadding='3' cellspacing='0'>
    <tbody><tr>
        <td width='35' valign='top'>
            <a href='" . $CFG->wwwroot . "/user/view.php?id=" . $userid . "&amp;course=" . $courseid . "' target='_blank'><img src='https://secure.gravatar.com/avatar/99200137f6b23954ea217439151277fa?s=100&d=mm' width='35' height='35' alt='Picture of " . fullname($userfrom) . "' title='Picture of " . fullname($userfrom) . "' class='CToWUd'></a>
        </td>
        <td>
            <div>
                " . $subject . "
            </div>
            <div>
                by <a href='" . $CFG->wwwroot . "/user/view.php?id=" . $userid . "&amp;course=" . $courseid . "' target='_blank'>" . fullname($userfrom) . "</a> - " . date('l, d F Y, g:i A', $time) . "
            </div>
        </td>
    </tr>
    <tr>
        <td valign='top'>
                &nbsp;
        </td>
        <td>
            <p dir='ltr' style='text-align:left'></p><p>" . $message . "</p>
            <div>
                    <a href='" . $CFG->wwwroot . "/mod/forum/post.php?reply=" . $objectid . "' target='_blank'>
                        Reply
                    </a>
            </div>
            <div>
                <a href='" . $CFG->wwwroot . "/mod/forum/discuss.php?d=" . $discussion . "#p" . $objectid . "' target='_blank'>
                    See this post in context
                </a>
            </div>
        </td>
    </tr>
</tbody></table><hr>
<div>
				    <a href='" . $CFG->wwwroot . "/mod/forum/index.php?id=" . $courseid . "' target='_blank'>Change your forum digest preferences</a>
				</div>
				<p>Reading this in an email? <a href='https://play.google.com/store/search?q=email' target='_blank'>Download the mobile app and receive notifications on your mobile device</a>.</p>
		";
		$content = $link . $tt;

		$user = $DB->get_record('user', array('email' => "datdt720@wru.vn"));
		email_to_user1($user, $userfrom, $shortname . ": " . $subject, $content);
		mtrace("My ad-hoc task started");
	}
}
