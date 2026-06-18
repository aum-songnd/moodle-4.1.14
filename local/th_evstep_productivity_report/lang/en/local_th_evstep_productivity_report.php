<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_evstep_productivity_report';
$string['taskname'] = 'Send weekly productivity report';

$string['list_email'] = 'List email';
$string['des_list_email'] = 'List of email to send mail, separated by commas';
$string['time'] = 'Time';
$string['des_time'] = 'Period of reporting data from current';
$string['setting_heading'] = 'Productivity Report';

$string['subject_email'] = 'Báo cáo năng suất hàng tuần EVSTEP từ {$a->time}';
$string['message_email'] = 'Báo cáo số lượng học viên được tạo tài khoản, gán khóa học từ {$a->time}
	1. Số tài khoản được gán từ {$a->time} là: {$a->number_userenrol}
	2. Số tài khoản được tạo từ {$a->time} là: {$a->number_user}
	Chi tiết trong tệp đính kèm.';

$string['taskname2'] = 'Send monthly productivity report';
$string['subject_email_month'] = 'Báo cáo năng suất hàng tháng EVSTEP từ {$a->time}';
$string['crm'] = 'AUM code';
$string['des_crm'] = 'AUM code';
$string['textfields'] = 'Filter';
$string['submit'] = 'Download';
$string['from'] = 'From';
$string['to'] = 'To';