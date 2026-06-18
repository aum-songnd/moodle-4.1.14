# Tài liệu chức năng Nhắc nhở giảng viên phê duyệt khóa học, trả lời hỏi đáp 24h, viết bài chào mừng, đăng định hướng học tập, chốt bảng điểm

1. **Tên plugin:** th_alert_scheduledtask
2. **Kiểu plugin:** local
3. **Project:** TNU,AOF
4. **Chức năng chung:** Đặt lịch nhắc nhở giảng viên phê duyệt khóa học, trả lời hỏi đáp 24h, viết bài chào mừng, đăng định hướng học tập, chốt bảng điểm 
5. **Người phát triển:** minhpl@aum.edu.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_alert_scheduledtask
# 1. Yêu cầu và Đầu vào, đầu ra:
1. Yêu cầu
- Đặt lịch nhắc nhở giảng viên phê duyệt khóa học, trả lời hỏi đáp 24h, viết bài chào mừng, đăng định hướng học tập, chốt bảng điểm 

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: không
- Đoạn code định nghĩa capabilities: không có

## 2. Hướng dẫn sử dụng
- Truy cập **Dashboard > Site administration > Plugins > Local plugins > TH th_alert_scheduledtask**

1. Nhắc nhở phê duyệt khóa học

![image](https://user-images.githubusercontent.com/66956549/230269455-40e484e5-3e36-4792-a8dd-7bb3182748ba.png)

- Mặc định là **Gửi email nhắc nhở phê duyệt**. Ngày nhắc nhở trước **2 ngày** mở môn. Có thể tắt vui lòng lòng liên hệ với Quản trị hệ thống.
- Email gửi:
  - Tiêu đề: **Duyệt đề cương chi tiết**
  - Nội dung:
    ```php
    Chào anh/chị,
    Hệ thống Elearning đã thiết lập môn học ngày {$a->startdate}.
    Anh chị vui lòng đăng nhập hệ thống {$a->elearningwebsite} theo tài khoản đã được cung cấp, vào môn dạy của mình và duyệt đề cương chi tiết, tài liệu và giáo án điện tử trên hệ thống bằng cách bấm nút "Phê duyệt" để mở môn học.
    Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật ({$a->hotline}).

    Xin cảm ơn
    ```

2. Nhắc nhở bài giới thiệu trên Forum (Diễn đàn)

![image](https://user-images.githubusercontent.com/66956549/230270046-a0744526-3074-443b-b6f7-b3c7b5b8e01a.png)

- Mặc đình là: **Gửi email nhắc nhở bài giới thiệu trên Forum**. Có thể tắt vui lòng lòng liên hệ với Quản trị hệ thống.
- Ngày trong nội dung thư, mặc định là: **5**.
- Ngày nhắc nhở đăng bài giới thiệu trên Forum, mặc định là: **0**. Nghĩa là đúng ngày mở môn sẽ gửi email.
- Email gửi:
  - Tiêu đề: **Đăng bài chào mừng**.
  - Nội dung:
    ```php
    Chào anh/chị,

    Anh chị cần đăng bài chào mừng hoặc chia sẻ kinh nghiệm tại Diễn đàn lớp học, giới thiệu sơ lược bản thân... trước {$a->duedate}. (Nếu đã thao tác, vui lòng bỏ qua nội dung thư này). Nếu cần hỗ trợ anh chị vui lòng liên hệ hotline: {$a->hotline}.

    Xin cảm ơn
    ```

3. Nhắc nhở đăng lịch học trực tuyến
![image](https://user-images.githubusercontent.com/66956549/230272852-373a6f09-88bc-4aa6-9cee-235075bdd88c.png)

- Mặc định là: **Gửi email nhắc nhở đăng lịch học trực tuyến**. Có thể tắt vui lòng lòng liên hệ với Quản trị hệ thống.
- Ngày trong nội dung thư, mặc định là: **5**.
- Ngày nhắc nhở đăng lịch học trực tuyến, mặc định là **0**.
- Email gửi:
    - Tiêu đề: **Đăng lịch học trực tuyến**
    - Nội dung:
    ```php
    Chào anh/chị,

    Anh chị cần đăng lịch học trực tuyến lên mục Thông báo trước thứ 6 tuần này.

    Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline,{$a->hotline}).
    ```

4. Nhắc nhở trả lời câu hỏi "Hỏi/đáp"

![image](https://user-images.githubusercontent.com/66956549/230273931-8be48e21-2afc-421c-a258-dd321cb4705c.png)

- Mặc định là: **Nhắc nhở trả lời câu hỏi "Hỏi/đáp"**. Có thể tắt vui lòng lòng liên hệ với Quản trị hệ thống.
- Ngày nhắc nhở trả lời câu hỏi "Hỏi/đáp", mặc định là: **7**.
- Email gửi:
    - Tiêu đề: **Trả lời mục "Hỏi/Đáp"**.
    - Nội dung: 
    ```php
    Chào anh/chị,

    Anh chị vui lòng kiểm tra mục "Hỏi/Đáp" và phản hồi các câu hỏi của học
    viên trong vòng 24h kể từ thời điểm học viên đặt câu hỏi.

    Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).
    ```
5. Nhắc nhở đăng định hướng học tập

![image](https://user-images.githubusercontent.com/66956549/230274922-7d94b3a5-ee9b-4411-b211-6a289c35563b.png)

- Mặc định là: **Gửi email Nhắc nhở đăng định hướng học tập**. Có thể tắt vui lòng lòng liên hệ với Quản trị hệ thống.
- Ngày trong nội dung thư, mặc định là: **9,16,23,30,37,44,51**.
- Ngày nhắc nhở Định hướng học tập, mặc định là **7,14,21,28,35,42,49**.
- Email gửi:
    - Tiêu đề: **Đăng định hướng học tập**
    - Nội dung:
    ```php
    Chào anh/chị,

    Anh chị cần đăng bài nhận xét về tuần học vừa qua và định hướng học tập
    hoặc chia sẻ kinh nghiệm học tập cho nội dung học tuần này trước {$a->duedate}.

    Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).
    ```
6. Nhắc nhở chốt bảng điểm

![image](https://user-images.githubusercontent.com/66956549/230275734-96de07cc-4e21-4af7-9dab-b9834fb623a4.png)

- Mặc định là: **Gửi email Nhắc nhở chốt bảng điểm**. Có thể tắt vui lòng lòng liên hệ với Quản trị hệ thống.
- Ngày trong nội dung thư, mặc định là: **51**.
- Ngày Nhắc nhở chốt bảng điểm, mặc định là **49**.
- Email gửi:
    - Tiêu đề: **Chốt bảng điểm bài tập nhóm**
    - Nội dung:
    ```php
    Chào anh/chị,

    Anh chị cần chốt bảng điểm bài tập nhóm (nếu có) trước 23h55, {$a->duedate}.
    Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).

    Xin cảm ơn
    ```
7. Người nhận email

![image](https://user-images.githubusercontent.com/66956549/230275803-94c89a70-c7a7-4c77-b4ab-1deb989678a0.png)
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: course, local_th_alert_scheduledtask
- Các câu truy vấn đã dùng: 
```sql
    SELECT  {course}.id, {course}.fullname
    FROM {course}
    WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";
```

2. Thêm Bảng: local_th_alert_scheduledtask

| Trường(cột) | Kiểu dữ liệu | Bắt buộc |
|-------------|--------------|----------|
| id          | int          | có       |
| courseid    | int          |          |
| component   | char         |          |
| classname   | int          |          |
| customdata  | text         |          |
| lastruntime | int          |          |

3. Database Diagram: Không

## Method
1. Các Method: alert_answer_question, alert_approve_course, alert_chot_bang_diem, alert_viet_nhanxetdanhgia_hangtuan, alert_post_schedule_online_course, alert_write_welcome_to_course_forum
2. Chi tiết các Method:
- Gửi email nhắc nhở trả lời "Hỏi/đáp"
```php
function alert_answer_question() 
```

- Gửi email nhắc nhở Duyệt đề cương chi tiết
```php
function alert_approve_course() 
```
- Gửi email nhắc nhở Chốt bảng điểm bài tập nhóm
```php
function alert_chot_bang_diem() 
```

- Gửi email nhắc nhở Đăng định hướng học tập
```php
function alert_viet_nhanxetdanhgia_hangtuan() 
```

- Gửi email nhắc nhở Đăng lịch học trực tuyến
```php
function alert_post_schedule_online_course() 
```

- Gửi email nhắc nhở Đăng bài chào mừng
```php
function alert_write_welcome_to_course_forum() 
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_alert_scheduledtask

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)
