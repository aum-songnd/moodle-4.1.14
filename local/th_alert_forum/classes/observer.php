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
class local_th_alert_forum_observer {

    /**
     * Store all actions about modules create/update/delete in own table.
     *
     * @param \core\event\base $event
     */
    public static function on_post_created($event) {

        global $DB, $CFG;
        $config = get_config("local_th_alert_forum");
        if ($config->type_send == "event") {
            $userid = $event->userid;
            $courseid = $event->courseid;
            $objectid = $event->objectid;
            $objecttable = $event->objecttable;

            $record = $DB->get_record($objecttable, array('id' => $objectid));
            $subject = $record->subject;
            $message = $record->message;
            $discussion = $record->discussion;
            $time = $record->created;
            //$forum = $record->forum;
            // $next_runtime = 0;
            $data = array(
                'courseid' => $courseid,
                'userid' => $userid,
                'table' => $objecttable,
                'objectid' => $objectid,
                'subject' => $subject,
                'message' => $message,
                'discussion' => $discussion,
                'time' => $time,
                //'forum' => $forum,
            );

            $task = new local_th_alert_forum\task\send_mail_forum_notification_adhoc_task();
            // $task->set_next_run_time($next_runtime);
            $task->set_custom_data($data);
            \core\task\manager::reschedule_or_queue_adhoc_task($task);
        }
    }

    public static function on_post_updated($event) {

        global $DB, $CFG;
        $config = get_config("local_th_alert_forum");
        if ($config->type_send == "event") {
            $userid = $event->userid;
            $courseid = $event->courseid;
            $objectid = $event->objectid;
            $objecttable = $event->objecttable;

            // $sql = "SELECT fp.*,df.forum
            //         FROM {forum_discussions} df JOIN {forum_posts} fp
            //         ON df.id=fp.discussion WHERE fp.id=?";
            // $params = array($objectid);
            // $record = $DB->get_record_sql($sql, $params);

            $record = $DB->get_record($objecttable, array('id' => $objectid));
            $subject = $record->subject;
            $message = $record->message;
            $discussion = $record->discussion;
            $time = $record->modified;
            //$forum = $record->forum;
            // $next_runtime = 0;
            $data = array(
                'courseid' => $courseid,
                'userid' => $userid,
                'table' => $objecttable,
                'objectid' => $objectid,
                'subject' => $subject,
                'message' => $message,
                'discussion' => $discussion,
                'time' => $time,
                //'forum' => $forum,
            );

            $task = new local_th_alert_forum\task\send_mail_forum_notification_adhoc_task();
            // $task->set_next_run_time($next_runtime);
            $task->set_custom_data($data);
            \core\task\manager::reschedule_or_queue_adhoc_task($task);
        }
    }

    public static function on_discussion_created($event) {

        global $DB, $CFG;
        $config = get_config("local_th_alert_forum");
        if ($config->type_send == "event") {
            $userid = $event->userid;
            $courseid = $event->courseid;
            $objecttable = $event->objecttable;
            $objectid = $event->objectid;
            $objecttable = 'forum_posts';

            $record = $DB->get_record($objecttable, array('discussion' => $objectid));

            // $sql = "SELECT fp.*,df.forum
            //         FROM {forum_discussions} df JOIN {forum_posts} fp
            //         ON df.id=fp.discussion WHERE fp.discussion=?";
            // $params = array($objectid);
            // $record = $DB->get_record_sql($sql, $params);

            $objectid = $record->id;
            $subject = $record->subject;
            $message = $record->message;
            $discussion = $record->discussion;
            $time = $record->created;
            //$forum = $record->forum;
            // $next_runtime = 0;
            $data = array(
                'courseid' => $courseid,
                'userid' => $userid,
                'table' => $objecttable,
                'objectid' => $objectid,
                'subject' => $subject,
                'message' => $message,
                'discussion' => $discussion,
                'time' => $time,
                //'forum' => $forum,
            );

            $task = new local_th_alert_forum\task\send_mail_forum_notification_adhoc_task();
            // $task->set_next_run_time($next_runtime);
            $task->set_custom_data($data);
            \core\task\manager::reschedule_or_queue_adhoc_task($task);
        }
    }
}
