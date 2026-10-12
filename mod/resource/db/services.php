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
 * Resource external functions and service definitions.
 *
 * @package    mod_resource
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_resource_view_resource' => array(
        'classname'     => 'mod_resource_external',
        'methodname'    => 'view_resource',
        'description'   => 'Simulate the view.php web interface resource: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/resource:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'mod_resource_get_resources_by_courses' => array(
        'classname'     => 'mod_resource_external',
        'methodname'    => 'get_resources_by_courses',
        'description'   => 'Returns a list of files in a provided list of courses, if no list is provided all files that
                            the user can view will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/resource:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_resource_save_video_progress' => [
        'classname'   => 'mod_resource_external',
        'methodname'  => 'save_video_progress',
        'description' => 'Lưu tiến trình và sơ đồ mapa xem video của học viên.',
        'type'        => 'write',
        'ajax'        => true, // Bắt buộc phải có true để Javascript Ajax call chạy được
        'capabilities'=> 'mod/resource:view',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ],
    'mod_resource_get_video_progress' => array(
        'classname'   => 'mod_resource_external',
        'methodname'  => 'get_video_progress',
        'description' => 'Returns the current user video progress for a resource activity.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'mod/resource:view',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);