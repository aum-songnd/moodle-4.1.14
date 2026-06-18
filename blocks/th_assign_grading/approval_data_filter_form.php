<?php

require_once $CFG->dirroot . '/lib/formslib.php';

class approval_data_filter_form extends moodleform {

    function definition() {
        global $DB;

        $mform = $this->_form;

        // Option CTV
        $users = $DB->get_records_sql("
            SELECT u.* FROM {user} AS u
            JOIN {role_assignments} AS ra ON ra.userid = u.id
            JOIN {role} AS r ON r.id = ra.roleid
            WHERE r.shortname = 'ctv_marking'
        ");
        
        foreach ($users as $userid => $user) {
            $user_arr[$userid] = fullname($user) . ', ' . $user->username . ', ' . $user->email;
        }

        $mform->addElement('autocomplete', 'userid', get_string('selectuser', 'block_th_assign_grading'), $user_arr, [
            'multiple' => true,
            'noselectionstring' => get_string('noselection', 'block_th_assign_grading'),
        ]);

        // Option Course name
        $courses = $DB->get_records_sql("SELECT id, fullname FROM {course} WHERE visible = 1");
        foreach ($courses as $cid => $course) {
            $course_arr[$cid] = $course->fullname;
        }

        $mform->addElement('autocomplete', 'coursename', get_string('selectcourse', 'block_th_assign_grading'), $course_arr, [
            'multiple' => true,
            'noselectionstring' => get_string('noselection', 'block_th_assign_grading'),
        ]);

        // Option date time
        $mform->addElement('date_selector', 'from_date', get_string('from_date', 'block_th_assign_grading'));
        $mform->addElement('date_selector', 'to_date', get_string('to_date', 'block_th_assign_grading'));

        // Option status
        $options = [
            0 => get_string('all', 'block_th_assign_grading'),
            1 => get_string('scored', 'block_th_assign_grading'),
            2 => get_string('not_scored_yet', 'block_th_assign_grading'),
        ];

        $mform->addElement('select', 'status', get_string('status', 'block_th_assign_grading'), $options);
        $mform->setType('status', PARAM_INT);

        
        $this->add_action_buttons(true, get_string('view', 'block_th_assign_grading'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['from_date']) && !empty($data['to_date']) && $data['from_date'] > $data['to_date']) {
            $errors['to_date'] = get_string('invaliddate', 'block_th_assign_grading');
        }

        return $errors;
    }
}
