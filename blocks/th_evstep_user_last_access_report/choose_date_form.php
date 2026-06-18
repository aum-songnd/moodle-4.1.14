<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

class choose_date_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'displayinfo', get_string('filter'));

        $roles = $DB->get_record('user_info_field', ['shortname' => 'th_user_role']);

        $options = explode("\n", $roles->param1);

        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $array_roles[$option] = format_string($option, true, ['context' => context_system::instance()]);
        }

        // Radio button
        $options = [
            $mform->createElement('radio', 'option_select', '', get_string('role', 'block_th_evstep_user_last_access_report'), 1),
            $mform->createElement('radio', 'option_select', '', get_string('user', 'block_th_evstep_user_last_access_report'), 2),
            $mform->createElement('radio', 'option_select', '', get_string('makhoa', 'block_th_evstep_user_last_access_report'), 3),
        ];
        $mform->addGroup($options, 'options_group', get_string('select_option', 'block_th_evstep_user_last_access_report'), [' '], false);
        $mform->setDefault('option_select', 1); 
        // END

        // Option 1
        $options_role = [
            'multiple' => true,
            'noselectionstring' => get_string('choosearole', 'block_th_evstep_user_last_access_report'), // Chuỗi hiển thị khi không chọn gì.
        ];

        $mform->addElement('autocomplete', 'roles', get_string('choosearole', 'block_th_evstep_user_last_access_report'), $array_roles, $options_role);
        // END

        // Option 2
        $users = $DB->get_records_sql("SELECT * FROM {user} WHERE id <> :guestid AND deleted = 0 AND confirmed = 1 AND suspended = 0 ", ['guestid' => $CFG->siteguest]);
    
        $user_arr = ["" => ""];
        foreach ($users as $userid => $user) {
            $user_arr[$userid] = fullname($user) . ', ' . $user->username . ', ' . $user->email;
        }
    
        $options_user = array(
            'multiple' => true,
            'noselectionstring' => get_string('user', 'block_th_evstep_user_last_access_report'),
        );
        $mform->addElement('autocomplete', 'userid',  get_string('user', 'block_th_evstep_user_last_access_report'), $user_arr, $options_user);
        // END

        // OPTION 3
        $mform->addElement('text', 'makhoa', get_string('makhoa', 'block_th_evstep_user_last_access_report'));
        $mform->setType('makhoa', PARAM_TEXT);
        // END

        $mform->disabledIf('roles', 'option_select', 'neq', 1);
        $mform->disabledIf('userid', 'option_select', 'neq', 2);
        $mform->disabledIf('makhoa', 'option_select', 'neq', 3);
        $mform->hideIf('roles', 'option_select', 'neq', 1);
        $mform->hideIf('userid', 'option_select', 'neq', 2);
        $mform->hideIf('makhoa', 'option_select', 'neq', 3);

        $objs[] = $mform->createElement('static', 's1', null,
            html_writer::start_tag('div', array('class' => 'w-100 d-flex align-items-center')));
        $objs[] = $mform->createElement('static', 's2', null,
            html_writer::tag('div', get_string('isbefore', 'filters'), array('class' => 'mr-2')));
        $objs[] = $mform->createElement('date_selector', 'startdate', null, array());
        $objs[] = $mform->createElement('static', 's3', null, html_writer::end_tag('div'));
        $grp = &$mform->addElement('group', '_grp', get_string('lastaccess', 'block_th_evstep_user_last_access_report'), $objs, '', false);

        $this->add_action_buttons(true, get_string('view'));
    }
}
