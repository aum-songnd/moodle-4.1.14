# **Tài liệu phát triển tính năng phân tách ngân hàng câu hỏi theo tỉ lệ**

1. **Tên plugin**: th_question_bank_segregator
2. **Kiểu plugin**: local
3. **Project**: TNU,AOF,TNUT,TUAF
4. **Chức năng chung**: phân tách ngân hàng câu hỏi theo tỉ lệ
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_question_bank_segregator

# 1. Yêu cầu: (bắt buộc)
Phân tách ngân hàng câu hỏi theo tỉ lệ

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: 
    - local/th_question_bank_segregator:view

    ```php
        $capabilities = array(
            'local/th_question_bank_segregator:view' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                    'manager' => CAP_ALLOW,
                ),
            ),
        );
    ```


**Hướng dẫn sử dụng**

- Trong giao diện ngân hàng câu hỏi chọn menu phân tách ngân hàng câu hỏi như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/ed2315b8-e809-42ec-90cd-f3afc571b4b5)

- Các danh mục hiện tại cần phân tách:
    ![image](https://github.com/thsambala/th/assets/57883256/6f801877-32d8-453b-8b7c-d202ec7a1528)

- Hệ thống sẽ trả về giao diện như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/f6882d89-6603-4369-a321-e3d2822d0715)

- Chọn Backup NHCH để backup lại ngân hàng câu hỏi hiện tại:
- Sau khi backup thành công ta tiến hàng chọn tỉ lệ của danh mục backup còn hệ thống sẽ tự tính toán tỉ lệ của danh mục setup. 
- Chọn tỉ lệ như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/93c31ef9-c0db-4c02-91ec-b46947962906)

- Chọn gửi để tiến hành phân tách NHCH. Hệ thống sẽ trả về thông báo như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/98a2ef53-1d6a-4e6e-b703-6643d960c05d)

- Chọn menu danh mục để xem kết quả phân tách:
    ![image](https://github.com/thsambala/th/assets/57883256/140a4bef-590d-4bc5-b8ed-c5b8b27722d9)

- Vậy là quá trình phân tách đã thành công
- Nếu muốn chọn lại tỉ lệ ta tiến hành restore lại NHCH đã backup. Chọn Restore NHCH như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/3f31cfd4-a7b8-428d-882b-4e4710d8bee8)

- Hệ thống trả về thông báo như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/4ffc2fe8-b451-4981-ab82-88565f971776)
- Chọn lại menu danh mục để xem kết quả restore:
    ![image](https://github.com/thsambala/th/assets/57883256/66ea44c7-8c2d-4563-9249-c380ba98204b)

- Vậy là quá trình resore NHCH đã thành công

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - question_categories
    - question
- Bảng mới:

    - th_course_category_tree

        | Tên        | Kiểu | Chú thích    |
        |------------|------| ------------ |
        | id         | int  | id   |
        | course_id    | int | id khóa học  |
        | category_tree_json | text  |  chuỗi cấu trúc danh mục gốc |
        | category_json   | text  |  chuỗi danh mục gốc |
        | timecreated  | int  | Ngày tạo     |
    
    

**Method**
1. Các Method: question_move_questions_to_category, get_categories_for_contexts, get_questions_category
2. Chi tiết Method:

- question_move_questions_to_category:
```php
/**
 * This function should be considered private to the question bank, it is called from
 * question/editlib.php question/contextmoveq.php and a few similar places to to the
 * work of actually moving questions and associated data. However, callers of this
 * function also have to do other work, which is why you should not call this method
 * directly from outside the questionbank.
 *
 * @param array $questionids of question ids.
 * @param integer $newcategoryid the id of the category to move to.
 */
function question_move_questions_to_category($questionids, $newcategoryid)
```
- get_categories_for_contexts:
```php
/**
 * Get all the category objects, including a count of the number of questions in that category,
 * for all the categories in the lists $contexts.
 *
 * @param mixed $contexts either a single contextid, or a comma-separated list of context ids.
 * @param string $sortorder used as the ORDER BY clause in the select statement.
 * @param bool $top Whether to return the top categories or not.
 * @return array of category objects.
 */
function get_categories_for_contexts($contexts, $sortorder = 'parent, sortorder, name ASC', $top = false)
```

- get_questions_category:
```php
/**
* Function to read all questions for category into big array
*
* @param int $category category number
* @param bool $noparent if true only questions with NO parent will be selected
* @param bool $recurse include subdirectories
* @param bool $export set true if this is called by questionbank export
*/
function get_questions_category( $category, $noparent=false, $recurse=true, $export=true )
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
