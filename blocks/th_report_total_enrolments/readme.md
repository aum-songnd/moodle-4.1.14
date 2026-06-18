# Tài liệu Báo cáo Ghi danh Tổng

1. **Tên plugin:** th_report_total_enrolments
2. **Kiểu plugin**: block
3. **Project:** VMC
4. **Chức năng chung:** Báo cáo các Tài khoản được ghi danh vào khóa học theo khoảng thời gian
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 	TASK-141
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_report_total_enrolments
# 1. Yêu cầu:
1. Yêu cầu
- Lấy các Tài khoản được ghi danh vào khóa học theo khoảng thời gian (ngày, tuần, tháng)

2. Đầu vào, Đầu ra
- Đầu vào:

   ![image](https://user-images.githubusercontent.com/66956549/210729137-b68dceb4-df7b-4745-9280-1341086b25db.png)

- Đầu ra:
    
    ![image](https://user-images.githubusercontent.com/66956549/210729622-d3eb4eb3-7d3c-4e2b-ae65-f76e86dc9d73.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **th_report_total_enrolments:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_report_total_enrolments:view' => array(
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

- Nhập các thông tin Đầu vào cần tra cứu (Ngày, Tuần, Tháng). Xong Chọn **Tra cứu**

    ![image](https://user-images.githubusercontent.com/66956549/210730736-cf41454b-217a-441c-8e59-7f7a068f1243.png)

- Bảng đầu ra được hiển thị:
  
  ![image](https://user-images.githubusercontent.com/66956549/210729622-d3eb4eb3-7d3c-4e2b-ae65-f76e86dc9d73.png)

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user_enrolments, course, user, enrol, role_assignments, context, role
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT DISTINCT row_number() OVER() as stt,u.id as userid,
		 c.id as courseid, c.fullname,r.shortname as shortname,ue.status,ue.timestart,ue.timeend
        FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra, {context} cnt, {role} r
        WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid  AND ra.contextid = cnt.id AND cnt.instanceid = c.id AND r.id = ra.roleid AND u.deleted=0 AND ue.timestart>=? AND ue.timestart<=? ORDER BY ue.timestart DESC";
```
Lấy các Học viên được ghi danh trong khoảng thời gian

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: demtk1
2. Chi tiết các Method:
- demtk1: Lấy các Học viên được ghi danh trong khoảng thời gian
```php
/**
 * demtk1 function Lấy các Học viên được ghi danh trong khoảng thời gian
 *
 * @param [type] $startdate     ngày bắt đầu
 * @param [type] $enddate       ngày kết thúc
 * @return [array]
 */
function demtk1($startdate, $enddate)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_report_total_enrolments

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

