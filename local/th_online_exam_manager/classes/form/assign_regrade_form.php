<?php

namespace local_th_online_exam_manager\form;

require_once("$CFG->libdir/formslib.php");
use core_user;

class assign_regrade_form extends \moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $reviewers = $this->_customdata['reviewers'];

        $mform->addElement('hidden', 'attemptid');
        $mform->setType('attemptid', PARAM_INT);

        $mform->addElement('hidden', 'quizid');
        $mform->setType('quizid', PARAM_INT);

        // Danh sách reviewer
        $revieweroptions = [];
        foreach ($reviewers as $id => $user) {
            $revieweroptions[$id] = fullname($user) . " ($user->email)";
        }
        $mform->addElement('autocomplete', 'reviewerid', get_string('reviewer1', 'local_th_online_exam_manager'), $revieweroptions, [
            'noselectionstring' => '',
            'multiple' => false,
        ]);
        $mform->setType('reviewerid', PARAM_INT);
        $mform->addRule('reviewerid', null, 'required');

        $mform->addElement('autocomplete', 'reviewerid2', get_string('reviewer2', 'local_th_online_exam_manager'), $revieweroptions, [
            'noselectionstring' => '',
            'multiple' => false,
        ]);
        $mform->setType('reviewerid2', PARAM_INT);
        $mform->addRule('reviewerid2', null, 'required');

        $this->add_action_buttons(true, get_string('assign', 'local_th_online_exam_manager'));
    }

    public function validation($data, $files) {
        $errors = [];

        if ($data['reviewerid'] == $data['reviewerid2']) {
            $errors['reviewerid2'] = get_string('samereviewer', 'local_th_online_exam_manager');
        }        
    
        return $errors;
    }
}
