<?php
defined('MOODLE_INTERNAL') || die();

global $PAGE;

$examtype = '';
$cmid = 0;

if (!empty($PAGE->cm) && !empty($PAGE->cm->id)) {
    $cmid = (int) $PAGE->cm->id;
} else {
    $cmid = optional_param('cmid', 0, PARAM_INT);
    if (!$cmid) {
        $cmid = optional_param('id', 0, PARAM_INT);
    }
}

if ($cmid && class_exists('\local_modcustomfields\customfield\mod_handler')) {
    $handler = \local_modcustomfields\customfield\mod_handler::create();

    // Lấy toàn bộ field definitions của handler (không phụ thuộc instance).
    $fields = $handler->get_fields();

    // Gọi thẳng api, truyền onlyvisible = false để bỏ qua kiểm tra can_view().
    $datas = \core_customfield\api::get_instance_fields_data($fields, $cmid, false);

    foreach ($datas as $data) {
        if ($data->get_field()->get('shortname') === 'ielts_exam_type') {
            $examtype = trim((string) $data->export_value());
            break;
        }
    }
}

if ($examtype === 'kt') {
    require_once($CFG->dirroot . '/theme/th_lambda_st/renderers/mod_quiz_renderer.php');
    require_once 'renderers/mod_quiz_renderer.php';
}