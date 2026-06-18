<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_th_submission_notify\task\send_notifications',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '9,15',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 0,
    ],
];
