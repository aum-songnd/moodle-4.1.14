<?php
$capabilities = array(

	'mod/qaa:addinstance' => array(
		'riskbitmask' => RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
		'clonepermissionsfrom' => 'moodle/course:manageactivities',
	),

	'mod/qaa:view' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'guest' => CAP_ALLOW,
			'student' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),

	'mod/qaa:makequest' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
			'student' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
		),
	),

	'mod/qaa:deletequest' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
			'student' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
		),
	),

	'mod/qaa:approval' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
		),
	),

	'mod/qaa:manage' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'manager' => CAP_ALLOW
		),
	),

);