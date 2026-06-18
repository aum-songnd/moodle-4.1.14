<?php
$capabilities = array(
    'local/th_roadmap_vstep:import' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),

     'local/th_roadmap_vstep:export' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ),
    ),
);
