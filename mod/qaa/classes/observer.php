<?php

require_once $CFG->dirroot . '/local/thlib/lib.php';

defined('MOODLE_INTERNAL') || die();

class mod_qaa_observer {

	/**
	 * Store all actions about modules create/update/delete in own table.
	 *
	 * @param \core\event\base $event
	 */
	public static function on_question_created($event) {

		$data = array(
			'courseid' => $event->courseid,
			'userid' => $event->userid,
			'contextinstanceid' => $event->contextinstanceid,
			'id' => $event->objectid,
		);

		$task = new mod_qaa\task\question_created_adhoc_task();
		$task->set_custom_data($data);
		\core\task\manager::queue_adhoc_task($task, true);
	}

	public static function on_question_updated($event) {

		$data = array(
			'courseid' => $event->courseid,
			'userid' => $event->userid,
			'contextinstanceid' => $event->contextinstanceid,
			'id' => $event->objectid,
		);

		$task = new mod_qaa\task\question_updated_adhoc_task();
		$task->set_custom_data($data);
		\core\task\manager::queue_adhoc_task($task, true);
	}

	public static function on_answer_created($event) {

		$data = array(
			'courseid' => $event->courseid,
			'userid' => $event->userid,
			'contextinstanceid' => $event->contextinstanceid,
			'id' => $event->objectid,
		);

		$task = new mod_qaa\task\answer_created_adhoc_task();
		$task->set_custom_data($data);
		\core\task\manager::queue_adhoc_task($task, true);
	}

	public static function on_answer_updated($event) {

		$data = array(
			'courseid' => $event->courseid,
			'userid' => $event->userid,
			'contextinstanceid' => $event->contextinstanceid,
			'id' => $event->objectid,
		);

		$task = new mod_qaa\task\answer_updated_adhoc_task();
		$task->set_custom_data($data);
		\core\task\manager::queue_adhoc_task($task, true);
	}

	public static function on_question_reject($event) {

		$data = array(
			'courseid' => $event->courseid,
			'userid' => $event->userid,
			'contextinstanceid' => $event->contextinstanceid,
			'id' => $event->objectid,
		);

		$task = new mod_qaa\task\question_reject_adhoc_task();
		$task->set_custom_data($data);
		\core\task\manager::queue_adhoc_task($task, true);
	}
}
