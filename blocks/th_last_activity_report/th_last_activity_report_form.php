<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

class th_last_activity_report_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $objs[] = $mform->createElement('static', 's1', null,
            html_writer::start_tag('div', array('class' => 'w-100 d-flex align-items-center')));
        $objs[] = $mform->createElement('static', 's2', null,
            html_writer::tag('div', get_string('isbefore', 'filters'), array('class' => 'mr-2')));
        $objs[] = $mform->createElement('date_selector', 'startdate', null, array());
        $objs[] = $mform->createElement('static', 's3', null, html_writer::end_tag('div'));
        $grp = &$mform->addElement('group', '_grp', get_string('lastactivity', 'block_th_last_activity_report'), $objs, '', false);

        $this->add_action_buttons(true, get_string('view'));
    }
}
