# Tài liệu Báo cáo kết thúc ghi danh khoá học

1. **Tên plugin:** th_course_unenrollment_report
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Thông kê Người dùng hết hạn ghi danh
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-104
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_course_unenrollment_report
# 1. Yêu cầu:
1. Yêu cầu
- Báo cáo để thống kê các người dùng bị hết hạn ghi danh trong khóa học theo thời gian (Ngày, Tuần, Tháng)
- Đầu vào: 

![image](https://user-images.githubusercontent.com/66956549/221814758-46da28b4-8722-44c9-94f2-c37209248f2f.png)
   
- Kết quả trả về dạng bảng

![image](https://user-images.githubusercontent.com/66956549/221815430-5a4ebf11-544f-49b6-8b91-e62c1c6e63fc.png)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_course_unenrollment_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_course_unenrollment_report:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
```
2. Hướng dẫn sử dụng

- Giao diện chính:
![image](https://user-images.githubusercontent.com/66956549/221819202-11696142-fff7-4c13-8bbc-3cc2deba53a9.png)

- Chọn khoảng thời gian Muốn xem: Từ ngày, Đến ngày
- Chọn Khóa học muốn Xem. Nếu không chọn sẽ lấy tất cả các khóa học trên hệ thống.
- Chọn loại báo cáo: Ngày, Tuần, Tháng
  
  - Loại báo cáo theo **Ngày:**
    - Đầu vào:
    ![image](https://user-images.githubusercontent.com/66956549/222022474-16d8a288-c777-4504-b056-c472f30a0e29.png)
    - Đầu ra:
    ![image](https://user-images.githubusercontent.com/66956549/222020747-930e360a-1bd7-4202-ad83-db6071274e9f.png)
    - Nếu chọn **Tổng kết kết thúc ghi danh cho cả khóa học**:
    ![image](https://user-images.githubusercontent.com/66956549/222021132-f4b6ada8-ade1-4a79-89f7-368a6136f278.png)
    - Bảng đầu ra sẽ là (Thêm 1 cột tổng ở cuối)
    ![image](https://user-images.githubusercontent.com/66956549/222021408-76c6804c-2173-44c6-b10a-72fa838c7e8a.png)
    - Khi kích vào ô có số lớn hơn 0

  - Loại báo cáo **Tuần, Tháng** tương tự.
- **Chú ý:** 
  - Loại báo cáo theo **Ngày** giới hạn tra cứu mặc định là 30 ngày.
  - Loại báo cáo theo **Tuần** giới hạn tra cứu mặc định là 8 tuần.   
  - Loại báo cáo theo **Tháng** giới hạn tra cứu mặc định là 6 tháng.
  - Có thể thay đổi các giới hạn này trong settings của plugin:    **Dashboard > Site administration > Plugins > Blocks > Course unenrollment report**
  ![image](https://user-images.githubusercontent.com/66956549/222022210-0ad525ea-12f5-4f53-ac72-734f8b77119e.png)    
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user, user_enrolments, course, enrol
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT ue.id, ue.timeend, ue.userid as count_usr, c.fullname, c.shortname
    FROM {course} as c, {user_enrolments} as ue, {enrol} as e
    WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
    AND ue.timeend >= :start_date and ue.timeend <= :end_date";
```

```sql
$sql = "SELECT ue.*
    FROM {course} as c, {user_enrolments} as ue, {enrol} as e
    WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
    AND ue.timeend <= :now AND ue.timeend != 0";
```
1. Thêm Bảng: Không
2. Database Diagram: Không

## Method
1. Các Method: Không
2. Chi tiết các Method: Không

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_course_unenrollment_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

