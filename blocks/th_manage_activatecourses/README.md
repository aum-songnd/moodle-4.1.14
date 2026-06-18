# Tài liệu Quản lý kích hoạt khóa học

1. **Tên plugin:** th_manage_activatecourses
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Quản lý kích hoạt khóa học để quản lý các tài khoản đã đăng kí vào khóa học nhưng chưa kích hoạt.
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-136
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_manage_activatecourses
   
# 1. Yêu cầu
1. Yêu cầu:
- Quản lý kích hoạt khóa học để quản lý các tài khoản đã đăng kí vào khóa học nhưng chưa kích hoạt.
2. Đầu vào, Đầu ra
- Đăng ký:
    - Đầu vào

    ![image](https://user-images.githubusercontent.com/66956549/210194895-f5a62979-fb08-4fa2-9bb3-712dac2a6b87.png)

    - Đầu ra

    ![image](https://user-images.githubusercontent.com/66956549/210195010-3cfbd6b7-541d-482c-b6fe-27015be3c7d0.png)

- Đăng ký theo lô:
    - Đầu vào

    ![image](https://user-images.githubusercontent.com/66956549/210195065-ddea7b27-2b0e-4abe-b48f-40be9cd31a2a.png)

    - Đầu ra

    ![image](https://user-images.githubusercontent.com/66956549/210195119-14095927-5dcb-4006-b9cb-2d3d614ea5d3.png)

- Hủy Đăng ký theo lô:
    - Đầu vào

    ![image](https://user-images.githubusercontent.com/66956549/210195153-0d5a901a-4433-4659-b91e-efaa30d0454a.png)

    - Đầu ra

    ![image](https://user-images.githubusercontent.com/66956549/210195186-a0e7350f-ddfd-4ddb-9e09-2c1363f8db85.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập và sử dụng tính năng. Đoạn code định nghĩa capabilities
- Các capability cần để truy cập và sử dụng tính năng:
  **block/th_manage_activatecourses:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_manage_activatecourses:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),
```
2. Hướng dẫn sử dụng
- Giao diện chính:
![all](https://user-images.githubusercontent.com/66956549/156317391-8a685693-3740-4aca-ab54-316c558ac868.png)
- Các chức năng Đăng ký, Sửa, Hủy Đăng ký người dùng khỏi Khóa học.
    - Sửa thông tin:
        - Chọn bánh răng trên cột **Thao tác**:
        ![edit1](https://user-images.githubusercontent.com/66956549/156318799-acee399b-6025-42ca-9e74-9a6350c102af.png)
        - Đổi khóa học xong chọn **Gửi**
        ![edit2](https://user-images.githubusercontent.com/66956549/156319583-165668c6-e72b-4b85-b0f1-4fc16ee03472.png)
    - Đăng ký:
        - Chọn Tab **Đăng ký** và chọn thông tin đăng ký, có thể thêm Người dùng vào **Chiến dịch**, nếu chọn **Chiến dịch**
        ![add](https://user-images.githubusercontent.com/66956549/156340487-2028b490-d630-4996-b262-af288fa2b92e.png)
        - Xong chọn **Gửi** để hoàn thành **Đăng ký**
        - Nếu nhiều tài tài cần **Đăng ký**, chọn Tab **Đăng ký theo lô**
        ![adds1](https://user-images.githubusercontent.com/66956549/156341515-f5541254-2eb3-494e-97d5-cb3b4245c622.png)
        - Nhập thông tin **Email,Shortname** khóa học, có thể thêm Người dùng vào **Chiến dịch**, nếu chọn **Chiến dịch**
        ![adds2](https://user-images.githubusercontent.com/66956549/156341963-1cf5820a-cd2b-4d72-b910-c8f988901eb3.png)
        - Chọn **Gửi** hoặc **Hủy bỏ** nếu không **Đăng ký**
        ![adds3](https://user-images.githubusercontent.com/66956549/156342235-a8525679-d453-4127-a442-c1cc1b1f078b.png)
        - Chọn **Thêm người dùng** để xác nhận hoặc **Hủy bỏ** nếu không **Đăng ký**
    - Hủy Đăng ký:
        - Chọn **X** trên cột **Thao tác**:
        ![delete](https://user-images.githubusercontent.com/66956549/156343661-58806f69-4ae7-4138-9ca6-8677c9ad828f.png)
        - Chọn **Tiếp tục** để xóa hoặc **Hủy bỏ** để không xóa:
        ![delete2](https://user-images.githubusercontent.com/66956549/156343984-700d7015-e2c1-4276-9575-efa5afe3e9cc.png)
        - Nếu nhiều tài tài cần **Hủy đăng ký**, chọn Tab **Hủy đăng ký theo lô**
        ![deletes1](https://user-images.githubusercontent.com/66956549/156344915-4e2e0aec-8769-400d-8bf9-a07a91398f34.png)
        - Nhập thông tin **Email,Shortname** khóa học,
        ![deletes2](https://user-images.githubusercontent.com/66956549/156344767-ba5f2026-a3ab-44ec-bfd4-5577ff2458ed.png)
        - Chọn **Gửi** hoặc **Hủy bỏ** nếu không **Hủy đăng ký**
        ![deletes3](https://user-images.githubusercontent.com/66956549/156345390-cad6a318-3a42-43b0-acba-26657b241aba.png)
        - Chọn **Xóa người dùng** để xác nhận **Hủy đăng ký** hoặc **Hủy bỏ** nếu không **Hủy đăng ký**
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng user, th_registeredcourses, course
- Các câu truy vấn đã dùng:
```sql
$sql = "DELETE FROM {th_registeredcourses}
		WHERE id= :id";
```
Hủy đăng ký

```sql
$sql = "SELECT thr.*
        FROM {th_registeredcourses} thr
        JOIN {user} u ON thr.userid=u.id
        JOIN {course} c ON thr.courseid=c.id
        WHERE thr.timeactivated = 0 AND u.deleted=0 AND u.suspended=0";
```
lấy danh sách các người dùng đã đăng ký nhưng chưa kích hoạt

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: get_all_user_register_courses, unregister_courses
2. Chi tiết các Method:
- get_all_user_register_courses: lấy danh sách các người dùng đã đăng ký nhưng chưa kích hoạt
```php  
/**
* get_all_user_register_courses function lấy danh sách các người dùng đã đăng ký nhưng chưa kích hoạt
*
* @return [array]
*/
public static function get_all_user_register_courses()
```

- unregister_courses: Hủy đăng ký
```php
/**
* unregister_courses function Hủy đăng ký
*
* @param [int] $id     id của bảng th_registeredcourses
* @return void
*/
public static function unregister_courses($id)
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_manage_activatecourses

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

