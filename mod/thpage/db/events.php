<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(

    array (
        'eventname' => '\mod_thpage\event\course_thpage_created',
        'callback'  => 'mod_thpage_observer::pdf_created',
        'internal'  => false,
        'priority'  => 1000,
    ),

    array (
        'eventname' => '\mod_thpage\event\course_thpage_updated',
        'callback'  => 'mod_thpage_observer::pdf_created',
        'internal'  => false,
        'priority'  => 1000,
    ),
);