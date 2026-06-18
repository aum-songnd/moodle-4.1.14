<?php
    $tasks = [
        [
            'classname' => 'local_th_daily_grade_update\task\daily_update_custom_grades',
            'blocking' => 0,
            'minute' => '0',
            'hour' => '3',
            'day' => '*',
            'dayofweek' => '1,3,5',
            'month' => '*',
        ],
        [
            'classname' => 'local_th_daily_grade_update\task\daily_update_qaa_grades',
            'blocking' => 0,
            'minute' => '5',
            'hour' => '3',
            'day' => '*',
            'dayofweek' => '1,3,5',
            'month' => '*',
        ],
    ];
?>
