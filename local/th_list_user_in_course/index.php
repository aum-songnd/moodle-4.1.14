<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once $CFG->dirroot . '/local/th_list_user_in_course/lib.php';
require_once($CFG->dirroot . '/enrol/locallib.php');

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$id = optional_param('id', 0, PARAM_INT); // Generic navigation return page switch.
$courseid = $id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'local_th_list_user_in_course', $courseid);
}

require_login($courseid);
require_capability('local/th_list_user_in_course:view', context_course::instance($courseid));

$context = context_system::instance();
$context_course = context_course::instance($id, MUST_EXIST);
$pageurl = '/local/th_list_user_in_course/index.php';
$PAGE->set_url('/local/th_list_user_in_course/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Danh sách thành viên');
$PAGE->set_title($SITE->fullname . ': ' . 'Danh sách thành viên');

$editurl = new moodle_url('/local/th_list_user_in_course/index.php', array('id' => $id));
$settingsnode = $PAGE->navbar->add('Danh sách thành viên', $editurl);
$settingsnode->make_active();

if (empty($id)){
    echo $OUTPUT->header();
    echo "Không tìm thấy id khóa học nào. Vui lòng kiểm tra lại !";
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
$baseurl = new moodle_url('/local/th_list_user_in_course/index.php', array('id' => $id));
if ($editcontrols = local_th_list_user_in_course_controls($context, $baseurl, $id)) {
    echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading('Danh sách thành viên');

$course_active_sql = "select *
    from
      (
      select {user_enrolments}.id, {enrol}.courseid as ue_courseid, {user_enrolments}.userid as ue_userid, {user_enrolments}.timestart, {user_enrolments}.timeend, {user_enrolments}.enrolid, {user_enrolments}.timecreated as ue_timecreated, u.firstname as ue_firstname, u.lastname as ue_lastname, u.phone2 as ue_phone, u.lastaccess as ue_lastaccess, u.username as ue_username, c.fullname as ue_fullname, {user_enrolments}.status
      from {enrol}, {user_enrolments}, {user} as u, {course} as c
      where {enrol}.id = {user_enrolments}.enrolid and {user_enrolments}.userid = u.id and {enrol}.courseid = c.id and c.id = $id) as a
    left join (
      select {th_registeredcourses}.courseid, {th_registeredcourses}.timecreated,
      {course}.fullname, {user}.firstname, {user}.lastname, {user}.username, {th_registeredcourses}.userid, {th_registeredcourses}.timeactivated
      from {th_registeredcourses}, {course}, {user}
      where {th_registeredcourses}.courseid = {course}.id and {th_registeredcourses}.userid = {user}.id and {course}.id = $id
    ) as b
    on a.ue_userid = b.userid and a.ue_courseid = b.courseid;";

$actives = $DB->get_records_sql($course_active_sql);

$course_no_active_sql = "select rc.*, c.fullname, u.id, u.firstname, u.lastname, u.phone2, u.username from {th_registeredcourses} as rc, {course} as c, {user} as u where timeactivated = 0 and rc.courseid = c.id and rc.userid = u.id and rc.courseid = $id";
$no_actives = $DB->get_records_sql($course_no_active_sql);

$allroles = role_fix_names(get_all_roles($context_course), $context_course);

$table = new html_table();
$table->head = array('Họ và Tên', 'Điện thoại', 'Vai trò', 'Nhóm', 'Trạng thái trong khóa học', 'Trạng thái');

if(!empty($actives)) {
    foreach($actives as $user) {
        $link = new moodle_url('/user/profile.php', ['id' => $user->ue_userid]);
        $link_user = html_writer::link($link, $user->ue_firstname . ' ' . $user->ue_lastname);
        
        $data_user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $user->ue_userid");
        $user_picture = $OUTPUT->user_picture($data_user, array('class' => 'userpicture'));

        $row = new html_table_row();
        $cell = new html_table_cell("<strong>$user_picture $link_user</strong>");
        $row->cells[] = $cell;
        $cell = new html_table_cell($user->ue_phone);
        $row->cells[] = $cell;

        $sql = "SELECT ra.roleid FROM {role_assignments} as ra, {context} as c
            WHERE ra.userid = $user->ue_userid AND ra.contextid = c.id
            AND c.instanceid = $id AND c.contextlevel = 50";

        $roles = $DB->get_records_sql($sql);

        $user_role = '';

        foreach ($roles as $k => $role) {
            foreach($allroles as $role1) {
                if($role1->id == $role->roleid){
                    $str = $role1->localname;
                }
            }
            $user_role .= $str . ',';
        }

        $user_role = rtrim($user_role, ',');
        
        $cell = new html_table_cell($user_role);
        $row->cells[] = $cell;
        
        $edit = html_writer::link(new moodle_url('/local/th_list_user_in_course/th_add_user_group.php', array('id' => $user->ue_userid, 'course_id' => $id)),
            $OUTPUT->pix_icon('t/editstring', get_string('edit')),
            array('title' => get_string('edit')));

        $groups = $DB->get_records_sql("SELECT gr.ten_nhom FROM {th_user_in_group} as ug, {th_list_user_in_course_group} as gr WHERE ug.user_id = $user->ue_userid AND ug.group_id = gr.id AND gr.course_id = $id");

        $list = '';
        foreach($groups as $group){
            $list = $list . $group->ten_nhom . ',';
        }

        $list = rtrim($list, ',');

        $cell = new html_table_cell($list . "<span style='margin-left: 5px;'>$edit</span>");
        $row->cells[] = $cell;

        $manager = new \course_enrolment_manager($PAGE, $course);
        $userenrolments = $manager->get_user_enrolments($user->ue_userid);

        foreach ($userenrolments as $ue) {
            $timestart = $ue->timestart;
            $timeend = $ue->timeend;
            $timeenrolled = $ue->timecreated;
            $actions = $ue->enrolmentplugin->get_user_enrolment_actions($manager, $ue);
            $instancename = $ue->enrolmentinstancename;

            $status = get_string('participationactive', 'enrol');
            $statusval = 0;
            switch ($ue->status) {
                case ENROL_USER_ACTIVE:
                    $currentdate = new DateTime();
                    $now = $currentdate->getTimestamp();
                    $isexpired = $timestart > $now || ($timeend > 0 && $timeend < $now);
                    $enrolmentdisabled = $ue->enrolmentinstance->status == ENROL_INSTANCE_DISABLED;
                    // If user enrolment status has not yet started/already ended or the enrolment instance is disabled.
                    if ($isexpired || $enrolmentdisabled) {
                        $status = get_string('participationnotcurrent', 'enrol');
                        $statusval = 1;
                    }
                    break;
                case ENROL_USER_SUSPENDED:
                    $status = get_string('participationsuspended', 'enrol');
                    $statusval = 2;
                    break;
            }
        }

        if($statusval == 0){
            $cell = new html_table_cell();
            $cell->text = html_writer::tag(
                'span',
                $status,
                array('class' => 'badge badge-success')
            );
            $row->cells[]  = $cell;
        } else {
            $cell = new html_table_cell();
            $cell->text = html_writer::tag(
                'span',
                $status,
                array('class' => 'badge badge-warning')
            );
            $row->cells[]  = $cell;
        }

        $cell = new html_table_cell();
        $cell->text   = html_writer::tag(
            'span',
            'Đã kích hoạt',
            array('class' => 'badge badge-success')
        );
        $row->cells[]  = $cell;

        $table->data[] = $row;
    }
}

if(!empty($no_actives)) {
    foreach($no_actives as $user) {
        $link = new moodle_url('/user/profile.php', ['id' => $user->id]);
        $link_user = html_writer::link($link, $user->firstname . ' ' . $user->lastname);

        $data_user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $user->id");
        $user_picture = $OUTPUT->user_picture($data_user, array('class' => 'userpicture'));
        
        $row = new html_table_row();
        $cell = new html_table_cell("<strong>$user_picture $link_user</strong>");
        $row->cells[] = $cell;
        $cell = new html_table_cell($user->phone2);
        $row->cells[] = $cell;

        foreach($allroles as $role1) {
            if($role1->shortname == 'student'){
                $str = $role1->localname;
            }
    
        }

        $cell = new html_table_cell($str);
        $row->cells[] = $cell;

        $edit = html_writer::link(new moodle_url('/local/th_list_user_in_course/th_add_user_group.php', array('id' => $user->id, 'course_id' => $id)),
            $OUTPUT->pix_icon('t/editstring', get_string('edit')),
            array('title' => get_string('edit')));

        $groups = $DB->get_records_sql("SELECT gr.ten_nhom FROM {th_user_in_group} as ug, {th_list_user_in_course_group} as gr WHERE ug.user_id = $user->id AND ug.group_id = gr.id AND gr.course_id = $id");

        $list = '';
        foreach($groups as $group){
            $list = $list . $group->ten_nhom . ',';
        }

        $list = rtrim($list, ',');

        $cell = new html_table_cell($list . "<span style='margin-left: 5px;'>$edit</span>");
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text   = html_writer::tag(
            'span',
            'Chưa được ghi danh',
            array('class' => 'badge badge-danger')
        );
        $row->cells[]  = $cell;

        $cell = new html_table_cell();
        $cell->text   = html_writer::tag(
            'span',
            'Chưa kích hoạt',
            array('class' => 'badge badge-danger')
        );

        $row->cells[]  = $cell;

        $table->data[] = $row;
    }
}

echo "<style>/* Style the table */
table {
  border-collapse: collapse !important;
  border-spacing: 0 !important;
  width: 100% !important;
  border: 1px solid #ddd !important;
}

/* Style table headers and table data */
th, td {
  text-align: left !important;
}

th:first-child, td:first-child {
  text-align: left !important;
}

tr {
    border-bottom: 1px solid #ddd;
}

thead {
    background: #f2f2f2;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 18px;
    border-bottom: 1px solid #ddd !important;
}

/* Zebra-striped table rows */
tr:nth-child(even) {
  background-color: #f2f2f2 !important
}
</style>";

$table->attributes = array('class' => 'th_list_user_in_course_table');
$html = html_writer::table($table);
echo $html;

$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_list_user_in_course_table', 'Danh sách thành viên', $lang));

echo $OUTPUT->footer();
?>

