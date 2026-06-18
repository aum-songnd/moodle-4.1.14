<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/lib/formslib.php';

class th_roadmap_vstep_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'local_th_roadmap_vstep'));

		$mform = $this->_form;
		
		$link = "<a href='roadmap.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'local_th_roadmap_vstep'), $link);

		$mform->addElement('filepicker', 'roadmap', get_string('file'),null,
	    [
	        'accepted_types' => '.csv',
	    ]);

		$this->add_action_buttons(true, get_string('submit'));
	}
}
?>
