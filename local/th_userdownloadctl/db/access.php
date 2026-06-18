<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/th_userdownloadctl:download' => [
        'riskbitmask'  => RISK_PERSONAL,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [
            'manager' => CAP_PREVENT,
        ],
    ],
];
