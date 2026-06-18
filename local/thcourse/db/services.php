<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    // Tạo mới / sao chép khóa học (đồng bộ hoặc xếp hàng async).
    'local_thcourse_create_or_duplicate_course' => [
        'classname'   => 'local_thcourse_external',
        'methodname'  => 'create_or_duplicate_course',
        'classpath'   => 'local/thcourse/externallib.php',
        'description' => 'Create or duplicate a course (sync or queue async).',
        'type'        => 'write',
        'ajax'        => true,
        // Không bắt buộc, quyền đã kiểm tra trong handler:
        // 'capabilities' => 'moodle/course:create',
        
    ],

    // API kiểm tra trạng thái sao chép theo shortname đích (2 trạng thái).
    'local_thcourse_get_multi_copy_status' => [
    'classname'   => 'local_thcourse_external',
    'methodname'  => 'get_multi_copy_status',
    'classpath'   => 'local/thcourse/externallib.php',
    'description' => 'Check duplication status for multiple target shortnames (đang_thực_hiện|hoàn_thành).',
    'type'        => 'read',
    'ajax'        => true,
],
];

$services = [
    'Local TH Course API' => [
        'functions'       => [
            'local_thcourse_create_or_duplicate_course',
            'local_thcourse_get_multi_copy_status',
        ],
        'restrictedusers' => 0, // 0 = bất kỳ user nào có token hợp lệ và quyền phù hợp đều gọi được
        'enabled'         => 1,
        // Nếu muốn giới hạn chỉ webservice token cụ thể, set 'restrictedusers' => 1 và cấu hình user+token trong UI.
    ],
];