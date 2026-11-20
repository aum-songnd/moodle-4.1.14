<?php

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_th_qaa_manager_load_courses' => [
        'classname'   => 'local_th_qaa_manager\external\load_courses',
        'classpath'   => 'local/th_qaa_manager/classes/external/load_courses.php',
        'description' => 'Load courses for qaa manager autocomplete filter',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
    ],
];
