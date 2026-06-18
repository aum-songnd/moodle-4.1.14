<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => '\local_th_activity_report_by_email\task\send_report',
		'blocking' => 0,
		'minute' => '0',
		'hour' => '4',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '1',
		'disabled' => 0,
	),
);
