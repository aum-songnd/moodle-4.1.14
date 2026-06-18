<?php
namespace local_th_framework_study\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class edit_framework_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $enable_train_code = get_config('local_th_framework_study', 'enable_train_code');
        $skip = get_config('local_th_framework_study', 'skip_slot_target');


        // Hidden ID
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Slot
        $mform->addElement('text', 'slot', get_string('slot', 'local_th_framework_study'));
        $mform->setType('slot', PARAM_RAW_TRIMMED);
        $mform->setDefault('slot', '');
     
        // Subject name
        $mform->addElement('text', 'subject_name', get_string('subjectname', 'local_th_framework_study'));
        $mform->setType('subject_name', PARAM_RAW_TRIMMED);
        $mform->addRule('subject_name', null, 'required', null, 'client');

        // Subject code
        $mform->addElement('text', 'subject_code', get_string('subjectcode', 'local_th_framework_study'));
        $mform->setType('subject_code', PARAM_RAW_TRIMMED);
        $mform->addRule('subject_code', null, 'required', null, 'client');

        // Credit (INT)
        $mform->addElement('text', 'credit', get_string('credit', 'local_th_framework_study'));
        $mform->setType('credit', PARAM_RAW_TRIMMED);
        $mform->setDefault('credit', '');        

        // Major code
        $mform->addElement('text', 'major_code', get_string('majorcode', 'local_th_framework_study'));
        $mform->setType('major_code', PARAM_RAW_TRIMMED);

        //target code
        $mform->addElement('text', 'target_code', get_string('targetcode', 'local_th_framework_study'));
        $mform->setType('target_code', PARAM_RAW_TRIMMED);
        $mform->setDefault('target_code', '');

        if ($enable_train_code) {
            $mform->addElement('text', 'training_type_code', get_string('training_type_code', 'local_th_framework_study'));
            $mform->setType('training_type_code', PARAM_RAW_TRIMMED);
            $mform->setDefault('training_type_code', '');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;
    
        $errors = [];

        $skip = get_config('local_th_framework_study', 'skip_slot_target');

        if (
            !$skip &&
            $data['slot'] !== '' &&
            !ctype_digit((string)$data['slot'])
        ) {
            $errors['slot'] = get_string('error_slot_numeric', 'local_th_framework_study');
        }
        
        if (
            !$skip &&
            $data['target_code'] !== '' &&
            mb_strlen($data['target_code']) > 100
        ) {
            $errors['target_code'] = get_string('error_target_code_max', 'local_th_framework_study');
        }
        

        // Tên môn không được để trống
        if (trim($data['subject_name']) === '') {
            $errors['subject_name'] = get_string('error_subject_name_required', 'local_th_framework_study');
        }
    
        // Mã môn không được để trống
        if (trim($data['subject_code']) === '') {
            $errors['subject_code'] = get_string('error_subject_code_required', 'local_th_framework_study');
        }

        if ($skip) {
            // Skip → cho phép trống hoặc số nguyên >= 1
            if (
                $data['credit'] !== '' &&
                !ctype_digit((string)$data['credit'])
            ) {
                $errors['credit'] = get_string('error_credit_numeric', 'local_th_framework_study');
            }
        } else {
            // Không skip → bắt buộc là số nguyên >= 1
            if (
                $data['credit'] === '' ||
                !ctype_digit((string)$data['credit']) ||
                (int)$data['credit'] < 1
            ) {
                $errors['credit'] = get_string('error_credit_numeric', 'local_th_framework_study');
            }
        }

    
        // Chiều dài tên môn
        if (mb_strlen($data['subject_name']) > 255) {
            $errors['subject_name'] = get_string('error_subject_name_max', 'local_th_framework_study');
        }
    
        // Chiều dài mã môn
        if (mb_strlen($data['subject_code']) > 100) {
            $errors['subject_code'] = get_string('error_subject_code_max', 'local_th_framework_study');
        }
    
        // Chiều dài mã ngành
        if (!empty($data['major_code']) && mb_strlen($data['major_code']) > 100) {
            $errors['major_code'] = get_string('error_major_code_max', 'local_th_framework_study');
        }
    
        return $errors;
    }
    
}
