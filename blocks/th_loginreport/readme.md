# **Tài liệu phát triển tính năng Báo cáo đăng nhập của học viên theo mã khóa học, mã lớp học, học viên

1. **Tên plugin**: th_loginreport
2. **Kiểu plugin**: blocks
3. **Project**: TNU,AOF,TNUT
4. **Chức năng chung**: Báo cáo đăng nhập của học viên theo mã khóa học, mã lớp học, học viên
5. **Người phát triển**: minhpl@aum.edu.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_loginreport

# 1. Yêu cầu: (bắt buộc)
Báo cáo đăng nhập của học viên theo mã khóa học, mã lớp học, học viên

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: blocks/th_loginreport:view

    ```php
    $capabilities = array(
        'block/th_loginreport:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW,
            ),
        ),
    );
    ```
- Xuất báo cáo đăng nhập bằng mã khóa:

![image](https://user-images.githubusercontent.com/57883256/233596687-a79846b6-8b1b-4c5f-a6e3-7a68ad8f49e1.png)

    - Chọn mã khóa
    - Chọn trạng thái học viên trong khóa học
        - Toàn bộ (Lấy tất cả học viên trong khóa học)
        - Hoạt động (Lấy danh sách học viên đang hoạt động)
        - Tạm dừng (Lấy danh sách học viên đang bị tạm dừng)
    - Chọn số lần đăng nhập
    - Chọn khoảng ngày đăng nhập
    - Sau khi chọn đầy đủ các thông tin nhấn xem để xuất báo cáo đăng nhập
    - Hệ thống trả về báo cáo như hình dưới:
![image](https://user-images.githubusercontent.com/57883256/233606565-eef93cef-18cc-4ea1-8710-480d1c833585.png)

- Xuất báo cáo đăng nhập bằng mã lớp

![image](https://user-images.githubusercontent.com/57883256/233597253-f759d2a5-31fe-470d-a011-cd39bcaabb7d.png)

    - Chọn mã lớp
    - Chọn trạng thái học viên trong khóa học
        - Toàn bộ (Lấy tất cả học viên trong khóa học)
        - Hoạt động (Lấy danh sách học viên đang hoạt động)
        - Tạm dừng (Lấy danh sách học viên đang bị tạm dừng)
    - Chọn số lần đăng nhập
    - Chọn khoảng ngày đăng nhập
    - Sau khi chọn đầy đủ các thông tin nhấn xem để xuất báo cáo đăng nhập
    - Hệ thống trả về báo cáo như hình dưới:
![image](https://user-images.githubusercontent.com/57883256/233606565-eef93cef-18cc-4ea1-8710-480d1c833585.png)

- Xuất điểm bằng học viên

![image](https://user-images.githubusercontent.com/57883256/233597650-76f2032b-b99f-4f23-bc15-7f79b7c76fd8.png)

    - Chọn học viên
    - Chọn trạng thái học viên trong khóa học
        - Toàn bộ (Lấy tất cả học viên trong khóa học)
        - Hoạt động (Lấy danh sách học viên đang hoạt động)
        - Tạm dừng (Lấy danh sách học viên đang bị tạm dừng)
    - Chọn vai trò
    - Chọn số lần đăng nhập
    - Chọn khoảng ngày đăng nhập
    - Sau khi chọn đầy đủ các thông tin nhấn xem để xuất báo cáo đăng nhập
    - Hệ thống trả về báo cáo như hình dưới:
![image](https://user-images.githubusercontent.com/57883256/233606565-eef93cef-18cc-4ea1-8710-480d1c833585.png)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - course
    - user_info_data

```sql
SELECT userid,data
        from {user_info_data}
        where fieldid = $fieldid
        and userid $insql_userid
        and " . $DB->sql_compare_text('data') . " = '" . $rolename . "'
```

**Method**
1. Các Method: filter_userarr_by_userstatus
2. Chi tiết Method:

- filter_userarr_by_userstatus
```php
public static function filter_userarr_by_userstatus($userid_arr, $user_status = thlib::USER_STATUS_ALL)
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
