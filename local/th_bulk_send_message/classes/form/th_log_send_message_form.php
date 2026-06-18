<?php

namespace local_th_bulk_send_message\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class th_log_send_message_form extends \moodleform {

    public function definition() {
        global $CFG, $DB, $COURSE, $OUTPUT;

        $mform = $this->_form;
        $context = \context_course::instance($COURSE->id);

        $userfields = implode(',', \core_user\fields::get_identity_fields($context));

        $userselectoroptions = [
            'multiple' => false,
            'noselectionstring' => get_string('all'),
            'ajax' => 'local_thlib/loadUser',
            'perpage' => $CFG->maxusersperpage,
            'userfields' => $userfields,
            'valuehtmlcallback' => function($value) use ($DB, $OUTPUT) {
                $user = $DB->get_record('user', ['id' => (int)$value], '*', IGNORE_MISSING);
                if (!$user) {
                    return '';
                }
                $user->fullname = fullname($user);
                return $OUTPUT->render_from_template('local_thlib/form-user-selector', $user);
            }
        ];

        $mform->addElement('header', 'filterheader', get_string('filter'));

        $mform->addElement(
            'autocomplete',
            'senderid',
            get_string('sender', 'local_th_bulk_send_message'),
            [],
            $userselectoroptions
        );
        $mform->setType('senderid', PARAM_RAW_TRIMMED);
        $mform->setDefault('senderid', '');

        $mform->addElement(
            'autocomplete',
            'receiverid',
            get_string('receiver', 'local_th_bulk_send_message'),
            [],
            $userselectoroptions
        );
        $mform->setType('receiverid', PARAM_RAW_TRIMMED);
        $mform->setDefault('receiverid', '');

        $mform->addElement(
            'text',
            'subject',
            get_string('subject', 'local_th_bulk_send_message')
        );
        $mform->setType('subject', PARAM_TEXT);

        $mform->addElement(
            'date_selector',
            'datefrom',
            get_string('datefrom', 'local_th_bulk_send_message'),
            ['optional' => true]
        );

        $mform->addElement(
            'date_selector',
            'dateto',
            get_string('dateto', 'local_th_bulk_send_message'),
            ['optional' => true]
        );

        $statusoptions = [
            ''  => get_string('all'),
            '1' => get_string('send_success', 'local_th_bulk_send_message'),
            '0' => get_string('send_fail', 'local_th_bulk_send_message'),
        ];

        $mform->addElement(
            'select',
            'status',
            get_string('status', 'local_th_bulk_send_message'),
            $statusoptions
        );

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement(
            'submit',
            'submitbutton',
            get_string('filter', 'local_th_bulk_send_message')
        );
        $buttonarray[] = &$mform->createElement(
            'cancel',
            'cancel',
            get_string('cancel', 'local_th_bulk_send_message')
        );
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
    
        $senderid = $data['senderid'] ?? '';
        $receiverid = $data['receiverid'] ?? '';
    
        if (
            ($senderid === '' || $senderid === '0' || $senderid === 0) &&
            ($receiverid === '' || $receiverid === '0' || $receiverid === 0)
        ) {
            $message = get_string('senderorreceiverrequired', 'local_th_bulk_send_message');
            $errors['senderid'] = $message;
            $errors['receiverid'] = $message;
        }
    
        return $errors;
    }
}