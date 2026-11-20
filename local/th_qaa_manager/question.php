<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/th_qaa_manager/lib.php');

require_login();

use local_th_qaa_manager\service\ticket_service;
use core_user\fields;

$context = context_system::instance();
require_capability('local/th_qaa_manager:answer', $context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);
if ($perpage <= 0) {
    $perpage = 25;
}
$baseurlparams = [
    'page' => $page,
    'perpage' => $perpage,
];
$baseurl = new moodle_url('/local/th_qaa_manager/question.php', $baseurlparams);

$title = get_string('listquestion', 'local_th_qaa_manager');
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');
// $PAGE->navbar->ignore_active(true);
$PAGE->navbar->add($title);
local_th_qaa_manager_require_datatable();
$download = optional_param('download', '', PARAM_ALPHA);
if ($download === 'excel') {
    $userfieldsapi = fields::for_name();
    $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

    $params = [
        'userid' => $USER->id,
        'useridanswer' => $USER->id,
        'status_assigned' => ticket_service::STATUS_ASSIGNED,
        'status_answered' => ticket_service::STATUS_ANSWERED,
        'status_rejected' => ticket_service::STATUS_ANSWER_REJECTED,
        'status_closed' => ticket_service::STATUS_CLOSED,
    ];

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
             WHERE (
                     t.assigneduserid = :userid
                     OR EXISTS (
                         SELECT 1
                         FROM {local_th_qaa_answer} a
                         WHERE a.ticketid = t.id
                         AND a.userid = :useridanswer
                     )
                 )
               AND t.status IN (:status_assigned, :status_answered, :status_rejected, :status_closed)
          ORDER BY t.timemodified DESC, t.id DESC";

    $export_tickets = $DB->get_records_sql($sql, $params);

    if ($export_tickets) {
        require_once($CFG->libdir . '/excellib.class.php');

        $filename = 'danh_sach_cau_hoi_ctv_' . date('Ymd_His') . '.xls';
        $workbook = new MoodleExcelWorkbook(clean_filename($filename));
        $worksheet = $workbook->add_worksheet('List Question');

        $format_header = $workbook->add_format([
            'bold' => 1,
            'size' => 11,
            'align' => 'center',
            'valign' => 'vcenter',
            'bg_color' => 'silver',
        ]);

        $headers = [
            get_string('col_no', 'local_th_qaa_manager'),
            get_string('course', 'local_th_qaa_manager'),
            get_string('student', 'local_th_qaa_manager'),
            get_string('question_content', 'local_th_qaa_manager'),
            get_string('col_answer_status', 'local_th_qaa_manager'),
            get_string('col_approval_status', 'local_th_qaa_manager'),
        ];

        $col = 0;
        foreach ($headers as $header) {
            $worksheet->write_string(0, $col++, $header, $format_header);
        }

        $rowidx = 1;
        $stt_num = 1;
        foreach ($export_tickets as $ticket) {
            $studentname = fullname($ticket);
            $questiontext = trim(html_to_text((string)$ticket->question));

            $ticketstatus = (int)$ticket->status;
            $answerstatus = '';
            $approvalstatus = '';

            if ($ticketstatus === ticket_service::STATUS_ASSIGNED) {
                $answerstatus = get_string('answer_status_unanswered', 'local_th_qaa_manager');
                $approvalstatus = '-';
            } else if ($ticketstatus === ticket_service::STATUS_ANSWER_REJECTED) {
                $answerstatus = get_string('answer_status_answered', 'local_th_qaa_manager');
                $approvalstatus = get_string('approval_status_rejected', 'local_th_qaa_manager');
            } else if ($ticketstatus === ticket_service::STATUS_ANSWERED) {
                $answerstatus = get_string('answer_status_answered', 'local_th_qaa_manager');
                $approvalstatus = get_string('approval_status_pending', 'local_th_qaa_manager');
            } else if ($ticketstatus === ticket_service::STATUS_CLOSED) {
                $answerstatus = get_string('answer_status_answered', 'local_th_qaa_manager');
                $approvalstatus = get_string('approval_status_approved', 'local_th_qaa_manager');
            } else if ($ticketstatus === ticket_service::STATUS_RECALLED) {
                $answerstatus = get_string('answer_status_unanswered', 'local_th_qaa_manager');
                $approvalstatus = get_string('status_recalled', 'local_th_qaa_manager');
            }

            $worksheet->write_number($rowidx, 0, $stt_num++);
            $worksheet->write_string($rowidx, 1, format_string($ticket->coursefullname));
            $worksheet->write_string($rowidx, 2, $studentname);
            $worksheet->write_string($rowidx, 3, $questiontext);
            $worksheet->write_string($rowidx, 4, $answerstatus);
            $worksheet->write_string($rowidx, 5, $approvalstatus);

            $rowidx++;
        }

        $workbook->close();
        exit;
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('listquestion', 'local_th_qaa_manager'), 3);

$userfieldsapi = fields::for_name();
$userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

$params = [
    'userid' => $USER->id,
    'useridanswer' => $USER->id,
    'status_assigned' => ticket_service::STATUS_ASSIGNED,
    'status_answered' => ticket_service::STATUS_ANSWERED,
    'status_rejected' => ticket_service::STATUS_ANSWER_REJECTED,
    'status_closed' => ticket_service::STATUS_CLOSED,
];


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
         WHERE (
                 t.assigneduserid = :userid
                 OR EXISTS (
                     SELECT 1
                     FROM {local_th_qaa_answer} a
                     WHERE a.ticketid = t.id
                     AND a.userid = :useridanswer
                 )
             )
           AND t.status IN (:status_assigned, :status_answered, :status_rejected, :status_closed)
      ORDER BY t.timemodified DESC, t.id DESC";

$tickets = $DB->get_records_sql($sql, $params);

// Old export handler moved before header.

if (!$tickets) {
    echo $OUTPUT->notification(get_string('nothingtodisplay', 'local_th_qaa_manager'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

$totaltickets = count($tickets);
$tickets_page = array_slice($tickets, $page * $perpage, $perpage);

$table = new html_table();
$table->attributes = [
    'class' => 'th_qaa_manager display generaltable text-left w-100',
    'border' => '1',
];
$qheadcell = new html_table_cell(get_string('question', 'local_th_qaa_manager'));
$qheadcell->attributes['class'] = 'col-question-link';

$table->head = [
    get_string('col_no', 'local_th_qaa_manager'),
    get_string('course', 'local_th_qaa_manager'),
    get_string('student', 'local_th_qaa_manager'),
    $qheadcell,
    get_string('col_answer_status', 'local_th_qaa_manager'),
    get_string('col_approval_status', 'local_th_qaa_manager'),
    get_string('actions', 'local_th_qaa_manager'),
];

$stt = $page * $perpage + 1;
foreach ($tickets_page as $ticket) {
    $studentname = fullname($ticket);

    $detailurl = new moodle_url(
        '/local/th_qaa_manager/view.php',
        [
            'id' => $ticket->id,
        ]
    );

    // Chuyển HTML và các entity như &nbsp; thành văn bản bình thường.
    $questiontext = shorten_text(
        trim(html_to_text((string)$ticket->question)),
        120
    );

    $ticketstatus = (int)$ticket->status;
    $answerstatushtml = '';
    $approvalstatushtml = '';

    if ($ticketstatus === ticket_service::STATUS_ASSIGNED) {
        $answerstatushtml = html_writer::span(get_string('answer_status_unanswered', 'local_th_qaa_manager'), 'badge badge-warning text-dark');
        $approvalstatushtml = '';
    } else if ($ticketstatus === ticket_service::STATUS_ANSWER_REJECTED) {
        $answerstatushtml = html_writer::span(get_string('answer_status_answered', 'local_th_qaa_manager'), 'badge badge-success');
        $approvalstatushtml = html_writer::span(get_string('approval_status_rejected', 'local_th_qaa_manager'), 'badge badge-danger');
    } else if ($ticketstatus === ticket_service::STATUS_ANSWERED) {
        $answerstatushtml = html_writer::span(get_string('answer_status_answered', 'local_th_qaa_manager'), 'badge badge-success');
        $approvalstatushtml = html_writer::span(get_string('approval_status_pending', 'local_th_qaa_manager'), 'badge badge-warning text-dark');
    } else if ($ticketstatus === ticket_service::STATUS_CLOSED) {
        $answerstatushtml = html_writer::span(get_string('answer_status_answered', 'local_th_qaa_manager'), 'badge badge-success');
        $approvalstatushtml = html_writer::span(get_string('approval_status_approved', 'local_th_qaa_manager'), 'badge badge-success');
    } else if ($ticketstatus === ticket_service::STATUS_RECALLED) {
        $answerstatushtml = html_writer::span(get_string('answer_status_unanswered', 'local_th_qaa_manager'), 'badge badge-warning text-dark');
        $approvalstatushtml = html_writer::span(get_string('status_recalled', 'local_th_qaa_manager'), 'badge badge-secondary');
    }

    $qlinkcell = new html_table_cell(html_writer::link($detailurl, s($questiontext)));
    $qlinkcell->attributes['class'] = 'col-question-link';

    $table->data[] = [
        $stt++,
        format_string($ticket->coursefullname),

        s($studentname),

        $qlinkcell,

        $answerstatushtml,
        $approvalstatushtml,

        html_writer::link(
            $detailurl,
            get_string(
                'questiondetail',
                'local_th_qaa_manager'
            )
        ),
    ];
}

echo html_writer::table($table);

echo html_writer::start_div('d-flex flex-wrap align-items-center justify-content-between my-3');

echo html_writer::div(
    $OUTPUT->paging_bar(
        $totaltickets,
        $page,
        $perpage,
        $baseurl
    ),
    'mb-2 mb-md-0'
);

$perpageoptions = [10, 25, 50, 100, 200, 500];
$optionshtml = '';
foreach ($perpageoptions as $option) {
    $url = new moodle_url('/local/th_qaa_manager/question.php', array_merge($baseurlparams, ['perpage' => $option, 'page' => 0]));
    $selected = ((int)$perpage === (int)$option) ? 'selected="selected"' : '';
    $optionshtml .= '<option value="' . s($url->out(false)) . '" ' . $selected . '>' . $option . '</option>';
}

$exporturl = new moodle_url('/local/th_qaa_manager/question.php', array_merge($baseurlparams, ['download' => 'excel']));
$exportbtn = html_writer::link(
    $exporturl,
    '<i class="fa fa-file-excel-o"></i> ' . get_string('exportexcel', 'local_th_qaa_manager'),
    ['class' => 'btn btn-success btn-sm font-weight-bold ml-3']
);

$lengthhtml = '<label class="mb-0 font-weight-normal text-muted">' . get_string('show', 'local_th_qaa_manager') . ' ' .
    '<select class="custom-select custom-select-sm form-control form-control-sm d-inline-block mx-1" style="width: auto; min-width: 70px;" onchange="window.location.href=this.value;">' .
    $optionshtml .
    '</select> ' . get_string('items', 'local_th_qaa_manager') . '</label>' . $exportbtn;

echo html_writer::div($lengthhtml, 'qaa_perpage_selector d-flex align-items-center mb-2 mb-md-0');

echo html_writer::end_div();

echo $OUTPUT->footer();
