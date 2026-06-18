<?php

defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => 'core\event\question_created',
        'callback' => 'local_th_shuffle_answers_observer::updated',
        'priority' => 1000,
        'internal' => false,
    ),

    array(
        'eventname' => '\core\event\question_updated',
        'callback' => 'local_th_shuffle_answers_observer::updated',
        'priority' => 1000,
        'internal' => false,
    ),
);
