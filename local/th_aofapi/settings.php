<?php
// This file is part of local_th_aofapi for Moodle - http://moodle.org/
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
 * @package       local_th_aofapi
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig/*$ADMIN->fulltree*/) {

	$settings = new admin_settingpage('local_th_aofapi', get_string('pluginname', 'local_th_aofapi'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();
	$configs[] = new admin_setting_configtextarea(
        'local_th_aofapi/dic_mapping',
        get_string('dic_mapping', 'local_th_aofapi'),
    	get_string('dic_mapping_desc', 'local_th_aofapi'),
        json_encode([
            'cc'   => 'cc',
            'kt.1' => 'kt1',
            'kt.2' => 'kt2',
            'kt'   => 'kt',
            'ddk'  => 'dkdt'
        ], JSON_PRETTY_PRINT)
    );

	foreach ($configs as $config) {
		$config->plugin = 'local_th_aofapi';
		$settings->add($config);
	}

}
