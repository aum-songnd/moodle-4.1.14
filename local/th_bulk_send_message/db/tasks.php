<?php
$time_run = get_config('local_th_bulk_send_message', 'time_run');

if(empty($time_run)){
    $time_run = '22';
}

$tasks = [
    [
        'classname' => 'local_th_bulk_send_message\task\auto_delete_log_send_message',
        'blocking' => 0,
        'minute' => '00',
        'hour' => $time_run,
        'day' => '*',
        'month' => '*',
        'dayofweek' => '1',
    ],
];