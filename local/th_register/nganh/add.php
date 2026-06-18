<?php

require_once '../../../config.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';
require_once $CFG->dirroot . '/local/th_register/nganh/lib.php';
require_once $CFG->dirroot . '/local/th_register/nganh/th_register_nganh_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$th_register_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'local_th_register', $courseid);
}

require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$pageurl = '/local/th_register/nganh/add.php';
$title = get_string('them_nganh_bulk', 'local_th_register');
$PAGE->set_url('/local/th_register/nganh/add.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('them_nganh_bulk', 'local_th_register'));
$PAGE->set_title(get_string('bulk_add_major', 'local_th_register'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('local_thlib/main', 'addAsteriskToCustomRequiredFieldForm', array($CFG->wwwroot));

$PAGE->navbar->add(get_string('manage_majors', 'local_th_register'), new moodle_url('/local/th_register/nganh/index.php'));
$editurl = new moodle_url('/local/th_register/nganh/add.php');
$settingsnode = $PAGE->navbar->add(get_string('bulk_add_major', 'local_th_register'), $editurl);
$settingsnode->make_active();

if (empty($th_register_csvkey)) {
    $form1 = new up_form();

    if ($form1->is_cancelled()) {
        // Cancelled forms redirect to the course main page.
        redirect(new moodle_url('/local/th_register/nganh/index.php'));
    } else if ($fromform = $form1->get_data()) {
        $content = $form1->get_file_content('list');
        $contents = th_register_parse_course($content);
        $list = th_register_get_content($contents);
        $option = $fromform->option;
        if ($option == 0) {
            $check = th_register_check_add_nganh($list);
        } else if ($option == 1) {
            $check = th_register_check_update_nganh($list);
        } else if ($option == 2) {
            $check = th_register_check_add_and_update_nganh($list);
        }

        // Save data in Session.
        $th_register_csvkey = $option . '_' . $courseid . '_' . time();
        $SESSION->local_th_register_csv[$th_register_csvkey] = $check;
    } else {
        // form didn't validate or this is the first display

        echo $OUTPUT->header();
        // echo "</br>";
        $baseurl = new moodle_url('/local/th_register/nganh/add.php');

        if ($editcontrols = local_th_register_nganh_controls($context, $baseurl)) {
            echo $OUTPUT->render($editcontrols);
        }
        echo $OUTPUT->heading($title);
        $form1->display();
        echo $OUTPUT->footer();
    }
}

if ($th_register_csvkey) {
    $form2 = new confirm_form(null, array('th_register_csvkey' => $th_register_csvkey));

    if ($form2->is_cancelled()) {
        // Cancelled forms redirect to the course main page.
        $courseurl = new moodle_url('/local/th_register/nganh/add.php');
        redirect($courseurl);
    } else if ($formdata = $form2->get_data()) {
        if (
            !empty($th_register_csvkey) && !empty($SESSION->local_th_register_csv) &&
            array_key_exists($th_register_csvkey, $SESSION->local_th_register_csv)
        ) {

            $data = $SESSION->local_th_register_csv[$th_register_csvkey];
            $option = $th_register_csvkey[0];
            if ($option == 0) {
                $add = $data->add;

                foreach ($add as $r) {
                    $record = new stdClass();
                    $record->ma_nganh = $r->ma_nganh;
                    $record->ten_nganh = $r->ten_nganh;
                    $record->hoc_phi_mot_tin_chi = $r->hoc_phi_mot_tin_chi;
                    $record->ngay_tao = time();
                    $record->nguoi_tao = $USER->id;
                    $DB->insert_record('th_nganh_hoc', $record, false);
                }
            } else if ($option == 1) {
                $update = $data->update;

                foreach ($update as $r) {
                    $record = new stdClass();
                    $record->id = $r->id;
                    $record->ma_nganh = $r->ma_nganh;
                    $record->ten_nganh = $r->ten_nganh;
                    $record->hoc_phi_mot_tin_chi = $r->hoc_phi_mot_tin_chi;
                    $record->ngay_sua = time();
                    $record->nguoi_sua = $USER->id;
                    $DB->update_record('th_nganh_hoc', $record, false);
                }
            } else if ($option == 2) {
                $add = $data->add;
                $update = $data->update;

                foreach ($add as $r) {
                    $record = new stdClass();
                    $record->ma_nganh = $r->ma_nganh;
                    $record->ten_nganh = $r->ten_nganh;
                    $record->hoc_phi_mot_tin_chi = $r->hoc_phi_mot_tin_chi;
                    $record->ngay_tao = time();
                    $record->nguoi_tao = $USER->id;
                    $DB->insert_record('th_nganh_hoc', $record, false);
                }

                foreach ($update as $r) {
                    $record = new stdClass();
                    $record->id = $r->id;
                    $record->ma_nganh = $r->ma_nganh;
                    $record->ten_nganh = $r->ten_nganh;
                    $record->hoc_phi_mot_tin_chi = $r->hoc_phi_mot_tin_chi;
                    $record->ngay_sua = time();
                    $record->nguoi_sua = $USER->id;
                    $DB->update_record('th_nganh_hoc', $record, false);
                }
            }
            redirect($CFG->wwwroot . '/local/th_register/nganh/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
    } else {
        echo $OUTPUT->header();
        if (
            !empty($th_register_csvkey) && !empty($SESSION->local_th_register_csv) &&
            array_key_exists($th_register_csvkey, $SESSION->local_th_register_csv)
        ) {

            $localth_register_csvdata = $SESSION->local_th_register_csv[$th_register_csvkey];
            $option = $th_register_csvkey[0];

            if ($option == 0) {
                echo $OUTPUT->heading(get_string('them_nganh', 'local_th_register'));
                if (!empty($localth_register_csvdata->error)) {

                    $errors = $localth_register_csvdata->error;

                    $html1 = th_register_table_add_nganh_error($errors);
                    echo $OUTPUT->heading(get_string('hints', 'local_th_register'));
                    echo $html1;
                }
                if (!empty($localth_register_csvdata->add)) {

                    $record = $localth_register_csvdata->add;

                    $html = th_display_table_add_nganh($record);
                    echo $OUTPUT->heading(get_string('confirm_add_major', 'local_th_register'));
                    echo $html;
                }
            } else if ($option == 1) {
                echo $OUTPUT->heading(get_string('sua_nganh', 'local_th_register'));
                if (!empty($localth_register_csvdata->error)) {

                    $errors = $localth_register_csvdata->error;

                    $html1 = th_register_table_add_nganh_error($errors);
                    echo $OUTPUT->heading(get_string('hints', 'local_th_register'));
                    echo $html1;
                }
                $update = $localth_register_csvdata->update;
                $no_update = $localth_register_csvdata->no_update;

                $html = th_register_table_update_nganh($update, $no_update);
                echo $OUTPUT->heading(get_string('confirm_update_major', 'local_th_register'));
                echo $html;
            } else if ($option == 2) {
                echo $OUTPUT->heading(get_string('add_update_major', 'local_th_register'));
                $add = $localth_register_csvdata->add;
                $update = $localth_register_csvdata->update;
                $no_update = $localth_register_csvdata->no_update;

                $table = th_register_table_add_and_update_nganh($add, $update, $no_update);
                echo $OUTPUT->heading(get_string('confirm_add_update_major', 'local_th_register'));
                echo $table;
            }
        }

        if (
            !empty($localth_register_csvdata) && isset($localth_register_csvdata->valid_time_found) &&
            empty($localth_register_csvdata->valid_time_found)
        ) {
            $a = new stdClass();
            $url = new moodle_url('/local/th_register/nganh/add.php');
            $a->url = $url->out();

            $wn = get_string('error_no_valid', 'local_th_register', $a);

            $notification = new \core\output\notification(
                $wn,
                \core\output\notification::NOTIFY_WARNING
            );

            $notification->set_show_closebutton(false);
            echo $OUTPUT->render($notification);
        } else {
            echo $form2->display();
        }
        echo $OUTPUT->footer();
    }
}
