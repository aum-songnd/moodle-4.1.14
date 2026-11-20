<?php
require_once(__DIR__ . '/../../config.php');

require_login();

use local_th_qaa_manager\service\ticket_service;
use core_user\fields;

$qaapairid = required_param('qaapairid', PARAM_INT);

$context = context_system::instance();
require_capability('local/th_qaa_manager:manage', $context);

$url = new moodle_url('/local/th_qaa_manager/view_question.php', [
    'qaapairid' => $qaapairid,
]);

$userfieldsapi = fields::for_name();
$userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

$sql = "SELECT qp.id AS qaapairid,
               qp.qaaid,
               qp.message AS question,
               qp.status AS qaastatus,
               q.course AS courseid,
               c.fullname AS coursefullname,
               {$userfields}
          FROM {qaapairs} qp
          JOIN {qaa} q ON q.id = qp.qaaid
          JOIN {course} c ON c.id = q.course
          JOIN {user} u ON u.id = qp.studentid
         WHERE qp.id = :qaapairid";

$qaapair = $DB->get_record_sql($sql, [
    'qaapairid' => $qaapairid,
], MUST_EXIST);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('questiondetail', 'local_th_qaa_manager'));
$PAGE->set_heading(get_string('questiondetail', 'local_th_qaa_manager'));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('questiondetail', 'local_th_qaa_manager'), $url);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('questiondetail', 'local_th_qaa_manager'), 3);

$table = new html_table();
$table->attributes['class'] = 'generaltable w-100';
$table->data = [
    [get_string('course', 'local_th_qaa_manager'), format_string($qaapair->coursefullname)],
    [get_string('student', 'local_th_qaa_manager'), fullname($qaapair)],
    [get_string('status', 'local_th_qaa_manager'), ticket_service::status_label(ticket_service::STATUS_NEW)],
];

echo html_writer::table($table);

echo $OUTPUT->heading(get_string('question', 'local_th_qaa_manager'), 4);

$cm = get_coursemodule_from_instance('qaa', $qaapair->qaaid);
$modcontext = $cm ? context_module::instance($cm->id) : $context;
$questioncontent = file_rewrite_pluginfile_urls(
    (string)$qaapair->question,
    'pluginfile.php',
    $modcontext->id,
    'mod_qaa',
    'message',
    $qaapair->qaapairid
);

echo html_writer::div(
    format_text($questioncontent, FORMAT_HTML, ['context' => $modcontext]),
    'qaa-manager-question'
);

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/th_qaa_manager/manage.php', [
            'status' => ticket_service::STATUS_APPROVED,
        ]),
        get_string('back'),
        ['class' => 'btn btn-secondary']
    ),
    'mt-3'
);

echo $OUTPUT->footer();
