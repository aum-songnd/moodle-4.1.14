<?php

namespace local_th_study_reminder\task;

require_once $CFG->dirroot . '/local/th_study_reminder/locallib.php';

defined('MOODLE_INTERNAL') || die();

class send_study_reminder extends \core\task\scheduled_task {

	public static function send() {
        global $DB;

        send_study_reminder_notification();
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname_study_reminder', 'local_th_study_reminder');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
        if (!get_config('local_th_study_reminder', 'enable_notifications')) {
            mtrace('Notifications are disabled. Skipping task execution.');
            return;
        }

		self::send();
	}
}
