<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

	$settings = new admin_settingpage('local_th_alert_create_user', get_string('pluginname', 'local_th_alert_create_user'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$configs[] = new admin_setting_heading('local_th_alert_create_user/heading1', get_string('setting_heading', 'local_th_alert_create_user'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_alert_create_user/uselink',
		get_string('setting_createpassword', 'local_th_alert_create_user'),
		get_string('des_setting_createpassword', 'local_th_alert_create_user'),
		1);

	//add config
	foreach ($configs as $config) {
		$config->pin = 'local_thlib';
		$settings->add($config);
	}

}
