# Tài liệu Chức năng buộc thay đổi mật khẩu

1. **Tên plugin:** local_th_forcepasswordchange
2. **Kiểu plugin**: local
3. **Project:** TNU,AOF,TNUT,TUAF
4. **Chức năng chung:** buộc người dùng đặt lại mật khẩu (thủ công hoặc lập lịch). 
5. **Người phát triển:** aum.datdt@gmail.com
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_forcepasswordchange
# 1. Yêu cầu:

- Buộc người dùng đặt lại mật khẩu tài khoản của mình.
- Đầu vào: Danh sách người cần đặt lại mật khẩu
- Đầu ra: Người dùng đặt lại mật khẩu của minh.

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
   - Các capability: không có
   - Đoạn code định nghĩa capabilities: không có


## 2. Hướng dẫn tích hợp mã nguồn
- Có hai cách đặt lại mật khẩu:
	- Thủ công: Truy cập: **Dashboard > Site administration > Security > TH Force Password Change**
	![image](https://github.com/thsambala/th/assets/66956549/72b20a74-77a4-4425-876d-48c1267261ec)

		- Mặc định là tích vào ô checkbox thì sẽ gửi mail chứa link đặt lại mật khẩu của người dùng (mật khẩu cũ không vào được nữa).
		- Nếu không tích vào ô checkbox thì khi người dùng đăng nhập với mật khẩu cũ sẽ buộc người dùng đặt lại mật khẩu mới.
		- Chọn **Submit**
	![image](https://github.com/thsambala/th/assets/66956549/b36c332c-db7e-4622-bf6a-f78fe0e283fa)

		- Xác nhận.

	- Tự động:
		- Mặc định: 6 tháng sẽ yêu cầu người dùng đặt lại mật khẩu.
		- Trong setting của plugin có phần cài đặt công việc này.

- Setting của plugin: Truy cập: **Dashboard > Site administration > Plugins > Local plugins > TH Force Password Change**
	![image](https://github.com/thsambala/th/assets/66956549/b11ea832-f031-4290-8a9c-82dd6595cc05)

	- Send the link by email: cái này dùng cho cách **Tự động** đặt lại mật khẩu, sẽ gửi mail chứa link đặt lại mật khẩu (nếu tích). Ngược lại (không tích), người dùng khi đăng nhập sẽ buộc đặt lại mật khẩu. **Mặc định: gửi mail**.
	- Select Roles: Các người dùng có quyền được chọn sẽ mặc định trong danh sách đặt lại mật khẩu. **Mặc định: Người quản lý**.
	- Link time is alive: thời gian link đặt mật khẩu sống. **Mặc định: 1 tuần**.

- Chú ý: 
	- Sau 1 ngày khi link đặt lại mật khẩu hết hạn: token sẽ bị xóa khỏi CSDL.
	- Trước khi gửi mail đặt lại mật khẩu, sẽ đổi mật khẩu người dùng với độ dài 20 ký tự tự sinh của Moodle.


# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
   - Bảng cần dùng: user,local_th_forcepasswordchange
   - Các câu truy vấn đã dùng:

	```sql
	SELECT u.*, upr.token, upr.timerequested, upr.id as tokenid
	FROM {user} u
	JOIN {local_th_forcepasswordchange} upr ON upr.userid = u.id
	WHERE upr.token = ? AND u.deleted = 0
	```

2. Thêm Bảng: local_th_forcepasswordchange

| Trường(cột)     | Kiểu dữ liệu |     Bắt buộc 					  |
|-----------------|--------------|----------------------------|
| id              | int          | có                         |
| userid          | int          | userid   						  |
| timerequested   | char         | thời gian tạo bản ghi      |
| timererequested | int          | thời gian đặt lại mật khẩu |
| token           | text         | token đặt mật khẩu         |


3. Database Diagram: Không

## Method
1. Các Method: local_th_forcepasswordchange_generate_password_reset, local_th_forcepasswordchange_login_process_password_set, local_th_forcepasswordchange_send_mail, th_forcepasswordchange
2. Chi tiết các Method:

```php
/** Create a new record in the database to track a new password set request for user.
 * @param object $user the user record, the requester would like a new password set for.
 * @return record created.
 */
function local_th_forcepasswordchange_generate_password_reset($user)
```

```php
/**
 * This function processes a user's submitted token to validate the request to set a new password.
 * If the user's token is validated, they are prompted to set a new password.
 * @param string $token the one-use identifier which should verify the password reset request as being valid.
 * @return void
 */
function local_th_forcepasswordchange_login_process_password_set($token)

```

```php
/**
 * Send the link create password to the user via email.
 *
 * @param stdClass $user A {@link $USER} object
 * @return bool|string Returns "true" if mail was sent OK and "false" if there was an error
 */
function local_th_forcepasswordchange_send_mail($user) {
```

```php
/**
 * hàm đặt lại mật khẩu trong lập lich
 */
function th_forcepasswordchange()
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_forcepasswordchange

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)
