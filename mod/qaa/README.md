# Mô tả chung
- **Tên plugin**: qaa
- **Kiểu plugin**: mod
- **Project**: tnu
- **Chức năng chung**: Cho phép học viên đặt câu hỏi để giáo viên trả lời trong vòng 24h trong từng khóa học
- **Người phát triển**: minhpl@aum.edu.vn 
## ChangLog:
2022-03-30: fix lỗi Học viên có quyền trả lời, update chức năng sửa câu trả lời cho Giảng viên.
# Mô tả chi tiết
- Học viên được phép đặt câu hỏi, mỗi khi học viên đặt câu hỏi, hệ thống sẽ gửi email thông báo đến giảng viên
- Học viên được phép sửa câu hỏi của mình trong vòng 30 phút
- Giảng viên được phép trả lời câu hỏi của học viên, hệ thống sẽ gửi email thông báo đến học viên.
- Giảng viên có thể sửa câu trả lời (nếu cần).
- Câu hỏi không được ngắn hơn mặc định 20 kí tự, số kí tự có thể thay đổi trong setting


# Tài liệu Chức năng "Hỏi/đáp" trong khóa học

1. **Tên plugin:** qaa
2. **Kiểu plugin:** mod
3. **Project:** TNU,AOF,TNUT,TUAF
4. **Chức năng chung:** Cho phép học viên đặt câu hỏi để giáo viên trả lời trong vòng 24h trong từng khóa học
5. **Người phát triển:** minhpl@aum.edu.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/mod/qaa
# 1. Yêu cầu và Đầu vào, đầu ra:
1. Yêu cầu
- Cho phép học viên đặt câu hỏi để giáo viên trả lời trong vòng 24h trong từng khóa học

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
## 1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **mod/qaa:addinstance**, **mod/qaa:view**, **mod/qaa:makequest**, **mod/qaa:deletequest**
- Đoạn code định nghĩa capabilities: 
```php
    'mod/qaa:addinstance' => array(
		'riskbitmask' => RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
		'clonepermissionsfrom' => 'moodle/course:manageactivities',
	),

	'mod/qaa:view' => array(
		'captype' => 'read',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'guest' => CAP_ALLOW,
			'student' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),

	'mod/qaa:makequest' => array(
		// 'riskbitmask' => RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'student' => CAP_ALLOW,
		),
	),

	'mod/qaa:deletequest' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_MODULE,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
```

## 2. Hướng dẫn sử dụng
- Truy cập khóa học và **Bật chế độ chỉnh sửa**

