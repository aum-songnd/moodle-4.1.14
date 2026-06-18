<?php

namespace local_th_random_test_enrolment;

use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

/**
 * Class th_random_test_enrolment_form
 * @package local_th_random_test_enrolment
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class th_random_test_enrolment_form extends moodleform
{

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition()
    {
        global $CFG, $SESSION, $DB;

        require_once $CFG->dirroot . '/local/th_random_test_enrolment/lib.php';

        $mform = $this->_form;

        // Infotext.
        $msg = get_string('th_random_test_enrolment_form_intro', 'local_th_random_test_enrolment');
        $mform->addElement('html', '<div id="intro">' . $msg . '</div>');

        // Textarea for Emails.
        $mform->addElement('textarea', 'usermails',
            get_string('list_of_user_identifications', 'local_th_random_test_enrolment'), 'wrap="virtual" rows="10" cols="80"');
        $mform->addRule('usermails', null, 'required');
        $mform->addHelpButton('usermails', 'usermails', 'local_th_random_test_enrolment');

        $roleoptions = array();
        // Get some basic data we are going to need.
        $roles = get_all_roles();
        // $systemcontext = context_system::instance();
        $rolenames = role_fix_names($roles, null, ROLENAME_ORIGINAL);
        if (!empty($rolenames)) {
            foreach ($rolenames as $key => $role) {
                if (!array_key_exists($role->id, $roleoptions)) {
                    $roleoptions[$role->id] = $role->localname;
                }
            }
        }

        $mform->addElement('select', 'role', get_string('role'), $roleoptions);
        $studentarchetype = get_archetype_roles('student');
        if ($studentarchetype != false && count($studentarchetype) > 0) {
            $firststudentrole = array_shift($studentarchetype);
            $firststudentroleid = $firststudentrole->id;
        } else {
            $firststudentroleid = '';
        }
        $mform->setDefault('role', $firststudentroleid);

        $mform->addElement('select', 'identifiertype', get_string('identifiers_type', 'local_th_random_test_enrolment'), [get_string('email', 'local_th_random_test_enrolment'), get_string('studentcode', 'local_th_random_test_enrolment')]);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id', $this->_customdata['courseid']);

         // Add form content if the user came back to check his input.
        $localth_random_test_enrolmenteditlist = optional_param('editlist', 0, PARAM_ALPHANUMEXT);
        if (!empty($localth_random_test_enrolmenteditlist)) {
            $localth_random_test_enrolmentdata = $localth_random_test_enrolmenteditlist . '_data';
            if (!empty($localth_random_test_enrolmentdata) && !empty($SESSION->local_th_random_test_enrolment_inputs) &&
                array_key_exists($localth_random_test_enrolmentdata, $SESSION->local_th_random_test_enrolment_inputs)) {
                $formdata = $SESSION->local_th_random_test_enrolment_inputs[$localth_random_test_enrolmentdata];
                $formdatatmp = $formdata->usermails;
                $identifiertype = $formdata->identifiertype;

                $mform->setDefault('usermails', $formdatatmp);
                $mform->setDefault('identifiertype', $identifiertype);
            }
        }

        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Get each of the rules to validate its own fields
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files)
    {
        $retval = array();

        if (empty($data['usermails'])) {
            $retval['usermails'] = get_string('error_usermails_empty', 'local_th_random_test_enrolment');
        }

        return $retval;
    }
}
