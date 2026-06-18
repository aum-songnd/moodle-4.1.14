<?php

$hour = get_config('thurl', 'task_hour');
$minute = get_config('thurl', 'task_minute');

$tasks = [
    [
        'classname' => 'mod_thurl\task\send_notification',
        'blocking' => 0,
        'minute' => $minute,
        'hour' => $hour,
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*', 
    ],
];