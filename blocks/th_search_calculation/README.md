# **Tài liệu phát triển tính năng Tìm khóa học chứa công thức**

1. **Tên plugin**: th_search_calculation
2. **Kiểu plugin**: block
3. **Project**:  tnu
4. **Chức năng chung**: 
- Hiển thị danh sách khóa học chứa công thức điểm tổng kết
- Hiển thị danh sách khóa học chứa công thức điểm bài kiểm tra
- Cập nhật công thức điểm tổng kết và điểm kiểm tra hàng loạt
5. **Người phát triển**: linhnt720@wru.vn
6. **Người yêu cầu**: TNU yêu cầu
7. **Tham chiếu ERP:** TASK-115
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/blocks/th_search_calculation
# 1. Yêu cầu:

- Hiển thị danh sách khóa học chứa công thức điểm tổng kết
- Hiển thị danh sách khóa học chứa công thức điểm bài kiểm tra
- Cập nhật công thức điểm tổng kết và điểm kiểm tra hàng loạt

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt

- Định nghĩa capability: block/th_search_calculation:view

- Hiển thị danh sách khóa học chứa công thức điểm tổng kết
    - Đầu vào:
        - Danh sách các khóa học trên hệ thống
    - Đầu ra:
        - STT
        - Tên khóa học
        - Tên rút gọn khóa học
        - Công thức tổng khóa học
    ![image](https://user-images.githubusercontent.com/57883256/205811609-bd83539f-e7a6-404e-82e9-48bdb67504e1.png)
- Hiển thị danh sách khóa học chứa công thức điểm kiểm tra
    - Đầu vào:
        - Danh sách các khóa học trên hệ thống
    - Đầu ra:
        - STT
        - Tên khóa học
        - Tên rút gọn khóa học
        - Công thức điểm kiểm tra khóa học
    ![image](https://user-images.githubusercontent.com/57883256/205812433-2893ed0e-4274-4a03-8bda-951f055b30e1.png)

- Hiển thị danh sách khóa học chứa công thức điểm chuyên cần
    - Đầu vào:
        - Danh sách các khóa học trên hệ thống
    - Đầu ra:
        - STT
        - Tên khóa học
        - Tên rút gọn khóa học
        - Công thức điểm kiểm tra khóa học
    ![image](https://github.com/thsambala/th/assets/57883256/2a96f7f2-7ad1-4567-bab9-b09a770ae92c)

- Hiển thị danh sách khóa học chứa công thức điểm điều kiện thi
    - Đầu vào:
        - Danh sách các khóa học trên hệ thống
    - Đầu ra:
        - STT
        - Tên khóa học
        - Tên rút gọn khóa học
        - Công thức điểm kiểm tra khóa học
    ![image](https://github.com/thsambala/th/assets/57883256/57409a84-950e-45e7-b113-602e4fecf5d3)

- Cập nhật công thức hàng loạt
    - Đầu vào:
        - Loại điểm cập nhật
            - Điểm kiểm tra
            - Điểm tổng kết
            - Điểm điều kiện
        - Công thức cần cập nhập
        - Công thức mới
    
    ![image](https://github.com/thsambala/th/assets/57883256/f7775919-605b-4f03-98d2-82ce8994e780)
    - Sau khi điền các thông tin người dùng nhấn gửi hệ thống sẽ trả về giao diện xác nhận như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/205814370-528bcf50-ea76-4e6d-b02e-a23ba2f86ae8.png)
    - Sau khi kiểm tra các thông tin nếu đã chính xác người dùng nhấn gửi để cập nhật công thức hàng loạt.
    - Hệ thống trả về giao diện thông báo thành công như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/93c3e6f5-0a94-4a57-a3b1-c4b13e6c1527)

- Cập nhật công thức hàng loạt theo đợt mở
    - Đầu vào:
        - Loại điểm cập nhật
            - Điểm kiểm tra
            - Điểm tổng kết
            - Điểm điều kiện
        - Đợt mở môn
        - Công thức cần cập nhập
        - Công thức mới
    
    ![image](https://github.com/thsambala/th/assets/57883256/c748ba31-7cd0-416e-9c7a-2344e01a4079)
    - Sau khi điền các thông tin người dùng nhấn gửi hệ thống sẽ trả về giao diện xác nhận như hình dưới:
    ![image](https://user-images.githubusercontent.com/57883256/205814370-528bcf50-ea76-4e6d-b02e-a23ba2f86ae8.png)
    - Sau khi kiểm tra các thông tin nếu đã chính xác người dùng nhấn gửi để cập nhật công thức hàng loạt.
    - Hệ thống trả về giao diện thông báo thành công như hình dưới:
    ![image](https://github.com/thsambala/th/assets/57883256/93c3e6f5-0a94-4a57-a3b1-c4b13e6c1527)

# 3. Phân tích thiết kế (database, functions nếu cần)

# 4. Mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết)

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)