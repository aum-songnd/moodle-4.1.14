# **Tài liệu phát triển tính năng gán nhiều người dùng vào khóa học**

1. **Tên plugin**: th_enrol_course
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Gán nhiều người dùng vào khóa học
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-139
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_enrol_course

# 1. Yêu cầu: (bắt buộc)
- Gán nhiều người dùng cùng lúc bằng hai cách:
    - Cách 1: chọn khóa học (Gán người dùng vào các khóa học được chọn)
    - Cách 2: chọn ngày bắt đầu khóa học (Gán người dùng vào các khóa học có ngày được chọn)

- Enrol bằng khóa học:
    - Đầu vào:
        - Khóa học
        - Quyền
        - Người dùng
    
    - Đầu ra:
        - Tên người dùng
        - Trạng thái
    
- Enrol bằng ngày bắt đầu khóa học:
    - Đầu vào:
        - Ngày
        - Quyền
        - Người dùng

    - Đầu ra:
        - Tên người dùng
        - Trạng thái
    
# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: block/th_enrol_course:view

    ```php
    $capabilities = array(
        'block/th_enrol_course:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            ),
        ),
    );
    ```

- Enrol bằng khóa học:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/169952818-c4995d2e-8e60-4dee-99f7-918e4de589dc.png)

    - Chọn khóa học, quyền và người dùng sau đó nhấn submit hệ thống sẽ trả về thông báo trạng thái ghi danh như hình dưới
    ![image](https://user-images.githubusercontent.com/57883256/169953005-873aa230-26e7-461d-a062-05aab17964ff.png)

- Enrol bằng ngày bắt đầu khóa học:
    - Giao diện chính chức năng:
    ![image](https://user-images.githubusercontent.com/57883256/169952903-51120a1c-44a4-447a-8026-301389080418.png)

    - Chọn ngày bắt đầu khóa học, quyền và người dùng sau đó nhấn nút submit hệ thống sẽ trả về thông báo trạng thái ghi danh như hình dưới
    ![image](https://user-images.githubusercontent.com/57883256/169953099-7ab30585-eda2-4153-bede-35041bbe2570.png)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - course
    - enrol
    - user_enrolments
    - context
    - role_assignments
    - role

- Kiểm tra người dùng đã gán vào khóa học chưa   
    ```sql
    SELECT * FROM {user_enrolments} WHERE userid = '$userid' AND enrolid = '$instance->id'
   ```
- Kiểm tra người dùng đã gán vào khóa học với quyền chưa:

    ```sql
    SELECT * FROM {role_assignments} WHERE userid = $userid AND roleid = $roleid AND contextid = $context_id->id
    ```
**Method:**
- Các Method cần dùng: enrol_user
- enrol_user
```php
/**
     * Enrol user into course via enrol instance.
     *
     * @param stdClass $instance
     * @param int $userid
     * @param int $roleid optional role id
     * @param int $timestart 0 means unknown
     * @param int $timeend 0 means forever
     * @param int $status default to ENROL_USER_ACTIVE for new enrolments, no change by default in updates
     * @param bool $recovergrades restore grade history
     * @return void
     */
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null)
```
# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)


