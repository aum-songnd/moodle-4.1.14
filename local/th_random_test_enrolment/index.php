<?php

use local_th_random_test_enrolment\confirm_form;
use local_th_random_test_enrolment\th_random_test_enrolment_form;

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_random_test_enrolment/locallib.php';

global $PAGE, $OUTPUT, $SESSION;

$id = required_param('id', PARAM_INT);
$localth_random_test_enrolmentkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

$context = context_system::instance();

if (!empty($id)) {
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);

    $PAGE->set_context($context);
    $PAGE->set_url('/local/th_random_test_enrolment/index.php', array('id' => $id));
    $PAGE->set_title("$course->shortname: " . get_string('pluginname', 'local_th_random_test_enrolment'));
    $PAGE->set_heading($course->fullname);

    require_login($course);
}

if (!has_capability('local/th_random_test_enrolment:enrolusers', $context)) {
    print_error('nopermissions', 'error', '', 'local/th_random_test_enrolment:enrolusers');
}

$PAGE->set_pagelayout('incourse');

if (empty($localth_random_test_enrolmentkey)) {
    $form = new th_random_test_enrolment_form(null, array('courseid' => $id));
    if ($formdata = $form->get_data()) {

        $courseid = $formdata->id;
        $roleid = $formdata->role;

        if ($formdata->identifiertype == 0) {
            $emails = $formdata->usermails;
            $checkedmails = local_th_random_test_enrolment_check_user_mails($emails, $courseid);
        }

        if ($formdata->identifiertype == 1) {
            $masinhviens = $formdata->usermails;
            $checkedmasinhvien = local_th_random_test_enrolment_check_user_masinhvien($masinhviens, $courseid);
            $emails = $masinhviens;
            $checkedmails = $checkedmasinhvien;
        }

        // Create local_th_random_test_enrolment array in Session.
        {
            if (!isset($SESSION->local_th_random_test_enrolment)) {
                $SESSION->local_th_random_test_enrolment = array();
            }
        }

        // Save data in Session.
        $localth_random_test_enrolmentkey = $courseid . '_' . time();
        $SESSION->local_th_random_test_enrolment[$localth_random_test_enrolmentkey] = $checkedmails;

        // role
        if (!isset($SESSION->local_th_random_test_enrolment_role)) {
            $SESSION->local_th_random_test_enrolment_role = array();
        }
        $SESSION->local_th_random_test_enrolment_role[$localth_random_test_enrolmentkey] = $roleid;

        // Create local_th_random_test_enrolment_inputs array in session.
        if (!isset($SESSION->local_th_random_test_enrolment_inputs)) {
            $SESSION->local_th_random_test_enrolment_inputs = array();
        }
        $localth_random_test_enrolmentdata = $localth_random_test_enrolmentkey . '_data';
        $SESSION->local_th_random_test_enrolment_inputs[$localth_random_test_enrolmentdata] = $formdata;

        if (!isset($SESSION->local_th_random_test_enrolment_identifiertype)) {
            $SESSION->local_th_random_test_enrolment_identifiertype = array();
        }
        $SESSION->local_th_random_test_enrolment_identifiertype[$localth_random_test_enrolmentkey] = $formdata->identifiertype;

    } else if ($form->is_cancelled()) {
        if (!empty($id)) {
            redirect($CFG->wwwroot . '/course/view.php?id=' . $id, '', 0);
        } else {
            redirect($CFG->wwwroot, '', 0);
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_th_random_test_enrolment'));
        echo $form->display();
        echo $OUTPUT->footer();
    }
}

if ($localth_random_test_enrolmentkey) {

    $form2 = new confirm_form(null, array('local_th_random_test_enrolment_key' => $localth_random_test_enrolmentkey, 'courseid' => $id));

    if ($formdata = $form2->get_data()) {
        if (!empty($localth_random_test_enrolmentkey) && !empty($SESSION->local_th_random_test_enrolment) &&
            array_key_exists($localth_random_test_enrolmentkey, $SESSION->local_th_random_test_enrolment)) {
            set_time_limit(600);

            $localth_random_test_enrolmentrole = $SESSION->local_th_random_test_enrolment_role[$localth_random_test_enrolmentkey];
                //enrol
            $msg = local_th_random_test_enrolment_users($localth_random_test_enrolmentkey, $localth_random_test_enrolmentrole);

            if ($msg->status == 'error') {
                redirect($CFG->wwwroot . '/user/index.php?id=' . $id, "$msg->text", null, \core\output\notification::NOTIFY_ERROR);
            } else {
                redirect($CFG->wwwroot . '/user/index.php?id=' . $id, "$msg->text", null, \core\output\notification::NOTIFY_SUCCESS);
            }

        } else {
            redirect($CFG->wwwroot . '/local/th_random_test_enrolment/index.php?id=' . $id, '', 0);
        }
    } else if ($form2->is_cancelled()) {
        redirect($CFG->wwwroot . '/local/th_random_test_enrolment/index.php?id=' . $id, '', 0);
    } else {
        $PAGE->set_url('/local/th_random_test_enrolment/index.php', array('id' => $id));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_th_random_test_enrolment'));

        $identifiertype = $SESSION->local_th_random_test_enrolment_identifiertype[$localth_random_test_enrolmentkey];

        if (!empty($localth_random_test_enrolmentkey) && !empty($SESSION->local_th_random_test_enrolment) &&
            array_key_exists($localth_random_test_enrolmentkey, $SESSION->local_th_random_test_enrolment)) {

            $localth_random_test_enrolmentdata = $SESSION->local_th_random_test_enrolment[$localth_random_test_enrolmentkey];
            $localth_random_test_enrolmentrole = $SESSION->local_th_random_test_enrolment_role[$localth_random_test_enrolmentkey];

            if (!empty($localth_random_test_enrolmentdata)) {

                if ($identifiertype == 0) {
                    local_th_random_test_enrolment_display_table($localth_random_test_enrolmentdata, LOCALth_random_test_enrolment_HINT);
                    local_th_random_test_enrolment_display_table($localth_random_test_enrolmentdata, LOCALth_random_test_enrolment_GROUPINFOS);
                    local_th_random_test_enrolment_display_table($localth_random_test_enrolmentdata, LOCALth_random_test_enrolment_ENROLUSERS);
                } else {

                    local_th_random_test_enrolment_display_table_mahocvien($localth_random_test_enrolmentdata, LOCALth_random_test_enrolment_HINT);
                    local_th_random_test_enrolment_display_table_mahocvien($localth_random_test_enrolmentdata, LOCALth_random_test_enrolment_GROUPINFOS);
                    local_th_random_test_enrolment_display_table_mahocvien($localth_random_test_enrolmentdata, LOCALth_random_test_enrolment_ENROLUSERS);
                }
            }
        }

        // Show notification if there aren't any valid email addresses to enrol.
        if (!empty($localth_random_test_enrolmentdata) && isset($localth_random_test_enrolmentdata->validemailfound) &&
            empty($localth_random_test_enrolmentdata->validemailfound)) {
            $a = new stdClass();
            $url = new moodle_url('/local/th_random_test_enrolment/index.php', array('id' => $id, 'editlist' => $localth_random_test_enrolmentkey));
            $a->url = $url->out();

            if ($identifiertype == 0) {
                $wn = get_string('error_no_valid_email_in_list', 'local_th_random_test_enrolment', $a);
            } else {
                $wn = get_string('error_no_valid_studentcode_in_list', 'local_th_random_test_enrolment', $a);
            }

            $notification = new \core\output\notification($wn
                ,
                \core\output\notification::NOTIFY_WARNING);

            $notification->set_show_closebutton(false);
            echo $OUTPUT->render($notification);

            // Otherwise show the enrolment details and the form with the enrol users button.
        } else {
            local_th_random_test_enrolment_display_enroldetails($localth_random_test_enrolmentrole);
            echo $form2->display();
        }

        echo $OUTPUT->footer();
    }
}
