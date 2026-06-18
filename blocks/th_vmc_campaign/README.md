# Tài liệu Quản lý các **Chiến dịch** và người dùng trong **Chiến dịch**

1. **Tên plugin:** th_vmc_campaign
2. **Kiểu plugin**: block
3. **Project:** VMC
4. **Chức năng chung:** để quản lý các **Chiến dịch** và người dùng trong **Chiến dịch**
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-131
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_vmc_campaign
# 1. Yêu cầu:
1. Yêu cầu: Để quản lý các **Chiến dịch** và người dùng trong **Chiến dịch**

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_vmc_campaign:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_vmc_campaign:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
```
2. Hướng dẫn sử dụng
- Giao diện chính
    ![image](https://user-images.githubusercontent.com/66956549/209790103-c907e32c-bb7e-41ee-9e64-1cef9027c055.png)
- Quản lý Chiến dịch
    - Thêm Chiến dịch:
        - Chọn Tab **Thêm Chiến dịch**:
    ![image](https://user-images.githubusercontent.com/66956549/209790519-3e1851ce-2a9c-43fe-8673-be1964b4b096.png)
        - Nhập thông tin **Chiến dịch**:
    ![image](https://user-images.githubusercontent.com/66956549/209790669-4c79c4a2-a070-4f8f-bf79-9bd7da7364ca.png)
        - Chọn **Lưu những thay đổi** để thêm hoặc **Hủy bỏ** để không thêm
    - Sửa thông tin Chiến dịch
        - Trong Tab **Tất cả Chiến dịch** chọn **bánh răng** để **Sửa**
    ![image](https://user-images.githubusercontent.com/66956549/209790836-957c72cb-0515-4faa-a4ec-e5ebe2fafd09.png)
        - Sửa các thông tin:
    ![image](https://user-images.githubusercontent.com/66956549/209790954-00945271-d20b-4c64-a5d0-90f45d427247.png)
        - Chọn **Lưu những thay đổi** hoặc **Hủy bỏ** nếu không muốn sửa.
    - Xóa Chiến dịch
        - Trong Tab **Tất cả Chiến dịch** chọn **X** để **Xóa**
    ![image](https://user-images.githubusercontent.com/66956549/209791096-48c6d124-d7c7-4400-ba77-c1496213f1d6.png)
        - Chọn **Tiếp tục** để xóa hoặc **Hủy bỏ** để không xóa:
    ![image](https://user-images.githubusercontent.com/66956549/209791214-6f2324b9-8fc1-4830-8b64-80608a58c95b.png)
- Quản lý người dùng trong Chiến dịch
    - Trong Tab **Tất cả Chiến dịch** chọn **Hình người** để vào phần **Quản lý người dùng trong Chiến dich**
    ![image](https://user-images.githubusercontent.com/66956549/209791532-d4a793f9-fa99-47f4-aa9f-b7feeb20397e.png)
    - **Quản lý người dùng** trong **Chiến dịch** xuất hiện:
    ![image](https://user-images.githubusercontent.com/66956549/209791663-97df01c0-2f95-4c9e-85ef-13b2609b17c9.png)
        - Thêm người dùng vào Chiến dịch chọn Thêm người dùng vào Chiến dịch
    ![image](https://user-images.githubusercontent.com/66956549/209791903-18ad0a7b-fca1-4ead-bb34-699114b460ad.png)
        - Nhập thông tin **Email,Shortname** Khóa học để thêm người dùng vào **Chiến dịch**
    ![image](https://user-images.githubusercontent.com/66956549/209792026-5d1184fa-f16c-4a49-a30b-661f6c021c51.png)
        - Chọn **Gửi** để **Thêm** hoặc **Hủy bỏ** để không thêm.
    ![image](https://user-images.githubusercontent.com/66956549/209792175-05e92524-5301-417d-84c7-dcf463d7b61d.png)
        - Chọn **Thêm người dùng** để **Thêm** hoặc **Hủy bỏ** để không Thêm
    - Xóa người dùng khỏi Chiến dịch:
        - Trong cột **Thao tác** chọn **X** để xóa người dùng khỏi **Chiến dịch**
    ![image](https://user-images.githubusercontent.com/66956549/209792636-53f0627e-8db6-4ff7-8a76-8c952aeb4eaf.png)
        - Chọn **Tiếp tục** để xóa hoặc **Hủy bỏ** để không xóa
    ![image](https://user-images.githubusercontent.com/66956549/209792790-e5489cfe-24b6-4b5e-81ce-51f00f5e43c4.png)
        - Nếu xóa nhiều người dùng cùng 1 lúc thì chọn **Xóa người dùng khỏi Chiến dịch**
    ![image](https://user-images.githubusercontent.com/66956549/209792951-88681a09-e636-4acd-bab3-db26ab537bd4.png)
        - Nhập thông tin **Email,Shortname** Khóa học để xóa người dùng khỏi **Chiến dịch**
    ![image](https://user-images.githubusercontent.com/66956549/209793291-1e13f0e6-3a57-4726-82ba-3586e146c1be.png)
        - Chọn **Gửi** để **Xóa** hoặc **Hủy bỏ** để không xóa.
    ![image](https://user-images.githubusercontent.com/66956549/209793399-3b2c4d4a-fa1f-40d7-87ad-f98aead678a9.png)
        - Chọn **Xóa người dùng** để **Xóa** hoặc **Hủy bỏ** để không xóa

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: marketing_campaign, user_campaign_course, course, user
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT u.username,c.fullname 
        FROM {user_campaign_course} ucc
        JOIN {user} u ON u.id=ucc.userid
        JOIN {course} c ON c.id=ucc.courseid
        JOIN {marketing_campaign} mc ON mc.id=ucc.campaignid
        WHERE ucc.id =:user_campaign_course_id
		";
```

2. Thêm Bảng: marketing_campaign, user_campaign_course
- marketing_campaign: lưu các chiến dịch

| Cột                 | Trường | Bắt buộc |
|---------------------|--------|----------|
| id                  | int    | có       |
| campaigncode        | char   | có       |
| campaignname        | char   | có       |
| campaigndescription | text   |          |
| timecreated         | int    | có       |
| timemodified        | int    | có       |

- user_campaign_course: lưu người dùng trong chiến dịch

| Cột          | Trường | Bắt buộc |
|--------------|--------|----------|
| id           | int    | có       |
| userid       | int    | có       |
| courseid     | int    | có       |
| campaignid   | int    | có       |
| timecreated  | int    | có       |
| timemodified | int    | có       |

3. Database Diagram: Không

## Method
1. Các Method: get_username
2. Chi tiết các Method:

- get_username: lấy các người dùng trong chiến dịch
```php
/**
 * get_username function lấy các người dùng trong chiến dịch
 *
 * @param [id] $user_campaign_course_id
 * @return void
 */
function get_username($user_campaign_course_id)
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_vmc_campaign

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

