<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_th_productivity_report';
$plugin->release = '1.0.1';
$plugin->version = 2023052701; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
	'block_th_activatecourses' => '2021100000',
);
