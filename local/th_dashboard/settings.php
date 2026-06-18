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
 * Settings Custom Navigation local plugin.
 *
 * @package    local_th_dashboard
 * @author     Carlos Escobedo <http://www.twitter.com/carlosagile>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  2017 Carlos Escobedo <http://www.twitter.com/carlosagile>)
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_dashboard', get_string('pluginname', 'local_th_dashboard'));
    $settings->add(new admin_setting_configcheckbox('local_th_dashboard/enabled', get_string('activate', 'local_th_dashboard'),
        get_string('stractivate', 'local_th_dashboard'), 0));

    global $DB;

    $sql = "SELECT r.id,r.shortname,r.name 
            FROM {role} r
            ORDER BY r.sortorder";
    $data = $DB->get_records_sql($sql);

    $role = array();
    foreach ($data as $key => $item) {
        if (is_null($item->name) || $item->name == "") {
            switch ($item->shortname) {
                case 'manager':         $rolename = get_string('manager', 'role'); break;
                case 'coursecreator':   $rolename = get_string('coursecreators'); break;
                case 'editingteacher':  $rolename = get_string('defaultcourseteacher'); break;
                case 'teacher':         $rolename = get_string('noneditingteacher'); break;
                case 'student':         $rolename = get_string('defaultcoursestudent'); break;
                case 'guest':           $rolename = get_string('guest'); break;
                case 'user':            $rolename = get_string('authenticateduser'); break;
                case 'frontpage':       $rolename = get_string('frontpageuser', 'role'); break;
                // We should not get here, the role UI should require the name for custom roles!
                default:                $rolename = $item->shortname; break;
            } 
        } else {
            $rolename = $item->name;
        }
        $role[$key] = $key . ' - ' . $item->shortname . ' ('. $rolename .')';
    }
    $settings->add(new admin_setting_configcheckbox('local_th_dashboard/flatenabled', get_string('flatenabled', 'local_th_dashboard'),
        get_string('strflatenabled', 'local_th_dashboard') . '</br>' . implode("</br>", $role), 0));

    $settings->add(new admin_setting_configtextarea('local_th_dashboard/menuitems',
        get_string('items', 'local_th_dashboard'), get_string('stritems', 'local_th_dashboard'), ''));

    $ADMIN->add('appearance', $settings);
}
