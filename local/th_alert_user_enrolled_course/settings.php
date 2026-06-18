<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

	$settings = new admin_settingpage('th_alert_user_enrolled_course', get_string('pluginname', 'local_th_alert_user_enrolled_course'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$options = [
		0 => get_string('send_email_per_enrollment', 'local_th_alert_user_enrolled_course'),
		1 => get_string('send_email_daily_at_22', 'local_th_alert_user_enrolled_course'),
	];	

	$configs[] = new admin_setting_configselect(
		'th_alert_user_enrolled_course/alert_user_enrolled',
		get_string('choose_email_method', 'local_th_alert_user_enrolled_course'),
		'',
		'',
		$options
	);	

	//add config
	foreach ($configs as $config) {
		$config->plugin = 'th_alert_user_enrolled_course';
		$settings->add($config);
	}
}
