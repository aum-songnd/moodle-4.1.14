<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/th_qaa_manager/lib.php');
require_once($CFG->dirroot . '/local/th_qaa_manager/classes/form/qaa_filter_form.php');
require_once($CFG->dirroot . '/local/th_qaa_manager/classes/form/qaa_stats_form.php');

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

require_login();

use local_th_qaa_manager\service\ticket_service;
use local_th_qaa_manager\service\answer_service;
use core_user\fields;

$context = context_system::instance();
require_capability('local/th_qaa_manager:manage', $context);

$action = optional_param('action', '', PARAM_ALPHA);
$ticketid = optional_param('ticketid', 0, PARAM_INT);
$qaapairid = optional_param('qaapairid', 0, PARAM_INT);
$assignuserid = optional_param('assignuserid', 0, PARAM_INT);

$bulkaction = optional_param('bulkaction', '', PARAM_ALPHA);
$bulkticketids = optional_param_array('bulkticketids', [], PARAM_INT);
$bulkqaapairids = optional_param_array('bulkqaapairids', [], PARAM_INT);
$bulkassignuserid = optional_param('bulkassignuserid', 0, PARAM_INT);

const STATUS_QAA_MANAGE = 10;
const STATUS_QAA_STATS = 20;

$status = optional_param('status', ticket_service::STATUS_APPROVED, PARAM_INT);

if ((int)$status === ticket_service::STATUS_ASSIGNED) {
    redirect(new moodle_url('/local/th_qaa_manager/manage.php', [
        'status' => STATUS_QAA_MANAGE,
        'filter_status' => 'assigned',
        'submitbutton' => '1',
    ]));
}

if ((int)$status === ticket_service::STATUS_ANSWERED) {
    redirect(new moodle_url('/local/th_qaa_manager/manage.php', [
        'status' => STATUS_QAA_MANAGE,
        'filter_status' => 'answered',
        'submitbutton' => '1',
    ]));
}

if ((int)$status === ticket_service::STATUS_CLOSED) {
    redirect(new moodle_url('/local/th_qaa_manager/manage.php', [
        'status' => STATUS_QAA_MANAGE,
        'filter_status' => 'completed',
        'submitbutton' => '1',
    ]));
}
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);
if ($perpage <= 0) {
    $perpage = 25;
}

$filter_ctv_is_array = is_array($_GET['filter_ctv'] ?? null) || is_array($_POST['filter_ctv'] ?? null);
if ($filter_ctv_is_array) {
    $filter_ctv_raw = optional_param_array('filter_ctv', null, PARAM_INT);
    $filter_ctv = $filter_ctv_raw ? array_filter($filter_ctv_raw) : [];
} else {
    $filter_ctv_scalar = optional_param('filter_ctv', 0, PARAM_INT);
    $filter_ctv = $filter_ctv_scalar ? [$filter_ctv_scalar] : [];
}

$filter_course_is_array = is_array($_GET['filter_course'] ?? null) || is_array($_POST['filter_course'] ?? null);
if ($filter_course_is_array) {
    $filter_course_raw = optional_param_array('filter_course', null, PARAM_INT);
    $filter_course = $filter_course_raw ? array_filter($filter_course_raw) : [];
} else {
    $filter_course_scalar = optional_param('filter_course', 0, PARAM_INT);
    $filter_course = $filter_course_scalar ? [$filter_course_scalar] : [];
}

$filter_status = optional_param('filter_status', 'all', PARAM_ALPHA);

$filter_startdate_array = optional_param_array('filter_startdate', null, PARAM_INT);
$filter_enddate_array = optional_param_array('filter_enddate', null, PARAM_INT);

$stats_startdate_array = optional_param_array('stats_startdate', null, PARAM_INT);
$stats_enddate_array = optional_param_array('stats_enddate', null, PARAM_INT);

// Tự động xác định Đơn vị (Company) theo IOMAD / thlib (nếu có)
if (file_exists($CFG->dirroot . '/local/thlib/lib.php')) {
    require_once($CFG->dirroot . '/local/thlib/lib.php');
}

$is_iomad = class_exists('iomad');
$companyid = 0;
if ($is_iomad) {
    $companyid = (int)iomad::get_my_companyid(context_system::instance(), false);
    if ($companyid <= 0) {
        $companyid = (int)$DB->get_field('company_users', 'companyid', ['userid' => $USER->id], IGNORE_MISSING);
    }
}

$company_name_select = $is_iomad
    ? "(SELECT comp.name FROM {company_users} cu JOIN {company} comp ON comp.id = cu.companyid WHERE cu.userid = qp.studentid LIMIT 1) AS student_company_name,"
    : "'' AS student_company_name,";

$baseurlparams = [
    'status' => $status,
    'page' => $page,
    'perpage' => $perpage,
];
if ($status === STATUS_QAA_MANAGE) {
    if ($filter_ctv) {
        foreach ($filter_ctv as $k => $v) {
            $baseurlparams['filter_ctv[' . $k . ']'] = $v;
        }
    }
    if ($filter_course) {
        foreach ($filter_course as $k => $v) {
            $baseurlparams['filter_course[' . $k . ']'] = $v;
        }
    }
    $baseurlparams['filter_status'] = $filter_status;
    if ($filter_startdate_array) {
        foreach ($filter_startdate_array as $k => $v) {
            $baseurlparams['filter_startdate[' . $k . ']'] = $v;
        }
    }
    if ($filter_enddate_array) {
        foreach ($filter_enddate_array as $k => $v) {
            $baseurlparams['filter_enddate[' . $k . ']'] = $v;
        }
    }
    if (optional_param('submitbutton', '', PARAM_TEXT) !== '') {
        $baseurlparams['submitbutton'] = '1';
    }
} else if ($status === STATUS_QAA_STATS) {
    if ($stats_startdate_array) {
        foreach ($stats_startdate_array as $k => $v) {
            $baseurlparams['stats_startdate[' . $k . ']'] = $v;
        }
    }
    if ($stats_enddate_array) {
        foreach ($stats_enddate_array as $k => $v) {
            $baseurlparams['stats_enddate[' . $k . ']'] = $v;
        }
    }
}
$baseurl = new moodle_url('/local/th_qaa_manager/manage.php', $baseurlparams);

$tab_titles = [
    ticket_service::STATUS_APPROVED => get_string('status_approved', 'local_th_qaa_manager'),
    STATUS_QAA_MANAGE => get_string('tab_qaa_manage', 'local_th_qaa_manager'),
    STATUS_QAA_STATS => get_string('tab_qaa_stats', 'local_th_qaa_manager'),
];
$active_tab_title = $tab_titles[(int)$status] ?? get_string('status_approved', 'local_th_qaa_manager');

$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title($active_tab_title);
$PAGE->set_heading($active_tab_title);
$PAGE->set_pagelayout('standard');
// $PAGE->navbar->ignore_active(true);
$PAGE->navbar->add($active_tab_title);
local_th_qaa_manager_require_datatable();

// Build CTV options.
$ctvfieldsapi = fields::for_name();
$ctvfieldssql = $ctvfieldsapi->get_sql('u', false, '', '', false)->selects;

$ctvusers = ticket_service::get_assignee_users();

$ctvoptions = [];

foreach ($ctvusers as $ctvuser) {

    if (empty($ctvuser->email)) {
        continue;
    }

    $label = fullname($ctvuser);

    $label .= ' - ' . $ctvuser->email;

    $ctvoptions[$ctvuser->id] = $label;
}

if (empty($ctvoptions)) {
    \core\notification::warning(get_string('noassignees', 'local_th_qaa_manager'));
}

if (optional_param('cancelbutton', '', PARAM_TEXT)) {
    redirect(new moodle_url('/local/th_qaa_manager/manage.php', ['status' => STATUS_QAA_MANAGE]));
}

$mform = null;
$is_filtered = false;
if ((int)$status === STATUS_QAA_MANAGE) {
    $mform = new \local_th_qaa_manager\form\qaa_filter_form(null, [
        'ctvoptions' => ['0' => get_string('filter_status_all', 'local_th_qaa_manager')] + $ctvoptions,
        'statusoptions' => [
            'all' => get_string('filter_status_all', 'local_th_qaa_manager'),
            'completed' => get_string('filter_status_completed', 'local_th_qaa_manager'),
            'incomplete' => get_string('filter_status_incomplete', 'local_th_qaa_manager'),
            'answered' => get_string('status_unapproved_short', 'local_th_qaa_manager'),
            'assigned' => get_string('status_assigned', 'local_th_qaa_manager'),
            'recalled' => get_string('status_recalled', 'local_th_qaa_manager'),
        ],
    ]);

    $mform->set_data([
        'filter_ctv' => $filter_ctv,
        'filter_course' => $filter_course,
        'filter_startdate' => $filter_startdate_array,
        'filter_enddate' => $filter_enddate_array,
        'filter_status' => $filter_status,
    ]);

    $filter_startdate = 0;
    $filter_enddate = 0;

    if ($data = $mform->get_data()) {
        $filter_ctv = !empty($data->filter_ctv) ? (array)$data->filter_ctv : [];
        $filter_course = !empty($data->filter_course) ? (array)$data->filter_course : [];
        $filter_startdate = (int)($data->filter_startdate ?? 0);
        $filter_enddate = (int)($data->filter_enddate ?? 0);
        $filter_status = $data->filter_status ?? 'all';
    } else {
        $filter_ctv_is_array = is_array($_GET['filter_ctv'] ?? null) || is_array($_POST['filter_ctv'] ?? null);
        if ($filter_ctv_is_array) {
            $filter_ctv_raw = optional_param_array('filter_ctv', null, PARAM_INT);
            $filter_ctv = $filter_ctv_raw ? array_filter($filter_ctv_raw) : [];
        } else {
            $filter_ctv_scalar = optional_param('filter_ctv', 0, PARAM_INT);
            $filter_ctv = $filter_ctv_scalar ? [$filter_ctv_scalar] : [];
        }

        $filter_course_is_array = is_array($_GET['filter_course'] ?? null) || is_array($_POST['filter_course'] ?? null);
        if ($filter_course_is_array) {
            $filter_course_raw = optional_param_array('filter_course', null, PARAM_INT);
            $filter_course = $filter_course_raw ? array_filter($filter_course_raw) : [];
        } else {
            $filter_course_scalar = optional_param('filter_course', 0, PARAM_INT);
            $filter_course = $filter_course_scalar ? [$filter_course_scalar] : [];
        }

        $filter_status = optional_param('filter_status', 'all', PARAM_ALPHA);

        if ($filter_startdate_array && !empty($filter_startdate_array['enabled'])) {
            $filter_startdate = make_timestamp($filter_startdate_array['year'], $filter_startdate_array['month'], $filter_startdate_array['day']);
        }
        if ($filter_enddate_array && !empty($filter_enddate_array['enabled'])) {
            $filter_enddate = make_timestamp($filter_enddate_array['year'], $filter_enddate_array['month'], $filter_enddate_array['day']);
        }
    }

    if (
        optional_param('submitbutton', '', PARAM_TEXT) !== ''
        || !empty($filter_ctv)
        || !empty($filter_course)
        || $filter_startdate > 0
        || $filter_enddate > 0
        || ($filter_status !== 'all' && $filter_status !== '')
    ) {
        $is_filtered = true;
    }
}

$stats_form = null;
$startdate_ts = time() - 30 * 24 * 3600;
$enddate_ts = time();

if ((int)$status === STATUS_QAA_STATS) {
    $stats_form = new \local_th_qaa_manager\form\qaa_stats_form();

    if ($stats_startdate_array) {
        $startdate_ts = make_timestamp($stats_startdate_array['year'], $stats_startdate_array['month'], $stats_startdate_array['day']);
    }
    if ($stats_enddate_array) {
        $enddate_ts = make_timestamp($stats_enddate_array['year'], $stats_enddate_array['month'], $stats_enddate_array['day']) + 86399;
    }

    if ($startdate_ts > $enddate_ts) {
        \core\notification::error(get_string('error_startdate_after_enddate', 'local_th_qaa_manager'));
        $startdate_ts = time() - 30 * 24 * 3600;
        $enddate_ts = time();
    }

    $stats_form->set_data([
        'stats_startdate' => $startdate_ts,
        'stats_enddate' => $enddate_ts,
    ]);
}

