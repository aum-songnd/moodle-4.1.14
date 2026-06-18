<?php
    defined('MOODLE_INTERNAL') || die();
    $messageproviders = [
        // Notify student
        'reminder' => [
            'defaults' => [
                'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
                'email' => MESSAGE_PERMITTED,
                'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            ],
        ],
        'notify_inactive_users' => [
            'defaults' => [
                'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
                'email' => MESSAGE_PERMITTED,
                'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            ],
        ],

        'completion_notify' => [
            'defaults' => [
                'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
                'email' => MESSAGE_PERMITTED,
                'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            ],
        ],
    ];
?>