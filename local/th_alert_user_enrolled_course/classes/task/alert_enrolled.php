<?php

namespace local_th_alert_user_enrolled_course\task;

require_once $CFG->libdir . '/datalib.php';

defined('MOODLE_INTERNAL') || die();

class alert_enrolled extends \core\task\scheduled_task {

	public static function alert() {
		global $CFG, $DB;

		$option = get_config('th_alert_user_enrolled_course', 'alert_user_enrolled');
		$hotline = get_config('local_thlib')->technical_hotline;

		if ($option == 1) {
			$time_start = strtotime("today 00:00");
			$time_end = strtotime("today 24:00");

			$ds_users = $DB->get_records_sql("SELECT DISTINCT ue.userid FROM {user_enrolments} as ue, {enrol} as e WHERE ue.enrolid = e.id AND ue.timecreated >= $time_start AND ue.timecreated < $time_end");

			foreach ($ds_users as $user) {
				$userid = $user->userid;
				$user = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$userid");
				$fullname = $user->firstname . ' ' . $user->lastname;
				$ds_enrolled = $DB->get_records_sql("SELECT ue.id, e.courseid FROM {user_enrolments} as ue, {enrol} as e WHERE ue.enrolid = e.id AND ue.timecreated >= $time_start AND ue.timecreated < $time_end AND ue.userid = $userid AND ue.status = 0");
				$fragment = "";
				$stt = 0;
				foreach ($ds_enrolled as $enrol) {
					$stt++;
					$course_id = $enrol->courseid;
					$courselink = $CFG->wwwroot . "/course/view.php?id=" . $course_id;
					$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id=$course_id");

					$fragment = $fragment . get_string_manager()->get_string('mailbody_fragment1', 'local_th_alert_user_enrolled_course',
						[
							'stt' => $stt,
							'coursefullname' => $course->fullname,
							'courselink' => $courselink,
						],
						$user->lang);
				}

				if ($stt > 0) {

					$fullname = $user->firstname . ' ' . $user->lastname;

					$body = get_string_manager()->get_string('mailsubject_mode2', 'local_th_alert_user_enrolled_course',
						[
							'fullname' => $fullname,
							'fragment1' => $fragment,
							'hotline' => $hotline,
						],
						$user->lang);

					$supportuser = \core_user::get_support_user();
					$subject = get_string('enrollment_notification', 'local_th_alert_user_enrolled_course');
					email_to_user($user, $supportuser, $subject, $body);
				}
			}
		}
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname', 'local_th_alert_user_enrolled_course');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::alert();
	}
}

?>