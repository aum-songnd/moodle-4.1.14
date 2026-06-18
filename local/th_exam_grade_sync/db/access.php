<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/th_exam_grade_sync:manage' => [
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
