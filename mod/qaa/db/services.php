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
 * Choice group external functions and service definitions.
 *
 * @package    mod_qaa
 * @category   external
 * @copyright  2018 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

	'mod_qaa_makequestion' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'makequestion',
		'description' => 'Make new question',
		'type' => 'write',
		'capabilities' => 'mod/qaa:makequest',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),

	'mod_qaa_updatequestion' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'updatequestion',
		'description' => 'update question',
		'type' => 'write',
		'capabilities' => 'mod/qaa:makequest',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),
	
	'mod_qaa_getparentquestion' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'getparentquestion',
		'description' => 'Make new question',
		'type' => 'read',
		'capabilities' => 'mod/qaa:makequest',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),
	
	'mod_qaa_detailqaa' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'detailqaa',
		'description' => 'detail qaa',
		'type' => 'read',
		'capabilities' => 'mod/qaa:makequest',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),

	'mod_qaa_decidequestion' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'decidequestion',
		'description' => 'decide question',
		'type' => 'read',
		'capabilities' => 'mod/qaa:approval',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),

	'mod_qaa_get_qaas_by_courses' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'get_issued_qaas',
		'description' => 'update question',
		'type' => 'read',
		'capabilities' => 'mod/qaa:makequest',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),

	'mod_qaa_deletequestion' => array(
		'classname' => 'mod_qaa_external',
		'methodname' => 'deletequestion',
		'description' => 'delete question',
		'type' => 'write',
		'capabilities' => 'mod/qaa:deletequest',
		'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
	),
	
);