![image](https://user-images.githubusercontent.com/66956549/230524782-a3cb16bc-a4b0-48de-ae70-5089057dc156.png)

- Chọn **Thêm hoạt động hoặc tài nguyên**
  
![image](https://user-images.githubusercontent.com/66956549/230524934-5ca83766-7a2d-44ed-bb50-1da76004622c.png)

- Chọn **Question and Answer**

![image](https://user-images.githubusercontent.com/66956549/230542929-49b8132f-c8e6-4f3b-8493-edf806d42cf8.png)

- Nhập tên, ví dụ: **Hỏi/đáp**. và chọn **Lưu và trở về khóa học** hoặc **Lưu và cho xem**.

![image](https://user-images.githubusercontent.com/66956549/230543302-3557bb56-31bd-448d-8db1-1768bff3aa94.png)

- **Hỏi/đáp** xuất hiện. chúng ta chọn vào để đặt **câu hỏi** với quyền hỏi viên, cũng như **Trả lời** với quyền Trợ giảng hoặc Giảng viên.

![image](https://user-images.githubusercontent.com/66956549/230543628-68db920a-2950-4a81-b487-ef813c41316f.png)

- Chọn **Thêm câu hỏi mới**. Quyền học viên mới có thể thêm được câu hỏi.

![image](https://user-images.githubusercontent.com/66956549/230543957-de3e75da-cf80-4bd2-98ca-643248c534de.png)

- Nhập **Nội dung** câu hỏi và Chọn **Submit**. Một email sẽ gửi câu hỏi đến cho Giảng viên và Trợ giảng.

![image](https://user-images.githubusercontent.com/66956549/230544131-770f0d10-37ad-4364-bce0-5e18b59fa8d3.png)

- Câu hỏi xuất hiện. Giảng viên hoặc Trợ giảng chọn **Trả lời** để trả lời câu hỏi.

![image](https://user-images.githubusercontent.com/66956549/230558450-4eacc600-8475-48b6-baca-6b6154c3b3ad.png)

- Giảng viên hoặc Trợ giảng trả lời câu hỏi của Học viên.

![image](https://user-images.githubusercontent.com/66956549/230560656-399e0741-0a9f-4f82-b5ac-db8afae84501.png)

- Chọn **Gửi**. Một email sẽ gửi đến Học viên với Nội dung là câu trả lời của Giảng viên hoặc Trợ giảng.

- Ngoài ra, Giảng viên hoặc Trợ Giảng có thể sửa lại câu hỏi của học viên cho chuẩn hoặc sửa câu trả lời của mình.
  - Sửa cẩu hỏi

![image](https://user-images.githubusercontent.com/66956549/230562213-561fab99-846e-4144-b38b-791227258c00.png)

![image](https://user-images.githubusercontent.com/66956549/230562504-db993ebf-09a6-4a37-b33e-06a0631a90fe.png)

  - Sửa lại cẩu hỏi và chọn **Gửi**

![image](https://user-images.githubusercontent.com/66956549/230563292-e619be06-0479-4273-9f3b-f42f835a479b.png)

  - Sửa câu trả lời

![image](https://user-images.githubusercontent.com/66956549/230562785-3e48962e-b66c-4c7d-9a69-28c9546d9ce0.png)

- Sửa lại nội dung câu trả lời và chọn **Gửi**
![image](https://user-images.githubusercontent.com/66956549/230562964-5c09d92f-9e97-485f-b4ea-758d2d3f9a50.png)
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: qaa, qaapairs, files, course, role, course_modules
- Các câu truy vấn đã dùng: 
```php
$sql = "SELECT * 
        FROM {files} 
        WHERE contextid = $modulecontext->id AND component = 'mod_qaa' AND filearea = 'content' AND itemid = $questionid";
```

2. Thêm Bảng:
- qaa

| Trường(cột) | Kiểu dữ liệu | Bắt buộc |
|-------------|--------------|----------|
| id          | int          | có       |
| course      | int          |          |
| name        | char         |          |
| grade       | int          |          |
| timecreated | int          |          |
| timemodified| int          |          |
| intro       | text         |          |
| introformat | int          |          |

- qaapairs

| Trường(cột) | Kiểu dữ liệu | Bắt buộc |
|-------------|--------------|----------|
| id          | int          | có       |
| parentid    | int          |          |
| qaaid       | int          |          |
| question    | text         |          |
| studentid   | int          |          |
| answer      | text         |          |
| teacherid   | int          |          |
| teacheridmodified      | int       |          |
| timecreatedquestion    | int       |          |
| timemodifiedquestion   | int          |          |
| timecreatedanswer      | int          |          |
| timemodifiedanswer     | int          |          |
3. Database Diagram: Không

## Method
1. Các Method: get_qaap_html, prepare_subject, qaa_supports, qaa_add_instance, qaa_update_instance, qaa_delete_instance, qaa_grade_item_update, qaa_grade_item_delete, qaa_update_grades, qaa_pluginfile
2. Chi tiết các Method:
```php
/**
 * [get_qaap_html hiển thị câu trả lời]
 * @param  [stdClass]  $qaap        [bản ghi trong bảng qaapairs]
 * @param  [boolean]   $link          [thêm url vào nội dung câu hỏi để chuyển hướng không]
 * @param  [stdClass]  $modulecontext [ngữ cảnh]
 * @return [void]                 [html hiện thị lên trình duyệt]
 */
function get_qaap_html($qaap, $link = false, $modulecontext = null)
```

```php
/**
 * { if length > 20 trim subject }
 *
 * @param      <type>  $str    The string subject
 * @param      <int>   $max    The maximum length
 */
function prepare_subject($str, $maxlength = 20)
```

```php
/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function qaa_supports($feature)
```

```php
/**
 * Saves a new instance of the mod_qa into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_qaa_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function qaa_add_instance($moduleinstance, $mform = null)
```

```php
/**
 * Updates an instance of the mod_qa in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_qaa_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function qaa_update_instance($moduleinstance, $mform = null)
```

```php
/**
 * Removes an instance of the mod_qa from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function qaa_delete_instance($id)
```

```php
/**
 * Creates or updates grade item for the given mod_qaa instance.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function qaa_grade_item_update($moduleinstance, $grades = null)
```

```php
/**
 * Delete grade item for given mod_qaa instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function qaa_grade_item_delete($moduleinstance)
```

```php
/**
 * Update mod_qaa grades in the gradebook.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function qaa_update_grades($moduleinstance, $userid = 0)
```

```php
/**
 * Serves the resource files.
 *
 * @package  mod_qaa
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function qaa_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/mod/qaa

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)