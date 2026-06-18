<?php


defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_th_create_upload_create_user_upload' => [
        'classname' => 'local_th_create_upload\external\local_th_create_user_upload_external',
        'methodname' => 'create_user_upload',
        'classpath' => 'local/th_create_upload/externallib.php',
        'description' => 'Create user',
        'type' => 'write',
        'ajax' => true,
    ],
];

$services = [
    'local_th_create_user_upload_service' => [
        'functions' => [
            'local_th_create_upload_create_user_upload',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];