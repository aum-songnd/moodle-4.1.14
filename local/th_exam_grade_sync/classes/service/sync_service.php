<?php
namespace local_th_exam_grade_sync\service;

use grade_item;
use context_course;

defined('MOODLE_INTERNAL') || die();

class sync_service {
    private string $categoryidnumber;
    private string $gradeitemidnumber;
    private string $category_nhkt;

    public function __construct() {
        $this->categoryidnumber = get_config('local_th_exam_grade_sync', 'categoryidnumber');
        $this->gradeitemidnumber = get_config('local_th_exam_grade_sync', 'gradeitem');
        $this->category_nhkt = get_config('local_th_exam_grade_sync', 'category_nhkt');
    }

     // LẤY ĐIỂM THI
    private function get_exam_score(int $examcourseid, int $userid): ?float {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
    
        // lấy điểm phúc khảo
        $pkidnumber = get_config('local_th_exam_grade_sync', 'gradeitem_pk');
        if ($pkidnumber) {
            $pkitem = grade_item::fetch([
                'courseid' => $examcourseid,
                'idnumber' => $pkidnumber,
            ]);
    
            if ($pkitem) {
                $pkgrade = $pkitem->get_final($userid);
                if ($pkgrade && $pkgrade->finalgrade !== null) {
                    return (float)$pkgrade->finalgrade;
                }
            }
        }
    
        $item = grade_item::fetch([
            'courseid' => $examcourseid,
            'idnumber' => $this->gradeitemidnumber,
        ]);
    
        if (!$item) {
            return null;
        }
    
        $grade = $item->get_final($userid);
        if (!$grade || $grade->finalgrade === null) {
            return null;
        }
    
        return (float)$grade->finalgrade;
    }
    

     // TÌM KHÓA HỌC HỌC
    private function find_learning_courses_by_code(string $coursecode): array {
        global $DB;
    
        // Lấy thông tin về danh mục thi và danh mục NHKT
        $examcat = $DB->get_record(
            'course_categories',
            ['idnumber' => $this->categoryidnumber],
            'id, path',
            MUST_EXIST
        );
    
        $nhktcat = $DB->get_record(
            'course_categories',
            ['idnumber' => $this->category_nhkt],
            'id, path',
            MUST_EXIST
        );
    
        $sql = "
            SELECT c.id, c.fullname, c.shortname, c.startdate
              FROM {course} c
              JOIN {course_categories} cc ON cc.id = c.category
              JOIN {customfield_data} d ON d.instanceid = c.id
              JOIN {customfield_field} f ON f.id = d.fieldid
             WHERE f.shortname = 'th_course_code'
               AND d.value = :coursecode
               AND cc.path NOT LIKE :examcatpath
               AND cc.path NOT LIKE :nhktcatpath
          ORDER BY c.startdate DESC, c.timecreated DESC
        ";
    
        // Lọc khóa học theo mã khóa học và loại trừ các khóa học thuộc danh mục thi và danh mục NHKT (bao gồm cả danh mục con)
        return $DB->get_records_sql($sql, [
            'coursecode'   => $coursecode,
            'examcatpath'  => $examcat->path . '%',   // Loại trừ danh mục thi và các danh mục con
            'nhktcatpath'  => $nhktcat->path . '%',   // Loại trừ danh mục NHKT và các danh mục con
        ]);
    }

     // TÌM GRADE ITEM ĐÍCH
    private function find_target_grade_item(int $learncourseid): ?grade_item {
        $item = grade_item::fetch([
            'courseid' => $learncourseid,
            'itemtype' => 'manual',
            'idnumber' => $this->gradeitemidnumber,
        ]);

        return $item ?: null;
    }
    
    private function log(
        int $examcourseid,
        int $userid,
        int $sourcecourseid,
        int $learncourseid,
        ?float $score,
        string $status,
        ?string $message
    ): void {
        global $DB;

        $DB->insert_record('local_gradesync_log', (object)[
            'userid'        => $userid,
            'examcourseid'  => $sourcecourseid,
            'learncourseid' => $learncourseid,
            'examscore'     => $score,
            'status'        => $status,
            'message'       => $message,
            'timecreated'   => time(),
        ]);
    }

