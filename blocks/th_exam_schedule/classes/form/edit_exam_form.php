<?php
namespace block_th_exam_schedule\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class edit_exam_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'h', get_string('edit'));

        // hidden id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // studentcode
        $mform->addElement('text', 'studentcode', get_string('studentcode', 'block_th_exam_schedule'), ['size' => 32]);
        $mform->setType('studentcode', PARAM_RAW_TRIMMED);
        $mform->addRule('studentcode', get_string('required'), 'required', null, 'client');

        // coursecode
        $mform->addElement('text', 'coursecode', get_string('coursecode', 'block_th_exam_schedule'), ['size' => 32]);
        $mform->setType('coursecode', PARAM_RAW_TRIMMED);
        $mform->addRule('coursecode', get_string('required'), 'required', null, 'client');

        // examdate 
        $mform->addElement('date_selector', 'examdate', get_string('col_examdate', 'block_th_exam_schedule'));
        $mform->addRule('examdate', get_string('required'), 'required', null, 'client');

        // examslot
        $mform->addElement('text', 'examslot', get_string('col_examslot', 'block_th_exam_schedule'), ['size' => 16]);
        $mform->setType('examslot', PARAM_RAW_TRIMMED);
        $mform->addRule('examslot', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (trim((string)$data['studentcode']) === '') {
            $errors['studentcode'] = get_string('required');
        }
        if (trim((string)$data['coursecode']) === '') {
            $errors['coursecode'] = get_string('required');
        }
        if (empty($data['examdate'])) {
            $errors['examdate'] = get_string('required');
        }
        if (trim((string)$data['examslot']) === '') {
            $errors['examslot'] = get_string('required');
        }

        return $errors;
    }
}
