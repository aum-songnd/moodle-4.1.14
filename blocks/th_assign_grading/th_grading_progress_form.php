<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/thlib/lib.php';


class th_grading_progress_form extends moodleform {
	public function definition() {
        global $CFG, $DB;
    
        $mform = $this->_form;
    
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
    
        $users = $DB->get_records_sql("SELECT u.* FROM {user} as u JOIN {role_assignments} as ra 
        ON ra.userid = u.id JOIN {role} as r ON r.shortname = 'ctv_marking' AND r.id = ra.roleid;");
    
        $user_arr = ["" => ""];
        foreach ($users as $userid => $user) {
            $user_arr[$userid] = fullname($user) . ', ' . $user->username . ', ' . $user->email;
        }
    
        $options_user = array(
            'multiple' => true,
            'noselectionstring' => 'Chọn CTV',
        );
        $mform->addElement('autocomplete', 'userid',  'Chọn CTV', $user_arr, $options_user);
        // $mform->addRule('userid', '', 'required', null, 'client', false, false);
    
        $from_date = get_string('from_date', 'block_th_assign_grading');
        $mform->addElement('date_selector', 'from_date', $from_date);
    
        $to_date = get_string('to_date', 'block_th_assign_grading');
        $mform->addElement('date_selector', 'to_date', $to_date);
    
        $options = array(
            0 => get_string('all', 'block_th_assign_grading'),
            1 => get_string('scored', 'block_th_assign_grading'),
            2 => get_string('not_scored_yet', 'block_th_assign_grading'),
        );
    
        $mform->addElement('select', 'status', get_string('status', 'block_th_assign_grading'), $options);
        $mform->setType('status', PARAM_INT);
    
        $this->add_action_buttons(true, get_string('submit', 'block_th_assign_grading'));
    }
    
}