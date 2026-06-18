<?php

class block_plugin02_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));
        
        
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_plugin02'));
        $mform->setDefault('config_text', get_string('defaultvalue', 'block_plugin01'));
        $mform->setType('config_text', PARAM_TEXT);
    }
}