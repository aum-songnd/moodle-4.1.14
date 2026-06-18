<?php
namespace block_th_exam_schedule\local;

defined('MOODLE_INTERNAL') || die();

class importer {

    public static function read_rows_from_storedfile(\stored_file $file): array {
        global $CFG;

        $filename = $file->get_filename();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // copy_content_to_temp: tham số 2 là prefix, không phải extension.
        // Nên copy xong tự rename để PhpSpreadsheet nhận đúng định dạng.
        $tmp = $file->copy_content_to_temp('block_th_exam_schedule', 'import_');
        $filepath = $tmp . '.' . $ext;
        @rename($tmp, $filepath);

        try {
            if ($ext === 'csv') {
                $raw = self::read_csv_numeric($filepath);
            } else if ($ext === 'xlsx' || $ext === 'ods') {
                require_once($CFG->dirroot . '/lib/phpspreadsheet/vendor/autoload.php');

                $reader = ($ext === 'ods')
                    ? new \PhpOffice\PhpSpreadsheet\Reader\Ods()
                    : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

                $spreadsheet = $reader->load($filepath);
                $raw = $spreadsheet->getActiveSheet()->toArray(null, true, true, false); // numeric keys 0..n
            } else {
                throw new \moodle_exception('Unsupported file type: ' . $ext);
            }

            // Convert raw numeric rows -> associative rows using header row if present.
            return self::to_assoc_rows($raw);

        } finally {
            @unlink($filepath);
        }
    }

    /**
     * Read CSV into numeric rows (0..n).
     */
    private static function read_csv_numeric(string $path): array {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \moodle_exception('Cannot open CSV file.');
        }

