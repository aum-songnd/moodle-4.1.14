<?php

class block_plugin01_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_plugin01'));
        $mform->setDefault('config_text', get_string('defaultvalue', 'block_plugin01'));
        $mform->setType('config_text', PARAM_TEXT);
        
    }
}