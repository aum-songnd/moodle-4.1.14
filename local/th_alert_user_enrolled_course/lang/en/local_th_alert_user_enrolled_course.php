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
 * Plugin strings are defined here.
 *
 * @package     local_th_alert_user_enrolled_course
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_alert_user_enrolled_course';
$string['th_alert_user_enrolled_course:view'] = 'view permission';
$string['taskname'] = 'Thông báo ghi danh khóa học';

$string['mailsubject'] = 'Course Enrollment Notification';
$string['mailbody'] = 'Hi {$a->username},

You have enrolled to course {$a->coursename}.
Please login to start your course at {$a->courselink}

If you have not received your email account notification, please check in the Ads / Spam folder, or contact the techical hotline 0943846035 for assistance

Thank you.';

$string['mailbody_fragment1'] = '{$a->stt}. You have enrolled to course {$a->coursefullname}, Please login and access the course at {$a->courselink}
';

$string['mailsubject_mode2'] = 'Hi {$a->fullname},
{$a->fragment1}If you have not received your email account notification, please check in the Ads / Spam folder, or contact the techical hotline {$a->hotline} for assistance. Thank you.';
$string['enrollment_notification'] = 'Course Enrollment Notification';
$string['adhoc_task_started'] = 'My ad-hoc task started';
$string['send_email_per_enrollment'] = 'Send one email per enrollment';
$string['send_email_daily_at_22'] = 'Send all enrollment emails at 22:00 daily';
$string['choose_email_method'] = 'Choose email sending method';
$string['page_heading'] = 'Heading';
