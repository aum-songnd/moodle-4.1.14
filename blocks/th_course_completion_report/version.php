<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023051200;
$plugin->requires = 2020110300;
$plugin->component = 'block_th_course_completion_report';
$plugin->dependencies = array(
	'local_thlib' => '2021100000',
);
