<?php

// $services = array(
// 	'th_managelogin' => array( //the name of the web service
// 		'functions' => array('local_th_managelogin_save_userinfo'), //web service functions of this service
// 		'requiredcapability' => '', //if set, the web service user need this capability to access
// 		//any function of this service. For example: 'some/capability:specified'
// 		'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
// 		//into the administration
// 		'enabled' => 1, //if enabled, the service can be reachable on a default installation
// 		'shortname' => 'th_managelogin', //the short name used to refer to this service from elsewhere including when fetching a token
// 	),
// );

$functions = array(
	'local_th_managelogin_save_userinfo' => array(
		'classname' => 'local_th_managelogin_external',
		'methodname' => 'save_userinfo',
		'classpath' => 'local/th_managelogin/externallib.php',
		'description' => 'Save user login Info',
		'type' => 'write',
		'loginrequired' => true,
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),
	'local_th_managelogin_checklogin_valid' => array(
		'classname' => 'local_th_managelogin_external',
		'methodname' => 'checklogin_valid',
		'classpath' => 'local/th_managelogin/externallib.php',
		'description' => 'check if login is valid',
		'type' => 'write',
		'loginrequired' => true,
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
	),

);
