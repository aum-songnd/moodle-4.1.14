<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => '\local_th_evstep_productivity_report\task\send_report',
		'blocking' => 0,
		'minute' => '0',
		'hour' => '2',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '1',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_evstep_productivity_report\task\send_month_report',
		'blocking' => 0,
		'minute' => '0',
		'hour' => '2',
		'day' => '1',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),
);
