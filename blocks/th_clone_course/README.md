# **Tài liệu phát triển tính năng giúp clone nhiều khóa học cùng một lúc bằng hai cách. Chọn khóa học hoặc từ file CSV**

1. **Tên plugin**: th_clone_course
2. **Kiểu plugin**: block
3. **Project**:  tnu,aof,tnut
4. **Chức năng chung**: giúp clone nhiều khóa học cùng một lúc bằng hai cách:
    - Cách 1: chọn khóa học 
    - Cách 2: dùng file csv chứa các thông tin cần thiết để import.
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-100
8. **Mã nguồn:** https://github.com/minhngb/th/tree/master/blocks/th_clone_course

# 1. Yêu cầu:
- Clone nhiều khóa học cùng một lúc bằng hai cách:
    - Cách 1: chọn khóa học 
    - Cách 2: dùng file csv chứa các thông tin cần thiết để import.
- Clone bằng chọn khóa học
    - Đầu vào khi clone bằng chọn khóa học:
        - Khóa học (Hiển thị những khóa học có ngày bắt đầu mới nhất )
        - Ngày bắt đầu (Hiển thị ngày chủ nhật gần nhất)
    - Lưu ý:
        - Khóa học phải có định dạng: tenkhoahoc - ngaybatdau (Ví dụ: Luật thương mại - 220221)
        - Tên rút gọn khóa học phải có định dạng: shortname-ngaybatdau (Ví dụ: LAWT224-220221)
    - Đầu ra khi clone bằng chọn khóa học:
        - Tên khóa học
        - Thông báo
- Clone bằng file csv:
    - Đầu vào khi clone bằng file csv:
        - File csv
        - Mẫu file csv:

        | CourseShortName | Ngaybatdau | 
        |--------------|-------|
        | Shortname1 | 21/2/2022 |
        | Shortname2 | 21/2/2022 | 

    - Đầu ra khi clone bằng file csv:
        - Gợi ý (Nếu tìm thấy giá trị lỗi trong file csv)
        - Danh sách khóa học có thể clone được:
            - Tên khóa học
            - Tên rút gọn khóa học
            - Trạng thái
            - Ngày bắt đầu khóa học

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt


- Capability có quyền truy cập và sử chức năng: block/th_clone_course:view

    ```php
    $capabilities = array(
        'block/th_clone_course:view' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        ),
    );
    ```

- Giao diện chính chức năng clone bằng chọn khóa học:
    - Chọn khóa học (Hiển thị những khóa học có ngày bắt đầu mới nhất )
    - Chọn ngày bắt đầu (Hiển thị ngày chủ nhật gần nhất)

    ![image](https://user-images.githubusercontent.com/57883256/169965314-6a993ac7-2283-47bb-a93d-64e7792892e5.png)

- Sau khi chọn khóa học và ngày bắt đầu người dùng chọn Clone để hệ thống tiến hành clone khóa học và trả về giao diện như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/169965631-cbb316c9-82fd-46d5-8b9d-cd6efefc1a0f.png)
    - Tên khóa học
    - Thông báo

- Chức năng clone bằng file csv:
  
    - Mẫu file csv:

    | CourseShortName | Ngaybatdau | 
    |--------------|-------|
    | Shortname1 | 21/2/2022 |
    | Shortname2 | 21/2/2022 | 
    - Giao diện
    
    ![image](https://user-images.githubusercontent.com/57883256/169965413-c3c90529-047d-4109-9ee8-d5490286ee52.png)

- Sau khi kéo file csv chứa thông tin đầu vào người dùng chọn Clone để hệ thống trả về giao diện xác nhận như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/210045035-a24f1e5c-67b7-4893-93d6-a1b1dd33adab.png)
    - Gợi ý (Nếu tìm thấy giá trị lỗi trong file csv)
    - Danh sách khóa học có thể clone được:
        - Tên khóa học
        - Tên rút gọn khóa học
        - Trạng thái
        - Ngày bắt đầu khóa học

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - course

- Lấy danh sách khóa học    
    ```sql
   SELECT * FROM {course} WHERE NOT id = 1
   ```
**Method:**
- Các Method cần dùng: create_copy, get_copies
- create_copy
    ```php
    /**
         * Creates a course copy.
         * Sets up relevant controllers and adhoc task.
         *
         * @return array $copyids THe backup and restore controller ids.
         */
        public function create_copy()
    ```
- get_copies
    ```php
    /**
         * Get the in progress course copy operations for a user.
         *
         * @param int $userid User id to get the course copies for.
         * @param int $courseid The optional source course id to get copies for.
         * @return array $copies Details of the inprogress copies.
         */
        static public function get_copies(int $userid, int $courseid=0)
    ```
# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
