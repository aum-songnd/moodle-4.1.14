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
 * Version details
 *
 * @package   block_th_course_status
 * @copyright 2017 Manoj Solanki (Coventry University)
 * @copyright
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_answer_question',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_approve_course',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_chot_bang_diem',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_viet_nhanxetdanhgia_hangtuan',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_post_schedule_online_course',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_write_welcome_to_course_forum',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),

	array(
		'classname' => '\local_th_alert_scheduledtask\task\alert_cham_diem_bai_tu_luan',
		'blocking' => 0,
		'minute' => '*/30',
		'hour' => '*',
		'day' => '*',
		'month' => '*',
		'dayofweek' => '*',
		'disabled' => 0,
	),
);
