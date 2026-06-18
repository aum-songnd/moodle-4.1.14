# Tài liệu Thông báo cho người dùng mình đã được enrol vào khóa học

1. **Tên plugin:** th_alert_user_enrolled_course
2. **Kiểu plugin:** local
3. **Project:** TNU,AOF
4. **Chức năng chung:** Thông báo cho người dùng mình đã được enrol vào khóa học khi khóa học được hiện.
5. **Người phát triển:** minhpl@aum.edu.vn,linhnt72@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_alert_user_enrolled_course
# 1. Yêu cầu và Đầu vào, đầu ra:
1. Yêu cầu
- Thông báo cho người dùng mình đã được enrol vào khóa học khi khóa học được hiện.

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **th_alert_user_enrolled_course:seeallthings**
- Đoạn code định nghĩa capabilities: 
```php
'local/th_alert_user_enrolled_course:seeallthings' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
```

## 2. Hướng dẫn sử dụng
- Truy cập **Dashboard > Site administration > Plugins > Local plugins > TH th_alert_user_enrolled_course**

![image](https://user-images.githubusercontent.com/66956549/230036231-308ee84b-c529-450f-ab94-420b9efcf09a.png)

- Có 2 phương thức gửi mail:
    - Mỗi ghi danh sẽ gửi 1 email.
    - Gửi toàn bộ mail ghi danh trong ngày vào 22h00.

- Hãy chọn 1 phương thức gửi mail (Mặc định: **Mỗi ghi danh sẽ gửi 1 email**) và chọn **Save changes**

- Nếu chọn **Mỗi ghi danh sẽ gửi 1 email**:
    - Những khóa học hiện sẽ gửi luôn.
    - Những khóa học ẩn thì đến ngày mở môn mới gửi.
- Nếu chọn **Gửi toàn bộ mail ghi danh trong ngày vào 22h00** thì sẽ gửi email ghi danh tất cả khóa học kể cả khóa ẩn.
  
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user_enrolments, enrol, course, task_adhoc
- Các câu truy vấn đã dùng: không
```sql
    SELECT DISTINCT ue.userid 
    FROM {user_enrolments} as ue, {enrol} as e 
    WHERE ue.enrolid = e.id AND ue.timecreated >= $time_start AND ue.timecreated < $time_end;
```

```sql
    SELECT ue.id, e.courseid 
    FROM {user_enrolments} as ue, {enrol} as e 
    WHERE ue.enrolid = e.id AND ue.timecreated >= $time_start AND ue.timecreated < $time_end AND ue.userid = $userid AND ue.status = 0;
```

```php
    $sql = "SELECT *
        from {task_adhoc}
        where " . $DB->sql_like('customdata', ':data');
```
2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: execute, alert
2. Chi tiết các Method:

- Thực hiện công việc gửi email ghi danh 22h00 hằng ngày.
```php
public function execute()
```

- Thực hiện công việc gửi email ghi danh ngay khi ghi danh thành công.
```php
public static function alert()
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_alert_user_enrolled_course

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)
