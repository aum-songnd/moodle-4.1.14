# Tài liệu Thông kê Lộ trình của học viên theo khóa học, học viên và ngày mở môn

1. **Tên plugin:** th_roadmap_report
2. **Kiểu plugin:** block
3. **Project:** Vstep
4. **Chức năng chung:** Thông kê Lộ trình của học viên theo khóa học, học viên, ngày mở môn.
5. **Người phát triển:** aum.datdt@gmail.com
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th_lms/blob/master/blocks/th_roadmap_report
9. Yêu cầu
- Thông kê Lộ trình của học viên theo khóa học, học viên, ngày mở môn.
- Chỉ xuất lộ trình các khóa test đầu vào.
- Đầu vào: 

![image](https://github.com/minhpl/image_repository/assets/66956549/6aec9ae4-35ca-4ba8-bf12-1be0bcf7a7ff)
   
- Kết quả trả về dạng bảng

![image](https://github.com/minhpl/image_repository/assets/66956549/9a58eca7-08b6-4899-b0e8-cd16e2ac4abc)

![image](https://github.com/minhpl/image_repository/assets/66956549/1913ad12-bff0-460c-b8a6-fc6ce1b6c1e1)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_roadmap_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_roadmap_report:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
```
- Chỉ xuất lộ trình các khóa test đầu vào nên các khóa xuất đầu vào để vào 1 danh mục có idnumber xác định sẽ giống với idnumber trong setting của plugin (idnumber = ktdv).
2. Hướng dẫn sử dụng
- Truy cập **TH Dashboard** và chọn **Báo cáo Lộ trình**

- Chọn khóa học hoặc Học viên, Ngày mở môn muốn xem:
![image](https://github.com/minhpl/image_repository/assets/66956549/c597f4f0-5a39-4310-a419-9c8b42b92a38)
![image](https://github.com/minhpl/image_repository/assets/66956549/388fcf79-68ea-43fb-8dec-68827e4fe5fd)
![image](https://github.com/minhpl/image_repository/assets/66956549/6687edb1-3cbf-470e-8001-9c2224dc547f)

- Chọn **Xem** và **Báo cáo Xuất hiện**
![image](https://github.com/minhpl/image_repository/assets/66956549/0c086128-e840-40ab-9b5f-afcb33ed19b0)
![image](https://github.com/minhpl/image_repository/assets/66956549/b7c83f87-5a0e-44b6-86e3-9be496cf7a60)
        
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: course_categories, course, user_enrolments, enrol, grade_items, grade_grades, local_th_roadmap_vstep
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT c.id
        FROM {course} c
        JOIN {course_categories} cc ON cc.id = c.category
        where cc.idnumber = :idnumber";
```
```sql
$sql = "SELECT c.id,fullname,shortname 
        FROM {course} c
        JOIN {course_categories} cc ON cc.id = c.category
        where cc.idnumber = :idnumber AND c.startdate >= :startdate AND c.startdate <= :enddate";
```
```sql
$sql = "SELECT ue.userid
        FROM {user_enrolments} ue
        JOIN {enrol} e ON e.id = ue.enrolid
        WHERE ue.userid $usql
            AND ue.status = :uestatus
            AND e.status = :estatus
            AND e.courseid = :courseid
            AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
        GROUP BY ue.userid";
```

```sql
$sql = "SELECT g.*
        FROM {grade_items} gi,
            {grade_grades} g
        WHERE g.itemid = gi.id AND gi.courseid = :courseid {$this->userselect}";
```
2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: get_right_rows
- Có hai class **th_roadmap_summary_grade_report_grader** và **th_roadmap_detail_grade_report_grader**, kế thừa class core **grade_report_grader**
2. Chi tiết các Method:
- get_right_rows: Sửa và thêm Lộ trình
 ```php
    /**
     * Builds and returns the rows that will make up the right part of the grader report
     * @param boolean $displayaverages whether to display average rows in the table
     * @return array Array of html_table_row objects
     */
    public function get_right_rows($displayaverages)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th_lms/blob/master/blocks/th_roadmap_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

