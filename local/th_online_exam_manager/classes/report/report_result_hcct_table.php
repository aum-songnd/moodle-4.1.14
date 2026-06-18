<?php
namespace local_th_online_exam_manager\report;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/tablelib.php');
require_once($GLOBALS['CFG']->dirroot . '/user/lib.php');
use html_writer;
use NumberFormatter;
use Collator;

class report_result_hcct_table extends \table_sql {

    private $courseids;
    private $useridlist;
    private $examdate;
    private $enrolmap;

    private int $totalstudents = 0;
    private int $attendedcount = 0;
    private int $absentcount   = 0;
    

    public function __construct($uniqueid, $userids, $courseids, $examdate, $quizids = [], $enrolmap = []) {
        parent::__construct($uniqueid);

        $this->attributes['id'] = $uniqueid;

        // ===== Khai báo cột =====
        $this->define_columns([
            'stt',
            'sbd',
            'studentcode',
            'fullname',
            'birth',
            'classcode',
            'point_number',
            'point_text',
            'regrade_number',
            'regrade_text',
            'note'
        ]);

        $this->column_class['fullname'] = 'col-fullname';

        // Header mặc định (sẽ override)
        $this->define_headers(array_fill(0, 11, ''));

        $this->sortable(false);
        $this->collapsible(false);
        $this->pageable(false);

        $this->useridlist = $userids;
        $this->courseids  = $courseids;
        $this->examdate   = $examdate;
        $this->enrolmap   = $enrolmap;
    }

    /* =========================================================
     * HEADER 2 DÒNG + COLSPAN
     * ========================================================= */
    public function print_headers() {
        echo html_writer::start_tag('thead');

        // ----- Dòng 1 -----
        echo html_writer::start_tag('tr');

        echo html_writer::tag('th', 'TT', ['rowspan' => 2]);
        echo html_writer::tag('th', 'SBD', ['rowspan' => 2]);
        echo html_writer::tag('th', 'Mã học viên', ['rowspan' => 2]);
        echo html_writer::tag('th', 'Họ và tên', ['rowspan' => 2]);
        echo html_writer::tag('th', 'Ngày sinh', ['rowspan' => 2]);
        echo html_writer::tag('th', 'Mã lớp', ['rowspan' => 2]);

        echo html_writer::tag('th', 'Điểm thi', ['colspan' => 2]);
        echo html_writer::tag('th', 'Điểm thi phúc khảo', ['colspan' => 2]);

        echo html_writer::tag('th', 'Ghi chú', ['rowspan' => 2]);

        echo html_writer::end_tag('tr');

        // ----- Dòng 2 -----
        echo html_writer::start_tag('tr');

        echo html_writer::tag('th', 'Số');
        echo html_writer::tag('th', 'Chữ');
        echo html_writer::tag('th', 'Số');
        echo html_writer::tag('th', 'Chữ');

        echo html_writer::end_tag('tr');

        echo html_writer::end_tag('thead');
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $users = $this->load_users();
        $courses = $this->load_courses();
        $grades = $this->load_course_and_regrade_totals();
        $actionstatuses = $this->load_action_statuses();
        $attempts = $this->load_attempts();

        $this->rawdata = [];

        foreach ($users as $userid => $user) {
            foreach ($courses as $courseid => $course) {
                if (!isset($this->enrolmap[$userid][$courseid])) {
                    continue;
                }

                $record = new \stdClass();
                $record->uniqueid = $userid . '_' . $courseid;
                $record->userid = $userid;
                $record->courseid = $courseid;
                $record->firstname = fullname($user);
                $record->lastname = $user->lastname ?? '';
                $record->firstname_raw = $user->firstname ?? '';
                $record->username = $user->username ?? '-';
                $record->coursefullname = $course->fullname ?? '-';
                $record->courseshortname = $course->shortname ?? '-';
                $record->examdate = $this->examdate;
                $record->timeopen = $course->startdate;
                $record->th_dob = $user->th_dob;
                $record->actionstatus = $actionstatuses[$userid][$courseid] ?? null;
                $record->state = $attempts[$userid][$courseid] ?? null;

                if (isset($grades[$userid][$courseid]['final'])) {
                    $record->grade = round($grades[$userid][$courseid]['final'], 2);
                } else {
                    $record->grade = '-';     
                }
                
                if (isset($grades[$userid][$courseid]['regrade'])) {
                    $record->regrade = round($grades[$userid][$courseid]['regrade'], 2);
                } else {
                    $record->regrade = '-';
                }
                
                $this->rawdata[] = $record;
            }
        }
        // Sắp xếp theo phòng thi, sau đó theo Họ và tên theo thứ tự chữ cái tiếng Việt.
        $collator = new \Collator('vi_VN');

        usort($this->rawdata, function($a, $b) use ($collator) {

            $coursecompare = $collator->compare(
                $a->coursefullname,
                $b->coursefullname
            );

            if ($coursecompare !== 0) {
                return $coursecompare;
            }

            $lastnamecompare = $collator->compare(
                $a->lastname,
                $b->lastname
            );

            if ($lastnamecompare !== 0) {
                return $lastnamecompare;
            }

            return $collator->compare(
                $a->firstname_raw,
                $b->firstname_raw
            );
        });
        // ===============================
        // ĐẾM LẠI KHÔNG BỊ NHÂN ĐÔI
        // ===============================
        $this->totalstudents = 0;
        $this->attendedcount = 0;
        $this->absentcount   = 0;

        $seenusers = [];

        foreach ($this->rawdata as $row) {

            if (isset($seenusers[$row->userid])) {
                continue;
            }
        
            $seenusers[$row->userid] = true;
            $this->totalstudents++;
        
            if (!empty($row->state) || $row->actionstatus == 30) {
                $this->attendedcount++;
            } else {
                $this->absentcount++;
            }
            
        }
        
    }

