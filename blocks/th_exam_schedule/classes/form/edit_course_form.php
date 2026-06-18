<?php
namespace block_th_exam_schedule\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class edit_course_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'h', get_string('edit'));

        // hidden id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // coursecode
        $mform->addElement('text', 'coursecode', get_string('col_coursecode', 'block_th_exam_schedule'), ['size' => 32]);
        $mform->setType('coursecode', PARAM_RAW_TRIMMED);
        $mform->addRule('coursecode', get_string('required'), 'required', null, 'client');

        // coursename
        $mform->addElement('text', 'coursename', get_string('col_coursename', 'block_th_exam_schedule'), ['size' => 64]);
        $mform->setType('coursename', PARAM_RAW_TRIMMED);
        $mform->addRule('coursename', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (trim((string)$data['coursecode']) === '') {
            $errors['coursecode'] = get_string('required');
        }
        if (trim((string)$data['coursename']) === '') {
            $errors['coursename'] = get_string('required');
        }

        return $errors;
    }
}
