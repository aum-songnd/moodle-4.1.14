# **Tài liệu phát triển tính năng Phê duyệt khóa học

1. **Tên plugin**: th_course_status
2. **Kiểu plugin**: blocks
3. **Project**: TNU,AOF,TNUT
4. **Chức năng chung**: Cho phép giảng viên phê duyệt khóa học và tự động hiện khóa học khi khóa học đến thời gian bắt đầu nếu đã được phê duyệt.
5. **Người phát triển**: linhnt720@wru.vn, Minhpl@aum.edu.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_course_status

# 1. Yêu cầu: (bắt buộc)
Cho phép giảng viên phê duyệt khóa học và tự động hiện khóa học khi khóa học đến thời gian bắt đầu nếu đã được phê duyệt.

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: local/th_course_status:addinstance

    ```php
    $capabilities = array(
        'block/th_course_status:addinstance' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_BLOCK,
            'archetypes' => array(
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW,
            ),
            'clonepermissionsfrom' => 'moodle/site:manageblocks',
        ),
    );
    ```

- Giao diện chính chức năng:

![image](https://user-images.githubusercontent.com/57883256/233028164-81d627c1-a052-47f0-a9ab-a443b5d7751a.png)


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - block_th_course_status
    - task_adhoc
    
- Bảng mới: 
    - block_th_course_status

    | Tên           | Kiểu   | Chú thích            |
    | --------      | -------| -----------          |
    | id            | int    |                      |
    | course        | int    | Khóa học             |
    | timecreated   | char   | Thời gian tạo        |
    | timemodified  | char   | Thời gian chỉnh sửa  |
    | teachingid    | text   | id giảng viên        |
    | ishidden      | text   | trạng thái           |


# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
