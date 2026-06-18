<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local plugin "th_bulkenrol" - Enrolment form
 *
 * @package   local_th_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_th_bulkenrol;

use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

/**
 * Class th_bulkenrol_form
 * @package local_th_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class th_bulkenrol_form extends moodleform
{

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition()
    {
        global $CFG, $SESSION, $DB;

        require_once $CFG->dirroot . '/local/th_bulkenrol/lib.php';

        $mform = $this->_form;

        // Infotext.
        $msg = get_string('th_bulkenrol_form_intro', 'local_th_bulkenrol');
        $mform->addElement('html', '<div id="intro">' . $msg . '</div>');

        // Textarea for Emails.
        $mform->addElement('textarea', 'usermails',
        get_string('list_user_identifications', 'local_th_bulkenrol'), 'wrap="virtual" rows="10" cols="80"');
        $mform->addRule('usermails', null, 'required');
        $mform->addHelpButton('usermails', 'usermails', 'local_th_bulkenrol');

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

        $mform->addElement('select', 'identifiertype', get_string('identifiertype', 'local_th_bulkenrol'), ['email', get_string('studentcode', 'local_th_bulkenrol')]);

        $radioarray = array();
        $radioarray[] = &$mform->createElement('radio', 'option', '', get_string('opt_enroll', 'local_th_bulkenrol'), 0);
        $radioarray[] = &$mform->createElement('radio', 'option', '', get_string('opt_suspend', 'local_th_bulkenrol'), 1);
        $radioarray[] = &$mform->createElement('radio', 'option', '', get_string('opt_unenroll', 'local_th_bulkenrol'), 2);

        $element = $mform->addGroup($radioarray, 'options', get_string('options', 'local_th_bulkenrol'), null, false);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id', $this->_customdata['courseid']);

        // Add form content if the user came back to check his input.
        $localth_bulkenroleditlist = optional_param('editlist', 0, PARAM_ALPHANUMEXT);
        if (!empty($localth_bulkenroleditlist)) {
            $localth_bulkenroldata = $localth_bulkenroleditlist . '_data';
            if (!empty($localth_bulkenroldata) && !empty($SESSION->local_th_bulkenrol_inputs) &&
                array_key_exists($localth_bulkenroldata, $SESSION->local_th_bulkenrol_inputs)) {
                $formdata = $SESSION->local_th_bulkenrol_inputs[$localth_bulkenroldata];
                $formdatatmp = $formdata->usermails;

                $identifiertype = $formdata->identifiertype;
                $option = $formdata->option;

                $mform->setDefault('usermails', $formdatatmp);
                $mform->setDefault('options', $option);
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
            $retval['usermails'] = get_string('error_usermails_empty', 'local_th_bulkenrol');
        }

        return $retval;
    }
}
