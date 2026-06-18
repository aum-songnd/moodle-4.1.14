<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$attemptid = required_param('attemptid', PARAM_INT);
// 1. Lấy bản ghi từ quiz_attempts
$attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);
$quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
$context = context_system::instance();

require_login();
require_capability('local/th_online_exam_manager:manager_regrade', $context);

$PAGE->set_url('/local/th_online_exam_manager/regrade_attempt_assign.php', ['attemptid' => $attemptid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('assignattemptstitle', 'local_th_online_exam_manager'));
$PAGE->set_pagelayout('course');

// $regrade_roleid = get_config('local_th_online_exam_manager', 'regrade_roles');

$roleshortname = trim(get_config(
    'local_th_online_exam_manager',
    'regrade_role'
));

$regrade_roleid = null;

if (!empty($roleshortname)) {
    $role = $DB->get_record(
        'role',
        ['shortname' => $roleshortname],
        'id',
        IGNORE_MISSING
    );

    if ($role) {
        $regrade_roleid = (int)$role->id;
    }
}

if ($regrade_roleid) {
    $role = $DB->get_record('role', ['id' => $regrade_roleid], '*', MUST_EXIST);

    // Lấy danh sách user có role 'regrade' tại context hệ thống
    $reviewers = get_role_users(
        $role->id,
        $context,
        false, // $parent = false: chỉ đúng context hệ thống
        'u.*',
        'u.lastname ASC'
    );
} else {
    $reviewers = [];
}

require_once(__DIR__ . '/classes/form/assign_regrade_form.php');
$form = new \local_th_online_exam_manager\form\assign_regrade_form(null, [
    'reviewers' => $reviewers,
]);

$form->set_data(['attemptid' => $attemptid, 'quizid' => $quiz->id]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'waiting']));
} else if ($data = $form->get_data()) {
    $reviewerid  = $data->reviewerid ?? 0;
    $reviewerid2 = $data->reviewerid2 ?? 0;
    $attemptid   = $data->attemptid;
    $quizid      = $data->quizid;
    $reason      = '';
    $results     = [];

    // Lấy tên hiển thị của reviewer 1 & 2 (nếu có)
    $reviewername1 = '';
    if (!empty($reviewerid)) {
        if ($u = $DB->get_record('user', ['id' => $reviewerid], 'id, firstname, lastname, alternatename, middlename, firstnamephonetic, lastnamephonetic')) {
            $reviewername1 = fullname($u);
        } else {
            $reviewername1 = 'User #' . (int)$reviewerid;
        }
    }

    $reviewername2 = '';
    if (!empty($reviewerid2)) {
        if ($u2 = $DB->get_record('user', ['id' => $reviewerid2], 'id, firstname, lastname, alternatename, middlename, firstnamephonetic, lastnamephonetic')) {
            $reviewername2 = fullname($u2);
        } else {
            $reviewername2 = 'User #' . (int)$reviewerid2;
        }
    }

    if ($attemptid) {
        // Reviewer 1
        if (!empty($reviewerid)) {
            if (!$DB->record_exists('th_exam_regrade_requests', ['quizid' => $quizid, 'attemptid' => $attemptid, 'reviewerid' => $reviewerid])) {
                $userid = $DB->get_field('quiz_attempts', 'userid', ['id' => $attemptid]);
                $DB->insert_record('th_exam_regrade_requests', [
                    'quizid'        => $quizid,
                    'attemptid'     => $attemptid,
                    'userid'        => $userid,
                    'reason'        => $reason,
                    'status'        => 'pending',
                    'reviewerid'    => $reviewerid,
                    'timerequested' => time(),
                ]);

                $results[] = [
                    'attemptid' => $attemptid,
                    'reviewer'  => $reviewername1 ?: get_string('reviewer', 'local_th_online_exam_manager'),
                    'status'    => '✅ ' . get_string('assignedsuccessfully', 'local_th_online_exam_manager'),
                ];
            } else {
                $results[] = [
                    'attemptid' => $attemptid,
                    'reviewer'  => $reviewername1 ?: get_string('reviewer', 'local_th_online_exam_manager'),
                    'status'    => '⚠️ ' . get_string('recordalreadyexists', 'local_th_online_exam_manager'),
                ];
            }
        }

        // Reviewer 2
        if (!empty($reviewerid2)) {
            if (!$DB->record_exists('th_exam_regrade_requests', ['quizid' => $quizid, 'attemptid' => $attemptid, 'reviewerid' => $reviewerid2])) {
                $userid = $DB->get_field('quiz_attempts', 'userid', ['id' => $attemptid]);
                $DB->insert_record('th_exam_regrade_requests', [
                    'quizid'        => $quizid,
                    'attemptid'     => $attemptid,
                    'userid'        => $userid,
                    'reason'        => $reason,
                    'status'        => 'pending',
                    'reviewerid'    => $reviewerid2,
                    'timerequested' => time(),
                ]);

                $results[] = [
                    'attemptid' => $attemptid,
                    'reviewer'  => $reviewername2 ?: get_string('reviewer', 'local_th_online_exam_manager'),
                    'status'    => '✅ ' . get_string('assignedsuccessfully', 'local_th_online_exam_manager'),
                ];
            } else {
                $results[] = [
                    'attemptid' => $attemptid,
                    'reviewer'  => $reviewername2 ?: get_string('reviewer', 'local_th_online_exam_manager'),
                    'status'    => '⚠️ ' . get_string('recordalreadyexists', 'local_th_online_exam_manager'),
                ];
            }
        }

        // Xoá assignment tạm
        $DB->delete_records('th_exam_regrade_assign', ['attemptid' => $attemptid]);
    }

    // Hiển thị bảng
    $table = new html_table();
    $table->head = [
        get_string('attemptid', 'local_th_online_exam_manager'),
        get_string('reviewer',  'local_th_online_exam_manager'),
        get_string('status',    'local_th_online_exam_manager')
    ];

    foreach ($results as $row) {
        $table->data[] = [$row['attemptid'], $row['reviewer'], $row['status']];
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('assignreviewerheading', 'local_th_online_exam_manager'));
    $form->display();
    echo html_writer::table($table);

    // Nút quay lại
    $backurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'pending']);
    echo html_writer::start_div('mt-3');
    echo $OUTPUT->single_button($backurl, get_string('back', 'local_th_online_exam_manager'), 'get');
    echo html_writer::end_div();
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('assignreviewerheading', 'local_th_online_exam_manager'));
    $form->display();
    echo $OUTPUT->footer();
}

