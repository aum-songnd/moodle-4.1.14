<?php
/**
 * db/services.php — Đăng ký Web Service functions với Moodle
 *
 * Moodle đọc file này để biết hàm PHP nào được phép gọi qua AJAX.
 * Không có file này → JavaScript Ajax.call() sẽ bị từ chối.
 */
defined('MOODLE_INTERNAL') || die();

// Danh sách các hàm Web Service của plugin
$functions = [

    // Hàm điểm danh — được JS gọi khi sinh viên chụp ảnh
    'mod_faceattend_submit_attendance' => [
        'classname'     => 'mod_faceattend_external',  // Tên class trong classes/external.php
        'methodname'    => 'submit_attendance',         // Tên method trong class
        'classpath'     => 'mod/faceattend/classes/external.php',
        'description'   => 'Submit face attendance for a session',
        'type'          => 'write',          // 'read' hoặc 'write'
        'ajax'          => true,             // BẮT BUỘC true để JS gọi được
        'loginrequired' => true,             // Phải đăng nhập mới gọi được
        'capabilities'  => 'mod/faceattend:attend',
    ],

];
