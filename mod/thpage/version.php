<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025041806;
$plugin->requires  = 2020110300;
$plugin->component = 'mod_thpage';
$plugin->cron      = 0;
$plugin->dependencies = array(
  	'local_thlib' => '2023121100',
);