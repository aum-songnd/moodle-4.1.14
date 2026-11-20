<?php
require_once(__DIR__ . '/../../config.php');

require_login();

require_once($CFG->dirroot . '/local/th_qaa_manager/classes/form/answer_form.php');

use local_th_qaa_manager\form\answer_form;
use local_th_qaa_manager\service\ticket_service;
use local_th_qaa_manager\service\answer_service;
use core_user\fields;

$id = required_param('id', PARAM_INT);
$review = optional_param('review', 0, PARAM_BOOL);
$returnstatus = optional_param('status', 0, PARAM_INT);
$returnpage = optional_param('page', 0, PARAM_INT);

$context = context_system::instance();

$userfieldsapi = fields::for_name();
$userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

$sql = "SELECT t.*,
               qp.qaaid,
               qp.message AS question,
               q.course AS courseid,
               c.fullname AS coursefullname,
               {$userfields}
          FROM {local_th_qaa_ticket} t
          JOIN {qaapairs} qp ON qp.id = t.qaapairid
          JOIN {qaa} q ON q.id = qp.qaaid
          JOIN {course} c ON c.id = q.course
          JOIN {user} u ON u.id = qp.studentid
         WHERE t.id = :id";

$ticket = $DB->get_record_sql($sql, [
    'id' => $id,
], MUST_EXIST);

$ismanager = has_capability('local/th_qaa_manager:manage', $context);
$isanswerer = has_capability('local/th_qaa_manager:answer', $context);
if (!$ismanager) {

    require_capability(
        'local/th_qaa_manager:answer',
        $context
    );

    $canview = false;

    if ((int)$ticket->assigneduserid === (int)$USER->id) {
        $canview = true;
    } else if ($DB->record_exists('local_th_qaa_answer', ['ticketid' => $ticket->id, 'userid' => $USER->id])) {
        $canview = true;
    }


    if (!$canview) {
        throw new moodle_exception(
            'notassignedtoyou',
            'local_th_qaa_manager'
        );
    }

    $latestanswer = answer_service::get_latest_answer($ticket->id, (int)$USER->id);
} else {
    $latestanswer = answer_service::get_latest_answer($ticket->id);
}

$cananswer = !$ismanager
    && (int)$ticket->assigneduserid === (int)$USER->id
    && (int)$ticket->status === ticket_service::STATUS_ASSIGNED;

$url = new moodle_url('/local/th_qaa_manager/view.php', [
    'id' => $ticket->id,
    'status' => $returnstatus,
    'page' => $returnpage,
]);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('questiondetail', 'local_th_qaa_manager'));
$PAGE->set_heading(get_string('questiondetail', 'local_th_qaa_manager'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('questiondetail', 'local_th_qaa_manager'));

$mform = null;

if ($cananswer) {
    $servermaxbytes = get_user_max_upload_file_size(
        $context,
        $CFG->maxbytes
    );

    $imagemaxbytes = 5 * 1024 * 1024;

    if ($servermaxbytes > 0) {
        $imagemaxbytes = min($servermaxbytes, $imagemaxbytes);
    }

    $editoroptions = [
        'context' => $context,
        'subdirs' => 0,
        'maxbytes' => $imagemaxbytes,
        'areamaxbytes' => 50 * 1024 * 1024,
        'maxfiles' => 20,
        'accepted_types' => ['web_image'],
        'trusttext' => false,
    ];

    $mform = new answer_form(
        $url,
        [
            'editoroptions' => $editoroptions,
        ]
    );

    $draftitemid = file_get_submitted_draft_itemid('answer');

    $answercontent = $latestanswer
        ? (string)$latestanswer->content
        : '';

    $answerformat = $latestanswer
        ? (int)$latestanswer->contentformat
        : FORMAT_HTML;

    $answercontent = file_prepare_draft_area(
        $draftitemid,
        $context->id,
        'local_th_qaa_manager',
        'answer',
        $ticket->id,
        $editoroptions,
        $answercontent
    );

    $mform->set_data((object)[
        'id' => $ticket->id,
        'answer' => [
            'text' => $answercontent,
            'format' => $answerformat,
            'itemid' => $draftitemid,
        ],
    ]);

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/th_qaa_manager/question.php'));
    } else if ($data = $mform->get_data()) {
        require_sesskey();

        $draftitemid = (int)($data->answer['itemid'] ?? 0);
        $content = (string)($data->answer['text'] ?? '');
        $contentformat = (int)($data->answer['format'] ?? FORMAT_HTML);

        $content = file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'local_th_qaa_manager',
            'answer',
            $ticket->id,
            $editoroptions,
            $content
        );

        answer_service::submit_answer(
            $ticket->id,
            $USER->id,
            $content,
            $contentformat
        );

        redirect(new moodle_url('/local/th_qaa_manager/question.php'));
    }
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('questiondetail', 'local_th_qaa_manager'), 3);

$table = new html_table();
$table->attributes['class'] = 'generaltable w-100';
$table->data = [
    [get_string('course', 'local_th_qaa_manager'), format_string($ticket->coursefullname)],
    [get_string('student', 'local_th_qaa_manager'), fullname($ticket)],
    [get_string('status', 'local_th_qaa_manager'), ticket_service::status_label((int)$ticket->status)],
];

