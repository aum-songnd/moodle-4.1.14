<?php

function local_th_activatequiz_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/th_activatequiz:pushquestions', $context)) {
        $url = new moodle_url('/local/th_activatequiz/index.php', ['courseid' => $course->id]);
        $navigation->add(get_string('activatequiz', 'local_th_activatequiz'), $url, navigation_node::TYPE_CUSTOM, null, 'th_activatequiz');
    }
}


function copy_question_to_category($questionid, $targetcategoryid, $context) {
    global $DB, $COURSE, $USER, $CFG;

    require_once($CFG->dirroot . '/question/editlib.php');
    require_once($CFG->libdir . '/questionlib.php');

    // Lấy thông tin câu hỏi gốc
    $question = $DB->get_record('question', ['id' => $questionid], '*', MUST_EXIST);
    get_question_options($question, true, [$COURSE]);

    // Lấy thông tin danh mục đích
    $category = $DB->get_record('question_categories', ['id' => $targetcategoryid], '*', MUST_EXIST);
    $categorycontext = context::instance_by_id($category->contextid);
    $contexts = new question_edit_contexts($categorycontext);

    // Chuẩn bị dữ liệu gán vào form
    $toform = fullclone($question);
    // $toform->id = null;
    $toform->category = "{$category->id},{$category->contextid}";
    $toform->scrollpos = 0;
    $toform->appendqnumstring = null;
    $toform->returnurl = null;
    $toform->makecopy = 1;
    $toform->inpopup = 0;
    $toform->courseid = $context->instanceid;

    // Xác định quyền thao tác
    $addpermission = has_capability('moodle/question:add', $categorycontext);
    $question->formoptions = (object)[
        'canedit' => question_has_capability_on($question, 'edit'),
        'canmove' => $addpermission && question_has_capability_on($question, 'move'),
        'cansaveasnew' => $addpermission && (question_has_capability_on($question, 'view') || question_has_capability_on($question, 'edit')),
        'repeatelements' => true
    ];
    $formeditable = $question->formoptions->canedit || $question->formoptions->cansaveasnew || $question->formoptions->canmove;

    // Lấy qtype object và tạo form
    $qtypeobj = question_bank::get_qtype($question->qtype);
    $mform = $qtypeobj->create_editing_form('question.php', $question, $category, $contexts, $formeditable);
    $mform->set_data($toform);

    // Tạo bản mới để lưu (clone)
    $clonedquestion = $question;
    unset($clonedquestion->id);
    $clonedquestion->contextid = $category->contextid;
    $clonedquestion->category = $category->id;

    // Gọi hàm lưu, tạo câu hỏi mới
    $newquestion = $qtypeobj->save_question($clonedquestion, $toform);

    // Cập nhật cache
    question_bank::notify_question_edited($newquestion->id);

    return $newquestion;
}


