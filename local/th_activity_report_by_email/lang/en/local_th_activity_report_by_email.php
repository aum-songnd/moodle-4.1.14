<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_activity_report_by_email';
$string['taskname'] = 'Send activity report';

$string['setting_heading'] = 'Activity Report';
$string['list_email'] = 'List email';
$string['des_list_email'] = 'Email list that receives activity reports, separated by commas';

$string['subject_email'] = 'Báo cáo Hoạt động của tài khoản {$a->username}';
$string['message_email'] = 'Báo cáo Hoạt động của tài khoản <b>{$a->username}</b> từ: {$a->time}
Chi tiết trong tệp đính kèm.';

$string['activity_tracking_email'] = 'Activity tracking email';
$string['des_activity_tracking_email'] = 'Activity tracking email';

$string['sendreport'] = 'Send Report';
$string['setting_sendreport'] = 'Send Report';
$string['activity_report'] = 'Activity Report';
$string['weekly_activity_report'] = 'Weekly Activity Report:';
$string['email_list_report'] = 'List of emails receiving the report:';
$string['userid_tracking'] = 'User ID tracking activity:';
$string['activity_log'] = 'Activity Log';
$string['time'] = 'Time';
$string['fullname'] = 'Full Name';
$string['affected_user'] = 'Affected User';
$string['event_context'] = 'Event Context';
$string['component'] = 'Component';
$string['event_name'] = 'Event Name';
$string['description'] = 'Description';
$string['origin'] = 'Origin';
$string['ip_address'] = 'IP Address';
$string['no_account'] = 'No account found: {$a}';
$string['email_send_success'] = 'Send: {$a} ok';
$string['email_send_fail'] = 'Send: {$a} no ok';
$string['email_not_found'] = 'No email: {$a}';