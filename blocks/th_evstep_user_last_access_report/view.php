<?php
require '../../config.php';
require_once $CFG->dirroot . '/blocks/th_evstep_user_last_access_report/choose_date_form.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

global $DB, $CFG, $COURSE;

$course = $DB->get_record('course', array('id' => $COURSE->id));
if (!$course) {
    new \moodle_exception('invalidcourse', 'blocks_th_evstep_user_last_access_report', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_evstep_user_last_access_report:view', context_course::instance($COURSE->id));

$baseurl = new moodle_url('/blocks/th_evstep_user_last_access_report/view.php');
$pluginname = get_string('pluginname', 'block_th_evstep_user_last_access_report');

$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);
$PAGE->navbar->add($pluginname, $baseurl);
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo hoạt động lần cuối của người dùng", $lang));

$mform = new choose_date_form();

if ($mform->is_cancelled()) {

    redirect($baseurl);
} else if ($mform->is_submitted() && $data = $mform->get_data()) {
    $th_crm_code = get_config('block_th_evstep_user_last_access_report', 'th_crm_code');
    $th_crm_code_field = 'profile_field_' . $th_crm_code;

    $th_student_class = get_config('block_th_evstep_user_last_access_report', 'th_student_class');
    $th_student_class_field = 'profile_field_' . $th_student_class;

    $th_user_role = get_config('block_th_evstep_user_last_access_report', 'th_user_role');
    $th_user_role_field = 'profile_field_' . $th_user_role;
    $params = [];
    $whereconditions = [];

    if ($data->option_select == 2 && !empty($data->userid)) {
        list($sql_condition, $sql_params) = $DB->get_in_or_equal($data->userid, SQL_PARAMS_NAMED);
        $whereconditions[] = "id $sql_condition";
        $params += $sql_params;
    }

    // Default conditions
    $whereconditions[] = "deleted = 0";
    $whereconditions[] = "(id <> 1 OR firstaccess = 0)";

    // Combine all conditions
    $where_sql = !empty($whereconditions) ? "WHERE " . implode(" AND ", $whereconditions) : "";

    $sql = "SELECT a.id, MAX(lsl.timecreated) as lastactivity
        FROM (
            SELECT id
            FROM {user}
            $where_sql
            EXCEPT
            SELECT DISTINCT u.id
            FROM {user} u
            JOIN {logstore_standard_log} lsl ON lsl.userid = u.id
            WHERE lsl.timecreated >= :startdate 
                AND u.deleted = 0 
                AND lsl.realuserid IS NULL 
                AND lsl.action <> 'loggedout'
        ) a
        LEFT JOIN {logstore_standard_log} lsl 
            ON lsl.userid = a.id 
            AND lsl.realuserid IS NULL 
            AND lsl.action <> 'loggedout'
        GROUP BY a.id
        HAVING a.id <> 1";

    $params['startdate'] = $data->startdate;
    $logs = $DB->get_records_sql($sql, $params);

    if (!empty($logs)) {
        // Get all user informations 
        $user_ids = array_column($logs, 'id');
        if (!empty($user_ids)) {
            list($sql_condition, $sql_params) = $DB->get_in_or_equal($user_ids, SQL_PARAMS_NAMED);
            $users = $DB->get_records_sql("SELECT * FROM {user} WHERE id $sql_condition AND deleted = 0", $sql_params);
        } else {
            $users = [];
        }
    } 

    $table = new html_table();
    $table->attributes = array('class' => 'table', 'border' => '1');
    $table->head = array(
        'STT', 
        get_string('fullname', 'block_th_evstep_user_last_access_report'),
        get_string('crm', 'block_th_evstep_user_last_access_report'),
        get_string('makhoa_column', 'block_th_evstep_user_last_access_report'),
        get_string('role', 'block_th_evstep_user_last_access_report'),
        get_string('lastaccess', 'block_th_evstep_user_last_access_report'),
        get_string('total', 'block_th_evstep_user_last_access_report')
    );

    $stt = 1;
    if (!empty($logs) && is_array($logs)) {
        foreach ($logs as $user) {

            if (!isset($users[$user->id])) {
                continue;
            }
            $user1 = $users[$user->id];

            profile_load_data($user1);

            if ($data->option_select == 1 && !empty($data->roles)) {
                if (!in_array($user1->$th_user_role_field, $data->roles)) {
                    continue;
                }
            } else if ($data->option_select == 3 && !empty($data->makhoa)) {
                $student_class = trim($data->makhoa);
                if (!preg_match("/$student_class/i", $user1->$th_student_class_field)) {
                    continue;
                }
            }
            
            $row = [
                $stt,
                $user1->firstname . ' ' . $user1->lastname,
                $user1->$th_crm_code_field,
                $user1->$th_student_class_field, 
                $user1->$th_user_role_field,
                isset($user->lastactivity) ? format_time(time() - $user->lastactivity) : 'Never',            
                '',
            ];

            $table->data[] = $row;
            $stt++;
        }
    }
    if($table->data) {
        $table->data[0][6] = count($table->data);
    }
    
} 

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_th_evstep_user_last_access_report'));
echo $mform->display();

if (isset($logs)) {
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
