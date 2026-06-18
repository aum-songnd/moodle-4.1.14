<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/th_syncexamqbank:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
    ],
];
