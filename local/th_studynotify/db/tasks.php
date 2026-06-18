<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\local_th_studynotify\task\notify_course_opened',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '1',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => '\local_th_studynotify\task\notify_inactive_students',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '7',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => '\local_th_studynotify\task\notify_quiz_kt_reminder',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '1',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => '\local_th_studynotify\task\notify_weekly_study',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '7',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ]
];
