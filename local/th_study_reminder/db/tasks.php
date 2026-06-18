<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => '\local_th_study_reminder\task\send_study_reminder',
		'blocking' => 0,
		'minute' => '0',
		'hour' => '8',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),
	array(
        'classname' => '\local_th_study_reminder\task\notify_inactive_users',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '14',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
	),
	array(
        'classname' => '\local_th_study_reminder\task\notify_completion_progress',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '20',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
	),
);