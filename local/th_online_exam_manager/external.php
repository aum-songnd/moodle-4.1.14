<?php

require_once "$CFG->libdir/externallib.php";
require_once "$CFG->dirroot/user/profile/lib.php";

use core_user;

class local_th_online_exam_manager_external extends external_api {
    public static function update_status_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT),
            'status' => new external_value(PARAM_ALPHA),
        ]);
    }

    public static function update_status($id, $status) {
        global $DB;
        self::validate_parameters(self::update_status_parameters(), ['id' => $id, 'status' => $status]);

        $record = $DB->get_record('th_exam_regrade_requests', ['id' => $id], '*', MUST_EXIST);

        if (!in_array($status, ['pending', 'rejected', 'reviewed', 'regraded', 'waitingreview'])) {
            throw new \invalid_parameter_exception('Invalid status value');
        }

        $DB->update_record('th_exam_regrade_requests', [
            'id' => $id,
            'status' => $status,
            'timeprocessed' => ($status == 'reviewed') ? time() : null
        ]);

        return ['result' => 'success'];
    }

    public static function update_status_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_TEXT),
        ]);
    }

    public static function load_quizzes_parameters() {
        return new external_function_parameters([
            'term' => new external_value(PARAM_RAW, 'Search term', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }
    
    public static function load_quizzes($term = '', $courseid) {
        global $DB;
    
        $like = $DB->sql_like('name', ':term', false);
        $params = ['term' => "%$term%", 'courseid' => $courseid];
    
        $sql = "SELECT id, name FROM {quiz}
                WHERE course = :courseid AND $like
                ORDER BY name ASC";
    
        $quizzes = $DB->get_records_sql($sql, $params);
    
        $results = [];
        foreach ($quizzes as $quiz) {
            $results[] = [
                'value' => $quiz->id,
                'label' => $quiz->name,
            ];
        }
    
        return $results;
    }

    public static function load_quizzes_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'value' => new external_value(PARAM_INT),
                'label' => new external_value(PARAM_TEXT),
            ])
        );
    }

    public static function load_attempts_parameters() {
        return new external_function_parameters([
            'term' => new external_value(PARAM_RAW, 'Search keyword', VALUE_DEFAULT, ''),
            'quizid' => new external_value(PARAM_INT, 'Quiz ID'),
        ]);
    }

    public static function load_attempts($term = '', $quizid) {
        global $DB;

        $params = self::validate_parameters(self::load_attempts_parameters(), [
            'term' => $term,
            'quizid' => $quizid,
        ]);

        $like = $DB->sql_like('u.firstname', ':term1', false) . " OR " . $DB->sql_like('u.lastname', ':term2', false) . " OR " . $DB->sql_like('u.username', ':term3', false);
        $sql = "SELECT qa.*,  u.firstname, u.lastname, u.username
                  FROM {quiz_attempts} qa
                INNER JOIN {user} u ON qa.userid = u.id
                    WHERE qa.quiz = :quizid AND qa.state = 'finished' AND qa.preview = 0 AND ($like)
                ORDER BY qa.userid ASC, qa.id ASC";

        $results = $DB->get_records_sql($sql, [
            'quizid' => $quizid,
            'term1' => "%{$params['term']}%",
            'term2' => "%{$params['term']}%",
            'term3' => "%{$params['term']}%",
        ]);

        $data = [];
        $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
        foreach ($results as $r) {
            $user = core_user::get_user($r->userid);
            $customfields = profile_user_record($r->userid);
            $mhv = '';

            if (!empty($setting_ma_hoc_vien)) {
                $arr_mahv = explode(',', $setting_ma_hoc_vien);
                $ma_hoc_vien1 = trim($arr_mahv[0]); // đảm bảo không dính khoảng trắng

                if (!empty($ma_hoc_vien1) && isset($customfields->$ma_hoc_vien1)) {
                    $mhv = $customfields->$ma_hoc_vien1;
                }
            }
            
            $label = fullname($user) . ", $user->username, $mhv" . " – Attempt #{$r->id} – " . userdate($r->timefinish);
            $data[] = [
                'value' => $r->id,
                'label' => $label,
            ];
        }

        return $data;
    }

    public static function load_attempts_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'value' => new external_value(PARAM_INT),
                'label' => new external_value(PARAM_TEXT),
            ])
        );
    }
}
