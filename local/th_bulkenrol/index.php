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
 * Local plugin "th_bulkenrol" - Enrolment page
 *
 * @package   local_th_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_th_bulkenrol\confirm_form;
use local_th_bulkenrol\th_bulkenrol_form;

require_once '../../config.php';

require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_bulkenrol/locallib.php';

global $PAGE, $OUTPUT, $SESSION;

$id = required_param('id', PARAM_INT);
$localth_bulkenrolkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

$context = context_system::instance();

if (!empty($id)) {
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);

    $PAGE->set_context($context);
    $PAGE->set_url('/local/th_bulkenrol/index.php', array('id' => $id));
    $PAGE->set_title("$course->shortname: " . get_string('pluginname', 'local_th_bulkenrol'));
    $PAGE->set_heading($course->fullname);

    require_login($course);
}

if (!has_capability('local/th_bulkenrol:enrolusers', $context)) {
    print_error('nopermissions', 'error', '', 'local/th_bulkenrol:enrolusers');
}

$PAGE->set_pagelayout('incourse');

if (empty($localth_bulkenrolkey)) {
    $form = new th_bulkenrol_form(null, array('courseid' => $id));
    if ($formdata = $form->get_data()) {

        $courseid = $formdata->id;
        $roleid = $formdata->role;

        if ($formdata->identifiertype == 0) {
            $emails = $formdata->usermails;
            $checkedmails = local_th_bulkenrol_check_user_mails($emails, $courseid);
        }

        if ($formdata->identifiertype == 1) {
            $masinhviens = $formdata->usermails;
            $checkedmasinhvien = local_th_bulkenrol_check_user_masinhvien($masinhviens, $courseid);
            $emails = $masinhviens;
            $checkedmails = $checkedmasinhvien;
        }

        // Create local_th_bulkenrol array in Session.
        {
            if (!isset($SESSION->local_th_bulkenrol)) {
                $SESSION->local_th_bulkenrol = array();
            }
        }

        // Save data in Session.
        $localth_bulkenrolkey = $courseid . '_' . time();
        $SESSION->local_th_bulkenrol[$localth_bulkenrolkey] = $checkedmails;

        // role
        if (!isset($SESSION->local_th_bulkenrol_role)) {
            $SESSION->local_th_bulkenrol_role = array();
        }
        $SESSION->local_th_bulkenrol_role[$localth_bulkenrolkey] = $roleid;

        // Create local_th_bulkenrol_inputs array in session.
        if (!isset($SESSION->local_th_bulkenrol_inputs)) {
            $SESSION->local_th_bulkenrol_inputs = array();
        }
        $localth_bulkenroldata = $localth_bulkenrolkey . '_data';
        $SESSION->local_th_bulkenrol_inputs[$localth_bulkenroldata] = $formdata;

        if (!isset($SESSION->local_th_bulkenrol_options)) {
            $SESSION->local_th_bulkenrol_options = array();
        }
        $SESSION->local_th_bulkenrol_options[$localth_bulkenrolkey] = $formdata->option;

        if (!isset($SESSION->local_th_bulkenrol_identifiertype)) {
            $SESSION->local_th_bulkenrol_identifiertype = array();
        }
        $SESSION->local_th_bulkenrol_identifiertype[$localth_bulkenrolkey] = $formdata->identifiertype;

    } else if ($form->is_cancelled()) {
        if (!empty($id)) {
            redirect($CFG->wwwroot . '/course/view.php?id=' . $id, '', 0);
        } else {
            redirect($CFG->wwwroot, '', 0);
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_th_bulkenrol'));
        echo $form->display();
        echo $OUTPUT->footer();
    }
}

if ($localth_bulkenrolkey) {

    $form2 = new confirm_form(null, array('local_th_bulkenrol_key' => $localth_bulkenrolkey, 'courseid' => $id));

    if ($formdata = $form2->get_data()) {
        if (!empty($localth_bulkenrolkey) && !empty($SESSION->local_th_bulkenrol) &&
            array_key_exists($localth_bulkenrolkey, $SESSION->local_th_bulkenrol)) {
            set_time_limit(600);

            $option = $SESSION->local_th_bulkenrol_options[$localth_bulkenrolkey];
            $localth_bulkenrolrole = $SESSION->local_th_bulkenrol_role[$localth_bulkenrolkey];
            if ($option == 0) {
                //enrol
                $msg = local_th_bulkenrol_users($localth_bulkenrolkey, $localth_bulkenrolrole);
            } else if ($option == 2) {
                //unenrol
                $msg = local_th_bulkunenrol_users($localth_bulkenrolkey);
            } else if ($option == 1) {
                //suppend
                $msg = local_th_bulksuspend_users($localth_bulkenrolkey);
            }

            if ($msg->status == 'error') {
                redirect($CFG->wwwroot . '/user/index.php?id=' . $id, "$msg->text", null, \core\output\notification::NOTIFY_ERROR);
            } else {
                redirect($CFG->wwwroot . '/user/index.php?id=' . $id, "$msg->text", null, \core\output\notification::NOTIFY_SUCCESS);
            }

        } else {
            redirect($CFG->wwwroot . '/local/th_bulkenrol/index.php?id=' . $id, '', 0);
        }
    } else if ($form2->is_cancelled()) {
        redirect($CFG->wwwroot . '/local/th_bulkenrol/index.php?id=' . $id, '', 0);
    } else {
        $PAGE->set_url('/local/th_bulkenrol/index.php', array('id' => $id));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_th_bulkenrol'));

        $identifiertype = $SESSION->local_th_bulkenrol_identifiertype[$localth_bulkenrolkey];

        if (!empty($localth_bulkenrolkey) && !empty($SESSION->local_th_bulkenrol) &&
            array_key_exists($localth_bulkenrolkey, $SESSION->local_th_bulkenrol)) {

            $localth_bulkenroldata = $SESSION->local_th_bulkenrol[$localth_bulkenrolkey];
            $localth_bulkenrolrole = $SESSION->local_th_bulkenrol_role[$localth_bulkenrolkey];
            $option = $SESSION->local_th_bulkenrol_options[$localth_bulkenrolkey];

            if (!empty($localth_bulkenroldata)) {

                if ($identifiertype == 0) {
                    if ($option == 0) {
                        //enrol
                        local_th_bulkenrol_display_table($localth_bulkenroldata, LOCALth_bulkenrol_HINT);
                        local_th_bulkenrol_display_table($localth_bulkenroldata, LOCALth_bulkenrol_GROUPINFOS);
                        local_th_bulkenrol_display_table($localth_bulkenroldata, LOCALth_bulkenrol_ENROLUSERS);
                    } else if ($option == 2) {
                        //unenrol
                        local_th_bulkunenrol_display_table($localth_bulkenroldata, LOCALth_bulkenrol_HINT);
                        local_th_bulkunenrol_display_table($localth_bulkenroldata, LOCALth_bulkenrol_ENROLUSERS);
                    } else if ($option == 1) {
                        //suspend
                        local_th_bulkunenrol_display_table($localth_bulkenroldata, LOCALth_bulkenrol_HINT);
                        local_th_bulksuspend_display_table($localth_bulkenroldata, LOCALth_bulkenrol_ENROLUSERS);
                    }
                } else {

                    local_th_bulkenrol_display_table_mahocvien($localth_bulkenroldata, LOCALth_bulkenrol_HINT);
                    local_th_bulkenrol_display_table_mahocvien($localth_bulkenroldata, LOCALth_bulkenrol_GROUPINFOS);
                    local_th_bulkenrol_display_table_mahocvien($localth_bulkenroldata, LOCALth_bulkenrol_ENROLUSERS);
                }
            }
        }

        // Show notification if there aren't any valid email addresses to enrol.
        if (!empty($localth_bulkenroldata) && isset($localth_bulkenroldata->validemailfound) &&
            empty($localth_bulkenroldata->validemailfound)) {
            $a = new stdClass();
            $url = new moodle_url('/local/th_bulkenrol/index.php', array('id' => $id, 'editlist' => $localth_bulkenrolkey));
            $a->url = $url->out();

            if ($identifiertype == 0) {
                $wn = get_string('error_no_valid_email_in_list', 'local_th_bulkenrol', $a);
            } else {
                $wn = get_string('error_no_valid_studentcode_in_list', 'local_th_bulkenrol', $a);
            }

            $notification = new \core\output\notification($wn
                ,
                \core\output\notification::NOTIFY_WARNING);

            $notification->set_show_closebutton(false);
            echo $OUTPUT->render($notification);

            // Otherwise show the enrolment details and the form with the enrol users button.
        } else {
            local_th_bulkenrol_display_enroldetails($localth_bulkenrolrole);
            echo $form2->display();
        }

        echo $OUTPUT->footer();
    }
}
