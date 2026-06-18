# Tài liệu Báo cáo hoàn thành khóa học

1. **Tên plugin:** th_course_completion_report
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Báo cáo hoàn thành khóa học
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_course_completion_report
# 1. Yêu cầu:
1. Yêu cầu
- Báo cáo để thống kê tỷ lệ hoàn thành khóa học của Học viên theo Khóa học hoặc theo Học viên
- Đầu vào: 

    ![image](https://github.com/thsambala/th/assets/66956549/705cbb29-63f1-4e6c-b5af-acd15b1619d2)
   
- Kết quả trả về dạng bảng

    ![image](https://github.com/thsambala/th/assets/66956549/11f3b518-93ed-43f4-abda-ce78d05d7583)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_course_completion_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_course_completion_report:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
```
2. Hướng dẫn sử dụng
- Truy cập **TH Dashboard** và chọn **Báo cáo hoàn thành khóa học**

- Chọn tra cứu theo Khóa học, chọn khóa học và chọn khoảng thời gian muốn tra cứu (**Sẽ lấy ra các học viên được ghi danh hoặc hết hạn trong khoảng thời gian đó**):

    ![image](https://github.com/thsambala/th/assets/66956549/0e318949-e388-4cc7-bb7d-c7b95d4aed55)

- Chọn **Tra cứu**

    ![image](https://github.com/thsambala/th/assets/66956549/2365a467-4493-49e6-960d-0a2263e9ad3b)

- Chọn tra cứu theo Học viên, chọn Học viên và chọn khoảng thời gian muốn tra cứu (**Sẽ lấy ra các khóa học học viên được ghi danh hoặc hết hạn trong khoảng thời gian đó**):

    ![image](https://github.com/thsambala/th/assets/66956549/209d7b43-af9d-498d-8304-ee3aa93f4fb3)

- Chọn **Tra cứu**

   ![image](https://github.com/thsambala/th/assets/66956549/4e068491-c714-41dd-abb7-c34775a7e59d)

- Quản trị hệ thống có settings plugin, truy cập **Dashboard > Site administration > Plugins > Blocks > TH course completion report**

    ![image](https://github.com/thsambala/th/assets/66956549/35b5a2c9-7d88-448d-8e98-2f090efd7d9f)

  - idnumbercategory: là idnumber của category (danh mục), để lấy các khóa học trong category đó. có thể điền nhiều idnumber của category vào đây ngăn cách bằng dấu phẩy (,) để lấy tất cả các khóa học trong các category đó.
  - date: giới hạn thời gian tìm kiếm trong form.
        
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user, user_enrolments, enrol, role_assignments, context, course
- Các câu truy vấn đã dùng:

```sql
 SELECT CONCAT(u.id,'_',b.courseid),u.id as userid,a.timestart,a.timeend,a.roleid,a.status,b.*,c.register
		    from
		    (select distinct ra.userid,e.courseid,timestart,timeend,ue.status,ra.roleid
		    from {user_enrolments} ue
		    join {enrol} e on ue.enrolid = e.id
		    join {role_assignments} ra on ra.userid = ue.userid and ra.roleid = 5
		    where ue.status = 0  AND (
                	(ue.timestart >= $from_date AND ue.timestart <= $to_date) OR
                	(ue.timeend >= $from_date AND ue.timeend <= $to_date)
                	)
		    	) a
		    join
		    (select distinct ra.userid,c.id as courseid,c.*
		    	from {context} ctx
		    	join {course} c on c.id = ctx.instanceid and ctx.contextlevel = 50
		    	join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = 5) b
		    on a.userid = b.userid and a.courseid = b.courseid
		    join
		    {user} u on u.id = a.userid and u.suspended = 0
		    left join
		   	(SELECT distinct tr.userid,tr.courseid,MAX(tr.timecreated) as register
			   	FROM {th_registeredcourses} tr
			   	JOIN {user} us ON tr.userid = us.id
			   	JOIN {course} co ON tr.courseid = co.id
			   	WHERE tr.timeactivated <> 0
			   	GROUP BY tr.userid,tr.courseid) c
			on a.userid = c.userid and a.courseid = c.courseid
		    where b.courseid <> 1 and b.courseid $insql order by b.startdate DESC
```

```sql
SELECT CONCAT(a.userid,'_',b.courseid),a.userid,a.timestart,a.timeend,a.roleid,a.status,b.*,c.register
		    from
		    (select distinct ra.userid,e.courseid,timestart,timeend,ue.status,ra.roleid
		    from {user_enrolments} ue
		    join {enrol} e on ue.enrolid = e.id
		    join {role_assignments} ra on ra.userid = ue.userid and ra.roleid = 5
		    where ue.status = 0  AND (
                	(ue.timestart >= $from_date AND ue.timestart <= $to_date) OR
                	(ue.timeend >= $from_date AND ue.timeend <= $to_date)
                	)
		    	) a
		    join
		    (select distinct ra.userid,c.id as courseid,c.*
		    	from {context} ctx
		    	join {course} c on c.id = ctx.instanceid and ctx.contextlevel = 50
		    	join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = 5) b
		    on a.userid = b.userid and a.courseid = b.courseid
		    join
		    {course} c on c.id = a.courseid and c.visible = 1
		     left join
		   	(SELECT distinct tr.userid,tr.courseid,MAX(tr.timecreated) as register
			   	FROM {th_registeredcourses} tr
			   	JOIN {user} us ON tr.userid = us.id
			   	JOIN {course} co ON tr.courseid = co.id
			   	WHERE tr.timeactivated <> 0
			   	GROUP BY tr.userid,tr.courseid) c
			on a.userid = c.userid and a.courseid = c.courseid
		    where b.courseid <> 1 and b.userid $insql order by b.startdate DESC
```
2. Thêm Bảng: Không

3. Database Diagram: Không

## Method
1. Các Method: th_course_completion_report_get_courseid_category, th_course_completion_report_get_userid, get_course_progress_percentage
2. Chi tiết các Method:

- th_course_completion_report_get_courseid_category: Lấy các khóa học trong các danh mục trong setting
```php
/**
 * [th_course_completion_report_get_courseid_category description] Lấy các khóa học trong các danh mục trong setting
 * @return [array] [list course]
 */
function th_course_completion_report_get_courseid_category()
```

- th_course_completion_report_get_userid: lấy tất cả các Tài khoản trên hệ thống
```php
/**
 * [th_course_completion_report_get_userid lấy tất cả các Tài khoản trên hệ thống]
 * @return [array] [list user]
 */
function th_course_completion_report_get_userid() 
```

```php
/**
 * Returns the course percentage completed by a certain user, returns null if no completion data is available.
 *
 * @param \stdClass $course Moodle course object
 * @param int $userid The id of the user, 0 for the current user
 * @return null|float The percentage, or null if completion is not supported in the course,
 *         or there are no activities that support completion.
 */
public static function get_course_progress_percentage($course, $userid = 0) 
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_course_completion_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

