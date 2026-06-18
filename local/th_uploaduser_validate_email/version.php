<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_th_uploaduser_validate_email';
$plugin->version   = 2026051500;
$plugin->requires  = 2020110900;
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.8';
$plugin->dependencies = array(
	'local_thlib' => '2025061301',
);
