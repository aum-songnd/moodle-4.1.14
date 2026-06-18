<?php

require_once('../../config.php');

global $DB, $PAGE, $SITE, $USER;

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_capability('local/th_feedback_report:view', $context);
require_sesskey();

$id = required_param('id', PARAM_INT);
$response = required_param('response', PARAM_TEXT);
$response = trim($response);

if ($response === '') {
    redirect(
        new moodle_url('/local/th_feedback_report/index.php'),
        get_string('response_required', 'local_th_feedback_report'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

$report = $DB->get_record('local_th_feedback_report', ['id' => $id], '*', MUST_EXIST);
$student = $DB->get_record('user', ['id' => $report->userid, 'deleted' => 0], '*', MUST_EXIST);

$oldresponse = trim((string)($report->response ?? ''));
$newresponse = $response;

if ($oldresponse === $newresponse) {
    redirect(
        new moodle_url('/local/th_feedback_report/index.php'),
        get_string('response_not_changed', 'local_th_feedback_report'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

$transaction = $DB->start_delegated_transaction();

// Lưu lịch sử phản hồi.
$history = new stdClass();
$history->reportid = $report->id;
$history->userid = $USER->id;
$history->status = $report->status;
$history->response = $newresponse;
$history->timecreated = time();

$DB->insert_record('th_feedback_responses', $history);

// Cập nhật phản hồi mới nhất vào bảng chính.
$data = new stdClass();
$data->id = $report->id;
$data->response = $newresponse;
$data->timemodified = time();

$DB->update_record('local_th_feedback_report', $data);

$transaction->allow_commit();

$systemname = format_string($SITE->shortname);

$statusoptions = [
    0 => get_string('not_processed', 'local_th_feedback_report'),
    1 => get_string('processing', 'local_th_feedback_report'),
    2 => get_string('processed', 'local_th_feedback_report'),
];

$statusname = $statusoptions[(int)$report->status] ?? get_string('unknown', 'local_th_feedback_report');

$subject = get_string('feedback_response_email_subject', 'local_th_feedback_report', $systemname);
$viewurl = new moodle_url('/local/th_feedback_report/view.php');

$a = new stdClass();
$a->fullname = fullname($student);
$a->description = $report->description;
$a->url = $viewurl->out(false);
$a->status = $statusname;
$a->response = $newresponse;
$a->timecreated = userdate($report->timecreated);
$a->timemodified = userdate(time());

$htmlmessage = html_writer::tag('p', get_string('feedback_response_email_greeting', 'local_th_feedback_report', $a));
$htmlmessage .= html_writer::tag('p', get_string('feedback_response_email_intro', 'local_th_feedback_report'));

$htmlmessage .= html_writer::tag(
    'p',
    html_writer::tag('strong', get_string('description', 'local_th_feedback_report') . ': ') . s($a->description)
);

$htmlmessage .= html_writer::tag(
    'p',
    html_writer::tag('strong', get_string('status', 'local_th_feedback_report') . ': ') . s($a->status)
);

$htmlmessage .= html_writer::tag(
    'p',
    html_writer::tag('strong', get_string('response_content', 'local_th_feedback_report') . ': ') . s($a->response)
);

$htmlmessage .= html_writer::tag(
    'p',
    html_writer::tag('strong', get_string('view_my_feedback_reports', 'local_th_feedback_report') . ': ')
    . html_writer::link($viewurl, get_string('click_to_view', 'local_th_feedback_report'))
);

$plainmessage = html_to_text($htmlmessage);

email_to_user(
    $student,
    \core_user::get_support_user(),
    $subject,
    $plainmessage,
    $htmlmessage
);

redirect(
    new moodle_url('/local/th_feedback_report/index.php'),
    get_string('update_response_success', 'local_th_feedback_report'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);