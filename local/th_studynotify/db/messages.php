<?php
defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    'course_opened' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
    ],
    'inactive_students' => [
        'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN,
        'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
    ],
    'quiz_kt_reminder' => [
        'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN,
        'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
    ],
    'weekly_study' => [
        'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN,
        'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
]
];


