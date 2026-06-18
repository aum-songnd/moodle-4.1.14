# **Tài liệu phát triển tính năng gửi tin nhắn và email hàng loạt bằng file excel**

1. **Tên plugin**: th_bulk_send_message
2. **Kiểu plugin**: local
3. **Project**: TNU, AOF, TNUT
4. **Chức năng chung**: Gửi tin nhắn hàng loạt cho học viên
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-118
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_bulk_send_message

# 1. Yêu cầu: (bắt buộc)
Gửi tin nhắn và email hàng loạt cho học viên bằng file excel.

Mục đích: quản lý học tập gửi mail thông báo với nội dung riêng cho từng học viên

Học viên được định danh bằng mã học viên trong cột MSV TNU
File excel mẫu:

![image](https://user-images.githubusercontent.com/57883256/207545785-cc8fb43d-8310-4bb0-9fd9-8b75877a733b.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: local/th_bulk_send_message:view

    ```php
    $capabilities = array(
        'local/th_bulk_send_message:view' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        )
    );
    ```


- Cài đặt custom field mã học viên của hệ thống:

![image](https://user-images.githubusercontent.com/57883256/207765104-5333584f-53a4-46ca-8a40-46f26ee2d1dc.png)

- Giao diện chính chức năng:

![image](https://user-images.githubusercontent.com/57883256/207545519-60cc76db-0a83-49ca-a6cb-7a54ad077f8e.png)

- File excel mẫu:

![image](https://user-images.githubusercontent.com/57883256/207545785-cc8fb43d-8310-4bb0-9fd9-8b75877a733b.png)

- Danh sách tin nhắn gửi đi:

![image](https://github.com/thsambala/th/assets/57883256/079ed434-635e-4fa1-b8d0-3191c37eff9e)



# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    
```sql
$user = $DB->get_record_sql("SELECT u.* FROM {user_info_field} as i, {user_info_data} as d, {user} as u 
WHERE (i.shortname = '$ma_hoc_vien1' OR i.shortname = '$ma_hoc_vien2') AND i.id = d.fieldid AND d.data = '$ma_hv' 
AND d.userid = u.id");
```
    
- Bảng mới:

| Tên      | Kiểu         | Chú thích    |
| -------- | ------------ | ------------ |
| id       | int          |              |
| date     | int          | Ngày giờ gửi |
| sender   | varchar(150) | Người gửi    |
| receiver | varchar(150) | Người nhận   |
| subject  | text         | Chủ đề       |
| body     | text         | Nội dung     |

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
