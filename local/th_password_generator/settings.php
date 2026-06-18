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

	$settings = new admin_settingpage('local_th_password_generator', get_string('pluginname', 'local_th_password_generator'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

// Thay thế default password generator
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpassworddefault', get_string('setting_passworddefault', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passworddefault',
		get_string('setting_passworddefault', 'local_th_password_generator'),
		get_string('passworddefaultdesc', 'local_th_password_generator'),
		0);

// ki tu dac biet
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpasswordsymbols', get_string('setting_passwordsymbols', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passwordsymbols',
		get_string('setting_passwordsymbols', 'local_th_password_generator'),
		get_string('passwordsymbolsdesc', 'local_th_password_generator'),
		0);

	$configs[] = new admin_setting_configtext('local_th_password_generator/lenpasswordsymbols',
		get_string('setting_lenpasswordsymbols', 'local_th_password_generator'),
		get_string('setting_lenpasswordsymbols', 'local_th_password_generator'),
		2, PARAM_RAW, 5);

// gom day so
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpasswordnumbers', get_string('setting_passwordnumbers', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passwordnumbers',
		get_string('setting_passwordnumbers', 'local_th_password_generator'),
		get_string('passwordnumbersdesc', 'local_th_password_generator'),
		0);

	$configs[] = new admin_setting_configtext('local_th_password_generator/lenpasswordnumbers',
		get_string('setting_lenpasswordnumbers', 'local_th_password_generator'),
		get_string('setting_lenpasswordnumbers', 'local_th_password_generator'),
		2, PARAM_INT, 5);

// gom chu thuong
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpasswordlowercase', get_string('setting_passwordlowercase', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passwordlowercase',
		get_string('setting_passwordlowercase', 'local_th_password_generator'),
		get_string('passwordlowercasedesc', 'local_th_password_generator'),
		0);

	$configs[] = new admin_setting_configtext('local_th_password_generator/lenpasswordlowercase',
		get_string('setting_lenpasswordlowercase', 'local_th_password_generator'),
		get_string('setting_lenpasswordlowercase', 'local_th_password_generator'),
		2, PARAM_INT, 5);

// chu hoa
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpassworduppercase', get_string('setting_passworduppercase', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passworduppercase',
		get_string('setting_passworduppercase', 'local_th_password_generator'),
		get_string('passworduppercasedesc', 'local_th_password_generator'),
		0);

	$configs[] = new admin_setting_configtext('local_th_password_generator/lenpassworduppercase',
		get_string('setting_lenpassworduppercase', 'local_th_password_generator'),
		get_string('setting_lenpassworduppercase', 'local_th_password_generator'),
		2, PARAM_INT, 5);

// 1 vai chu hoac so giong nhau
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpasswordsimilar', get_string('setting_passwordsimilar', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passwordsimilar',
		get_string('setting_passwordsimilar', 'local_th_password_generator'),
		get_string('passwordsimilardesc', 'local_th_password_generator'),
		0);
	$configs[] = new admin_setting_configtext('local_th_password_generator/addpasswordsimilar',
		get_string('setting_addpasswordsimilar', 'local_th_password_generator'),
		get_string('setting_addpasswordsimilar', 'local_th_password_generator'),
		'il1Lo0O', PARAM_TEXT, 10);

// 1 vai ki tu khong ro rang
	$configs[] = new admin_setting_heading('local_th_password_generator/headingpasswordambiguous', get_string('setting_passwordambiguous', 'local_th_password_generator'), '');

	$configs[] = new admin_setting_configcheckbox('local_th_password_generator/passwordambiguous',
		get_string('setting_passwordambiguous', 'local_th_password_generator'),
		get_string('passwordambiguousdesc', 'local_th_password_generator'),
		0);

	$configs[] = new admin_setting_configtext('local_th_password_generator/addpasswordambiguous',
		get_string('setting_addpasswordambiguous', 'local_th_password_generator'),
		get_string('setting_addpasswordambiguous', 'local_th_password_generator'),
		'{}[]()/\"`~,;:.<>|', PARAM_RAW, 10);

//add config
	foreach ($configs as $config) {
		$config->pin = 'local_thlib';
		$settings->add($config);
	}

}
