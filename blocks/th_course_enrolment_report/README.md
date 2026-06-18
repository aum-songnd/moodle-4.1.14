# **Tài liệu phát triển tính năng xuất danh sách thông tin các học viên được gán vào khóa học trong khoảng thời gian được chọn**

1. **Tên plugin**: th_course_enrolment_report
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT, TUAF
4. **Chức năng chung**: Xuất ra danh sách thông tin các học viên được gán vào khóa học trong khoảng thời gian được chọn
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-138
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_course_enrolment_report

# 1. Yêu cầu: (bắt buộc)

- Xuất ra danh sách thông tin các học viên được gán vào khóa học trong khoảng thời gian được chọn

- Xuất theo khóa học
    - Đầu vào:
        - Khoảng thời gian tìm kiếm
        - Khóa học (Nếu không chọn gì thì tìm tất cả các khóa học)
    - Đầu ra:
        - Họ và tên
        - Quyền
        - Khóa học
        - Tên tắt (shortname)
        - Ngày gán
        - Ngày kích hoạt
        - Ngày kết thúc
        - Trạng thái
        - Trạng thái hoàn thành

![image](https://github.com/minhpl/image_repository/assets/66956549/474d47c9-ba00-414b-a944-3de44ec25efd)
- Xuất theo học viên
    - Đầu vào:
        - Khoảng thời gian tìm kiếm
        - Học viên (Nếu không chọn gì thì tìm tất cả học viên)
    - Đầu ra:
        - Họ và tên
        - Quyền
        - Khóa học
        - Tên tắt (shortname)
        - Ngày gán
        - Ngày kích hoạt
        - Ngày kết thúc
        - Trạng thái
        - Trạng thái hoàn thành

![image](https://github.com/minhpl/image_repository/assets/66956549/c52bcdea-7a1c-4f2a-b9c7-dabd189ae466)
# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: block/th_course_enrolment_report:view

    ```php
    $capabilities = array(
        'block/th_course_enrolment_report:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        ),
    );
    ```

**Xuất theo khóa học**
- Giao diện chính chức năng:

![image](https://github.com/minhpl/image_repository/assets/66956549/474d47c9-ba00-414b-a944-3de44ec25efd)

- Chọn khoảng thời gian tìm kiếm và chọn khóa học sau đó nhấn submit hệ thống sẽ trả về báo cáo như hình dưới

![image](https://github.com/minhpl/image_repository/assets/66956549/62c2b16a-e945-4046-a179-6435b02620ea)

- Lưu ý: 
    - Nếu để trống không chọn môn học nào thì sẽ mặc định chọn tất cả khóa học.

**Xuất theo học viên**
- Giao diện chính chức năng:
    ![image](https://github.com/minhpl/image_repository/assets/66956549/c52bcdea-7a1c-4f2a-b9c7-dabd189ae466)
- Chọn khoảng thời gian tìm kiếm và chọn học viên sau đó nhấn submit hệ thống sẽ trả về báo cáo như hình dưới

![image](https://github.com/minhpl/image_repository/assets/66956549/62c2b16a-e945-4046-a179-6435b02620ea)

- Lưu ý: 
    - Nếu để trống học viên sẽ mặc định chọn tất cả học viên

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - user_enrolments
    - enrol
    - th_registeredcourses
    - role_assignments
    - context

- Lấy danh sách học viên đã kích hoạt trong tất cả khóa học từ startdate đến enddate
    ```sql
    SELECT DISTINCT(ue.id), u.id as userid, u.firstname, u.lastname, u.username, c.fullname, c.id as courseid, 
                ue.status, ue.timestart, ue.timeend,c.shortname
                FROM {user} u 
                JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.timecreated >= '$startdate' AND ue.timecreated <= '$enddate'
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id 
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
                JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = 5 AND ctx.id = ra.contextid
                WHERE c.visible = 1 AND ue.status = 0 AND u.id AND c.id = $course_id AND u.suspended = 0 AND u.deleted = 0
                ORDER BY c.fullname, u.lastname, u.firstname
   ```
- Lấy danh sách học viên chưa kích hoạt trong tất cả khóa học từ startdate đến enddate:

    ```sql
   select rc.*, c.fullname, u.firstname, u.lastname, u.username, c.shortname from {th_registeredcourses} as rc, {course} as c, 
    {user} as u where timeactivated = 0 and rc.courseid = $course_id and rc.courseid = c.id and rc.userid = u.id and rc.timecreated >= $startdate 
    and rc.timecreated <= $enddate AND u.suspended = 0 AND u.deleted = 0
    ```
**Method:**
- Các Method cần dùng: is_course_complete, is_enrolled

- is_course_complete
```php
/**
     * Has the supplied user completed this course
     *
     * @param int $user_id User's id
     * @return boolean
     */
    public function is_course_complete($user_id)
```

- is_enrolled
```php
/**
 * Returns true if user is enrolled (is participating) in course
 * this is intended for students and teachers.
 *
 * Since 2.2 the result for active enrolments and current user are cached.
 *
 * @param context $context
 * @param int|stdClass $user if null $USER is used, otherwise user object or id expected
 * @param string $withcapability extra capability name
 * @param bool $onlyactive consider only active enrolments in enabled plugins and time restrictions
 * @return bool
 */
function is_enrolled(context $context, $user = null, $withcapability = '', $onlyactive = false)
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)


