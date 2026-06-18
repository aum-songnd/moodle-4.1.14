# Tài liệu Xuất lộ trình học

1. **Tên plugin:** th_roadmap_vstep
2. **Kiểu plugin:** local
3. **Project:** Vstep
4. **Chức năng chung:** Xuất lộ trình học dựa trên điểm Nghe, Nói, Đọc, Viết của học viên.
5. **Người phát triển:** datdtpx@gmail.com
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_roadmap_vstep
# 1. Yêu cầu:
1. Yêu cầu
- Xuất lộ trình học dựa trên điểm Nghe, Nói, Đọc, Viết của học viên.

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập, sử dụng tính năng và đoạn code định nghĩa capabilities
- Các capability: **local/th_roadmap_vstep:import**, **local/th_roadmap_vstep:export**
- Đoạn code định nghĩa capabilities:
```php
    'local/th_roadmap_vstep:import' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),

     'local/th_roadmap_vstep:export' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ),
    ),
```
2. Hướng dẫn sử dụng
- Quản trị hệ thống
    - Truy cập : **Dashboard > Site administration > Plugins > Local plugins > TH Road Map Vstep**
        - Xem các idnumer của item (listening, reading, speaking, writing) để trong khóa học đặt item có idnumber tương ứng.
        - Tải bảng lộ trình: 
        ![image](https://github.com/minhpl/image_repository/assets/66956549/20e9dab2-7f61-4334-9ebe-478cb16b76b2)
        ![image](https://github.com/minhpl/image_repository/assets/66956549/cee2eaf2-f598-4925-a788-5a02bcd3ded1)
        ![image](https://github.com/minhpl/image_repository/assets/66956549/60cf96b7-af3e-4bfc-81fc-c0b5d1e38f6d)
- Giảng viên
    - Để xuất lộ trình chúng ta cần thực hiện 2 bước:
      - Bước 1: Nhập điểm target level cho học viên
  
        - Vào khóa học cần Nhập điểm
        - Tìm Khối **Điều hướng** chọn **Điểm số**
    ![image](https://github.com/minhpl/image_repository/assets/66956549/166c4393-cdf7-4f61-96f2-0fb916828eb2)
        - Bảng điểm xuất hiện
    ![image](https://github.com/minhpl/image_repository/assets/66956549/b68d0050-0f5f-4ae6-aec5-01046fc109f6)
        - Tìm thành phần Target
    ![image](https://github.com/minhpl/image_repository/assets/66956549/076db908-f4f9-4c25-91ea-c2497679456a)
        - Chọn hình **cái bút**
    ![image](https://github.com/minhpl/image_repository/assets/66956549/0b444e13-7ebd-4aa9-85a3-cc2ab12cb0e6)
        - Chọn **Bật chế độ chỉnh sửa**, để ghi điểm Target.
    ![image](https://github.com/minhpl/image_repository/assets/66956549/da7aeaa3-e72f-46cf-878a-357ad58b8e63)
        - Nhập điểm Target cho mỗi học viên vào ô tương ứng và chọn **Lưu**.
    ![image](https://github.com/minhpl/image_repository/assets/66956549/8997ff20-9f08-42ec-bd00-67e24c6e738e)
        - Quay lại Bảng điểm để xem thay đổi.
      - Bước 2: Xuất lộ trình
        - Người xuất cần được cấp quyền (**local/th_roadmap_vstep:export**) xuất lộ trình trên hệ thống mới sử dụng được tính năng này.
        - Vào khóa học muốn xuất lộ trình.
    ![image](https://github.com/minhpl/image_repository/assets/66956549/365fcd83-379d-47ea-a1d6-d8b232af6f48)
        - Trong phần **Khu vực quản trị**. Chọn **Xuất lộ trình**.
    ![image](https://github.com/minhpl/image_repository/assets/66956549/d3ca16ff-16c3-4512-ba4f-a59efa6c94fb)
        - Chọn **Tải xuống**
    ![image](https://github.com/minhpl/image_repository/assets/66956549/4c9249e4-e36e-476e-8eeb-3befe01a4e39)
        - Tệp **Lộ trình được tải xuống**. Chúng ta mở lên.
    ![image](https://github.com/minhpl/image_repository/assets/66956549/7a22f00a-09f7-4ef6-ae5d-c5cd85230967)
# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: course, grade_grades, local_th_roadmap_vstep
- Các câu truy vấn đã dùng:

```php
    $sql = "SELECT fullname FROM {course} WHERE shortname = ?";
```

```php
    $records = $DB->get_records('local_th_roadmap_vstep', null, '', 'coursecode,knlnn,listening,reading,speaking,writing,dktq,groups');
```
2. Thêm Bảng: local_th_roadmap_vstep

| Trường(cột) | Kiểu dữ liệu | Bắt buộc |
|-------------|--------------|----------|
| id          | int          | có       |
| coursecode  | char         |          |
| knlnn       | int          |          |
| listening   | int          |          |
| speaking    | int          |          |
| reading     | int          |          |
| writing     | int          |          |
| dktq        | char         |          |
| groups      | char         |          |
| timecreated | int          |          |
| timemodified| int          |          |

3. Database Diagram: Không

## Method
1. Các Method: get_coursefullname, th_roadmap_vstep, roadmapA, roadmapB, roadmapC.
2. Chi tiết các Method:

```php
/** 
 * @param  $shortname [string]
 * @return [string] fullname
 */
function get_coursefullname($shortname)
```
```php
/**
 * @param  $records [stdClass] record table local_th_roadmap_vstep
 * @param  $listening [int]  listening level
 * @param  $reading [int]   reading level
 * @param  $speaking [int]  speaking level
 * @param  $writing [int]   writing level
 * @param  $target [int]    target level
 * @return [string] Road Map
 */
function th_roadmap_vstep($records, $listening, $reading, $speaking, $writing, $target) 
```
```php
/**
 * @param  $data [stdClass] record table local_th_roadmap_vstep
 * @param  $start [string] target level
 * @param  $end [string] Current level
 * @param  $niz [array] save roadmap
 * @return [array] road map A
 */
function roadmapA($data, $start, $end, $niz) 
```
```php
/**
 * @param  $data [stdClass] record table local_th_roadmap_vstep
 * @param  $start [string] target level
 * @param  $end [string] Current level
 * @param  $type [string] type (listening,speaking,reading,writing) column table local_th_roadmap_vstep
 * @param  $niz [array] save roadmap
 * @return [array] road map B
 */
function roadmapB($data, $start, $end, $type, $niz)
```
```php
/**
 * @param  $data [stdClass] record table local_th_roadmap_vstep
 * @param  $niz [array] save roadmapC
 * @param  $thithu [array] save roadmapThithu
 * @return [array] road map C and thi thu
 */
function roadmapC($data, $niz, $thithu)
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_roadmap_vstep

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

