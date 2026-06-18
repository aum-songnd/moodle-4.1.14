# **Tài liệu phát triển tính năng lưu trữ bài luyện tập**

1. **Tên plugin**: th_store_quiz
2. **Kiểu plugin**: Local
3. **Project**: 
4. **Chức năng chung**: Lấy 3 lần điểm cao nhất của bài luyện tập còn lại xóa không lưu trữ
5. **Người phát triển**: aum.diendt@gmail.com
6. **Người yêu cầu**: linhnt720@wru.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_store_quiz

# 1. Yêu cầu: (bắt buộc)
Lấy 3 lần điểm cao nhất của bài luyện tập còn lại xóa không lưu trữ

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: local/th_store_quiz:view

    ```php
    $capabilities = array(
       'local/th_store_quiz:view' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
    );
    ```

- Giao diện chính chức năng: 

Chọn thời gian sau bao nhiêu ngày sẽ xóa bài luyện tập và tùy chọn bật tắt tính năng

![image](https://github.com/thvietnam/th_lms/assets/148859628/f509584e-3e00-4f85-bed3-9907d34af4cc)

Giao diện các lấy ra các bài luyện tập điểm không đạt điều kiện

![image](https://github.com/thvietnam/th_lms/assets/148859628/89bc13d6-3900-4b14-b1d4-df81e8463e0b)

Giao diện các bài luyện tập đã xóa

![image](https://github.com/thvietnam/th_lms/assets/148859628/2006a735-80e4-4e26-8931-707fda5d5ae1)



# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - quiz
    - quiz_attempts

- Danh sách 3 bài luyện tập điểm cao nhất
```sql
SELECT * FROM (SELECT {quiz_attempts}.* FROM {quiz_attempts}
JOIN {quiz}
ON {quiz_attempts}.quiz = {quiz}.id
WHERE name like '%bai luyen tap%') as mdl_table_quiz WHERE (id) NOT IN (
SELECT id  FROM (
SELECT id, ROW_NUMBER() OVER (PARTITION BY userid, quiz ORDER BY sumgrades DESC) AS ranking
FROM {quiz_attempts}
) AS ranked
WHERE ranking <= 3);
```

**Method**
1. Các Method:  auto_delete_quiz
2. Chi tiết Method:

- email_to_user
```php
		public static function auto_delete_quiz() {
		global $CFG, $DB;
		$days = get_config('local_th_store_quiz', 'deletion_date');
		$isactive = get_config('local_th_store_quiz', 'active');
		if($isactive == 1){
         		mtrace("My task started");
		$list_quizs = $DB->get_records_sql("SELECT * FROM (SELECT {quiz_attempts}.* FROM {quiz_attempts}
JOIN {quiz}
ON {quiz_attempts}.quiz = {quiz}.id
WHERE name like '%bai luyen tap%') as mdl_table_quiz WHERE (id) NOT IN (
SELECT id  FROM (
SELECT id, ROW_NUMBER() OVER (PARTITION BY userid, quiz ORDER BY sumgrades DESC) AS ranking
    FROM {quiz_attempts}
) AS ranked
WHERE ranking <= 3);");
		foreach($list_quizs as $quiz){
			$value = json_encode($quiz);
			$data_array = json_decode($value, true);
			$quizid = $data_array['quiz'];
			$attemptid = $data_array['id'];
			$sumgrades = $data_array['sumgrades'];
			$timefinish = $data_array['timefinish'];
			$timenow = time();
			$time = $timenow - $timefinish;
			$quiz_question = $DB->get_records_sql("SELECT * FROM {quiz} where id = $quizid;");
			$quiz_object = json_encode($quiz_question["$quizid"]);
			$object = json_decode($quiz_object);
			if($time > $days * 24 * 3600){
			  $data = new \stdClass();
              $data->quiz = $quizid;
              $data->userid = $data_array['userid'];
              $data->attempt = $data_array['attempt'];
              $data->sumgrades = $data_array['sumgrades'];
              $DB->insert_record('th_store_quiz', $data);
              $result = quiz_delete_attempt($attemptid, $object);
			}
		}
			if(!empty($result)){
              mtrace("Fail"); 
			} else {
			  mtrace("Success");
			}
		}
	}
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
