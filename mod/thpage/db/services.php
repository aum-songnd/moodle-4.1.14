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
 * thpage external functions and service definitions.
 *
 * @package    mod_thpage
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_thpage_view_thpage' => array(
        'classname'     => 'mod_thpage_external',
        'methodname'    => 'view_thpage',
        'description'   => 'Simulate the view.php web interface thpage: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/thpage:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_thpage_get_thpages_by_courses' => array(
        'classname'     => 'mod_thpage_external',
        'methodname'    => 'get_thpages_by_courses',
        'description'   => 'Returns a list of thpages in a provided list of courses, if no list is provided all thpages that the user
                            can view will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/thpage:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);
