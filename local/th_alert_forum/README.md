# Tài liệu Tổng hợp các Post Forum, Các câu hỏi đáp trong tuần thành một email, gửi đến địa chỉ mail được chỉ định trong danh sách.

1. **Tên plugin:** th_alert_forum
2. **Kiểu plugin**: local
3. **Project:** TNU,AOF,TNUT
4. **Chức năng chung:** Tổng hợp các Post Forum, Các câu hỏi đáp trong tuần thành một email, gửi đến địa chỉ mail được chỉ định trong danh sách.
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-125
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_alert_forum
# 1. Yêu cầu:

- Tổng hợp các Post Forum, Các câu hỏi đáp trong tuần thành một email, gửi đến địa chỉ mail được chỉ định trong danh sách.

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: không có
- Đoạn code định nghĩa capabilities: không có

2. Hướng dẫn sử dụng
- Gửi các bài viết trong Forum qua Mail đến Email theo **Sự kiện** hoặc **Lập lịch**.
- Dưới đây là setting của plugin:
    ![image](https://user-images.githubusercontent.com/66956549/209629506-1b5ab60a-2926-4ef5-9742-98128fd27121.png)
    - Kiểu gửi: có thể chọn **Sự kiện** hoặc **Lập lịch**. Mặc định là **Lập lịch**.
    - Danh sách các Email sẽ nhận được thông báo.
- Nếu chọn **Lập lịch** thì mặc định thông báo sẽ được gửi vào 02 giờ ngày thứ Hai hàng tuần. Giờ gửi có thể đặt lịch tại: siteadmin>server>task>scheduled>task>local_th_alert_forum\task\alert_forum
- Nếu chọn **Sự kiện** thì sau khi mỗi post/trả lời trong forum hoặc qaa sẽ gửi mail ngay lập tức

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: qaa, qaapairs, course, modules, course_modules, forum_discussions, forum_posts, task_scheduled
- Các câu truy vấn đã dùng:

```sql
$sql = "SELECT DISTINCT q.course
		FROM {qaa} q
        JOIN {qaapairs} qp ON q.id = qp.qaaid
        JOIN {course} c ON q.course = c.id AND c.visible = 1
		WHERE (timemodifiedquestion > ? AND timemodifiedquestion <= ?)
		OR (timemodifiedanswer > ? AND timemodifiedanswer <= ?)";
```
Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Hỏi/đáp trong khoảng thời gian

```sql
$sql = "SELECT fp.id,fp.discussion,fp.modified,fp.message,fp.parent,fp.subject,fd.course,fd.userid,fd.forum
	   FROM {forum_discussions} fd
        INNER JOIN {forum_posts} fp ON fp.discussion=fd.id
        INNER JOIN {course} c ON c.id=fd.course
	   WHERE fp.parent = 0 AND fd.timemodified > ? AND fd.timemodified <= ? AND c.visible = 1";
```
Lấy ra các chủ đề của các forum trong khoảng thời gian

```sql
$sql = "SELECT * 
        FROM {forum_posts} 
        WHERE parent != 0 AND discussion = ? 
        AND modified > ? AND modified <= ?";
```
Lấy ra các chủ đề của các câu trả lời (forum) trong khoảng thời gian

2. Thêm Bảng: không
3. Database Diagram: Không

## Method
1. Các Method: alert_qaa, alert_forum, email_to_user1
2. Chi tiết các Method:
- alert_qaa: Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Hỏi/đáp trong khoảng thời gian
```php  
/**
 * alert_qaa function Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Hỏi/đáp trong khoảng thời gian
 *
 * @return void
 */
function alert_qaa()
```
- alert_forum: Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Forum trong khoảng thời gian
```php
/**
 * alert_forum function Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Forum trong khoảng thời gian
 *
 * @return void
 */
function alert_forum()
```

- email_to_user1: gửi email và CC cho người quản lí
```php
/**
 * Send an email to a specified user
 *
 * @param stdClass $user  A {@link $USER} object
 * @param stdClass $from A {@link $USER} object
 * @param string $subject plain text subject line of the email
 * @param string $messagetext plain text version of the message
 * @param string $messagehtml complete html version of the message (optional)
 * @param string $attachment a file on the filesystem, either relative to $CFG->dataroot or a full path to a file in one of
 *          the following directories: $CFG->cachedir, $CFG->dataroot, $CFG->dirroot, $CFG->localcachedir, $CFG->tempdir
 * @param string $attachname the name of the file (extension indicates MIME)
 * @param bool $usetrueaddress determines whether $from email address should
 *          be sent out. Will be overruled by user profile setting for maildisplay
 * @param string $replyto Email address to reply to
 * @param string $replytoname Name of reply to recipient
 * @param int $wordwrapwidth custom word wrap width, default 79
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function email_to_user1($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) 
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_alert_forum

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

