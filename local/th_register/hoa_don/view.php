<?php
require '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';
require_once $CFG->dirroot . '/local/th_register/hoa_don/lib.php';
require_once $CFG->dirroot . '/local/th_register/hoa_don/select_dot_mo_mon_form.php';

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse');
}
require_login($COURSE->id);
require_capability('local/th_register:view_danh_sach_dong_hoc_phi', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/hoa_don/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_invoices', 'local_th_register'));
$PAGE->set_title(get_string('manage_invoices', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/hoa_don/view.php');

$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', get_string('student_payment_list', 'local_th_register'), $lang));
$PAGE->navbar->add(get_string('student_list', 'local_th_register'), new moodle_url('/local/th_register/hoa_don/view.php'));

$mform = new select_dot_mo_mon_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {
    $dot_mo_mon_id = (int) $fromform->dot_mo_mon_id;
    $component = 'local_th_register';
    $filearea = 'dot' . $dot_mo_mon_id;
    $contextid = $context->id;
    // da nop hoa don
    $sql = "SELECT DISTINCT u.* FROM {th_hoa_don} hd
                    JOIN {user} u ON hd.userid_moodle = u.id
                    WHERE hd.dot_mo_mon_id = $dot_mo_mon_id";
    $users1 = $DB->get_records_sql($sql);

    $sql = "SELECT DISTINCT u.* FROM {th_dang_ky_mon_hoc} dkh
                JOIN {user} u ON dkh.userid_moodle = u.id
                JOIN {th_dot_mo_has_mon_hoc} dmhmh ON dmhmh.id = dkh.dot_mo_has_mon_hoc_id
                WHERE dmhmh.dot_mo_mon_id = $dot_mo_mon_id";
    $users2 = $DB->get_records_sql($sql);

    if ($users1 || $users2) {
        $userid_arr = array();
        foreach ($users1 as $uid => $user) {
            $userid_arr[$uid] = $uid;
        }
        // print_object($users1);
        // exit;
        foreach ($users2 as $uid => $user) {
            $userid_arr[$uid] = $uid;
        }
        $hoa_don = $DB->get_records('th_hoa_don', ['dot_mo_mon_id' => $dot_mo_mon_id]);
        $dot_mo_mon = $DB->get_record('th_dot_mo_mon', ['id' => $dot_mo_mon_id]);

        $extra = array_filter(explode(',', $CFG->showuseridentity));
        $userfields = array_values($extra);

        $usernamefield = get_all_user_name_fields();
        $usernamefield = implode(",", $usernamefield);
        $alluserfields = "id," . $usernamefield;

        if (count($userfields) > 0) {
            $alluserfields .= "," . implode(',', $userfields);
        }

        $alluserfields .= "," . "email";
        $user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);

        $table = new html_table();

        $table->attributes = array('class' => 'table', 'border' => '1');
        $headrows = new html_table_row();

        $cell = new html_table_cell(get_string('majors', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('ma_mon', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('ten_mon', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('so_tin_chi', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('unit_price', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('total_price', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('registration_code', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('payment_times', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('paid_amount', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('remaining_debt', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;
        $cell = new html_table_cell(get_string('remaining_credit', 'local_th_register'));
        $cell->attributes['class'] = 'cell headingcell';
        $cell->header = true;
        $headrows->cells[] = $cell;

        $rightrows = [];
        $rightrows[] = $headrows;
        list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);
        $soCot = count($leftrows[0]->cells);
        $config = get_config('local_thlib');
        $strLeft = trim($config->custom_fields1, ' ');
        $strRight = trim($config->custom_fields2, ' ');
        if ($strLeft == '') {
            $cellLeft = 0;
        } else {
            $cellLeft = count(explode(',', trim($config->custom_fields1)));
        }
        if ($strRight == '') {
            $cellRight = 0;
        } else {
            $cellRight = count(explode(',', trim($config->custom_fields2)));
        }
        $t = 0;
        // print_object($userid_arr);
        foreach ($userid_arr as $userid) {
            $sql = "SELECT a.*,b.so_tien_da_nop FROM
                    (SELECT DISTINCT m.id,m.userid_moodle,m.ma_dang_ky,m.id_nganh,m.hoc_phi_mot_tin_chi,mh.ma_mon,ten_mon,so_tin_chi,dmm.ten_dot_mo,dmm.id as dot_mo_mon_id
                    FROM {th_dot_mo_has_mon_hoc} dmhmh
                    JOIN {th_dot_mo_mon} dmm ON dmhmh.dot_mo_mon_id = dmm.id
                    JOIN {th_mon_hoc} mh ON dmhmh.mon_hoc_id = mh.id
                    JOIN {th_dang_ky_mon_hoc} dkh ON dmhmh.id = dkh.dot_mo_has_mon_hoc_id
                    JOIN (SELECT dkh.*,nganh.hoc_phi_mot_tin_chi 
                        FROM {th_dang_ky_mon_hoc} dkh
                        JOIN {th_nganh_hoc} nganh ON nganh.id = dkh.id_nganh) m 
                    ON m.dot_mo_has_mon_hoc_id = dmhmh.id AND dmhmh.dot_mo_mon_id = $dot_mo_mon_id
                    LEFT JOIN {files} f ON dkh.userid_moodle = f.userid) a
                    LEFT JOIN
                    (SELECT DISTINCT hd.userid_moodle,hd.dot_mo_mon_id,hd.so_tien_da_nop
                    FROM {th_hoa_don} hd) b
                    ON a.userid_moodle = b.userid_moodle AND a.dot_mo_mon_id=b.dot_mo_mon_id
                    WHERE a.dot_mo_mon_id=$dot_mo_mon_id AND a.userid_moodle = $userid";
            $mon_hoc_dang_ky = $DB->get_records_sql($sql);
            $so_lan_nop = $DB->count_records('files', array('component' => $component, 'filearea' => $filearea, 'contextid' => $contextid, 'userid' => $userid)) - 1;
            if ($so_lan_nop == -1) {
                $so_lan_nop = 0;
            }
            // print_object($mon_hoc_dang_ky);
            // exit;

            $hoc_phi_mot_tin_chi = 0;
            $tong_so_tin_chi = 0;
            $so_tien_da_nop = 0;
            $ma_dang_ky = '';

            if ($mon_hoc_dang_ky) {
                foreach ($mon_hoc_dang_ky as $key => $mon_hoc) {
                    $hoc_phi_mot_tin_chi = $mon_hoc->hoc_phi_mot_tin_chi;
                    $tong_so_tin_chi += $mon_hoc->so_tin_chi;
                    $so_tien_da_nop = $mon_hoc->so_tien_da_nop;
                    $ten_dot_mo = $mon_hoc->ten_dot_mo;
                }
            } else {
                $ten_dot_mo = $DB->get_field('th_dot_mo_mon', 'ten_dot_mo', array('id' => $dot_mo_mon_id));
                if ($tmp = $DB->get_record_sql("SELECT * FROM {th_hoa_don} hd WHERE hd.userid_moodle=$userid AND hd.dot_mo_mon_id=$dot_mo_mon_id")) {
                    $so_tien_da_nop = $tmp->so_tien_da_nop;
                }
            }
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(get_string('total', 'local_th_register'));
            $row->cells[] = $cell;
            $cell = new html_table_cell(get_string('course_count', 'local_th_register', count($mon_hoc_dang_ky)));
            $row->cells[] = $cell;
            $cell = new html_table_cell($tong_so_tin_chi);
            $row->cells[] = $cell;
            $cell = new html_table_cell(number_format($hoc_phi_mot_tin_chi, 0, ',', '.'));
            $row->cells[] = $cell;
            $tong_tien = $tong_so_tin_chi * $hoc_phi_mot_tin_chi;
            $cell = new html_table_cell(number_format($tong_tien, 0, ',', '.'));
            $row->cells[] = $cell;
            $cell = new html_table_cell($ma_dang_ky);
            $row->cells[] = $cell;
            $cell = new html_table_cell($so_lan_nop);
            $row->cells[] = $cell;
            if (!$so_tien_da_nop) {
                $so_tien_da_nop = 0;
            }
            $cell = new html_table_cell(number_format($so_tien_da_nop, 0, ',', '.'));
            $row->cells[] = $cell;
            if ($tong_so_tin_chi * $hoc_phi_mot_tin_chi - $so_tien_da_nop > 0) {
                $no = $tong_so_tin_chi * $hoc_phi_mot_tin_chi - $so_tien_da_nop;
                $cell = new html_table_cell(number_format($no, 0, ',', '.'));
            } else {
                $cell = new html_table_cell(0);
            }
            $row->cells[] = $cell;
            if ($tong_so_tin_chi * $hoc_phi_mot_tin_chi - $so_tien_da_nop > 0) {
                $cell = new html_table_cell(0);
            } else {
                $du = $so_tien_da_nop - $tong_so_tin_chi * $hoc_phi_mot_tin_chi;
                $cell = new html_table_cell(number_format($du, 0, ',', '.'));
            }
            $row->cells[] = $cell;
            $rightrows[$userid . '_' . $t] = $row;
            if (!$mon_hoc_dang_ky) {
                $t++;
                continue;
            }

            foreach ($mon_hoc_dang_ky as $key => $mon_hoc) {
                $row = new html_table_row();
                $cell = new html_table_cell(th_hoa_don_get_ten_nganh($mon_hoc->id_nganh));
                $row->cells[] = $cell;
                $cell = new html_table_cell($mon_hoc->ma_mon);
                $row->cells[] = $cell;
                $cell = new html_table_cell($mon_hoc->ten_mon);
                $row->cells[] = $cell;
                $cell = new html_table_cell($mon_hoc->so_tin_chi);
                $row->cells[] = $cell;
                $cell = new html_table_cell(number_format($mon_hoc->hoc_phi_mot_tin_chi, 0, ',', '.'));
                $row->cells[] = $cell;
                $cell = new html_table_cell(number_format($mon_hoc->so_tin_chi * $mon_hoc->hoc_phi_mot_tin_chi, 0, ',', '.'));
                $row->cells[] = $cell;
                $cell = new html_table_cell($mon_hoc->ma_dang_ky);
                $row->cells[] = $cell;
                $cell = new html_table_cell();
                $row->cells[] = $cell;
                $cell = new html_table_cell();
                $row->cells[] = $cell;
                $cell = new html_table_cell();
                $row->cells[] = $cell;
                $cell = new html_table_cell();
                $row->cells[] = $cell;
                $rightrows[$userid . '_' . $mon_hoc->ma_mon] = $row;
                $hoc_phi_mot_tin_chi = $mon_hoc->hoc_phi_mot_tin_chi;
                $tong_so_tin_chi += $mon_hoc->so_tin_chi;
                $so_tien_da_nop = $mon_hoc->so_tien_da_nop;
                $t++;
            }
        }
        $stt = 0;
        foreach ($rightrows as $key => $row) {
            $arr = explode("_", $key);
            $userid = $arr[0];
            $row->cells = array_merge($leftrows[$userid]->cells, $row->cells);
            if ($stt != 0) {
                $c = new html_table_cell($stt);
                $row->cells[0] = $c;
            }
            $stt++;
            $table->data[] = $row;
        }
        $headrows = array_shift($table->data);
        $table->head = $headrows->cells;
        $table->attributes['style'] = "width: 100%; text-align:center;";
        $table->align[0] = 'center';

        $html2 = html_writer::table($table);
    } else {
        $html2 = get_string('no_students_registered', 'local_th_register');
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('student_list_registered', 'local_th_register'));
$mform->display();
if (isset($html2)) {
    echo $html2;
}
echo $OUTPUT->footer();
