<?php
/**
 * toggle.php — Mở/Đóng buổi điểm danh (dành cho giảng viên)
 * Được gọi khi giảng viên click nút "Đóng/Mở điểm danh" trong view.php
 */
require_once('../../config.php');

$id = required_param('id', PARAM_INT); // course module id
require_sesskey(); // Bảo vệ chống CSRF

$cm      = get_coursemodule_from_id('faceattend', $id, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$faceattend = $DB->get_record('faceattend', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/faceattend:manage', $context);

// Toggle trạng thái
$faceattend->is_open    = $faceattend->is_open ? 0 : 1;
$faceattend->timemodified = time();
$DB->update_record('faceattend', $faceattend);

// Quay lại trang view
redirect(new moodle_url('/mod/faceattend/view.php', ['id' => $id]));
