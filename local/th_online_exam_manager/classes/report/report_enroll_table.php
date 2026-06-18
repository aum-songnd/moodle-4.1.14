<?php
namespace local_th_online_exam_manager\report;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/tablelib.php');
require_once($GLOBALS['CFG']->dirroot . '/user/lib.php');
use html_writer;
use moodle_url;

class report_enroll_table extends \table_sql {

    private $courseid;
    private $quizids;
    private $examdate;
    private $enrolmap;
    private $useridlist;

    public function __construct($uniqueid, $userids, $courseid, $examdate, $quizids, $enrolmap = []) {
        parent::__construct($uniqueid);
        $this->attributes['id'] = $uniqueid;
        $this->define_columns([
            'stt', 'firstname', 'classcode', 'username', 'course', 'coursecode', 'monitorroom', 'examdate',
            'quizname', 'status', 'timeaccess', 'attemptstatus', 'note'
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
            get_string('quizname', 'local_th_online_exam_manager'),
            get_string('statusquiz', 'local_th_online_exam_manager'),
            get_string('timeaccess', 'local_th_online_exam_manager'),
            get_string('attemptstatus', 'local_th_online_exam_manager'),
            get_string('note', 'local_th_online_exam_manager'),
        ]);

        $this->sortable(false);
        $this->collapsible(false);
        $this->pageable(false);
        $this->enrolmap = $enrolmap;

        $this->useridlist = $userids;
        $this->courseid = $courseid;
        $this->examdate = $examdate;
        $this->quizids = $quizids;
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $users = $this->load_users();
        $quizzes = $this->load_quizzes();
        $courses = $this->load_courses($quizzes);
        $attempts = $this->load_attempts();
        $courseaccesses = $this->load_course_accesses($quizzes);
        $statuses = $this->load_action_statuses();

        $this->rawdata = [];

        foreach ($users as $userid => $user) {
            foreach ($this->quizids as $quizid) {
                $record = clone $user;
                $record->quizid = $quizid;
                $record->uniqueid = $userid . '_' . $quizid;
                $record->userid = $userid;
                $record->quizname = $quizzes[$quizid]->name ?? '-';
                $record->timeopen = $quizzes[$quizid]->timeopen;
                $courseid = $quizzes[$quizid]->course ?? 0;
                $record->courseid = $courseid;
                if (empty($this->enrolmap[$userid][$courseid])) {
                    continue;
                }

                $record->courseshortname = $courses[$courseid]->shortname ?? '-';
                $record->coursefullname = $courses[$courseid]->fullname ?? '-';

                if (isset($attempts[$userid][$quizid])) {
                    $record->attempttimemodified = $attempts[$userid][$quizid]->timemodified;
                    $record->state = $attempts[$userid][$quizid]->state;
                } else {
                    $record->attempttimemodified = null;
                    $record->state = null;
                }
                $record->courseaccessed = !empty($courseaccesses[$userid][$quizid]);
                $record->actionstatus = $statuses[$userid][$courseid] ?? null;


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

        list($insql, $params) = $DB->get_in_or_equal($this->useridlist, SQL_PARAMS_NAMED);

        return $DB->get_records_sql("
            SELECT 
                u.id AS uniqueid,
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

    private function load_quizzes(): array {
        global $DB;
        return $DB->get_records_list('quiz', 'id', $this->quizids);
    }

    private function load_courses(array $quizzes): array {
        global $DB;

        $courseids = array_unique(array_map(function($q) {
            return $q->course;
        }, $quizzes));

        if (empty($courseids)) {
            return [];
        }

        return $DB->get_records_list('course', 'id', $courseids);
    }

    private function load_attempts(): array {
        global $DB;

        if (empty($this->quizids)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($this->quizids, SQL_PARAMS_NAMED);

        $attempts = $DB->get_records_sql("
            SELECT id, userid, quiz, timemodified, state
            FROM {quiz_attempts}
            WHERE quiz $insql
        ", $params);

        $attemptmap = [];
        foreach ($attempts as $attempt) {
            $attemptmap[$attempt->userid][$attempt->quiz] = $attempt;
        }
        return $attemptmap;
    }

    private function load_action_statuses(): array {
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

    private function load_course_accesses(array $quizzes): array {
        global $DB;
    
        if (empty($this->useridlist) || empty($quizzes)) {
            return [];
        }
    
        $accessmap = [];
    
        list($userinsql, $userparams) = $DB->get_in_or_equal(
            $this->useridlist,
            SQL_PARAMS_NAMED,
            'u'
        );
    
        foreach ($quizzes as $quizid => $quiz) {
            $courseid = (int)$quiz->course;
            $timeopen = (int)$quiz->timeopen;
            $timeclose = (int)$quiz->timeclose;
    
            if (empty($courseid) || empty($timeopen)) {
                continue;
            }
    
            $params = $userparams;
            $params['courseid'] = $courseid;
            $params['timeopen'] = $timeopen;
    
            $timeclosesql = '';
            if (!empty($timeclose)) {
                $timeclosesql = ' AND l.timecreated <= :timeclose ';
                $params['timeclose'] = $timeclose;
            }
    
            $sql = "
                SELECT DISTINCT l.userid
                  FROM {logstore_standard_log} l
                 WHERE l.userid $userinsql
                   AND l.courseid = :courseid
                   AND l.timecreated >= :timeopen
                   $timeclosesql
            ";
    
            $records = $DB->get_records_sql($sql, $params);
    
            foreach ($records as $rec) {
                $accessmap[$rec->userid][$quizid] = true;
            }
        }
    
        return $accessmap;
    }

    public function col_stt($row) {
        static $stt = 0;
        return ++$stt;
    }

    public function col_firstname($row) {
        return html_writer::link(
                new moodle_url('/user/profile.php', ['id' => $row->userid]),
                fullname($row)
            );
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

    public function col_course($row) {
        return $row->coursefullname ?? '-';
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

    public function col_coursecode($row) {
        return $row->courseshortname ?? '-';
    }

    public function col_examdate($row) {
        if($row->timeopen){
            return userdate($row->timeopen, '%d/%m/%Y');
        } else {
            return '-';
        }
    }

    public function col_quizname($row) {
        return $row->quizname ?? '-';
    }

    public function col_status($row) {
        if (!empty($row->state)) {
            return get_string('attended', 'local_th_online_exam_manager');
        }
    
        if (!empty($row->courseaccessed)) {
            return get_string('attended', 'local_th_online_exam_manager');
        }
    
        if ((int)$row->actionstatus === 30) {
            return get_string('attended', 'local_th_online_exam_manager');
        }
    
        return get_string('absent', 'local_th_online_exam_manager');
    }

    public function col_timeaccess($row) {
        return !empty($row->attempttimemodified)
            ? userdate($row->attempttimemodified, '%H:%M:%S')
            : '-';
    }

    public function col_attemptstatus($row) {
        if (empty($row->state)) {
            return '-';
        }

        $states = [
            'inprogress' => get_string('inprogress', 'local_th_online_exam_manager'),
            'finished' => get_string('finished', 'local_th_online_exam_manager'),
            'abandoned' => get_string('abandoned', 'local_th_online_exam_manager')
        ];
        return $states[$row->state] ?? get_string('unknown', 'local_th_online_exam_manager');
    }

    public function col_note($row) {
        $map = [
            10 => 'exam_action_warn',
            20 => 'exam_action_alert',
            30 => 'exam_action_suspend',
        ];
    
        return isset($map[$row->actionstatus]) 
            ? get_string($map[$row->actionstatus], 'local_th_online_exam_manager') 
            : '-';
    }
    
}
