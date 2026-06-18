<?php

namespace local_th_online_exam_manager\form;

require_once("$CFG->libdir/formslib.php");

class regrade_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'quizid');
        $mform->setType('quizid', PARAM_INT);

        $mform->addElement('hidden', 'attemptid');
        $mform->setType('attemptid', PARAM_INT);

        $mform->addElement('textarea', 'reason', get_string('reason', 'local_th_online_exam_manager'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('reason', PARAM_TEXT);
        $mform->addRule('reason', null, 'required', null, 'client');

        $this->add_action_buttons();
    }
}
