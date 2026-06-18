<?php


defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'report/th_courseopening:view' => [
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
    ]
];