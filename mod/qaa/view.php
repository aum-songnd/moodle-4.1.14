<?php
// This file is part of Moodle - http://moodle.org/
//
// GNU GPL v3 or later.

/**
 * Feed view for mod_qaa (controller: thin; delegates to actions/services/renderer).
 *
 * @package     mod_qaa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/question_form.php');
require_once(__DIR__ . '/answer_form.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/repository/qaapairs_repository.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/service/feed_service.php');
require_once($CFG->dirroot . '/mod/qaa/classes/output/comment.php');
require_once($CFG->dirroot . '/mod/qaa/classes/output/thread.php');
require_once($CFG->dirroot . '/mod/qaa/classes/output/thread_list.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/add_answer_action.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/add_question_action.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/edit_question_action.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/actions/edit_answer_action.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/mapper/comment_mapper.php');

use mod_qaa\local\repository\qaapairs_repository;
use mod_qaa\local\service\feed_service;
use mod_qaa\local\actions\add_question_action;
use mod_qaa\local\actions\edit_question_action;
use mod_qaa\local\actions\edit_answer_action;
use mod_qaa\local\actions\add_answer_action;

/* ---------------- Parameters ---------------- */
$id         = optional_param('id', 0, PARAM_INT);
$q          = optional_param('q', 0, PARAM_INT);
$action     = optional_param('action', '', PARAM_TEXT);
$questionid = optional_param('questionid', 0, PARAM_INT);      // root question id context
$replytomsg = optional_param('replytomsg', null, PARAM_INT);    // child reply target
$embed      = optional_param('embed', 0, PARAM_INT);
$redirecturl= optional_param('returnurl', '', PARAM_LOCALURL);
// Lấy tham số phân trang từ URL.
$page       = optional_param('page', 0, PARAM_INT);

