<?php
require_once("$CFG->libdir/formslib.php");

class th_essay_checker_report_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Thêm radio buttons để chọn kiểu lọc
        $radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'filter_type', '', get_string('course', 'local_th_essay_checker_report'), 1);
		$radioarray[] = &$mform->createElement('radio', 'filter_type', '', get_string('startdatecourse', 'local_th_essay_checker_report'), 2);
		$mform->addGroup($radioarray, 'show_option', 'Tìm kiếm theo', array(''), false);

		$mform->setDefault('filter_type', 1);

        // Add course autocomplete field (allow multiple selection).
        $mform->addElement('autocomplete', 'courseids', get_string('selectcourse', 'local_th_essay_checker_report'), $this->get_courses(), [
            'multiple' => true, // Allow multiple selection.
            'noselectionstring' => get_string('nocourseselected', 'local_th_essay_checker_report') // Placeholder text.
        ]);
        $mform->setType('courseids', PARAM_SEQUENCE); // Expect a comma-separated list of IDs.


        // Add start date field.
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_th_essay_checker_report'));
        $mform->setType('startdate', PARAM_INT);

        // Add end date field.
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_th_essay_checker_report'));
        $mform->setType('enddate', PARAM_INT);

        // Sử dụng hideif và disableif để ẩn hoặc vô hiệu hóa trường
        $mform->hideIf('startdate', 'filter_type', 'neq', 2);
        $mform->hideIf('enddate', 'filter_type', 'neq', 2);
        $mform->hideIf('courseids', 'filter_type', 'neq', 1);
        $mform->disabledIf('courseids', 'filter_type', 'eq', 2);
        $mform->disabledIf('startdate', 'filter_type', 'eq', 1);
        $mform->disabledIf('enddate', 'filter_type', 'eq', 1);

        // Add submit button.
        $mform->addElement('submit', 'submitbutton', get_string('submit', 'local_th_essay_checker_report'));
    }

    private function get_courses() {
        global $DB;
        // SQL query to get visible courses excluding course with id = 1
        $sql = "
            SELECT id, CONCAT(fullname, ',', shortname)
            FROM {course}
            WHERE visible = 1 AND id != 1
            ORDER BY fullname ASC
        ";

        // Get the courses using get_records_sql_menu
        $courses = $DB->get_records_sql_menu($sql, array());
        return $courses;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate start date and end date
        if ($data['filter_type'] == 2) {
            if (!empty($data['startdate']) && !empty($data['enddate'])) {
                if ($data['startdate'] > $data['enddate']) {
                    $errors['enddate'] = get_string('enddateerror', 'local_th_essay_checker_report');
                }
            }
        }

        return $errors;
    }
}
