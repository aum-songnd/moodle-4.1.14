# **Tài liệu phát triển tính năng đăng ký học**

1. **Tên plugin**: th_register
2. **Kiểu plugin**: local
3. **Project**: TNU
4. **Chức năng chung**: Đăng ký học
5. **Người phát triển**: linhnt720@wru.vn, datdt720@wru.vn
6. **Người yêu cầu**: Minhpl@aum.edu.vn
7. **Tham chiếu ERP:** 
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_register

# 1. Yêu cầu: (bắt buộc)
Hỗ trợ học viên đăng ký học

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt: (bắt buộc)

- Capability có quyền truy cập và sử chức năng: 
    - local/th_register:view
    - local/th_register:register

    ```php
    $capabilities = array(
        'local/th_register:view' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        ),
        'local/th_register:download_hoa_don' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        ),
        'local/th_register:confirm_dong_hoc_phi' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        ),
        'local/th_register:view_danh_sach_dong_hoc_phi' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'manager' => CAP_ALLOW,
            ),
        ),
        'local/th_register:register' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'user' => CAP_ALLOW,
            ),
        ),
    );
    ```


**Quản lý môn học**

- Danh sách môn học:

![image](https://github.com/thsambala/th/assets/57883256/b536e4f2-c98d-471c-87d0-eb09672d9512)

- Thêm môn học:

![image](https://github.com/thsambala/th/assets/57883256/32de1bc1-461c-4a91-922b-7dc12950e291)

- Thêm môn học theo lô:

![image](https://github.com/thsambala/th/assets/57883256/a317cf66-3422-4400-be79-490d41e6297e)

- Thêm môn học vào ngành theo lô:

![image](https://github.com/thsambala/th/assets/57883256/2835a2bf-2004-489d-bf0e-58a1e0577861)

- Thêm môn học thuộc đối tượng theo lô

![image](https://github.com/thsambala/th/assets/57883256/a651bb09-8737-41da-ba8f-d1f7a4092933)

**Quản lý ngành học**

- Danh sách ngành học:

![image](https://github.com/thsambala/th/assets/57883256/247c8326-dd2e-46af-8608-915d1f32fd1f)

- Thêm ngành học:

![image](https://github.com/thsambala/th/assets/66956549/3e8fdae3-3acf-4cd9-92b8-948438c4aa86)

- Thêm ngành học theo lô:

![image](https://github.com/thsambala/th/assets/57883256/990c1921-7fe0-418b-9932-653228d0cd8e)

**Quản lý đợt mở môn**

- Danh sách đợt mở:

![image](https://github.com/thsambala/th/assets/66956549/430e5cf1-27b5-4273-83b0-a183beb78355)

- Thêm đợt mở:

![image](https://github.com/thsambala/th/assets/66956549/24343c50-b888-4bef-8627-5ec3a8d9c558)

**Quản lý đối tượng**

- Danh sách đối tượng:

![image](https://github.com/thsambala/th/assets/57883256/101d5374-94f3-4ab7-a0ae-eba4859b63cb)

- Thêm đối tượng:

![image](https://github.com/thsambala/th/assets/57883256/8596d68b-37be-4f7c-a9f4-a8212dee5684)

- Thêm đối tượng theo lô:

![image](https://github.com/thsambala/th/assets/57883256/3fcefd9c-634a-4468-916a-3839f611b23a)

**Quản lý môn học trong đợt mở**

- Danh sách môn học trong các đợt:

![image](https://github.com/thsambala/th/assets/57883256/9f5844c5-2a57-4bd8-aadc-c79166649d5e)

- Thêm môn học mới vào đợt mở môn:

![image](https://github.com/thsambala/th/assets/57883256/28f970ca-5349-4ce4-8096-eb5cfccdc048)

- Thêm môn học vào đợt mở theo lô:

![image](https://github.com/thsambala/th/assets/57883256/cd2cddba-635c-444b-9c35-cb88c7789443)

**Quản lý hóa đơn**

- Danh sách học viên đóng học phí:

![image](https://github.com/thsambala/th/assets/66956549/736774b1-2af5-463a-bb8d-26e4a73c1ff5)

**Học viên Đăng ký học**

- Giao diện chính chức năng:

![image](https://github.com/thsambala/th/assets/66956549/61fd932f-0278-4a99-a212-15e507624cd7)

- Chọn đợt mở và ngành học:

![image](https://github.com/thsambala/th/assets/66956549/aa58af7c-8cd0-421c-a8ff-387da5c6495b)

- Chọn môn học muốn đăng ký sau đó nhấn đăng ký:

![image](https://github.com/thsambala/th/assets/66956549/f0fccb64-5e51-4a03-ab4b-9f81adc4a7b4)

- Hiển thị danh sách môn học đã đăng ký và số tiền học phí của đợt mở

![image](https://github.com/thsambala/th/assets/66956549/4560043d-1803-4b99-96a9-b49ea497f639)

- Nộp hóa đơn đóng học phí

![image](https://github.com/thsambala/th/assets/57883256/1bee1f09-4bf8-437b-b82b-0f364d3a79b8)

- Chọn thêm hóa đơn hệ thống trả về giao diện như hình dưới

![image](https://github.com/thsambala/th/assets/57883256/ace13ac6-3fad-42f1-a170-5fb830f735a1)

- Nhập số tiền nộp và hình ảnh hóa đơn chuyển khoản sau đó chọn gửi hệ thống trả về giao diện như hình dưới:

![image](https://github.com/thsambala/th/assets/66956549/4d401d7a-c155-4f0d-ab8a-7328512a5a9b)

# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart 
**Database:**
- Các bảng cần dùng:
    - user
    - th_mon_hoc
    - th_nganh_hoc
    - th_doi_tuong
    - th_dot_mo_mon
    - th_dot_mo_has_mon_hoc
    - th_hoa_don
    - th_dang_ky_mon_hoc
    - th_mon_hoc_has_nganh_hoc
    - th_mon_hoc_has_doi_tuong
```sql
SELECT {th_dot_mo_has_mon_hoc}.mon_hoc_id FROM {th_dang_ky_mon_hoc},{th_dot_mo_has_mon_hoc} WHERE {th_dang_ky_mon_hoc}.userid_moodle = '$user_id'
                AND {th_dot_mo_has_mon_hoc}.dot_mo_mon_id = '$dot_mo->id' AND {th_dot_mo_has_mon_hoc}.id = {th_dang_ky_mon_hoc}.dot_mo_has_mon_hoc_id
```

```sql
SELECT {th_dot_mo_has_mon_hoc}.mon_hoc_id FROM {th_dang_ky_mon_hoc},{th_dot_mo_has_mon_hoc} WHERE {th_dang_ky_mon_hoc}.userid_moodle = '$userid'
    AND {th_dot_mo_has_mon_hoc}.dot_mo_mon_id = '$dot_mo_mon_id' AND {th_dot_mo_has_mon_hoc}.id = {th_dang_ky_mon_hoc}.dot_mo_has_mon_hoc_id AND {th_dang_ky_mon_hoc}.id_nganh = '$nganhid'
```
- Bảng mới:

    - th_mon_hoc

        | Tên        | Kiểu | Chú thích    |
        |------------|------| ------------ |
        | id         | int  | id môn học   |
        | ma_mon     | char | Mã môn học   |
        | ten_mon    | char | Tên môn học  |
        | so_tin_chi | int  | Số tín chỉ   |
        | ngay_tao   | int  | Ngày tạo     |
        | nguoi_tao  | int  | Người tạo    |
        | ngay_sua   | int  | Ngày sửa     |
        | nguoi_sua  | int  | Người sửa    |
    
    - th_nganh_hoc

        | Tên       | Kiểu | Chú thích     |
        |-----------|------| ------------  |
        | id        | int  | id ngành học  |
        | ma_nganh  | char | Mã ngành học  |
        | ten_nganh | char | Tên ngành học |
        | ngay_tao  | int  | Ngày tạo      |
        | nguoi_tao | int  | Người tạo     |
        | ngay_sua  | int  | Ngày sửa      |
        | nguoi_sua | int  | Người sửa     |

    - th_doi_tuong

        | Tên           | Kiểu | Chú thích     |
        |---------------|------| --------------|
        | id            | int  | id đối tượng  |
        | ma_doi_tuong  | char | Mã đối tượng  |
        | ten_doi_tuong | char | Tên đối tượng |
        | ngay_tao      | int  | Ngày tạo      |
        | nguoi_tao     | int  | Người tạo     |
        | ngay_sua      | int  | Ngày sửa      |
        | nguoi_sua     | int  | Người sửa     |

    - th_dot_mo_mon

        | Tên                 | Kiểu | Chú thích           |
        |---------------------|------| ------------------- |
        | id                  | int  | id đợt mở           |
        | ma_dot_mo_mon       | char | Mã đợt mở           |
        | ten_dot_mo          | char | Tên đợt mở          |
        | hoc_phi_mot_tin_chi | int  | Học phí một tín chỉ |
        | tu_ngay             | int  | Từ ngày             |
        | den_ngay            | int  | Đến ngày            |
        | ngay_tao            | int  | Ngày tạo            |
        | nguoi_tao           | int  | Người tạo           |
        | ngay_sua            | int  | Ngày sửa            |
        | nguoi_sua           | int  | Người sửa           |

    - th_dot_mo_has_mon_hoc

        | Tên           | Kiểu | Chú thích    |
        |---------------|------| ------------ |
        | id            | int  |              |
        | dot_mo_mon_id | int  | id đợt mở    |
        | mon_hoc_id    | int  | id môn học   |
        | ngay_tao      | int  | Ngày tạo     |
        | nguoi_tao     | int  | Người tạo    |
        | ngay_sua      | int  | Ngày sửa     |
        | nguoi_sua     | int  | Người sửa    |

    - th_hoa_don

        | Tên              | Kiểu | Chú thích    |
        |------------------|------| ------------ |
        | id               | int  |              |
        | userid_moodle    | int  | id user      |
        | dot_mo_mon_id    | int  | id đợt mở    |
        | link_anh_hoa_don | char | link ảnh     |
        | so_tien_da_nop   | int  | số tiền nộp  |
        | ngay_tao         | int  | Ngày tạo     |
    
    - th_dang_ky_mon_hoc
        | Tên                   | Kiểu | Chú thích                |
        |-----------------------|------| ------------------------ |
        | id                    | int  |                          |
        | userid_moodle         | int  | id user                  |
        | dot_mo_has_mon_hoc_id | int  | id dot_mo_has_mon_hoc_id |
        | id_nganh              | int  | id nganh                 |
        | ma_dang_ky            | char | mã đăng ký               |
        | ngay_tao              | int  | Ngày tạo                 |

    - th_mon_hoc_has_nganh_hoc

        | Tên          | Kiểu | Chú thích    |
        |--------------|------| ------------ |
        | id           | int  |              |
        | mon_hoc_id   | int  | id môn học   |
        | nganh_hoc_id | int  | id ngành học |
        | ngay_tao     | int  | Ngày tạo     |
        | nguoi_tao    | int  | Người tạo    |
        | ngay_sua     | int  | Ngày sửa     |
        | nguoi_sua    | int  | Người sửa    |

    - th_mon_hoc_has_doi_tuong

        | Tên          | Kiểu | Chú thích    |
        |--------------|------| ------------ |
        | id           | int  |              |
        | mon_hoc_id   | int  | id môn học   |
        | doi_tuong_id | int  | id đối tượng |
        | ngay_tao     | int  | Ngày tạo     |
        | nguoi_tao    | int  | Người tạo    |
        | ngay_sua     | int  | Ngày sửa     |
        | nguoi_sua    | int  | Người sửa    |
    
    - 20230713: update CSDL:
        - th_nganh_hoc: thêm cột hoc_phi_mot_tin_chi
        - th_dot_mo_mon: xóa cột hoc_phi_mot_tin_chi

**Method**
1. Các Method: ds_mon_dk
2. Chi tiết Method:

- ds_mon_dk:
```php
/**
 * Bảng danh sách môn học đăng ký
 *
 * @param int $userid
 * @param int $dot_mo_mon_id 
 * @param int $hoc_phi_1_tin
 * @param int $nganhid
 * @return object  
 */
function ds_mon_dk($userid, $dot_mo_mon_id, $hoc_phi_1_tin, $nganhid)
```

# 4. mã nguồn: hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này (nếu cần)

# 5. Triển khai: (Hướng dẫn triển khai, lưu ý khi upload nên appstore. nếu cần)

# 6. Kiểm thử: (nếu cần)
