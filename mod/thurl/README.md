# **Tài liệu phát triển tính năng Tạo block riêng cho Giảng viên setup lớp học trực tuyến**

1. **Tên plugin**: thurl
2. **Kiểu plugin**: mod
3. **Project**:  tnu
4. **Chức năng chung**: Giảng viên vào edit chỉnh sửa url
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: TNU yêu cầu
7. **Tham chiếu ERP:** TASK-91
8. **Mã nguồn:** https://github.com/minhngb/th/tree/master/mod/thurl

# 1. Yêu cầu:

Viết plugin cho phép edit chỉnh sửa url. Để giáo viên có thể cập nhật link lớp học trực tuyến

- định nghĩa capability: mod_thurl:can_edit_url.
- Chỉ role có capability này mới được chỉnh sửa
- Mặc định editting teacher có capability này


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt

Để chỉnh sửa URL cần có các **Capabilities** sau:
- mod_thurl:can_edit_url

```php
'mod/thurl:can_edit_url' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),
```

**Giảng viên chọn url muốn sửa**:

![image](https://user-images.githubusercontent.com/57883256/204966229-cce7af79-bdbe-4419-80d0-70bf3d13ebe8.png)

**Hệ thống trả về giao diện như hình dưới đổi với người có quyền chỉnh sửa link**:

![image](https://user-images.githubusercontent.com/13426817/205596149-a9c79e3c-7408-4335-be61-8fbc7d95ffa6.png)

**Giảng viên chọn vào chỉnh sửa hệ thống sẽ trả về giao diện như hình dưới**:

![image](https://user-images.githubusercontent.com/57883256/207767295-1a978dd5-cc63-42da-a7d0-24e84101d340.png)

**Nhập các giá trị muốn đổi và nhấn gửi để lưu lại**


**Đối với học viên (không có capabilty: mod_url:can_edit_url )**:

Khi học viên ấn vào link sẽ mở link ngay lập tức mà không vào giao diện chỉnh sửa


# 3. Phân tích thiết kế (database, functions nếu cần)
- Các bảng cần dùng:
    - course_modules
    - course
    - thurl

```sql
$url_edit = $DB->get_record_sql("SELECT * FROM {thurl} WHERE id = '$url_id'");
$cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '$cm_id'");
```
# 4. Mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết)

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)
