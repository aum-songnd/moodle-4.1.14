<?php

namespace block_plugin01\form;

require_once("$CFG->libdir/formslib.php");

class myform2 extends \moodleform {
   
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text','email',get_string('email'));
        $mform->setType('email', PARAM_EMAIL);
        // $mform->setDefault('email', 'Please enter email');
        $mform->addElement('passwordunmask','password',get_string('password'));
        // $mform->addElement('submit','intro','login');
        $this->add_action_buttons(true,'Login');

    }
    function validation($data, $files) {
    
        return [];
    }
}