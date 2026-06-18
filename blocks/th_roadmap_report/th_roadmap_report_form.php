<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

class th_roadmap_report_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('course'), '0');
        $radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('student', 'block_th_roadmap_report'), '1');
        $radioarray[] = $mform->createElement('radio', 'show_option', '', get_string('date', 'block_th_roadmap_report'), '2');
        $mform->addGroup($radioarray, 'radioar', get_string('option', 'block_th_roadmap_report'), array(''), false);
        $mform->setDefault('show_option', '0');

        $config = get_config('local_thlib');
        $sortorder = "lastname,firstname";
        if ($config->sortorder == 1) {
            $sortorder = "firstname,lastname";
        }

        $selectuser = array();
        $users = $DB->get_records_select('user', 'deleted = 0 AND id <> 1', array(), 'id,firstname,lastname,email');

        foreach ($users as $key => $user) {
            $selectuser[$user->id] = fullname($user) . ','. $user->email;
        }

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('no_selection', 'block_th_roadmap_report'),
        );
        $mform->addElement('autocomplete', 'userid', get_string('selectuser', 'block_th_roadmap_report'), $selectuser, $options);

        $config = trim(get_config('block_th_roadmap_report', 'idnumber'));
        $selectcourse = array();
        $sql = "SELECT c.id,fullname,shortname 
            FROM {course} c
            JOIN {course_categories} cc ON cc.id = c.category
            where cc.idnumber = :idnumber";
        $courses = $DB->get_records_sql($sql, ['idnumber' => $config]);
        foreach ($courses as $id => $course) {
            $selectcourse[$id] = $course->fullname . ','. $course->shortname;
        }

        $mform->addElement('autocomplete', 'courseidarr', get_string('selectcourse', 'block_th_roadmap_report'), $selectcourse, $options);

        $mform->addElement('date_selector', 'startdate', get_string('from', 'block_th_roadmap_report'));
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('-1 month');
        $mform->setDefault('startdate', $date->getTimestamp());
        $mform->addElement('date_selector', 'enddate', get_string('to', 'block_th_roadmap_report'));

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'summary_detail', '', get_string('summary', 'block_th_roadmap_report'), '0');
        $radioarray[] = $mform->createElement('radio', 'summary_detail', '', get_string('detail', 'block_th_roadmap_report'), '1');
        $mform->addGroup($radioarray, 'radioar', get_string('summary_detail', 'block_th_roadmap_report'), array(''), false);
        $mform->setDefault('summary_detail', '0');

        $mform->disabledIf('courseidarr', 'show_option', 'neq', '0');
        $mform->disabledIf('userid', 'show_option', 'neq', '1');
        $mform->disabledIf('startdate', 'show_option', 'neq', '2');
        $mform->disabledIf('enddate', 'show_option', 'neq', '2');

        $mform->hideif('courseidarr', 'show_option', 'neq', '0');
        $mform->hideif('userid', 'show_option', 'neq', '1');
        $mform->hideif('startdate', 'show_option', 'neq', '2');
        $mform->hideif('enddate', 'show_option', 'neq', '2');

        $this->add_action_buttons(true, get_string('view'));
    }
}
