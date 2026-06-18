<?php

namespace block_th_grades_approval\task;

require_once $CFG->libdir . '/datalib.php';
require_once($CFG->dirroot.'/blocks/th_grades_approval/classes/lib.php');

defined('MOODLE_INTERNAL') || die();

class send_mail_grades_approval extends \core\task\scheduled_task {
	
	public static function send_mail_approval() {
		global $CFG, $DB;
		
		mtrace("My task started");

		send_mail_grades_approval();
		
		mtrace("My task finished");
		
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname', 'block_th_grades_approval');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::send_mail_approval();
	}
}

?>