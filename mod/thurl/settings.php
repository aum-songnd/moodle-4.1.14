<?php

// This file is part of Moodle - http://moodle.org/
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
 * Url module admin settings and defaults
 *
 * @package    mod_thurl
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once "$CFG->libdir/resourcelib.php";

	$displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
		RESOURCELIB_DISPLAY_EMBED,
		RESOURCELIB_DISPLAY_FRAME,
		RESOURCELIB_DISPLAY_OPEN,
		RESOURCELIB_DISPLAY_NEW,
		RESOURCELIB_DISPLAY_POPUP,
	));
	$defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
		RESOURCELIB_DISPLAY_EMBED,
		RESOURCELIB_DISPLAY_OPEN,
		RESOURCELIB_DISPLAY_POPUP,
	);

	//--- general settings -----------------------------------------------------------------------------------
	$settings->add(new admin_setting_configtext('thurl/framesize',
		get_string('framesize', 'thurl'), get_string('configframesize', 'thurl'), 130, PARAM_INT));
	$settings->add(new admin_setting_configpasswordunmask('thurl/secretphrase', get_string('password'),
		get_string('configsecretphrase', 'thurl'), ''));
	$settings->add(new admin_setting_configcheckbox('thurl/rolesinparams',
		get_string('rolesinparams', 'thurl'), get_string('configrolesinparams', 'thurl'), false));
	$settings->add(new admin_setting_configmultiselect('thurl/displayoptions',
		get_string('displayoptions', 'thurl'), get_string('configdisplayoptions', 'thurl'),
		$defaultdisplayoptions, $displayoptions));

	//--- modedit defaults -----------------------------------------------------------------------------------
	$settings->add(new admin_setting_heading('thurlmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

	$settings->add(new admin_setting_configcheckbox('thurl/printintro',
		get_string('printintro', 'thurl'), get_string('printintroexplain', 'thurl'), 1));
	$settings->add(new admin_setting_configselect('thurl/display',
		get_string('displayselect', 'thurl'), get_string('displayselectexplain', 'thurl'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
	$settings->add(new admin_setting_configtext('thurl/popupwidth',
		get_string('popupwidth', 'thurl'), get_string('popupwidthexplain', 'thurl'), 620, PARAM_INT, 7));
	$settings->add(new admin_setting_configtext('thurl/popupheight',
		get_string('popupheight', 'thurl'), get_string('popupheightexplain', 'thurl'), 450, PARAM_INT, 7));

		//--- Task scheduling settings -----------------------------------------------------------------------------------

$settings->add(new admin_setting_heading('thurltaskschedule', get_string('taskschedule', 'mod_thurl'), ''));


$settings->add(new admin_setting_configcheckbox(
    'thurl/enable_taskschedule',
    get_string('enable_taskschedule', 'mod_thurl'),
    get_string('enable_taskschedule_desc', 'mod_thurl'),
    0
));

$settings->add(new admin_setting_configtext('thurl/scheduled_hour',
    get_string('scheduled_hour', 'mod_thurl'),
    get_string('scheduled_time_desc', 'mod_thurl'),
    10, PARAM_INT));


$settings->add(new admin_setting_configtext('thurl/scheduled_minute',
    get_string('scheduled_minute', 'mod_thurl'),
    get_string('scheduled_time_desc', 'mod_thurl'),
    '00', PARAM_TEXT));

$settings->add(new admin_setting_heading('thurltasksrun', get_string('taskstimerun', 'mod_thurl'), ''));

$settings->add(new admin_setting_configtext(
		'thurl/task_hour',
		get_string('task_hour', 'mod_thurl'),
		get_string('scheduled_time_desc', 'mod_thurl'),
		10, PARAM_INT
));
	
$settings->add(new admin_setting_configtext(
		'thurl/task_minute',
		get_string('task_minute', 'mod_thurl'),
		get_string('scheduled_time_desc', 'mod_thurl'),
		'00', PARAM_TEXT
));

}
