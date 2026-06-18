# **Tài liệu phát triển tính năng Lưu lại nhật ký gửi mail**

1. **Tên plugin**: th_log_email
2. **Kiểu plugin**: local
3. **Project**: TNU,AOF,TNUT
4. **Chức năng chung**: Lưu lại nhật ký gửi mail
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_log_email

# 1. Yêu cầu: (bắt buộc)
Lưu lại nhật ký gửi mail

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Sửa code gốc, vào tệp:/moodle/lib/moodlelib.php, trong hàm **email_to_user** tìm đoạn code:
```php
    if ($mail->send()) {
        set_send_count($user);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    }
```
- Thay thế bằng đoạn code này:
```php
    if ($mail->send()) {
    	
		if (file_exists($CFG->dirroot . '/local/th_log_email/lib.php')) {
            require_once $CFG->dirroot . '/local/th_log_email/lib.php';
            th_log_email(time(), $mail->From, $user->email, $subject, $messagetext);
        }
        set_send_count($user);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } 
```
- Capability có quyền truy cập và sử chức năng: local/th_log_email:view

    ```php
    $capabilities = array(
        'local/th_log_email:view' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        )
    );
    ```

- Giao diện chính chức năng:

![image](https://user-images.githubusercontent.com/57883256/233012972-3162b3b0-cd6e-44fe-bfe3-a47d60d78138.png)

- Có thể tìm kiếm email theo ngày gửi, người gửi, người nhận, chủ đề, nội dung:

![image](https://user-images.githubusercontent.com/57883256/233013282-accb1b75-159f-4261-9193-b669e2e0ca82.png)

- Lưu ý: Tool có chức năng tự động xóa log mail sau 6 tháng.


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - th_log_email

- Danh sách email

```sql
SELECT * FROM {local_th_log_email} ORDER BY id DESC LIMIT $start, $limit
```
    
- Bảng mới: 
    - th_log_email

    | Tên      | Kiểu   | Chú thích  |
    | -------- | -------| -----------|
    | id       | int    |            |
    | date     | int    | Ngày gửi   |
    | sender   | char   | Người gửi  |
    | receiver | char   | Người nhận |
    | subject  | text   | chủ đề     |
    | body     | text   | Nội dung   |

**Method**
1. Các Method: get_user_enrolments, get_user_enrolment_actions, role_fix_names
2. Chi tiết Method:

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
