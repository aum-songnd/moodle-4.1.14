# Tài liệu Báo cáo Đăng nhập

1. **Tên plugin:** th_vmc_loginreport
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Báo cáo Đăng nhập giúp tra cứu thông tin Học viên trong Khóa học (lần truy cập cuối, số lần truy cập khóa học, Tình trạng hoàn thành khóa học, trạng thái ghi danh).
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-129
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_vmc_loginreport
# 1. Yêu cầu:
1. Yêu cầu
- Báo cáo Đăng nhập giúp tra cứu thông tin Học viên trong Khóa học (lần truy cập cuối, số lần truy cập khóa học, Tình trạng hoàn thành khóa học, trạng thái ghi danh).

2. Đầu vào, đầu ra
- Đầu vào:

![image](https://user-images.githubusercontent.com/13426817/209927753-496917dd-79c9-4fe3-a997-fcfe990e9acf.png)
- Đầu ra:
 
 ![image](https://user-images.githubusercontent.com/13426817/209927909-b85cc729-5c44-47de-9833-04e83ab93285.png)

	
# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_vmc_loginreport:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_vmc_loginreport:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),
```
2. Hướng dẫn sử dụng
- Giao diện chính
![image](https://user-images.githubusercontent.com/66956549/209770091-aa357710-1297-4702-b4fe-1e8b0c3ee5e4.png)
- Có 2 Lựa chọn để tìm kiếm thông tin là Theo Khóa học và Theo Học viên
    - Theo Khóa học nhập các thông tin, rồi chọn **Tra cứu**

![image](https://user-images.githubusercontent.com/66956549/209770245-98e9b4e2-9d3c-42ff-993a-34eb8d212aa3.png)
        
![image](https://user-images.githubusercontent.com/66956549/209770571-908f49fb-1cf2-4c91-8501-447a38023378.png)
    - Theo Học viên: chọn học viên cần tra cứu, rồi chọn **Tra cứu**

![image](https://user-images.githubusercontent.com/66956549/209771087-31d77327-81e7-4a99-87df-fa4d569209a5.png)
       
![image](https://user-images.githubusercontent.com/66956549/209770989-5f79522d-1e8b-46fd-b8aa-bf15ad134f1a.png)

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng course, user, enrol, user_enrolments, role_assignments, logstore_standard_log, user_lastaccess, th_registeredcourses
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT DISTINCT ue.userid
		FROM {course} c, {user} u, {enrol} e, {user_enrolments} ue, {role_assignments} ra
		WHERE
		c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND u.deleted=0 AND c.id=:courseid AND u.suspended = 0";
```

```sql
$sql = "SELECT u.id
		FROM {user} u JOIN {th_registeredcourses} r ON u.id =  r.userid
		WHERE r.courseid = :courseid AND r.timeactivated=0 AND u.deleted=0 AND u.suspended=0";
```

```sql
$sql = "SELECT m.id, m.userid, m.fullname, ul.timeaccess
        FROM {user_lastaccess} ul
        RIGHT JOIN (
            SELECT DISTINCT ue.userid, c.fullname, c.id
            FROM
            {course} c, {user} u, {enrol} e, {user_enrolments} ue, {role_assignments} ra
            WHERE
            c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND u.deleted=0 AND u.id=:userid AND e.status = 0 AND u.suspended = 0
        ) m
        ON ul.courseid = m.id AND ul.userid = m.userid ORDER BY ul.timeaccess DESC";
```

```sql
$sql = "SELECT r.courseid
        FROM {user} u JOIN {th_registeredcourses} r ON u.id =  r.userid
        WHERE r.userid = :userid AND r.timeactivated=0 AND u.deleted=0 AND u.suspended=0";
```

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: th_loginreport_get_last_access, th_loginreport_get_count_access_course
2. Chi tiết các Method:
- th_loginreport_get_count_access_course: đếm số lần truy cập vào khóa học
```php
/**
 * th_loginreport_get_count_access_course function đếm số lần truy cập vào khóa học
 *
 * @param [int] $userid         id của người dùng
 * @param [int] $courseid       id của khóa học
 * @return [int]
 */
function th_loginreport_get_count_access_course($userid, $courseid)
```

- th_loginreport_get_last_access: lấy thời gian truy cập lần cuối vào khóa học
```php
/**
 * th_loginreport_get_last_access function lấy thời gian truy cập lần cuối vào khóa học
 *
 * @param [type] $userid        id của người dùng
 * @param [type] $courseid      id của khóa học
 * @return [int]
 */
function th_loginreport_get_last_access($userid, $courseid)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_vmc_loginreport

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

