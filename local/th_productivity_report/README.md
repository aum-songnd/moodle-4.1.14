# Tài liệu Báo cáo năng suất

1. **Tên plugin:** th_productivity_report
2. **Kiểu plugin**: local
3. **Project:** VMC
4. **Chức năng chung:** Hằng tuần, Hằng tháng gửi danh sách các Tài khoản được tạo, được gán đến địa chỉ email được chỉ định trong danh sách.
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_productivity_report
# 1. Yêu cầu:

- Hằng tuần, Hằng tháng gửi danh sách các Tài khoản được tạo, được gán đến địa chỉ email được chỉ định trong danh sách.
- Ngoài ra, còn làm thêm giao diện để xuất báo cáo khi cần thiết:
  - Đầu vào:
  
  ![image](https://github.com/thsambala/th/assets/66956549/d9f2993f-c708-4cb3-9741-273560c0001f)

  - Đầu ra: tệp Excel

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **local/th_productivity_report:view**
- Đoạn code định nghĩa capabilities: không có
```php
    'local/th_productivity_report:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
```

2. Hướng dẫn sử dụng
- Báo cáo này gửi báo cáo hằng tuần, hằng tháng (hằng tuần: 2 giờ sáng thứ 2, hằng tháng: 2 giờ sáng, ngày 1).
- Có thể thay đổi thay đổi trong **Dashboard > Site administration > Server > Tasks > Scheduled tasks**

![image](https://github.com/thsambala/th/assets/66956549/ee9979af-0d45-4511-bc80-08b5c9d401e1)

- Setting của plugin (**Dashboard > Site administration > Plugins > Local plugins > TH th_productivity_report**):

![image](https://github.com/thsambala/th/assets/66956549/325f7255-4575-4bc9-9662-b7ec82f54388)

- Ngoài ra, còn làm thêm giao diện để xuất báo cáo khi cần thiết:
	- Chọn khoảng thời gian muốn xem báo cáo:

	![image](https://github.com/thsambala/th/assets/66956549/d9f2993f-c708-4cb3-9741-273560c0001f)

	- Chọn **Tải xuống** để tải báo cáo về .

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: th_registeredcourses, user, course, user_info_data, user_info_field
- Các câu truy vấn đã dùng:
```sql
SELECT b.*,a.sl
	FROM
	(SELECT thr.id,thr.courseid, count(thr.id) as sl,u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			where u.deleted = 0 and thr.timecreated BETWEEN :from1 AND :to1
			GROUP BY thr.courseid
			) a
	RIGHT JOIN
	(SELECT thr.id,u.firstname,u.lastname, c.fullname,thr.courseid,thr.timecreated,c.startdate,uid.data as crm_code, u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			LEFT JOIN {user_info_data} uid ON thr.userid = uid.userid and uid.fieldid = (
				Select id from {user_info_field} where shortname = :th_crm_code)
			where u.deleted = 0 and thr.timecreated BETWEEN :from2 AND :to2
			order by c.startdate DESC, thr.timecreated DESC) b
	ON a.courseid = b.courseid AND a.id = b.id AND a.userid = b.userid
	order by b.courseid DESC, b.startdate DESC, b.timecreated
```

```sql
SELECT b.*,a.sl
	FROM
	(SELECT thr.id,thr.courseid, count(thr.id) as sl,u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			where u.deleted = 0 and thr.timecreated BETWEEN :from1 AND :to1
			GROUP BY thr.courseid
			) a
	RIGHT JOIN
	(SELECT thr.id,u.firstname,u.lastname, c.fullname,thr.courseid,thr.timecreated,c.startdate,uid.data as crm_code, u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			LEFT JOIN {user_info_data} uid ON thr.userid = uid.userid and uid.fieldid = (
				Select id from {user_info_field} where shortname = :th_crm_code)
			where u.deleted = 0 and thr.timecreated BETWEEN :from2 AND :to2
			order by c.startdate DESC, thr.timecreated DESC) b
	ON a.courseid = b.courseid AND a.id = b.id and a.userid = b.userid
	order by b.courseid DESC, b.startdate DESC, b.timecreated
```

2. Thêm Bảng: không
3. Database Diagram: Không

## Method
1. Các Method: th_send_productivity_report, th_send_month_productivity_report
2. Chi tiết các Method:
```php
/**
 * th_send_productivity_report function gửi báo cáo hằng tuần
 *
 * @return void
 */
function th_send_productivity_report()
```
```php
/**
 * th_send_month_productivity_report function gửi báo cáo hằng tháng
 *
 * @return void
 */
function th_send_month_productivity_report()
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_productivity_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

