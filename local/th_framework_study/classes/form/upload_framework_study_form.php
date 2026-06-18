<?php
require_once($CFG->libdir . '/formslib.php');

class framework_upload_form extends moodleform {
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        // Action: Import or Delete
        $mform->addElement(
            'select',
            'actionmode',
            get_string('actionmode', 'local_th_framework_study'),
            [
                'import' => get_string('action_import', 'local_th_framework_study'),
                'delete' => get_string('action_delete', 'local_th_framework_study')
            ]
        );
        $mform->setDefault('actionmode', 'import');
        // Example file link
        $examplelink = "<a href='{$CFG->wwwroot}/local/th_framework_study/example_frameworkstudy.xlsx'>example.xlsx</a>";
        $mform->addElement(
            'static',
            'examplefile',
            get_string('examplefile', 'local_th_framework_study'),
            $examplelink
        );

        // File upload
        $mform->addElement('filepicker', 'frameworkfile',
            get_string('choosefile', 'local_th_framework_study'),
            null,
            ['accepted_types' => ['.xlsx']]
        );
        $mform->addRule('frameworkfile', null, 'required', null, 'client');

        // Action buttons (Preview)
        $this->add_action_buttons(true, get_string('preview', 'local_th_framework_study'));
    }
}
