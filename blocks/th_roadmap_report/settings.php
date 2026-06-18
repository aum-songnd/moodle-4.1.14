<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$settings->add(new admin_setting_configtext('block_th_roadmap_report/idnumber',
		get_string('idnumbercoursecategory', 'block_th_roadmap_report'),
		get_string('idnumbercoursecategory_desc', 'block_th_roadmap_report'), 'ktdv', PARAM_TEXT));
}
