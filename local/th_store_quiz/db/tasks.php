<?php

$time_run = get_config('local_th_store_quiz', 'time_run');
$day_run = get_config('local_th_store_quiz', 'day_run');

if(empty($time_run)){
    $time_run = '2';
}

if(empty($day_run)){
    $day_run = '2';
}
$tasks = [
    [
        'classname' => 'local_th_store_quiz\task\auto_delete_quiz',
        'blocking' => 0,
        'minute' => '0',
        'hour' => $time_run,
        'day' => '*',
        'month' => '*',
        'dayofweek' => $day_run,
    ],
];
