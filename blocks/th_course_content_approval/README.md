# **Tài liệu phát triển tính năng phê duyệt học liệu**

1. **Tên plugin**: th_course_content_approval
2. **Kiểu plugin**: blocks
3. **Project**: TNU,AOF,TNUT,TUAF
4. **Chức năng chung**: phê duyệt học liệu
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_course_content_approval

# 1. Yêu cầu: (bắt buộc)
Hỗ trợ học viên phê duyệt học liệu

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: 
    - moodle/course:update
    - moodle/course:visibility
    - moodle/course:viewhiddencourses

    ```php
        $capabilities = array(
            'moodle/course:update',
            'moodle/course:visibility',
            'moodle/course:viewhiddencourses',
        );
    ```


**Hướng dẫn sử dụng**

- Trong giao diện khóa học chọn vào nút phê duyệt bên phải màn hình:
    ![image](https://github.com/thsambala/th/assets/57883256/3505ab55-1c66-4883-a269-f01c9fbbe69e)

- Hệ thống sẽ trả về giao diện như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/1cfbc5cd-4eb7-4313-a536-fdbdf1f0e995)

- Chọn học liệu cần phê duyệt và nhấn nút phê duyệt:
    ![image](https://github.com/thsambala/th/assets/57883256/7f84bc03-cd9f-4f45-bc8b-7d177b6b2bb8)

- Sau khi nhấn vào nút phê duyệt sẽ chuyển thành màu xanh và hiển thị thông báo là đã thành công
- Có thể xem lại lịch sử phê duyệt. Chọn nút lịch sử phê duyệt như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/13ff95fc-baa1-4309-826d-7ef8153131fd)
- Hệ thống trả về giao diện như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/c86c9709-d34f-48ad-a20a-93de1ddeaca4)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course
    - course_sections
- Bảng mới:

    - th_course_content_approval

        | Tên        | Kiểu | Chú thích    |
        |------------|------| ------------ |
        | id         | int  | id phê duyệt   |
        | cmid     | int | id cousre module   |
        | course_id    | int | id khóa học  |
        | timecreated | int  | Ngày tạo   |
        | teachingid   | int  |  Người phê duyệt    |
        | ishidden  | int  | trạng thái    |
    
    

**Method**
1. Các Method: course_section_cm_list_item
2. Chi tiết Method:

- course_section_cm_list_item:
```php
/**
 * Renders HTML to display one course module for display within a section.
 *
 * This function calls:
 * {@link core_course_renderer::course_section_cm()}
 *
 * @param stdClass $course
 * @param completion_info $completioninfo
 * @param cm_info $mod
 * @param int|null $sectionreturn
 * @param array $displayoptions
 * @return String
 */
public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array())
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
