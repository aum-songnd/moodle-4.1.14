<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class upload_mapping_form extends moodleform {
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        // Example file link
        $examplelink = "<a href='{$CFG->wwwroot}/local/th_framework_study/example_subjectcode.xlsx'>example.xlsx</a>";
        $mform->addElement(
            'static',
            'examplefile',
            get_string('examplefile', 'local_th_framework_study'),
            $examplelink
        );

        $mform->addElement(
            'filepicker',
            'mappingfile',
            get_string('file'),
            null,
            [
                'accepted_types' => ['.xlsx'],
                'maxbytes' => 0
            ]
        );
        $mform->addRule('mappingfile', null, 'required');

        $this->add_action_buttons(true, get_string('submit'));
    }
}
