<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/th_export_quiz_batch:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
