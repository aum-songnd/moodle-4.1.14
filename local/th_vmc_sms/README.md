# Tài liệu Gửi Tin nhắn nhắc nhở Học viên (Học viên không có Email)

1. **Tên plugin:** th_vmc_sms
2. **Kiểu plugin:** local
3. **Project:** VMC
4. **Chức năng chung:** Để gửi Tin nhắn nhắc nhở Học viên (Học viên không có Email)
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-124
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_vmc_sms
   
# 1. Yêu cầu:
- Gửi Tin nhắn nhắc nhở Học viên (Học viên không có Email)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập và sử dụng tính năng. Đoạn code định nghĩa capabilities
- Các capability cần để truy cập và sử dụng tính năng: không có
- Đoạn code định nghĩa capabilities: không có
2. Hướng dẫn sử dụng
- Dưới đây là giao diện setting:
![image](https://user-images.githubusercontent.com/66956549/156486299-63c7ba80-558f-4f1e-ac8b-7d88c577ca9e.png)
- Tích vào Checkbox để bật gửi Tin nhắn và Nhập số ngày sẽ gửi SMS sau khi gán vào khóa học
    - Gửi thông tin Tài khoản của Người dùng (không có email) (gửi ngay khi tạo tài khoản)
    - Gửi SMS đối với học viên chưa hoàn thành 50% bài giảng (mặc định sau 20 ngày tạo tài khoản)
    - Gửi SMS đối với học viên chưa hoàn thành khóa học (mặc định sau 50 ngày tạo tài khoản)
    - Gửi SMS đối với học viên chưa hoàn thành khóa học (mặc định sau 80 ngày tạo tài khoản)
![image](https://user-images.githubusercontent.com/66956549/156487204-47dd0b0d-973f-4233-91c4-0a0a09c6e7e9.png)
- Xong chọn **Lưu các thay đổi** thì đúng ngày thì sẽ gửi Tin nhắn cho Học viên,

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user, course, enrol, user_enrolments, role_assignments, context, course_modules_completion, course_modules
- Các câu truy vấn đã dùng: 

2. Thêm Bảng: local_th_vmc_sms
   
| Trường(cột) | Kiểu dữ liệu | Bắt buộc |
|-------------|--------------|----------|
| id          | int          | có       |
| courseid    | int          |          |
| component   | char         |          |
| classname   | char         |          |
| customdata  | text         |          |
| lastruntime | int          |          |

3. Database Diagram: Không

## Method
1. Các Method: sendSMS, sendSMSThongBaoTaiKhoan, tin_nhan2, tin_nhan3, tin_nhan4
2. Chi tiết các Method:
- sendSMS: gửi SMS
```php  
/**
 * sendSMS function gửi SMS
 *
 * @param [int] $sodienthoai
 * @param [string] $noidung
 * @param [int] $userid
 * @return void thông báo
 */
function sendSMS($sodienthoai, $noidung, $userid = -1)
```

- sendSMSThongBaoTaiKhoan: Gửi thông tin tài khoản và mật khẩu
```php
/**
 * sendSMSThongBaoTaiKhoan function
 *
 * @param [int] $user
 * @param [string] $password
 * @return void
 */
function sendSMSThongBaoTaiKhoan($user, $password)
```

- tin_nhan2: Gửi SMS đối với học viên chưa hoàn thành 50% bài giảng
```php
/**
 * tin_nhan2 function Gửi SMS đối với học viên chưa hoàn thành 50% bài giảng
 *
 * @return void
 */
function tin_nhan2()
```

- tin_nhan3: Gửi SMS đối với học viên chưa hoàn thành khóa học
```php
/**
 * tin_nhan3 function Gửi SMS đối với học viên chưa hoàn thành khóa học
 *
 * @return void
 */
function tin_nhan3()
```

- tin_nhan4: Gửi SMS đối với học viên chưa hoàn thành khóa học
```php
/**
 * tin_nhan4 function Gửi SMS đối với học viên chưa hoàn thành khóa học
 *
 * @return void
 */
function tin_nhan4()
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_vmc_sms

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)



