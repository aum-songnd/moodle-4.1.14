<?php

namespace block_th_user_access_course\form;

require_once("$CFG->libdir/formslib.php");


class myform2 extends \moodleform {
 
    public function definition() {
        global $DB ,$COURSE;
        $mform = $this->_form; 
#ADD RADIO         
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'radioar', '', 'Course', 'course');
        $radioarray[] = $mform->createElement('radio', 'radioar', '', 'User', 'user');
        $mform->addGroup($radioarray, 'radioar_group', '', array(' '), false);

        $mform->setDefault('radioar', 'course');

        $mform->hideIf('userids', 'radioar', 'eq', 'course');
        $mform->disabledIf('userids', 'radioar', 'eq', 'course');

        $mform->hideIf('courseids', 'radioar', 'eq', 'user');
        $mform->disabledIf('courseids', 'radioar', 'eq', 'user');

#USER
        $users = $DB->get_records_sql(
            "SELECT id , username , firstname, lastname
            FROM {user} WHERE id<>1
            ORDER BY id ASC");
        $useroptions = array();
        foreach($users as $user){
            $useroptions[$user->id] = $user->username;
        }
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('users')
        );

        $mform->addElement('autocomplete', 'userids', get_string('users'), $useroptions, $options);

#COURSE
        $courses = $DB->get_records_sql(
            "SELECT id , fullname
            FROM {course} WHERE category<>0
            ORDER BY id ASC");
        $courseoptions = array();
        foreach($courses as $course){
            $courseoptions[$course->id] = $course->fullname;
        }
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('courses')
        );

        $mform->addElement('autocomplete', 'courseids', get_string('courses'), $courseoptions, $options);
#ROLE
        $context = \context_course::instance($COURSE->id);   
        $roles = array();
        $roles = get_assignable_roles($context);
        $options = array(
            'multiple'=>true,
            'noselectionstring' => 'Role',
        );
        $mform->addElement('autocomplete', 'roleids', get_string('roles'), $roles, $options);

        $mform->addElement('submit','intro','Click me!');
    }
    function validation($data, $files) {
    
        return [];
    }

}