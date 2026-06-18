<?php
$capabilities = array(
	'local/th_bulk_enrolment_manager:view' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	)
);