<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/th_daily_grade_update:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
