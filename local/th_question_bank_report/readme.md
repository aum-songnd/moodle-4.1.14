# **Tài liệu phát triển tính năng báo cáo biến động ngân hàng câu hỏi**

1. **Tên plugin**: th_question_bank_report
2. **Kiểu plugin**: Local
3. **Project**: TNU,AOF,TNUT,TUAF
4. **Chức năng chung**: Báo cáo thống kê biến động ngân hàng câu hỏi
5. **Người phát triển**: aum.diendt@gmail.com
6. **Người yêu cầu**: linhnt720@wru.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_question_bank_report

# 1. Yêu cầu: (bắt buộc)
Báo cáo thống kê biến động ngân hàng câu hỏi

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: local/th_question_bank_report:view

    ```php
    $capabilities = array(
       'local/th_question_bank_report:view' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
    );
    ```

- Giao diện chính chức năng: 

![image](https://github.com/user-attachments/assets/e5adf481-8ddd-4c05-9ec0-2a630e894ca1)


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - question_categories
    - question
    - context
    - logstore_standard_log
    - course
    - user
    - course_categories
- Truy vấn dữ liệu:
```sql
SELECT c.id AS courseid, c.fullname AS coursename, c.shortname AS courseshortname, 
    u.id AS userid, u.email AS email,
    SUM(CASE WHEN l.action = 'created' THEN 1 ELSE 0 END) AS questionscreated,
    SUM(CASE WHEN l.action = 'updated' THEN 1 ELSE 0 END) AS questionsedited,
    (SELECT COUNT(q.id)
        FROM {question_categories} qc
        JOIN {question} q ON qc.id = q.category
        JOIN {context} ctx ON ctx.id = qc.contextid
        WHERE ctx.instanceid = c.id 
        AND ctx.contextlevel = 50
        AND q.parent = 0
        AND q.hidden = 0) AS totalquestions
FROM {logstore_standard_log} l
JOIN {course} c ON l.courseid = c.id
JOIN {user} u ON l.userid = u.id
JOIN {course_categories} cat ON c.category = cat.id
JOIN {question} q ON q.id = l.objectid
WHERE l.target = 'question' 
AND q.hidden = 0
AND q.parent = 0
AND $sqlcondition
GROUP BY c.id, c.fullname, c.shortname, u.id, u.email
ORDER BY cat.name, c.fullname, u.email;
```

**Method**
1. Các Method:  
2. Chi tiết Method:


# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
