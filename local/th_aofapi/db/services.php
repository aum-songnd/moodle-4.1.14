<?php

$services = array(
	'th_aofapi' => array( //the name of the web service
		'functions' => array('aofapi_getScore'), //web service functions of this service
		'requiredcapability' => '', //if set, the web service user need this capability to access
		//any function of this service. For example: 'some/capability:specified'
		'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
		//into the administration
		'enabled' => 1, //if enabled, the service can be reachable on a default installation
		'shortname' => 'th_aofapi', //the short name used to refer to this service from elsewhere including when fetching a token
	),
);

$functions = array(
	'aofapi_getScore' => array(
		'classname' => 'local_th_aofapi_external',
		'methodname' => 'getScore',
		'classpath' => 'local/th_aofapi/externallib.php',
		'description' => 'fetch score data',
		'type' => 'read',
		'ajax' => true,
		'loginrequired' => true,
	),

);
