<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';

class select_courses_to_send_notification_form extends moodleform {
	//Add elements to form
	public function definition() {
		$mform = $this->_form;

        global $DB;
        $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE id != 1 AND visible = 1');
        $courses_arr = [];
        foreach ($courses as $course) {
            $courses_arr[$course->id] = $course->fullname;
        }

        $selected_courses = $this->get_selected_courses();

        $mform->addElement('header', 'displayinfo', get_string('textfields', 'local_th_study_reminder'));

        $options_course = [
            'multiple' => true,
            'noselectionstring' => get_string('chooseacourse', 'local_th_study_reminder'),
        ];

        $mform->addElement('autocomplete', 'courses', get_string('chooseacourse', 'local_th_study_reminder'), $courses_arr, $options_course);
        $mform->setDefault('courses', $selected_courses);
        $mform->setType('courses', PARAM_SEQUENCE);

        $this->add_action_buttons(true, get_string('savechanges', 'local_th_study_reminder'));
	}

    private function get_selected_courses() {
        global $DB;

        $records = $DB->get_records('th_study_reminder');
        $selected = [];
        foreach ($records as $record) {
            $selected[] = $record->courseid;
        }
        return $selected;
    }
}