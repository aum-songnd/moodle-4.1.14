# Tài liệu chức năng Ghi danh người dùng vào khóa học theo lô

1. **Tên plugin:** th_bulkenrol
2. **Kiểu plugin:** local
3. **Project:** TNU,VMC,AOF
4. **Chức năng chung:** Ghi danh người dùng vào khóa học theo lô
5. **Người phát triển:** minhpl@aum.edu.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_bulkenrol
# 1. Yêu cầu và Đầu vào, đầu ra:
1. Yêu cầu
- Ghi danh người dùng vào khóa học theo lô.
- Dựa trên plugin bulkenrol: https://moodle.org/plugins/local_bulkenrol

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **th_bulkenrol:enrolusers**
- Đoạn code định nghĩa capabilities:
```php
    'local/th_bulkenrol:enrolusers' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
	),
```

## 2. Hướng dẫn sử dụng
### Quản trị hệ thống
- Truy cập **Dashboard > Site administration > Plugins > Enrolments > TH User bulk enrolment** để cấu hình chức năng của plugin này.

![image](https://user-images.githubusercontent.com/66956549/230697251-5233c380-41ef-412b-8233-d5c7430ab83b.png)

### Người quản lý
- Truy cập khóa học muốn ghi danh người dùng, ví dụ: An ninh mạng - 230312. **Quản trị Khóa học > Thành viên > TH User bulk enrolment**

![image](https://user-images.githubusercontent.com/66956549/230697409-5626a5d9-776e-4159-a617-c5fe6c1b3018.png)

- Vùng nhận email hoặc mã học viên.
- Vai trò: Học viên, Giảng viên, Trợ giảng,...
- Kiểu ghi danh: email hoặc Mã học viên.
- Trạng thái ghi danh: ghi danh (enroll), đình chỉ (suspend), hủy ghi danh (unenroll)
![image](https://user-images.githubusercontent.com/66956549/230698119-8a17cf7b-9365-4cf0-8499-07168d21d385.png)
- ví dụ:
    - Ghi danh theo email
![image](https://user-images.githubusercontent.com/66956549/230698169-73f717ad-f175-4464-aa04-024eeecbbfaa.png)
    - Ghi danh theo Mã sinh viên
![image](https://user-images.githubusercontent.com/66956549/230698305-cc08408c-2d6e-4ee7-943c-2524d0f6e5eb.png)

![image](https://user-images.githubusercontent.com/66956549/230698563-de313fb7-4020-48a6-a135-ac4e879d6ff4.png)

- Lưu ý: Tính năng này không cho phép gán tài khoản bị suspend:
![image](https://github.com/thvietnam/th_lms/assets/57883256/94757646-223b-4047-bfc9-7393f5e4d2de)

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user, user_info_field, user_info_data, course
- Các câu truy vấn đã dùng:
    ```php
    $sql = "SELECT {user}.*
			from {user_info_field}, {user_info_data}, {user}
			where shortname $insql and {user_info_data}.fieldid = {user_info_field}.id and data = '$email' and
			{user}.id = {user_info_data}.userid";
    ```

2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: local_th_bulkenrol_check_user_mails, local_th_bulkenrol_check_user_masinhvien, local_th_bulkenrol_check_email, local_th_bulkenrol_check_masinhvien, local_th_bulkenrol_parse_emails, local_th_bulkenrol_get_user, local_th_bulkenrol_get_user_by_msv, local_th_bulkenrol_get_exception_info, local_th_bulkenrol_users, local_th_bulkunenrol_users, local_th_bulksuspend_users, local_th_bulkenrol_display_table, local_th_bulkenrol_display_table_mahocvien, 
local_th_bulkunenrol_display_table, local_th_bulksuspend_display_table, local_th_bulkenrol_is_already_member, local_th_bulkenrol_display_enroldetails
2. Chi tiết các Method:

```php
/**
 * Check list of submitted user mails and creates a data structure for displaying information on the confirm page and
 * for performing the th_bulkenrol.
 *
 * @param string $emailstextfield Text field value to be checked for emails and course groups.
 * @param int $courseid ID of the course, used to determine the context for checking whether a user is already enroled.
 *
 * @return stdClass Object containing information to be displayed on confirm page and being used for th_bulkenrol.
 */
function local_th_bulkenrol_check_user_mails($emailstextfield, $courseid)
```

```php
/**
 * Check list of submitted user_masinhvien and creates a data structure for displaying information on the confirm page and
 * for performing the th_bulkenrol.
 *
 * @param string $emailstextfield Text field value to be checked for user_masinhvien and course groups.
 * @param int $courseid ID of the course, used to determine the context for checking whether a user is already enroled.
 *
 * @return stdClass Object containing information to be displayed on confirm page and being used for th_bulkenrol.
 */
function local_th_bulkenrol_check_user_masinhvien($emailstextfield, $courseid)
```

```php
/**
 *
 * Check submitted email, working on the $checkedemails array
 *
 * @param string $email e-mail of the user that should be enroled
 * @param int $linecnt line counter used for error messages
 * @param int $courseid course id
 * @param context_course $context context instance of the course the user should be enroled into
 * @param string $currentgroup name of the group a user should be added to as member
 * @param object $checkedemails Object containing information to be displayed on confirm page and being used for th_bulkenrol.
 */
function local_th_bulkenrol_check_email($email, $linecnt, $courseid, $context, $currentgroup, &$checkedemails)
```

```php
/**
 *
 * Check submitted user masinhvien, working on the $checkedemails array
 *
 * @param string $email user_masinhvien of the user that should be enroled
 * @param int $linecnt line counter used for error messages
 * @param int $courseid course id
 * @param context_course $context context instance of the course the user should be enroled into
 * @param string $currentgroup name of the group a user should be added to as member
 * @param object $checkedemails Object containing information to be displayed on confirm page and being used for th_bulkenrol.
 */
function local_th_bulkenrol_check_masinhvien($email, $linecnt, $courseid, $context, $currentgroup, &$checkedemails)
```

```php
/**
 * Takes input from text area containing a list of e-mail adresses (optionally group names starting with '#').
 * Returns an array representation of the input.
 *
 * @param mixed $emails input value of the text area.
 * @return string[] of e-emails and optional group names
 */
function local_th_bulkenrol_parse_emails($emails)
```

```php
/**
 * Takes an e-mail and returns a moodle user record and error string (if occured).
 *
 * @param string $email E-mail used to search for a user
 * @return string,object[]
 */
function local_th_bulkenrol_get_user($email)
```

```php
/**
 * Takes an e-mail and returns a moodle user record and error string (if occured).
 *
 * @param string $email user masinhvien used to search for a user
 * @return string,object[]
 */
function local_th_bulkenrol_get_user_by_msv($email)
```

```php
/**
 * Get an understandable reason from an exception which happened during th_bulkenrol.
 *
 * @param object $e should be of instanceof Exception
 * @return string readable form of an exception
 */
function local_th_bulkenrol_get_exception_info($e)
```

```php
/**
 * Perform user enrolment into the course and optionally add users as member into course groups. Groups are created if necessary.
 *
 * @param string $localth_bulkenrolkey
 * @return object
 */
function local_th_bulkenrol_users($localth_bulkenrolkey, $localth_bulkenrolrole)
```

```php
/**
 * Perform user unenrol into the course
 *
 * @param string $localth_bulkenrolkey
 * @return object
 */
function local_th_bulkunenrol_users($localth_bulkenrolkey)
```

```php
/**
 * Perform user suspend into the course
 *
 * @param string $localth_bulkenrolkey
 * @return object
 */
function local_th_bulksuspend_users($localth_bulkenrolkey)
```

```php
/**
 * According to the parameter, either a table with hints is displayed or a table with users to be written is displayed.
 *
 * @param object $localth_bulkenroldata
 * @param string $key
 */
function local_th_bulkenrol_display_table($localth_bulkenroldata, $key)
```

```php
/**
 * According to the parameter, either a table with hints is displayed or a table with users to be written is displayed.
 *
 * @param object $localth_bulkenroldata
 * @param string $key
 */
function local_th_bulkenrol_display_table_mahocvien($localth_bulkenroldata, $key)
```

```php
/**
 * table unenrol
 *
 * @param object $localth_bulkenroldata
 * @param string $key
 */
function local_th_bulkunenrol_display_table($localth_bulkenroldata, $key)
```

```php
/**
 * table suspend
 *
 * @param object $localth_bulkenroldata
 * @param string $key
 */
function local_th_bulksuspend_display_table($localth_bulkenroldata, $key)
```

```php
/**
 * Checks whether a user with id $userid can be found in members list of the course group with name $groupname.
 *
 * @param int $courseid id of the course
 * @param string $groupname
 * @param int $userid id of the user
 * @return mixed Data structure containing flag 'already_member' and 'error'.
 */
function local_th_bulkenrol_is_already_member($courseid, $groupname, $userid)
```

```php
/**
 * Helper function to show the enrolment details about the upcoming enrolments.
 */
function local_th_bulkenrol_display_enroldetails($localth_bulkenrolrole)
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_bulkenrol

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

# Mô tả chung
- **Tên plugin**: th_bulkenrol
- **Kiểu plugin**: local
- **Project**: vmc, tnu
- **Chức năng chung**: Ghi danh người dùng theo lô bằng cách nhập danh sách emails
- **Người phát triển**: minhpl@aum.edu.vn 
# Mô tả chi tiết


moodle-local_th_bulkenrol
======================

[![Build Status](https://travis-ci.com/moodleuulm/moodle-local_th_bulkenrol.svg?branch=master)](https://travis-ci.com/moodleuulm/moodle-local_th_bulkenrol)

Moodle plugin which provides the possibility to bulk enrol a list of users who are identified by their e-mail adresses into a course.


Requirements
------------

This plugin requires Moodle 3.10+


Motivation for this plugin
--------------------------

In some organizations or some teaching scenarios, manually enrolling students into a course may be the preferred way. However, enrolling one user at a time into a course can be daunting for teachers. On the other hand, teachers may not be allowed to use (or be able to understand) the Moodle core upload CSV functionality.

To ease the life of teachers, there is the need for a bulk enrolment tool. There are already plugins out there which provide this functionality, so this is just another one. The goal of this bulk enrolment implementation is not to fulfil everybody's needs, but to do one thing and to do this well.

So, the key features of this plugin are:
1. to let teachers submit a line-separated list of email addresses to enrol them into a course,
2. to let teachers submit this list to a textarea within their course instead of requiring them to create and upload a CSV file first.


Installation
------------

Install the plugin like any other plugin to folder
/local/th_bulkenrol

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it does not do anything to Moodle yet.

To configure the plugin and its behaviour, please visit:
Site administration -> Plugins -> Enrolments -> User bulk enrolment

There, you find only one setting:

### 1. Enrolment plugin
The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.


Capabilities
------------
This plugin also introduces a new capability:

### local/th_bulkenrol:enrolusers

By default, Moodle users are _not_ allowed to use the functionality provided by this plugin. As administrator, you can selectively grant users the ability to use this functionality by adding the local/th_bulkenrol:enrolusers capability to an appropriate Moodle role.


How this plugin works
---------------------

Teachers (rather users who have been granted the capability which is described in the "Capabilities" section above) will find an additional "User bulk enrolment" menu item within their course's course administration menu.

To enrol existing Moodle users into the course, the teacher will then have to add a list of e-mail adresses to the form on this page, one user / e-mail adress per line.

Example:
```
alice@example.com
bob@example.com
```

Optionally, the teacher will be able to create groups and add the enrolled users to the groups. All he has to do is to add a heading line with a hash sign and the group's name, separating the list of users.

Example:
```
# Group 1
alice@example.com
bob@example.com
# Group 2
carol@example.com
dave@example.com
```


Limitations
-----------

This plugin currently only accepts a list of e-mail adresses to be enrolled into a course. It does especially not accept lists of user names, matriculation IDs or something else.

Additionally, this plugin only enrols users who already exist in Moodle. It won't create Moodle user accounts on-the-fly. 


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/view/local_th_bulkenrol

The latest development version can be found on Github:
https://github.com/moodleuulm/moodle-local_th_bulkenrol


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/moodleuulm/moodle-local_th_bulkenrol/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Due to limited resources, the functionality of this plugin is primarily implemented for our own local needs and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/moodleuulm/moodle-local_th_bulkenrol/issues

Please create pull requests on Github:
https://github.com/moodleuulm/moodle-local_th_bulkenrol/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

Due to limited resources, this plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

As the plugin creator, we manage the translation into german for our own local needs on AMOS. Please contribute your translation into all other languages in AMOS where they will be reviewed by the official language pack maintainers for Moodle.


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send us a pull request on Github with modifications.


PHP7 Support
------------

Since Moodle 3.4 core, PHP7 is mandatory. We are developing and testing this plugin for PHP7 only.


Copyright
---------

Soon Systems GmbH
www.soon-systems.de

on behalf of

Ulm University
Communication and Information Centre (kiz)
Alexander Bias
