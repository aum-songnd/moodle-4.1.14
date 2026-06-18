<?php
$capabilities = array(
	'local/th_study_reminder:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
);