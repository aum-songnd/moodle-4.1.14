<?php
$functions = [
    'th_create_update_framework_study' => [
        'classname'   => 'local_th_framework_study\external\create_update_framework',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'API tạo/cập nhật khung chương trình học',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/th_framework_study:manageframeworkstudy',
        'services' => ['th_ops_service'],
    ]
];
