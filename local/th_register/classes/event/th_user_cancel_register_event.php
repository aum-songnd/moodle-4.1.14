<?php

namespace local_th_register\event;

defined('MOODLE_INTERNAL') || die();

class th_user_cancel_register_event extends \core\event\base {
	protected function init() {
		$this->data['crud'] = 'd';
		$this->data['edulevel'] = self::LEVEL_OTHER;
	}

	public static function get_name() {
		return get_string('event_cancel_course_registration', 'local_th_register');
	}

	/**
	 * Returns non-localised event description with id's for admin use only.
	 *
	 * @return string
	 */
	public function get_description() {
		$a = new \stdClass();
		$a->userid = $this->userid;
		$a->courses = json_encode($this->other, JSON_UNESCAPED_UNICODE);
	
		return get_string('event_cancel_course_registration_desc', 'local_th_register', $a);
	
	}
}

?>