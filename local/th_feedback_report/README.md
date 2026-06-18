# **Tài liệu phát triển tính năng báo lỗi/feedback hệ thống**

1. **Tên plugin**: th_feedback_report
2. **Kiểu plugin**: local
3. **Project**:  LMS-CORE
4. **Chức năng chung**: tính năng báo lỗi/feedback hệ thống
5. **Người phát triển**: linhnt
6. **Mã nguồn:** https://github.com/thvietnam/th_lms/tree/master/local/th_feedback_report

# 1. Yêu cầu:

Viết plugin cho phép người dùng báo lỗi/feedback các vấn đề găp phải khi sử dụng hệ thống

# 2. Các câu lệnh để update kiểu hiển thị cho File, SCORM để hiển thị ngay trang hiện tại, không mở popup.
- Câu lệnh sql update SCORM:
    ```sql
    UPDATE lms_scorm SET popup = 0;
    ```					
- Câu lệnh sql update PDF(Chỉ update các file có kiểu pdf):
    ```sql
    UPDATE `lms_resource` AS r 
       JOIN `lms_course_modules` AS cm ON r.id = cm.instance 
       JOIN `lms_context` AS c ON c.instanceid = cm.id 
       JOIN `lms_files` AS f ON f.contextid = c.id SET r.display = 1 
       WHERE f.component = 'mod_resource' AND f.mimetype = 'application/pdf';
    ```
