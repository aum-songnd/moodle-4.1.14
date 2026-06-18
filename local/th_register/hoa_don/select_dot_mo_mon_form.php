<?php

defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/lib/formslib.php';

class select_dot_mo_mon_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $COURSE, $USER;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $option = array(
            'multiple'          => false,
            'noselectionstring' => get_string('noselectionstring', 'local_th_register'),
        );

        $records = $DB->get_records('th_dot_mo_mon');
        $dot_mo_mon = array('');
        foreach ($records as $key => $record) {
           $dot_mo_mon[$key] = $record->ten_dot_mo . ', ' . $record->ma_dot_mo_mon;
        }

        $mform->addElement('autocomplete', 'dot_mo_mon_id', get_string('batch_opening', 'local_th_register'), $dot_mo_mon, $option);
        $mform->addRule('dot_mo_mon_id', '', 'required', null, 'client', false, false);
        
        $this->add_action_buttons(false, get_string('submit'));
    }
}