    public function get_summary_html(): string {
        return '
            <br>
            <div style="font-size:12px;">
                <strong>* Số sinh viên có trong danh sách:</strong> ' . $this->totalstudents . ' sinh viên
                <br>
                <strong>- Trong đó:</strong>
                <br>
                + Số sinh viên dự thi: ' . $this->attendedcount . '
                <br>
                + Số sinh viên vắng thi: ' . $this->absentcount . '
            </div>
        ';
    }
    
    private function load_attempts(): array {
        global $DB;
    
        if (empty($this->useridlist) || empty($this->courseids)) {
            return [];
        }
    
        list($uinsql, $uparams) = $DB->get_in_or_equal($this->useridlist, SQL_PARAMS_NAMED, 'u');
        list($cinsql, $cparams) = $DB->get_in_or_equal($this->courseids, SQL_PARAMS_NAMED, 'c');
    
        $sql = "
            SELECT 
                qa.userid,
                q.course,
                MAX(qa.state) AS state
            FROM {quiz_attempts} qa
            JOIN {quiz} q ON q.id = qa.quiz
            WHERE qa.userid $uinsql
            AND q.course $cinsql
            GROUP BY qa.userid, q.course
        ";
    
        $records = $DB->get_records_sql($sql, array_merge($uparams, $cparams));
    
        $map = [];
        foreach ($records as $r) {
            $map[$r->userid][$r->course] = $r->state;
        }
    
        return $map;
    }
    
    

