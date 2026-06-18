<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_evstep_mailling_reminder', get_string('pluginname', 'local_th_evstep_mailling_reminder'));
	$ADMIN->add('localplugins', $settings);

    $configs = array();

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_assign_turns',
    new lang_string('defaultassignturnssetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultassignturnssettingdescription', 'local_th_evstep_mailling_reminder'), 22, PARAM_RAW);

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_attemps_need_to_grading',
    new lang_string('defaultneedtogradingsetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultneedtogradingsettingdescription', 'local_th_evstep_mailling_reminder'), 9, PARAM_RAW);

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_attemps_to_grading',
    new lang_string('defaultattempstogradingsetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultattempstogradingsettingdescription', 'local_th_evstep_mailling_reminder'), 18, PARAM_RAW);

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_ctv_finished_grading',
    new lang_string('defaultctvfinishedgradingsetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultctvfinishedgradingsettingdescription', 'local_th_evstep_mailling_reminder'), 15, PARAM_RAW);

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_grading_approved',
    new lang_string('defaultgradingapprovedsetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultgradingapprovedsettingdescription', 'local_th_evstep_mailling_reminder'), 18, PARAM_RAW);

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_user_enrolled',
    new lang_string('defaultuserenrolledssetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultuserenrolledssettingdescription', 'local_th_evstep_mailling_reminder'), 22, PARAM_RAW);

    $configs[] = new admin_setting_configtext('local_th_evstep_mailling_reminder/config_collaborator_manager',
    new lang_string('defaultqtvssetting', 'local_th_evstep_mailling_reminder'),
    new lang_string('defaultqtvssettingdescription', 'local_th_evstep_mailling_reminder'), 'collaborator_manager', PARAM_TEXT);


    //add config
	foreach ($configs as $config) {
		$settings->add($config);
	}
}