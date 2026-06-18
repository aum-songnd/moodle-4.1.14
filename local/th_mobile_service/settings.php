<?php
// This file is part of local_th_mobile_service for Moodle - http://moodle.org/
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
 * @package       local_th_mobile_service
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig/*$ADMIN->fulltree*/) {

	$settings = new admin_settingpage('local_th_mobile_service', get_string('pluginname', 'local_th_mobile_service'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$configs[] = new admin_setting_configtext('local_th_mobile_service/studentcode',
		get_string('studentcodeshortname', 'local_th_mobile_service'),
		get_string('studentcodedesc', 'local_th_mobile_service'),
		'', PARAM_TEXT);

	foreach ($configs as $config) {
		$config->plugin = 'local_th_mobile_service';
		$settings->add($config);
	}

}
