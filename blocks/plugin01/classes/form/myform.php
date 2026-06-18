<?php

namespace block_plugin01\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class myform extends \moodleform {
    // Add elements to form.
    public function definition() {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // $select = $mform->addElement('select', 'colors', get_string('colors','block_plugin01'), array('red', 'blue', 'green'),);
        // $select->setMultiple(true);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'yesno', '', get_string('ghidanh','block_plugin01'), 1);
        $radioarray[] = $mform->createElement('radio', 'yesno', '', get_string('xoaghidanh','block_plugin01'), 0);
        
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
        $mform->setDefault('yesno', 1);
      
        ##################### CUSTOM #####################################

        global $DB;
        #ten user
        $users = $DB->get_records_sql(
            "SELECT id, username 
            FROM {user} WHERE id <> 1 AND deleted =0
            ORDER BY id ASC");

        $useroptions = array();
        foreach ($users as $user) {
            $useroptions[$user->id] = $user->username;
        }

        $options = array(
            'multiple' => true,
            'noselectionstring' => 'User', 
        );
        $mform->addElement('autocomplete', 'userids', get_string('users'), $useroptions, $options);


        #ten khoa hoc
        $courses = $DB->get_records_sql(
            "SELECT id, fullname
            FROM {course} WHERE category <> 0 and visible <>0
            ORDER BY fullname ASC");    

        $courseoptions = array();
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname;
        }

        $options = array(
            'multiple' => true,
            'noselectionstring' => 'Khoa hoc', 
        );

        $mform->addElement('autocomplete', 'courseids', get_string('courses'), $courseoptions, $options);

        
        #ten role
        $context = \context_course::instance(1, MUST_EXIST);   
        $roles = array();
        $roles = get_assignable_roles($context);
        $options = array(
            'multiple'=>true,
            'noselectionstring' => 'Role',
        );
        $mform->addElement('autocomplete', 'roleids', get_string('roles'), $roles, $options);
        
        // $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
        // $mform->addElement('tags', 'tags', get_string('tags'),array('itemtype' => 'context_course', 'component' => 'core','displaycontext' => $context ));
            

        #add button
        $mform->addElement('submit', 'intro', 'Xác nhận');
        
        $mform->addRule('userids', null, 'required', null, 'client');
        $mform->addRule('courseids', null, 'required', null, 'client');
        $mform->addRule('roleids', null, 'required', null, 'client');

        // $attributes='size="50"';
        // $mform->addElement('passwordunmask', 'password', 'password',$attributes);
        // $mform->addElement('editor', 'intro', 'Giới thiệu');
        //  $this->add_action_buttons(true,'Xác nhận');
        }

    // Custom validation should be added here.
    function validation($data, $files) {
    
        return [];
    }
}