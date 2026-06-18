<?php
namespace local_th_online_exam_manager\report;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/tablelib.php');
require_once($GLOBALS['CFG']->dirroot . '/user/lib.php');
use html_writer;
use moodle_url;

class report_result_table extends \table_sql {

    private $courseids;
    private $useridlist;
    private $examdate;
    private $enrolmap;
    public function __construct($uniqueid, $userids, $courseids, $examdate, $quizids = [], $enrolmap = []) {
        parent::__construct($uniqueid);
        $this->attributes['id'] = $uniqueid;
        $this->define_columns([
            'stt', 'firstname', 'classcode', 'username', 'course', 'coursecode', 'monitorroom', 'examdate', 'point', 'regradepoint', 'note'
        ]);

        $this->define_headers([
            get_string('stt', 'local_th_online_exam_manager'),
            get_string('firstname', 'local_th_online_exam_manager'),
            get_string('classcode', 'local_th_online_exam_manager'),
            get_string('username', 'local_th_online_exam_manager'),
            get_string('course', 'local_th_online_exam_manager'),
            get_string('coursecode', 'local_th_online_exam_manager'),
            get_string('monitorroom', 'local_th_online_exam_manager'),
            get_string('examdate', 'local_th_online_exam_manager'),
            get_string('point', 'local_th_online_exam_manager'),
            get_string('regradepoint', 'local_th_online_exam_manager'),
            get_string('note', 'local_th_online_exam_manager')

        ]);

        $this->sortable(false);
        $this->collapsible(false);
        $this->pageable(false);
        $this->enrolmap = $enrolmap;

        $this->useridlist = $userids;
        $this->courseids = $courseids;
        $this->examdate = $examdate;
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $users = $this->load_users();
        $courses = $this->load_courses();
        $grades = $this->load_course_and_regrade_totals();

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
                $record->username = $user->username ?? '-';
                $record->coursefullname = $course->fullname ?? '-';
                $record->courseshortname = $course->shortname ?? '-';
                $record->examdate = $this->examdate;
                $record->timeopen = $course->startdate;
        
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
        // Sắp xếp theo coursefullname (A → Z)
        usort($this->rawdata, function($a, $b) {
            return strcmp($a->coursefullname, $b->coursefullname);
        });
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
                u.username
            FROM {user} u
            WHERE u.id $insql
        ", $params);
    }
    
    private function load_courses(): array {
        global $DB;

        if (empty($this->courseids)) return [];

        return $DB->get_records_list('course', 'id', $this->courseids);
    }

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
                gf.finalgrade AS finalgrade,
                pk.finalgrade AS regrade
            FROM (
                SELECT 
                    gg.userid, 
                    gi.courseid, 
                    gg.finalgrade
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.itemtype = 'course'
                AND gg.userid $userinsql
                AND gi.courseid $courseinsql
            ) gf
            LEFT JOIN (
                SELECT 
                    gg.userid, 
                    gi.courseid, 
                    gg.finalgrade
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.idnumber = :regradeidnumber
            ) pk ON gf.userid = pk.userid AND gf.courseid = pk.courseid
        ";
    
        $params = array_merge($userparams, $courseparams, ['regradeidnumber' => $regradeidnumber]);

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
    
    public function col_stt($row) {
        static $stt = 0;
        return ++$stt;
    }

    public function col_firstname($row) {
        $firstname = $row->firstname ?? '-';
        if ($firstname !== '-' && !empty($row->userid)) {
            return html_writer::link(
                new moodle_url('/user/profile.php', ['id' => $row->userid]),
                format_string($firstname)
            );
        }

        return '-';
    }

    public function col_classcode($row) {
        global $DB;
        $setting_classcode = get_config('local_thlib', 'classcodeshortname');
		if (!empty($setting_classcode)) {
			$arr_classcode = explode(',', $setting_classcode);
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

    public function col_course($row) {
        return $row->coursefullname ?? '-';
    }

    public function col_coursecode($row) {
        return $row->courseshortname ?? '-';
    }

    public function col_monitorroom($row) {
        global $DB;

        $usergroups = groups_get_user_groups($row->courseid, $row->userid);

        if (!empty($usergroups[0])) {
            list($insql, $params) = $DB->get_in_or_equal($usergroups[0], SQL_PARAMS_NAMED);

            $groups = $DB->get_records_select('groups', "id $insql", $params, '', 'name');

            $names = array_map(function($g) {
                return $g->name;
            }, $groups);

            return implode(', ', $names);
        }

        return '-';
    }

    public function col_examdate($row) {
        return userdate($row->timeopen, '%d/%m/%Y');
    }

    public function col_username($row) {
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

    public function col_point($row) {
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

    public function col_regradepoint($row) {
        return is_numeric($row->regrade) ? round($row->regrade, 2) : '-';
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
}