// Bulk actions.
if ($bulkaction) {
    require_sesskey();

    if ($bulkaction === 'bulkapproveq') {
        require_capability('local/th_qaa_manager:approvequestion', $context);

        if (empty($bulkqaapairids)) {
            redirect(
                $baseurl,
                get_string('noselectedtickets', 'local_th_qaa_manager'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }

        $count = ticket_service::approve_questions($bulkqaapairids, $USER->id);

        redirect(
            new moodle_url('/local/th_qaa_manager/manage.php', [
                'status' => ticket_service::STATUS_APPROVED,
            ]),
            get_string('bulkapprovedone', 'local_th_qaa_manager', $count),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    if ($bulkaction === 'bulkassign') {
        require_capability('local/th_qaa_manager:assign', $context);

        if (!empty($bulkqaapairids)) {
            foreach ($bulkqaapairids as $qaapairid) {
                $tid = ticket_service::approve_question((int)$qaapairid, $USER->id);
                if ($tid > 0) {
                    $bulkticketids[] = $tid;
                }
            }
            $bulkticketids = array_values(array_unique(array_filter(array_map('intval', $bulkticketids))));
        }

        if (empty($bulkticketids)) {
            redirect(
                $baseurl,
                get_string('noselectedtickets', 'local_th_qaa_manager'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }

        if ($bulkassignuserid <= 0 || !array_key_exists((int)$bulkassignuserid, $ctvoptions)) {
            redirect(
                $baseurl,
                get_string('invalidassignee', 'local_th_qaa_manager'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }

        $count = ticket_service::assign_questions($bulkticketids, $bulkassignuserid, $USER->id);

        redirect(
            $baseurl,
            get_string('bulkassigndone', 'local_th_qaa_manager', $count),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    if ($bulkaction === 'bulkdeleteassign') {
        require_capability('local/th_qaa_manager:assign', $context);

        if (empty($bulkticketids)) {
            redirect(
                $baseurl,
                get_string('noselectedtickets', 'local_th_qaa_manager'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }

        $count = 0;
        foreach ($bulkticketids as $ticketid) {
            $ticket = $DB->get_record('local_th_qaa_ticket', ['id' => $ticketid]);
            if ($ticket && (int)$ticket->status !== ticket_service::STATUS_CLOSED && !empty($ticket->assigneduserid)) {
                ticket_service::delete_assignment($ticketid);
                $count++;
            }
        }

        redirect(
            $baseurl,
            get_string('bulk_delete_assignment_success', 'local_th_qaa_manager', $count),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    if ($bulkaction === 'bulkapprovea') {
        require_capability('local/th_qaa_manager:manage', $context);

        if (empty($bulkticketids)) {
            redirect(
                $baseurl,
                get_string('noselectedtickets', 'local_th_qaa_manager'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }

        $count = 0;
        foreach ($bulkticketids as $ticketid) {
            $ticket = $DB->get_record('local_th_qaa_ticket', ['id' => $ticketid]);
            if ($ticket && (int)$ticket->status === ticket_service::STATUS_ANSWERED) {
                answer_service::approve_answer($ticketid, $USER->id);
                $count++;
            }
        }

        redirect(
            $baseurl,
            get_string('bulk_approve_answer_success', 'local_th_qaa_manager', $count),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

// Single actions.
if ($action) {
    require_sesskey();

    if ($action === 'approveq') {
        require_capability('local/th_qaa_manager:approvequestion', $context);

        if (empty($qaapairid)) {
            throw new moodle_exception('invalidticket', 'local_th_qaa_manager');
        }

        ticket_service::approve_question($qaapairid, $USER->id);

        redirect(new moodle_url('/local/th_qaa_manager/manage.php', [
            'status' => ticket_service::STATUS_APPROVED,
        ]));
    }

    if ($action === 'assign') {
        require_capability('local/th_qaa_manager:assign', $context);

        $qaapairid = optional_param('qaapairid', 0, PARAM_INT);
        if (empty($ticketid) && !empty($qaapairid)) {
            require_capability('local/th_qaa_manager:approvequestion', $context);
            $ticketid = ticket_service::approve_question($qaapairid, $USER->id);
        }

        if (empty($ticketid)) {
            throw new moodle_exception('invalidticket', 'local_th_qaa_manager');
        }

        if (!array_key_exists((int)$assignuserid, $ctvoptions)) {
            redirect(
                $baseurl,
                get_string('invalidassignee', 'local_th_qaa_manager'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }

        ticket_service::assign($ticketid, $assignuserid, $USER->id);
        redirect(
            $baseurl,
            get_string('assign_success', 'local_th_qaa_manager'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    if ($action === 'approvea') {
        require_capability('local/th_qaa_manager:approveanswer', $context);

        if (empty($ticketid)) {
            throw new moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        answer_service::approve_answer(
            $ticketid,
            $USER->id
        );

        $redirectparams = $baseurlparams;
        $redirectparams['filter_status'] = 'completed';
        $redirectparams['submitbutton'] = '1';
        $redirectparams['page'] = 0;

        redirect(
            new moodle_url(
                '/local/th_qaa_manager/manage.php',
                $redirectparams
            )
        );
    }

    if ($action === 'rejecta') {
        require_capability(
            'local/th_qaa_manager:approveanswer',
            $context
        );

        if (empty($ticketid)) {
            throw new moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        answer_service::reject_answer(
            $ticketid,
            $USER->id
        );

        redirect(
            new moodle_url(
                '/local/th_qaa_manager/manage.php',
                [
                    'status' => ticket_service::STATUS_APPROVED,
                    'page' => 0,
                ]
            )
        );
    }

    if ($action === 'deleteassign') {
        require_capability('local/th_qaa_manager:assign', $context);

        if (empty($ticketid)) {
            throw new moodle_exception('invalidticket', 'local_th_qaa_manager');
        }

        ticket_service::delete_assignment($ticketid);

        redirect(
            $baseurl,
            get_string('delete_assignment_success', 'local_th_qaa_manager'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    if ($action === 'cancelrejectq') {
        require_capability('local/th_qaa_manager:manage', $context);

        if (empty($ticketid)) {
            throw new moodle_exception('invalidticket', 'local_th_qaa_manager');
        }

        ticket_service::cancel_reject_question($ticketid);

        redirect(
            new moodle_url('/local/th_qaa_manager/manage.php', [
                'status' => ticket_service::STATUS_APPROVED,
            ]),
            get_string('cancelreject_success', 'local_th_qaa_manager'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

$download = optional_param('download', '', PARAM_ALPHA);
if ($download === 'excel') {
    require_once($CFG->libdir . '/excellib.class.php');

    $userfieldsapi = fields::for_name();
    $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

    $ctvfieldsapi = fields::for_name();
    $ctvfields = $ctvfieldsapi->get_sql('ctv', false, 'ctv_', '', false)->selects;

    $revfieldsapi = fields::for_name();
    $revfields = $revfieldsapi->get_sql('rev', false, 'rev_', '', false)->selects;

    $studentfieldsapi = fields::for_name();
    $studentfields = $studentfieldsapi->get_sql('u', false, 'student_', '', false)->selects;

    if ((int)$status === STATUS_QAA_STATS) {
        $filename = 'chi_tiet_trang_thai_ctv_' . date('Ymd_Hi', time()) . '.xlsx';
        $workbook = new MoodleExcelWorkbook($filename, 'Excel2007');
        $worksheet = $workbook->add_worksheet('Thong ke CTV');

        $format_header = $workbook->add_format([
            'bold' => 1,
            'size' => 11,
            'align' => 'center',
            'valign' => 'vcenter',
            'bg_color' => 'silver',
            'border' => 1,
        ]);
        $format_stt = $workbook->add_format([
            'size' => 11,
            'align' => 'center',
            'valign' => 'vcenter',
            'border' => 1,
        ]);
        $format_text = $workbook->add_format([
            'size' => 11,
            'valign' => 'vcenter',
            'border' => 1,
        ]);
        $format_number = $workbook->add_format([
            'size' => 11,
            'align' => 'center',
            'valign' => 'vcenter',
            'border' => 1,
        ]);
        $format_total = $workbook->add_format([
            'bold' => 1,
            'size' => 11,
            'align' => 'center',
            'valign' => 'vcenter',
            'bg_color' => '#E8EEF5',
            'border' => 1,
        ]);
        $format_total_label = $workbook->add_format([
            'bold' => 1,
            'size' => 11,
            'align' => 'center',
            'valign' => 'vcenter',
            'bg_color' => '#E8EEF5',
            'border' => 1,
        ]);

        $sql_ctv_stats = "
            SELECT ah.userid AS ctv_id,
                   COUNT(DISTINCT t.qaapairid) AS count_assigned,
                   COUNT(DISTINCT CASE WHEN t.status IN (:st_ans, :st_rej, :st_closed) THEN t.qaapairid END) AS count_answered,
                   COUNT(DISTINCT CASE WHEN t.status = :st_pending THEN t.qaapairid END) AS count_pending,
                   COUNT(DISTINCT CASE WHEN t.status = :st_comp THEN t.qaapairid END) AS count_completed,
                   COUNT(DISTINCT CASE WHEN (ah.recalltype IN (1, 2) OR t.status = 7) AND (t.status = 7 OR t.assigneduserid IS NULL OR t.assigneduserid = 0 OR t.assigneduserid <> ah.userid) THEN t.qaapairid END) AS count_recalled
              FROM {local_th_qaa_assign_history} ah
              JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
              JOIN {qaapairs} qp ON qp.id = t.qaapairid";
        $ctv_stats_params = [
            'st_ans' => ticket_service::STATUS_ANSWERED,
            'st_rej' => ticket_service::STATUS_ANSWER_REJECTED,
            'st_closed' => ticket_service::STATUS_CLOSED,
            'st_pending' => ticket_service::STATUS_ANSWERED,
            'st_comp' => ticket_service::STATUS_CLOSED,
            'start' => $startdate_ts,
            'end' => $enddate_ts,
        ];
        if ($companyid > 0) {
            $sql_ctv_stats .= " JOIN {company_users} cu ON cu.userid = qp.studentid AND cu.companyid = :stats_company_id";
            $ctv_stats_params['stats_company_id'] = $companyid;
        }
        $sql_ctv_stats .= " WHERE ah.timeassigned >= :start AND ah.timeassigned <= :end GROUP BY ah.userid";

        $ctv_stats = $DB->get_records_sql($sql_ctv_stats, $ctv_stats_params);

        $headers = [
            get_string('col_ctv', 'local_th_qaa_manager'),
            get_string('col_assigned_count', 'local_th_qaa_manager'),
            get_string('col_answered_count', 'local_th_qaa_manager'),
            get_string('col_pending_count', 'local_th_qaa_manager'),
            get_string('col_completed_count', 'local_th_qaa_manager'),
            get_string('col_recalled_count', 'local_th_qaa_manager'),
        ];

        $col = 0;
        foreach ($headers as $header) {
            $worksheet->write_string(0, $col++, $header, $format_header);
        }

        $rowidx = 1;
        foreach ($ctvusers as $ctv) {
            $stat = $ctv_stats[$ctv->id] ?? null;
            $assigned = $stat ? $stat->count_assigned : 0;
            $answered = $stat ? $stat->count_answered : 0;
            $pending = $stat ? $stat->count_pending : 0;
            $completed = $stat ? $stat->count_completed : 0;
            $recalled = $stat ? $stat->count_recalled : 0;

            $worksheet->write_string($rowidx, 0, fullname($ctv), $format_text);
            $worksheet->write_string($rowidx, 1, get_string('count_questions_unit', 'local_th_qaa_manager', $assigned), $format_number);
            $worksheet->write_string($rowidx, 2, get_string('count_questions_unit', 'local_th_qaa_manager', $answered), $format_number);
            $worksheet->write_string($rowidx, 3, get_string('count_questions_unit', 'local_th_qaa_manager', $pending), $format_number);
            $worksheet->write_string($rowidx, 4, get_string('count_questions_unit', 'local_th_qaa_manager', $completed), $format_number);
            $worksheet->write_string($rowidx, 5, get_string('count_questions_unit', 'local_th_qaa_manager', $recalled), $format_number);

            $rowidx++;
        }

        $workbook->close();
        exit;
    }

    if ((int)$status === STATUS_QAA_MANAGE) {
        $whereclauses = [];
        $params = [];

        if (!empty($filter_ctv)) {
            list($ctvsql, $ctvparams) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv');
            $whereclauses[] = 'COALESCE(t.assigneduserid, a.userid) ' . $ctvsql;
            $params = array_merge($params, $ctvparams);
        }

        if (!empty($filter_course)) {
            list($coursesql, $courseparams) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse');
            $whereclauses[] = 'c.id ' . $coursesql;
            $params = array_merge($params, $courseparams);
        }

        if ($filter_startdate > 0) {
            $whereclauses[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) >= :filter_startdate';
            $params['filter_startdate'] = $filter_startdate;
        }

        if ($filter_enddate > 0) {
            $whereclauses[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) <= :filter_enddate';
            $params['filter_enddate'] = $filter_enddate + 86399;
        }

        if ($companyid > 0) {
            $whereclauses[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :companyid)';
            $params['companyid'] = $companyid;
        }

        if ($filter_status === 'completed') {
            $whereclauses[] = 't.status = :status_closed';
            $params['status_closed'] = ticket_service::STATUS_CLOSED;
        } else if ($filter_status === 'incomplete') {
            $whereclauses[] = 't.status != :status_closed';
            $params['status_closed'] = ticket_service::STATUS_CLOSED;
        } else if ($filter_status === 'answered') {
            $whereclauses[] = 't.status = :status_answered';
            $params['status_answered'] = ticket_service::STATUS_ANSWERED;
        } else if ($filter_status === 'assigned') {
            $whereclauses[] = 't.status = :status_assigned';
            $params['status_assigned'] = ticket_service::STATUS_ASSIGNED;
        } else if ($filter_status === 'recalled') {
            $whereclauses[] = '(t.status = :status_recalled OR t.id IN (SELECT ticketid FROM {local_th_qaa_assign_history} WHERE recalltype IN (1, 2)))';
            $params['status_recalled'] = ticket_service::STATUS_RECALLED;
        }

        if ($filter_status === 'recalled') {
            $whereclauses = ['ah.timerecalled IS NOT NULL', 'ah.recalltype IN (1, 2)'];
            $params = [];

            if (!empty($filter_ctv)) {
                list($ctvsql, $ctvparams) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv');
                $whereclauses[] = 'ah.userid ' . $ctvsql;
                $params = array_merge($params, $ctvparams);
            }

            if (!empty($filter_course)) {
                list($coursesql, $courseparams) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse');
                $whereclauses[] = 'c.id ' . $coursesql;
                $params = array_merge($params, $courseparams);
            }

            if ($filter_startdate > 0) {
                $whereclauses[] = 'ah.timeassigned >= :filter_startdate';
                $params['filter_startdate'] = $filter_startdate;
            }

            if ($filter_enddate > 0) {
                $whereclauses[] = 'ah.timeassigned <= :filter_enddate';
                $params['filter_enddate'] = $filter_enddate + 86399;
            }

            if ($companyid > 0) {
                $whereclauses[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :companyid)';
                $params['companyid'] = $companyid;
            }

            $whereclauses[] = 'ah.id IN (SELECT MAX(id) FROM {local_th_qaa_assign_history} WHERE timerecalled IS NOT NULL AND recalltype IN (1, 2) GROUP BY ticketid)';
            $whereclauses[] = '(t.status = 7 OR t.assigneduserid IS NULL OR t.assigneduserid = 0 OR t.assigneduserid <> ah.userid)';

            $wheresql = ' WHERE ' . implode(' AND ', $whereclauses);

            $sql = "SELECT CONCAT('ah_', ah.id) AS rowuniqueid,
                           ah.id AS historyid,
                           ah.ticketid,
                           ah.userid AS ctvid,
                           ah.assignedby,
                           ah.timeassigned,
                           ah.timerecalled,
                           ah.recalltype,
                           7 AS ticketstatus,
                           qp.id AS qaapairid,
                           qp.message AS question,
                           qp.timecreatedquestion,
                           qp.parentid,
                           qp.timecreatedanswer AS qp_timecreatedanswer,
                           c.id AS courseid,
                           c.fullname AS coursefullname,
                           c.shortname AS courseshortname,
                           c.idnumber AS courseidnumber,
                           u.id AS studentid,
                           ctv.id AS ctvid_user,
                           cm.id AS cmid,
                           {$company_name_select}
                           {$studentfields},
                           {$ctvfields}
                      FROM {local_th_qaa_assign_history} ah
                      JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
                      JOIN {qaapairs} qp ON qp.id = t.qaapairid
                      JOIN {qaa} q ON q.id = qp.qaaid
                      JOIN {course} c ON c.id = q.course
                      JOIN {modules} md ON md.name = 'qaa'
                      JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                      JOIN {user} u ON u.id = qp.studentid
                      JOIN {user} ctv ON ctv.id = ah.userid
                      {$wheresql}
                  ORDER BY ah.timerecalled DESC, ah.id DESC";

            $export_tickets = $DB->get_records_sql($sql, $params);
        } else if (empty($filter_status) || $filter_status === 'all' || $filter_status === 'incomplete') {
            $where1 = [];
            $params1 = [];
            if (!empty($filter_ctv)) {
                list($ctvsql, $ctvparams) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv1');
                $where1[] = 'COALESCE(t.assigneduserid, a.userid) ' . $ctvsql;
                $params1 = array_merge($params1, $ctvparams);
            }
            if (!empty($filter_course)) {
                list($coursesql, $courseparams) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse1');
                $where1[] = 'c.id ' . $coursesql;
                $params1 = array_merge($params1, $courseparams);
            }
            if ($filter_status === 'incomplete') {
                $where1[] = 't.status != :stclosed1';
                $params1['stclosed1'] = ticket_service::STATUS_CLOSED;
            }
            $where1[] = 't.status != 7';
            if ($filter_startdate > 0) {
                $where1[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) >= :fstart1';
                $params1['fstart1'] = $filter_startdate;
            }
            if ($filter_enddate > 0) {
                $where1[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) <= :fend1';
                $params1['fend1'] = $filter_enddate + 86399;
            }
            if ($companyid > 0) {
                $where1[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :fcomp1)';
                $params1['fcomp1'] = $companyid;
            }
            $wheresql1 = $where1 ? ' WHERE ' . implode(' AND ', $where1) : '';

            $sql1 = "SELECT CONCAT('t_', t.id) AS rowuniqueid,
                            t.id AS ticketid,
                            0 AS historyid,
                            t.status AS ticketstatus,
                            t.assigneduserid,
                            t.reviewerid,
                            COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) AS assign_timecreated,
                            qp.id AS qaapairid,
                            qp.message AS question,
                            qp.timecreatedquestion,
                            qp.parentid,
                            qp.timecreatedanswer AS qp_timecreatedanswer,
                            c.id AS courseid,
                            c.fullname AS coursefullname,
                            c.shortname AS courseshortname,
                            c.idnumber AS courseidnumber,
                            u.id AS studentid,
                            ctv.id AS ctvid,
                            ctv.email AS ctv_email,
                            a.id AS answerid,
                            a.timecreated AS answer_timecreated,
                            a.status AS answer_status,
                            rev.id AS reviewerid_user,
                            cm.id AS cmid,
                            {$company_name_select}
                            {$studentfields},
                            {$ctvfields},
                            {$revfields}
                       FROM {local_th_qaa_ticket} t
                       JOIN {qaapairs} qp ON qp.id = t.qaapairid
                       JOIN {qaa} q ON q.id = qp.qaaid
                       JOIN {course} c ON c.id = q.course
                       JOIN {modules} md ON md.name = 'qaa'
                       JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                       JOIN {user} u ON u.id = qp.studentid
                  LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id AND (t.assigneduserid IS NULL OR a.userid = t.assigneduserid)
                  LEFT JOIN {user} ctv ON ctv.id = COALESCE(t.assigneduserid, a.userid)
                  LEFT JOIN {user} rev ON rev.id = t.reviewerid
                       {$wheresql1}";

            $where2 = ['ah.timerecalled IS NOT NULL', 'ah.recalltype IN (1, 2)'];
            $params2 = [];
            if (!empty($filter_ctv)) {
                list($ctvsql2, $ctvparams2) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv2');
                $where2[] = 'ah.userid ' . $ctvsql2;
                $params2 = array_merge($params2, $ctvparams2);
            }
            if (!empty($filter_course)) {
                list($coursesql2, $courseparams2) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse2');
                $where2[] = 'c.id ' . $coursesql2;
                $params2 = array_merge($params2, $courseparams2);
            }
            if ($filter_startdate > 0) {
                $where2[] = 'ah.timeassigned >= :fstart2';
                $params2['fstart2'] = $filter_startdate;
            }
            if ($filter_enddate > 0) {
                $where2[] = 'ah.timeassigned <= :fend2';
                $params2['fend2'] = $filter_enddate + 86399;
            }
            if ($companyid > 0) {
                $where2[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :fcomp2)';
                $params2['fcomp2'] = $companyid;
            }
            $where2[] = 'ah.id IN (SELECT MAX(id) FROM {local_th_qaa_assign_history} WHERE timerecalled IS NOT NULL AND recalltype IN (1, 2) GROUP BY ticketid)';
            $where2[] = '(t.status = 7 OR t.assigneduserid IS NULL OR t.assigneduserid = 0 OR t.assigneduserid <> ah.userid)';
            $wheresql2 = ' WHERE ' . implode(' AND ', $where2);

            $null_revfields = preg_replace('/rev\.([a-z0-9_]+)\s+AS\s+([a-z0-9_]+)/i', 'NULL AS $2', $revfields);

            $sql2 = "SELECT CONCAT('ah_', ah.id) AS rowuniqueid,
                            t.id AS ticketid,
                            ah.id AS historyid,
                            7 AS ticketstatus,
                            ah.userid AS assigneduserid,
                            0 AS reviewerid,
                            ah.timeassigned AS assign_timecreated,
                            qp.id AS qaapairid,
                            qp.message AS question,
                            qp.timecreatedquestion,
                            qp.parentid,
                            qp.timecreatedanswer AS qp_timecreatedanswer,
                            c.id AS courseid,
                            c.fullname AS coursefullname,
                            c.shortname AS courseshortname,
                            c.idnumber AS courseidnumber,
                            u.id AS studentid,
                            ctv.id AS ctvid,
                            ctv.email AS ctv_email,
                            0 AS answerid,
                            0 AS answer_timecreated,
                            0 AS answer_status,
                            0 AS reviewerid_user,
                            cm.id AS cmid,
                            {$company_name_select}
                            {$studentfields},
                            {$ctvfields},
                            {$null_revfields}
                       FROM {local_th_qaa_assign_history} ah
                       JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
                       JOIN {qaapairs} qp ON qp.id = t.qaapairid
                       JOIN {qaa} q ON q.id = qp.qaaid
                       JOIN {course} c ON c.id = q.course
                       JOIN {modules} md ON md.name = 'qaa'
                       JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                       JOIN {user} u ON u.id = qp.studentid
                       JOIN {user} ctv ON ctv.id = ah.userid
                       {$wheresql2}";

            $params = array_merge($params1, $params2);
            $sql = "({$sql1}) UNION ALL ({$sql2}) ORDER BY ticketid DESC, historyid DESC";
            $export_tickets = $DB->get_records_sql($sql, $params);
        } else {
            $wheresql = '';
            if ($whereclauses) {
                $wheresql = ' WHERE ' . implode(' AND ', $whereclauses);
            }

            $sql = "SELECT t.id AS ticketid,
                           t.status AS ticketstatus,
                           t.assigneduserid,
                           t.reviewerid,
                           COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) AS assign_timecreated,
                           qp.id AS qaapairid,
                           qp.message AS question,
                           qp.timecreatedquestion,
                           qp.parentid,
                           qp.timecreatedanswer AS qp_timecreatedanswer,
                           c.id AS courseid,
                           c.fullname AS coursefullname,
                           c.shortname AS courseshortname,
                           c.idnumber AS courseidnumber,
                           u.id AS studentid,
                           ctv.id AS ctvid,
                           ctv.email AS ctv_email,
                           a.id AS answerid,
                           a.timecreated AS answer_timecreated,
                           a.status AS answer_status,
                           rev.id AS reviewerid_user,
                           cm.id AS cmid,
                           {$company_name_select}
                           {$studentfields},
                           {$ctvfields},
                           {$revfields}
                      FROM {local_th_qaa_ticket} t
                      JOIN {qaapairs} qp ON qp.id = t.qaapairid
                      JOIN {qaa} q ON q.id = qp.qaaid
                      JOIN {course} c ON c.id = q.course
                      JOIN {modules} md ON md.name = 'qaa'
                      JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                      JOIN {user} u ON u.id = qp.studentid
                 LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id AND (t.assigneduserid IS NULL OR a.userid = t.assigneduserid)
                 LEFT JOIN {user} ctv ON ctv.id = COALESCE(t.assigneduserid, a.userid)
                 LEFT JOIN {user} rev ON rev.id = t.reviewerid
                      {$wheresql}
                  ORDER BY t.id DESC";

            $export_tickets = $DB->get_records_sql($sql, $params);
        }
    } else if ((int)$status === ticket_service::STATUS_APPROVED) {
        $approved_comp_where1 = '';
        $approved_comp_where2 = '';
        $export_params = [
            'status_approved' => ticket_service::STATUS_APPROVED,
            'status_rejected' => ticket_service::STATUS_ANSWER_REJECTED,
            'status_recalled' => ticket_service::STATUS_RECALLED,
        ];
        if ($companyid > 0) {
            $approved_comp_where1 = ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :appr_comp1)';
            $approved_comp_where2 = ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :appr_comp2)';
            $export_params['appr_comp1'] = $companyid;
            $export_params['appr_comp2'] = $companyid;
        }

        $sql = "
            (SELECT CONCAT('t_', t.id) AS rowuniqueid,
                   qp.id AS id,
                   t.id AS ticketid,
                   t.status AS status,
                   t.assigneduserid,
                   t.reviewerid,
                   qp.id AS qaapairid,
                   qp.message AS question,
                   qp.timecreatedquestion,
                   qp.parentid,
                   qp.timecreatedanswer AS qp_timecreatedanswer,
                   q.course AS courseid,
                   c.fullname AS coursefullname,
                   c.idnumber AS courseidnumber,
                   c.shortname AS courseshortname,
                   uid.data AS student_aum_code,
                   {$company_name_select}
                   {$userfields}
              FROM {local_th_qaa_ticket} t
              JOIN {qaapairs} qp ON qp.id = t.qaapairid
              JOIN {qaa} q ON q.id = qp.qaaid
              JOIN {course} c ON c.id = q.course
              JOIN {user} u ON u.id = qp.studentid
         LEFT JOIN {user_info_field} uif ON uif.shortname = 'th_crm_code'
         LEFT JOIN {user_info_data} uid ON uid.fieldid = uif.id AND uid.userid = u.id
             WHERE (t.status = :status_approved OR t.status = :status_rejected OR t.status = :status_recalled)
               AND t.id = (SELECT MAX(id) FROM {local_th_qaa_ticket} t2 WHERE t2.qaapairid = t.qaapairid)
               {$approved_comp_where1})
            UNION ALL
            (SELECT CONCAT('qp_', qp.id) AS rowuniqueid,
                   qp.id AS id,
                   0 AS ticketid,
                   0 AS status,
                   0 AS assigneduserid,
                   0 AS reviewerid,
                   qp.id AS qaapairid,
                   qp.message AS question,
                   qp.timecreatedquestion,
                   qp.parentid,
                   qp.timecreatedanswer AS qp_timecreatedanswer,
                   q.course AS courseid,
                   c.fullname AS coursefullname,
                   c.idnumber AS courseidnumber,
                   c.shortname AS courseshortname,
                   uid.data AS student_aum_code,
                   {$company_name_select}
                   {$userfields}
              FROM {qaapairs} qp
              JOIN {qaa} q ON q.id = qp.qaaid
              JOIN {course} c ON c.id = q.course
              JOIN {user} u ON u.id = qp.studentid
         LEFT JOIN {user_info_field} uif ON uif.shortname = 'th_crm_code'
         LEFT JOIN {user_info_data} uid ON uid.fieldid = uif.id AND uid.userid = u.id
             WHERE qp.status = 0
               {$approved_comp_where2})
          ORDER BY timecreatedquestion DESC";

        $export_tickets = $DB->get_records_sql($sql, $export_params);
    } else {
        $else_comp_where = '';
        $else_params = ['status' => $status];
        if ($companyid > 0) {
            $else_comp_where = ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :else_comp)';
            $else_params['else_comp'] = $companyid;
        }

        $sql = "SELECT t.*,
                       qp.qaaid,
                       qp.message AS question,
                       a.content AS answer,
                       a.contentformat,
                       q.course AS courseid,
                       c.fullname AS coursefullname,
                       {$company_name_select}
                       {$userfields}
                  FROM {local_th_qaa_ticket} t
                  JOIN {qaapairs} qp ON qp.id = t.qaapairid
             LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id
                  JOIN {qaa} q ON q.id = qp.qaaid
                  JOIN {course} c ON c.id = q.course
                  JOIN {user} u ON u.id = qp.studentid
                 WHERE t.status = :status {$else_comp_where}
              ORDER BY t.id DESC";

        $export_tickets = $DB->get_records_sql($sql, $else_params);
    }

    $filename = 'danh_sach_qaa_' . date('Ymd_His') . '.xls';
    $workbook = new MoodleExcelWorkbook(clean_filename($filename));
    $worksheet = $workbook->add_worksheet('QAA List');

    $format_header = $workbook->add_format([
        'bold' => 1,
        'size' => 11,
        'align' => 'center',
        'valign' => 'vcenter',
        'bg_color' => 'silver',
    ]);

    $headers = [
        get_string('col_no', 'local_th_qaa_manager'),
        get_string('col_ctv', 'local_th_qaa_manager'),
        get_string('col_course_name', 'local_th_qaa_manager'),
        get_string('col_course_code', 'local_th_qaa_manager'),
        get_string('question_content', 'local_th_qaa_manager'),
        get_string('col_answer_content', 'local_th_qaa_manager'),
        get_string('student', 'local_th_qaa_manager'),
    ];
    if ($is_iomad) {
        $headers[] = get_string('col_company', 'local_th_qaa_manager');
    }
    $headers = array_merge($headers, [
        get_string('col_crm_code', 'local_th_qaa_manager'),
        get_string('col_date_question', 'local_th_qaa_manager'),
        get_string('col_date_assign', 'local_th_qaa_manager'),
        get_string('col_date_answer', 'local_th_qaa_manager'),
        get_string('status', 'local_th_qaa_manager'),
        get_string('col_approver', 'local_th_qaa_manager'),
    ]);

    $col = 0;
    foreach ($headers as $header) {
        $worksheet->write_string(0, $col++, $header, $format_header);
    }

    $rowidx = 1;
    $stt_num = 1;
    if (!empty($export_tickets)) {
        foreach ($export_tickets as $ticket) {
            $ctvname = '-';
            if (!empty($ticket->assigneduserid)) {
                $ctvuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'ctv_');
                $ctvname = fullname($ctvuser);
            } else if (!empty($ticket->ctvid)) {
                $ctvuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'ctv_');
                $ctvname = fullname($ctvuser);
            }

            $studentuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'student_');
            $studentname = fullname($studentuser);

            $qtext = trim(html_to_text((string)($ticket->question ?? '')));
            $atext = trim(html_to_text((string)($ticket->answer ?? '')));

            $qdate = '-';
            if ((int)($ticket->ticketstatus ?? $ticket->status ?? 0) === ticket_service::STATUS_RECALLED || !empty($ticket->historyid)) {
                if (!empty($ticket->parentid) && !empty($ticket->qp_timecreatedanswer)) {
                    $qdate = date('d/m/Y H:i', $ticket->qp_timecreatedanswer);
                } else if (!empty($ticket->timecreatedquestion)) {
                    $qdate = date('d/m/Y H:i', $ticket->timecreatedquestion);
                } else if (!empty($ticket->timecreated)) {
                    $qdate = date('d/m/Y H:i', $ticket->timecreated);
                }

                $adate = '-';
                $statuslabel = get_string('status_recalled', 'local_th_qaa_manager');
            } else {
                if (!empty($ticket->parentid) && !empty($ticket->qp_timecreatedanswer)) {
                    $qdate = date('d/m/Y H:i', $ticket->qp_timecreatedanswer);
                } else if (!empty($ticket->timecreatedquestion)) {
                    $qdate = date('d/m/Y H:i', $ticket->timecreatedquestion);
                } else if (!empty($ticket->timecreated)) {
                    $qdate = date('d/m/Y H:i', $ticket->timecreated);
                }

                $adate = '-';
                if (!empty($ticket->answer_timecreated)) {
                    $adate = date('d/m/Y H:i', $ticket->answer_timecreated);
                }

                $statuslabel = ticket_service::status_label((int)($ticket->ticketstatus ?? $ticket->status ?? 0));
            }

            $revname = '-';
            if (!empty($ticket->reviewerid)) {
                $revuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'rev_');
                $revname = fullname($revuser);
            }

            $assigndate = '-';
            if ((int)$ticketstatus !== ticket_service::STATUS_QUESTION_REJECTED) {
                if (!empty($ticket->timeassigned)) {
                    $assigndate = date('d/m/Y H:i', $ticket->timeassigned);
                } else if (!empty($ticket->assign_timecreated) && (!empty($ticket->assigneduserid) || !empty($ticket->ctvid))) {
                    $assigndate = date('d/m/Y H:i', $ticket->assign_timecreated);
                }
            }

            $rowcells = [
                ['type' => 'number', 'val' => $stt_num++],
                ['type' => 'string', 'val' => $ctvname],
                ['type' => 'string', 'val' => format_string($ticket->coursefullname ?? '')],
                ['type' => 'string', 'val' => $ticket->courseshortname ?? ''],
                ['type' => 'string', 'val' => $qtext],
                ['type' => 'string', 'val' => $atext],
                ['type' => 'string', 'val' => $studentname],
            ];
            if ($is_iomad) {
                $rowcells[] = ['type' => 'string', 'val' => $ticket->student_company_name ?? '-'];
            }
            $rowcells = array_merge($rowcells, [
                ['type' => 'string', 'val' => $ticket->student_aum_code ?? ''],
                ['type' => 'string', 'val' => $qdate],
                ['type' => 'string', 'val' => $assigndate],
                ['type' => 'string', 'val' => $adate],
                ['type' => 'string', 'val' => $statuslabel],
                ['type' => 'string', 'val' => $revname],
            ]);

            foreach ($rowcells as $colidx => $cell) {
                if ($cell['type'] === 'number') {
                    $worksheet->write_number($rowidx, $colidx, $cell['val']);
                } else {
                    $worksheet->write_string($rowidx, $colidx, $cell['val']);
                }
            }

            $rowidx++;
        }
    }

    $workbook->close();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($active_tab_title, 3);

$tabs = [
    ticket_service::STATUS_APPROVED => get_string('status_approved', 'local_th_qaa_manager'),
    STATUS_QAA_MANAGE => get_string('tab_qaa_manage', 'local_th_qaa_manager'),
    STATUS_QAA_STATS => get_string('tab_qaa_stats', 'local_th_qaa_manager'),
];

if (!array_key_exists((int)$status, $tabs)) {
    $status = ticket_service::STATUS_APPROVED;
}

$tabrow = [];
foreach ($tabs as $key => $label) {
    $tabparams = ['status' => $key];
    $tabrow[] = new tabobject(
        (string)$key,
        new moodle_url('/local/th_qaa_manager/manage.php', $tabparams),
        $label
    );
}

echo $OUTPUT->tabtree($tabrow, (string)$status);

if ((int)$status === STATUS_QAA_MANAGE) {
    $mform->display();
}

if ((int)$status === STATUS_QAA_STATS) {
    $stats_form->display();

    // 1. Calculate overview metrics (counts root student questions + student follow-up replies)
    $new_q_sql = "SELECT COUNT(1)
           FROM {qaapairs} qp
          WHERE qp.studentid IS NOT NULL AND qp.studentid > 0
            AND COALESCE(NULLIF(qp.timecreatedquestion, 0), qp.timecreatedanswer) >= :start
            AND COALESCE(NULLIF(qp.timecreatedquestion, 0), qp.timecreatedanswer) <= :end";
    $new_q_params = ['start' => $startdate_ts, 'end' => $enddate_ts];
    if ($companyid > 0) {
        $new_q_sql .= " AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :st_comp)";
        $new_q_params['st_comp'] = $companyid;
    }
    $new_questions_count = $DB->count_records_sql($new_q_sql, $new_q_params);

    // 2. Render 'Tổng số câu hỏi' line above
    echo html_writer::tag(
        'h4',
        get_string('stats_total_new', 'local_th_qaa_manager') . ': ' . html_writer::tag('span', get_string('count_questions_unit', 'local_th_qaa_manager', $new_questions_count), ['class' => 'text-primary font-weight-bold']),
        ['class' => 'font-weight-bold th-qaa-stats-heading']
    );

    // 3. CTV details header & Export Excel button
    $export_ctv_url = new moodle_url('/local/th_qaa_manager/manage.php', array_merge($baseurlparams, [
        'status' => STATUS_QAA_STATS,
        'download' => 'excel',
    ]));

    echo html_writer::start_div('d-flex justify-content-between align-items-center th-qaa-stats-subheading-row');
    echo html_writer::tag('h4', get_string('ctv_status_detail', 'local_th_qaa_manager'), ['class' => 'font-weight-bold mb-0']);
    echo html_writer::link(
        $export_ctv_url,
        '<i class="fa fa-file-excel-o mr-1"></i> ' . get_string('exportexcel', 'local_th_qaa_manager'),
        ['class' => 'btn btn-success font-weight-bold shadow-sm']
    );
    echo html_writer::end_div();

    $sql_ctv_stats = "
        SELECT ah.userid AS ctv_id,
               COUNT(DISTINCT t.qaapairid) AS count_assigned,
               COUNT(DISTINCT CASE WHEN t.status IN (:st_ans, :st_rej, :st_closed) THEN t.qaapairid END) AS count_answered,
               COUNT(DISTINCT CASE WHEN t.status = :st_pending THEN t.qaapairid END) AS count_pending,
               COUNT(DISTINCT CASE WHEN t.status = :st_comp THEN t.qaapairid END) AS count_completed,
               COUNT(DISTINCT CASE WHEN (ah.recalltype IN (1, 2) OR t.status = 7) AND (t.status = 7 OR t.assigneduserid IS NULL OR t.assigneduserid = 0 OR t.assigneduserid <> ah.userid) THEN t.qaapairid END) AS count_recalled
          FROM {local_th_qaa_assign_history} ah
          JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
          JOIN {qaapairs} qp ON qp.id = t.qaapairid";
    $ctv_stats_params = [
        'st_ans' => ticket_service::STATUS_ANSWERED,
        'st_rej' => ticket_service::STATUS_ANSWER_REJECTED,
        'st_closed' => ticket_service::STATUS_CLOSED,
        'st_pending' => ticket_service::STATUS_ANSWERED,
        'st_comp' => ticket_service::STATUS_CLOSED,
        'start' => $startdate_ts,
        'end' => $enddate_ts,
    ];
    if ($companyid > 0) {
        $sql_ctv_stats .= " JOIN {company_users} cu ON cu.userid = qp.studentid AND cu.companyid = :stats_company_id";
        $ctv_stats_params['stats_company_id'] = $companyid;
    }
    $sql_ctv_stats .= " WHERE ah.timeassigned >= :start AND ah.timeassigned <= :end GROUP BY ah.userid";

    $ctv_stats = $DB->get_records_sql($sql_ctv_stats, $ctv_stats_params);

    $table = new html_table();
    $table->head = [
        get_string('col_ctv', 'local_th_qaa_manager'),
        get_string('col_assigned_count', 'local_th_qaa_manager'),
        get_string('col_answered_count', 'local_th_qaa_manager'),
        get_string('col_pending_count', 'local_th_qaa_manager'),
        get_string('col_completed_count', 'local_th_qaa_manager'),
        get_string('col_recalled_count', 'local_th_qaa_manager'),
    ];
    $table->attributes = [
        'class' => 'th_qaa_manager display generaltable text-left w-100 mt-2',
        'border' => '1',
    ];
    $table->data = [];

    foreach ($ctvusers as $ctv) {
        $stat = $ctv_stats[$ctv->id] ?? null;
        $assigned = $stat ? $stat->count_assigned : 0;
        $answered = $stat ? $stat->count_answered : 0;
        $pending = $stat ? $stat->count_pending : 0;
        $completed = $stat ? $stat->count_completed : 0;
        $recalled = $stat ? $stat->count_recalled : 0;

        $table->data[] = [
            s(fullname($ctv)),
            get_string('count_questions_unit', 'local_th_qaa_manager', $assigned),
            get_string('count_questions_unit', 'local_th_qaa_manager', $answered),
            get_string('count_questions_unit', 'local_th_qaa_manager', $pending),
            get_string('count_questions_unit', 'local_th_qaa_manager', $completed),
            get_string('count_questions_unit', 'local_th_qaa_manager', $recalled),
        ];
    }

    echo html_writer::table($table);

    echo $OUTPUT->footer();
    exit;
}

// Dynamic perpage parameter is used.

$userfieldsapi = fields::for_name();
$userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

$ctvfieldsapi = fields::for_name();
$ctvfields = $ctvfieldsapi->get_sql('ctv', false, 'ctv_', '', false)->selects;

$revfieldsapi = fields::for_name();
$revfields = $revfieldsapi->get_sql('rev', false, 'rev_', '', false)->selects;

$studentfieldsapi = fields::for_name();
$studentfields = $studentfieldsapi->get_sql('u', false, 'student_', '', false)->selects;

// New pending questions are read directly from qaapairs.
// Approved/assigned/answered/closed tickets are read from local_th_qaa_ticket.
if ((int)$status === ticket_service::STATUS_NEW) {
    $where = "qp.status = :status AND (qp.parentid IS NULL OR qp.parentid = 0)";
    $params = ['status' => 0];
    if ($companyid > 0) {
        $where .= ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :c_comp)';
        $params['c_comp'] = $companyid;
    }

    $total = $DB->count_records_sql(
        "SELECT COUNT(1) FROM {qaapairs} qp WHERE {$where}",
        $params
    );

    $sql = "SELECT qp.id AS qaapairid,
                   qp.qaaid,
                   qp.message AS question,
                   qp.status AS qaastatus,
                   qp.timecreatedquestion,
                   q.course AS courseid,
                   c.fullname AS coursefullname,
                   {$company_name_select}
                   {$userfields}
              FROM {qaapairs} qp
              JOIN {qaa} q ON q.id = qp.qaaid
              JOIN {course} c ON c.id = q.course
              JOIN {user} u ON u.id = qp.studentid
             WHERE {$where}
          ORDER BY qp.id DESC";

    $tickets = $DB->get_records_sql(
        $sql,
        $params,
        $page * $perpage,
        $perpage
    );
} else if ((int)$status === STATUS_QAA_MANAGE) {
    if (!$is_filtered) {
        $total = 0;
        $tickets = [];
    } else {
        $whereclauses = [];
        $params = [];

        if (!empty($filter_ctv)) {
            list($ctvsql, $ctvparams) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv');
            $whereclauses[] = 'COALESCE(t.assigneduserid, a.userid) ' . $ctvsql;
            $params = array_merge($params, $ctvparams);
        }

        if (!empty($filter_course)) {
            list($coursesql, $courseparams) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse');
            $whereclauses[] = 'c.id ' . $coursesql;
            $params = array_merge($params, $courseparams);
        }

        if ($filter_startdate > 0) {
            $whereclauses[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) >= :filter_startdate';
            $params['filter_startdate'] = $filter_startdate;
        }

        if ($filter_enddate > 0) {
            $whereclauses[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) <= :filter_enddate';
            $params['filter_enddate'] = $filter_enddate + 86399;
        }

        if ($companyid > 0) {
            $whereclauses[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :companyid)';
            $params['companyid'] = $companyid;
        }

        if ($filter_status === 'completed') {
            $whereclauses[] = 't.status = :status_closed';
            $params['status_closed'] = ticket_service::STATUS_CLOSED;
        } else if ($filter_status === 'incomplete') {
            $whereclauses[] = 't.status != :status_closed';
            $params['status_closed'] = ticket_service::STATUS_CLOSED;
        } else if ($filter_status === 'answered') {
            $whereclauses[] = 't.status = :status_answered';
            $params['status_answered'] = ticket_service::STATUS_ANSWERED;
        } else if ($filter_status === 'assigned') {
            $whereclauses[] = 't.status = :status_assigned';
            $params['status_assigned'] = ticket_service::STATUS_ASSIGNED;
        } else if ($filter_status === 'recalled') {
            $whereclauses[] = '(t.status = :status_recalled OR t.id IN (SELECT ticketid FROM {local_th_qaa_assign_history} WHERE recalltype IN (1, 2)))';
            $params['status_recalled'] = ticket_service::STATUS_RECALLED;
        }


        if ($filter_status === 'recalled') {
            $whereclauses = ['ah.timerecalled IS NOT NULL', 'ah.recalltype IN (1, 2)'];
            $params = [];

            if (!empty($filter_ctv)) {
                list($ctvsql, $ctvparams) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv');
                $whereclauses[] = 'ah.userid ' . $ctvsql;
                $params = array_merge($params, $ctvparams);
            }

            if (!empty($filter_course)) {
                list($coursesql, $courseparams) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse');
                $whereclauses[] = 'c.id ' . $coursesql;
                $params = array_merge($params, $courseparams);
            }

            if ($filter_startdate > 0) {
                $whereclauses[] = 'ah.timeassigned >= :filter_startdate';
                $params['filter_startdate'] = $filter_startdate;
            }

            if ($filter_enddate > 0) {
                $whereclauses[] = 'ah.timeassigned <= :filter_enddate';
                $params['filter_enddate'] = $filter_enddate + 86399;
            }

            if ($companyid > 0) {
                $whereclauses[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :companyid)';
                $params['companyid'] = $companyid;
            }

            $whereclauses[] = 'ah.id IN (SELECT MAX(id) FROM {local_th_qaa_assign_history} WHERE timerecalled IS NOT NULL AND recalltype IN (1, 2) GROUP BY ticketid)';
            $whereclauses[] = '(t.status = 7 OR t.assigneduserid IS NULL OR t.assigneduserid = 0 OR t.assigneduserid <> ah.userid)';

            $wheresql = ' WHERE ' . implode(' AND ', $whereclauses);

            $countsql = "SELECT COUNT(DISTINCT ah.ticketid)
                           FROM {local_th_qaa_assign_history} ah
                           JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
                           JOIN {qaapairs} qp ON qp.id = t.qaapairid
                           JOIN {qaa} q ON q.id = qp.qaaid
                           JOIN {course} c ON c.id = q.course
                           JOIN {user} u ON u.id = qp.studentid
                           JOIN {user} ctv ON ctv.id = ah.userid
                           {$wheresql}";

            $total = $DB->count_records_sql($countsql, $params);

            $sql = "SELECT ah.id AS historyid,
                           ah.ticketid,
                           ah.userid AS ctvid,
                           ah.assignedby,
                           ah.timeassigned,
                           ah.timerecalled,
                           ah.recalltype,
                           t.status AS ticketstatus,
                           qp.id AS qaapairid,
                           qp.message AS question,
                           qp.timecreatedquestion,
                           qp.parentid,
                           qp.timecreatedanswer AS qp_timecreatedanswer,
                           c.id AS courseid,
                           c.fullname AS coursefullname,
                           c.shortname AS courseshortname,
                           c.idnumber AS courseidnumber,
                           u.id AS studentid,
                           ctv.id AS ctvid_user,
                           cm.id AS cmid,
                           {$company_name_select}
                           {$studentfields},
                           {$ctvfields}
                      FROM {local_th_qaa_assign_history} ah
                      JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
                      JOIN {qaapairs} qp ON qp.id = t.qaapairid
                      JOIN {qaa} q ON q.id = qp.qaaid
                      JOIN {course} c ON c.id = q.course
                      JOIN {modules} md ON md.name = 'qaa'
                      JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                      JOIN {user} u ON u.id = qp.studentid
                      JOIN {user} ctv ON ctv.id = ah.userid
                      {$wheresql}
                  ORDER BY ah.timerecalled DESC, ah.id DESC";

            $tickets = $DB->get_records_sql(
                $sql,
                $params,
                $page * $perpage,
                $perpage
            );
        } else if (empty($filter_status) || $filter_status === 'all' || $filter_status === 'incomplete') {
            $where1 = [];
            $params1 = [];
            if (!empty($filter_ctv)) {
                list($ctvsql, $ctvparams) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv1');
                $where1[] = 'COALESCE(t.assigneduserid, a.userid) ' . $ctvsql;
                $params1 = array_merge($params1, $ctvparams);
            }
            if (!empty($filter_course)) {
                list($coursesql, $courseparams) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse1');
                $where1[] = 'c.id ' . $coursesql;
                $params1 = array_merge($params1, $courseparams);
            }
            if ($filter_status === 'incomplete') {
                $where1[] = 't.status != :stclosed1';
                $params1['stclosed1'] = ticket_service::STATUS_CLOSED;
            }
            $where1[] = 't.status != 7';
            if ($filter_startdate > 0) {
                $where1[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) >= :fstart1';
                $params1['fstart1'] = $filter_startdate;
            }
            if ($filter_enddate > 0) {
                $where1[] = 'COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) <= :fend1';
                $params1['fend1'] = $filter_enddate + 86399;
            }
            if ($companyid > 0) {
                $where1[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :fcomp1)';
                $params1['fcomp1'] = $companyid;
            }
            $wheresql1 = $where1 ? ' WHERE ' . implode(' AND ', $where1) : '';

            $sql1 = "SELECT CONCAT('t_', t.id) AS rowuniqueid,
                            t.id AS ticketid,
                            0 AS historyid,
                            t.status AS ticketstatus,
                            t.assigneduserid,
                            t.reviewerid,
                            COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) AS assign_timecreated,
                            qp.id AS qaapairid,
                            qp.message AS question,
                            qp.timecreatedquestion,
                            qp.parentid,
                            qp.timecreatedanswer AS qp_timecreatedanswer,
                            c.id AS courseid,
                            c.fullname AS coursefullname,
                            c.shortname AS courseshortname,
                            c.idnumber AS courseidnumber,
                            u.id AS studentid,
                            ctv.id AS ctvid,
                            ctv.email AS ctv_email,
                            a.id AS answerid,
                            a.timecreated AS answer_timecreated,
                            a.status AS answer_status,
                            rev.id AS reviewerid_user,
                            cm.id AS cmid,
                            {$company_name_select}
                            {$studentfields},
                            {$ctvfields},
                            {$revfields}
                       FROM {local_th_qaa_ticket} t
                       JOIN {qaapairs} qp ON qp.id = t.qaapairid
                       JOIN {qaa} q ON q.id = qp.qaaid
                       JOIN {course} c ON c.id = q.course
                       JOIN {modules} md ON md.name = 'qaa'
                       JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                       JOIN {user} u ON u.id = qp.studentid
                  LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id AND (t.assigneduserid IS NULL OR a.userid = t.assigneduserid)
                  LEFT JOIN {user} ctv ON ctv.id = COALESCE(t.assigneduserid, a.userid)
                  LEFT JOIN {user} rev ON rev.id = t.reviewerid
                       {$wheresql1}";

            $where2 = ['ah.timerecalled IS NOT NULL', 'ah.recalltype IN (1, 2)'];
            $params2 = [];
            if (!empty($filter_ctv)) {
                list($ctvsql2, $ctvparams2) = $DB->get_in_or_equal($filter_ctv, SQL_PARAMS_NAMED, 'fctv2');
                $where2[] = 'ah.userid ' . $ctvsql2;
                $params2 = array_merge($params2, $ctvparams2);
            }
            if (!empty($filter_course)) {
                list($coursesql2, $courseparams2) = $DB->get_in_or_equal($filter_course, SQL_PARAMS_NAMED, 'fcourse2');
                $where2[] = 'c.id ' . $coursesql2;
                $params2 = array_merge($params2, $courseparams2);
            }
            if ($filter_startdate > 0) {
                $where2[] = 'ah.timeassigned >= :fstart2';
                $params2['fstart2'] = $filter_startdate;
            }
            if ($filter_enddate > 0) {
                $where2[] = 'ah.timeassigned <= :fend2';
                $params2['fend2'] = $filter_enddate + 86399;
            }
            if ($companyid > 0) {
                $where2[] = 'EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :fcomp2)';
                $params2['fcomp2'] = $companyid;
            }
            $where2[] = 'ah.id IN (SELECT MAX(id) FROM {local_th_qaa_assign_history} WHERE timerecalled IS NOT NULL AND recalltype IN (1, 2) GROUP BY ticketid)';
            $where2[] = '(t.status = 7 OR t.assigneduserid IS NULL OR t.assigneduserid = 0 OR t.assigneduserid <> ah.userid)';
            $wheresql2 = ' WHERE ' . implode(' AND ', $where2);

            $null_revfields = preg_replace('/rev\.([a-z0-9_]+)\s+AS\s+([a-z0-9_]+)/i', 'NULL AS $2', $revfields);

            $sql2 = "SELECT CONCAT('ah_', ah.id) AS rowuniqueid,
                            t.id AS ticketid,
                            ah.id AS historyid,
                            7 AS ticketstatus,
                            ah.userid AS assigneduserid,
                            0 AS reviewerid,
                            ah.timeassigned AS assign_timecreated,
                            qp.id AS qaapairid,
                            qp.message AS question,
                            qp.timecreatedquestion,
                            qp.parentid,
                            qp.timecreatedanswer AS qp_timecreatedanswer,
                            c.id AS courseid,
                            c.fullname AS coursefullname,
                            c.shortname AS courseshortname,
                            c.idnumber AS courseidnumber,
                            u.id AS studentid,
                            ctv.id AS ctvid,
                            ctv.email AS ctv_email,
                            0 AS answerid,
                            0 AS answer_timecreated,
                            0 AS answer_status,
                            0 AS reviewerid_user,
                            cm.id AS cmid,
                            {$company_name_select}
                            {$studentfields},
                            {$ctvfields},
                            {$null_revfields}
                       FROM {local_th_qaa_assign_history} ah
                       JOIN {local_th_qaa_ticket} t ON t.id = ah.ticketid
                       JOIN {qaapairs} qp ON qp.id = t.qaapairid
                       JOIN {qaa} q ON q.id = qp.qaaid
                       JOIN {course} c ON c.id = q.course
                       JOIN {modules} md ON md.name = 'qaa'
                       JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                       JOIN {user} u ON u.id = qp.studentid
                       JOIN {user} ctv ON ctv.id = ah.userid
                       {$wheresql2}";

            $params = array_merge($params1, $params2);
            $countsql = "SELECT COUNT(1) FROM (({$sql1}) UNION ALL ({$sql2})) counttbl";
            $total = $DB->count_records_sql($countsql, $params);

            $sql = "({$sql1}) UNION ALL ({$sql2}) ORDER BY ticketid DESC, historyid DESC";
            $tickets = $DB->get_records_sql(
                $sql,
                $params,
                $page * $perpage,
                $perpage
            );
        } else {
            $wheresql = '';
            if ($whereclauses) {
                $wheresql = ' WHERE ' . implode(' AND ', $whereclauses);
            }

            $countsql = "SELECT COUNT(1)
                           FROM {local_th_qaa_ticket} t
                           JOIN {qaapairs} qp ON qp.id = t.qaapairid
                           JOIN {qaa} q ON q.id = qp.qaaid
                           JOIN {course} c ON c.id = q.course
                           JOIN {user} u ON u.id = qp.studentid
                      LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id AND (t.assigneduserid IS NULL OR a.userid = t.assigneduserid)
                      LEFT JOIN {user} ctv ON ctv.id = COALESCE(t.assigneduserid, a.userid)
                      LEFT JOIN {user} rev ON rev.id = t.reviewerid
                          {$wheresql}";

            $total = $DB->count_records_sql($countsql, $params);

            $sql = "SELECT t.id AS ticketid,
                           t.status AS ticketstatus,
                           t.assigneduserid,
                           t.reviewerid,
                           COALESCE((SELECT MAX(ah.timeassigned) FROM {local_th_qaa_assign_history} ah WHERE ah.ticketid = t.id AND (t.assigneduserid IS NULL OR ah.userid = t.assigneduserid)), t.timecreated) AS assign_timecreated,
                           qp.id AS qaapairid,
                           qp.message AS question,
                           qp.timecreatedquestion,
                           qp.parentid,
                           qp.timecreatedanswer AS qp_timecreatedanswer,
                           c.id AS courseid,
                           c.fullname AS coursefullname,
                           c.shortname AS courseshortname,
                           c.idnumber AS courseidnumber,
                           u.id AS studentid,
                           ctv.id AS ctvid,
                           ctv.email AS ctv_email,
                           a.id AS answerid,
                           a.timecreated AS answer_timecreated,
                           a.status AS answer_status,
                           rev.id AS reviewerid_user,
                           cm.id AS cmid,
                           {$company_name_select}
                           {$studentfields},
                           {$ctvfields},
                           {$revfields}
                      FROM {local_th_qaa_ticket} t
                      JOIN {qaapairs} qp ON qp.id = t.qaapairid
                      JOIN {qaa} q ON q.id = qp.qaaid
                      JOIN {course} c ON c.id = q.course
                      JOIN {modules} md ON md.name = 'qaa'
                      JOIN {course_modules} cm ON cm.module = md.id AND cm.instance = q.id
                      JOIN {user} u ON u.id = qp.studentid
                 LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id AND (t.assigneduserid IS NULL OR a.userid = t.assigneduserid)
                 LEFT JOIN {user} ctv ON ctv.id = COALESCE(t.assigneduserid, a.userid)
                 LEFT JOIN {user} rev ON rev.id = t.reviewerid
                      {$wheresql}
                  ORDER BY t.id DESC";

            $tickets = $DB->get_records_sql(
                $sql,
                $params,
                $page * $perpage,
                $perpage
            );
        }
    }
} else if ((int)$status === STATUS_QAA_STATS) {
    $total = 0;
    $tickets = [];
} else {
    if (
        (int)$status === ticket_service::STATUS_APPROVED
        || (int)$status === ticket_service::STATUS_ANSWER_REJECTED
        || (int)$status === ticket_service::STATUS_QUESTION_REJECTED
    ) {
        if ((int)$status === ticket_service::STATUS_APPROVED) {
            $appr_comp_where1 = '';
            $appr_comp_where2 = '';
            $count1_params = [
                'status_approved' => ticket_service::STATUS_APPROVED,
                'status_rejected' => ticket_service::STATUS_ANSWER_REJECTED,
                'status_recalled' => ticket_service::STATUS_RECALLED,
            ];
            $count2_params = ['status_qp' => 0];
            $sql_params = [
                'status_approved' => ticket_service::STATUS_APPROVED,
                'status_rejected' => ticket_service::STATUS_ANSWER_REJECTED,
                'status_recalled' => ticket_service::STATUS_RECALLED,
            ];

            if ($companyid > 0) {
                $appr_comp_where1 = ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :c_comp1)';
                $appr_comp_where2 = ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :c_comp2)';
                $count1_params['c_comp1'] = $companyid;
                $count2_params['c_comp2'] = $companyid;
                $sql_params['c_comp1'] = $companyid;
                $sql_params['c_comp2'] = $companyid;
            }

            $count1 = $DB->count_records_sql(
                "SELECT COUNT(1)
                   FROM {local_th_qaa_ticket} t
                   JOIN {qaapairs} qp ON qp.id = t.qaapairid
                  WHERE (t.status = :status_approved OR t.status = :status_rejected OR t.status = :status_recalled)
                    AND t.id = (SELECT MAX(id) FROM {local_th_qaa_ticket} t2 WHERE t2.qaapairid = t.qaapairid)
                    {$appr_comp_where1}",
                $count1_params
            );

            $count2 = $DB->count_records_sql(
                "SELECT COUNT(1)
                   FROM {qaapairs} qp
                  WHERE qp.status = :status_qp
                    {$appr_comp_where2}",
                $count2_params
            );

            $total = $count1 + $count2;

            $sql = "
                (SELECT CONCAT('t_', t.id) AS rowuniqueid,
                       qp.id AS id,
                       t.id AS ticketid,
                       t.status AS status,
                       t.assigneduserid,
                       t.reviewerid,
                       qp.id AS qaapairid,
                       qp.message AS question,
                       qp.timecreatedquestion,
                       qp.parentid,
                       qp.timecreatedanswer AS qp_timecreatedanswer,
                       q.course AS courseid,
                       c.fullname AS coursefullname,
                       c.idnumber AS courseidnumber,
                       c.shortname AS courseshortname,
                       uid.data AS student_aum_code,
                       {$company_name_select}
                       {$userfields}
                  FROM {local_th_qaa_ticket} t
                  JOIN {qaapairs} qp ON qp.id = t.qaapairid
                  JOIN {qaa} q ON q.id = qp.qaaid
                  JOIN {course} c ON c.id = q.course
                  JOIN {user} u ON u.id = qp.studentid
             LEFT JOIN {user_info_field} uif ON uif.shortname = 'th_crm_code'
             LEFT JOIN {user_info_data} uid ON uid.fieldid = uif.id AND uid.userid = u.id
                 WHERE (t.status = :status_approved OR t.status = :status_rejected OR t.status = :status_recalled)
                   AND t.id = (SELECT MAX(id) FROM {local_th_qaa_ticket} t2 WHERE t2.qaapairid = t.qaapairid)
                   {$appr_comp_where1})
                UNION ALL
                (SELECT CONCAT('qp_', qp.id) AS rowuniqueid,
                       qp.id AS id,
                       0 AS ticketid,
                       0 AS status,
                       0 AS assigneduserid,
                       0 AS reviewerid,
                       qp.id AS qaapairid,
                       qp.message AS question,
                       qp.timecreatedquestion,
                       qp.parentid,
                       qp.timecreatedanswer AS qp_timecreatedanswer,
                       q.course AS courseid,
                       c.fullname AS coursefullname,
                       c.idnumber AS courseidnumber,
                       c.shortname AS courseshortname,
                       uid.data AS student_aum_code,
                       {$company_name_select}
                       {$userfields}
                  FROM {qaapairs} qp
                  JOIN {qaa} q ON q.id = qp.qaaid
                  JOIN {course} c ON c.id = q.course
                  JOIN {user} u ON u.id = qp.studentid
             LEFT JOIN {user_info_field} uif ON uif.shortname = 'th_crm_code'
             LEFT JOIN {user_info_data} uid ON uid.fieldid = uif.id AND uid.userid = u.id
                 WHERE qp.status = 0
                   {$appr_comp_where2})
              ORDER BY timecreatedquestion DESC";

            $tickets = $DB->get_records_sql(
                $sql,
                $sql_params,
                $page * $perpage,
                $perpage
            );
        } else {
            $where = "t.status = :status";
            $params = [
                'status' => $status,
            ];

            if ($companyid > 0) {
                $where .= ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :c_comp)';
                $params['c_comp'] = $companyid;
            }

            $countsql = "SELECT COUNT(1)
                           FROM {local_th_qaa_ticket} t
                           JOIN {qaapairs} qp ON qp.id = t.qaapairid
                          WHERE {$where}";
            $total = $DB->count_records_sql($countsql, $params);

            $sql = "SELECT t.*,
                           qp.qaaid,
                           qp.message AS question,
                           qp.timecreatedquestion,
                           qp.parentid,
                           qp.timecreatedanswer AS qp_timecreatedanswer,
                           a.content AS answer,
                           a.contentformat,
                           q.course AS courseid,
                           c.fullname AS coursefullname,
                           c.idnumber AS courseidnumber,
                           c.shortname AS courseshortname,
                           uid.data AS student_aum_code,
                           {$company_name_select}
                           {$userfields}
                      FROM {local_th_qaa_ticket} t
                      JOIN {qaapairs} qp ON qp.id = t.qaapairid
                 LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id
                      JOIN {qaa} q ON q.id = qp.qaaid
                      JOIN {course} c ON c.id = q.course
                      JOIN {user} u ON u.id = qp.studentid
                 LEFT JOIN {user_info_field} uif ON uif.shortname = 'th_crm_code'
                 LEFT JOIN {user_info_data} uid ON uid.fieldid = uif.id AND uid.userid = u.id
                     WHERE {$where}
                  ORDER BY t.id DESC";

            $tickets = $DB->get_records_sql(
                $sql,
                $params,
                $page * $perpage,
                $perpage
            );
        }
    } else {
        $where = "t.status = :status";
        $params = [
            'status' => $status,
        ];

        if ($companyid > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM {company_users} cu WHERE cu.userid = qp.studentid AND cu.companyid = :c_comp)';
            $params['c_comp'] = $companyid;
        }

        $countsql = "SELECT COUNT(1)
                       FROM {local_th_qaa_ticket} t
                       JOIN {qaapairs} qp ON qp.id = t.qaapairid
                      WHERE {$where}";
        $total = $DB->count_records_sql($countsql, $params);

        $sql = "SELECT t.*,
                       qp.qaaid,
                       qp.message AS question,
                       a.content AS answer,
                       a.contentformat,
                       q.course AS courseid,
                       c.fullname AS coursefullname,
                       {$company_name_select}
                       {$userfields}
                  FROM {local_th_qaa_ticket} t
                  JOIN {qaapairs} qp ON qp.id = t.qaapairid
             LEFT JOIN {local_th_qaa_answer} a ON a.ticketid = t.id
                  JOIN {qaa} q ON q.id = qp.qaaid
                  JOIN {course} c ON c.id = q.course
                  JOIN {user} u ON u.id = qp.studentid
                 WHERE {$where}
              ORDER BY t.id DESC";

        $tickets = $DB->get_records_sql(
            $sql,
            $params,
            $page * $perpage,
            $perpage
        );
    }
}

// Old export handler moved before header.

if (!$tickets) {
    if ((int)$status === STATUS_QAA_MANAGE && !$is_filtered) {
        echo $OUTPUT->notification(get_string('select_filter_info', 'local_th_qaa_manager'), 'info');
    } else {
        echo $OUTPUT->notification(get_string('nothingtodisplay', 'local_th_qaa_manager'), 'notifymessage');
    }
    echo $OUTPUT->footer();
    exit;
}

$showbulkcolumn = in_array((int)$status, [
    ticket_service::STATUS_NEW,
    ticket_service::STATUS_APPROVED,
    ticket_service::STATUS_ASSIGNED,
    ticket_service::STATUS_ANSWER_REJECTED,
    STATUS_QAA_MANAGE,
], true);

$showactionscolumn = in_array((int)$status, [
    ticket_service::STATUS_NEW,
    ticket_service::STATUS_APPROVED,
    ticket_service::STATUS_ASSIGNED,
    ticket_service::STATUS_ANSWERED,
    ticket_service::STATUS_ANSWER_REJECTED,
    ticket_service::STATUS_QUESTION_REJECTED,
], true);

// Only show the answer column on Pending answer approval and Completed tabs.
$showanswercolumn = in_array((int)$status, [
    ticket_service::STATUS_ANSWERED,
    ticket_service::STATUS_CLOSED,
], true);

$table = new html_table();
$table->attributes = [
    'class' => 'th_qaa_manager display generaltable text-left w-100',
    'border' => '1',
];

$table->head = [];

if ((int)$status === STATUS_QAA_MANAGE) {
    $table->head[] = html_writer::checkbox('selectall_dummy', 1, false, '', [
        'onclick' => "event.stopPropagation(); document.querySelectorAll('.qaa-bulk-checkbox').forEach(function(cb) { cb.checked = event.target.checked; });",
    ]);
    $table->head[] = get_string('col_no', 'local_th_qaa_manager');
    $table->head[] = get_string('col_ctv', 'local_th_qaa_manager');
    $table->head[] = get_string('col_course_name', 'local_th_qaa_manager');
    $table->head[] = get_string('col_course_code', 'local_th_qaa_manager');
    $qcell = new html_table_cell(get_string('col_question_link', 'local_th_qaa_manager'));
    $qcell->attributes['class'] = 'col-question-link';
    $table->head[] = $qcell;
    $table->head[] = get_string('col_student', 'local_th_qaa_manager');
    if ($is_iomad) {
        $table->head[] = get_string('col_company', 'local_th_qaa_manager');
    }
    $table->head[] = get_string('col_date_question', 'local_th_qaa_manager');
    $table->head[] = get_string('col_date_assign', 'local_th_qaa_manager');
    $table->head[] = get_string('col_date_answer', 'local_th_qaa_manager');
    $table->head[] = get_string('col_approval', 'local_th_qaa_manager');
    $table->head[] = get_string('col_approver', 'local_th_qaa_manager');
    $table->head[] = get_string('actions');
} else {
    if ($showbulkcolumn) {
        $table->head[] = html_writer::checkbox('selectall_dummy', 1, false, '', [
            'onclick' => "event.stopPropagation(); document.querySelectorAll('.qaa-bulk-checkbox').forEach(function(cb) { cb.checked = event.target.checked; });",
        ]);
    }

    if (
        (int)$status === ticket_service::STATUS_APPROVED
        || (int)$status === ticket_service::STATUS_ANSWER_REJECTED
        || (int)$status === ticket_service::STATUS_QUESTION_REJECTED
    ) {
        $table->head[] = get_string('col_no', 'local_th_qaa_manager');
        $table->head[] = get_string('course', 'local_th_qaa_manager');
        $table->head[] = get_string('col_course_code', 'local_th_qaa_manager');
        $qcell = new html_table_cell(get_string('col_question_link', 'local_th_qaa_manager'));
        $qcell->attributes['class'] = 'col-question-link';
        $table->head[] = $qcell;
        $table->head[] = get_string('student', 'local_th_qaa_manager');
        if ($is_iomad) {
            $table->head[] = get_string('col_company', 'local_th_qaa_manager');
        }
        $table->head[] = get_string('col_student_username', 'local_th_qaa_manager');
        $table->head[] = get_string('col_date_question', 'local_th_qaa_manager');
        if ((int)$status === ticket_service::STATUS_QUESTION_REJECTED) {
            $table->head[] = get_string('rejectreason', 'local_th_qaa_manager');
        }
        if ((int)$status !== ticket_service::STATUS_APPROVED) {
            $table->head[] = get_string('status', 'local_th_qaa_manager');
        }
        $table->head[] = get_string('actions', 'local_th_qaa_manager');
    } else {
        $table->head[] = get_string('course', 'local_th_qaa_manager');
        $table->head[] = get_string('student', 'local_th_qaa_manager');
        if ($is_iomad) {
            $table->head[] = get_string('col_company', 'local_th_qaa_manager');
        }
        $qcell = new html_table_cell(get_string('question', 'local_th_qaa_manager'));
        $qcell->attributes['class'] = 'col-question-link';
        $table->head[] = $qcell;

        if ($showanswercolumn) {
            $table->head[] = get_string('answercolumn', 'local_th_qaa_manager');
        }

        $table->head[] = get_string('status', 'local_th_qaa_manager');

        if ($showactionscolumn) {
            $table->head[] = get_string('actions', 'local_th_qaa_manager');
        }
    }
}

if ((int)$status === STATUS_QAA_MANAGE) {
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

    $i = $page * $perpage + 1;
    foreach ($tickets as $ticket) {
        $row = [];

        $ticketstatus = (int)$ticket->ticketstatus;

        // 1. Checkbox
        if ($ticketstatus === ticket_service::STATUS_ANSWERED) {
            $row[] = html_writer::checkbox('bulkticketids[]', $ticket->ticketid, false, '', [
                'class' => 'qaa-bulk-checkbox',
                'form' => 'qaa-bulk-form',
            ]);
        } else {
            $row[] = '';
        }
        $row[] = $i++;

        // 3. Tên CTV
        $ctvuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'ctv_');
        $ctvname = (!empty($ctvuser->firstname) || !empty($ctvuser->lastname)) ? fullname($ctvuser) : '';
        if ($ctvname !== '') {
            $row[] = s($ctvname);
        } else if (!empty($ticket->assigneduserid)) {
            $u = $DB->get_record('user', ['id' => $ticket->assigneduserid]);
            $row[] = $u ? s(fullname($u)) : '-';
        } else if (!empty($ticket->ctvid)) {
            $u = $DB->get_record('user', ['id' => $ticket->ctvid]);
            $row[] = $u ? s(fullname($u)) : '-';
        } else {
            $row[] = '-';
        }

        // 4. Tên khóa học
        $courseurl = new moodle_url('/course/view.php', ['id' => $ticket->courseid]);
        $row[] = html_writer::link($courseurl, format_string($ticket->coursefullname));

        // 5. Mã khóa học
        $row[] = s($ticket->courseshortname ?? '');

        // 6. Link câu hỏi
        $detailparams = [
            'id' => $ticket->ticketid,
            'status' => $status,
            'page' => $page,
        ];
        if ((int)$status === STATUS_QAA_MANAGE) {
            if ($filter_ctv) {
                foreach ($filter_ctv as $k => $v) {
                    $detailparams['filter_ctv[' . $k . ']'] = $v;
                }
            }
            if ($filter_course) {
                foreach ($filter_course as $k => $v) {
                    $detailparams['filter_course[' . $k . ']'] = $v;
                }
            }
            $detailparams['filter_status'] = $filter_status;
            if ($filter_startdate_array) {
                foreach ($filter_startdate_array as $k => $v) {
                    $detailparams['filter_startdate[' . $k . ']'] = $v;
                }
            }
            if ($filter_enddate_array) {
                foreach ($filter_enddate_array as $k => $v) {
                    $detailparams['filter_enddate[' . $k . ']'] = $v;
                }
            }
            $detailparams['submitbutton'] = '1';
        }
        $detailurl = new moodle_url('/local/th_qaa_manager/view.php', $detailparams);
        $modqaaurl = new moodle_url('/mod/qaa/view.php', [
            'id' => $ticket->cmid,
            'questionid' => $ticket->qaapairid,
        ]);
        $questiontext = shorten_text(
            trim(html_to_text($ticket->question)),
            120
        );
        $qlinkcell = new html_table_cell(html_writer::link($modqaaurl, s($questiontext), ['target' => '_blank']));
        $qlinkcell->attributes['class'] = 'col-question-link';
        $row[] = $qlinkcell;

        // 7. Học viên
        $studentuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'student_');
        $row[] = s(fullname($studentuser));

        // 7b. Đơn vị
        if ($is_iomad) {
            $row[] = s($ticket->student_company_name ?? '-');
        }

        // 8. Ngày gửi câu hỏi
        if (!empty($ticket->parentid) && !empty($ticket->qp_timecreatedanswer)) {
            $row[] = date('d/m/Y H:i', $ticket->qp_timecreatedanswer);
        } else {
            $row[] = date('d/m/Y H:i', $ticket->timecreatedquestion);
        }

        // 8b. Thời gian phân công
        if ((int)$ticketstatus === ticket_service::STATUS_QUESTION_REJECTED) {
            $row[] = '-';
        } else if (!empty($ticket->timeassigned)) {
            $row[] = date('d/m/Y H:i', $ticket->timeassigned);
        } else if (!empty($ticket->assign_timecreated) && (!empty($ticket->assigneduserid) || !empty($ticket->ctvid))) {
            $row[] = date('d/m/Y H:i', $ticket->assign_timecreated);
        } else {
            $row[] = '-';
        }

        // 9. Ngày trả lời
        if ((int)$ticketstatus === ticket_service::STATUS_RECALLED || !empty($ticket->historyid)) {
            $row[] = '-';
        } else if (!empty($ticket->answer_timecreated)) {
            $row[] = date('d/m/Y', $ticket->answer_timecreated);
        } else {
            $row[] = '-';
        }

        // 10. Phê duyệt
        $viewlink = html_writer::link($detailurl, get_string('view', 'local_th_qaa_manager'));
        $badge = '';

        if ((int)$ticketstatus === ticket_service::STATUS_RECALLED || !empty($ticket->historyid) || $filter_status === 'recalled') {
            $badge = html_writer::span(get_string('status_recalled', 'local_th_qaa_manager'), 'badge badge-secondary');
        } else if ($ticketstatus === ticket_service::STATUS_CLOSED) {
            $badge = html_writer::span(get_string('status_approved_short', 'local_th_qaa_manager'), 'badge badge-success');
        } else if ($ticketstatus === ticket_service::STATUS_QUESTION_REJECTED) {
            $badge = html_writer::span(get_string('status_question_rejected', 'local_th_qaa_manager'), 'badge badge-danger');
        } else if ($ticketstatus === ticket_service::STATUS_ANSWER_REJECTED) {
            $badge = html_writer::span(get_string('approval_status_rejected', 'local_th_qaa_manager'), 'badge badge-danger');
        } else if ($ticketstatus === ticket_service::STATUS_ANSWERED) {
            $badge = html_writer::span(get_string('status_unapproved_short', 'local_th_qaa_manager'), 'badge badge-warning text-dark');
        } else if ($ticketstatus === ticket_service::STATUS_ASSIGNED) {
            $badge = html_writer::span(get_string('answer_status_unanswered', 'local_th_qaa_manager'), 'badge badge-warning text-dark');
        }

        if ($badge !== '') {
            $approvaltext = html_writer::div($viewlink . $badge, 'd-flex flex-column align-items-start text-left');
        } else {
            $approvaltext = html_writer::div($viewlink, 'text-left');
        }
        $row[] = $approvaltext;

        // 11. Người phê duyệt
        if ((int)$ticket->ticketstatus === ticket_service::STATUS_CLOSED && !empty($ticket->reviewerid)) {
            $revuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'rev_');
            $row[] = s(fullname($revuser));
        } else {
            $row[] = '';
        }

        // 12. Thao tác
        $actions = [];
        if ($ticketstatus === ticket_service::STATUS_ANSWERED) {
            $approvelink = html_writer::link(
                new moodle_url($baseurl, [
                    'action' => 'approvea',
                    'ticketid' => $ticket->ticketid,
                    'sesskey' => sesskey(),
                ]),
                get_string('approve', 'local_th_qaa_manager'),
                [
                    'onclick' => "return confirm('" . s(get_string('confirmapproveanswer', 'local_th_qaa_manager')) . "');",
                ]
            );

            $rejectlink = html_writer::link(
                new moodle_url($baseurl, [
                    'action' => 'rejecta',
                    'ticketid' => $ticket->ticketid,
                    'sesskey' => sesskey(),
                ]),
                get_string('rejectquestion', 'local_th_qaa_manager'),
                [
                    'onclick' => "return confirm('" . s(get_string('confirmrejectanswer', 'local_th_qaa_manager')) . "');",
                ]
            );

            $actions[] = $approvelink;
            $actions[] = $rejectlink;
        }

        if ($ticketstatus === ticket_service::STATUS_QUESTION_REJECTED) {
            $cancelurl = new moodle_url($baseurl, [
                'action' => 'cancelrejectq',
                'ticketid' => $ticket->ticketid,
                'sesskey' => sesskey(),
            ]);
            $actions[] = html_writer::link(
                $cancelurl,
                get_string('cancelrejectq', 'local_th_qaa_manager'),
                [
                    'onclick' => "return confirm('" . s(get_string('confirmcancelrejectq', 'local_th_qaa_manager')) . "');",
                ]
            );
        }

        if (!empty($ticket->assigneduserid) && $ticketstatus === ticket_service::STATUS_ASSIGNED) {
            $deleteurl = new moodle_url($baseurl, [
                'action' => 'deleteassign',
                'ticketid' => $ticket->ticketid,
                'sesskey' => sesskey(),
            ]);
            $actions[] = html_writer::link(
                $deleteurl,
                get_string('col_delete_assignment', 'local_th_qaa_manager'),
                [
                    'onclick' => "return confirm('" . s(get_string('delete_assignment_confirm', 'local_th_qaa_manager')) . "');",
                ]
            );
        }
        $row[] = html_writer::div(implode(' | ', $actions), 'text-left text-nowrap');

        $table->data[] = $row;
    }
} else {
    $i = $page * $perpage + 1;
    foreach ($tickets as $ticket) {
        $studentname = fullname($ticket);
        $questiontext = shorten_text(
            trim(html_to_text($ticket->question)),
            120
        );

        $answertext = '';

        if ($showanswercolumn && !empty($ticket->answer)) {

            $answerhasimage = preg_match('/<img[^>]*>/i', $ticket->answer);

            if ($answerhasimage) {
                $answertext = get_string(
                    'answercontainimage',
                    'local_th_qaa_manager'
                );
            } else {
                $answertext = shorten_text(
                    trim(html_to_text($ticket->answer)),
                    120
                );
            }
        }

        $actions = [];

        if ((int)$status === ticket_service::STATUS_APPROVED) {
            $assignurl = new moodle_url('/local/th_qaa_manager/manage.php', [
                'action' => 'assign',
                'sesskey' => sesskey(),
                'status' => $status,
                'page' => $page,
            ]);

            $formhtml = html_writer::start_tag('form', [
                'method' => 'post',
                'action' => $assignurl->out(false),
                'class' => 'd-flex flex-column align-items-start justify-content-start m-0 p-0 text-left',
            ]);

            $formhtml .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'sesskey',
                'value' => sesskey(),
            ]);

            $formhtml .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'action',
                'value' => 'assign',
            ]);

            if (!empty($ticket->ticketid)) {
                $formhtml .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'ticketid',
                    'value' => $ticket->ticketid,
                ]);
            } else {
                $formhtml .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'qaapairid',
                    'value' => $ticket->qaapairid,
                ]);
            }

            // Top row: Phân công & Từ chối buttons (LEFT ALIGNED)
            $toprow = html_writer::start_tag('div', [
                'class' => 'd-flex flex-row align-items-center justify-content-start mb-1 m-0 p-0',
                'style' => 'gap: 4px;',
            ]);

            $assignsubmitattrs = [
                'type' => 'submit',
                'value' => get_string('assign', 'local_th_qaa_manager'),
                'class' => 'btn btn-sm btn-success',
                'style' => 'min-width: 82px; margin: 0;',
                'onclick' => "return confirm('" . s(get_string('confirmassignquestion', 'local_th_qaa_manager')) . "');",
            ];

            if (empty($ctvoptions)) {
                $assignsubmitattrs['disabled'] = 'disabled';
            }

            $toprow .= html_writer::empty_tag('input', $assignsubmitattrs);

            $rejectparams = ['qaapairid' => $ticket->qaapairid];
            $toprow .= html_writer::link(
                new moodle_url('/local/th_qaa_manager/reject_question.php', $rejectparams),
                get_string('rejectquestion', 'local_th_qaa_manager'),
                [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'style' => 'min-width: 82px; margin: 0;',
                ]
            );

            $toprow .= html_writer::end_tag('div');

            // Bottom row: Chọn CTV select dropdown (LEFT ALIGNED, width: 168px = 82 + 4 + 82)
            $bottomrow = html_writer::start_tag('div', ['class' => 'd-flex flex-row align-items-center justify-content-start m-0 p-0']);

            $selectedassignee = 0;
            $bottomrow .= html_writer::select(
                $ctvoptions,
                'assignuserid',
                $selectedassignee,
                ['' => get_string('selectassignee', 'local_th_qaa_manager')],
                [
                    'class' => 'form-control form-control-sm',
                    'style' => 'width:168px; margin: 0;',
                ]
            );

            $bottomrow .= html_writer::end_tag('div');

            $formhtml .= $toprow . $bottomrow;
            $formhtml .= html_writer::end_tag('form');
            $actions[] = $formhtml;
        } else if (!empty($ticket->ticketid) && in_array((int)$status, [
            ticket_service::STATUS_ASSIGNED,
            ticket_service::STATUS_ANSWER_REJECTED,
        ], true)) {
            $assignurl = new moodle_url('/local/th_qaa_manager/manage.php', [
                'action' => 'assign',
                'ticketid' => $ticket->ticketid,
                'sesskey' => sesskey(),
                'status' => $status,
                'page' => $page,
            ]);

            $formhtml = html_writer::start_tag('form', [
                'method' => 'post',
                'action' => $assignurl->out(false),
                'class' => 'd-flex flex-column align-items-start justify-content-start m-0 p-0',
                'style' => 'text-align: left;',
            ]);

            $formhtml .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'sesskey',
                'value' => sesskey(),
            ]);

            $formhtml .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'action',
                'value' => 'assign',
            ]);

            $formhtml .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'ticketid',
                'value' => $ticket->ticketid,
            ]);

            // Top row: Phân công button (LEFT ALIGNED)
            $toprow = html_writer::start_tag('div', ['class' => 'd-flex flex-row align-items-center justify-content-start mb-1 m-0 p-0']);

            $assignsubmitattrs = [
                'type' => 'submit',
                'value' => get_string('assign', 'local_th_qaa_manager'),
                'class' => 'btn btn-sm btn-success',
                'style' => 'width: 168px; margin: 0;',
                'onclick' => "return confirm('" . s(get_string('confirmassignquestion', 'local_th_qaa_manager')) . "');",
            ];

            if (empty($ctvoptions)) {
                $assignsubmitattrs['disabled'] = 'disabled';
            }

            $toprow .= html_writer::empty_tag('input', $assignsubmitattrs);
            $toprow .= html_writer::end_tag('div');

            // Bottom row: Chọn CTV select dropdown (LEFT ALIGNED)
            $bottomrow = html_writer::start_tag('div', ['class' => 'd-flex flex-row align-items-center justify-content-start m-0 p-0']);

            $selectedassignee = 0;
            $bottomrow .= html_writer::select(
                $ctvoptions,
                'assignuserid',
                $selectedassignee,
                ['' => get_string('selectassignee', 'local_th_qaa_manager')],
                [
                    'class' => 'form-control form-control-sm',
                    'style' => 'width:168px; margin: 0;',
                ]
            );

            $bottomrow .= html_writer::end_tag('div');

            $formhtml .= $toprow . $bottomrow;
            $formhtml .= html_writer::end_tag('form');
            $actions[] = $formhtml;
        }

        if ((int)$status === ticket_service::STATUS_ANSWERED) {
            $actions[] = html_writer::link(
                new moodle_url('/local/th_qaa_manager/view.php', [
                    'id' => $ticket->id,
                    'review' => 1,
                    'status' => $status,
                    'page' => $page,
                ]),
                get_string('questiondetail', 'local_th_qaa_manager')
            );

            $actions[] = html_writer::link(
                new moodle_url('/local/th_qaa_manager/manage.php', [
                    'action' => 'approvea',
                    'ticketid' => $ticket->id,
                    'sesskey' => sesskey(),
                    'status' => $status,
                    'page' => $page,
                ]),
                get_string('approveanswer', 'local_th_qaa_manager'),
                [
                    'onclick' => "return confirm('" . s(get_string('confirmapproveanswer', 'local_th_qaa_manager')) . "');",
                ]
            );

            $actions[] = html_writer::link(
                new moodle_url('/local/th_qaa_manager/manage.php', [
                    'action' => 'rejecta',
                    'ticketid' => $ticket->id,
                    'sesskey' => sesskey(),
                    'status' => $status,
                    'page' => $page,
                ]),
                get_string('rejectanswer', 'local_th_qaa_manager'),
                [
                    'onclick' => "return confirm('" . s(get_string('confirmrejectanswer', 'local_th_qaa_manager')) . "');",
                ]
            );
        }

        if ((int)$status === ticket_service::STATUS_QUESTION_REJECTED) {
            $actions[] = html_writer::link(
                new moodle_url('/local/th_qaa_manager/manage.php', [
                    'action' => 'cancelrejectq',
                    'ticketid' => $ticket->id,
                    'sesskey' => sesskey(),
                    'status' => $status,
                    'page' => $page,
                ]),
                get_string('cancelrejectq', 'local_th_qaa_manager'),
                [
                    'class' => 'text-danger font-weight-bold',
                    'onclick' => "return confirm('" . s(get_string('confirmcancelrejectq', 'local_th_qaa_manager')) . "');",
                ]
            );
        }

        $row = [];

        if ($showbulkcolumn) {
            if ((int)$status === ticket_service::STATUS_APPROVED && empty($ticket->ticketid)) {
                $row[] = html_writer::checkbox('bulkqaapairids[]', $ticket->qaapairid, false, '', [
                    'class' => 'qaa-bulk-checkbox',
                    'form' => 'qaa-bulk-form',
                ]);
            } else {
                $row[] = html_writer::checkbox('bulkticketids[]', ((int)$status === ticket_service::STATUS_APPROVED ? $ticket->ticketid : $ticket->id), false, '', [
                    'class' => 'qaa-bulk-checkbox',
                    'form' => 'qaa-bulk-form',
                ]);
            }
        }

        if ((int)$status === STATUS_QAA_MANAGE && $filter_status === 'recalled') {
            $row[] = ''; // Dummy checkbox column

            $row[] = $i++;

            $ctvuser = local_th_qaa_manager_get_user_from_prefix($ticket, 'ctv_');
            $row[] = s(fullname($ctvuser));

            $courseurl = new moodle_url('/course/view.php', ['id' => $ticket->courseid]);
            $row[] = html_writer::link($courseurl, format_string($ticket->coursefullname));

            $row[] = s($ticket->courseshortname ?? '');

            $detailurl = new moodle_url('/local/th_qaa_manager/view.php', [
                'id' => $ticket->ticketid,
                'status' => $status,
                'page' => $page,
            ]);

            $qlinkcell = new html_table_cell(html_writer::link($detailurl, s($questiontext)));
            $qlinkcell->attributes['class'] = 'col-question-link';
            $row[] = $qlinkcell;

            $row[] = s($studentname);

            $row[] = !empty($ticket->timeassigned) ? date('d/m/Y H:i', $ticket->timeassigned) : '-';
            $row[] = !empty($ticket->timerecalled) ? date('d/m/Y H:i', $ticket->timerecalled) : '-';

            $recall_label = ((int)$ticket->recalltype === 1) ? get_string('col_delete_assignment', 'local_th_qaa_manager') : get_string('stats_auto_recalled', 'local_th_qaa_manager');
            $row[] = html_writer::span($recall_label, 'badge badge-secondary');

            $row[] = '-';
            $row[] = '-';

            $table->data[] = $row;
            continue;
        }

        if (
            in_array((int)$status, [
                ticket_service::STATUS_APPROVED,
                ticket_service::STATUS_ASSIGNED,
                ticket_service::STATUS_ANSWER_REJECTED,
                ticket_service::STATUS_QUESTION_REJECTED
            ], true)
        ) {
            $row[] = $i++;

            $courseurl = new moodle_url('/course/view.php', [
                'id' => $ticket->courseid,
            ]);

            $row[] = html_writer::link(
                $courseurl,
                format_string($ticket->coursefullname)
            );

            $row[] = s($ticket->courseshortname ?? '');

            if ((int)$status === ticket_service::STATUS_APPROVED && empty($ticket->ticketid)) {
                $vqparams = ['qaapairid' => $ticket->qaapairid];
                $detailurl = new moodle_url('/local/th_qaa_manager/view_question.php', $vqparams);
            } else {
                $detailparams = [
                    'id' => ((int)$status === ticket_service::STATUS_APPROVED ? $ticket->ticketid : $ticket->id),
                    'status' => $status,
                    'page' => $page,
                ];
                $detailurl = new moodle_url('/local/th_qaa_manager/view.php', $detailparams);
            }

            $qlinkcell = new html_table_cell(html_writer::link(
                $detailurl,
                s($questiontext)
            ));
            $qlinkcell->attributes['class'] = 'col-question-link';
            $row[] = $qlinkcell;

            $row[] = s($studentname);

            if ($is_iomad) {
                $row[] = s($ticket->student_company_name ?? '-');
            }

            $row[] = s($ticket->student_aum_code ?? '');

            if (!empty($ticket->parentid) && !empty($ticket->qp_timecreatedanswer)) {
                $row[] = date('d/m/Y', $ticket->qp_timecreatedanswer);
            } else {
                $row[] = date('d/m/Y', $ticket->timecreatedquestion ?? $ticket->timecreated);
            }

            if ((int)$status === ticket_service::STATUS_QUESTION_REJECTED) {
                $row[] = s($ticket->rejectreason ?? '');
            }

            if ((int)$status !== ticket_service::STATUS_APPROVED) {
                $row[] = ticket_service::status_label((int)$ticket->status);
            }
        } else {
            $courseurl = new moodle_url('/course/view.php', [
                'id' => $ticket->courseid,
            ]);

            $row[] = html_writer::link(
                $courseurl,
                format_string($ticket->coursefullname)
            );

            $row[] = s($studentname);

            if ($is_iomad) {
                $row[] = s($ticket->student_company_name ?? '-');
            }

            if ((int)$status === ticket_service::STATUS_NEW) {
                $vqparams = ['qaapairid' => $ticket->qaapairid];
                $detailurl = new moodle_url('/local/th_qaa_manager/view_question.php', $vqparams);

                $qlinkcell = new html_table_cell(html_writer::link(
                    $detailurl,
                    s($questiontext)
                ));
                $qlinkcell->attributes['class'] = 'col-question-link';
                $row[] = $qlinkcell;

                $row[] = ticket_service::status_label(ticket_service::STATUS_NEW);
            } else {
                $detailparams = [
                    'id' => $ticket->id,
                    'status' => $status,
                    'page' => $page,
                ];
                $detailurl = new moodle_url('/local/th_qaa_manager/view.php', $detailparams);

                $qlinkcell = new html_table_cell(html_writer::link(
                    $detailurl,
                    s($questiontext)
                ));
                $qlinkcell->attributes['class'] = 'col-question-link';
                $row[] = $qlinkcell;

                if ($showanswercolumn) {
                    $answerpreview = $answertext !== ''
                        ? $answertext
                        : '-';

                    $row[] = html_writer::link(
                        $detailurl,
                        s($answerpreview)
                    );
                }

                $row[] = ticket_service::status_label((int)$ticket->status);
            }
        }

        if ($showactionscolumn) {
            $actionscell = new html_table_cell(implode('<br>', $actions));
            $actionscell->attributes['class'] = 'th-qaa-actions-cell text-left';
            $row[] = $actionscell;
        }

        $table->data[] = $row;
    }
}

$bulkbuttons = [];

if ((int)$status === STATUS_QAA_MANAGE) {
    $bulkbuttons[] = html_writer::tag(
        'button',
        get_string('bulkapproveanswer', 'local_th_qaa_manager'),
        [
            'type' => 'submit',
            'name' => 'bulkaction',
            'value' => 'bulkapprovea',
            'class' => 'btn btn-success mr-2',
            'onclick' => "return confirm('" . s(get_string('confirmbulkapproveanswer', 'local_th_qaa_manager')) . "');",
        ]
    );
}

if (in_array((int)$status, [
    ticket_service::STATUS_APPROVED,
    ticket_service::STATUS_ASSIGNED,
    ticket_service::STATUS_ANSWER_REJECTED,
], true)) {
    $bulkbuttons[] = html_writer::select(
        $ctvoptions,
        'bulkassignuserid',
        0,
        ['' => get_string('selectassignee', 'local_th_qaa_manager')],
        [
            'class' => 'form-control mr-2',
            'style' => 'width:300px;',
        ]
    );

    $bulkassignattrs = [
        'type' => 'submit',
        'name' => 'bulkaction',
        'value' => 'bulkassign',
        'class' => 'btn btn-secondary mr-2',
        'onclick' => "return confirm('" . s(get_string('confirmbulkassign', 'local_th_qaa_manager')) . "');",
    ];

    if (empty($ctvoptions)) {
        $bulkassignattrs['disabled'] = 'disabled';
    }

    $bulkbuttons[] = html_writer::tag(
        'button',
        get_string('bulkassign', 'local_th_qaa_manager'),
        $bulkassignattrs
    );
}

if ($showbulkcolumn) {
    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => $baseurl->out(false),
        'id' => 'qaa-bulk-form',
    ]);

    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey(),
    ]);

    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'status',
        'value' => $status,
    ]);

    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'page',
        'value' => $page,
    ]);

    // Output all baseurlparams as hidden fields so they are preserved on POST!
    foreach ($baseurlparams as $key => $value) {
        if ($key === 'sesskey' || $key === 'status' || $key === 'page') {
            continue;
        }
        echo html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $key,
            'value' => $value,
        ]);
    }

    // In Phân công CTV tab (or non-QAA_MANAGE tabs), show bulk buttons ABOVE table.
    if ((int)$status !== STATUS_QAA_MANAGE && $bulkbuttons) {
        echo html_writer::div(
            implode('', $bulkbuttons),
            'mb-3 form-inline'
        );
    }
}

