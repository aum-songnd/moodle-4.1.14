<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$settings = new admin_settingpage('local_th_shuffle_answers', get_string('pluginname', 'local_th_shuffle_answers', null, true));

        $setting = new admin_setting_configcheckbox('local_th_shuffle_answers/shuffleanswers',
                get_string('setting_shuffle_choices', 'local_th_shuffle_answers'),
                get_string('setting_shuffle_choices_desc', 'local_th_shuffle_answers'),
                1);
        $settings->add($setting);

	$ADMIN->add('qbehavioursettings', $settings);
}
