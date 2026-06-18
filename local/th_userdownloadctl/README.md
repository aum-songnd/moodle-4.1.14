# TH User Download Control

| | |
|---|---|
| **Owner** | TH Vietnam Solution and Technology Limited |
| **Khởi tạo** | 2025-07-08 |
| **Cập nhật** | 2026-05-11 |
| **Phiên bản** | 2.0 |

Plugin Moodle kiểm soát quyền sử dụng chức năng **Download** trong trang Bulk user actions.

---

## Bài toán

Moodle mặc định cho phép tất cả người dùng có quyền `moodle/user:update` (thường là Manager, Site Administrator) vào trang **Bulk user actions** (`/admin/user/user_bulk.php`) và sử dụng chức năng **Download** để xuất toàn bộ danh sách tài khoản ra file.

Trong thực tế, một số tổ chức cần giao quyền quản lý người dùng (chỉnh sửa thông tin, kích hoạt/vô hiệu hóa tài khoản) cho một nhóm nhân sự nhất định, nhưng **không** muốn nhóm đó có thể tải xuống danh sách người dùng vì lý do bảo mật dữ liệu.

Moodle không cung cấp capability riêng để tách biệt quyền "quản lý người dùng" và quyền "tải xuống danh sách người dùng". Plugin này được xây dựng để giải quyết khoảng trống đó.

---

## Đặc tả chức năng

### Capability mới

Plugin định nghĩa một capability:

| Trường        | Giá trị                                        |
|---------------|------------------------------------------------|
| Tên           | `local/th_userdownloadctl:download`            |
| Mô tả         | Cho phép tải xuống danh sách người dùng        |
| Risk          | `RISK_PERSONAL`                                |
| Default (Manager) | `CAP_PREVENT` — không được phép mặc định  |
| Site Admin    | Luôn được phép (Moodle tự xử lý)              |

Việc phân quyền được thực hiện hoàn toàn qua **Role definition** hoặc **Role assignment** trong Moodle — không cần chỉnh sửa cấu hình plugin.

### Kiểm soát truy cập — 2 tầng

**Khi plugin bị tắt (disabled):**
Không can thiệp. Moodle xử lý quyền theo mặc định của nó.

**Khi plugin được bật (enabled):**
Kiểm tra duy nhất `has_capability('local/th_userdownloadctl:download', context_system())`:

- Có capability (kể cả Site Admin — Moodle tự xử lý) → cho qua, không can thiệp.
- Không có capability:
  - **GET request**: Ẩn `<option value="download">` khỏi giao diện bằng output buffering.
  - **POST request** với `action=download`: Redirect về trang và hiển thị thông báo lỗi.

> Plugin không kiểm tra `is_siteadmin()` riêng. Việc Site Admin luôn có quyền là hành vi mặc định của hàm `has_capability()` trong Moodle.

### Luồng xử lý

```
User vào /admin/user/user_bulk.php
          │
          ▼
Plugin enabled?
   Không → không can thiệp (Moodle default), kết thúc
   Có    →
          │
          ▼
has_capability('local/th_userdownloadctl:download', context_system())?
   Có  → không can thiệp, kết thúc
   Không →
          ├─ GET  → ẩn <option value="download"> khỏi HTML
          └─ POST action=download → redirect + thông báo lỗi
```

### Phạm vi

**Trong phạm vi:**
- Trang `/admin/user/user_bulk.php`
- Action `download` trong form bulk actions

**Ngoài phạm vi:**
- Các trang xuất dữ liệu khác (course participants, reports, v.v.)
- Các bulk action khác (delete, confirm, v.v.)

### Test cases

| #  | Tình huống                                                        | Kết quả mong đợi                          |
|----|-------------------------------------------------------------------|-------------------------------------------|
| 1  | Plugin **tắt**, bất kỳ người dùng nào                             | Moodle xử lý mặc định, không can thiệp   |
| 2  | Plugin **bật**, Site Admin                                        | `has_capability` = true → thấy và dùng được Download |
| 3  | Plugin **bật**, người dùng được gán capability                    | `has_capability` = true → thấy và dùng được Download |
| 4  | Plugin **bật**, người dùng không có capability                    | Không thấy option Download trong UI      |
| 5  | Plugin **bật**, người dùng không có capability, POST thẳng `action=download` | Redirect + thông báo lỗi        |
| 6  | Plugin **bật**, không ai được gán capability                      | Tất cả (trừ Site Admin) đều bị chặn      |

---

## Cấu trúc file

```
local/th_userdownloadctl/
├── db/
│   └── access.php       — định nghĩa capability
├── lang/
│   ├── en/
│   │   └── local_th_userdownloadctl.php
│   └── vi/
│       └── local_th_userdownloadctl.php
├── lib.php              — hook before_http_headers (logic chính)
├── settings.php         — trang cấu hình plugin
├── version.php
└── README.md
```

---

## Hướng dẫn cài đặt

### Yêu cầu

- Moodle 4.1 trở lên

### Các bước cài đặt

1. Sao chép thư mục `th_userdownloadctl` vào `{moodle_root}/local/`

2. Đăng nhập với tài khoản Site Admin, vào **Site administration > Notifications** để Moodle nhận diện và cài đặt plugin.

3. Sau khi cài đặt, vào **Site administration > Plugins > Local plugins > TH User Download Control** và bật plugin bằng cách đánh dấu **Enable download restriction**.

### Hướng dẫn phân quyền

Sau khi bật plugin, mặc định **không ai** (ngoài Site Admin) có thể sử dụng chức năng Download. Để cấp quyền:

**Cách 1 — Gán cho một Role (khuyến nghị):**

1. Vào **Site administration > Users > Permissions > Define roles**
2. Chọn role cần cấp quyền (ví dụ: Manager)
3. Tìm capability `local/th_userdownloadctl:download`
4. Chọn **Allow** và lưu

**Cách 2 — Gán trực tiếp cho người dùng cụ thể:**

1. Vào **Site administration > Users > Permissions > Assign system roles**
2. Tạo một role mới chỉ có capability `local/th_userdownloadctl:download`
3. Gán role đó cho người dùng cần thiết

### Gỡ cài đặt

1. Vào **Site administration > Plugins > Plugins overview**
2. Tìm plugin **TH User Download Control**, chọn **Uninstall**
3. Xóa thư mục `local/th_userdownloadctl` khỏi server

---

## Lưu ý kỹ thuật

- Plugin không sửa đổi bất kỳ file core Moodle nào.
- Khi plugin bị tắt, toàn bộ hành vi phân quyền trả về mặc định Moodle.
- Kiểm soát được thực hiện ở 2 tầng: ẩn UI và chặn POST, tránh bypass bằng request trực tiếp.
- Site Admin luôn có quyền thông qua cơ chế `has_capability()` mặc định của Moodle.

---

## Tương thích

Đã kiểm tra trên Moodle:
- 4.1 (LTS)
- 4.5

---

## Tác giả

TH Vietnam Solution and Technology Limited
