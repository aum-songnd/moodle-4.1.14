<?php 
$observers = [
    [
        'eventname'   => '\core\event\config_log_created',
        'callback'    => '\local_th_study_reminder\observer::config_schedule_task',
    ]
]


?>