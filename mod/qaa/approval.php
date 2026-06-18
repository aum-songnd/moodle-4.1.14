<?php
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/qaa/classes/form/approval_form.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/approval_approve_action.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/approval_reject_action.php');

$id         = required_param('id', PARAM_INT);          // cmid
$action     = optional_param('action', '', PARAM_ALPHA);// '', 'approve', 'reject'
$questionid = optional_param('questionid', 0, PARAM_INT);

$cm       = get_coursemodule_from_id('qaa', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id'=>$cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('qaa', ['id'=>$cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/qaa:approval', $context);

$PAGE->set_url(new moodle_url('/mod/qaa/approval.php', ['id'=>$cm->id]));
$PAGE->set_title(get_string('approval', 'mod_qaa'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('modulename', 'mod_qaa'), new moodle_url('/mod/qaa/view.php', ['id'=>$cm->id]));
$PAGE->navbar->add(get_string('approval', 'mod_qaa'));

echo $OUTPUT->header();

/** ========= CASE: mở form approve/reject cho 1 câu ========= */
if ($questionid && in_array($action, ['approve', 'reject'], true)) {
    $root = $DB->get_record('qaapairs', ['id'=>$questionid, 'qaaid'=>$cm->instance], '*', MUST_EXIST);

    $mform = new \mod_qaa\form\approval_form(
        new moodle_url('/mod/qaa/approval.php', ['id'=>$cm->id, 'action'=>$action, 'questionid'=>$questionid]),
        ['mode'=>$action, 'context'=>$context, 'cmid'=>$cm->id, 'root'=>$root]
    );

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/mod/qaa/approval.php', ['id'=>$cm->id]));
    } else if ($data = $mform->get_data()) {
        require_sesskey();
        if ($action === 'approve') {
            $act = new \mod_qaa\local\actions\approval_approve_action($DB);
            $ret = $act->execute($data, $cm, $context);
            redirect($ret);
        } else {
            $act = new \mod_qaa\local\actions\approval_reject_action($DB);
            $ret = $act->execute($data, $cm, $context);
            redirect($ret);
        }
    }

    echo $OUTPUT->heading(get_string($action === 'approve' ? 'approvequestion' : 'rejectquestion', 'mod_qaa') . " #{$root->id}", 3);

    // Show root question HTML
    $raw = file_rewrite_pluginfile_urls((string)$root->message, 'pluginfile.php', $context->id, 'mod_qaa', 'message', $root->id);
    echo html_writer::div(format_text($raw, FORMAT_HTML, ['context'=>$context]), 'qaa-approval-root mb-3');

    $mform->display();

    echo $OUTPUT->footer();
    exit;
}

/** ========= CASE: danh sách pending ========= */
echo $OUTPUT->heading(get_string('approvalqueue', 'mod_qaa'), 3);

// ====== PHÂN TRANG ======
$perpage = 50;
$page    = optional_param('page', 0, PARAM_INT); // Trang hiện tại (0-based).
$baseurl = new moodle_url('/mod/qaa/approval.php', ['id' => $cm->id]);

// Tổng số bản ghi pending.
$totalpending = $DB->count_records('qaapairs', [
    'qaaid'  => $cm->instance,
    'status' => 0,
]);

if (!$totalpending) {
    echo $OUTPUT->notification(get_string('nothingtodisplay', 'moodle'), 'notifymessage');
    echo html_writer::link(new moodle_url('/mod/qaa/view.php', ['id'=>$cm->id]),
                          get_string('back'), ['class'=>'btn btn-secondary mr-2 qaa-back-list']);
    echo $OUTPUT->footer();
    exit;
}

// SQL lấy dữ liệu theo trang.
$sql = "SELECT r.*
          FROM {qaapairs} r
         WHERE r.qaaid = :qaaid
           AND r.status = 0
      ORDER BY r.id DESC";

$pending = $DB->get_records_sql(
    $sql,
    ['qaaid' => $cm->instance],
    $page * $perpage, // limitfrom
    $perpage          // limitnum
);

$table = new html_table();
$table->attributes['class'] = 'generaltable qaa-list w-100';
$table->head  = [
    get_string('discussion', 'mod_qaa'),
    get_string('creator', 'mod_qaa'),
    get_string('timecreated', 'mod_qaa'),
    get_string('actions', 'core')
];
$table->align = ['left','left','left','left'];

foreach ($pending as $r) {
    $authorid = !empty($r->studentid) ? (int)$r->studentid : (int)$r->teacherid;
    $author   = core_user::get_user($authorid);
    $name     = fullname($author);

    if ($r->parentid) {
        $created  = $r->timecreatedanswer ? date('d/m/Y H:i:s', $r->timecreatedanswer) : '';
    } else {
        $created  = $r->timecreatedquestion ? date('d/m/Y H:i:s', $r->timecreatedquestion) : '';
    }

    $title = format_string(
        core_text::substr(trim(strip_tags((string)$r->message)), 0, 120)
        . (core_text::strlen((string)$r->message) > 120 ? '…' : '')
    );

    $approveurl = new moodle_url('/mod/qaa/approval.php', [
        'id'         => $cm->id,
        'action'     => 'approve',
        'questionid' => $r->id,
        'sesskey'    => sesskey(),
        'page'       => $page, // giữ nguyên trang khi xử lý
    ]);
    $rejecturl  = new moodle_url('/mod/qaa/approval.php', [
        'id'         => $cm->id,
        'action'     => 'reject',
        'questionid' => $r->id,
        'sesskey'    => sesskey(),
        'page'       => $page,
    ]);

    $returnurl = new moodle_url('/mod/qaa/approval.php', ['id' => $cm->id, 'page' => $page]);

    $content = html_writer::link(
        new moodle_url('/mod/qaa/view.php', [
            'id'         => $cm->id,
            'questionid' => $r->id,
            'embed'      => 1,
            'returnurl'  => $returnurl->out_as_local_url(false),
        ]),
        $title
    );

    $table->data[] = [
        $content,
        $name,
        $created,
        html_writer::link($approveurl, get_string('approve','core')) . ' | ' .
        html_writer::link($rejecturl,  get_string('reject','core')),
    ];
}

echo html_writer::table($table);

// Thanh phân trang (dưới bảng).
echo $OUTPUT->paging_bar($totalpending, $page, $perpage, $baseurl);

echo html_writer::link(
    new moodle_url('/mod/qaa/view.php', ['id'=>$cm->id]),
    get_string('back'),
    ['class'=>'btn btn-secondary mr-2 qaa-back-list']
);
echo $OUTPUT->footer();
