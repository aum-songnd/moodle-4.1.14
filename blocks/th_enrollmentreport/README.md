# Tài liệu Báo cáo học viên ghi danh

1. **Tên plugin:** th_enrollmentreport
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Báo cáo học viên ghi danh dùng để lấy các Học viên được gán vào Khóa học theo thời gian (Ngày, Tuần, Tháng)
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-121
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_enrollmentreport
   
# 1. Yêu cầu
1. Yêu cầu:
- Lấy các Học viên được gán vào Khóa học theo thời gian
2. Đầu vào, Đầu ra
- Đầu vào

![image](https://user-images.githubusercontent.com/66956549/210189953-d5c12388-6c72-4cf4-b9a7-90e26592c802.png)

- Đầu ra

![image](https://user-images.githubusercontent.com/66956549/210192159-c5ddd720-942e-4e73-a2da-5537f4cecca2.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập và sử dụng tính năng. Đoạn code định nghĩa capabilities
- Các capability cần để truy cập và sử dụng tính năng:
  **block/th_enrollmentreport:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_enrollmentreport:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	)
```
2. Hướng dẫn sử dụng
- Đầu vào:
    - Khóa học
    - Ngày bắt đầu 
    - Ngày Kết thúc
    - Lựa chọn theo **Ngày**, **Tuần**, **Tháng**.
- Chú ý: Nếu không chọn **Khóa học** nào thì có nghĩa là chọn tất cả các khóa học trong hệ thống.  
- Đầu ra: thông tin Học viên
    - Tên tài khoản
    - Họ và tên
    - Thư điện tử
    - Ngày ghi danh vào Khóa học
    - Ngày kết thúc ghi danh
    - Thời gian là các khoảng thời sẽ được tìm kiếm.
    - Tổng số là số Học viên được ghi danh trong khoảng thời gian.
- Ví dụ:
    - Đầu vào:
        - Chọn Khóa học **GIẢM MỎI MẮT, KHÔ MẮT VÀ LÀM ĐẸP VÙNG DA QUANH MẮT**
        - Ngày bắt đầu: **02/07/2022**
        - Ngày kết thúc: **02/01/2023**
        - Lựa chọn là: theo **Ngày**

        ![image](https://user-images.githubusercontent.com/66956549/210191596-4ad6584b-6b11-41d2-8d80-5b9620b651cb.png)

    - Đầu ra:
        - th_enrollmentreport sẽ tìm các **Học viên** trong 4 khoảng thời gian. Với **Đầu vào** như trên 4 khoảng thời gian sẽ là:
            - 02/01/2023 (ngày kết thúc)
            - 01/01/2023 (lùi 1 ngày so với ngày kết thúc)
            - 31/12/2022 (lùi 2 ngày so với ngày kết thúc)
            - 02/07/2022 - 30/12/2022 (khoảng thời gian còn lại)
        - 4 khoảng thời gian này sẽ được hiển thị trong cột **Thời gian**.

            ![image](https://user-images.githubusercontent.com/66956549/210192159-c5ddd720-942e-4e73-a2da-5537f4cecca2.png)

        - Với lựa chọn **Tuần** và **Tháng** cũng tương tự.
        - 4 khoảng thời gian sẽ là
            - Tuần hiện tại/Tháng hiện tại
            - Lùi 1 tuần/Lùi 1 tháng
            - Lùi 2 tuần/Lùi 2 tháng
            - Khoảng thời gian còn lại
        - **Tuần**

           ![image](https://user-images.githubusercontent.com/66956549/210193864-235b48f3-ea4c-4567-8ac2-2a4d37040d90.png)

        - **Tháng**

            ![image](https://user-images.githubusercontent.com/66956549/210193951-728fd03f-c7ed-4c6d-81fe-5ea67fd91fff.png)

- Có 1 số ô có giá trị là **N/A**, nghĩa là không có giá trị.

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user, user_enrolments, course, enrol, role_assignments
- Các câu truy vấn đã dùng:
```sql
$sql = "SELECT DISTINCT u.id, ue.timestart, ue.timeend
		FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
		WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND ra.roleid=5 AND u.deleted=0 
        AND c.id = $courseid AND ue.timecreated >= $startday AND ue.timecreated < $endday AND e.status = 0 AND u.suspended = 0";
```
Lấy các Học viên được gán vào Khóa học theo khoảng thời gian

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: laytaikhoan
2. Chi tiết các Method:
- laytaikhoan: Lấy các Học viên được gán vào Khóa học theo khoảng thời gian
```php  
/**
 * [laytaikhoan] Lấy các Học viên được ghi danh vào Khóa học theo thời gian
 *
 * @param [int] $startday	Ngày bắt đầu (từ 0 giờ 0 phút 0 giây Ngày bắt đầu)
 * @param [int] $endday		Ngày kết thúc (đến 23 giờ 59 phút 59 giây Ngày kết thúc)
 * @param [int] $courseid	ID của khóa hoc
 * @return [array]          Các Học viên được ghi danh vào khóa học trong khoảng thời gian
 */
function laytaikhoan($startday, $endday, $courseid)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_enrollmentreport

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

