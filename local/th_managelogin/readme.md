# Tài liệu phát triển tính năng chống đăng nhập đồng thời trên nhiều thiết bị

1. **Tên tính năng**: Chống đăng nhập đồng thời trên nhiều thiết bị
2. **Project**:  tnu,aof,tnut,vmc
3. **Người phát triển**: minhpl@aum.edu.vn, datdt720@wru.vn
6. **Người yêu cầu**: minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-19
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_managelogin
# 1. Yêu cầu:

- Giới hạn số lần login đồng thời của mobile app

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt

**Quản trị viên truy cập link: Dashboard/ Site administration/ Plugins/ Local plugins/ TH th_managelogin trên hệ thống Web để setup số lượng truy cập trên thiết bị mobile**:

![image](https://user-images.githubusercontent.com/66956549/207557125-df5b6204-e6fc-4975-8797-cba0b7cd8e2e.png)

# 3. Phân tích thiết kế (database, functions nếu cần)

SQL đã dùng:

```sql
$sql = "SELECT *
		from {th_managelogin}
		where userid =  $userid and uuid = '$manageloginobj->uuid'";
```

Tạo Bảng trong CSDL:
Tên bảng: th_managelogin
| Các cột          | Kiểu dữ liệu | Mục đích                                      |
|------------------|--------------|-----------------------------------------------|
| id               | int          | Khóa chính                                    |
| userid           | int          | id của user                                   |
| uuid             | text         | uuid của thiết bị đăng nhập                   |
| loginstatus      | int          | xác định thiết bị có đăng xuất hay không      |
| timecreated      | int          | thời gian tạo bản ghi                         |
| timeloggedin     | int          | thời gian đăng nhập                           |
| isloggedin_valid | int          | xác định thiết bị có được hoạt động hay không |

Có 2 hàm cần chú ý:

```php
/**
     * [Lưu thông tin của thiết bị đăng nhập hoặc đăng xuất]
     * @param  [int] $userid       [id của tài khoản trong bảng user]
     * @param  [text] $uuid        [định danh thiết bị]
     * @param  [int] $loginstatus  [trạng thái đăng nhập. bằng 0 là đăng xuất, bằng 1 là đăng nhập]
     * @return [stdClass]          [debug: $records, isloggedin_valid: 0 or 1 (0 là đăng xuất, 1 là đăng nhập)]
     */
    public static function save_userinfo($userid, $uuid, $loginstatus) 
```
App sẽ gửi thông tin cho hệ thống Web, hệ thống sẽ lưu thông tin đăng nhập của thiết bị và cập nhật lại trạng thái hoạt động của các thiết bị đã đăng nhập.

```php
 /**
     * Kiểm tra thiết bị có được đăng nhập vào hệ thống không.
     *
     * @param  [int]  $userid       [id của tài khoản trong bảng user]
     * @param  [text] $uuid         [định danh thiết bị]
     * @param  [int]  $loginstatus  [trạng thái đăng nhập. bằng 0 là đăng xuất, bằng 1 là đăng nhập]
     * @return [stdClass]           [debug: bản ghi trong CSDL, isloggedin_valid: 0 or 1 (0 là buộc logout, 1 là được login)]
     */
    public static function checklogin_valid($userid, $uuid, $loginstatus)
```

Khi App gửi thông tin cho Hệ thống Web sẽ gửi thông tin về trạng thái của thiết bị đang đăng nhập lại cho App. App sẽ kiểm tra thiết bị xem có được đăng nhập vào hệ thống để học tập hay không

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_managelogin

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

