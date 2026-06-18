<?php
/**
 * lib.php — Các hàm bắt buộc mà Moodle gọi để quản lý plugin
 *
 * Moodle yêu cầu mỗi activity module phải định nghĩa 3 hàm cốt lõi:
 *   faceattend_add_instance()    — gọi khi giảng viên TẠO MỚI activity
 *   faceattend_update_instance() — gọi khi giảng viên CHỈNH SỬA activity
 *   faceattend_delete_instance() — gọi khi giảng viên XÓA activity
 */
defined('MOODLE_INTERNAL') || die();

// ─────────────────────────────────────────────────────────
// 1. TẠO MỚI activity
// Moodle gọi hàm này sau khi giảng viên submit form tạo activity.
// $data = object chứa dữ liệu từ form (name, intro, session_date, ...)
// Trả về: ID của bản ghi vừa tạo trong DB
// ─────────────────────────────────────────────────────────
function faceattend_add_instance($data) {
    global $DB;

    $data->timecreated  = time();
    $data->timemodified = time();

    // Moodle truyền intro dưới dạng array nếu có editor
    // Hàm này chuẩn hóa lại trước khi lưu
    if (isset($data->introeditor)) {
        $data->intro       = $data->introeditor['text'];
        $data->introformat = $data->introeditor['format'];
    }

    // Chuyển session_date từ dạng Moodle date picker (array) sang Unix timestamp
    if (is_array($data->session_date)) {
        $data->session_date = make_timestamp(
            $data->session_date['year'],
            $data->session_date['month'],
            $data->session_date['day'],
            $data->session_date['hour']   ?? 0,
            $data->session_date['minute'] ?? 0
        );
    }

    $data->is_open = 1; // Mặc định mở khi tạo mới

    return $DB->insert_record('faceattend', $data);
}

// ─────────────────────────────────────────────────────────
// 2. CẬP NHẬT activity
// Moodle gọi hàm này khi giảng viên chỉnh sửa activity đã có.
// $data->instance = ID của activity cần cập nhật
// ─────────────────────────────────────────────────────────
function faceattend_update_instance($data) {
    global $DB;

    $data->id           = $data->instance; // Moodle dùng 'instance', DB dùng 'id'
    $data->timemodified = time();

    if (isset($data->introeditor)) {
        $data->intro       = $data->introeditor['text'];
        $data->introformat = $data->introeditor['format'];
    }

    if (is_array($data->session_date)) {
        $data->session_date = make_timestamp(
            $data->session_date['year'],
            $data->session_date['month'],
            $data->session_date['day'],
            $data->session_date['hour']   ?? 0,
            $data->session_date['minute'] ?? 0
        );
    }

    return $DB->update_record('faceattend', $data);
}

// ─────────────────────────────────────────────────────────
// 3. XÓA activity
// Moodle gọi hàm này khi giảng viên xóa activity.
// Phải xóa TẤT CẢ dữ liệu liên quan đến activity này.
// $id = ID của activity cần xóa
// ─────────────────────────────────────────────────────────
function faceattend_delete_instance($id) {
    global $DB;

    // Kiểm tra activity tồn tại
    if (!$DB->record_exists('faceattend', ['id' => $id])) {
        return false;
    }

    // Xóa tất cả log điểm danh của buổi này
    $DB->delete_records('faceattend_log', ['faceattendid' => $id]);

    // Xóa activity chính
    $DB->delete_records('faceattend', ['id' => $id]);

    return true;
}

// ─────────────────────────────────────────────────────────
// 4. HỖ TRỢ HIỂN THỊ — Moodle gọi để lấy thông tin activity
// ─────────────────────────────────────────────────────────

/**
 * Trả về thông tin feature mà plugin hỗ trợ.
 * Moodle dùng hàm này để biết plugin có hỗ trợ
 * grading, completion, backup, v.v. hay không.
 */
function faceattend_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:         return true;  // Có phần mô tả
        case FEATURE_SHOW_DESCRIPTION:  return true;  // Hiển thị mô tả ở course page
        case FEATURE_BACKUP_MOODLE2:    return true;  // Hỗ trợ backup/restore
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true; // Ghi nhận khi sinh viên xem
        default:                        return null;
    }
}

/**
 * Lấy danh sách tất cả activity faceattend trong một course.
 * Moodle gọi hàm này để hiển thị trên trang course.
 */
function faceattend_get_coursemodule_info($coursemodule) {
    global $DB;

    $record = $DB->get_record('faceattend',
        ['id' => $coursemodule->instance],
        'id, name, intro, introformat, session_date, is_open'
    );

    if (!$record) {
        return false;
    }

    $info = new cached_cm_info();
    $info->name = $record->name;

    // Thêm badge "Đang mở" / "Đã đóng" vào tên activity
    if ($record->is_open) {
        $info->name .= ' 🟢';
    } else {
        $info->name .= ' 🔴';
    }

    return $info;
}
