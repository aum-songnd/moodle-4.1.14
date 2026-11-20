<?php
require_once(__DIR__ . '/../../config.php');

require_login();

use local_th_qaa_manager\service\ticket_service;
use core_user\fields;

$qaapairid = required_param('qaapairid', PARAM_INT);
$cancel = optional_param('cancel', 0, PARAM_BOOL);
$rejectreason = optional_param('rejectreason', '', PARAM_TEXT);

$context = context_system::instance();
require_capability('local/th_qaa_manager:approvequestion', $context);

$manageurl = new moodle_url('/local/th_qaa_manager/manage.php', [
    'status' => ticket_service::STATUS_APPROVED,
]);

$url = new moodle_url('/local/th_qaa_manager/reject_question.php', [
    'qaapairid' => $qaapairid,
]);

if ($cancel) {
    redirect($manageurl);
}

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

if (!in_array((int)$qaapair->qaastatus, [0, 1], true)) {
    redirect($manageurl, get_string('invalidticket', 'local_th_qaa_manager'), null, \core\output\notification::NOTIFY_WARNING);
}

if (data_submitted() && confirm_sesskey()) {
    if (trim($rejectreason) === '') {
        \core\notification::error(get_string('rejectreasonrequired', 'local_th_qaa_manager'));
    } else {
        ticket_service::reject_question($qaapairid, $USER->id, $rejectreason);

        redirect(
            new moodle_url('/local/th_qaa_manager/manage.php', [
                'status' => ticket_service::STATUS_APPROVED,
            ]),
            get_string('questionrejectdone', 'local_th_qaa_manager'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('rejectquestion', 'local_th_qaa_manager'));
$PAGE->set_heading(get_string('rejectquestion', 'local_th_qaa_manager'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('rejectquestion', 'local_th_qaa_manager'), $url);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('rejectquestion', 'local_th_qaa_manager'), 3);

$table = new html_table();
$table->attributes['class'] = 'generaltable w-100';
$table->data = [
    [get_string('course', 'local_th_qaa_manager'), format_string($qaapair->coursefullname)],
    [get_string('student', 'local_th_qaa_manager'), fullname($qaapair)],
    [
        get_string('status', 'local_th_qaa_manager'),
        ticket_service::status_label(((int)$qaapair->qaastatus === 1) ? ticket_service::STATUS_APPROVED : ticket_service::STATUS_NEW)
    ],
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

echo $OUTPUT->heading(get_string('rejectreason', 'local_th_qaa_manager'), 4);

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $url->out(false),
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey(),
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'qaapairid',
    'value' => $qaapairid,
]);

echo html_writer::tag('textarea', s($rejectreason), [
    'name' => 'rejectreason',
    'rows' => 6,
    'class' => 'form-control w-100',
    'required' => 'required',
]);

echo html_writer::div(
    html_writer::tag(
        'button',
        get_string('answer', 'local_th_qaa_manager'),
        [
            'type' => 'submit',
            'class' => 'btn btn-danger mr-2',
            'onclick' => "return confirm('" . s(get_string('confirmrejectquestion', 'local_th_qaa_manager')) . "');",
        ]
    ) .
    html_writer::link(
        $manageurl,
        get_string('cancel'),
        [
            'class' => 'btn btn-secondary'
        ]
    ),
    'mt-3'
);

echo html_writer::end_tag('form');

echo $OUTPUT->footer();