    public function build_preview_by_examdate(int $examdate): array {
        $examcourses = $this->get_exam_courses_by_examdate($examdate);
        return $this->build_preview_by_examcourses($examcourses);
    }
    
    private function build_preview_by_examcourses(array $examcourses): array {
        global $DB;
        $rows = [];
    
        $examcoursesbycode = [];
        foreach ($examcourses as $examcourse) {
            $coursecode = $this->extract_course_code_from_exam_course($examcourse);
            if ($coursecode) {
                $examcoursesbycode[$coursecode][] = $examcourse;
            }
        }
    
        foreach ($examcoursesbycode as $coursecode => $examcourses) {
    
            // TẤT CẢ khóa học (mới → cũ)
            $learningcourses = $this->find_learning_courses_by_code($coursecode);
            if (!$learningcourses) {
                continue;
            }
    
            // Gom user từ tất cả khóa thi
            $users = [];
            $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);

            foreach ($examcourses as $examcourse) {
                $examcontext = context_course::instance($examcourse->id);
                foreach (get_role_users($studentroleid, $examcontext, false, 'u.*') as $u) {
                    $users[$u->id] = $u;
                }
            }

            foreach ($users as $u) {
                $latestexamcourse = null;
                foreach ($examcourses as $examcourse) {
                    if (is_enrolled(
                        context_course::instance($examcourse->id),
                        $u->id,
                        '',
                        false
                    )) {
                        $latestexamcourse = $examcourse;
                        break;
                    }
                }
    
                if (!$latestexamcourse) {
                    continue;
                }

                $targetlearncourse = null;
                foreach ($learningcourses as $learncourse) {
                    if (is_enrolled(
                        context_course::instance($learncourse->id),
                        $u->id,
                        '',
                        false
                    )) {
                        $targetlearncourse = $learncourse;
                        break;
                    }
                }
    
                if (!$targetlearncourse) {
                    $rows[] = (object)[
                        'userid'      => $u->id,
                        'fullname'    => fullname($u),
                        'examcourse'  => $latestexamcourse,
                        'learncourse' => null,
                        'score'       => null,
                        'status'      => 'ERROR',
                        'message'     => 'Học viên chưa ghi danh khóa học'
                    ];
                    continue;
                }

                $score = $this->get_exam_score($latestexamcourse->id, $u->id);
                if ($score === null) {
                    $rows[] = (object)[
                        'userid'      => $u->id,
                        'fullname'    => fullname($u),
                        'examcourse'  => $latestexamcourse,
                        'learncourse' => $targetlearncourse,
                        'score'       => null,
                        'status'      => 'ERROR',
                        'message'     => 'Không có điểm thi'
                    ];
                    continue;
                }

                if (!$this->find_target_grade_item($targetlearncourse->id)) {
                    $rows[] = (object)[
                        'userid'      => $u->id,
                        'fullname'    => fullname($u),
                        'examcourse'  => $latestexamcourse,
                        'learncourse' => $targetlearncourse,
                        'score'       => $score,
                        'status'      => 'ERROR',
                        'message'     => 'Thiếu mục điểm thi'
                    ];
                    continue;
                }
    
                $rows[] = (object)[
                    'userid'      => $u->id,
                    'fullname'    => fullname($u),
                    'examcourse'  => $latestexamcourse,
                    'learncourse' => $targetlearncourse,
                    'score'       => $score,
                    'status'      => 'OK',
                    'message'     => 'Sẵn sàng đồng bộ'
                ];
            }
        }
    
