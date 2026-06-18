<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023121401; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires = 2020110300; // Requires this Moodle version
$plugin->component = 'block_th_roadmap_report'; // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array(
    'local_thlib' => '2021100000',
    'local_th_roadmap_vstep' => '2023110300'
);
