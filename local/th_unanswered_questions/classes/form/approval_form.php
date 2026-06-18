<?php
namespace local_th_unanswered_questions\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class approval_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $mode  = $this->_customdata['mode'];     // 'approve' | 'reject'
        $ctx   = $this->_customdata['context'];
        $cmid  = $this->_customdata['cmid'];
        $root  = $this->_customdata['root'];

        if ($mode === 'approve') {
            $mform->addElement('header', 'h', get_string('approveandanswer', 'mod_qaa'));

            $opts = ['maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$ctx, 'subdirs'=>true];
            $mform->addElement('editor', 'content', get_string('youranswer','mod_qaa'), ['rows'=>12], $opts);
            $mform->setType('content', PARAM_RAW);
        } else {
            $mform->addElement('header', 'h', get_string('rejectwithreason', 'mod_qaa'));
            $opts = ['maxfiles'=>0, 'noclean'=>true, 'context'=>$ctx];
            $mform->addElement('editor', 'content', get_string('reason','mod_qaa'), ['rows'=>10], $opts);
            $mform->setType('content', PARAM_RAW);
            $mform->addRule('content', null, 'required', null, 'client');
        }

        // hidden
        $mform->addElement('hidden', 'cmid', $cmid);             
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'questionid', $root->id); 
        $mform->setType('questionid', PARAM_INT);
        $mform->addElement('hidden', 'mode', $mode);           
        $mform->setType('mode', PARAM_ALPHA);

        $this->add_action_buttons(true, get_string('submit'));
    }

    public function validation($data, $files) {
        $errors = [];
        $config = get_config('mod_qaa');
        $min_character = $config->min_question_character;
        $plain = trim(strip_tags((string)($data['content']['text'] ?? '')));
        if (\core_text::strlen($plain) < $min_character) {
            $errors['content'] = get_string('err_content_too_short', 'mod_qaa', $min_character);
        }
        return $errors;
    }
}
