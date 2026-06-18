<?php
// local/mycourses/db/services.php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_my_courses_services' => [
        'classname'   => 'local_my_courses_external',
        'methodname'  => 'get_my_courses',
        'classpath'   => 'local/my_courses/externallib.php',
        'description' => 'Lấy danh sách khóa học của người dùng với logic ưu tiên',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => false,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];

$services = [
    'My courses service' => [
        'functions'       => ['local_my_courses_services'],
        'restrictedusers' => 0,
        'enabled'         => 1,
        'shortname'       => 'local_mycourses',
    ],
];
