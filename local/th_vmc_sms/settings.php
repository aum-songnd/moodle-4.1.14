<?php
// This file is part of local_thlib for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings page
 *
 * @package       local_thlib
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

	$settings = new admin_settingpage('local_th_vmc_sms', get_string('pluginname', 'local_th_vmc_sms'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$configs[] = new admin_setting_heading('local_th_vmc_sms/heading1', get_string('setting_timecourse', 'local_th_vmc_sms'), '');

	$configs[] = new admin_setting_configtext('local_th_vmc_sms/timecourse',
		get_string('setting_timecourse', 'local_th_vmc_sms'),
		get_string('setting_timecourse', 'local_th_vmc_sms'),
		90, PARAM_INT, 5);

	$configs[] = new admin_setting_heading('local_th_vmc_sms/heading2', get_string('setting_tin_nhan2', 'local_th_vmc_sms'), '');

	$configs[] = new \local_th_vmc_sms\th_custom_admin_setting_configcheckbox('local_th_vmc_sms/tinnhan2',
		get_string('setting_tin_nhan2', 'local_th_vmc_sms'),
		get_string('sendsms', 'local_th_vmc_sms'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_sms/songayguitinnhan2',
		get_string('setting_daysend', 'local_th_vmc_sms'),
		get_string('setting_daysend', 'local_th_vmc_sms'),
		20, PARAM_INT, 5);

	$configs[] = new admin_setting_heading('local_th_vmc_sms/heading3', get_string('setting_tin_nhan3', 'local_th_vmc_sms'), '');

	$configs[] = new \local_th_vmc_sms\th_custom_admin_setting_configcheckbox('local_th_vmc_sms/tinnhan3',
		get_string('setting_tin_nhan3', 'local_th_vmc_sms'),
		get_string('sendsms', 'local_th_vmc_sms'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_sms/songayguitinnhan3',
		get_string('setting_daysend', 'local_th_vmc_sms'),
		get_string('setting_daysend', 'local_th_vmc_sms'),
		50, PARAM_INT, 5);

	$configs[] = new admin_setting_heading('local_th_vmc_sms/heading4', get_string('setting_tin_nhan4', 'local_th_vmc_sms'), '');

	$configs[] = new \local_th_vmc_sms\th_custom_admin_setting_configcheckbox('local_th_vmc_sms/tinnhan4',
		get_string('setting_tin_nhan4', 'local_th_vmc_sms'),
		get_string('sendsms', 'local_th_vmc_sms'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_sms/songayguitinnhan4',
	get_string('setting_daysend', 'local_th_vmc_sms'),
	get_string('setting_daysend', 'local_th_vmc_sms'),
	80, PARAM_INT, 5);

	$configs[] = new admin_setting_heading('local_th_vmc_sms/heading_smsapi', get_string('setting_smsapi', 'local_th_vmc_sms'), '');

	$configs[] = new admin_setting_configtext('local_th_vmc_sms/sms_api_url',
		get_string('setting_sms_api_url', 'local_th_vmc_sms'),
		get_string('setting_sms_api_url_desc', 'local_th_vmc_sms'),
		'https://api.brandsms.vn/api/SMSBrandname/SendSMS', PARAM_RAW_TRIMMED);
	
	$configs[] = new admin_setting_configtext('local_th_vmc_sms/sms_api_token',
		get_string('setting_sms_api_token', 'local_th_vmc_sms'),
		get_string('setting_sms_api_token_desc', 'local_th_vmc_sms'),
		'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c24iOiJ2bWN2aWV0bmFtIiwic2lkIjoiMzU2NDcyMGEtY2YyMS00ODdjLWI2ODAtNjJkMmNlN2M4MWIyIiwib2J0IjoiIiwib2JqIjoiIiwibmJmIjoxNjE4Mzg0MTcwLCJleHAiOjE2MTgzODc3NzAsImlhdCI6MTYxODM4NDE3MH0.vhjeYR5f-reWiPZyxnc3q-1KkLC4r4aNlFXFzaxWkqM', PARAM_RAW_TRIMMED);
	
	$configs[] = new admin_setting_configtext('local_th_vmc_sms/sms_brandname',
		get_string('setting_sms_brandname', 'local_th_vmc_sms'),
		get_string('setting_sms_brandname_desc', 'local_th_vmc_sms'),
		'VMC VIETNAM', PARAM_TEXT);
		
	//add config
	foreach ($configs as $config) {
		$config->pin = 'local_thlib';
		$settings->add($config);
	}

}
