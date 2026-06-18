<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	$configs = array();

	$configs[] = new admin_setting_configtext('block_th_course_completion_report/idnumbercategory',
		get_string('idnumber', 'block_th_course_completion_report'),
		get_string('des_idnumber', 'block_th_course_completion_report'),
		'', PARAM_RAW, 20);

	$configs[] = new admin_setting_configtext(
		'block_th_course_completion_report/date',
		get_string('labelallowhtml', 'block_th_course_completion_report'),
		get_string('descallowhtml', 'block_th_course_completion_report'),
		365,
		PARAM_INT
	);

	//add config
	foreach ($configs as $config) {
		$config->plugin = 'block_th_course_completion_report';
		$settings->add($config);
	}
}
