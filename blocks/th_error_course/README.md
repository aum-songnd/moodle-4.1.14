# **Tài liệu phát triển tính năng liệt kê các khóa học bị sai định dạng**

1. **Tên plugin**: th_error_course
2. **Kiểu plugin**: block
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Liệt kê các khóa học bị sai định dạng
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-140
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_error_course

# 1. Yêu cầu: (bắt buộc)
- Liệt kê các khóa học bị sai định dạng
- Đầu vào:
    - Tất cả các khóa học trong hệ thống
- Đầu ra:
    - Tên khóa học
    - Thông báo
    - Action (chỉnh sửa khóa học)
    
    ![image](https://user-images.githubusercontent.com/13426817/210507934-09d0e9ed-8d3f-4551-8139-5b92167d4335.png)
# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: block/th_error_course:view

    ```php
    $capabilities = array(
        'block/th_error_course:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            ),
        ),
    );
    ```

- Các khóa học chuẩn phải có định dạng:
    - Tên khóa học: tenkhoahoc - ngaybatdau (ví dụ: Luật ngân hàng - 220221)
    - Tên rút gọn khóa học: shortname-ngaybatdau (ví dụ: LAWT224-220221)
    - Tên rút gọn và id khóa học phải giống nhau.

- Giao diện danh sách các khóa học bị sai định dạng:
![image](https://user-images.githubusercontent.com/57883256/169966119-4acd6eef-5cf7-4b43-9bd8-e43e986cb595.png)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course
- Lấy danh sách khóa học    
    ```sql
    SELECT * FROM {course} WHERE NOT id = 1
   ```
**Method:**
- Các Method cần dùng: get_list_courses
- get_list_courses
    ```php
    /**
     * Lấy danh sách khóa học.
     * @return object
     */
    function get_list_courses()
    ```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
