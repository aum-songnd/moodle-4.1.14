<?php
// This file is part of mod_qaa for Moodle - http://moodle.org/
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
 * @package       mod_qaa
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	$configs = array();

	// More non-HTML characters than this is long
	$configs[] = new admin_setting_configtext('mod_qaa/min_question_character',
		get_string('min_character', 'mod_qaa'),
		get_string('min_characterdesc', 'mod_qaa'),
		'20', PARAM_INT);

	$configs[] = new admin_setting_configtext('mod_qaa/monitor_emailaddress',
		get_string('monitoremailaddress', 'mod_qaa'),
		get_string('monitoremailaddress', 'mod_qaa'),
		'', PARAM_RAW);

	$configs[] = new admin_setting_configtext('mod_qaa/typefileupload',
		get_string('typefileupload', 'mod_qaa'),
		get_string('limittypefileupload', 'mod_qaa'),
		'docx,xlsx,pptx,pdf,png,jpg,jpeg', PARAM_RAW);

	$currentmaxbytes = get_config('moodlecourse', 'maxbytes');
	if (isset($CFG->maxbytes)) {
		$choices = get_max_upload_sizes($CFG->maxbytes, 0, 0, $currentmaxbytes);
	} else {
		$choices = get_max_upload_sizes(0, 0, 0, $currentmaxbytes);
	}
	$configs[] = new admin_setting_configselect('mod_qaa/maxbytes', get_string('maximumupload'),
		get_string('qaahelpmaximumupload', 'mod_qaa'), 10485760, $choices);

	$configs[] = new admin_setting_configtext('mod_qaa/maxfiles',
		get_string('limitmaxfiles', 'mod_qaa'),
		get_string('limitmaxfilesdesc', 'mod_qaa'),
		'5', PARAM_INT);

	$configs[] = new admin_setting_configtext('mod_qaa/limittime_createquestion',
		get_string('limittimecreatequestion', 'mod_qaa'),
		get_string('des_limittimecreatequestion', 'mod_qaa'),
		'5', PARAM_INT);

	$configs[] = new admin_setting_configcheckbox(
		'mod_qaa/enable_sendmailcreatequestion',
		get_string('enable_sendmailcreatequestion', 'mod_qaa'),
		'',
		0
	);
		

	foreach ($configs as $config) {
		$config->plugin = 'mod_qaa';
		$settings->add($config);
	}

}
