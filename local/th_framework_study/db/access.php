<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/th_framework_study:manageframeworkstudy' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/th_framework_study:manageexemption' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/th_framework_study:viewmyown' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'student' => CAP_ALLOW
        )
    ),

    'local/th_framework_study:viewothers' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
);