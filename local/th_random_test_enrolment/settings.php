<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$settings = new admin_settingpage('local_th_random_test_enrolment', get_string('pluginname', 'local_th_random_test_enrolment', null, true));

	if ($ADMIN->fulltree) {

		// Create enrolment chooser widget.
		$enroloptions = array();
		foreach (enrol_get_plugins(true) as $name => $plugin) {
			$enroloptions[$name] = get_string('pluginname', 'enrol_' . $name);
		}
		$settings->add(
			new admin_setting_configselect(
				'local_th_random_test_enrolment/enrolplugin',
				get_string('enrolplugin', 'local_th_random_test_enrolment'),
				get_string('enrolplugin_desc', 'local_th_random_test_enrolment'),
				'',
				$enroloptions)
		);
		unset($enroloptions);

		// Create role chooser widget.
		$roleoptions = array();
		// Get some basic data we are going to need.
		$roles = get_all_roles();
		$systemcontext = context_system::instance();
		$rolenames = role_fix_names($roles, $systemcontext, ROLENAME_ORIGINAL);
		if (!empty($rolenames)) {
			foreach ($rolenames as $key => $role) {
				if (!array_key_exists($role->id, $roleoptions)) {
					$roleoptions[$role->id] = $role->localname;
				}
			}
		}
		// Get first default role for 'student' archetype.
		$studentarchetype = get_archetype_roles('student');
		if ($studentarchetype != false && count($studentarchetype) > 0) {
			$firststudentrole = array_shift($studentarchetype);
			$firststudentroleid = $firststudentrole->id;
		} else {
			$firststudentroleid = '';
		}
		$settings->add(
			new admin_setting_configselect(
				'local_th_random_test_enrolment/role',
				get_string('role', 'local_th_random_test_enrolment'),
				get_string('role_description', 'local_th_random_test_enrolment'),
				$firststudentroleid,
				$roleoptions)
		);
		unset($roleoptions);

		$settings->add(
		new admin_setting_configtext(
			'local_th_random_test_enrolment/groups',
			get_string('group', 'local_th_random_test_enrolment'),
			get_string('group_description', 'local_th_random_test_enrolment'),
			'Listening,Reading,Writing,Speaking',
			PARAM_RAW,
			100)
		);
	}

	$ADMIN->add('enrolments', $settings);
}
