<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	$settings->add(new admin_setting_configcheckbox(
		'block_th_manage_activatecourses/sendmail',
		get_string('setting_sendmail', 'block_th_manage_activatecourses'),
		get_string('setting_des_sendmail', 'block_th_manage_activatecourses'),
		0));
}