if (!empty($ticket->rejectreason)) {
    $table->data[] = [get_string('rejectreason', 'local_th_qaa_manager'), s($ticket->rejectreason)];
}

echo html_writer::table($table);
echo $OUTPUT->heading(get_string('question', 'local_th_qaa_manager'), 4);
$cm = get_coursemodule_from_instance('qaa', $ticket->qaaid);
$modcontext = $cm ? context_module::instance($cm->id) : $context;
$questioncontent = file_rewrite_pluginfile_urls(
    (string)$ticket->question,
    'pluginfile.php',
    $modcontext->id,
    'mod_qaa',
    'message',
    $ticket->qaapairid
);

echo html_writer::div(
    format_text(
        $questioncontent,
        FORMAT_HTML,
        ['context' => $modcontext]
    ),
    'qaa-manager-question'
);

if ($ismanager) {
    if (!function_exists('local_th_qaa_manager_get_user_from_prefix')) {
        function local_th_qaa_manager_get_user_from_prefix($record, $prefix)
        {
            $user = new \stdClass();
            $prefixlen = strlen($prefix);
            foreach ($record as $key => $value) {
                if (strpos($key, $prefix) === 0) {
                    $prop = substr($key, $prefixlen);
                    $user->$prop = $value;
                }
            }
            return $user;
        }
    }

    $userfieldsapi = fields::for_name();
    $ctvuserfields = $userfieldsapi->get_sql('u', false, 'ctv_', '', false)->selects;

    $answers = $DB->get_records_sql("
        SELECT a.*, {$ctvuserfields}
          FROM {local_th_qaa_answer} a
          JOIN {user} u ON u.id = a.userid
         WHERE a.ticketid = :ticketid
      ORDER BY a.id ASC", ['ticketid' => $ticket->id]);

    foreach ($answers as $ans) {
        $ctvuser = local_th_qaa_manager_get_user_from_prefix($ans, 'ctv_');
        $ctvname = fullname($ctvuser);

        echo $OUTPUT->heading(
            get_string('answercolumn', 'local_th_qaa_manager') . ' ' . get_string('from_ctv', 'local_th_qaa_manager', s($ctvname)),
            4
        );

        if ((int)$ans->status === answer_service::STATUS_REJECTED && !empty($ans->rejectreason)) {
            echo $OUTPUT->notification(
                get_string(
                    'answerrejectreason',
                    'local_th_qaa_manager',
                    s($ans->rejectreason)
                ),
                \core\output\notification::NOTIFY_WARNING
            );
        }

        $answercontent = file_rewrite_pluginfile_urls(
            (string)$ans->content,
            'pluginfile.php',
            $context->id,
            'local_th_qaa_manager',
            'answer',
            $ticket->id
        );

        echo html_writer::div(
            format_text(
                $answercontent,
                (int)$ans->contentformat,
                [
                    'context' => $context,
                    'overflowdiv' => true,
                ]
            ),
            'qaa-manager-answer mb-3'
        );
    }
} else {
    if (
        $latestanswer
        && (int)$latestanswer->status === answer_service::STATUS_REJECTED
        && !empty($latestanswer->rejectreason)
    ) {
        echo $OUTPUT->notification(
            get_string(
                'answerrejectreason',
                'local_th_qaa_manager',
                s($latestanswer->rejectreason)
            ),
            \core\output\notification::NOTIFY_WARNING
        );
    }

    if ($latestanswer) {
        echo $OUTPUT->heading(
            get_string('answer', 'local_th_qaa_manager'),
            4
        );

        $answercontent = file_rewrite_pluginfile_urls(
            (string)$latestanswer->content,
            'pluginfile.php',
            $context->id,
            'local_th_qaa_manager',
            'answer',
            $ticket->id
        );

        echo html_writer::div(
            format_text(
                $answercontent,
                (int)$latestanswer->contentformat,
                [
                    'context' => $context,
                    'overflowdiv' => true,
                ]
            ),
            'qaa-manager-answer'
        );
    }
}

if ($mform) {
    $formheading = get_string(
        'submitanswer',
        'local_th_qaa_manager'
    );

    echo $OUTPUT->heading($formheading, 4);
    $mform->display();
}

$backparams = [];
foreach ($_GET as $key => $value) {
    if ($key === 'id') {
        continue;
    }
    if (is_array($value)) {
        foreach ($value as $subkey => $subvalue) {
            $backparams[$key . '[' . $subkey . ']'] = $subvalue;
        }
    } else {
        $backparams[$key] = $value;
    }
}
$backparams['status'] = $returnstatus;
$backparams['page'] = $returnpage;

$backurl = $ismanager
    ? new moodle_url('/local/th_qaa_manager/manage.php', $backparams)
    : new moodle_url('/local/th_qaa_manager/question.php');

echo html_writer::div(
    html_writer::link(
        $backurl,
        get_string('back'),
        ['class' => 'btn btn-secondary']
    ),
    'mt-3'
);

echo $OUTPUT->footer();
