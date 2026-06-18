<?php

require_once "$CFG->libdir/externallib.php";
require_once "lib.php";
require_once $CFG->dirroot . "/course/externallib.php";

//

class local_th_managelogin_external extends external_api {

    public static function save_userinfo_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'string, client IP address'),
                'uuid' => new external_value(PARAM_RAW, 'string, client IP address'),
                'loginstatus' => new external_value(PARAM_INT, 'int, client IP address'),
            )
        );
    }

    public static function save_userinfo_returns() {

        return new external_single_structure(
            array(
                'isloggedin_valid' => new external_value(PARAM_RAW, 'string, mã môn học', VALUE_OPTIONAL),
                'debug' => new external_value(PARAM_RAW, 'string, mã môn học', VALUE_OPTIONAL),
            ),
            '',
            VALUE_OPTIONAL
        );
    }

    /**
     * [Lưu thông tin của thiết bị đăng nhập hoặc đăng xuất]
     * @param  [int] $userid       [id của tài khoản trong bảng user]
     * @param  [text] $uuid        [định danh thiết bị]
     * @param  [int] $loginstatus  [trạng thái đăng nhập. bằng 0 là đăng xuất, bằng 1 là đăng nhập]
     * @return [stdClass]          [debug: bản ghi trong CSDL, isloggedin_valid: 0 or 1 (0 là đăng xuất, 1 là đăng nhập)]
     */
    public static function save_userinfo($userid, $uuid, $loginstatus) {

        global $DB;

        $manageloginobj = new stdClass();
        $manageloginobj->userid = $userid;
        $manageloginobj->uuid = $uuid;
        $manageloginobj->loginstatus = $loginstatus;
        $manageloginobj->timecreated = time();
        if ($loginstatus == 1) {
            $manageloginobj->timeloggedin = time();
            $manageloginobj->isloggedin_valid = 1;
        } else {
            $manageloginobj->isloggedin_valid = 0;
            $manageloginobj->timeloggedin = 0;
        }

        $sql = "SELECT *
		from {th_managelogin}
		where userid =  $userid and uuid = '$manageloginobj->uuid'";

        $record = $DB->get_record_sql($sql);
        if (!$record) {
            $DB->insert_record('th_managelogin', $manageloginobj);
        } else {

            $manageloginobj->id = $record->id;
            $DB->update_record('th_managelogin', $manageloginobj);
        }

        $configure = get_config('local_th_managelogin');
        $numberofconcurrentlogin = $configure->numberofconcurrentlogin;
        if ($numberofconcurrentlogin < 1) {
            $numberofconcurrentlogin = 1;
        }

        $records = $DB->get_records('th_managelogin', ['userid' => $userid], 'timeloggedin desc', '*');

        if (count($records) > $numberofconcurrentlogin) {
            $values = array_values($records);
            $length = count($values);

            $t = 1;
            for ($i = 0; $i < $length; $i++) {
                $v = $values[$i];
                if ($v->loginstatus == 1 AND $t <= $numberofconcurrentlogin) {
                    $v->isloggedin_valid = 1;
                    $t = $t + 1;
                } else {
                    $v->isloggedin_valid = 0;
                }
                $DB->update_record('th_managelogin', $v);
            }
        }

        $result = new stdClass();
        $result->debug = "debugmessage2: " . json_encode($records);
        if ($loginstatus == 1) {
            $result->isloggedin_valid = 1;
        } else {
            $result->isloggedin_valid = 0;
        }

        return $result;
    }

    public static function checklogin_valid_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'string, client IP address'),
                'uuid' => new external_value(PARAM_RAW, 'string, client IP address'),
                'loginstatus' => new external_value(PARAM_INT, 'int, client IP address'),
            )
        );
    }

    public static function checklogin_valid_returns() {

        return new external_single_structure(
            array(
                'isloggedin_valid' => new external_value(PARAM_RAW, 'string, mã môn học', VALUE_OPTIONAL),
                'debug' => new external_value(PARAM_RAW, 'string, mã môn học', VALUE_OPTIONAL),
            ),
            '',
            VALUE_OPTIONAL
        );
    }
    /**
     * Kiểm tra thiết bị có được đăng nhập vào hệ thống không.
     *
     * @param  [int]  $userid       [id của tài khoản trong bảng user]
     * @param  [text] $uuid         [định danh thiết bị]
     * @param  [int]  $loginstatus  [trạng thái đăng nhập. bằng 0 là đăng xuất, bằng 1 là đăng nhập]
     * @return [stdClass]           [debug: bản ghi trong CSDL, isloggedin_valid: 0 or 1 (0 là buộc logout, 1 là được login)]
     */
    public static function checklogin_valid($userid, $uuid, $loginstatus) {

        global $DB;

        $sql = "SELECT *
		from {th_managelogin}
		where userid =  $userid and uuid = '$uuid'";

        $isloggedin_valid = 1;
        $record = $DB->get_record_sql($sql);
        if ($record) {
            $isloggedin_valid = $record->isloggedin_valid;
        }

        $result = new stdClass();
        $result->debug = "isloggedin_valid: " . json_encode($record);
        $result->isloggedin_valid = $isloggedin_valid;

        return $result;
    }
}
