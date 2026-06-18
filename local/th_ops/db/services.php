<?php

/**
 * Webservice declaration for th_ops
 */

$functions = [
    // Service liên quan đến khóa học
    'thc_ops_get_courses_by_field' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_courses_by_field',
        'description' => 'Tìm kiếm khóa học theo shortname,shortnames, idnumber, id, ids, categoryid hoặc categorycode.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
        'services' => ['th_ops_service']
    ],

    'thc_ops_create_courses' => [
        'classname' => 'th_ops_external',
        'methodname' => 'create_courses',
        'description' => 'Tạo mới các khóa học, ánh xạ categorycode sang categoryid. Wrapper cho core_course_create_courses.',
        'type' => 'write',
        'capabilities' => 'moodle/course:create',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_update_courses' => [
        'classname' => 'th_ops_external',
        'methodname' => 'update_courses',
        'description' => 'Cập nhật thông tin nhiều khóa học theo coursecode (shortname), hỗ trợ đổi parent_categorycode.',
        'type' => 'write',
        'capabilities' => 'moodle/course:update',
        'services' => ['th_ops_service'],
    ],
    // End Service liên quan đến khóa học

    // Service liên quan đến người dùng
    'thc_ops_update_users_by_username' => [
        'classname' => 'th_ops_external',
        'methodname' => 'update_users_by_username',
        'description' => 'Cập nhật thông tin nhiều người dùng theo username (cho phép cập nhật tất cả trường chuẩn và custom fields)',
        'type' => 'write',
        'capabilities' => 'moodle/user:update',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_get_users' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_users',
        'description' => 'Lấy danh sách người dùng theo tiêu chí lọc (wrapper cho core_user_get_users).',
        'type' => 'read',
        'capabilities' => 'moodle/user:viewdetails',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_get_users_by_field' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_users_by_field',
        'description' => 'Lấy người dùng theo trường chỉ định (id, idnumber, username, email), và tự bổ sung email nếu thiếu. Wrapper cho core_user_get_users_by_field.',
        'type' => 'read',
        'capabilities' => 'moodle/user:viewdetails',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_create_users' => [
        'classname' => 'th_ops_external',
        'methodname' => 'create_users',
        'description' => 'Tạo mới danh sách người dùng. Wrapper cho core_user_create_users.',
        'type' => 'write',
        'capabilities' => 'moodle/user:create',
        'services' => ['th_ops_service'],
    ],

    // End service liên quan đến người dùng

    // Service liên quan đến danh mục (category)

    'thc_ops_create_categories' => [
        'classname' => 'th_ops_external',
        'methodname' => 'create_categories',
        'description' => 'Tạo mới danh mục khóa học (wrapper cho core_course_create_categories, hỗ trợ parent_categorycode).',
        'type' => 'write',
        'capabilities' => 'moodle/category:manage',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_update_categories_by_categorycode' => [
        'classname' => 'th_ops_external',
        'methodname' => 'update_categories_by_categorycode',
        'description' => 'Cập nhật thông tin category (danh mục khóa học) theo category code (idnumber/shortname)',
        'type' => 'write',
        'capabilities' => 'moodle/category:manage',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_get_categories_by_ids_or_idnumbers' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_categories_by_ids_or_idnumbers',
        'description' => 'Lấy danh mục khóa học LMS theo list id hoặc idnumber (dạng OR)',
        'type' => 'read',
        'capabilities' => 'moodle/category:manage',
        'services' => ['th_ops_service'],
    ],
    // End service liên quan đến danh mục

    // Service liên quan đến vai trò
    'thc_ops_get_roles' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_roles',
        'description' => 'Lấy danh sách tất cả vai trò khóa học trong hệ thống Moodle',
        'type' => 'read',
        'capabilities' => 'moodle/role:manage', // Có thể chọn quyền phù hợp hơn nếu chỉ cần quyền đọc
        'services' => ['th_ops_service'],
    ],
    'thc_ops_update_roles_by_rolecode' => [
        'classname' => 'th_ops_external',
        'methodname' => 'update_roles_by_rolecode',
        'description' => 'Cập nhật thông tin name và shortname vai trò theo rolecode (shortname) qua API.',
        'type' => 'write',
        'capabilities' => 'moodle/role:manage',
        'services' => ['th_ops_service'],
    ],
    // End service liên quan đến vai trò


    // Service liên quan đến ghi danh
    'thc_ops_get_enrolled_users' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_enrolled_users',
        'description' => 'Lấy danh sách người dùng đã ghi danh vào khóa học theo courseid',
        'type' => 'read',
        'capabilities' => 'moodle/course:viewparticipants', // Quyền phù hợp để xem user trong course
        'services' => ['th_ops_service'],
    ],
    'thc_ops_get_enrolled_users_by_coursecode' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_enrolled_users_by_coursecode',
        'description' => 'Lấy danh sách người dùng đã ghi danh vào khóa học theo course shortname (coursecode)',
        'type' => 'read',
        'capabilities' => 'moodle/course:viewparticipants',
        'services' => ['th_ops_service'],
    ],
    'thc_ops_get_enrolled_courses_users_by_coursecode' => [
        'classname' => 'th_ops_external',
        'methodname' => 'get_enrolled_courses_users_by_coursecode',
        'description' => 'Lấy danh sách người dùng đã ghi danh cho nhiều khóa học theo danh sách mã khóa học (coursecode)',
        'type' => 'read',
        'capabilities' => 'moodle/course:viewparticipants',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_update_enrol_manual_enrol_users' => [
        'classname' => 'th_ops_external', // hoặc tên class chứa function bạn định nghĩa, thường là th_ops_external
        'methodname' => 'update_enrol_manual_enrol_users', // tên hàm PHP trong class, vd: public static function update_enrol_manual_enrol_users
        'description' => 'Cập nhật ghi danh thủ công cho user vào khóa học, hỗ trợ nhiều role cùng lúc. Truyền vào username, course shortname, danh sách rolecodes, timestart, timeend, suspend.',
        'type' => 'write',
        'capabilities' => 'moodle/role:assign, moodle/course:enrolconfig, enrol/manual:enrol',
        'services' => ['th_ops_service'],
    ],
    // End service liên quan đến ghi danh
    // Tạo mới / sao chép khóa học (đồng bộ hoặc xếp hàng async).
    'thc_ops_create_or_duplicate_course' => [
        'classname'   => 'th_ops_external',
        'methodname'  => 'create_or_duplicate_course',
        'description' => 'Create or duplicate a course (sync or queue async).',
        'type'        => 'write',
        'services' => ['th_ops_service'],
        // Không bắt buộc, quyền đã kiểm tra trong handler:
        // 'capabilities' => 'moodle/course:create',
    ],

    // API kiểm tra trạng thái sao chép theo shortname đích (2 trạng thái).
    'thc_ops_get_multi_copy_status' => [
        'classname'   => 'th_ops_external',
        'methodname'  => 'get_multi_copy_status',
        'description' => 'Check duplication status for multiple target shortnames (đang_thực_hiện|hoàn_thành).',
        'type'        => 'read',
        'services' => ['th_ops_service'],
    ],
    // End service liên quan đến tạo , sao chép khóa học và kiểm tra trạng thái  

    'thc_ops_create_upload_create_user_upload' => [
        'classname' => 'th_ops_external',
        'methodname' => 'create_user_upload',
        'description' => 'Create user',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_users' => [
        'classname' => 'th_ops_external',
        'methodname' => 'users',
        'description' => 'Create user',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_categories' => [
        'classname' => 'th_ops_external',
        'methodname' => 'categories',
        'description' => 'Create categorie',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_courses' => [
        'classname' => 'th_ops_external',
        'methodname' => 'courses',
        'description' => 'Create categorie',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_courses_copy_status' => [
        'classname' => 'th_ops_external',
        'methodname' => 'courses_copy_status',
        'description' => 'Create categorie',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_enrols' => [
        'classname' => 'th_ops_external',
        'methodname' => 'enrols',
        'description' => 'Create categorie',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],

    'thc_ops_roles' => [
        'classname' => 'th_ops_external',
        'methodname' => 'roles',
        'description' => 'Create categorie',
        'type' => 'write',
        'services' => ['th_ops_service'],
    ],
];

$services = [
    'th_ops_service' => [
        'functions' => ['core_course_get_categories'],
        'restrictedusers' => 0,                // 0: Dịch vụ có thể gọi bởi tất cả user có quyền
        'enabled' => 1,                // Kích hoạt dịch vụ
        'shortname' => 'th_ops_service', // Tên rút gọn của dịch vụ
    ],
];
