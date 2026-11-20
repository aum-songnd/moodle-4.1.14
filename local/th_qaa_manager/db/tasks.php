<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\local_th_qaa_manager\task\weekly_question_notify_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '9',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '1',
    ],
    [
        'classname' => '\local_th_qaa_manager\task\auto_recall_assignments_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => '\local_th_qaa_manager\task\daily_answer_notify_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '15',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];