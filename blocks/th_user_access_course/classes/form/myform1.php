<?php

namespace block_th_user_access_course\form;

require_once("$CFG->libdir/formslib.php");


class myform extends \moodleform {
 
    public function definition() {
        global $DB;
        $mform = $this->_form; 


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

        $mform->addElement('submit','intro','Click me!');
    }
    function validation($data, $files) {
    
        return [];
    }

}