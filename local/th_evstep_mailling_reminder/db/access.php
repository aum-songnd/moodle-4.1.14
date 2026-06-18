<?php
$capabilities = array(
	'local/th_evstep_mailling_reminder:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
);