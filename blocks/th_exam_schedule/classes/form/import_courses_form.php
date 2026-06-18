<?php
namespace block_th_exam_schedule\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class import_courses_form extends \moodleform {

    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('header', 'h', get_string('importcoursesfile', 'block_th_exam_schedule'));

        $examplelink = "<a href='{$CFG->wwwroot}/blocks/th_exam_schedule/example1.xlsx'>example.xlsx</a>";
        $mform->addElement(
            'static',
            'examplefile',
            get_string('examplefile', 'local_th_framework_study'),
            $examplelink
        );

        $mform->addElement('filepicker', 'coursesfile', get_string('importcoursesfile', 'block_th_exam_schedule'), null, [
            'accepted_types' => ['.csv', '.xlsx', '.ods'],
            'maxbytes' => 0,
        ]);

        $mform->addRule('coursesfile', null, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('import', 'block_th_exam_schedule'));
    }
}
