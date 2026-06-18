<?php

$functions = [
    'local_th_online_exam_manager_update_status' => [
        'classname' => 'local_th_online_exam_manager_external',
        'methodname' => 'update_status',
        'classpath' => 'local/th_online_exam_manager/external.php',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/th_online_exam_manager:view',
    ],

    'local_th_online_exam_manager_load_quizzes' => [
        'classname'   => 'local_th_online_exam_manager_external',
        'methodname'  => 'load_quizzes',
        'classpath'   => 'local/th_online_exam_manager/external.php',
        'description' => 'Load list of quizzes in a course',
        'type'        => 'read',
        'ajax'        => true,
    ],

    'local_th_online_exam_manager_load_attempts' => [
        'classname'   => 'local_th_online_exam_manager_external',
        'methodname'  => 'load_attempts',
        'classpath'   => 'local/th_online_exam_manager/external.php',
        'description' => 'Load attempts by quiz ID',
        'type'        => 'read',
        'ajax'        => true,
    ],
];