/* ---------------- Load course + cm + instance ---------------- */
if ($id) {
    $cm = get_coursemodule_from_id('qaa', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('qaa', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($q) {
    $moduleinstance = $DB->get_record('qaa', ['id' => $q], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('qaa', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('missingidandcmid', 'error');
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

$layout = 'list';
$ismanager = has_capability('mod/qaa:approval', $modulecontext);

/* ---------------- Event: viewed ---------------- */
$event = \mod_qaa\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context'  => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('qaa', $moduleinstance);
$event->trigger();

/* ---------------- Page setup ---------------- */
$params = ['id' => $cm->id];
if ($embed) { $params['embed'] = 1; }
if ($page) { $params['page'] = $page; }
if ($questionid) { $params['questionid'] = $questionid; }
if ($replytomsg) { $params['replytomsg'] = $replytomsg; }
if ($redirecturl) { $params['redirecturl'] = $redirecturl; }

$pageurl = new moodle_url('/mod/qaa/view.php', $params);

$PAGE->set_url($pageurl);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

/* ---------------- DI: services ---------------- */
$repo  = new qaapairs_repository($DB);
$feed  = new feed_service($repo);

/* ---------------- Forms (init only, handle later) ---------------- */
$canmake = has_capability('mod/qaa:makequest', $modulecontext);
$canapproval = has_capability('mod/qaa:approval', $modulecontext);

$questionform = null;
$answerform   = null;

if ($canmake) {
    $replytomsg_param = optional_param('replytomsg', null, PARAM_INT);

    // Chọn action cho question form: add hoặc edit.
    $questionaction = ($action === 'edit_question') ? 'edit_question' : 'add_question';
    $question_action = new moodle_url('/mod/qaa/view.php', [
        'id'         => $cm->id,
        'questionid' => $questionid,
        'action'     => $questionaction,
        'replytomsg' => is_null($replytomsg_param) ? 0 : (int)$replytomsg_param,
        'embed'      => 1,
    ]);
    $questionform = new question_form($question_action);

    // Chọn action cho answer form: add hoặc edit.
    $answeraction = ($action === 'edit_answer') ? 'edit_answer' : 'add_answer';
    $answer_action = new moodle_url('/mod/qaa/view.php', [
        'id'         => $cm->id,
        'questionid' => $questionid ?: optional_param('questionid', 0, PARAM_INT),
        'action'     => $answeraction,
        'replytomsg' => is_null($replytomsg_param) ? 0 : (int)$replytomsg_param,
        'embed'      => 1,
    ]);
    $answerform = new answer_form($answer_action);
}

/* ---------------- Actions (approve/add/edit/answer) ---------------- */
if ($action === 'approve_msg') {
    // Cho phép dùng class action nếu bạn tạo; nếu không sẽ fallback inline.
    require_sesskey();
    if (has_capability('mod/qaa:approval', $modulecontext)) {
        $mid = required_param('mid', PARAM_INT);
        if ($DB->record_exists('qaapairs', ['id' => $mid, 'qaaid' => $cm->instance])) {
            $DB->set_field('qaapairs', 'status', 1, ['id' => $mid]);
        }
    }
    redirect(new moodle_url('/mod/qaa/view.php', ['id' => $cm->id, 'embed' => 1]));
    exit;
}

if ($canmake && $questionform && ($action == 'add_question' || $action == 'edit_question')) {
    if ($questionform->is_cancelled()) {
        redirect($pageurl);
        exit;
    } else if ($fromform = $questionform->get_data()) {
        if (empty($fromform->isedit)) {
            $act = new add_question_action($DB);
            $ret = $act->execute($fromform, $cm, $course, $modulecontext, $moduleinstance);
            redirect($ret);
            exit;
        } else {
            $act = new edit_question_action($DB);
            $ret = $act->execute($fromform, $cm, $modulecontext);
            redirect($ret);
            exit;
        }
    }
}

if ($canmake && $answerform && ($action === 'add_answer' || $action === 'edit_answer')) {
    $answer_submitted = false;
    if ($fromform = $answerform->get_data()) {
        if (empty($fromform->isedit)) {
            $replyto = required_param('replytomsg', PARAM_INT);
            $act = new add_answer_action($DB);
            $ret = $act->execute($fromform, $cm, $modulecontext, $questionid ?: 0, $replyto);
            redirect($ret);
            exit;
        } else {
            $act = new edit_answer_action($DB);
            $ret = $act->execute($fromform, $cm, $modulecontext);
            redirect($ret);
            exit;
        }
    } else if ($answerform->is_cancelled()) {
        redirect($pageurl);
        exit;
    } else if ($answerform->is_submitted()) {
        // Validation fail
        $answer_submitted = true;
    }
}

/* ---------------- Output: header ---------------- */
echo $OUTPUT->header();

$context = context_course::instance($course->id);
$studentrole = $DB->get_record('role', ['shortname' => 'student'], 'id', IGNORE_MISSING);
$teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher'], 'id', IGNORE_MISSING);

if (user_has_role_assignment($USER->id, $studentrole->id, $context->id)) {
    if ($moduleinstance->question_close_time > time() || $moduleinstance->question_close_time == 0) {
        $question_close_time = false;
        $answer_close_time = false;
    } else {
        $question_close_time = true;
        $answer_close_time = true;
    }
} else if (user_has_role_assignment($USER->id, $teacherrole->id, $context->id)) {
    $question_close_time = true;
    $answer_close_time = false;
} else {
    $question_close_time = false;
    $answer_close_time = false;
}

/* ---------------- Quick add question on top ---------------- */

if ($canmake) {
    echo html_writer::start_div('qaa-add-question-top');

    if ($action === 'add_question') {
        // Trường hợp add question (form trên đầu)
        if ($questionform->is_submitted() && !$questionform->is_cancelled() && !$questionform->get_data()) {
            // Validation fail -> hiển thị lại form với lỗi
            echo html_writer::start_div('qaa-add-question-form');
            $questionform->display();
            echo html_writer::end_div();
        } else {
            // Lần đầu mở form
            $toform = ['id' => $cm->id, 'embed' => 1, 'action' => 'add_question'];
            $questionform->set_data($toform);
            echo html_writer::start_div('qaa-add-question-form');
            $questionform->display();
            echo html_writer::end_div();
        }

        $PAGE->requires->js_call_amd('local/thlib/main', 'scrollIntoView');

    } else {
        // Các action khác (edit_question, ...) hiện nút "Thêm câu hỏi"
        if (empty($questionid) && !$question_close_time) {
            $url = new moodle_url('/mod/qaa/view.php', [
                'id'    => $cm->id,
                'action'=> 'add_question',
                'embed' => 1
            ]);
            echo html_writer::link($url, get_string('addquestion', 'mod_qaa'), ['class' => 'btn btn-primary']);
        }

        // Nút "Duyệt câu hỏi" đặt chung hàng với nút "Thêm câu hỏi"
        if (empty($questionid) && has_capability('mod/qaa:approval', $coursecontext)) {
            $url_approval = new moodle_url('/mod/qaa/approval.php', ['id' => $cm->id]);
            echo ' '; // khoảng trắng nhỏ giữa 2 nút
            echo html_writer::link($url_approval, get_string('approval', 'mod_qaa'), [
                'class' => 'btn btn-primary',
            ]);
        }
    }

    echo html_writer::end_div();
}

if (empty($questionid) && $layout === 'list') {
    $perpage = 10;

    // Tổng số thread (root) để vẽ thanh phân trang.
    $total = $repo->count_threads($cm->instance, $USER->id, $ismanager);

    // Lấy dữ liệu theo trang.
    $summary = $repo->get_threads_summary($cm->instance, $USER->id, $ismanager, $page, $perpage);

    // Map ra rows cho template
    $rows = \mod_qaa\local\mapper\list_mapper::map_rows($summary, $modulecontext, $cm->id, $pageurl->out_as_local_url(false));

    // Render
    echo html_writer::div('', 'qaa-add-question-top'); // (tuỳ: vẫn hiển thị nút "Thêm câu hỏi")
    $renderer = $PAGE->get_renderer('mod_qaa');

    echo $renderer->render_thread_list(new \mod_qaa\output\thread_list($rows));

    $baseurl = new moodle_url('/mod/qaa/view.php', ['id' => $cm->id]);
    echo $OUTPUT->paging_bar($total, $page, $perpage, $baseurl);
    echo $OUTPUT->footer();
    return;
}

/* ---------------- Feed render (renderer + mustache) ---------------- */
$roots = $feed->get_feed_tree($cm->instance, $modulecontext);
// Nếu có questionid => hiển thị đúng thread của root (kể cả khi questionid là id con)
if (!empty($questionid)) {
    $result = qaa_find_root_for_id($roots, (int)$questionid);

    if ($result === null) {
        // không thấy vì không có quyền xem hoặc ID sai
        echo $OUTPUT->notification(get_string('nothingtodisplay', 'moodle'), 'notifymessage');
        echo html_writer::link(new moodle_url('/mod/qaa/view.php', ['id'=>$cm->id]),
                               get_string('back'), ['class'=>'btn btn-secondary']);
        echo $OUTPUT->footer();
        exit;
    }

    // Chỉ render đúng root
    $roots = [$result['root']];

    // Nếu questionid là con → cuộn tới nó sau khi render xong
    if (!is_null($result['foundChildId'])) {
        $childid = (int)$result['foundChildId'];
        $PAGE->requires->js_init_code("
            (function(){
              var el = document.querySelector('[data-reply-target=\"'+{$childid}+'\"]');
              if (el) el.scrollIntoView({behavior:'smooth', block:'center'});
            })();
        ");
    }
}

$renderer = $PAGE->get_renderer('mod_qaa');
echo html_writer::start_div('qaa-feed');

foreach ($roots as $root) {
    $rootownerid = (int)($root->studentid ?? 0);

    echo html_writer::start_div('qaa-card');
    // echo html_writer::div(get_string('question', 'mod_qaa'), 'qaa-card-title');

    // Map DB record -> DTO comment (đệ quy)
    $dto = qaa_map_record_to_comment($root, $modulecontext, $cm->id, $root->id, $canmake, $canapproval, $rootownerid, $DB);

    // Render thread with mustache
    echo $renderer->render_thread(new \mod_qaa\output\thread([$dto]));

    // Inline EDIT form cho root (giữ hành vi cũ)
    if ($action === 'edit_question' && $embed && (int)$questionid
        && $canmake) {

        // Nếu KHÔNG phải case validation fail thì mới set_data từ DB
        if (!($questionform->is_submitted() && !$questionform->is_cancelled() && !$questionform->get_data())) {
            $editquestiondata = $DB->get_record('qaapairs', ['id' => $questionid], '*', MUST_EXIST);

            $draftid_editor = file_get_submitted_draft_itemid('question');
            $currentquestion = file_prepare_draft_area(
                $draftid_editor,
                $modulecontext->id,
                'mod_qaa', 'message', $questionid,
                ['subdirs' => true],
                $editquestiondata->message
            );

            $toform = [
                'id' => $cm->id,
                'questionid' => $editquestiondata->id,
                'isedit' => true,
                'action' => 'edit_question',
                'question' => [
                    'text' => $currentquestion,
                    'format' => FORMAT_HTML,
                    'itemid' => $draftid_editor
                ],
            ];
            $questionform->set_data($toform);
        }

        echo html_writer::start_div('qaa-edit-box', ['id' => 'qaa-edit-box']);
        $questionform->display();
        echo html_writer::end_div();

        $PAGE->requires->js_call_amd('mod_qaa/edit', 'moveEditBox', [$questionid]);
    }

    if ($action === 'edit_answer' && $embed && (int)$questionid
        && $canmake) {

        // Nếu không phải validation fail thì set_data từ DB
        if (!($answerform->is_submitted() && !$answerform->is_cancelled() && !$answerform->get_data())) {
            $editquestiondata = $DB->get_record('qaapairs', ['id' => $questionid], '*', MUST_EXIST);

            $draftid_editor = file_get_submitted_draft_itemid('question');
            $currentquestion = file_prepare_draft_area(
                $draftid_editor,
                $modulecontext->id,
                'mod_qaa', 'message', $questionid,
                ['subdirs' => true],
                $editquestiondata->message
            );

            $toform = [
                'id' => $cm->id,
                'questionid' => $editquestiondata->id,
                'isedit' => true,
                'action' => 'edit_answer',
                'answer' => [
                    'text' => $currentquestion,
                    'format' => FORMAT_HTML,
                    'itemid' => $draftid_editor
                ],
            ];

            $answerform->set_data($toform);
        }

        echo html_writer::start_div('qaa-edit-box', ['id' => 'qaa-edit-box']);
        $answerform->display();
        echo html_writer::end_div();

        $PAGE->requires->js_call_amd('mod_qaa/edit', 'moveEditBox', [$questionid]);
    }

    echo html_writer::end_div(); // .qaa-card
}

echo html_writer::end_div(); // .qaa-feed

/* ---------------- Reply box (move into right place) ---------------- */
$replytargetid = is_null($replytomsg) ? -1 : (int)$replytomsg;

if ($canmake && $action === 'add_answer' && $embed && $questionid) {
    $toform = [
        'id' => $cm->id,
        'questionid' => $questionid,
        'isedit' => false,
        'action' => 'add_answer'
    ];

    // Không tạo form mới – dùng lại $answerform đã khởi tạo ở trên.
    if (!($answerform->is_submitted() && !$answerform->is_cancelled() && !$answerform->get_data())) {
        // chỉ set_data lần đầu, không phải case validation fail
        $answerform->set_data($toform);
    }

    echo html_writer::start_div('qaa-reply-box', ['id' => 'qaa-reply-box']);
    $answerform->display();
    echo html_writer::end_div();

    $PAGE->requires->js_call_amd('mod_qaa/reply_parent', 'moveReplyBox');
}

if (!empty($questionid)) {
    $root = reset($roots);
    
    $isApproved = ((int)$root->status === 1);
    $isOwner    = ((int)$root->studentid === (int)$USER->id);
    $canmakeFlag = 0;

    if ($isApproved && $canmake) {
        if ($canapproval || $isOwner) {
            // GV: trả lời mọi bài đã duyệt; Chủ bài: được trả lời bài của mình
            $canmakeFlag = 1;
        } elseif (!$canapproval) {
            // HV: chỉ trả lời bài đã duyệt và KHÔNG phải bài của mình? (theo yêu cầu cũ)
            $canmakeFlag = $isOwner ? 1 : 0;
        }
    }
    
    if ($canmakeFlag) {
        $replyurl = (new moodle_url('/mod/qaa/view.php', [
            'id' => $cm->id,
            'questionid' => $root->id,
            'action' => 'add_answer',
            'replytomsg' => $root->id,
            'embed' => 1,
            'sesskey' => sesskey()
        ]))->out(false);
        
        if (!$answer_close_time) {
            echo html_writer::link($replyurl, get_string('answer', 'mod_qaa'), ['class'=>'btn btn-primary mt-3 mr-2 qaa-answer']);
        }
    }

    if($redirecturl) {
        $redirecturl = new moodle_url($redirecturl);
        echo html_writer::link($redirecturl,
                          get_string('back'), ['class'=>'btn btn-secondary mt-3 qaa-back-list']);
    } else {
        echo html_writer::link(new moodle_url('/mod/qaa/view.php', ['id'=>$cm->id]),
                          get_string('back'), ['class'=>'btn btn-secondary mt-3 qaa-back-list']);
    }
}

/* ---------------- Footer ---------------- */
echo $OUTPUT->footer();
