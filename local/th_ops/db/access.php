<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/th_ops:can_view_all_roles' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW, // Cho phép mặc định với manager
        ),
        'clonepermissionsfrom' => 'moodle/role:manage',
    ),

    'local/th_ops:create_thc_ops_service_token' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW, // hoặc thêm các role khác nếu muốn
        ),
        'clonepermissionsfrom' => 'webservice/rest:use',
    ),

);
