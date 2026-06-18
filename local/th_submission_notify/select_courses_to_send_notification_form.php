<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class select_courses_to_send_notification_form extends moodleform {
	/**
     * Defines the form elements.
     */
	public function definition() {
		$mform = $this->_form;

        global $DB;

        // Get all visible courses, excluding site home (id = 1)
        $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE id != 1 AND visible = 1');

        $courses_arr = [];
        foreach ($courses as $course) {
            $courses_arr[$course->id] = $course->fullname;
        }

        // Fetch currently selected courses from DB
        $selected_courses = $this->get_selected_courses();
        
        // Section header
        $mform->addElement('header', 'displayinfo', get_string('textfields', 'local_th_submission_notify'));

        // Autocomplete course selection field
        $options_course = [
            'multiple' => true,
            'noselectionstring' => get_string('chooseacourse', 'local_th_submission_notify'),
        ];

        $mform->addElement('autocomplete', 'courses', get_string('chooseacourse', 'local_th_submission_notify'), $courses_arr, $options_course);
        $mform->setDefault('courses', $selected_courses);
        $mform->setType('courses', PARAM_SEQUENCE);

        // Add Save/Cancel buttons
        $this->add_action_buttons(true, get_string('savechanges', 'local_th_submission_notify'));
	}

    /**
     * Fetches selected course IDs from the database.
     *
     * @return array
     */
    private function get_selected_courses() {
        global $DB;

        $records = $DB->get_records('th_submission_notify_courses');
        $selected = [];
        foreach ($records as $record) {
            $selected[] = $record->courseid;
        }
        return $selected;
    }
}