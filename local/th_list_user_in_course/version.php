<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'local_th_list_user_in_course';
$plugin->version      = 2023032100; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
    'local_thlib' => '2021100000',
    'block_th_activatecourses' => '2021102500',
    'block_th_manage_activatecourses' => '2021103010'
);
$plugin->release   = 'v3.10-r1';
$plugin->requires  = 2020110900;
$plugin->supported = [310, 310];
$plugin->maturity  = MATURITY_STABLE;
