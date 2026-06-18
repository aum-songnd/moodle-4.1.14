<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_bulk_send_message/lib.php';
require_once $CFG->dirroot . '/local/th_bulk_send_message/classes/form/th_log_send_message_form.php';

use local_th_bulk_send_message\form\th_log_send_message_form;

global $DB, $CFG, $COURSE, $USER, $OUTPUT, $PAGE;

if (!$course = $DB->get_record('course', ['id' => $COURSE->id])) {
    print_error('invalidcourse', 'th_bulk_send_message', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_bulk_send_message:view', context_course::instance($COURSE->id));

$page = optional_param('page', 0, PARAM_INT);

$perpage = (int)get_config('local_th_bulk_send_message', 'logperpage');
if (empty($perpage)) {
    $perpage = 50;
}

$offset = $page * $perpage;
$offset = $page * $perpage;

$PAGE->set_url(new moodle_url('/local/th_bulk_send_message/th_log_send_message.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('bulk_message_list', 'local_th_bulk_send_message'));
$PAGE->set_title(get_string('bulk_message_list', 'local_th_bulk_send_message'));
$editurl = new moodle_url('/local/th_bulk_send_message/th_log_send_message.php');
$PAGE->settingsnav->add(get_string('bulk_message_list', 'local_th_bulk_send_message'), $editurl);

$mform = new th_log_send_message_form();

$filters = [
    'sender' => optional_param('filtersender', '', PARAM_RAW_TRIMMED),
    'receiver' => optional_param('filterreceiver', '', PARAM_RAW_TRIMMED),
    'subject' => optional_param('filtersubject', '', PARAM_RAW_TRIMMED),
    'datefrom' => optional_param('filterdatefrom', '', PARAM_RAW_TRIMMED),
    'dateto' => optional_param('filterdateto', '', PARAM_RAW_TRIMMED),
    'status' => optional_param('filterstatus', '', PARAM_RAW_TRIMMED),
];

$showtable = false;

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_bulk_send_message/th_log_send_message.php'));
} else if ($data = $mform->get_data()) {
    // Khi submit form thì luôn quay về page đầu.
    $page = 0;
    $offset = 0;

    $filters['sender'] = '';
    $filters['receiver'] = '';

    if (!empty($data->senderid)) {
        $senderuser = $DB->get_record('user', ['id' => (int)$data->senderid], 'id,email', IGNORE_MISSING);
        if (!empty($senderuser->email)) {
            $filters['sender'] = $senderuser->email;
        }
    }

    if (!empty($data->receiverid)) {
        $receiveruser = $DB->get_record('user', ['id' => (int)$data->receiverid], 'id,email', IGNORE_MISSING);
        if (!empty($receiveruser->email)) {
            $filters['receiver'] = $receiveruser->email;
        }
    }

    $filters['subject'] = trim($data->subject ?? '');
    $filters['datefrom'] = !empty($data->datefrom) ? date('Y-m-d', $data->datefrom) : '';
    $filters['dateto'] = !empty($data->dateto) ? date('Y-m-d', $data->dateto) : '';
    $filters['status'] = (string)($data->status ?? '');
}

if (
    $filters['sender'] !== '' ||
    $filters['receiver'] !== '' ||
    $filters['subject'] !== '' ||
    $filters['datefrom'] !== '' ||
    $filters['dateto'] !== '' ||
    $filters['status'] !== ''
) {
    $showtable = true;
}

$where = [];
$params = [];

if (!empty($filters['sender'])) {
    $where[] = $DB->sql_like('sender', ':sender', false);
    $params['sender'] = '%' . $filters['sender'] . '%';
}

if (!empty($filters['receiver'])) {
    $where[] = $DB->sql_like('receiver', ':receiver', false);
    $params['receiver'] = '%' . $filters['receiver'] . '%';
}

if (!empty($filters['subject'])) {
    $where[] = $DB->sql_like('subject', ':subject', false);
    $params['subject'] = '%' . $filters['subject'] . '%';
}

if ($filters['status'] !== '' && ($filters['status'] === '0' || $filters['status'] === '1')) {
    $where[] = 'status = :status';
    $params['status'] = (int)$filters['status'];
}

if (!empty($filters['datefrom'])) {
    $where[] = 'date >= :datefrom';
    $params['datefrom'] = strtotime($filters['datefrom'] . ' 00:00:00');
}

if (!empty($filters['dateto'])) {
    $where[] = 'date <= :dateto';
    $params['dateto'] = strtotime($filters['dateto'] . ' 23:59:59');
}

$wheresql = '';
if (!empty($where)) {
    $wheresql = ' WHERE ' . implode(' AND ', $where);
}

$table = new html_table();
$tablehead = [
    get_string('stt', 'local_th_bulk_send_message'),
    get_string('sender', 'local_th_bulk_send_message'),
    get_string('receiver', 'local_th_bulk_send_message'),
    get_string('receiverusername', 'local_th_bulk_send_message'),
    get_string('receiverstudentcode', 'local_th_bulk_send_message'),
    get_string('subject', 'local_th_bulk_send_message'),
];

if (!empty($showcontentcolumn)) {
    $tablehead[] = get_string('content', 'local_th_bulk_send_message');
}

$tablehead[] = get_string('send_date', 'local_th_bulk_send_message');
$tablehead[] = get_string('status', 'local_th_bulk_send_message');

$table->head = $tablehead;
$table->attributes = array(
    'class' => 'th_log_send_message_table',
    'border' => '1',
    'id' => 'th_log_send_message_table'
);
$table->attributes['style'] = "width: 100%; text-align:center;";

$total = 0;
if ($showtable) {
    $countsql = "SELECT COUNT(id)
                   FROM {th_bulk_send_message}
                 $wheresql";
    $total = $DB->count_records_sql($countsql, $params);

    $sql = "SELECT id, sender, receiver, receiverusername, receiverstudentcode, subject, body, date, status
              FROM {th_bulk_send_message}
            $wheresql
          ORDER BY date DESC";

    $records = $DB->get_records_sql($sql, $params, $offset, $perpage);

    $stt = $offset + 1;
    foreach ($records as $record) {
        $row = [];
        $row[] = $stt++;
        $row[] = s($record->sender);
        $row[] = s($record->receiver);
        $row[] = s($record->receiverusername ?? '');
        $row[] = s($record->receiverstudentcode ?? '');
        $row[] = s($record->subject);

        if (!empty($showcontentcolumn)) {
            $row[] = format_text($record->body, FORMAT_HTML);
        }

        $row[] = userdate($record->date, '%d/%m/%Y %H:%M');
        $row[] = ((int)$record->status === 1)
            ? html_writer::tag('span', get_string('send_success', 'local_th_bulk_send_message'), ['class' => 'badge badge-success'])
            : html_writer::tag('span', get_string('send_fail', 'local_th_bulk_send_message'), ['class' => 'badge badge-warning']);

        $table->data[] = $row;
    }
}

echo $OUTPUT->header();

$baseurl = new moodle_url('/local/th_bulk_send_message/th_log_send_message.php');
if ($editcontrols = local_th_send_message_controls($context, $baseurl)) {
    echo $OUTPUT->render($editcontrols);
}

echo $OUTPUT->heading("<center>" . get_string('bulk_message_list', 'local_th_bulk_send_message') . "</center>");

$mform->display();

if (!$showtable) {
    echo $OUTPUT->notification(
        get_string('pleasechoosefiltercondition', 'local_th_bulk_send_message'),
        \core\output\notification::NOTIFY_INFO
    );
}

if ($showtable) {
    if (!empty($total)) {
        echo html_writer::table($table);

        $baseurlparams = [];
        if ($filters['sender'] !== '') {
            $baseurlparams['filtersender'] = $filters['sender'];
        }
        if ($filters['receiver'] !== '') {
            $baseurlparams['filterreceiver'] = $filters['receiver'];
        }
        if ($filters['subject'] !== '') {
            $baseurlparams['filtersubject'] = $filters['subject'];
        }
        if ($filters['datefrom'] !== '') {
            $baseurlparams['filterdatefrom'] = $filters['datefrom'];
        }
        if ($filters['dateto'] !== '') {
            $baseurlparams['filterdateto'] = $filters['dateto'];
        }
        if ($filters['status'] !== '') {
            $baseurlparams['filterstatus'] = $filters['status'];
        }

        $pagingurl = new moodle_url('/local/th_bulk_send_message/th_log_send_message.php', $baseurlparams);
        echo $OUTPUT->paging_bar($total, $page, $perpage, $pagingurl);
    } else {
        echo $OUTPUT->notification(
            get_string('nodata', 'local_th_bulk_send_message'),
            \core\output\notification::NOTIFY_INFO
        );
    }
}

$sl_mail_done = $DB->get_field_sql("
    SELECT COUNT(id)
      FROM {th_bulk_send_message}
     WHERE DATE(FROM_UNIXTIME(date)) = CURDATE() AND status = 1
");

$sl_mail_error = $DB->get_field_sql("
    SELECT COUNT(id)
      FROM {th_bulk_send_message}
     WHERE DATE(FROM_UNIXTIME(date)) = CURDATE() AND status = 0
");

$stats = html_writer::div(
    html_writer::tag('h3', get_string('statistics', 'local_th_bulk_send_message')) .
    html_writer::tag('p', get_string('emails_sent_success', 'local_th_bulk_send_message', $sl_mail_done)) .
    html_writer::tag('p', get_string('emails_sent_fail', 'local_th_bulk_send_message', $sl_mail_error))
);

echo $stats;
$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_log_send_message_table', get_string('bulk_message_list', 'local_th_bulk_send_message'), $lang));
echo $OUTPUT->footer();