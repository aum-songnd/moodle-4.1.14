<?php
namespace local_th_framework_study\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class subjectcode_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $id = $this->_customdata['id'] ?? 0;

        // Hidden id (khi edit)
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Mã hiện tại
        $mform->addElement('text', 'current_code', get_string('currentcode', 'local_th_framework_study'));
        $mform->setType('current_code', PARAM_TEXT);
        $mform->addRule('current_code', null, 'required', null, 'client');

        // Mã cũ
        $mform->addElement('text', 'old_code', get_string('oldcode', 'local_th_framework_study'));
        $mform->setType('old_code', PARAM_TEXT);
        $mform->addRule('old_code', null, 'required', null, 'client');

        // Buttons
        $this->add_action_buttons(true, $id ? 'Cập nhật' : 'Thêm mới');
    }

    public function validation($data, $files) {
        global $DB;

        $errors = [];

        if ($DB->record_exists('th_subject_code_mapping', [
            'current_code' => $data['current_code'],
            'old_code'     => $data['old_code']
        ])) {
            $errors['old_code'] = 'Mapping đã tồn tại';
        }

        return $errors;
    }
}
