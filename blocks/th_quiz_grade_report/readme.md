# Tài liệu Báo cáo Điểm có điều kiện
1. **Tên plugin:** th_quiz_grade_report
2. **Kiểu plugin:** block
3. **Project:** TNU,AOF,TNUT
4. **Chức năng chung:** Thông kê các bài Quiz thỏa mãn điểm
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-135
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_quiz_grade_report
# 1. Yêu cầu:
- Xuất Danh sách học viên chưa làm bài luyện tập, chưa làm kiểm tra, chưa đủ điều kiện thi để làm báo cáo và nhắc nhở học viên vào làm bài
- Đầu vào:

![image](https://user-images.githubusercontent.com/66956549/210025186-1aa0e466-088e-47bb-b6b7-995b6d821115.png)
- Đầu ra:

![image](https://user-images.githubusercontent.com/66956549/210025223-581a3499-2462-4733-9634-dc6fb47c87e7.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **th_quiz_grade_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_quiz_grade_report:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
```
2. Hướng dẫn sử dụng
- Truy cập **TH Dashboard** và Chọn **Báo cáo điểm có điều kiện**

![image](https://user-images.githubusercontent.com/66956549/210028284-a0d9fc20-9eb0-4dc1-935e-4073ae5352d5.png)

- **Báo cáo điểm có điều kiện** xuất hiện

![image](https://user-images.githubusercontent.com/66956549/210029050-0cb4e895-8cd2-49f9-814f-c07b3fe9a83b.png)

- Nhập các thông tin vào **Biểu Mẫu** và Chọn **Xem**

![image](https://user-images.githubusercontent.com/66956549/210029160-94b3a2c2-a2ad-4014-a28a-e6d8be83d01e.png)

- Báo cáo xuất hiện

![image](https://user-images.githubusercontent.com/66956549/210029232-ffffc03c-123b-4273-a6cc-b7dd685219a3.png)

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: quiz, grade_items, course, user, course_modules, modules, quiz_grades, enrol, user_enrolments, role_assignments, role, user_info_data, user_info_field, context
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT *
        FROM {course}
        WHERE startdate >= :startdate AND startdate < :enddate AND visible = 1";
```

```sql
$sql = "SELECT q.id,q.name,q.course,gi.gradepass,gi.idnumber,cm.id as cmid,qg.userid,qg.grade
        FROM {quiz} q
        JOIN {grade_items} gi ON gi.iteminstance = q.id AND gi.itemmodule like 'quiz' AND gi.idnumber <> ''
        JOIN {course_modules} cm ON cm.instance = q.id AND cm.course = $courseid AND cm.deletioninprogress = 0 AND cm.visible = 1 AND cm.module = (SELECT DISTINCT m.id FROM {modules} m JOIN {course_modules} cm ON m.id = cm.module WHERE m.name like 'quiz' AND cm.visible = 1)
        LEFT JOIN {quiz_grades} qg ON qg.quiz = q.id AND qg.userid = $userid
        WHERE 1 = 1 AND q.course = $courseid AND q.timeopen >= $this->start_date_quiz AND q.timeopen < $this->end_date_quiz ";
```

```sql
sql = "SELECT u.*,d.data
        FROM {user} u
        JOIN
        (SELECT DISTINCT eu1_u.id
          FROM {user} eu1_u
          JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id AND ej1_ue.status = 0 AND (ej1_ue.timeend = 0 || ej1_ue.timeend > :timeend)
          JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = :ej1_courseid)
          JOIN {role_assignments} ra ON ra.userid = eu1_u.id AND ra.roleid = 5
          WHERE 1 = 1 AND eu1_u.deleted = 0) je
        ON je.id = u.id
        JOIN {user_info_data} d ON d.userid = u.id AND (d.fieldid = (SELECT id FROM {user_info_field} WHERE shortname LIKE :student_cohort AND d.data LIKE :makhoa) OR d.fieldid = (SELECT id FROM {user_info_field} WHERE shortname LIKE :student_cohort2 AND d.data LIKE :makhoa1))
        WHERE u.deleted = 0 AND u.suspended = 0 ORDER BY u.lastname, u.firstname, u.id";
```

```sql
$sql = "SELECT c.id,c.category,c.sortorder,c.shortname,c.fullname,c.idnumber,c.startdate,c.visible,c.defaultgroupingid,c.groupmode,c.groupmodeforce , ctx.id AS ctxid, ctx.path AS ctxpath, ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel, ctx.instanceid AS ctxinstance, ctx.locked AS ctxlocked
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                 WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
             WHERE c.id <> :siteid AND c.visible = 1 AND c.startdate >= :startdate AND c.startdate < :enddate
          ORDER BY c.visible DESC,c.sortorder ASC";
```

2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: get_course, get_quiz_grade, get_enrolled_users, get_all_user_course, enrol_get_all_users_courses
2. Chi tiết các Method:

- get_course: lấy ra những khóa học trong đợt mở môn
```php
/**
* get_course function lấy ra những khóa học trong đợt mở môn
*
* @return [array]
*/
public function get_course()
```

- get_quiz_grade: lấy Học viên có những điểm thoả mãn đầu vào
```php
/**
* get_quiz_grade function  lấy Học viên có những điểm thoả mãn đầu vào
*
* @return [array]
*/
public function get_quiz_grade()
```

- get_enrolled_users: lấy các Học viên thoả mãn đầu vào (các môn trong đợt mở, Mã khóa)
```php
/**
* get_enrolled_users function lấy các Học viên thoả mãn đầu vào (các môn trong đợt mở, Mã khóa)
*
* @param [int] $courseid      id của khóa học
* @param [int] $makhoa        Mã khóa học (AUM062222HC)
* @return [array]
*/
public function get_enrolled_users($courseid, $makhoa)
```

- enrol_get_all_users_courses: Lấy tất cả các khóa học mà Học viên đang học
```php
/**
* enrol_get_all_users_courses function Lấy tất cả các khóa học mà Học viên đang học
* 
* @param [int] $userid         id của Học viên
* @param boolean $onlyactive
* @param [string] $fields
* @param [string] $sort
* @return [array]
*/
public function enrol_get_all_users_courses($userid, $onlyactive = false, $fields = null, $sort = null)
```

- get_all_user_course: lấy tất cả các Học viên thỏa mãn đầu vào
```php
/**
* get_all_user_course function lấy tất cả các Học viên thỏa mãn đầu vào
*
* @return [array]
*/
public function get_all_user_course() 
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_quiz_grade_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

