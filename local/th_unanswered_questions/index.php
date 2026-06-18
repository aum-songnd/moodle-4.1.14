<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/th_unanswered_questions/classes/form/approval_form.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/approval_approve_action.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/approval_reject_action.php');

global $DB, $CFG, $COURSE, $USER;

// ====== PARAM ======
$cmid       = optional_param('cmid', 0, PARAM_INT);   
$teacherid  = optional_param('id', $USER->id, PARAM_INT);
$page       = optional_param('page', 0, PARAM_INT); // trang hiện tại (0-based)
$action     = optional_param('action', '', PARAM_ALPHA);// '', 'approve', 'reject'
$questionid = optional_param('questionid', 0, PARAM_INT);
$perpage    = 50;
$offset     = $page * $perpage;

// ====== LOGIN & CONTEXT ======
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$url = new moodle_url('/local/th_unanswered_questions/index.php', [
    'id' => $teacherid
]);

$PAGE->set_url($url);
$heading = get_string('th_unanswered_questions', 'local_th_unanswered_questions');
$PAGE->set_heading($heading);
$PAGE->set_title($heading);
$PAGE->navbar->add($heading , $url);

// ====== PERMISSION CHECK ======
if (!has_capability('local/th_unanswered_questions:view', $context) && $USER->id != $teacherid) {
    print_error('permission', 'local_th_unanswered_questions');
}

echo $OUTPUT->header();

