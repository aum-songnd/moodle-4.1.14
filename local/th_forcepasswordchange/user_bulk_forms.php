<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/datalib.php');

/**
 * Bulk user form
 *
 * @copyright  Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_bulk_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {

        global $DB;

        $mform = $this->_form;
        // $customdata = $this->_customdata['columns'];


        $mform->addElement('header', 'header', get_string('pluginname', 'local_th_forcepasswordchange'));

        $users = $DB->get_records_sql("SELECT * FROM {user} WHERE id <> 1 AND deleted = 0");
        $user_arr = array();
        foreach ($users as $userid => $user) {
            $user_arr[$userid] = fullname($user) . ', ' . $user->username . ', ' . $user->email;
        }

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('selectuser', 'local_th_forcepasswordchange'),
        );
        $mform->addElement('autocomplete', 'users', get_string('selectuser', 'local_th_forcepasswordchange'), $user_arr, $options);
        $mform->addRule('users', null, 'required', null, 'client');
        $mform->addElement('checkbox', 'send_mail', get_string('send_mail', 'local_th_forcepasswordchange'));
        $mform->setDefault('send_mail', 1);

        $this->add_action_buttons(true, get_string('submit'));
    }
}
