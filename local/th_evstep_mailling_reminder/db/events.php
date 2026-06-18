<?php 
$observers = [
    [
        'eventname'   => '\core\event\config_log_created',
        'callback'    => '\local_th_evstep_mailling_reminder\observer::config_schedule_task',
    ],
    [
        'eventname' => '\block_th_assign_grading\event\approval_completed',
        'callback' => '\local_th_evstep_mailling_reminder\observer::on_approval_completed',
    ]
]


?>