if ($questionid && in_array($action, ['approve', 'reject'], true)) {
    $context = context_module::instance($cmid);
    $cm      = get_coursemodule_from_id('qaa', $cmid, 0, false, MUST_EXIST);
    $root = $DB->get_record('qaapairs', ['id'=>$questionid, 'qaaid'=>$cm->instance], '*', MUST_EXIST);

    $mform = new \local_th_unanswered_questions\form\approval_form(
        new moodle_url('/local/th_unanswered_questions/index.php', ['cmid'=>$cm->id, 'action'=>$action, 'questionid'=>$questionid, 'page'=>$page, 'id'=>$teacherid]),
        ['mode'=>$action, 'context'=>$context, 'cmid'=>$cm->id, 'root'=>$root, 'page'=>$page, 'userid'=>$teacherid]
    );

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/th_unanswered_questions/index.php', ['id'=>$teacherid]));
    } else if ($data = $mform->get_data()) {
        if ($action === 'approve') {
            $act = new \mod_qaa\local\actions\approval_approve_action($DB);
            $ret = $act->execute($data, $cm, $context);

            redirect(
                new moodle_url('/local/th_unanswered_questions/index.php', ['id' => $teacherid]),
                get_string('approvequestionsuccess', 'local_th_unanswered_questions')
            );
        } else {
            $act = new \mod_qaa\local\actions\approval_reject_action($DB);
            $ret = $act->execute($data, $cm, $context);

            redirect(
                new moodle_url('/local/th_unanswered_questions/index.php', ['id' => $teacherid]),
                get_string('rejectquestionsuccess', 'local_th_unanswered_questions')
            );
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

// ====== PARAM SQL ======
$monthlimit = (int) get_config('local_th_unanswered_questions', 'monthlimit');
if ($monthlimit <= 0) {
    $monthlimit = 2; // fallback an toàn
}

$params = [
    'teacherid'     => $teacherid,
    'coursecontext' => CONTEXT_COURSE,
    'twomonthsago'  => strtotime("-{$monthlimit} months"),
];

// ====== COUNT TOTAL ======
$countsql = "
    SELECT COUNT(DISTINCT qp.id)
    FROM {course} c
    JOIN {context} ctx ON ctx.instanceid = c.id
        AND ctx.contextlevel = :coursecontext
    JOIN {role_assignments} ra ON ra.contextid = ctx.id
    JOIN {role} r ON r.id = ra.roleid
    JOIN {qaa} qaa ON qaa.course = c.id
    JOIN {qaapairs} qp ON qp.qaaid = qaa.id
    WHERE ra.userid = :teacherid
      AND r.shortname = 'editingteacher'
      AND c.startdate >= :twomonthsago
      AND qp.status = 0
";

$total = $DB->count_records_sql($countsql, $params);

// ====== GET DATA WITH PAGINATION ======
$sql = "
    SELECT DISTINCT
        qp.id AS questionid,
        qp.studentid,
        qp.message AS question,
        CASE
            WHEN qp.parentid IS NULL THEN qp.timecreatedquestion
            ELSE qp.timecreatedanswer
        END AS timecreatedquestion,
        qaa.id AS qaaid,
        qaa.course AS courseid,
        c.fullname AS coursename
    FROM {course} c
    JOIN {context} ctx ON ctx.instanceid = c.id
        AND ctx.contextlevel = :coursecontext
    JOIN {role_assignments} ra ON ra.contextid = ctx.id
    JOIN {role} r ON r.id = ra.roleid
    JOIN {qaa} qaa ON qaa.course = c.id
    JOIN {qaapairs} qp ON qp.qaaid = qaa.id
    WHERE ra.userid = :teacherid
      AND r.shortname = 'editingteacher'
      AND c.startdate >= :twomonthsago
      AND qp.status = 0
    ORDER BY qp.timecreatedquestion DESC
";

$list_question = $DB->get_records_sql(
    $sql,
    $params,
    $offset,
    $perpage
);

// ====== OUTPUT ======
$listquestion = get_string('listquestion', 'local_th_unanswered_questions');

echo "<div class='th_question_answer'>";
echo "<h1>{$listquestion}</h1>";

if (empty($list_question)) {
    echo $OUTPUT->notification(
        get_string('noquestions', 'local_th_unanswered_questions'),
        'info'
    );
} else {
    echo "<ul>";

    foreach ($list_question as $question) {

        $student = core_user::get_user($question->studentid);
        $avatar  = $OUTPUT->user_picture($student, ['size' => 50]);

        $studentname = fullname($student);
        $datetime    = userdate($question->timecreatedquestion);
        $coursename  = format_string($question->coursename);
        $questiontext = format_text($question->question, FORMAT_HTML);
        $answertext   = get_string('answer', 'local_th_unanswered_questions');

        $cm = get_coursemodule_from_instance(
            'qaa',
            $question->qaaid,
            $question->courseid,
            false,
            MUST_EXIST
        );

        $approveurl = new moodle_url('/local/th_unanswered_questions/index.php', [
            'cmid'         => $cm->id,
            'action'     => 'approve',
            'questionid' => $question->questionid,
            'sesskey'    => sesskey(),
            'page'       => $page, // giữ nguyên trang khi xử lý
            'id'     => $teacherid
        ]);
        $rejecturl  = new moodle_url('/local/th_unanswered_questions/index.php', [
            'cmid'         => $cm->id,
            'action'     => 'reject',
            'questionid' => $question->questionid,
            'sesskey'    => sesskey(),
            'page'       => $page,
            'id'     => $teacherid
        ]);

        $actions = html_writer::link(
            $approveurl,
            get_string('approve', 'core'),
            ['class' => 'btn btn-primary thuq-approve']
        ) . ' ' .
        html_writer::link(
            $rejecturl,
            get_string('reject', 'core'),
            ['class' => 'btn btn-secondary thuq-reject']
        );

        echo "
        <li class='thuq-thread'>
            <div class='thuq-question'>
                <div class='thuq-avatar'>{$avatar}</div>

                <div class='thuq-body'>
                    <div class='thuq-header'>
                        <span class='thuq-student'>{$studentname}</span>
                        <span class='thuq-course'>{$coursename}</span>
                        <span class='thuq-time'>{$datetime}</span>
                    </div>

                    <div class='thuq-content'>
                        <a href='{$CFG->wwwroot}/mod/qaa/view.php?id={$cm->id}&questionid={$question->questionid}&returnurl={$url}'>
                            {$questiontext}
                        </a>
                    </div>

                    <div class='thuq-actions'>
                        $actions
                    </div>
                </div>
            </div>
        </li>";
    }

    echo "</ul>";

    // ====== PAGINATION BAR ======
    echo $OUTPUT->paging_bar(
        $total,
        $page,
        $perpage,
        $url
    );
}

echo "</div>";
echo $OUTPUT->footer();
