<?php

$services = array(
	'th_tnuapi' => array( //the name of the web service
		'functions' => array('tnuapi_xet_dkdt'), //web service functions of this service
		'requiredcapability' => '', //if set, the web service user need this capability to access
		//any function of this service. For example: 'some/capability:specified'
		'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
		//into the administration
		'enabled' => 1, //if enabled, the service can be reachable on a default installation
		'shortname' => 'th_tnuapi', //the short name used to refer to this service from elsewhere including when fetching a token
	),
);

$functions = array(
	'tnuapi_xet_dkdt' => array(
		'classname' => 'local_th_tnuapi_external',
		'methodname' => 'xet_dkdt',
		'classpath' => 'local/th_tnuapi/externallib.php',
		'description' => 'fetch dkdt data',
		'type' => 'read',
		'ajax' => true,
		'loginrequired' => true,
	),

);
