# **Tài liệu phát triển tính năng gửi email nhắc việc giảng viên trả lời câu hỏi**

1. **Tên plugin**: th_unanswered_questions
2. **Kiểu plugin**: Local
3. **Project**: TNU,AOF,TNUT,TUAF
4. **Chức năng chung**: Nhắc nhở Giảng viên vào trả lời các câu hỏi của Học viên ở phần Hỏi/ Đáp trong khóa học
5. **Người phát triển**: aum.diendt@gmail.com
6. **Người yêu cầu**: linhnt720@wru.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_unanswered_questions

# 1. Yêu cầu: (bắt buộc)
Nhắc nhở Giảng viên vào trả lời các câu hỏi của Học viên ở phần Hỏi/ Đáp trong khóa học

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: local/th_unanswered_questions:view

    ```php
    $capabilities = array(
       'local/th_unanswered_questions:view' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
    );
    ```

- Giao diện chính chức năng: 

Chọn khung thời gian gửi thông báo nhắc nhở giảng viên trả lời câu hỏi

![image](https://github.com/thvietnam/th_lms/assets/148859628/9f23f5e2-97af-46ff-9ca5-a0403c402be6)

Sau khi thông báo gửi về email thì giảng viên nhấn vào link để trả lời câu hỏi

![image](https://github.com/thvietnam/th_lms/assets/148859628/08692535-45e5-4493-b446-a203c95daf67)

Khi nhấn vào link thì đây là giao diện các câu hỏi giảng viên chưa trả lời

![image](https://github.com/thvietnam/th_lms/assets/148859628/9e334dc0-bf17-483d-9d16-46bb7b28751d)

Giảng viên nhấn nút trả lời để trả lời các câu hỏi

![image](https://github.com/thvietnam/th_lms/assets/148859628/a5789bec-7dc3-48fa-b3ac-39eaae38e043)




# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - qaapairs
    - qaa
    - course
    - context
    - role_assignments
    - user
    - role
    - user_info_data
    - modules
    - course_modules

- Danh sách các câu hỏi giảng viên chưa trả lời
```sql
SELECT {qaapairs}.id, vmc_table.fullname, {qaapairs}.studentid, {qaapairs}.timecreatedquestion, {qaapairs}.question, {qaa}.id as qaa_id, vmc_table.user_id, vmc_table.course_id FROM (select ra.id, c.id as course_id, c.fullname, u.id as user_id, u.lastname
FROM (SELECT * FROM {course}
		WHERE startdate >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 MONTH))) c
JOIN {context} ct ON c.id = ct.instanceid
JOIN {role_assignments} ra ON ra.contextid = ct.id
JOIN {user} u ON u.id = ra.userid
JOIN {role} r ON r.id = ra.roleid
where r.shortname = 'editingteacher') as vmc_table
JOIN {qaa} on vmc_table.course_id = {qaa}.course
JOIN {qaapairs} on {qaa}.id = {qaapairs}.qaaid
where vmc_table.user_id = {$id} and {qaapairs}.answer is null;
```

**Method**
1. Các Method:  email_to_user
2. Chi tiết Method:

- email_to_user
```php
	public static function auto_send_unanswered_question() {
		global $CFG, $DB;
		mtrace("My task started");
		$title  = get_string('title', 'local_th_unanswered_questions');
		$content1 = get_string('content1', 'local_th_unanswered_questions');
		$content2 = get_string('content2', 'local_th_unanswered_questions');
		$teachersid = $DB->get_records_sql("select distinct vmc_table.user_id from (select ra.id, c.id as course_id, c.fullname, u.id as user_id, u.lastname
		FROM (SELECT * FROM {course}
		WHERE startdate >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 MONTH))) c
		JOIN {context} ct ON c.id = ct.instanceid
		JOIN {role_assignments} ra ON ra.contextid = ct.id
		JOIN {user} u ON u.id = ra.userid
		JOIN {role} r ON r.id = ra.roleid
		where r.shortname = 'editingteacher') as vmc_table
		JOIN {qaa} on vmc_table.course_id = {qaa}.course
		JOIN {qaapairs} on {qaa}.id = {qaapairs}.qaaid
		where {qaapairs}.answer is null;");
		foreach($teachersid as $teacherid){
			$id = $teacherid->user_id;
			$url = "$CFG->wwwroot/local/th_unanswered_questions/index.php?id={$id}";
			$user_from = $DB->get_record('user', array('id' => 1));
			$user_to  =$DB->get_record('user', array('id' => $id));
			$result = email_to_user($user_to, $user_from, "{$title}", "$content1 $url . $content2");
		}
		if (!empty($result)) {
			mtrace("Run task success");
		} else {
			mtrace("Run task failed");
		}
	}
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
