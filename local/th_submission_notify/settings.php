<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_submission_notify', get_string('pluginname', 'local_th_submission_notify'));
	$ADMIN->add('localplugins', $settings);

    $configs = array();

    $configs[] = new admin_setting_configcheckbox('local_th_submission_notify/enable_notifications',
    new lang_string('enablenotifications', 'local_th_submission_notify'), 
    new lang_string('enablenotifications_desc', 'local_th_submission_notify'),1);

    $configs[] = new admin_setting_configtext('local_th_submission_notify/hour_send',
    new lang_string('hour_send_setting', 'local_th_submission_notify'),
    new lang_string('hour_send_setting_desc', 'local_th_submission_notify'), '9,15', PARAM_RAW);

    $configs[] = new admin_setting_configtextarea('local_th_submission_notify/emaillist',
    new lang_string('emaillist', 'local_th_submission_notify'),
    new lang_string('emaillist_desc', 'local_th_submission_notify'), 
    '', PARAM_TEXT);

    $configs[] = new admin_setting_configtextarea('local_th_submission_notify/config_averagescore',
    new lang_string('averagescore_shortname_setting', 'local_th_submission_notify'),
    new lang_string('averagescore_shortname_setting_description', 'local_th_submission_notify'), 'trinhdotrungbinh', PARAM_TEXT);

    //add config
	foreach ($configs as $config) {
		$settings->add($config);
	}
}
