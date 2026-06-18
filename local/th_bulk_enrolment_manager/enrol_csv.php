<?php

require_once '../../config.php';
require_once 'lib.php';
require_once 'th_bulk_enrolment_manager_form.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/' . $CFG->admin . '/tool/uploaduser/locallib.php';
require_once $CFG->dirroot . '/' . $CFG->admin . '/tool/uploaduser/user_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $SITE;

$localth_bulkenrol_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'local_th_bulk_enrolment_manager', $courseid);
}

require_login($courseid);
require_capability('local/th_bulk_enrolment_manager:view', context_course::instance($COURSE->id));

$context = context_system::instance();
$baseurl = new moodle_url('/local/th_bulk_enrolment_manager/enrol_csv.php');
$PAGE->set_url('/local/th_bulk_enrolment_manager/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_bulk_enrolment_manager', 'local_th_bulk_enrolment_manager'));
$title = get_string('enrolbycsv', 'local_th_bulk_enrolment_manager');
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'local_th_bulk_enrolment_manager'));
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'local_th_bulk_enrolment_manager'), new moodle_url('/local/th_bulk_enrolment_manager/view.php'));
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
    throw new coding_exception('Can not instantiate enrol_manual');
}

if (empty($localth_bulkenrol_csvkey)) {
    $form = new th_excel_bulk_enrolment_manager_form();

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/my'));
    } else if ($fromform = $form->get_data()) {
        $content = $form->get_file_content('file');
        $lines = explode(PHP_EOL, $content);
        $result = th_check_bulk_enrol_csv($lines);

        $localth_bulkenrol_csvkey = $courseid . '_' . time();
        $_SESSION['local_th_bulkenrol_csv'][$localth_bulkenrol_csvkey] = $result;
        redirect(new moodle_url('/local/th_bulk_enrolment_manager/enrol_csv.php', ['key' => $localth_bulkenrol_csvkey]));
    } else {
        echo $OUTPUT->header();
		if ($editcontrols = local_th_bulk_enrolment_manager_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
        echo $OUTPUT->heading($title);
        $form->display();
        echo $OUTPUT->footer();
    }
    exit;
}

$form2 = new confirm_form(null, array('local_th_bulkenrol_csv_key' => $localth_bulkenrol_csvkey));
if ($form2->is_cancelled()) {
    redirect(new moodle_url('/local/th_bulk_enrolment_manager/enrol_csv.php'));
} else if ($formdata = $form2->get_data()) {
    if (!empty($_SESSION['local_th_bulkenrol_csv'][$localth_bulkenrol_csvkey])) {
        set_time_limit(600);
        $data = $_SESSION['local_th_bulkenrol_csv'][$localth_bulkenrol_csvkey];
        foreach ($data->user_enroled as $entry) {
            $instance = $DB->get_record('enrol', array('courseid' => $entry->courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
            $context = context_course::instance($entry->courseid);

            switch ($entry->status) {
                case 1:
                    $enrol_manual->enrol_user($instance, $entry->id, $entry->roleid, $entry->timestart, $entry->timeend, ENROL_USER_ACTIVE);
                    break;
                case 0:
                    $enrol_manual->unenrol_user($instance, $entry->id);
                    break;
                case 2:
                    // $DB->set_field('user_enrolments', 'status', 1, ['userid' => $entry->id, 'enrolid' => $instance->id]);
                    $enrolplugin = enrol_get_plugin('manual');
                    $manualinstance = $DB->get_record('enrol', [
                        'courseid' => $entry->courseid,
                        'enrol' => 'manual'
                    ], '*', MUST_EXIST);
                
                    $enrolplugin->update_user_enrol($manualinstance, $entry->id, 1);
                    break;
            }
        }

        unset($_SESSION['local_th_bulkenrol_csv'][$localth_bulkenrol_csvkey]);

        echo $OUTPUT->header();
        if ($editcontrols = local_th_bulk_enrolment_manager_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
        echo $OUTPUT->heading(get_string('enrolbycsv', 'local_th_bulk_enrolment_manager'));
        echo $OUTPUT->notification(get_string('user_enrolled_success', 'local_th_bulk_enrolment_manager'), 'notifysuccess');
        echo html_writer::div(
            html_writer::link(
                new moodle_url('/local/th_bulk_enrolment_manager/enrol_csv.php'),
                get_string('back'), ['class' => 'btn btn-secondary']
            ),
            'text-center mt-3'
        );
        echo $OUTPUT->footer();
    }
    exit;
}

// Confirmation page
$data = $_SESSION['local_th_bulkenrol_csv'][$localth_bulkenrol_csvkey] ?? null;
echo $OUTPUT->header();
if ($editcontrols = local_th_bulk_enrolment_manager_controls($context, $baseurl)) {
    echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading(get_string('enrolbycsv', 'local_th_bulk_enrolment_manager'));

if (!empty($data->error_messages)) {
    echo html_writer::tag('h4', get_string('Hints', 'local_th_bulk_enrolment_manager'));
    echo th_display_table_error($data->error_messages);
}

if (!empty($data->user_enroled)) {
    echo html_writer::tag('h4', get_string('users_to_enrol_in_course', 'local_th_bulk_enrolment_manager'));
    echo th_display_table_enrol($data->user_enroled);
    $form2->display();
}

if ($data->validfound == 0) {
    echo html_writer::div(
        html_writer::link(
            new moodle_url('/local/th_bulk_enrolment_manager/enrol_csv.php'),
            get_string('back'), ['class' => 'btn btn-secondary']
        ),
        'text-center mt-3'
    );
}

echo $OUTPUT->footer();
