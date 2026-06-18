<?php
$time_run = get_config('local_th_log_email', 'time_run');

if(empty($time_run)){
    $time_run = '22';
}

$tasks = [
    [
        'classname' => 'local_th_log_email\task\auto_delete_log_email',
        'blocking' => 0,
        'minute' => '00',
        'hour' => $time_run,
        'day' => '*',
        'month' => '*',
        'dayofweek' => '1',
    ],
];