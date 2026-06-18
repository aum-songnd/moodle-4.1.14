# Tài liệu Chức năng Tìm kiếm các khóa học theo fullname hoặc shortname

1. **Tên plugin:** th_search_courses
2. **Kiểu plugin:** local
3. **Project:** TNU,AOF,TNUT
4. **Chức năng chung:** Tìm kiếm các khóa học theo fullname hoặc shortname
5. **Người phát triển:** datdt720@wru.vn
6. **Người yêu cầu:** minhpl@aum.edu.vn
7. **Tham chiếu ERP:** TASK-107
8. **Mã nguồn:** https://github.com/thsambala/th/tree/master/local/th_search_courses
# 1. Yêu cầu:
- Tìm kiếm các khóa học theo fullname hoặc shortname

# 2. Mô tả chi tiết/ hướng dẫn sử dụng/ hướng dẫn cài đặt
1. Các capability cần để truy cập và sử dụng tính năng. Đoạn code định nghĩa capabilities
- Các capability cần để truy cập và sử dụng tính năng: không có
2. Hướng dẫn sử dụng
- Truy cập link: https://tnu.aum.edu.vn/local/th_search_courses/view.php hoặc TH Dashboard > Tìm khóa học

  ![thdashboarch](https://user-images.githubusercontent.com/66956549/208218597-bee75ba4-0572-4242-905d-45fc2b715f82.png)
- Nhập khóa học cần tìm kiếm
![form](https://user-images.githubusercontent.com/66956549/208218514-2ea1e88b-be3e-414a-a128-9fe5a2ecd59c.png)
- Chọn Tìm kiếm

# 3. Phân tích thiết kế (database, functions nếu cần)

## Database:

1. Các bảng cần dùng và các câu truy vấn
- Bảng cần dùng: Bảng course
- Các câu truy vấn đã dùng:
```sql
$sql = "SELECT * FROM {course}
    WHERE (LOWER(fullname) LIKE BINARY LOWER('%$searchterms%')
    OR shortname LIKE '%$searchterms%') AND id <> 1 ORDER BY startdate DESC";
```
Lấy các khóa học theo yêu cầu.

1. Thêm Bảng: Không
2. Database Diagram: Không

## Method
1. Các Method cần chú ý: th_search_courses_get_courses_search_fullname (Tìm kiếm các khóa học theo fullname hoặc shortname)
2. Chi tiết các Method:
- th_search_courses_get_courses_search_fullname: Tìm kiếm các khóa học theo fullname hoặc shortname
```php
/**
 * A list of courses that match a search
 *
 * @global object
 * @global object
 * @param array $searchterms An array of search criteria
 * @param string $sort A field and direction to sort by
 * @param int $page The page number to get
 * @param int $recordsperpage The number of records per page
 * @param int $totalcount Passed in by reference.
 * @param array $requiredcapabilities Extra list of capabilities used to filter courses
 * @param array $searchcond additional search conditions, for example ['c.enablecompletion = :p1']
 * @param array $params named parameters for additional search conditions, for example ['p1' => 1]
 * @return stdClass[] {@link $COURSE} records
 */
function th_search_courses_get_courses_search_fullname($searchterms, $sort, $page, $recordsperpage, &$totalcount,
    $requiredcapabilities = array(), $searchcond = [], $params = [])
```

# 4. mã nguồn (nếu cần hướng dẫn viết mã nguồn chi tiết, những thay đổi mã nguồn cần để viết tính năng này)

https://github.com/thsambala/th/tree/master/local/th_search_courses

# 5. Triển khai (nếu cần)

# 6. Kiểm thử (nếu cần)

