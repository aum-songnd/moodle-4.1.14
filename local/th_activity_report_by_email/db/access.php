<?php
$capabilities = array(
	'local/th_activity_report_by_email:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
);