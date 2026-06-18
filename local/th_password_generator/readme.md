# Tài liệu Tạo mật khẩu cho người dùng mỗi khi tạo tài khoản

1. **Tên plugin:** th_password_generator
2. **Kiểu plugin:** local
3. **Project:** TNU,AOF,VMC
4. **Chức năng chung:** Tạo mật khẩu cho người dùng mỗi khi tạo tài khoản và chọn chức năng sinh mật khẩu
5. **Người phát triển:** minhpl@aum.edu.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_password_generator
# 1. Yêu cầu và Đầu vào, đầu ra:
1. Yêu cầu
- Tạo mật khẩu cho người dùng mỗi khi tạo tài khoản và chọn chức năng sinh mật khẩu

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: không
- Đoạn code định nghĩa capabilities: không có

## 2. Hướng dẫn sử dụng
- Truy cập **Dashboard > Site administration > Plugins > Local plugins > TH password generator**

![image](https://user-images.githubusercontent.com/13426817/159406778-020a2457-d083-44e1-b373-31e7a17e57e1.png)

- `Thay thế defaul password generator`, mặc định là **không**. Nếu là **không** sẽ dùng hàm tạo mật khẩu mặc định của hệ thống.
- `Bao gồm các kí tự đặc biệt`, mặc định là **không**, nếu chọn **có** thì chúng ta cần quan tâm đến `Độ dài chứa kí tự đặc biệt`, mặc định là **2**.

![image](https://user-images.githubusercontent.com/13426817/159406820-b18c2edf-32bf-445b-9ed5-cebfff5741f8.png)

- `Bao gồm chữ thường`, mặc định là **không**. Nếu chọn **có** thì chúng ta cần quan tâm đến `Độ dài chữ thường`, mặc định là **4**.
- `Bao gồm chữ hoa`, mặc định là **không**. Nếu chọn **có** thì chúng ta cần quan tâm đến `Độ dài chữ hoa`, mặc định là **2**.

![image](https://user-images.githubusercontent.com/13426817/159406869-215bd2ef-eca9-44a8-8917-115f781bf8e1.png)

- `Không bao gồm một vài chữ hoặc số có thể giống nhau`, mặc định là **không**, nếu chọn **có** thì chúng ta cần quan tâm đến `Nhập chữ số mà bạn không mong muốn trong mật khẩu`, mặc định là **il1Lo0O**
- `Không bao gồm một vài kí tự không rõ ràng`, mặc định là **không**, nếu chọn **có** thì chúng ta cần quan tâm đến `Nhập kí tự mà bạn không mong muốn trong mật khẩu`, mặc định là **{}[]()/\"`~,;:.<>|**

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: không
- Các câu truy vấn đã dùng: không

2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: randomPassword
2. Chi tiết các Method:
- Tạo mật khẩu
```php
function randomPassword()
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_appcss_aof

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)