        return $rows;
    }

    public function build_preview_by_examcourse(int $examcourseid): array {
        global $DB;
    
        $examcourse = $DB->get_record(
            'course',
            ['id' => $examcourseid],
            '*',
            MUST_EXIST
        );
    
        // giả lập 1 "đợt thi" chỉ có 1 phòng
        return $this->build_preview_by_examcourses([$examcourse]);
    }

    // LẤY CÁC KHÓA THI CÙNG ĐỢT
    private function get_exam_courses_by_examdate(int $examdate): array {
        global $DB;
    
        $category = $DB->get_record(
            'course_categories',
            ['idnumber' => $this->categoryidnumber],
            'id, path',
            MUST_EXIST
        );
    
        return $DB->get_records_sql(
            "
            SELECT c.*
              FROM {course} c
              JOIN {course_categories} cc ON cc.id = c.category
             WHERE cc.path LIKE :catpath
               AND c.startdate = :examdate
             ORDER BY c.timecreated DESC
            ",
            [
                'catpath'  => $category->path . '%',
                'examdate' => $examdate
            ]
        );
    }

    // private function get_exam_courses_by_examdate(int $examdate): array {
    //     global $DB;
    
    //     $category = $DB->get_record(
    //         'course_categories',
    //         ['idnumber' => $this->categoryidnumber],
    //         'id, path',
    //         MUST_EXIST
    //     );
    
    //     return $DB->get_records_sql(
    //         "
    //         SELECT c.*
    //           FROM {course} c
    //           JOIN {course_categories} cc ON cc.id = c.category
    //          WHERE cc.path LIKE :catpath
    //            AND c.startdate = :examdate
    //          ORDER BY c.timecreated DESC
    //         ",
    //         [
    //             'catpath'  => $category->path . '%',
    //             'examdate' => $examdate
    //         ]
    //     );
    // }
    
    
    private function extract_course_code_from_exam_course(\stdClass $course): ?string {
        global $DB;
    
        $field = $DB->get_record(
            'customfield_field',
            ['shortname' => 'th_course_code'],
            'id'
        );
    
        if (!$field) {
            return null;
        }
    
        return $DB->get_field('customfield_data', 'value', [
            'fieldid'    => $field->id,
            'instanceid' => $course->id
        ]) ?: null;
    }
    
    public function sync_by_examdate(int $examdate): array {
        $preview = $this->build_preview_by_examdate($examdate);
        $result = ['updated' => 0, 'errors' => 0];
    
        foreach ($preview as $r) {
    
            if ($r->status !== 'OK') {
                $result['errors']++;
                $this->log(
                    0,
                    $r->userid,
                    $r->examcourse->id ?? 0,
                    $r->learncourse->id ?? 0,
                    $r->score,
                    'ERROR',
                    $r->message
                );
                continue;
            }
    
            $targetitem = $this->find_target_grade_item($r->learncourse->id);
            if (!$targetitem) {
                $result['errors']++;
                continue;
            }
    
            $targetitem->update_final_grade(
                $r->userid,
                $r->score,
                'local_th_exam_grade_sync',
                'Đồng bộ điểm thi theo đợt ' . userdate($examdate)
            );
    
            $result['updated']++;
    
            $this->log(
                0,
                $r->userid,
                $r->examcourse->id,
                $r->learncourse->id,
                $r->score,
                'UPDATED',
                'Đồng bộ thành công'
            );
        }
    
        return $result;
    }

    public function sync_by_examcourse(int $examcourseid): array {
        $preview = $this->build_preview_by_examcourse($examcourseid);
        $result = ['updated' => 0, 'errors' => 0];
    
        foreach ($preview as $r) {
    
            if ($r->status !== 'OK') {
                $result['errors']++;
                continue;
            }
    
            $targetitem = $this->find_target_grade_item($r->learncourse->id);
            if (!$targetitem) {
                $result['errors']++;
                continue;
            }
    
            $targetitem->update_final_grade(
                $r->userid,
                $r->score,
                'local_th_exam_grade_sync',
                'Đồng bộ điểm thi theo phòng thi'
            );
    
            $result['updated']++;
    
            $this->log(
                0,
                $r->userid,
                $r->examcourse->id,
                $r->learncourse->id,
                $r->score,
                'UPDATED',
                'Đồng bộ thành công'
            );
        }
    
        return $result;
    }
    
}
