<?php

defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_register_add_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $COURSE, $USER;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $attributes = array('maxlength' => "10", 'style' => 'max-width: 100px !important;');
        // $attributes = array();
        $mform->addElement('text', 'so_tien_da_nop', get_string('paid_amount_vnd', 'local_th_register'), $attributes);
        $mform->setType('so_tien_da_nop', PARAM_TEXT);
        $mform->addRule('so_tien_da_nop', '', 'required', null, 'client', false, false);
        $maxbytes = get_config('local_th_register', 'maxbytes');
        $mform->addElement('filemanager', 'attachments', get_string('invoice', 'local_th_register'), null,
            array(
                'accepted_types' => array('.jpg', '.png', '.jpeg'),
                'areamaxbytes' => $maxbytes,
                'maxfiles' => 10,
            )
        );
        $mform->addRule('attachments', '', 'required', null, 'client', false, false);
        $this->add_action_buttons(true, get_string('submit'));
    }

    public function validation($data, $files) {
        global $DB;

        $data['so_tien_da_nop'] = str_replace(',', '', $data['so_tien_da_nop']);
        if (!is_numeric($data['so_tien_da_nop']) OR $data['so_tien_da_nop'] == 0) {
            return array('so_tien_da_nop' => get_string('invalid_amount', 'local_th_register'));
        }

        if (strlen($data['so_tien_da_nop']) > 10) {
            return array('so_tien_da_nop' => get_string('invalid_amount', 'local_th_register'));
        }
        return array();
    }
}
