# **Tài liệu phát triển tính năng Xuất báo cáo điểm theo mã khóa, mã lớp, học viên, từ ngày đến ngày

1. **Tên plugin**: th_gradereport
2. **Kiểu plugin**: blocks
3. **Project**: TNU,AOF,TNUT
4. **Chức năng chung**: Xuất báo cáo điểm theo mã khóa, mã lớp, học viên, từ ngày đến ngày
5. **Người phát triển**: minhpl@aum.edu.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_gradereport

# 1. Yêu cầu: (bắt buộc)
Xuất báo cáo điểm theo mã khóa, mã lớp, học viên, từ ngày đến ngày

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: blocks/th_gradereport:view

    ```php
    $capabilities = array(
        'block/th_gradereport:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_BLOCK,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW,
            ),
        ),
    );
    ```
- Xuất điểm bằng mã khóa:

![image](https://user-images.githubusercontent.com/57883256/233544121-be08c93c-69ec-4106-bf26-9e0a492018dc.png)

    - Chọn mã khóa
    - Chọn mã môn học 
    - Chọn trạng thái học viên trong khóa học
        - Toàn bộ (Lấy tất cả học viên trong khóa học)
        - Hoạt động (Lấy danh sách học viên đang hoạt động)
        - Tạm dừng (Lấy danh sách học viên đang bị tạm dừng)
    - Chọn khoảng ngày được ghi danh vào môn học
    - Chọn loại báo cáo
        - Tổng quan 
        - Chi tiết
    - Sau khi chọn đầy đủ các thông tin nhấn submit để xuất báo cáo điểm
    - Hệ thống trả về giao diện như hình dưới:
![image](https://user-images.githubusercontent.com/57883256/233608000-0e8d4552-9e86-4d6a-8e03-71cfbb501aaa.png)

- Xuất điểm bằng mã lớp

![image](https://user-images.githubusercontent.com/57883256/233588249-96d87c2a-83db-44db-a848-f754fc64ecf9.png)

    - Chọn mã lớp
    - Chọn mã môn học 
    - Chọn trạng thái học viên trong khóa học
        - Toàn bộ (Lấy tất cả học viên trong khóa học)
        - Hoạt động (Lấy danh sách học viên đang hoạt động)
        - Tạm dừng (Lấy danh sách học viên đang bị tạm dừng)
    - Chọn khoảng ngày được ghi danh vào môn học
    - Chọn loại báo cáo
        - Tổng quan 
        - Chi tiết
    - Sau khi chọn đầy đủ các thông tin nhấn submit để xuất báo cáo điểm
    - Hệ thống trả về giao diện như hình dưới:
![image](https://user-images.githubusercontent.com/57883256/233608000-0e8d4552-9e86-4d6a-8e03-71cfbb501aaa.png)

- Xuất điểm bằng học viên

![image](https://user-images.githubusercontent.com/57883256/233588720-6ebfb6fe-0698-442e-b7a5-6f94459f1ab9.png)

    - Chọn học viên
    - Chọn mã môn học 
    - Chọn trạng thái học viên trong khóa học
        - Toàn bộ (Lấy tất cả học viên trong khóa học)
        - Hoạt động (Lấy danh sách học viên đang hoạt động)
        - Tạm dừng (Lấy danh sách học viên đang bị tạm dừng)
    - Chọn khoảng ngày được ghi danh vào môn học
    - Chọn loại báo cáo
        - Tổng quan 
        - Chi tiết
    - Sau khi chọn đầy đủ các thông tin nhấn submit để xuất báo cáo điểm
    - Hệ thống trả về giao diện như hình dưới:
![image](https://user-images.githubusercontent.com/57883256/233608000-0e8d4552-9e86-4d6a-8e03-71cfbb501aaa.png)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - course
    - user_enrolments
    - grade_items
    - grade_grades

```sql
SELECT user_course.* ,{grade_grades}.finalgrade
				from(
					select row_number() OVER (Order by a.userid) as id, a.*
					from (
						select {user}.id as userid , course.fullname, course.id as courseid
						from
							{user}
							left join
							{user_enrolments} ue
							on {user}.id = ue.userid
							AND ((ue.timestart > :timefrom1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated > :timefrom2))
					AND ((ue.timestart < :timeend1 and ue.timestart!=0) OR (ue.timestart = 0 AND ue.timecreated < :timeend2))
							left join {enrol}
							on {enrol}.id = ue.enrolid
							left join {course} course
							on course.id = {enrol}.courseid and course.visible = 1
							where {user}.id $insql
						) a
						group by userid, courseid
					) user_course
				left join {grade_items}
				on {grade_items}.courseid = user_course.courseid and {grade_items}.itemtype='course'
				left join {grade_grades}
				on {grade_grades}.itemid = {grade_items}.id and {grade_grades}.userid = user_course.userid
```

**Method**
1. Các Method: grade_format_gradevalue
2. Chi tiết Method:

- grade_format_gradevalue
```php
/**
 * Returns string representation of grade value
 *
 * @param float $value The grade value
 * @param object $grade_item Grade item object passed by reference to prevent scale reloading
 * @param bool $localized use localised decimal separator
 * @param int $displaytype type of display. For example GRADE_DISPLAY_TYPE_REAL, GRADE_DISPLAY_TYPE_PERCENTAGE, GRADE_DISPLAY_TYPE_LETTER
 * @param int $decimals The number of decimal places when displaying float values
 * @return string
 */
function grade_format_gradevalue($value, &$grade_item, $localized=true, $displaytype=null, $decimals=null)
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
