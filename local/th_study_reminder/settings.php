<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_study_reminder', new lang_string('pluginname', 'local_th_study_reminder'));
	$ADMIN->add('localplugins', $settings);

    $configs = array();
    $configs[] = new admin_setting_heading('local_th_study_reminder/sr_general',
    new lang_string('settings_general', 'local_th_study_reminder'), '');

    $configs[] = new admin_setting_configcheckbox('local_th_study_reminder/enable_notifications',
    new lang_string('enablenotifications', 'local_th_study_reminder'), 
    new lang_string('enablenotifications_desc', 'local_th_study_reminder'),1);

    $configs[] = new admin_setting_configtextarea('local_th_study_reminder/config_notification_content',
    new lang_string('notification_content_setting', 'local_th_study_reminder'),
    new lang_string('notification_content_setting_description', 'local_th_study_reminder'), 
    new lang_string('notification_content_default', 'local_th_study_reminder'), PARAM_TEXT);

    $configs[] = new admin_setting_configtext('local_th_study_reminder/config_notification_hour_send',
    new lang_string('notification_hour_send_setting', 'local_th_study_reminder'),
    new lang_string('notification_hour_send_setting_description', 'local_th_study_reminder'), 8, PARAM_INT);

    $configs[] = new admin_setting_configtextarea('local_th_study_reminder/config_ktck',
    new lang_string('ktck_shortname_setting', 'local_th_study_reminder'),
    new lang_string('ktck_shortname_setting_description', 'local_th_study_reminder'), 'ktck', PARAM_TEXT);
    
    $configs[] = new admin_setting_configtext('local_th_study_reminder/config_point',
    new lang_string('point_setting', 'local_th_study_reminder'),
    new lang_string('point_setting_description', 'local_th_study_reminder'), 5, PARAM_INT);

    $configs[] = new admin_setting_heading('local_th_study_reminder/sr_inactive',
    new lang_string('settings_inactive_users', 'local_th_study_reminder'), '');

    $configs[] = new admin_setting_configcheckbox('local_th_study_reminder/enable_notify_inactive_users',
    new lang_string('enablenotifyinactiveusers', 'local_th_study_reminder'), 
    new lang_string('enablenotifyinactiveusers_desc', 'local_th_study_reminder'),1);

    $configs[] = new admin_setting_configtext('local_th_study_reminder/config_notify_inactive_hour',
    new lang_string('notify_inactive_hour_setting', 'local_th_study_reminder'),
    new lang_string('notify_inactive_hour_desc', 'local_th_study_reminder'), 14, PARAM_INT);

    $configs[] = new admin_setting_heading('local_th_study_reminder/sr_progress',
    new lang_string('settings_completion_progress', 'local_th_study_reminder'), '');

    $configs[] = new admin_setting_configcheckbox('local_th_study_reminder/enable_notify_completion_progress',
    new lang_string('enablenotifycompletionprogress', 'local_th_study_reminder'), 
    new lang_string('enablenotifycompletionprogress_desc', 'local_th_study_reminder'),1);

    $configs[] = new admin_setting_configtext('local_th_study_reminder/config_notify_completion_progress_hour',
    new lang_string('notify_completion_progress_setting', 'local_th_study_reminder'),
    new lang_string('notify_completion_progress_desc', 'local_th_study_reminder'), 20, PARAM_INT);

    $configs[] = new admin_setting_configtextarea('local_th_study_reminder/standard_months_map_cb',
    new lang_string('standard_months_map_cb', 'local_th_study_reminder'),
    new lang_string('standard_months_map_cb_desc', 'local_th_study_reminder'),
    "VSTEP.A0:18\nVSTEP.A1:15\nVSTEP.A2:10");

    $configs[] = new admin_setting_configtextarea('local_th_study_reminder/standard_months_map_ct',
    new lang_string('standard_months_map_ct', 'local_th_study_reminder'),
    new lang_string('standard_months_map_ct_desc', 'local_th_study_reminder'),
    "VSTEP.A0:9.5\nVSTEP.A1:6.5\nVSTEP.A2:4");

    $configs[] = new admin_setting_configtext('local_th_study_reminder/config_finalquiz',
    new lang_string('finalquiz_setting', 'local_th_study_reminder'),
    new lang_string('finalquiz_setting_description', 'local_th_study_reminder'), 'kt,ess', PARAM_TEXT);

    //add config
	foreach ($configs as $config) {
		$settings->add($config);
	}
}