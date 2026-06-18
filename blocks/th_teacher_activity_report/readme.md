# Tài liệu Báo cáo hoạt động của Giảng viên trong Khóa học

1. **Tên plugin:** th_teacher_activity_report
2. **Kiểu plugin:** block
3. **Project:** TNU,AOF,TNUT
4. **Chức năng chung:** Thông kê hoạt động của Giảng viên trong Khóa học
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-134
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_teacher_activity_report
# 1. Yêu cầu:
1. Yêu cầu
- Thông kê hoạt động của Giảng viên trong Khóa học
- Đầu vào: 

![image](https://user-images.githubusercontent.com/13426817/209920309-91f3f768-cb98-4162-9b5f-7ccbda2cc5a2.png)
   
- Kết quả trả về dạng bảng

![image](https://user-images.githubusercontent.com/13426817/209920479-879d1293-621c-45ad-9a84-25618680983b.png)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_teacher_activity_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_teacher_activity_report:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
```
2. Hướng dẫn sử dụng
- Truy cập **TH Dashboard** và chọn **Báo cáo hoạt động của Giảng viên**

![image](https://user-images.githubusercontent.com/66956549/209899278-e16442e0-d3d7-436b-afcf-5f85fee7c7a6.png)
- Giao diện chính

![image](https://user-images.githubusercontent.com/66956549/209899480-f19c2236-d94b-45c4-993f-c527eb3334ba.png)

- Có 3 lựa chọn: **Ngày mở môn**, **Môn học** và **Tên Giảng viên**:
  - Ngày mở môn
![image](https://user-images.githubusercontent.com/66956549/209899480-f19c2236-d94b-45c4-993f-c527eb3334ba.png)

     - Chọn **Ngày mở môn** và **Khoảng thời gian**

     ![image](https://user-images.githubusercontent.com/66956549/209900697-25a6c95c-2d58-453f-a03c-5dfe1c911913.png)

     - Chọn **Xem**

    ![image](https://user-images.githubusercontent.com/66956549/209900602-eb826e99-f87d-4539-a971-58b7c167a8be.png)

     - Báo cáo xuất hiện:

    ![image](https://user-images.githubusercontent.com/66956549/209900876-e172eb3d-1d18-46fc-9113-92ca7ba73c81.png)

  - Môn học
![image](https://user-images.githubusercontent.com/66956549/209901170-f8feb1e7-4af7-47d6-9a9c-ff8ca4973bb2.png)

     - Chọn **Chọn Môn học** và **Khoảng thời gian**

     ![image](https://user-images.githubusercontent.com/66956549/209901412-ddfbee6f-b306-4704-8add-03c716757850.png)

     - Chọn **Xem**

    ![image](https://user-images.githubusercontent.com/66956549/209901694-48b1451b-fa2d-4bf1-9912-6461e380aac2.png)

     - Báo cáo xuất hiện:

    ![image](https://user-images.githubusercontent.com/66956549/209901764-957d3a7a-5b6a-4f5d-8c2a-766fe634b584.png)

  - Tên Giảng viên

    ![image](https://user-images.githubusercontent.com/66956549/209902739-6e1f80fe-a4b1-4d47-81a5-2b7abc21ae48.png)

     - Chọn **Giảng viên** và **Khoảng thời gian**

     ![image](https://user-images.githubusercontent.com/66956549/209902861-3825860b-7985-4d16-b1ef-83f6e457c64b.png)

     - Chọn **Xem**

    ![image](https://user-images.githubusercontent.com/66956549/209902961-1b5a1068-31bb-4a9a-84fb-4b651b506237.png)

     - Báo cáo xuất hiện:

    ![image](https://user-images.githubusercontent.com/66956549/209903030-5eeb73b4-b798-4c5d-ae5b-1722ffeed561.png)
        
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: logstore_standard_log, user_lastaccess, course, user, forum_discussions, forum_posts, forum, qaapairs, qaa, enrol, user_enrolments, role_assignments, role
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT *
        FROM {course}
        WHERE startdate >= :startdate 
        AND startdate < :enddate AND visible = 1";
```

```sql
$sql = "SELECT COUNT(ls.id)
        FROM {logstore_standard_log} ls
        WHERE userid = :userid AND courseid = :courseid AND contextlevel=50
            AND target like 'course' AND action like 'viewed' AND realuserid IS NULL
        AND timecreated >= :startdate AND timecreated < :enddate";
```

```sql
$sql = "SELECT COUNT(fp.id)
        FROM {forum_discussions} fd
        JOIN {forum_posts} fp ON fp.discussion = fd.id
        JOIN {course} c ON c.id = fd.course
        JOIN {forum} f ON fd.forum = f.id
        WHERE c.visible = 1 AND f.type LIKE :forum_type AND fp.userid = :userid
            AND c.id = :courseid AND fp.created >= :startdate AND fp.created < :enddate";
```

```sql
$sql = "SELECT COUNT(qa.id)
        FROM {qaa} q
        JOIN {qaapairs} qa ON q.id = qa.qaaid
        JOIN {course} c ON q.course = c.id
        WHERE c.id = :courseid AND c.visible = 1
        AND timecreatedquestion >= :startdate AND timecreatedquestion < :enddate";
```

```sql
 $sql = "SELECT COUNT(qa.id)
        FROM {qaa} q
        JOIN {qaapairs} qa ON q.id = qa.qaaid
        JOIN {course} c ON q.course = c.id
        WHERE c.id = :courseid AND c.visible = 1 AND qa.teacherid = :userid
        AND timecreatedanswer >= :startdate AND timecreatedanswer < :enddate";
```
2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: get_course, get_count_access_course, get_last_access_course, get_count_forum_posts, get_questions_qaa, get_answers_qaa
2. Chi tiết các Method:

- get_course: lấy những khóa học trong đợt mở môn
```php
/**
* get_course function lấy những khóa học trong đợt mở môn
*
* @param  [int]   $course_start_date       Ngày mở môn
* @return [array] danh sách các khóa học
*/
public function get_course($course_start_date)
```

- get_count_access_course: lấy số lần truy cập khóa học
```php
/**
* get_count_access_course function lấy số lần truy cập khóa học
*
* @param [int] $userid         id của người dùng
* @param [int] $courseid       id của khóa học
* @param [int] $startdate      Ngày bắt đầu
* @param [int] $enddate        Ngày kết thúc
* @return int                  số lần truy cập khóa học
*/
public function get_count_access_course($userid, $courseid, $startdate, $enddate)
```

- get_last_access_course: lần cuối người dùng truy cập khóa học
```php
/**
* get_last_access_course function  lần cuối người dùng truy cập khóa học
*
* @param [int] $userid     id của người dùng
* @param [int] $courseid   id của khóa học
* @return string           lần cuối người dùng truy cập khóa học
*/
public function get_last_access_course($userid, $courseid)
```

- get_count_forum_posts: lần cuối người dùng truy cập khóa học
```php
/**
* get_count_forum_posts function lấy số bài viết trong forum trong khoảng thời gian
*
* @param [int] $userid         id của người dùng
* @param [int] $courseid       id của khóa học
* @param [char] $forum_type    kiểu forum (general, news,...)
* @param [int] $startdate      ngày bắt đầu
* @param [int] $enddate        ngày kết thúc
* @return [int]                số bài viết trong forum     
*/
public function get_count_forum_posts($userid, $courseid, $forum_type, $startdate, $enddate)
```

- get_questions_qaa: lấy số câu hỏi trong hoạt động Hỏi/đáp của khóa học
```php
/**
* get_questions_qaa function   lấy số câu hỏi trong hoạt động Hỏi/đáp của khóa học
*
* @param  [int] $courseid      id của khóa học
* @param  [int] $startdate     Ngày bắt đầu
* @param  [int] $enddate       Ngày kết thúc
* @return [int]                số câu hỏi trong Hỏi/đáp
*/
public function get_questions_qaa($courseid, $startdate, $enddate)
```

- get_answers_qaa: lấy số câu trả lời trong hoạt động Hỏi/đáp của khóa học
```php
/**
* get_answers_qaa function lấy số câu trả lời trong hoạt động Hỏi/đáp của khóa học
*
* @param [int] $userid         id của người dùng
* @param [int] $courseid       id của khóa học
* @param [int] $startdate      Ngày bắt đầu
* @param [int] $enddate        Ngày kết thúc
* @return [int]                Số câu trả lời trong Hỏi/đáp
*/
public function get_answers_qaa($userid, $courseid, $startdate, $enddate)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_teacher_activity_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

