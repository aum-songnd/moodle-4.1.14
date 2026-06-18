<?php

require('../../config.php');

global $DB, $OUTPUT, $PAGE, $COURSE;

$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_plugin01', $courseid);
}

require_login($courseid);

$PAGE->set_url('/blocks/plugin01/view3.php');
$PAGE->set_pagelayout('standard');

$strviewfeed = get_string('pluginname', 'block_plugin01');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

echo $OUTPUT->header();
$dbs =$DB->get_records_sql('SELECT id FROM {course} WHERE id <> :ids',['ids'=>1]);


foreach ($dbs as $x => $y){
    $rec = $DB->get_records_sql(
        "SELECT 
            u.id AS user_id,
            u.username,
            u.firstname,
            u.lastname,
            u.email,
            c.fullname AS coursename,
            GROUP_CONCAT(DISTINCT r.shortname ORDER BY r.shortname SEPARATOR ', ') AS roles
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {course} c ON c.id = e.courseid
        
        JOIN {role_assignments} ra ON ra.userid = u.id 
        JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50 AND ctx.instanceid = c.id
        JOIN {role} r ON r.id = ra.roleid
        WHERE u.deleted = 0 AND c.id = :courseid
        GROUP BY u.id, u.username, u.firstname, u.lastname, u.email, c.fullname
        ORDER BY u.id ASC",
        ['courseid' => $y->id ],
    );
    
    $coursename = '';
    if (!empty($rec)) {
        $first = reset($rec);
        $coursename = $first->coursename;
    }
    echo html_writer::tag('h3', 'Danh sách thanh viên trong khoá: ' . format_string($coursename));
    

    $table = new html_table();
    
    $headerrow = new html_table_row();
    
    $cell = new html_table_cell('STT');
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
    
    $cell = new html_table_cell('Userid');
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
    
    $cell = new html_table_cell('Username');
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
    
    
    
    $cell = new html_table_cell('Full name');
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
    
    $cell = new html_table_cell('Role');
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
    
    $cell = new html_table_cell('Email');
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
    
    $table->data[] = $headerrow;
    
    // Dữ liệu
    $stt = 1;
    foreach ($rec as $item) {
        $row = new html_table_row();
    
        $row->cells[] = new html_table_cell($stt++);
        $row->cells[] = new html_table_cell(s($item->user_id));
        $row->cells[] = new html_table_cell(s($item->username));
        $row->cells[] = new html_table_cell(s($item->firstname . ' ' . $item->lastname));
        $row->cells[] = new html_table_cell(s($item->roles));
        $row->cells[] = new html_table_cell(s($item->email));
    
        $table->data[] = $row;
    }
    
    echo html_writer::table($table);
}

#tra ve theo kieu key , value
$data1= $DB->get_records_menu('user',null,'firstname ASC','id,firstname',20,5);
print_object($data1);
#tra ve theo kieu key , value them dieu kien
$data2=$DB->get_records_select_menu('user','id>20',null,'','id,username',0,5);
print_object($data2);

// $data3=$DB->get_records_select('user','deleted<>1',null,'',"id,CONCAT('username',',','firstname',' ','lastname')",0,5);
// print_object($data3); KHONG AP DUNG DUOC CONCAT

#ham count 
$data4=$DB->count_records_select('user','id<20 OR id >50',null,"COUNT('*')");
print_object($data4);

#toan tu or
$data5 =$DB->sql_bitor(20,30);
print_object($data5);

#toan tu and
$data6 =$DB->sql_bitand(20,30);
print_object($data6);

#toan tu not
$data7 =$DB->sql_bitnot(20);
print_object($data7);

#Su dung like
// $like=$DB->sql_like('username',':sinhvien');
// $params = ['sinhvien' => '%sinh%'];
// $data8=$DB->get_records_select('user',$like,$params,'username ASC','id,username',0,30);
// print_object($data8);

#su dung like_escape
$username = optional_param('username', '', PARAM_RAW_TRIMMED);

$like=$DB->sql_like('username',':sinhvien');
$param['sinhvien'] = '%'. $DB->sql_like_escape($username) .'%';
$data9 = $DB->get_records_select('user', $like, $param, 'id ASC', 'id , username');
print_object($data9);



echo $OUTPUT->footer();