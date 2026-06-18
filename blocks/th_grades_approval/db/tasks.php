<?php

$time_send = get_config('block_th_grades_approval', 'time_send_mail');

$tasks = [
    [
        'classname' => 'block_th_grades_approval\task\send_mail_grades_approval',
        'blocking' => 0,
        'minute' => '00',
        'hour' => $time_send,
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];