# Tài liệu Báo cáo Hoạt động của Người dùng

1. **Tên plugin:** th_user_activity_report
2. **Kiểu plugin**: block
3. **Project:** AOF,TNU,TNUT
4. **Chức năng chung:** Thông kê hoạt động của Người dùng theo mã khóa học, mã lớp học, mã Học viên
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 	TASK-141
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_user_activity_report
1. Yêu cầu
- Thông kê hoạt động của Người dùng theo mã khóa học, mã lớp học, mã Học viên (Giới hạn thời gian xem báo cáo là 31 ngày)
  
2. Đầu vào, Đầu ra
- Đầu vào:
  - Mã khóa:
   
   ![image](https://user-images.githubusercontent.com/66956549/210733492-8198f2ee-a660-4550-9f7e-de322aaa632f.png)

   - Mã lớp:
  
    ![image](https://user-images.githubusercontent.com/66956549/210734070-d98e53e3-44be-48da-805c-a31eeaf26ba6.png)

   - Học viên:

    ![image](https://user-images.githubusercontent.com/66956549/210734327-0afc390f-d3cd-4b00-82fc-1ace65567dbc.png)

- Đầu ra:
    
    ![image](https://user-images.githubusercontent.com/66956549/210734632-44c16e59-b3c9-4792-a418-cdef479fbde2.png)

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_user_activity_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_user_activity_report:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'teacher' => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
		),
	),
```
2. Hướng dẫn sử dụng

- Chọn **Mã khóa**, rồi Chọn **Xem**

   ![image](https://user-images.githubusercontent.com/66956549/210739556-422a854c-40fc-437e-b53d-64c60174badb.png)

- Bảng đầu ra được hiển thị:
  
  ![image](https://user-images.githubusercontent.com/66956549/210740037-ae5cc283-732c-423a-a469-d781c1a00607.png)

- Chọn **Mã Lớp**, rồi Chọn **Xem**
  
  ![image](https://user-images.githubusercontent.com/66956549/210752984-06eede8e-6699-47b4-b5ac-49126c1bb36a.png)

- Chọn **Học viên**, rồi Chọn **Xem**
    ![image](https://user-images.githubusercontent.com/66956549/210753229-5da1d7f8-b0e6-4cf4-87e5-0a2aacd7a602.png)

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng logstore_standard, user_info_data
- Các câu truy vấn đã dùng:

```sql
 $sql = "SELECT userid,data
        from {user_info_data}
        where fieldid = $fieldid
        and userid $insql_userid
        and data $insql_rolenotin";
```

```sql
 $sql = "SELECT userid,data
        from {user_info_data}
        where fieldid = $fieldid
        and userid $insql_userid
        and " . $DB->sql_compare_text('data') . " = '" . $rolename . "'";
```
- Lấy các hoạt động của người dùng
```sql
 $sql = "Select row_number() OVER() as stt, userid, timecreated as time
        from lms_logstore_standard_log 
        where userid $insql and action != 'failed' and target != 'user_login' and timecreated <= :time_to and timecreated >= :time_from";
```

2. Thêm Bảng: Không
3. Database Diagram: Không

## Method
1. Các Method: Không
2. Chi tiết các Method: không

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_user_activity_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