        // detect delimiter from first line
        $first = fgets($handle);
        if ($first === false) {
            fclose($handle);
            return [];
        }
        $delimiter = self::detect_csv_delimiter($first);
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!is_array($data)) {
                continue;
            }
            // Skip empty lines.
            if (count(array_filter($data, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }
            // BOM remove
            if (isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/u', '', (string)$data[0]);
            }
            $rows[] = $data;
        }

        fclose($handle);
        return $rows;
    }

    private static function detect_csv_delimiter(string $line): string {
        $candidates = [',', ';', "\t", '|'];
        $best = ',';
        $bestcount = -1;
        foreach ($candidates as $d) {
            $count = substr_count($line, $d);
            if ($count > $bestcount) {
                $bestcount = $count;
                $best = $d;
            }
        }
        return $best;
    }

    /**
     * Convert numeric rows to associative rows using header row if it looks like header.
     * Keys are normalized with normalize_key().
     */
    private static function to_assoc_rows(array $rawrows): array {
        $rawrows = array_values(array_filter($rawrows, fn($r) => is_array($r)));

        // Find first non-empty row as header candidate
        $header = null;
        $startIndex = 0;

        for ($i = 0; $i < count($rawrows); $i++) {
            $line = $rawrows[$i];
            if (count(array_filter($line, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }
            $header = array_map(fn($h) => trim((string)$h), $line);
            // BOM remove
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/u', '', (string)$header[0]);
            }
            $startIndex = $i + 1;
            break;
        }

        if ($header === null) {
            return [];
        }

        // Decide if this is really a header
        $isHeader = self::looks_like_header($header);

        $rows = [];
        for ($i = $isHeader ? $startIndex : ($startIndex - 1); $i < count($rawrows); $i++) {
            $line = $rawrows[$i];
            if (count(array_filter($line, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }

            if (!$isHeader) {
                $rows[] = $line; // numeric
                continue;
            }

            $assoc = [];
            foreach ($header as $idx => $key) {
                $k = self::normalize_key($key);
                if ($k === '') {
                    continue;
                }
                $assoc[$k] = isset($line[$idx]) ? trim((string)$line[$idx]) : '';
            }
            $rows[] = $assoc;
        }

        return $rows;
    }

    private static function looks_like_header(array $cells): bool {
        // Header thường chứa chữ. Nếu toàn số thì coi như không header.
        foreach ($cells as $c) {
            if (preg_match('/[A-Za-zÀ-ỹ]/u', (string)$c)) {
                return true;
            }
        }
        return false;
    }

    public static function normalize_key(string $s): string {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $s = preg_replace('/\s+/u', '', $s);
        // giữ chữ, số và _
        $s = preg_replace('/[^a-z0-9_]/', '', $s);
        return $s;
    }
    

    private static function get_cell(array $row, array $candidates): ?string {
        // associative keys
        foreach ($candidates as $key) {
            if (is_string($key) && array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return trim((string)$row[$key]);
            }
        }
        // numeric fallback
        foreach ($candidates as $key) {
            if (is_int($key) && isset($row[$key]) && $row[$key] !== '') {
                return trim((string)$row[$key]);
            }
        }
        return null;
    }

    public static function parse_exam_date(string $value): int {
        $value = trim($value);

        // numeric: unix timestamp seconds or excel serial
        if (is_numeric($value)) {
            $num = (float)$value;
            if ($num > 2000000000) {
                return (int)$num;
            }
            // Excel serial -> unix
            return (int)round(($num - 25569) * 86400);
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt instanceof \DateTime) {
                $dt->setTime(0, 0, 0);
                return $dt->getTimestamp();
            }
        }

        $ts = strtotime($value);
        if ($ts !== false) {
            return strtotime('today', $ts);
        }

        return strtotime('today');
    }

    public static function import_courses(array $rows): int {
        global $DB;

        $count = 0;
        $now = time();

        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) {
                continue;
            }

            $coursecode = self::get_cell($row, ['ma_mon_hoc', 'coursecode', 'code', 'mamon', 'ma_mon', 0]);
            $coursename = self::get_cell($row, ['ten_mon_hoc', 'coursename', 'name', 'tenmon', 'ten_mon', 1]);
            
            // Skip header-like rows that slipped through (safety)
            if ($coursecode && $coursename) {
                $ck = self::normalize_key($coursecode);
                $nk = self::normalize_key($coursename);
                if (in_array($ck, ['coursecode', 'mamon', 'ma_mon', 'code'], true) ||
                    in_array($nk, ['coursename', 'tenmon', 'ten_mon', 'name'], true)) {
                    continue;
                }
            }

            if (!$coursecode || !$coursename) {
                continue;
            }

            $coursecode = trim($coursecode);
            $coursename = trim($coursename);

            $existing = $DB->get_record('block_thexamschedule_course', ['coursecode' => $coursecode]);
            if ($existing) {
                $existing->coursename = $coursename;
                $existing->timemodified = $now;
                $DB->update_record('block_thexamschedule_course', $existing);
            } else {
                $rec = (object)[
                    'coursecode' => $coursecode,
                    'coursename' => $coursename,
                    'timemodified' => $now,
                ];
                $DB->insert_record('block_thexamschedule_course', $rec);
            }

            $count++;
        }

        return $count;
    }

    public static function import_exams(array $rows): int {
        global $DB;

        $count = 0;
        $now = time();

        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) {
                continue;
            }

            $studentcode = self::get_cell($row, ['ma_hoc_vien', 'studentcode', 'mahocvien', 'ma_hocvien', 'mssv', 0]);
            $coursecode  = self::get_cell($row, ['ma_mon_hoc', 'coursecode', 'mamon', 'ma_mon', 1]);
            $examdateRaw = self::get_cell($row, ['ngay_thi', 'examdate', 'ngaythi', 'date', 2]);
            $examslot    = self::get_cell($row, ['ca_thi', 'examslot', 'cathi', 'slot', 3]);            

            // Skip header-like rows (safety)
            if ($studentcode) {
                $sk = self::normalize_key($studentcode);
                if (in_array($sk, ['studentcode', 'mahocvien', 'ma_hocvien', 'mssv'], true)) {
                    continue;
                }
            }

            if (!$studentcode || !$coursecode || !$examdateRaw || !$examslot) {
                continue;
            }

            $examdate = self::parse_exam_date($examdateRaw);

            $userid = 0;

            $sql = "
                SELECT uid.userid
                FROM {user_info_data} uid
                JOIN {user_info_field} uif ON uif.id = uid.fieldid
                WHERE uif.shortname = :shortname
                AND uid.data = :data
            ";
            $shortname = get_config('block_th_exam_schedule', 'student_code');
            $userid = (int)$DB->get_field_sql($sql, [
                'shortname' => $shortname,
                'data' => $studentcode,
            ]);
            if ($userid) {
                $userid = (int)$userid;
            }
            // Upsert by (studentcode, coursecode) ONLY
            $existing = $DB->get_record('block_thexamschedule_exam', [
                'studentcode' => $studentcode,
                'coursecode'  => $coursecode,
            ]);

            if ($existing) {
                $existing->examdate = $examdate;
                $existing->examslot = $examslot;
                $existing->timemodified = $now;
                $DB->update_record('block_thexamschedule_exam', $existing);
            } else {
                $rec = (object)[
                    'studentcode' => $studentcode,
                    'coursecode' => $coursecode,
                    'examdate' => $examdate,
                    'examslot' => $examslot,
                    'timemodified' => $now,
                ];
                $DB->insert_record('block_thexamschedule_exam', $rec);
            }
                

            $count++;
        }

        return $count;
    }

    public static function preview_courses(array $rows): array {
        global $DB;
    
        $items = [];
        $seen = []; // coursecode đã xuất hiện trong file
    
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) continue;
    
            $coursecode = self::get_cell($row, ['ma_mon_hoc', 'coursecode', 'code', 'mamon', 'ma_mon', 0]);
            $coursename = self::get_cell($row, ['ten_mon_hoc', 'coursename', 'name', 'tenmon', 'ten_mon', 1]);
    
            // header-like safety
            if ($coursecode) {
                $ck = self::normalize_key((string)$coursecode);
                if (in_array($ck, ['ma_mon_hoc','coursecode','code','mamon','ma_mon'], true)) {
                    continue;
                }
            }
    
            if (!$coursecode || !$coursename) {
                $items[] = ['status' => 'INVALID', 'statuslabel' => 'Dòng lỗi'];
                continue;
            }
    
            $coursecode = trim((string)$coursecode);
            $coursename = trim((string)$coursename);
    
            // Check trùng trong file
            $key = mb_strtoupper($coursecode, 'UTF-8');
            if (isset($seen[$key])) {
                $items[] = [
                    'status' => 'SAME', // apply sẽ bỏ qua
                    'statuslabel' => 'Mã môn bị trùng',
                    'coursecode' => $coursecode,
                    'coursename' => $coursename,
                ];
                continue;
            }
            $seen[$key] = true;
    
            // Check DB
            $existing = $DB->get_record('block_thexamschedule_course', ['coursecode' => $coursecode], 'id,coursename');
    
            if (!$existing) {
                $items[] = [
                    'status' => 'NEW',
                    'coursecode' => $coursecode,
                    'coursename' => $coursename,
                ];
            } else if (trim((string)$existing->coursename) !== $coursename) {
                $items[] = [
                    'status' => 'UPDATE',
                    'coursecode' => $coursecode,
                    'coursename' => $coursename,
                ];
            } else {
                $items[] = [
                    'status' => 'SAME',
                    'coursecode' => $coursecode,
                    'coursename' => $coursename,
                ];
            }
        }
    
        return $items;
    }
    
    public static function apply_courses_preview(array $items): array {
        global $DB;
        $now = time();
    
        $r = ['new' => 0, 'update' => 0, 'same' => 0, 'invalid' => 0];
    
        foreach ($items as $it) {
            $status = strtoupper((string)($it['status'] ?? ''));
    
            switch ($status) {
                case 'NEW':
                    // Chặn trùng (file trùng hoặc DB vừa phát sinh)
                    if (empty($it['coursecode']) || $DB->record_exists('block_thexamschedule_course', ['coursecode' => $it['coursecode']])) {
                        $r['same']++;
                        break;
                    }
    
                    $DB->insert_record('block_thexamschedule_course', (object)[
                        'coursecode' => $it['coursecode'],
                        'coursename' => $it['coursename'] ?? '',
                        'timemodified' => $now
                    ]);
                    $r['new']++;
                    break;
    
                case 'UPDATE':
                    if (empty($it['coursecode'])) {
                        $r['invalid']++;
                        break;
                    }
    
                    $rec = $DB->get_record(
                        'block_thexamschedule_course',
                        ['coursecode' => $it['coursecode']],
                        '*',
                        MUST_EXIST
                    );
                    $rec->coursename = $it['coursename'] ?? '';
                    $rec->timemodified = $now;
                    $DB->update_record('block_thexamschedule_course', $rec);
                    $r['update']++;
                    break;
    
                case 'SAME':
                    $r['same']++;
                    break;
    
                default:
                    $r['invalid']++;
                    break;
            }
        }
    
        return $r;
    }
    
    public static function preview_exams(array $rows): array {
        global $DB;
    
        $items = [];
        $seen = []; // bắt trùng trong file theo 4 trường
    
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) {
                continue;
            }
    
            $studentcode = self::get_cell($row, ['ma_hoc_vien', 'studentcode', 0]);
            $coursecode  = self::get_cell($row, ['ma_mon_hoc', 'coursecode', 1]);
            $dateRaw     = self::get_cell($row, ['ngay_thi', 'examdate', 2]);
            $examslot    = self::get_cell($row, ['ca_thi', 'examslot', 3]);
    
            if (!$studentcode || !$coursecode || !$dateRaw || !$examslot) {
                $items[] = ['status' => 'INVALID', 'statuslabel' => 'Dòng lỗi'];
                continue;
            }
    
            $studentcode = trim((string)$studentcode);
            $coursecode  = trim((string)$coursecode);
            $examslot    = trim((string)$examslot);
            $examdate    = self::parse_exam_date($dateRaw);
    
            // ===== Validate tồn tại mã môn / mã học viên =====
            $reasons = [];
    
            if (!$DB->record_exists('block_thexamschedule_course', ['coursecode' => $coursecode])) {
                $reasons[] = 'Không tìm thấy mã môn';
            }
    
            $shortname = trim((string)get_config('block_th_exam_schedule', 'student_code'));
            if ($shortname !== '') {
                $founduserid = $DB->get_field_sql("
                    SELECT uid.userid
                      FROM {user_info_data} uid
                      JOIN {user_info_field} uif ON uif.id = uid.fieldid
                     WHERE uif.shortname = :shortname
                       AND uid.data = :data
                ", [
                    'shortname' => $shortname,
                    'data' => $studentcode,
                ]);
    
                if (empty($founduserid)) {
                    $reasons[] = 'Không tìm thấy mã học viên';
                }
            }
    
            if (!empty($reasons)) {
                $items[] = [
                    'status' => 'INVALID',
                    'statuslabel' => implode('; ', $reasons),
                    'studentcode' => $studentcode,
                    'coursecode' => $coursecode,
                    'examdate' => $examdate,
                    'examslot' => $examslot,
                ];
                continue;
            }
    
            // ===== Trùng trong file theo 4 trường =====
            $filekey = mb_strtoupper($studentcode . '|' . $coursecode . '|' . (int)$examdate . '|' . $examslot, 'UTF-8');
            if (isset($seen[$filekey])) {
                $items[] = [
                    'status' => 'SAME',
                    'statuslabel' => 'Trùng trong file (bỏ qua)',
                    'studentcode' => $studentcode,
                    'coursecode' => $coursecode,
                    'examdate' => $examdate,
                    'examslot' => $examslot,
                ];
                continue;
            }
            $seen[$filekey] = true;
    
            // ===== Trùng trong DB theo 4 trường =====
            $exists = $DB->record_exists('block_thexamschedule_exam', [
                'studentcode' => $studentcode,
                'coursecode'  => $coursecode,
                'examdate'    => (int)$examdate,
                'examslot'    => $examslot,
            ]);
    
            if ($exists) {
                $items[] = [
                    'status' => 'SAME',
                    'statuslabel' => 'Trùng (bỏ qua)',
                    'studentcode' => $studentcode,
                    'coursecode' => $coursecode,
                    'examdate' => $examdate,
                    'examslot' => $examslot,
                ];
            } else {
                $items[] = [
                    'status' => 'NEW',
                    'studentcode' => $studentcode,
                    'coursecode' => $coursecode,
                    'examdate' => $examdate,
                    'examslot' => $examslot,
                ];
            }
        }
    
        return $items;
    }
    
    public static function apply_exams_preview(array $items): array {
        global $DB;
        $now = time();
    
        $r = ['new' => 0, 'update' => 0, 'same' => 0, 'invalid' => 0];
    
        foreach ($items as $it) {
            $st = strtoupper((string)($it['status'] ?? ''));
    
            if ($st === 'SAME') {
                $r['same']++;
                continue;
            }
    
            if ($st !== 'NEW') {
                $r['invalid']++;
                continue;
            }
    
            $studentcode = trim((string)($it['studentcode'] ?? ''));
            $coursecode  = trim((string)($it['coursecode'] ?? ''));
            $examdate    = (int)($it['examdate'] ?? 0);
            $examslot    = trim((string)($it['examslot'] ?? ''));
    
            if ($studentcode === '' || $coursecode === '' || $examdate <= 0 || $examslot === '') {
                $r['invalid']++;
                continue;
            }
    
            // Lưới an toàn: nếu DB đã có rồi thì bỏ qua để khỏi duplicate
            if ($DB->record_exists('block_thexamschedule_exam', [
                'studentcode' => $studentcode,
                'coursecode'  => $coursecode,
                'examdate'    => $examdate,
                'examslot'    => $examslot,
            ])) {
                $r['same']++;
                continue;
            }
    
            $DB->insert_record('block_thexamschedule_exam', (object)[
                'studentcode'  => $studentcode,
                'coursecode'   => $coursecode,
                'examdate'     => $examdate,
                'examslot'     => $examslot,
                'timemodified' => $now,
            ]);
    
            $r['new']++;
        }
    
        // Không còn UPDATE trong mô hình thi lại (thi lại = thêm mới)
        $r['update'] = 0;
    
        return $r;
    }
    

    public static function preview_delete_courses(array $rows): array {
        global $DB;
    
        $items = [];
        $seen = [];
    
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) continue;
    
            $coursecode = self::get_cell($row, ['ma_mon_hoc', 'coursecode', 'mamon', 'ma_mon', 0]);
            if (!$coursecode) {
                $items[] = ['status' => 'INVALID', 'statuslabel' => 'Dòng lỗi'];
                continue;
            }
            $coursecode = trim((string)$coursecode);
            $key = mb_strtoupper($coursecode, 'UTF-8');
    
            // trùng trong file
            if (isset($seen[$key])) {
                $items[] = [
                    'status' => 'SAME',
                    'statuslabel' => 'Trùng trong file (bỏ qua)',
                    'coursecode' => $coursecode,
                ];
                continue;
            }
            $seen[$key] = true;
    
            // tồn tại trong DB?
            $exists = $DB->record_exists('block_thexamschedule_course', ['coursecode' => $coursecode]);
    
            if (!$exists) {
                $items[] = [
                    'status' => 'INVALID',
                    'statuslabel' => 'Không tìm thấy mã môn',
                    'coursecode' => $coursecode,
                ];
            } else {
                $items[] = [
                    'status' => 'DELETE',
                    'statuslabel' => 'Xóa',
                    'coursecode' => $coursecode,
                ];
            }
        }
    
        return $items;
    }
    
    public static function apply_delete_courses_preview(array $items): array {
        global $DB;
    
        $r = ['delete' => 0, 'same' => 0, 'invalid' => 0];
    
        foreach ($items as $it) {
            $status = strtoupper((string)($it['status'] ?? ''));
            $coursecode = trim((string)($it['coursecode'] ?? ''));
    
            if ($status === 'SAME') { $r['same']++; continue; }
            if ($status !== 'DELETE' || $coursecode === '') { $r['invalid']++; continue; }
    
            // Xóa môn học
            $DB->delete_records('block_thexamschedule_course', ['coursecode' => $coursecode]);
    
            // (Khuyên dùng) Xóa luôn lịch thi liên quan để tránh dữ liệu mồ côi
            $DB->delete_records('block_thexamschedule_exam', ['coursecode' => $coursecode]);
    
            $r['delete']++;
        }
    
        return $r;
    }
    
    public static function preview_delete_exams(array $rows): array {
        global $DB;
    
        $items = [];
        $seen = [];
    
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) continue;
    
            $studentcode = self::get_cell($row, ['ma_hoc_vien', 'studentcode', 'mahocvien', 'mssv', 0]);
            $coursecode  = self::get_cell($row, ['ma_mon_hoc', 'coursecode', 'mamon', 'ma_mon', 1]);
            $examdateRaw = self::get_cell($row, ['ngay_thi', 'examdate', 'ngaythi', 2]);
            $examslot    = self::get_cell($row, ['ca_thi', 'examslot', 'cathi', 3]);
    
            if (!$studentcode || !$coursecode || !$examdateRaw || !$examslot) {
                $items[] = [
                    'status' => 'INVALID',
                    'statuslabel' => 'Dòng lỗi',
                ];
                continue;
            }
    
            $examdate = self::parse_exam_date($examdateRaw);
    
            // bắt trùng trong file theo 4 trường
            $key = mb_strtoupper($studentcode.'|'.$coursecode.'|'.$examdate.'|'.$examslot, 'UTF-8');
            if (isset($seen[$key])) {
                $items[] = [
                    'status' => 'SAME',
                    'statuslabel' => 'Trùng trong file (bỏ qua)',
                    'studentcode' => $studentcode,
                    'coursecode'  => $coursecode,
                    'examdate'    => $examdate,
                    'examslot'    => $examslot,
                ];
                continue;
            }
            $seen[$key] = true;
    
            // tồn tại trong DB?
            $exists = $DB->record_exists('block_thexamschedule_exam', [
                'studentcode' => $studentcode,
                'coursecode'  => $coursecode,
                'examdate'    => $examdate,
                'examslot'    => $examslot,
            ]);
    
            if (!$exists) {
                $items[] = [
                    'status' => 'INVALID',
                    'statuslabel' => 'Không tìm thấy lịch thi',
                    'studentcode' => $studentcode,
                    'coursecode'  => $coursecode,
                    'examdate'    => $examdate,
                    'examslot'    => $examslot,
                ];
            } else {
                $items[] = [
                    'status' => 'DELETE',
                    'statuslabel' => 'Sẽ xóa',
                    'studentcode' => $studentcode,
                    'coursecode'  => $coursecode,
                    'examdate'    => $examdate,
                    'examslot'    => $examslot,
                ];
            }
        }
    
        return $items;
    }
    
    public static function apply_delete_exams_preview(array $items): array {
        global $DB;
    
        $r = ['delete' => 0, 'same' => 0, 'invalid' => 0];
    
        foreach ($items as $it) {
            $status = strtoupper((string)($it['status'] ?? ''));
    
            if ($status === 'SAME') {
                $r['same']++;
                continue;
            }
    
            if ($status !== 'DELETE') {
                $r['invalid']++;
                continue;
            }
    
            $studentcode = trim((string)($it['studentcode'] ?? ''));
            $coursecode  = trim((string)($it['coursecode'] ?? ''));
            $examdate    = (int)($it['examdate'] ?? 0);
            $examslot    = trim((string)($it['examslot'] ?? ''));
    
            if ($studentcode === '' || $coursecode === '' || $examdate <= 0 || $examslot === '') {
                $r['invalid']++;
                continue;
            }
    
            $DB->delete_records('block_thexamschedule_exam', [
                'studentcode' => $studentcode,
                'coursecode'  => $coursecode,
                'examdate'    => $examdate,
                'examslot'    => $examslot,
            ]);
    
            $r['delete']++;
        }
    
        return $r;
    }
    
    
}
