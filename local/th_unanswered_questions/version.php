<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'local_th_unanswered_questions';
$plugin->version      = 2026012000; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->release   = 'v3.10-r1';
$plugin->requires  = 2020110900;
$plugin->dependencies = array(
    'mod_qaa' => '2025122400',
);
$plugin->maturity  = MATURITY_STABLE;
