# **Tài liệu phát triển tính năng hiển thị danh sách khóa học chưa được mở**

1. **Tên plugin**: th_report_hide_course
2. **Kiểu plugin**: blocks
3. **Project**: TNU,AOF,TNUT
4. **Chức năng chung**: Hiển thị danh sách khóa học chưa được mở
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_report_hide_course

# 1. Yêu cầu: (bắt buộc)
Hiển thị danh sách khóa học chưa được mở

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: blocks/th_report_hide_course:view

    ```php
    $capabilities = array(
        'blocks/th_report_hide_course:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            )
        )
    );
    ```

- Giao diện chính chức năng:

![image](https://github.com/minhpl/image_repository/assets/66956549/219268b2-4eea-4ee4-b876-60d6c659247b)

- Chọn đợt mở môn và chọn submit hệ thống sẽ trả về danh sách môn chưa mở như hình dưới:

![image](https://github.com/minhpl/image_repository/assets/66956549/e3192b1a-1cb9-4a2b-9208-3598c53d173d)


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course
    - role
    - user
    - block_th_course_status

- Danh sách khóa học
```sql
SELECT * FROM {course} WHERE NOT id = 1 AND startdate = '$startdate' ORDER BY fullname
```
```sql
SELECT ishidden FROM {block_th_course_status} WHERE course = :course ORDER BY timecreated DESC LIMIT 1
```
```sql
SELECT timecreated FROM {block_th_course_status} WHERE course = :course ORDER BY timecreated DESC LIMIT 1
```
**Method**
1. Các Method: get_role_users
2. Chi tiết Method:

- get_role_users
```php
/**
 * Gets all the users assigned this role in this context or higher
 *
 * Note that moodle is based on capabilities and it is usually better
 * to check permissions than to check role ids as the capabilities
 * system is more flexible. If you really need, you can to use this
 * function but consider has_capability() as a possible substitute.
 *
 * All $sort fields are added into $fields if not present there yet.
 *
 * If $roleid is an array or is empty (all roles) you need to set $fields
 * (and $sort by extension) params according to it, as the first field
 * returned by the database should be unique (ra.id is the best candidate).
 *
 * @param int $roleid (can also be an array of ints!)
 * @param context $context
 * @param bool $parent if true, get list of users assigned in higher context too
 * @param string $fields fields from user (u.) , role assignment (ra) or role (r.)
 * @param string $sort sort from user (u.) , role assignment (ra.) or role (r.).
 *      null => use default sort from users_order_by_sql.
 * @param bool $all true means all, false means limit to enrolled users
 * @param string $group defaults to ''
 * @param mixed $limitfrom defaults to ''
 * @param mixed $limitnum defaults to ''
 * @param string $extrawheretest defaults to ''
 * @param array $whereorsortparams any paramter values used by $sort or $extrawheretest.
 * @return array
 */
function get_role_users($roleid, context $context, $parent = false, $fields = '',
        $sort = null, $all = true, $group = '',
        $limitfrom = '', $limitnum = '', $extrawheretest = '', $whereorsortparams = array())
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)


# **Tài liệu phát triển tính năng báo cáo phê duyệt chậm**

1. **Tên plugin**: th_report_hide_course
2. **Kiểu plugin**: blocks
3. **Project**: TNU,AOF,TNUT
4. **Chức năng chung**: báo cáo phê duyệt chậm
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_report_hide_course

# 1. Yêu cầu: (bắt buộc)
Báo cáo phê duyệt chậm

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: blocks/th_report_hide_course:view

    ```php
    $capabilities = array(
        'blocks/th_report_hide_course:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'manager' => CAP_ALLOW
            )
        )
    );
    ```

- Giao diện chính chức năng:

![image](https://github.com/thsambala/th_lms/assets/57883256/090ee811-b292-435e-98bf-b0b964432ad6)

- Chọn đợt mở môn và chọn submit hệ thống sẽ trả về báo cáo phê duyệt chậm như hình dưới:

![image](https://github.com/thsambala/th_lms/assets/57883256/cf31126d-b0a5-42f8-9223-dc3c591cff41)


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course
    - role
    - user
    - block_th_course_status

- Danh sách khóa học
```sql
SELECT * FROM {course} WHERE NOT id = 1 AND startdate = '$startdate' ORDER BY fullname
```
```sql
SELECT ishidden FROM {block_th_course_status} WHERE course = :course ORDER BY timecreated DESC LIMIT 1
```
```sql
SELECT timecreated FROM {block_th_course_status} WHERE course = :course ORDER BY timecreated DESC LIMIT 1
```
**Method**
1. Các Method: get_role_users
2. Chi tiết Method:

- get_role_users
```php
/**
 * Gets all the users assigned this role in this context or higher
 *
 * Note that moodle is based on capabilities and it is usually better
 * to check permissions than to check role ids as the capabilities
 * system is more flexible. If you really need, you can to use this
 * function but consider has_capability() as a possible substitute.
 *
 * All $sort fields are added into $fields if not present there yet.
 *
 * If $roleid is an array or is empty (all roles) you need to set $fields
 * (and $sort by extension) params according to it, as the first field
 * returned by the database should be unique (ra.id is the best candidate).
 *
 * @param int $roleid (can also be an array of ints!)
 * @param context $context
 * @param bool $parent if true, get list of users assigned in higher context too
 * @param string $fields fields from user (u.) , role assignment (ra) or role (r.)
 * @param string $sort sort from user (u.) , role assignment (ra.) or role (r.).
 *      null => use default sort from users_order_by_sql.
 * @param bool $all true means all, false means limit to enrolled users
 * @param string $group defaults to ''
 * @param mixed $limitfrom defaults to ''
 * @param mixed $limitnum defaults to ''
 * @param string $extrawheretest defaults to ''
 * @param array $whereorsortparams any paramter values used by $sort or $extrawheretest.
 * @return array
 */
function get_role_users($roleid, context $context, $parent = false, $fields = '',
        $sort = null, $all = true, $group = '',
        $limitfrom = '', $limitnum = '', $extrawheretest = '', $whereorsortparams = array())
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
