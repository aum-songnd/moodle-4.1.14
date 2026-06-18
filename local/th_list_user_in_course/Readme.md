# **Tài liệu phát triển tính năng hiển thị danh sách học viên chưa kích hoạt và phân nhóm trong khóa học VMC**

1. **Tên plugin**: th_list_user_in_course
2. **Kiểu plugin**: local
3. **Project**: VMC
4. **Chức năng chung**: Hiển thị danh sách học viên chưa kích hoạt và phân nhóm trong khóa học VMC
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-149
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_list_user_in_course

# 1. Yêu cầu: (bắt buộc)
Hiển thị danh sách học viên chưa kích hoạt và phân nhóm trong khóa học VMC

Mục đích: Phân nhóm học viên để chăm sóc

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: local/th_list_user_in_course:view

    ```php
    $capabilities = array(
        'local/th_list_user_in_course:view' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        )
    );
    ```


- Truy cập vào khóa học bất kỳ -> chọn thành viên -> chọn danh sách tất cả thành viên như hình dưới:

    ![image](https://user-images.githubusercontent.com/57883256/229968602-3556b8d7-5fc4-4e9d-b7d4-b301ca0ad7d7.png)

- Giao diện chính chức năng:

![image](https://user-images.githubusercontent.com/57883256/229968859-ecfea54b-dd39-4b36-a9c4-021a2b204af9.png)

- Xem danh sách nhóm:

![image](https://user-images.githubusercontent.com/57883256/229969288-3ea93fa2-515b-43a8-b7f9-46f0acc4f062.png)

- Tạo nhóm mới:

![image](https://user-images.githubusercontent.com/57883256/229969504-9ad0f376-0af6-4e23-ba04-1deb1c6f6002.png)

Hệ thống trả về giao diện tạo nhóm như hình dưới:

![image](https://user-images.githubusercontent.com/57883256/229969849-70cf2f8d-9d36-4e8b-9c3e-cf52128c3fb5.png)

Điền tên và mô tả nhóm sau đó nhấn gửi để tạo nhóm

- Chỉnh sửa nhóm:

![image](https://user-images.githubusercontent.com/57883256/229970307-f0399fe3-08dd-4533-950e-e5b7f501cadd.png)

Hệ thống trả về giao diện sửa nhóm như hình dưới

![image](https://user-images.githubusercontent.com/57883256/229971039-7df3f864-2d2c-4d43-b9d9-a5fd88f4ca6f.png)

Chỉnh sửa sau đó nhấn gửi để lưu lại thông tin nhóm

- Xóa nhóm:

![image](https://user-images.githubusercontent.com/57883256/229971400-77bbf27b-bfb3-4dff-91da-79e5386d400c.png)

- Chỉnh sửa danh sách nhóm của học viên:

![image](https://user-images.githubusercontent.com/57883256/229971725-5c9d29fe-2033-424c-98de-6dc0158aabea.png)

Hệ thống trả về giao diện như hình dưới

![image](https://user-images.githubusercontent.com/57883256/229971841-2c6a975a-6ef5-4be0-8655-7ca766dc1561.png)

Chọn nhóm sau đó lưu lại để thêm học viên vào nhóm.


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - user_enrolments
    - enrol
    - course
    - th_registeredcourses

- Danh sách người dùng đã kích hoạt
```sql
select *
    from
      (
      select {user_enrolments}.id, {enrol}.courseid as ue_courseid, {user_enrolments}.userid as ue_userid, {user_enrolments}.timestart, {user_enrolments}.timeend, {user_enrolments}.enrolid, {user_enrolments}.timecreated as ue_timecreated, u.firstname as ue_firstname, u.lastname as ue_lastname, u.phone2 as ue_phone, u.lastaccess as ue_lastaccess, u.username as ue_username, c.fullname as ue_fullname, {user_enrolments}.status
      from {enrol}, {user_enrolments}, {user} as u, {course} as c
      where {enrol}.id = {user_enrolments}.enrolid and {user_enrolments}.userid = u.id and {enrol}.courseid = c.id and c.id = $id) as a
    left join (
      select {th_registeredcourses}.courseid, {th_registeredcourses}.timecreated,
      {course}.fullname, {user}.firstname, {user}.lastname, {user}.username, {th_registeredcourses}.userid, {th_registeredcourses}.timeactivated
      from {th_registeredcourses}, {course}, {user}
      where {th_registeredcourses}.courseid = {course}.id and {th_registeredcourses}.userid = {user}.id and {course}.id = $id
    ) as b
    on a.ue_userid = b.userid and a.ue_courseid = b.courseid;
```

- Danh sách người dùng chưa kích hoạt

```sql
select rc.*, c.fullname, u.id, u.firstname, u.lastname, u.phone2, u.username from {th_registeredcourses} as rc, {course} as c, {user} as u where timeactivated = 0 and rc.courseid = c.id and rc.userid = u.id and rc.courseid = $id
```
    
- Bảng mới: 
    - th_list_user_in_course_group

    | Tên           | Kiểu         | Chú thích     |
    | --------      | ------------ | ------------  |
    | id            | int          |               |
    | ten_nhom      | varchar(500) | Tên nhóm      |
    | mo_ta         | text         | Mô tả         |
    | course_id     | int          | id khóa học   |
    | timecreated   | int          | thời gian tạo |

    - th_user_in_group

    | Tên           | Kiểu         | Chú thích     |
    | --------      | ------------ | ------------  |
    | id            | int          |               |
    | user_id       | varchar(500) | id người dùng |
    | group_id      | text         | id nhóm       |
    | timecreated   | int          | thời gian tạo |

**Method**
1. Các Method: get_user_enrolments, get_user_enrolment_actions, role_fix_names
2. Chi tiết Method:

- get_user_enrolments
```php
/**
     * Gets the enrolments this user has in the course - including all suspended plugins and instances.
     *
     * @global moodle_database $DB
     * @param int $userid
     * @return array
     */
    public function get_user_enrolments($userid)
```

- get_user_enrolment_actions
```php
/**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue)
```

- role_fix_names
```php
/**
 * Prepare list of roles for display, apply aliases and localise default role names.
 *
 * @param array $roleoptions array roleid => roleobject (with optional coursealias), strings are accepted for backwards compatibility only
 * @param context $context the context, null means system context
 * @param int $rolenamedisplay
 * @param bool $returnmenu null means keep the same format as $roleoptions, true means id=>localname, false means id=>rolerecord
 * @return array Array of context-specific role names, or role objects with a ->localname field added.
 */
function role_fix_names($roleoptions, context $context = null, $rolenamedisplay = ROLENAME_ALIAS, $returnmenu = null)
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