    private function load_users(): array {
        global $DB;
    
        if (empty($this->useridlist)) {
            return [];
        }
    
        list($insql, $params) = $DB->get_in_or_equal($this->useridlist, SQL_PARAMS_NAMED);
    
        return $DB->get_records_sql("
            SELECT 
                u.id,
                u.firstname,
                u.lastname,
                u.firstnamephonetic,
                u.lastnamephonetic,
                u.middlename,
                u.alternatename,
                u.username,
                dob.data AS th_dob
            FROM {user} u
            LEFT JOIN {user_info_field} f 
                ON f.shortname = :dobfield
            LEFT JOIN {user_info_data} dob 
                ON dob.userid = u.id AND dob.fieldid = f.id
            WHERE u.id $insql
        ", array_merge(
            $params,
            ['dobfield' => 'th_dob']
        ));

    }
    
    private function load_courses(): array {
        global $DB;

        if (empty($this->courseids)) return [];

        return $DB->get_records_list('course', 'id', $this->courseids);
    }

    // private function load_course_and_regrade_totals(): array {
    //     global $DB;
    
    //     if (empty($this->useridlist) || empty($this->courseids)) {
    //         return [];
    //     }

    //     $regradeidnumber = get_config('local_th_online_exam_manager', 'regrade_grade_itemid');
    
    //     list($userinsql, $userparams) = $DB->get_in_or_equal($this->useridlist, SQL_PARAMS_NAMED, 'uid');
    //     list($courseinsql, $courseparams) = $DB->get_in_or_equal($this->courseids, SQL_PARAMS_NAMED, 'cid');
    
    //     $sql = "
    //         SELECT 
    //             CONCAT(gf.userid, '_', gf.courseid) AS uniqueid,
    //             gf.userid,
    //             gf.courseid,
    //             gf.finalgrade AS finalgrade,
    //             pk.finalgrade AS regrade
    //         FROM (
    //             SELECT 
    //                 gg.userid, 
    //                 gi.courseid, 
    //                 gg.finalgrade
    //             FROM {grade_grades} gg
    //             JOIN {grade_items} gi ON gi.id = gg.itemid
    //             WHERE gi.itemtype = 'course'
    //             AND gg.userid $userinsql
    //             AND gi.courseid $courseinsql
    //         ) gf
    //         LEFT JOIN (
    //             SELECT 
    //                 gg.userid, 
    //                 gi.courseid, 
    //                 gg.finalgrade
    //             FROM {grade_grades} gg
    //             JOIN {grade_items} gi ON gi.id = gg.itemid
    //             WHERE gi.idnumber = :regradeidnumber
    //         ) pk ON gf.userid = pk.userid AND gf.courseid = pk.courseid
    //     ";
    
    //     $params = array_merge($userparams, $courseparams, ['regradeidnumber' => $regradeidnumber]);

    //     $records = $DB->get_records_sql($sql, $params);
    
    //     $map = [];
    //     foreach ($records as $record) {
    //         if (!isset($map[$record->userid])) {
    //             $map[$record->userid] = [];
    //         }
    
    //         if (!isset($map[$record->userid][$record->courseid])) {
    //             $map[$record->userid][$record->courseid] = [];
    //         }
    
    //         $map[$record->userid][$record->courseid]['final'] = $record->finalgrade;
    //         $map[$record->userid][$record->courseid]['regrade'] = $record->regrade; 
    //     }
    
    //     return $map;
    // }
    private function load_course_and_regrade_totals(): array {
        global $DB;
    
        if (empty($this->useridlist) || empty($this->courseids)) {
            return [];
        }
    
        $regradeidnumber = get_config('local_th_online_exam_manager', 'regrade_grade_itemid');
    
        list($userinsql, $userparams) = $DB->get_in_or_equal($this->useridlist, SQL_PARAMS_NAMED, 'uid');
        list($courseinsql, $courseparams) = $DB->get_in_or_equal($this->courseids, SQL_PARAMS_NAMED, 'cid');
    
        $sql = "
            SELECT 
                CONCAT(gf.userid, '_', gf.courseid) AS uniqueid,
                gf.userid,
                gf.courseid,
                gf.finalgrade,
                pk.regrade
            FROM (
                SELECT 
                    gg.userid,
                    gi.courseid,
                    MAX(gg.finalgrade) AS finalgrade
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.itemtype = 'course'
                AND gg.userid $userinsql
                AND gi.courseid $courseinsql
                GROUP BY gg.userid, gi.courseid
            ) gf
            LEFT JOIN (
                SELECT 
                    gg.userid,
                    gi.courseid,
                    MAX(gg.finalgrade) AS regrade
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.idnumber = :regradeidnumber
                GROUP BY gg.userid, gi.courseid
            ) pk ON gf.userid = pk.userid AND gf.courseid = pk.courseid
        ";
    
        $params = array_merge($userparams, $courseparams, [
            'regradeidnumber' => $regradeidnumber
        ]);
    
        $records = $DB->get_records_sql($sql, $params);
    
        $map = [];
        foreach ($records as $record) {
            if (!isset($map[$record->userid])) {
                $map[$record->userid] = [];
            }
    
            if (!isset($map[$record->userid][$record->courseid])) {
                $map[$record->userid][$record->courseid] = [];
            }
    
            $map[$record->userid][$record->courseid]['final'] = $record->finalgrade;
            $map[$record->userid][$record->courseid]['regrade'] = $record->regrade;
        }
    
        return $map;
    }

    public function load_action_statuses(): array {
        global $DB;
    
        $sql = "
            SELECT em1.id, em1.userid, em1.courseid, em1.action_status
            FROM {local_th_exam_monitor} em1
            INNER JOIN (
                SELECT userid, courseid, MAX(timecreated) AS maxtime
                FROM {local_th_exam_monitor}
                GROUP BY userid, courseid
            ) em2 ON em1.userid = em2.userid AND em1.courseid = em2.courseid AND em1.timecreated = em2.maxtime
        ";
    
        $records = $DB->get_records_sql($sql);
        $result = [];
    
        foreach ($records as $rec) {
            $result[$rec->userid][$rec->courseid] = (int)$rec->action_status;
        }
    
        return $result;
    }

    private $stt = 0;  // Static counter for "stt"

    // Add this method to reset the counter for each report
    public function reset_stt() {
        $this->stt = 0;
    }
    
    public function col_stt($row) {
        $this->stt++;  // Increment the counter
        return $this->stt;
    }

    public function col_sbd($row) {
        return '';
    }

    public function col_fullname($row) {
        return $row->firstname ?? '-';
    }

    public function col_classcode($row) {
        global $DB;
        $setting_ma_hoc_vien = get_config('local_thlib', 'classcodeshortname');
		if (!empty($setting_ma_hoc_vien)) {
			$arr_classcode = explode(',', $setting_ma_hoc_vien);
			$classcode1 = $arr_classcode[0];
			$user_info_field_data = $DB->get_record_sql("SELECT d.* FROM {user_info_field} as i, {user_info_data} as d, {user} as u 
                                                WHERE (i.shortname = '$classcode1') AND i.id = d.fieldid 
                                                AND u.id = $row->userid AND d.userid = u.id
                                            ");
            if($user_info_field_data && $user_info_field_data->data){
                return $user_info_field_data->data;
            } else {
                return '-';
            }
		} else {
			return '-';
		}
    }

    public function col_birth($row) {
        return $row->th_dob;
    }

    public function col_studentcode($row) {
        global $DB;
        $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
		if (!empty($setting_ma_hoc_vien)) {
			$arr_mahv = explode(',', $setting_ma_hoc_vien);
			$ma_hoc_vien1 = $arr_mahv[0];
			$user_info_field_data = $DB->get_record_sql("SELECT d.* FROM {user_info_field} as i, {user_info_data} as d, {user} as u 
                                                WHERE (i.shortname = '$ma_hoc_vien1') AND i.id = d.fieldid 
                                                AND u.id = $row->userid AND d.userid = u.id
                                            ");
            if($user_info_field_data && $user_info_field_data->data){
                return $user_info_field_data->data;
            } else {
                return '-';
            }
		} else {
			return '-';
		}
    }

    public function col_point_number($row) {
        global $DB;
        $sql = "SELECT *
                FROM {local_th_exam_monitor}
                WHERE userid = $row->userid AND courseid = $row->courseid
                AND action_status BETWEEN 30 AND 34";
        $records = $DB->get_records_sql($sql);
        
        if($records) {
            return 0;
        } else {
            return is_numeric($row->grade) ? round($row->grade, 2) : '-';
        }
    }

    public function col_point_text($row) {
        global $DB;
        $sql = "SELECT *
                FROM {local_th_exam_monitor}
                WHERE userid = $row->userid AND courseid = $row->courseid
                AND action_status BETWEEN 30 AND 34";
        $records = $DB->get_records_sql($sql);
        
        if ($records) {
            $fmt = new NumberFormatter("vi", NumberFormatter::SPELLOUT);
            return $fmt->format(round(0, 2));
        } else {
            $fmt = new NumberFormatter("vi", NumberFormatter::SPELLOUT);
            if (is_numeric($row->grade)) {
                return $fmt->format(round($row->grade, 2));
            } else {
                return '-';
            }
        }
    }

    public function col_regrade_number($row) {
        return is_numeric($row->regrade) ? round($row->regrade, 2) : '-';
    }

    public function col_regrade_text($row) {
        if (is_numeric($row->regrade)) {
            $fmt = new NumberFormatter("vi", NumberFormatter::SPELLOUT);
            return $fmt->format(round($row->regrade, 2));
        } else {
            return '-';
        }
    }

    public function col_note($row) {
        global $DB;
        $notes = [];

        // 1. Lấy bản ghi mới nhất
        $sql = "
            SELECT m.*
            FROM {local_th_exam_monitor} m
            INNER JOIN (
                SELECT userid, courseid, MAX(timecreated) AS maxtime
                FROM {local_th_exam_monitor}
                GROUP BY userid, courseid
            ) t
            ON m.userid = t.userid
            AND m.courseid = t.courseid
            AND m.timecreated = t.maxtime
            WHERE m.userid = :userid
            AND m.courseid = :courseid
        ";

        $params = [
            'userid'   => $row->userid,
            'courseid' => $row->courseid
        ];

        $record = $DB->get_record_sql($sql, $params);

        // 2. Ghi chú theo action_status
        if ($record) {
            if ($record->action_status == 10) {
                $notes[] = get_string('exam_action_warn', 'local_th_online_exam_manager');
            } else if ($record->action_status == 20) {
                $notes[] = get_string('exam_action_alert', 'local_th_online_exam_manager');
            } else if ($record->action_status == 30) {
                $notes[] = get_string('exam_action_suspend', 'local_th_online_exam_manager');
            }
        }

        // 3. Ghi chú theo điểm
        if (is_numeric($row->grade) && is_numeric($row->regrade)) {
            if (round($row->regrade, 2) == round($row->grade, 2)) {
                $notes[] = get_string('unchanged', 'local_th_online_exam_manager');
            } elseif ($row->regrade > $row->grade) {
                $notes[] = get_string('increased', 'local_th_online_exam_manager');
            } else {
                $notes[] = get_string('decreased', 'local_th_online_exam_manager');
            }
        }

        // 4. Trả về, mỗi ghi chú xuống dòng
        return implode('<br>', $notes);
    }  

    public function print_nothing_to_display() {
        echo html_writer::div(
            'KHÔNG CÓ DỮ LIỆU',
            'text-center',
            [
                'style' => '
                    font-size: 14px;
                    font-weight: bold;
                    margin: 15px 0;
                '
            ]
        );
    }
    
    
}