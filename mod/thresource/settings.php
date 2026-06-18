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
 * thresource module admin settings and defaults
 *
 * @package    mod_thresource
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_DOWNLOAD,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_DOWNLOAD,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('thresource/framesize',
        get_string('framesize', 'thresource'), get_string('configframesize', 'thresource'), 130, PARAM_INT));
    $settings->add(new admin_setting_configmultiselect('thresource/displayoptions',
        get_string('displayoptions', 'thresource'), get_string('configdisplayoptions', 'thresource'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('thresourcemodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('thresource/printintro',
        get_string('printintro', 'thresource'), get_string('printintroexplain', 'thresource'), 1));
    $settings->add(new admin_setting_configselect('thresource/display',
        get_string('displayselect', 'thresource'), get_string('displayselectexplain', 'thresource'), RESOURCELIB_DISPLAY_AUTO,
        $displayoptions));
    $settings->add(new admin_setting_configcheckbox('thresource/showsize',
        get_string('showsize', 'thresource'), get_string('showsize_desc', 'thresource'), 0));
    $settings->add(new admin_setting_configcheckbox('thresource/showtype',
        get_string('showtype', 'thresource'), get_string('showtype_desc', 'thresource'), 0));
    $settings->add(new admin_setting_configcheckbox('thresource/showdate',
        get_string('showdate', 'thresource'), get_string('showdate_desc', 'thresource'), 0));
    $settings->add(new admin_setting_configtext('thresource/popupwidth',
        get_string('popupwidth', 'thresource'), get_string('popupwidthexplain', 'thresource'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('thresource/popupheight',
        get_string('popupheight', 'thresource'), get_string('popupheightexplain', 'thresource'), 450, PARAM_INT, 7));
    $options = array('0' => get_string('none'), '1' => get_string('allfiles'), '2' => get_string('htmlfilesonly'));
    $settings->add(new admin_setting_configselect('thresource/filterfiles',
        get_string('filterfiles', 'thresource'), get_string('filterfilesexplain', 'thresource'), 0, $options));
}