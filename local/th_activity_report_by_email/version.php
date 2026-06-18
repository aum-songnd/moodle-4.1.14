<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_th_activity_report_by_email';
$plugin->release = '1.0.1';
$plugin->version = 2025032502; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
	'block_th_activatecourses' => '2021100000',
);
