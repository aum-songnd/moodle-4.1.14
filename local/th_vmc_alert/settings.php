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

	$settings = new admin_settingpage('local_th_vmc_alert', get_string('pluginname', 'local_th_vmc_alert'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();
//
	$configs[] = new admin_setting_configtext('local_th_vmc_alert/siteprefix',
			get_string('siteprefix', 'local_th_vmc_alert'),
			get_string('siteprefix_des', 'local_th_vmc_alert'),
			'[OM\'E]', PARAM_TEXT, 50);
	$configs[] = new admin_setting_heading('local_th_vmc_alert/headingalertlogin', get_string('setting_alertlogin', 'local_th_vmc_alert'), '');

	$configs[] = new \local_th_vmc_alert\th_custom_admin_setting_configcheckbox('local_th_vmc_alert/alertlogin',
		get_string('setting_alertlogin', 'local_th_vmc_alert'),
		get_string('alertlogindesc', 'local_th_vmc_alert'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_alert/dayalertlogin',
		get_string('setting_dayalertlogin', 'local_th_vmc_alert'),
		get_string('setting_dayalertlogin', 'local_th_vmc_alert'),
		2, PARAM_INT, 5);

//
	$configs[] = new admin_setting_heading('local_th_vmc_alert/headingalertlearning', get_string('setting_alertlearning', 'local_th_vmc_alert'), '');

	$configs[] = new \local_th_vmc_alert\th_custom_admin_setting_configcheckbox('local_th_vmc_alert/alertlearning',
		get_string('setting_alertlearning', 'local_th_vmc_alert'),
		get_string('alertlearningdesc', 'local_th_vmc_alert'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_alert/dayalertlearning',
		get_string('setting_dayalertlearning', 'local_th_vmc_alert'),
		get_string('setting_dayalertlearning', 'local_th_vmc_alert'),
		15, PARAM_INT, 5);

//

	$configs[] = new admin_setting_heading('local_th_vmc_alert/headingalertaccountduedate', get_string('setting_alertaccountduedate', 'local_th_vmc_alert'), '');

	$configs[] = new \local_th_vmc_alert\th_custom_admin_setting_configcheckbox('local_th_vmc_alert/alertaccountduedate',
		get_string('setting_alertaccountduedate', 'local_th_vmc_alert'),
		get_string('alertaccountduedatedesc', 'local_th_vmc_alert'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_alert/dayalertaccountduedate2',
		get_string('setting_dayalertaccountduedate', 'local_th_vmc_alert'),
		get_string('setting_dayalertaccountduedate', 'local_th_vmc_alert'),
		10, PARAM_INT, 5);

	//
	$configs[] = new admin_setting_heading('local_th_vmc_alert/headingalertketthuckhoahoc', get_string('setting_alertketthuckhoahoc', 'local_th_vmc_alert'), '');

	$configs[] = new \local_th_vmc_alert\th_custom_admin_setting_configcheckbox('local_th_vmc_alert/alertketthuckhoahoc',
		get_string('setting_alertketthuckhoahoc', 'local_th_vmc_alert'),
		get_string('alertketthuckhoahocdesc', 'local_th_vmc_alert'),
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_alert/dayalertketthuckhoahoc2',
		get_string('setting_dayalertketthuckhoahoc', 'local_th_vmc_alert'),
		get_string('setting_dayalertketthuckhoahoc', 'local_th_vmc_alert'),
		0, PARAM_INT, 5);

	//
	$configs[] = new admin_setting_heading('local_th_vmc_alert/headingalertaccountenrolcourse', 'Thông báo tài khoản đã được gán khóa học', '');

	$configs[] = new \local_th_vmc_alert\th_custom_admin_setting_configcheckbox('local_th_vmc_alert/alertaccountenrolcourse',
		'Thông báo tài khoản đã được gán khóa học',
		'Thông báo tài khoản đã được gán khóa học, nếu tick tính năng này sẽ được bật',
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_alert/dayalertaccountenrolcourse',
		'Ngày nhắc nhở thông báo tài khoản đã được gán khóa học, tính từ thời điểm ghi danh khóa học',
		'Ngày nhắc nhở thông báo tài khoản đã được gán khóa học, tính từ thời điểm ghi danh khóa học',
		2, PARAM_INT, 5);

	//
	$configs[] = new admin_setting_heading('local_th_vmc_alert/headingalertactivecourse', 'Thông báo nhắc nhở học tập khóa học đã kích hoạt', '');

	$configs[] = new \local_th_vmc_alert\th_custom_admin_setting_configcheckbox('local_th_vmc_alert/alertactivecourse',
		'Thông báo nhắc nhở học tập khóa học đã kích hoạt',
		'Thông báo nhắc nhở học tập khóa học đã kích hoạt, nếu tick tính năng này sẽ được bật',
		0);

	$configs[] = new admin_setting_configtext('local_th_vmc_alert/dayalertactivecourse',
		'Ngày nhắc nhở thông báo nhắc nhở học tập khóa học đã kích hoạt, tính từ thời điểm ghi danh khóa học',
		'Ngày nhắc nhở thông báo nhắc nhở học tập khóa học đã kích hoạt, tính từ thời điểm ghi danh khóa học',
		14, PARAM_INT, 5);

//add config
	foreach ($configs as $config) {
		$config->pin = 'local_thlib';
		$settings->add($config);
	}

}
