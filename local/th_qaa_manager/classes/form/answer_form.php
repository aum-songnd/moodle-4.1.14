<?php
namespace local_th_qaa_manager\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class answer_form extends \moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $context = \context_system::instance();

        $servermaxbytes = get_user_max_upload_file_size(
            $context,
            $CFG->maxbytes
        );

        $imagemaxbytes = 5 * 1024 * 1024;

        if ($servermaxbytes > 0) {
            $imagemaxbytes = min($servermaxbytes, $imagemaxbytes);
        }

        $editoroptions = $this->_customdata['editoroptions'] ?? [
            'context' => $context,
            'subdirs' => 0,
            'maxbytes' => $imagemaxbytes,
            'areamaxbytes' => 50 * 1024 * 1024,
            'maxfiles' => 20,
            'accepted_types' => ['web_image'],
            'trusttext' => false,
        ];

        $mform->addElement(
            'editor',
            'answer',
            get_string('answer', 'local_th_qaa_manager'),
            [
                'rows' => 15,
            ],
            $editoroptions
        );

        $mform->setType('answer', PARAM_RAW);

        $mform->addRule(
            'answer',
            get_string('required'),
            'required',
            null,
            'client'
        );

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(
            false,
            get_string('submitanswer', 'local_th_qaa_manager')
        );
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['answer']['text'])) {
            $text = trim(html_to_text($data['answer']['text']));
            if (\core_text::strlen($text) < 20) {
                $errors['answer'] = get_string('error_answer_too_short', 'local_th_qaa_manager');
            }
        } else {
            $errors['answer'] = get_string('required');
        }

        return $errors;
    }
}