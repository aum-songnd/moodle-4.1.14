<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
	'local/th_random_test_enrolment:enrolusers' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
	),
);
