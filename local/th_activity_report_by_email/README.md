# Tài liệu Gửi nhật ký hoạt động

1. **Tên plugin:** th_activity_report_by_email
2. **Kiểu plugin**: local
3. **Project:** TNU
4. **Chức năng chung:** Gửi nhật ký hoạt động của 1 tài khoản đến 1 danh sách các email trong setting của plugin
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_activity_report_by_email
# 1. Yêu cầu:

- Gửi nhật ký hoạt động của 1 tài khoản đến 1 danh sách các email trong setting của plugin

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **local/th_activity_report_by_email:view**
- Đoạn code định nghĩa capabilities:

```php
    'local/th_activity_report_by_email:view' => array(
		'riskbitmask' => RISK_SPAM | RISK_XSS,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
			'manager' => CAP_ALLOW,
		),
	),
```

2. Hướng dẫn sử dụng
- Truy cập **Dashboard > Site administration > Plugins > Local plugins > TH th_activity_report_by_email**

![image](https://github.com/thsambala/th/assets/66956549/7a055d3e-a817-4d1b-bbd3-7b8306713b3d)

-  Mặc định vào 4 giờ sáng thứ 2 hằng tuần sẽ gửi nhật ký hoạt động trong tuần. Có thể thay đổi trong **Dashboard > Site administration > Server > Tasks > Scheduled tasks**
  
![image](https://github.com/thsambala/th/assets/66956549/91f5bd92-4dc4-4a07-bbe7-7558c555937e)

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user,logstore_standard_log
- Các câu truy vấn đã dùng:

2. Thêm Bảng: không
3. Database Diagram: Không

## Method
1. Các Method: th_activity_report_by_email
2. Chi tiết các Method:
```php
/**
 * th_activity_report_by_email function gửi nhật ký hoạt động của 1 tài khoản đến 1 danh sách các email
 *
 * @return void
 */
function th_activity_report_by_email() 
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_activity_report_by_email

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

