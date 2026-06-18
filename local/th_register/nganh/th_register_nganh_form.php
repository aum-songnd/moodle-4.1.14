<?php
defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

class up_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $link = "<a href='nganh.csv'>example.csv</a>";
        $mform->addElement('static', 'example', get_string('examplecsv', 'local_th_register'), $link);

        $mform->addElement('filepicker', 'list', get_string('file'), null,
            array(
                    'accepted_types' => '.csv',
                    'areamaxbytes' => 100000000,
                    'maxfiles' => 1,
                )
        );
        $mform->addRule('list', get_string('required'), 'required', null, 'client');

        $options = array(
            '0' => get_string('add_new_only', 'local_th_register'),
            '1' => get_string('update_existing_only', 'local_th_register'),
            '2' => get_string('add_new_and_update_existing', 'local_th_register'),
        );
        $select = $mform->addElement('select', 'option', get_string('option', 'local_th_register'), $options);
        $select->setSelected('0');

        $this->add_action_buttons(true, get_string('submit'));
        // $mform->addElement('static', 'note', get_string('note', 'local_th_register'), get_string('description', 'local_th_register'));
    }
}

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_register_add_form extends moodleform {
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);

        $mform->addElement('text', 'ma_nganh', get_string('ma_nganh', 'local_th_register'));
        $mform->setType('ma_nganh', PARAM_RAW);
        $mform->addRule('ma_nganh', '', 'required', null, 'client', false, false);
        $mform->addElement('text', 'ten_nganh', get_string('ten_nganh', 'local_th_register'));
        $mform->setType('ten_nganh', PARAM_RAW);
        $mform->addRule('ten_nganh', '', 'required', null, 'client', false, false);

        $mform->addElement('text', 'hoc_phi_mot_tin_chi', get_string('hoc_phi_mot_tin_chi', 'local_th_register'));
        $mform->setType('hoc_phi_mot_tin_chi', PARAM_RAW);
        // $mform->setDefault('hoc_phi_mot_tin_chi', '3');
        $mform->addRule('hoc_phi_mot_tin_chi', '', 'required', null, 'client', false, false);

        $this->add_action_buttons(true, get_string('submit'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = array();
    
        if (strlen($data['ma_nganh']) > 10) {
            $errors['ma_nganh'] = get_string('error_ma_nganh_length', 'local_th_register');
        }
        if (strlen($data['ten_nganh']) > 45) {
            $errors['ten_nganh'] = get_string('error_ten_nganh_length', 'local_th_register');
        }
        if ($data['id'] == 0 && $DB->record_exists('th_nganh_hoc', array('ma_nganh' => $data['ma_nganh']))) {
            $errors['ma_nganh'] = get_string('error_ma_nganh_exists', 'local_th_register');
        }
    
        if (!is_numeric($data['hoc_phi_mot_tin_chi'])) {
            $errors['hoc_phi_mot_tin_chi'] = get_string('error_hoc_phi_numeric', 'local_th_register');
        }
        if ($data['hoc_phi_mot_tin_chi'] <= 0) {
            $errors['hoc_phi_mot_tin_chi'] = get_string('error_hoc_phi_positive', 'local_th_register');
        }
        if (strlen($data['hoc_phi_mot_tin_chi']) > 10) {
            $errors['hoc_phi_mot_tin_chi'] = get_string('error_hoc_phi_length', 'local_th_register');
        }
    
        $sql = "SELECT * FROM {th_nganh_hoc} WHERE id != :id AND ma_nganh = :ma_nganh";
        if ($data['id'] != 0 && $DB->record_exists_sql($sql, array('id' => $data['id'], 'ma_nganh' => $data['ma_nganh']))) {
            $errors['ma_nganh'] = get_string('error_ma_nganh_exists', 'local_th_register');
        }
        return $errors;
    }
    
}

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';
require_once 'lib.php';

class confirm_form extends moodleform {

    protected function definition() {
        global $SESSION;

        $th_register_csvkey = $this->_customdata['th_register_csvkey'];

        $mform = $this->_form;

        $mform->addElement('hidden', 'key');
        $mform->setType('key', PARAM_RAW);
        $mform->setDefault('key', $th_register_csvkey);

        $showbutton = true;
        $check = null;
        if (isset($SESSION->local_th_register_csv) && array_key_exists($th_register_csvkey, $SESSION->local_th_register_csv)) {
            $check = $SESSION->local_th_register_csv[$th_register_csvkey];
            if (isset($check->valid_time_found) && empty($check->valid_time_found)) {
                $showbutton = false;
            }
        }

        if ($showbutton) {

            $buttonstring = get_string('submit');

            $this->add_action_buttons(true, $buttonstring);
        }
    }
}
