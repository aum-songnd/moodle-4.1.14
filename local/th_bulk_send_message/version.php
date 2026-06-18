<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_th_bulk_send_message';
$plugin->version = 2026042200; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
	'local_thlib' => '2023052700',
);
$plugin->release = 'v3.10-r1';
$plugin->requires = 2020110900;
$plugin->supported = [310, 310];
$plugin->maturity = MATURITY_STABLE;
