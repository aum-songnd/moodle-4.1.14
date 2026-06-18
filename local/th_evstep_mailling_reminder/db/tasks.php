<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
	array(
		'classname' => '\local_th_evstep_mailling_reminder\task\send_mail_attemps_need_to_grading',
		'blocking' => 0,
		'minute' => '0', // Chạy vào phút
		'hour' => '9',  // Chạy vào giờ 
		'day' => '*',    // Chạy mỗi ngày
		'month' => '*',  // Chạy mỗi tháng
		'dayofweek' => '*', // Chạy mọi ngày trong tuần
		'disabled' => 0, // Kích hoạt cron job
	),

	array(
		'classname' => '\local_th_evstep_mailling_reminder\task\send_mail_attemps_to_grading',
		'blocking' => 0,
		'minute' => '0', // Chạy vào phút
		'hour' => '18',  // Chạy vào giờ
		'day' => '*',    // Chạy mỗi ngày
		'month' => '*',  // Chạy mỗi tháng
		'dayofweek' => '*', // Chạy mọi ngày trong tuần
		'disabled' => 0, // Kích hoạt cron job
	),

	array(
		'classname' => '\local_th_evstep_mailling_reminder\task\send_mail_ctv_finished_grading',
		'blocking' => 0,
		'minute' => '0', // Chạy vào phút
		'hour' => '15',  // Chạy vào giờ
		'day' => '*',    // Chạy mỗi ngày
		'month' => '*',  // Chạy mỗi tháng
		'dayofweek' => '*', // Chạy mọi ngày trong tuần
		'disabled' => 0, // Kích hoạt cron job
	),

	array(
		'classname' => '\local_th_evstep_mailling_reminder\task\send_mail_grading_approved',
		'blocking' => 0,
		'minute' => '0', // Chạy vào phút
		'hour' => '18',  // Chạy vào giờ
		'day' => '*',    // Chạy mỗi ngày
		'month' => '*',  // Chạy mỗi tháng
		'dayofweek' => '*', // Chạy mọi ngày trong tuần
		'disabled' => 0, // Kích hoạt cron job
	),

	array(
		'classname' => '\local_th_evstep_mailling_reminder\task\send_mail_assign_turns',
		'blocking' => 0,
		'minute' => '0', // Chạy vào phút
		'hour' => '22',  // Chạy vào giờ
		'day' => '*',    // Chạy mỗi ngày
		'month' => '*',  // Chạy mỗi tháng
		'dayofweek' => '*', // Chạy mọi ngày trong tuần
		'disabled' => 0, // Kích hoạt cron job
	),

	array(
		'classname' => '\local_th_evstep_mailling_reminder\task\send_mail_user_enrolled',
		'blocking' => 0,
		'minute' => '0', // Chạy vào phút
		'hour' => '22',  // Chạy vào giờ
		'day' => '*',    // Chạy mỗi ngày
		'month' => '*',  // Chạy mỗi tháng
		'dayofweek' => '*', // Chạy mọi ngày trong tuần
		'disabled' => 0, // Kích hoạt cron job
	),
);
