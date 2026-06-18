<?php
defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'lib.php';
global $CFG;

class up_form extends moodleform {

    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $link = "<a href='example.csv'>example.csv</a>";
        $mform->addElement('static', 'example', get_string('examplecsv', 'local_th_register'), $link);

        $mform->addElement('filepicker', 'list', get_string('file'));
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

class th_register_add_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);

        $mform->addElement('text', 'ma_dot_mo_mon', get_string('ma_dot_mo_mon', 'local_th_register'));
        //, array('disabled' => '', 'readonly' => '')
        $mform->setType('ma_dot_mo_mon', PARAM_RAW);

        $mform->addRule('ma_dot_mo_mon', '', 'required', null, 'client', false, false);

        $mform->addElement('text', 'ten_dot_mo', get_string('ten_dot_mo_mon', 'local_th_register'));
        $mform->setType('ten_dot_mo', PARAM_RAW);
        $mform->addRule('ten_dot_mo', '', 'required', null, 'client', false, false);

        // $mform->addElement('text', 'hoc_phi_mot_tin_chi', get_string('hoc_phi_mot_tin_chi', 'local_th_register'));
        // $mform->setType('hoc_phi_mot_tin_chi', PARAM_RAW);
        // // $mform->setDefault('hoc_phi_mot_tin_chi', '3');
        // $mform->addRule('hoc_phi_mot_tin_chi', '', 'required', null, 'client', false, false);

        $mform->addElement('date_time_selector', 'tu_ngay', get_string('from'));
        $mform->addRule('tu_ngay', '', 'required', null, 'client', false, false);

        $mform->addElement('date_time_selector', 'den_ngay', get_string('to'));
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 month');
        $mform->setDefault('den_ngay', $date->getTimestamp());
        $mform->addRule('den_ngay', '', 'required', null, 'client', false, false);
        $this->add_action_buttons(true, get_string('submit'));
    }

    public function validation($data, $files) {
        global $DB;

        if (strlen($data['ma_dot_mo_mon']) > 45) {
            return array('ma_dot_mo_mon' => get_string('batch_opening_code_exceed', 'local_th_register'));
        }
        if (substr_count($data['ma_dot_mo_mon'], " ")) {
            return array('ma_dot_mo_mon' => get_string('batch_opening_code_space', 'local_th_register'));
        }
        if (strlen($data['ten_dot_mo']) > 45) {
            return array('ten_dot_mo' => get_string('batch_opening_name_exceed', 'local_th_register'));
        }
        if ($data['id'] == 0 && $DB->record_exists('th_dot_mo_mon', array('ma_dot_mo_mon' => $data['ma_dot_mo_mon']))) {
            return array('ma_dot_mo_mon' => get_string('batch_opening_code_exists', 'local_th_register'));
        }
        if ($data['tu_ngay'] >= $data['den_ngay']) {
            return array('den_ngay' => get_string('batch_opening_enddate_invalid', 'local_th_register'));
        }

        $sql = "SELECT * FROM {th_dot_mo_mon} WHERE id != :id AND ma_dot_mo_mon = :ma_dot_mo_mon";
        $params = array('id' => $data['id'], 'ma_dot_mo_mon' => $data['ma_dot_mo_mon']);
        if ($data['id'] != 0 && $DB->record_exists_sql($sql, $params)) {
            return array('ma_dot_mo_mon' => get_string('batch_opening_code_exists', 'local_th_register'));
        }

        return array();
    }
}

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
