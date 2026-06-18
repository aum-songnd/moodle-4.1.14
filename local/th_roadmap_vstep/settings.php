<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$settings = new admin_settingpage('local_th_roadmap_vstep', get_string('name', 'local_th_roadmap_vstep', null, true));

	if ($ADMIN->fulltree) {

		$settings->add(
		new admin_setting_configtext(
			'local_th_roadmap_vstep/listening',
			get_string('listening', 'local_th_roadmap_vstep'),
			get_string('listening_desc', 'local_th_roadmap_vstep'),
			'listening',
			PARAM_RAW,
			10)
		);

		$settings->add(
		new admin_setting_configtext(
			'local_th_roadmap_vstep/reading',
			get_string('reading', 'local_th_roadmap_vstep'),
			get_string('reading_desc', 'local_th_roadmap_vstep'),
			'reading',
			PARAM_RAW,
			10)
		);

		$settings->add(
		new admin_setting_configtext(
			'local_th_roadmap_vstep/speaking',
			get_string('speaking', 'local_th_roadmap_vstep'),
			get_string('speaking_desc', 'local_th_roadmap_vstep'),
			'speaking',
			PARAM_RAW,
			10)
		);

		$settings->add(
		new admin_setting_configtext(
			'local_th_roadmap_vstep/writing',
			get_string('writing', 'local_th_roadmap_vstep'),
			get_string('writing_desc', 'local_th_roadmap_vstep'),
			'writing',
			PARAM_RAW,
			10)
		);

		$settings->add(
		new admin_setting_configtext(
			'local_th_roadmap_vstep/target',
			get_string('target', 'local_th_roadmap_vstep'),
			get_string('target_desc', 'local_th_roadmap_vstep'),
			'target',
			PARAM_RAW,
			10)
		);

		$url = new moodle_url('/local/th_roadmap_vstep/view.php');
	    $item = new admin_setting_heading('local_th_roadmap_vstep/about',
	        '',
	        new lang_string('linkupload', 'local_th_roadmap_vstep', $url->out()));
	    $settings->add($item);
		}

	$ADMIN->add('localplugins', $settings);
}
