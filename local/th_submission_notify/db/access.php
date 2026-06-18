<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/th_submission_notify:select_course' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ]
    ]

];
