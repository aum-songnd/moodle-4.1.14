# **Tài liệu phát triển tính năng phê duyệt bảng điểm môn học**

1. **Tên plugin**: th_grades_approval
2. **Kiểu plugin**: blocks
3. **Project**: AOF
4. **Chức năng chung**: phê duyệt bảng điểm môn học
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_grades_approval

# 1. Yêu cầu: (bắt buộc)
Cho phép giảng viên phê duyệt bảng điểm môn học

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: 
    - block/th_grades_approval:addinstance

    ```php
    $capabilities = array(
        'block/th_grades_approval:addinstance' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,

            'captype' => 'write',
            'contextlevel' => CONTEXT_BLOCK,
            'archetypes' => array(
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            ),

            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),
    );
    ```

**Phê duyệt bảng điểm môn học**

- Giảng viên truy cập vào khóa học và bấm vào nút phê duyệt như hình dưới:

![image](https://github.com/thsambala/th/assets/57883256/9b9dda0d-c2d9-4587-b8b2-4d71c4c405aa)

- Hệ thống trả về giao diện chính chức năng phê duyệt như hình dưới:

![image](https://github.com/thsambala/th/assets/57883256/7da37d72-bd31-4cbd-83a0-655db12e18c6)

Giảng viên có thể xem bảng điểm và thực hiện các thao tác phê duyệt, hủy phê duyệt và tải bảng điểm về dưới dạng file excel trên giao diện trên

**Báo cáo phê duyệt bảng điểm**

- Giao diện chính chức năng:

![image](https://github.com/thsambala/th/assets/57883256/2a20be87-0198-4246-8b67-76487efb3c08)

- Chọn đợt mở môn và nhấn submit hệ thống sẽ trả về giao diện như hình dưới:

![image](https://github.com/thsambala/th/assets/57883256/a4289c23-2589-47d7-8182-708eaa2a4764)

**Các cài đặt của chức năng phê duyệt bảng điểm**

![image](https://github.com/thsambala/th/assets/57883256/25f5a639-d3c1-4c63-b71a-4f03e220a8b0)

**Gửi mail nhắc nhở phê duyệt bảng điểm**

- Gửi mail nhắc nhở phê duyệt bảng điểm hiện tại đang là 60 ngày sau ngày kích hoạt, Có thể setting tăng giảm số ngày trong cài đặt

- Chủ đề mail: Phê duyệt bảng điểm
- Nội dung mail:
    ```
    Chào anh/chị,
    Anh chị cần phê duyệt bảng điểm môn học $course_fullname trước ngày $date
    Nếu đã thao tác, vui lòng bỏ qua nội dung này.
    Nếu cần hỗ trợ, anh chị vui lòng liên hệ kỹ thuật (Hotline: 0948293794).

    Xin cảm ơn!
    ```

- Chủ đề và nội dung mail có thể chỉnh sửa trong cài đặt

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - course
    - role
    - enrol
    - user_enrolments
    - role_assignments
    - context
    - grade_items
    - grade_grades

- Câu lệnh sql lấy danh sách giảng viên của môn học
```php
$roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'editingteacher'");
$ds_giang_vien = $DB->get_records_sql("SELECT u.* FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$course_id' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$course_id' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'");
```

- Bảng mới:

    - block_th_grades_approval

        | Tên         | Kiểu | Chú thích     |
        |-------------|------| --------------|
        | id          | int  | id            |
        | course      | int  | id môn học    |
        | timecreated | int  | thời gian tạo |
        | teachingid  | int  | id giảng viên |
        | ishidden    | int  | trạng thái    |

**Method**
1. Các Method: send_mail_grades_approval, load_final_grades, load_users
2. Chi tiết Method:

- send_mail_grades_approval:

```php
/**
 * Gửi mail nhắc nhở phê duyệt bảng điểm 
 */
function send_mail_grades_approval()
```

```php
/**
 * we supply the userids in this query, and get all the grades
 * pulls out all the grades, this does not need to worry about paging
 */
public function load_final_grades()
```

```php
/**
 * pulls out the userids of the users to be display, and sorts them
 */
public function load_users()
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
