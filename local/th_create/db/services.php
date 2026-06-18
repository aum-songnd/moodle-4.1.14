<?php


defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_th_create_create_users' => [
        'classname' => 'local_th_create_external',
        'methodname' => 'create_users',
        'classpath' => 'local/th_create/externallib.php',
        'description' => 'Create user',
        'type' => 'write',
        'ajax' => true,
    ],
];

$services = [
    'Local TH Create users API' => [
        'functions' => [
            'local_th_create_create_users',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];