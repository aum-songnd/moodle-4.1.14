# **Tài liệu phát triển tính năng API chứa thông tin đăng kí dự thi**
- **Tên plugin**: th_aofapi
- **Kiểu plugin**: local
- **Project**:  AOF
- **Chức năng chung**: API chứa thông tin điểm khóa học của AOF
- **Người phát triển**: aum.linhnt@gmail.com
- **Người yêu cầu**: AOF yêu cầu
# 1. Yêu cầu:
AOF yêu cầu tạo API lấy danh sách xét điều kiện dự thi
# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
API lấy danh sách xét đăng ký dự thi. 

## Mẫu API

- Method: POST 
- Endpoint: https://aof.aum.edu.vn
- Resource /webservice/restful/server.php/aofapi_xet_dkdt

- Header:
 Content-Type: application/json
  Authorization: {token}
  Accept: application/json

JSON Payload sample:

        {
               "ma_mon":"PSST113",
               "dot_mo":"1723050000",
               "ma_lop":"",
            }

JSON Response sample:

    {
       "ma_mon": "PSST113",
       "dot_mo": "1723050000",
       "ketqua":[
         {
            "mhv": "20-2-7340301-1686",
            'makhoa'=> ABC,
            'malop' => DEF,
            "cc": 9,
            'kt1'   => 8,
            'kt2'   => 8,
            "kt": 8.5,
            "dkdt": 1
         },
         {
            "mhv": "20-2-7340301-1687",
            'makhoa'=> ABC,
            'malop' => DEF,
            "cc": 9,
            'kt1'   => 8,
            'kt2'   => 8,
            "kt": 8.5,
            "dkdt": 1
         }
      ]
    }

Lưu ý:
- Token: dùng thể xác thực, sẽ được gửi riêng ở thư khác.
- Các trường cc/kt1/kt2/kt/dkdt có thể nhận giá trị -1. Giá trị -1 nghĩa là chưa có điểm (chưa làm bài). Giá trị 0 nghĩa là làm bài rồi nhưng được 0.

**Điều kiện plugin hoạt động:**

ID của các activity trong khóa học đặt theo đúng định dạng:

|                           | id            |
|---------------------------|---------------|
| Điểm chuyên cần           | cc            |
| điểm kiểm tra             | kt            |
| Điểm xét điều kiện dự thi | ddk           |
| kiểm tra 1                | kt.1          |
| kiểm tra 2                | kt.2          |

**Cách cấu hình để lấy token:**

- địa chỉ cấu hình Manage tokens:
https://aof.aum.edu.vn/admin/settings.php?section=webservicetokens
- Capability cần có để sử dụng webservice: webservice/restful:use


# 3. Phân tích thiết kế: database, chú ý về các method, method call flowchart
# 4. Mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết)
# 5. Triển khai
# 6. Kiểm thử 
