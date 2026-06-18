<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
	'local/th_studynotify:view' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
);