# Tài liệu Chức năng Gửi email nhắc nhở sinh viên học tập

1. **Tên plugin:** th_vmc_alert
2. **Kiểu plugin:** local
3. **Project:** VMC
4. **Chức năng chung:** Gửi email nhắc nhở sinh viên học tập
5. **Người phát triển:** minhpl@aum.edu.vn, linhnt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_vmc_alert
# 1. Yêu cầu và Đầu vào, đầu ra:
1. Yêu cầu
- Gửi email nhắc nhở sinh viên học tập.

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: không
- Đoạn code định nghĩa capabilities: không

## 2. Hướng dẫn sử dụng
- Truy cập **Dashboard > Site administration > Plugins > Local plugins > TH th_vmc_alert**
- Có 6 email:
### 1. Nhắc nhở học viên chưa login 
  
  ![image](https://user-images.githubusercontent.com/66956549/230568252-3a5b16ea-22c8-4f35-bb01-e6b0490b5650.png)

- Nhắc đối với học viên chưa login, mặc định là: **không** nhắc nhở.
- Ngày nhắc nhở học viên chưa login, mặc định là: **2**
- Email gửi:
  - Tiêu đề: Thông Báo Tài Khoản
  - Nội dung:
    ```php
    {$a->userfullname} thân mến!

    Tài khoản của bạn để truy cập các khóa học Chăm Sóc Sức Khỏe Chủ Động của Trung tâm VMC Việt Nam tại hệ thống học trực tuyến elearning.vmcvietnam.org đã được tạo, thông tin tài khoản đã được gửi đến email của bạn rồi đấy!
    Hãy kiểm tra email, đăng nhập, đổi mật khẩu và bắt đầu thực hành theo các chỉ dẫn của giảng viên Trung tâm VMC nhé!

    Sức khỏe của bạn là niềm vui của chúng tôi.

    Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
    -	Hotline: 0988 622 194 / 096 600 0643
    -	Email: trungtamvmc@gmail.com

    Trân trọng cảm ơn!
    Trung tâm VMC Việt Nam
    ```
### 2. Nhắc nhở đôn đốc học tập, làm bài kiểm tra
  ![image](https://user-images.githubusercontent.com/66956549/230569582-45bee196-a412-4a34-b0b9-5918d9e1a273.png)

- Nhắc nhở đôn đốc học tập, làm bài kiểm tra, mặc định là: **không** nhắc nhở.
- Ngày nhắc nhở đôn đốc học tập, mặc định là: **15**.
- Email gửi:
  - Tiêu đề: Thông Báo Học Tập và Hoàn Thành Khóa Học
  - Nội dung: 
    ```php
    {$a->userfullname} thân mến!

    Chắc hẳn bạn đã biết được rất nhiều điều hữu ích cho sức khỏe của mình thông qua khóa học {$a->coursefullname}.
    => Nếu bạn vẫn chưa học xong khóa học, hãy tiếp tục chăm chỉ.
    => Nếu bạn đã hoàn thành xong khóa học, bạn hoàn toàn có thể ôn lại lần nữa và tiếp tục thực hành trên cơ thể mình hoặc người thân trong gia đình.
    Ngoài việc thực hành theo các bài hướng dẫn, hãy làm các bài luyện tập trắc nghiệm giúp bạn kiểm tra lại những điều mình đã được lĩnh hội nhé!
    Để được cấp “Chứng nhận hoàn thành khóa học” của Trung tâm VMC và tham gia các lớp chia sẻ chuyên sâu trực tiếp với giảng viên thì việc hoàn thành bài kiểm tra trắc nghiệm cuối khóa là BẮT BUỘC.

    VMC gửi đến bạn lời chúc sức khỏe và bình an!

    Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
    -	Hotline: 096 600 0643
    -	Email: trungtamvmc@gmail.com

    Trân trọng cảm ơn!
    Trung tâm VMC Việt Nam
    ```
### 3. Thông báo sắp kết thúc tài khoản trong khóa học
![image](https://user-images.githubusercontent.com/66956549/230570437-f4b2c9d6-458f-421e-a46e-52fb7a5755ac.png)

- Thông báo sắp kết thúc tài khoản trong khóa học, mặc định là: **không** thông báo.
- Ngày nhắc nhở thông báo tài khoản sắp kết thúc, tính từ thời điểm ghi danh lùi về , mặc định là: **10**.
- Email gửi:
    - Tiêu đề: Thông Báo Thời Điểm Kết Thúc Khoá Học
    - Nội dung:
    ```php
    {$a->userfullname} thân mến!

    Chúc mừng bạn đã và đang đồng hành cùng với khóa học {$a->coursefullname}.
    Bạn còn {$a->dayremain} ngày nữa để học trước khi kết thúc (vào ngày {$a->duedate}). Hãy chắc chắn rằng bạn đã học tập xong và nắm vững tất cả những kĩ năng được giảng viên hướng dẫn nhé.
    Trong quá trình thực hành, nếu có bất kì thắc mắc gì về hệ thống hoặc kiến thức chuyên môn, đừng ngần ngại mà hãy phản hồi lại cho chúng tôi qua:
    -	Diễn đàn thảo luận chung
    -	Cán bộ quản lý học tập: 0903428169
    -	Hotline: 096 600 0643
    -	Email: trungtamvmc@gmail.com

    Trung tâm VMC Việt Nam luôn luôn đồng hành cùng bạn.
    Gửi tới bạn và gia đình lời chúc sức khỏe, bình an và hạnh phúc!
    Trân trọng cảm ơn!
    Trung tâm VMC Việt Nam
    ```

### 4. Thông báo tài khoản trong khóa học kết thúc

![image](https://user-images.githubusercontent.com/66956549/230571298-99125460-9aa7-4cd1-915d-70984d33277b.png)

- Thông báo tài khoản trong khóa học kết thúc, mặc định là **không** thông báo.
- Ngày nhắc nhở thông báo tài khoản trong khóa học kết thúc, tính từ thời điểm hạn ghi danh khóa học lùi về , mặc định là: **0**
- Email gửi:
  - Tiêu đề: Thông Báo Kết Thúc Khóa Học
  - Nội dung: 
    ```php
    {$a->userfullname} thân mến!
    Chúc mừng bạn đã đồng hành một thời gian dài với khóa học {$a->coursefullname}.
    Khóa học của bạn đã đến thời hạn kết thúc (vào ngày {$a->duedate}).
    Qua quá trình bạn học và thực hành, nếu có bất kì thắc mắc cũng như ý kiến đóng góp, đừng ngại ngần phản hồi lại với chúng tôi qua:
    -   Diễn đàn thảo luận chung
    -   Cán bộ quản lý học tập: 0903428169
    -   Hotline: 096 600 0643
    -   Email: trungtamvmc@gmail.com

    Trung tâm VMC Việt Nam luôn luôn đồng hành cùng bạn.
    Gửi tới bạn và gia đình lời chúc sức khỏe, bình an và hạnh phúc!
    Trân trọng cảm ơn!
    Trung tâm VMC Việt Nam
    ```

### 5. Thông báo tài khoản đã được gán khóa học

![image](https://github.com/thsambala/th/assets/57883256/877a4854-445f-4adf-bbd7-1104902bf136)

- Thông báo tài khoản đã được gán khóa học, mặc định là **không** thông báo.
- Ngày nhắc nhở thông báo gán khóa học, tính từ thời điểm hạn ghi danh khóa học, mặc định là: **2**
- Email gửi:
  - Tiêu đề: Thông báo tài khoản đã được gán khóa học
  - Nội dung: 
    ```php
    {$a->userfullname} thân mến!
    Trung tâm VMC trân trọng thông báo bạn đã đăng ký thành công khóa học {$a->coursefullname}.

    Bạn hãy đăng nhập tài khoản tại trang elearning.vmcvietnam.org để kích hoạt khóa học của mình nhé!
    Sau khi kích hoạt khóa học, bạn sẽ có 90 ngày để học tập trên hệ thống.
    Hãy tham gia học tập và thực hành thường xuyên để đạt kết quả tốt nhất nhé!

    VMC gửi đến bạn lời chúc sức khỏe và bình an!
    ----------------------------------------------------------------------
    Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
    -   Hotline:  096 600 0643
    -   Thông qua cán bộ quản lý học tập: 0903428169
    -   Email: trungtamvmc@gmail.com
    Trân trọng cảm ơn!
    Trung tâm VMC Việt Nam
    ```

### 6. Nhắc nhở học tập khóa học đã kích hoạt

![image](https://github.com/thsambala/th/assets/57883256/1d73b338-3018-4d9f-bebd-a5c432b8f20f)

- Nhắc nhở học tập khóa học đã kích hoạt, mặc định là **không** thông báo.
- Ngày nhắc nhở tập khóa học đã kích hoạt, Trong vòng 14 ngày học viên chưa vào khóa học, và không quá 90 ngày kể từ ngày kích hoạt, mặc định là: **14**
- Email gửi:
  - Tiêu đề: Nhắc nhở học tập khóa học đã kích hoạt
  - Nội dung: 
    ```php
    {$a->userfullname} thân mến!

    Trong 14 ngày vừa qua, hệ thống VMC chưa ghi nhận bạn vào học tập và hoàn thiện khóa học {$a->coursefullname}.
    Khóa học của bạn đã kích hoạt sẽ có thời hạn trong 90 ngày nên bạn hãy cố gắng sắp xếp công việc để dành thời gian học tập và thực hành thường xuyên hơn nhé.

    Các Giảng viên và Quản lý học tập của Trung tâm VMC sẽ luôn đồng hành giúp đỡ bạn để hoàn thành khóa học.

    Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
    -   Hotline:  096 600 0643
    -   Thông qua cán bộ quản lý học tập: 0903428169
    -   Email: trungtamvmc@gmail.com
    Trân trọng cảm ơn!
    Trung tâm VMC Việt Nam
    ```
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user, user_enrolments, enrol, role_assignments, context, course, local_th_vmc_alert
- Các câu truy vấn đã dùng:
```sql
    SELECT *,$now-timecreated,$now-timecreated
    from {user}
    where ($now-timecreated)>:daythreshold1 and  ($now-timecreated)<:daythreshold2 and deleted = 0 and lastaccess = 0
```
```sql
    SELECT ue.id,courseid,ra.userid,ue.timecreated
    from {user_enrolments} ue
    join {enrol} e
    on e.id = ue.enrolid and ($now  - ue.timecreated)>:daythreshold1 and ($now  - ue.timecreated)<=:daythreshold2 and ue.status = 0
    join {role_assignments} ra
    on ue.userid = ra.userid and ra.roleid = 5
    join {context} c
    on c.contextlevel = 50 and c.id = ra.contextid and e.courseid = c.instanceid
```
```sql
   SELECT ue.id, courseid, ra.userid, ue.timecreated, ue.timeend
    from {user_enrolments} ue
    join {enrol} e
    on e.id = ue.enrolid and ue.timeend > 0 and (ue.timeend-ue.timecreated)>=$tendayperiod and (ue.timeend - $now)<=:daythreshold1 and (ue.timeend-$now)>=:daythreshold2 and ue.status = 0
    join {role_assignments} ra
    on ra.userid = ue.userid and ra.roleid = 5
    join {context} c
    on c.contextlevel = 50 and c.id = ra.contextid and e.courseid = c.instanceid
    join {course} on {course}.id = e.courseid and {course}.visible = 1
```
```sql
   SELECT ue.id, courseid, ra.userid, ue.timecreated, ue.timeend
    from {user_enrolments} ue
    join {enrol} e
    on e.id = ue.enrolid and ue.timeend > 0 and (ue.timeend - $now)<=:daythreshold1 and (ue.timeend-$now)>=:daythreshold2 and ue.status = 0
    join {role_assignments} ra
    on ra.userid = ue.userid and ra.roleid = 5
    join {context} c
    on c.contextlevel = 50 and c.id = ra.contextid and e.courseid = c.instanceid
    join {course} on {course}.id = e.courseid and {course}.visible = 1
```
2. Thêm Bảng: local_th_vmc_alert

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
1. Các Method: alert_login, alert_learning, alert_accountduedate, alert_ketthuckhoahoc, alert_account_enrol_course, alert_active_course
2. Chi tiết các Method:
- Gửi email Thông Báo Tài Khoản
```php
function alert_login()
```
- Gửi email Nhắc nhở đôn đốc học tập, làm bài kiểm tra
```php
function alert_learning()
```
- Gửi email Thông báo sắp kết thúc tài khoản trong khóa học
```php
function alert_accountduedate()
```
- Gửi email Thông báo tài khoản trong khóa học kết thúc
```php
function alert_ketthuckhoahoc()
```
- Gửi email Thông báo tài khoản đã được gán khóa học
```php
function alert_account_enrol_course()
```
- Gửi email Nhắc nhở học tập khóa học đã kích hoạt
```php
function alert_active_course()
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_vmc_alert

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)
