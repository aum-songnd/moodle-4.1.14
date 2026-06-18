<?php
namespace local_th_exam_monitor\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class action_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $userid   = $this->_customdata['userid'];
        $courseid = $this->_customdata['courseid'];
        $groupid = $this->_customdata['groupid'];

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);

        $options_status = [
            '' => get_string('selectaction', 'local_th_exam_monitor'),
            EXAM_ACTION_WARN => get_string('warn', 'local_th_exam_monitor'),
            EXAM_ACTION_ALERT => get_string('alert', 'local_th_exam_monitor'),
            EXAM_ACTION_SUSPEND => get_string('suspend_exam', 'local_th_exam_monitor'),
        ];
        // Action
        $mform->addElement('select', 'action_status', get_string('status'), $options_status);
        $mform->addRule('action_status', null, 'required');

        // Editor (PASTE ẢNH ĐƯỢC)
        $editoroptions = [
            'maxfiles' => 1,
            'maxbytes' => 0,
            'trusttext' => true,
            'context' => \context_course::instance($courseid),
        ];

        $mform->addElement(
            'editor',
            'note_editor',
            get_string('note', 'local_th_exam_monitor'),
            null,
            $editoroptions
        );
        $mform->addRule('note_editor', null, 'required');

        $this->add_action_buttons(true, get_string('save'));
    }
}
