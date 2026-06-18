<?php

namespace mod_qaa\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/mod/qaa/lib.php';

defined('MOODLE_INTERNAL') || die();

class question_created_adhoc_task extends \core\task\adhoc_task {

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		global $CFG, $DB;

		mtrace(get_string('runtask', 'mod_qaa'));

		$data = $this->get_custom_data();
		$id = $data->id;
		$userid = $data->userid;
		$courseid = $data->courseid;
		$cmid = $data->contextinstanceid;

		mod_qaa_event_send_email($id, $userid, $courseid, $cmid, 'typequestion');

		mtrace(get_string('runtask', 'mod_qaa'));
	}
}
