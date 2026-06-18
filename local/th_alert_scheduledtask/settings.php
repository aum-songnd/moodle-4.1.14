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

	$settings = new admin_settingpage('local_th_alert_scheduledtask', get_string('pluginname', 'local_th_alert_scheduledtask'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertapprovecourse', get_string('setting_alertapprovecourse', 'local_th_alert_scheduledtask'), get_string('setting_alertapprovecourse', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertapprovecourse',
		get_string('setting_alertapprovecourse', 'local_th_alert_scheduledtask'),
		get_string('alertapprovecourse', 'local_th_alert_scheduledtask'),
		1);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertapprovecourse',
		get_string('setting_dayalertapprovecourse', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertapprovecourse', 'local_th_alert_scheduledtask'),
		-2, PARAM_INT, 5);

// -------------------------------------------------------------------Dang lam
	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertwritewelcomtocourseforum', get_string('setting_alertwritewelcomtocourseforum', 'local_th_alert_scheduledtask'), get_string('setting_alertwritewelcomtocourseforum', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertwritewelcomtocourseforum',
		get_string('setting_alertwritewelcomtocourseforum', 'local_th_alert_scheduledtask'),
		get_string('alertwritewelcomtocourseforum', 'local_th_alert_scheduledtask'),
		1);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/day_in_content2',
		get_string('setting_day_in_week', 'local_th_alert_scheduledtask'),
		get_string('setting_day_in_week_desc', 'local_th_alert_scheduledtask'),
		5, PARAM_INT, 5);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertwritewelcomtocourseforum',
		get_string('setting_dayalertwritewelcomtocourseforum', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertwritewelcomtocourseforum', 'local_th_alert_scheduledtask'),
		0, PARAM_INT, 5);

// ------------------------------------------------------------------------

	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertpostscheduleonlinecourse', get_string('setting_alertpostscheduleonlinecourse', 'local_th_alert_scheduledtask'),
		get_string('setting_alertpostscheduleonlinecourse', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertpostscheduleonlinecourse',
		get_string('setting_alertpostscheduleonlinecourse', 'local_th_alert_scheduledtask'),
		get_string('alertpostscheduleonlinecourse', 'local_th_alert_scheduledtask'),
		1);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/day_in_content3',
		get_string('setting_day_in_week', 'local_th_alert_scheduledtask'),
		get_string('setting_day_in_week_desc', 'local_th_alert_scheduledtask'),
		5, PARAM_INT, 5);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertpostscheduleonlinecourse',
		get_string('setting_dayalertpostscheduleonlinecourse', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertpostscheduleonlinecourse', 'local_th_alert_scheduledtask'),
		0, PARAM_INT, 5);
//----------------------------------------------------------------------------
	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertanswerquestion', get_string('setting_alertanswerquestion', 'local_th_alert_scheduledtask'),
		get_string('setting_alertanswerquestion', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertanswerquestion',
		get_string('setting_alertanswerquestion', 'local_th_alert_scheduledtask'),
		get_string('alertanswerquestion', 'local_th_alert_scheduledtask'),
		1);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertanswerquestion',
		get_string('setting_dayalertanswerquestion', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertanswerquestion', 'local_th_alert_scheduledtask'),
		7, PARAM_INT, 5);
//----------------------------------------------------------------------------
	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertnhanxetdanhgiahangtuan',
		get_string('setting_alertnhanxetdanhgiahangtuan', 'local_th_alert_scheduledtask'),
		get_string('setting_alertnhanxetdanhgiahangtuan', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertnhanxetdanhgiahangtuan',
		get_string('setting_alertnhanxetdanhgiahangtuan', 'local_th_alert_scheduledtask'),
		get_string('alertnhanxetdanhgiahangtuan', 'local_th_alert_scheduledtask'),
		1);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/day_in_content5',
		get_string('setting_day_in_week', 'local_th_alert_scheduledtask'),
		get_string('setting_day_in_week_desc', 'local_th_alert_scheduledtask'),
		"9, 16, 23, 30, 37, 44, 51", PARAM_RAW, 30);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertnhanxetdanhgiahangtuan',
		get_string('setting_dayalertnhanxetdanhgiahangtuan', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertnhanxetdanhgiahangtuandesc', 'local_th_alert_scheduledtask'),
		"7, 14, 21, 28, 35, 42, 49", PARAM_RAW, 30);
//--------------------------------------------------------------------------------
	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertchotbangdiem', get_string('setting_alertchotbangdiem', 'local_th_alert_scheduledtask'),
		get_string('setting_alertchotbangdiem', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertchotbangdiem',
		get_string('setting_alertchotbangdiem', 'local_th_alert_scheduledtask'),
		get_string('alertchotbangdiem', 'local_th_alert_scheduledtask'),
		0);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/day_in_content6',
		get_string('setting_day_in_week', 'local_th_alert_scheduledtask'),
		get_string('setting_day_in_week_desc', 'local_th_alert_scheduledtask'),
		51, PARAM_INT, 5);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertchotbangdiem',
		get_string('setting_dayalertchotbangdiem', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertchotbangdiem', 'local_th_alert_scheduledtask'),
		49, PARAM_INT, 5);

//--------------------------------------------------------------------------------
	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/headingalertchamdiembaituluan', get_string('setting_alertchamdiembaituluan', 'local_th_alert_scheduledtask'),
		get_string('setting_alertchamdiembaituluan', 'local_th_alert_scheduledtask'));

	$configs[] = new \local_th_alert_scheduledtask\th_custom_admin_setting_configcheckbox('local_th_alert_scheduledtask/alertchamdiembaituluan',
		get_string('setting_alertchamdiembaituluan', 'local_th_alert_scheduledtask'),
		get_string('alertchamdiembaituluan', 'local_th_alert_scheduledtask'),
		1);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/day_in_content7',
		get_string('setting_day_in_week', 'local_th_alert_scheduledtask'),
		get_string('setting_day_in_week_desc', 'local_th_alert_scheduledtask'),
		"33,61", PARAM_RAW, 5);

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/dayalertchamdiembaituluan',
		get_string('setting_dayalertchamdiembaituluan', 'local_th_alert_scheduledtask'),
		get_string('setting_dayalertchamdiembaituluan', 'local_th_alert_scheduledtask'),
		"30,58", PARAM_RAW, 5);

// -----------------------------------------------------------------

	$configs[] = new admin_setting_heading('local_th_alert_scheduledtask/heading_other',
		get_string('setting_other', 'local_th_alert_scheduledtask'),
		get_string('setting_other', 'local_th_alert_scheduledtask'));

	$configs[] = new admin_setting_configtext('local_th_alert_scheduledtask/emailcontact',
		get_string('emailcontact', 'local_th_alert_scheduledtask'),
		get_string('emailcontact', 'local_th_alert_scheduledtask'),
		get_string('default_emailcontact', 'local_th_alert_scheduledtask'),
		PARAM_RAW,
		100);

	foreach ($configs as $config) {
		$config->plugin = 'local_th_alert_scheduledtask';
		$settings->add($config);
	}

}
