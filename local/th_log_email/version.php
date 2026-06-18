<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'local_th_log_email';
$plugin->version      = 2025062302; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
    'local_thlib' => '2021100000',
);
$plugin->release   = 'v3.10-r3';
$plugin->requires  = 2020110900;
$plugin->supported = [310, 410];
$plugin->maturity  = MATURITY_STABLE;
