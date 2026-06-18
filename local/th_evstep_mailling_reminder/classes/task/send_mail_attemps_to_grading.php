<?php

namespace local_th_evstep_mailling_reminder\task;

require_once $CFG->dirroot . '/local/th_evstep_mailling_reminder/locallib.php';

defined('MOODLE_INTERNAL') || die();

class send_mail_attemps_to_grading extends \core\task\scheduled_task {

	public static function send() {
		th_send_mail_attemps_to_grading();
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname_attemps_to_grading', 'local_th_evstep_mailling_reminder');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::send();
	}
}
