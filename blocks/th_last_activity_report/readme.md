# Tài liệu Báo cáo hoạt động lần cuối của Người dùng trên cả Web và App

1. **Tên plugin:** th_last_activity_report
2. **Kiểu plugin:** block
3. **Project:** VMC
4. **Chức năng chung:** Thông kê hoạt động lần cuối của Người dùng trên cả Web và App
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-148
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_last_activity_report
# 1. Yêu cầu:
1. Yêu cầu
- Báo cáo để thống kê các học viên không có hoạt động trên hệ thống (cả trên app) trong khoảng thời gian tìm kiếm
- Đầu vào: 

![image](https://user-images.githubusercontent.com/66956549/221334197-83295165-e3ac-4e80-879e-41b926fb7a53.png)
   
- Kết quả trả về dạng bảng

![image](https://user-images.githubusercontent.com/66956549/221335094-aac56721-2307-4320-9b6c-b71ea4234863.png)


# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **block/th_last_activity_report:view**
- Đoạn code định nghĩa capabilities:
```php
'block/th_last_activity_report:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
```
2. Hướng dẫn sử dụng
- Truy cập **TH Dashboard** và chọn **Báo cáo hoạt động lần cuối của Người dùng**

- Chọn thời gian muốn xem những báo cáo:
![image](https://user-images.githubusercontent.com/66956549/221335227-94f035e2-f811-442c-9991-0db445ccbe36.png)

- Chọn **Xem**
![image](https://user-images.githubusercontent.com/66956549/221335187-19e3f15a-ef8d-4d3e-abd8-907b751f5937.png)

- Báo cáo Xuất hiện
![image](https://user-images.githubusercontent.com/66956549/221335094-aac56721-2307-4320-9b6c-b71ea4234863.png)
        
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: user, logstore_standard_log
- Các câu truy vấn đã dùng:

```sql
 $sql = "SELECT a.id, MAX(lsl.timecreated) as lastactivity
        FROM (SELECT id
            FROM {user}
            WHERE deleted = 0 AND (id <> 1 OR firstaccess = 0)
            EXCEPT
            SELECT DISTINCT u.id
            FROM {user} u
            JOIN {logstore_standard_log} lsl ON lsl.userid = u.id
            WHERE lsl.timecreated >= $data->startdate AND u.deleted = 0 AND lsl.realuserid IS NULL AND lsl.action <> 'loggedout') a
            LEFT JOIN {logstore_standard_log} lsl ON lsl.userid = a.id AND lsl.realuserid IS NULL AND lsl.action <> 'loggedout'
        GROUP BY a.id
        HAVING a.id <> 1";
```
1. Thêm Bảng: Không

2. Database Diagram: Không

## Method
1. Các Method: format_time
2. Chi tiết các Method:

- format_time: Lấy thời gian của hoạt động lần cuối của người dùng
```php
/**
* Format a date/time (seconds) as weeks, days, hours etc as needed
*
* Given an amount of time in seconds, returns string
* formatted nicely as years, days, hours etc as needed
*
* @package core
* @category time
* @uses MINSECS
* @uses HOURSECS
* @uses DAYSECS
* @uses YEARSECS
* @param int $totalsecs Time in seconds
* @param stdClass $str Should be a time object
* @return string A nicely formatted date/time string
*/
function format_time($totalsecs, $str = null) 
```
# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/blocks/th_last_activity_report

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

