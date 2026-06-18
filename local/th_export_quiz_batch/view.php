<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/th_export_quiz_batch/classes/form/export_form.php');
require_once($CFG->dirroot . '/local/th_export_quiz_batch/classes/task/export_quiz_task.php');

require_login();
$context = context_system::instance();
require_capability('local/th_export_quiz_batch:view', $context);

$tab = optional_param('tab', 'notexported', PARAM_ALPHA);

$url = new moodle_url('/local/th_export_quiz_batch/view.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_th_export_quiz_batch'));
$PAGE->set_heading(get_string('pluginname', 'local_th_export_quiz_batch'));
$PAGE->navbar->add(get_string('pluginname', 'local_th_export_quiz_batch'), $url);

echo $OUTPUT->header();

echo html_writer::div(
    $OUTPUT->heading(get_string('heading', 'local_th_export_quiz_batch')),
    'text-center'
);


$tabs = [
    new tabobject('notexported', new moodle_url($url, ['tab' => 'notexported']), get_string('tab_notexported', 'local_th_export_quiz_batch')),
    new tabobject('processing', new moodle_url($url, ['tab' => 'processing']), get_string('tab_processing', 'local_th_export_quiz_batch')),
    new tabobject('exported', new moodle_url($url, ['tab' => 'exported']), get_string('tab_exported', 'local_th_export_quiz_batch')),
];

echo $OUTPUT->tabtree($tabs, $tab);

global $DB, $USER;

if ($tab === 'notexported') {
    $mform = new \local_th_export_quiz_batch\form\export_form();

    if ($mform->is_cancelled()) {
        redirect($url);
    } else if ($data = $mform->get_data()) {
        foreach ($data->courseids as $courseid) {

            $quizzes = $DB->get_records('quiz', ['course' => $courseid]);
            if (empty($quizzes)) {
                continue;
            }

            $exists = $DB->record_exists_sql("
                SELECT 1
                FROM {local_th_export_quiz_batch}
                WHERE courseid = :courseid
                  AND " . $DB->sql_compare_text('status') . " = " . $DB->sql_compare_text(':status'),
                ['courseid' => $courseid, 'status' => 'processing']
            );

            if ($exists) {
                continue;
            }
            $record = new stdClass();
            $record->userid = $USER->id;
            $record->courseid = $courseid;
            $record->quizid = null;
            $record->status = 'processing';
            $record->createdate = time();
            $record->filepath = null;
            $record->fileurl = null;
            $id = $DB->insert_record('local_th_export_quiz_batch', $record);

            $quizids = [];
            foreach ($quizzes as $quiz) {
                $quizids[] = $quiz->id;
            }
            $task = new \local_th_export_quiz_batch\task\export_quiz_task();
            $task->set_custom_data([
                'courseid' => $courseid,
                'quizids'  => $quizids,
                'userid'   => $USER->id,
                'recordid' => $id
            ]);
            $task->set_component('local_th_export_quiz_batch');
            \core\task\manager::queue_adhoc_task($task);
        }

        redirect(new moodle_url('/local/th_export_quiz_batch/view.php', ['tab' => 'processing']));
    }

    $mform->display();

} else if ($tab === 'processing') {
    $records = $DB->get_records('local_th_export_quiz_batch', ['status' => 'processing']);

    if (empty($records)) {
        echo $OUTPUT->notification(get_string('noprocessing', 'local_th_export_quiz_batch'), 'notifymessage');
    } else {
        echo html_writer::start_tag('table', ['class' => 'generaltable']);
        echo html_writer::start_tag('thead');
        echo html_writer::tag('tr',
            html_writer::tag('th', get_string('course', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('requester', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('time', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('status', 'local_th_export_quiz_batch'))
        );
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($records as $rec) {
            $course = $DB->get_record('course', ['id' => $rec->courseid]);
            $user = $DB->get_record('user', ['id' => $rec->userid]);
            echo html_writer::tag('tr',
                html_writer::tag('td', format_string($course->fullname)) .
                html_writer::tag('td', fullname($user)) .
                html_writer::tag('td', userdate($rec->createdate)) .
                html_writer::tag('td', get_string('status_processing', 'local_th_export_quiz_batch'))
            );
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
    }

} else if ($tab === 'exported') {
    $records = $DB->get_records('local_th_export_quiz_batch', ['status' => 'done']);

    if (empty($records)) {
        echo $OUTPUT->notification(get_string('nodataexported', 'local_th_export_quiz_batch'), 'notifymessage');
    } else {
        echo html_writer::start_tag('table', ['class' => 'generaltable']);
        echo html_writer::start_tag('thead');
        echo html_writer::tag('tr',
            html_writer::tag('th', get_string('course', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('creator', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('createdate', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('zipfile', 'local_th_export_quiz_batch')) .
            html_writer::tag('th', get_string('action', 'local_th_export_quiz_batch'))
        );
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($records as $rec) {
            $course = $DB->get_record('course', ['id' => $rec->courseid], '*', MUST_EXIST);
            $user   = $DB->get_record('user', ['id' => $rec->userid], '*', MUST_EXIST);

            $downloadlink = '-';
            $deletebtn    = '-';

            if (!empty($rec->filepath)) {
                $fullpath = $rec->filepath;
                if (file_exists($fullpath)) {
                    // Link tải
                    $downloadurl  = new moodle_url($rec->fileurl, ['sesskey' => sesskey()]);
                    $downloadlink = html_writer::link(
                        $downloadurl,
                        get_string('downloadzip', 'local_th_export_quiz_batch')
                    );

                    // Link xoá
                    $deleteurl = new moodle_url(
                        '/local/th_export_quiz_batch/delete.php',
                        ['id' => $rec->id, 'sesskey' => sesskey()]
                    );
                    $deletebtn = html_writer::link(
                        $deleteurl,
                        get_string('delete'),
                        ['onclick' => "return confirm('" .
                            get_string('confirmdeletefile', 'local_th_export_quiz_batch') . "');"]
                    );
                } else {
                    $downloadlink = html_writer::span(
                        get_string('filenotfound', 'local_th_export_quiz_batch'),
                        'text-warning'
                    );

                    // Link xoá
                    $deleteurl = new moodle_url(
                        '/local/th_export_quiz_batch/delete.php',
                        ['id' => $rec->id, 'sesskey' => sesskey()]
                    );
                    $deletebtn = html_writer::link(
                        $deleteurl,
                        get_string('delete'),
                        ['onclick' => "return confirm('" .
                            get_string('confirmdeletefile', 'local_th_export_quiz_batch') . "');"]
                    );
                }
            }

            echo html_writer::tag('tr',
                html_writer::tag('td', format_string($course->fullname)) .
                html_writer::tag('td', fullname($user)) .
                html_writer::tag('td', userdate($rec->createdate)) .
                html_writer::tag('td', $downloadlink) .
                html_writer::tag('td', $deletebtn)
            );
        }


        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
    }
}

echo $OUTPUT->footer();
