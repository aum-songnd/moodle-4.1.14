<?php
$functions = [
    'local_th_feedback_report_error_categories' => [
        'classname'   => '\local_th_feedback_report\external\error_categories',
        'methodname'  => 'execute',
        'description' => 'Get error categories list',
        'type'        => 'read',
        'ajax'        => true,
        'services' => ['moodle_mobile_app'],
    ],

    'local_th_feedback_report_save_feedback' => [
        'classname'   => '\local_th_feedback_report\external\save_feedback',
        'methodname'  => 'execute',
        'description' => 'Lưu thông tin feedback của người dùng',
        'type'        => 'write',
        'ajax'        => true,
        'services' => ['moodle_mobile_app'],
    ],
];