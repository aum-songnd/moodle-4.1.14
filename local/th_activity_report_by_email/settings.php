<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

	$settings = new admin_settingpage('local_th_activity_report_by_email', get_string('pluginname', 'local_th_activity_report_by_email'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$configs[] = new admin_setting_heading('local_th_activity_report_by_email/heading1', get_string('setting_heading', 'local_th_activity_report_by_email'), '');

	$configs[] = new \local_th_activity_report_by_email\th_custom_admin_setting_configcheckbox('local_th_activity_report_by_email/sendreport',
		get_string('setting_sendreport', 'local_th_activity_report_by_email'),
		get_string('sendreport', 'local_th_activity_report_by_email'),
		1);

	$configs[] = new admin_setting_configtext('local_th_activity_report_by_email/email',
		get_string('activity_tracking_email', 'local_th_activity_report_by_email'),
		get_string('des_activity_tracking_email', 'local_th_activity_report_by_email'),
		'', PARAM_RAW, 20);

	$configs[] = new admin_setting_configtext('local_th_activity_report_by_email/list_email',
		get_string('list_email', 'local_th_activity_report_by_email'),
		get_string('des_list_email', 'local_th_activity_report_by_email'),
		'', PARAM_RAW, 50);

	//add config
	foreach ($configs as $config) {
		$config->pin = 'local_thlib';
		$settings->add($config);
	}

}
