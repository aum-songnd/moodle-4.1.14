# Tài liệu Thông báo tạo tài khoản

1. **Tên plugin:** th_alert_create_user
2. **Kiểu plugin**: local
3. **Project:** TNU
4. **Chức năng chung:** Khi tạo tài khoản, sẽ gửi mail yêu cầu đặt lại mật khẩu. Thay tính năng thông tin tài khoản mặc định của hệ thống
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_alert_create_user
# 1. Yêu cầu:

- Nội dung có link để người dùng ấn vào sẽ chuyển để biểu mẫu đặt mật khẩu (hạn chế nhập sai mật khẩu).
- Đầu vào: Tạo tài khoản mới.
- Đầu ra: mail gửi đến người dùng.
	- Nội dung:
	"XXX thân mến!

	Chào mừng bạn đến với hệ thống TNU eLearning

	Tài khoản để học tập của bạn đã được tạo với các thông tin sau:
	- Tên đăng nhập: khxxxx
	- Mật khẩu: Bấm vào ĐÂY (link) để tạo mật khẩu đăng nhập vào hệ thống.
	- Địa chỉ đăng nhập tại: https://tnu.aum.edu.vn
	Lưu ý: mật khẩu phân biệt chữ hoa, chữ thường, và không chứa dấu cách ở hai đầu. Khi nhập mật khẩu nên tắt chế độ gõ tiếng Việt.

	Cảm ơn,
	Ban Quản Trị TNU eLearning".

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
   - Các capability: không có
   - Đoạn code định nghĩa capabilities: không có


## 2. Hướng dẫn tích hợp mã nguồn
- Cài plugin **local/th_alert_create_user**
- Tìm kiếm hàm **setnew_password_and_mail** trong mã nguồn(tệp /lib/moodlelib.php) và thêm đoạn if else như sau.
- **Mã cũ:**
```php
function setnew_password_and_mail($user, $fasthash = false) {
	global $CFG, $DB;

	// We try to send the mail in language the user understands,
	// unfortunately the filter_string() does not support alternative langs yet
	// so multilang will not work properly for site->fullname.
	$lang = empty($user->lang) ? get_newuser_language() : $user->lang;

	$site = get_site();

	$supportuser = core_user::get_support_user();

	$newpassword = generate_password();

	update_internal_user_password($user, $newpassword, $fasthash);

	$a = new stdClass();
	$a->firstname = fullname($user, true);
	$a->sitename = format_string($site->fullname);
	$a->username = $user->username;
	$a->newpassword = $newpassword;
	$a->link = $CFG->wwwroot . '/login/?lang=' . $lang;
	$a->signoff = generate_email_signoff();

	$message = (string) new lang_string('newusernewpasswordtext', '', $a, $lang);

	$subject = format_string($site->fullname) . ': ' . (string) new lang_string('newusernewpasswordsubj', '', $a, $lang);

	// Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
	return email_to_user($user, $supportuser, $subject, $message);
}
```
- **Mã mới:**
```php
function setnew_password_and_mail($user, $fasthash = false) {

	global $CFG;
	//use link
	if (file_exists($CFG->dirroot . '/local/th_alert_create_user/locallib.php') and get_config('local_th_alert_create_user', 'uselink')) {
		require_once $CFG->dirroot . '/local/th_alert_create_user/locallib.php';
		return local_th_alert_create_user($user);

	} else {
		//use password
		global $CFG, $DB;

		// We try to send the mail in language the user understands,
		// unfortunately the filter_string() does not support alternative langs yet
		// so multilang will not work properly for site->fullname.
		$lang = empty($user->lang) ? get_newuser_language() : $user->lang;

		$site = get_site();

		$supportuser = core_user::get_support_user();

		$newpassword = generate_password();

		update_internal_user_password($user, $newpassword, $fasthash);

		$a = new stdClass();
		$a->firstname = fullname($user, true);
		$a->sitename = format_string($site->fullname);
		$a->username = $user->username;
		$a->newpassword = $newpassword;
		$a->link = $CFG->wwwroot . '/login/?lang=' . $lang;
		$a->signoff = generate_email_signoff();

		$message = (string) new lang_string('newusernewpasswordtext', '', $a, $lang);

		$subject = format_string($site->fullname) . ': ' . (string) new lang_string('newusernewpasswordsubj', '', $a, $lang);

		// Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
		return email_to_user($user, $supportuser, $subject, $message);
	}
}
```
- **if** gửi link để người dùng tạo mật khẩu. **else** gửi mật khẩu.

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
   - Bảng cần dùng: user,local_th_alert_create_user
   - Các câu truy vấn đã dùng:

	```sql
	SELECT u.*, upr.token, upr.timerequested, upr.id as tokenid
	FROM {user} u
	JOIN {local_th_alert_create_user} upr ON upr.userid = u.id
	WHERE upr.token = ? AND u.deleted = 0
	```

2. Thêm Bảng: local_th_alert_create_user

| Trường(cột)     | Kiểu dữ liệu | Bắt buộc |
|-----------------|--------------|----------|
| id              | int          | có       |
| userid          | int          |          |
| timerequested   | char         |          |
| timererequested | int          |          |
| token           | text         |          |


3. Database Diagram: Không

## Method
1. Các Method: local_th_alert_create_user, th_core_login_process_password_set, th_core_login_generate_password_reset
2. Chi tiết các Method:

```php
/**
 * Send the link create password to the user via email.
 *
 * @param stdClass $user A {@link $USER} object
 * @return bool|string Returns "true" if mail was sent OK and "false" if there was an error
 */
function local_th_alert_create_user($user)
```

```php
/**
 * This function processes a user's submitted token to validate the request to set a new password.
 * If the user's token is validated, they are prompted to set a new password.
 * @param string $token the one-use identifier which should verify the password reset request as being valid.
 * @return void
 */
function th_core_login_process_password_set($token)

```

```php
/** Create a new record in the database to track a new password set request for user.
 * @param object $user the user record, the requester would like a new password set for.
 * @return record created.
 */
function th_core_login_generate_password_reset($user)
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_alert_create_user

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)
