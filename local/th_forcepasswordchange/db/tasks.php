<?php


defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => '\local_th_forcepasswordchange\task\user_bulk_forcepasswordchange',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*',
		'day' => '*',
		'month' => '6',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
        'classname' => '\local_th_forcepasswordchange\task\password_reset_cleanup_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*/6',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