echo html_writer::table($table);

if ($showbulkcolumn) {
    // In Quản lý hỏi đáp tab (STATUS_QAA_MANAGE), show bulk buttons BELOW table.
    if ((int)$status === STATUS_QAA_MANAGE && $bulkbuttons) {
        echo html_writer::div(
            implode('', $bulkbuttons),
            'mt-3 mb-3 form-inline'
        );
    }

    echo html_writer::end_tag('form');
}

echo html_writer::start_div('d-flex flex-wrap align-items-center justify-content-between my-3');

echo html_writer::div(
    $OUTPUT->paging_bar(
        $total,
        $page,
        $perpage,
        $baseurl
    ),
    'mb-2 mb-md-0'
);

$perpageoptions = [10, 25, 50, 100, 200, 500];
$optionshtml = '';
foreach ($perpageoptions as $option) {
    $url = new moodle_url('/local/th_qaa_manager/manage.php', array_merge($baseurlparams, ['perpage' => $option, 'page' => 0]));
    $selected = ((int)$perpage === (int)$option) ? 'selected="selected"' : '';
    $optionshtml .= '<option value="' . s($url->out(false)) . '" ' . $selected . '>' . $option . '</option>';
}

$exporturl = new moodle_url('/local/th_qaa_manager/manage.php', array_merge($baseurlparams, ['download' => 'excel']));
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
