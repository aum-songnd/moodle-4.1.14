<?php
defined('MOODLE_INTERNAL') || die();

function th_hoa_don_check_dang_ky($dot_mo_mon_id, $userid) {
    global $DB;

    $sql = "SELECT * FROM {th_dot_mo_has_mon_hoc} dmhmh
    		JOIN {th_dang_ky_mon_hoc} dkmh ON dmhmh.id = dkmh.dot_mo_has_mon_hoc_id
    		WHERE dmhmh.dot_mo_mon_id = :dot_mo_mon_id AND dkmh.userid_moodle = :userid";
    $params = array('dot_mo_mon_id' => $dot_mo_mon_id, 'userid' => $userid);

    if ($DB->record_exists_sql($sql, $params)) {
        return true;
    }
    return false;
}

function th_hoa_don_get_ten_nganh($id) {
    global $DB;

    $sql = "SELECT ten_nganh FROM {th_nganh_hoc} WHERE id = :id";
    $params = array('id' => $id);

    $record = $DB->get_record_sql($sql, $params);

    return $record ? $record->ten_nganh : '';
}

function th_hoa_don_download($ten_dot_mo, $contextid, $component, $filearea) {

    global $DB;

    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, $component, $filearea);
    $file = array();
    foreach ($files as $f) {
        if ($f->get_filename() == '.') {
            $user = $DB->get_record('user', ['id' => $f->get_userid()], 'firstname,lastname');

            $fullname = $user->firstname . ' ' . $user->lastname;

            $username = $DB->get_field('user', 'username', ['id' => $f->get_userid()]);
            $file[$username . '_' . $fullname . '_' . $f->get_userid()] = $f;
        }
    }
    $tempzip = tempnam($CFG->tempdir . '/', 'local_th_register');
    // Zip files.
    $zipper = new zip_packer();
    if ($zipper->archive_to_pathname($file, $tempzip)) {
        $zipfile = $tempzip;
    }
    send_temp_file($zipfile, $ten_dot_mo . '.zip');
}

function th_hoa_don_delete($tu_ngay, $den_ngay) {

    global $DB, $CFG;
    if (time() >= $tu_ngay && time() <= $den_ngay) {
        $fs = get_file_storage();
        if ($confirm and confirm_sesskey()) {
            $itemid = $DB->get_record('files', array('id' => $record->id), 'itemid')->itemid;
            $fs->delete_area_files($contextid, $component, $filearea, $itemid);
            // $id = $DB->get_record('th_hoa_don', array('userid_moodle' => $USER->id, 'dot_mo_mon_id' => $dot_mo_mon_id))->id;
            $DB->delete_records('th_hoa_don', ['userid_moodle' => $USER->id, 'dot_mo_mon_id' => $dot_mo_mon_id]);
            redirect($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id, get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        $strheading = get_string('delete');
        $PAGE->navbar->add($strheading);
        $PAGE->set_title($strheading);
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);

        $yesurl = new moodle_url('/local/th_register/hoa_don/edit.php', array('id' => $dot_mo_mon_id, 'confirm' => 1, 'action' => 'delete', 'sesskey' => sesskey()));
        $message = get_string('confirm_delete_invoice', 'local_th_register');
        echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id));
        echo $OUTPUT->footer();
        die;
    }
}

function th_hoa_don_table_tinh_trang_nop($dot_mo_mon_id, $tu_ngay, $den_ngay, $record) {
    global $DB, $USER, $CFG;

    $userid = $USER->id;

    $context = context_system::instance();

    $sql = "SELECT DISTINCT u.*,hd.so_tien_da_nop,dmm.tu_ngay,den_ngay FROM {th_hoa_don} hd
                JOIN {user} u ON hd.userid_moodle = u.id
                JOIN {th_dot_mo_mon} dmm ON hd.dot_mo_mon_id = dmm.id
                WHERE dmm.id = $dot_mo_mon_id";
    $data = $DB->get_records_sql($sql);

    $hoa_don = $DB->get_records('th_hoa_don', ['dot_mo_mon_id' => $dot_mo_mon_id]);
    $dot_mo_mon = $DB->get_record('th_dot_mo_mon', ['id' => $dot_mo_mon_id]);
    $so_tien_da_nop = $DB->get_field('th_hoa_don', 'so_tien_da_nop', array('userid_moodle' => $userid, 'dot_mo_mon_id' => $dot_mo_mon_id));
    // exit();
    $table = new html_table();
    $table->size = array('30%', '70%');
    $table->caption = get_string('status', 'local_th_register');

    if (has_capability('local/th_register:view', $context)) {
        $row = new html_table_row();
        $cell = new html_table_cell(get_string('num_paid_users', 'local_th_register'));
        $cell->header = true;
        $row->cells[] = $cell;
        $cell = new html_table_cell(count($data));
        $cell->header = true;
        $row->cells[] = $cell;
        $table->data[] = $row;
    }

    if (has_capability('local/th_register:register', $context)) {
        $row = new html_table_row();
        $cell = new html_table_cell(get_string('payment_status', 'local_th_register'));
        $cell->header = true;
        $row->cells[] = $cell;
        if ($record) {
            $cell = new html_table_cell(get_string('waiting_confirmation', 'local_th_register'));

        } else {
            $cell = new html_table_cell(get_string('not_paid', 'local_th_register'));
        }
        $cell->header = true;
        $row->cells[] = $cell;
        $table->data[] = $row;

        $row = new html_table_row();
        $cell = new html_table_cell(get_string('amount_paid', 'local_th_register'));
        $cell->header = true;
        $row->cells[] = $cell;
        if ($record) {
            $cell = new html_table_cell(number_format($so_tien_da_nop, 0, ',', '.') . get_string('currency_vnd', 'local_th_register'));
        } else {
            $cell = new html_table_cell('0' . get_string('currency_vnd', 'local_th_register'));
        }
        $cell->header = true;
        $row->cells[] = $cell;
        $table->data[] = $row;
    }

    $row = new html_table_row();
    $cell = new html_table_cell(get_string('start_date', 'local_th_register'));
    $cell->header = true;
    $row->cells[] = $cell;
    $cell = new html_table_cell(date('d/m/Y H:i:s', $tu_ngay));
    $cell->header = true;
    $row->cells[] = $cell;
    $table->data[] = $row;

    $row = new html_table_row();
    $cell = new html_table_cell(get_string('deadline', 'local_th_register'));
    $cell->header = true;
    $row->cells[] = $cell;
    if (time() >= $tu_ngay && time() <= $den_ngay) {
        $cell = new html_table_cell(date('d/m/Y H:i:s', $den_ngay));
    } else {
        $cell = new html_table_cell(get_string('expired', 'local_th_register'));
    }
    $cell->header = true;
    $row->cells[] = $cell;
    $table->data[] = $row;
    $html = html_writer::table($table);
    $thongtin = '';
    if ($stk = get_config('local_th_register','stk')){
        $thongtin .= $stk;
    }

    $actions = html_writer::start_tag('center');
    $actions = html_writer::start_tag('div', array('class' => 'submissionlinks'));
    if ($record) {

        if (time() >= $tu_ngay && time() <= $den_ngay) {
            $actions .= html_writer::link($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id . '&action=' . 'add',
            get_string('edit_invoice', 'local_th_register'),
                array('class' => 'btn btn-secondary ml-1 mb-1')
            );
            // $actions .= html_writer::link($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id . '&action=' . 'delete',
            //     "Xóa hóa đơn",
            //     array('class' => 'btn btn-primary ml-1 mb-1')
            // );
        }
    } else {
        if (time() >= $tu_ngay && time() <= $den_ngay) {
            $actions .= html_writer::link($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id . '&action=' . 'add',
            get_string('add_invoice', 'local_th_register'),
            array('class' => 'btn btn-secondary ml-1 mb-1')
            );
        }
    }
    if (has_capability('local/th_register:view_danh_sach_dong_hoc_phi', $context)) {
            $actions .= html_writer::link($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id . '&action=' . 'view',
            get_string('view_paid_students', 'local_th_register'),
            array('class' => 'btn btn-secondary ml-1 mb-1')
        );
    }
     if (has_capability('local/th_register:download_hoa_don', $context)) {
            $actions .= html_writer::link($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id . '&action=' . 'download',
            get_string('download_invoice', 'local_th_register'),
            array('class' => 'btn btn-primary ml-1 mb-1')
        );
    }
    if (has_capability('local/th_register:confirm_dong_hoc_phi', $context)) {
            $actions .= html_writer::link($CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id . '&action=' . 'list',
            get_string('confirm_payment', 'local_th_register'),
            array('class' => 'btn btn-secondary ml-1 mb-1')
        );
    }
    $actions .= html_writer::end_tag('div') . '</br>';
    return $html .  $thongtin . $actions;
}

function th_hoa_don_danh_sach_nop_hoa_don($dot_mo_mon_id) {

    global $DB;
    $sql = "SELECT DISTINCT hd.*,u.firstname,lastname,username,email FROM {th_hoa_don} hd
                JOIN {user} u ON hd.userid_moodle = u.id
                JOIN {th_dang_ky_mon_hoc} dkh ON hd.userid_moodle = dkh.userid_moodle
                WHERE hd.dot_mo_mon_id = $dot_mo_mon_id";
    $users = $DB->get_records_sql($sql);

    $table = new html_table();
    $header = array(
        get_string('no.', 'local_th_register'),
        get_string('username', 'local_th_register'),
        get_string('fullname', 'local_th_register'),
        get_string('amount_paid', 'local_th_register'),
        get_string('confirm_payment', 'local_th_register'),
    );
    $table->head = $header;
    $stt = 1;
    foreach ($users as $key => $user) {
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($user->username);
        $row->cells[] = $cell;
        $fullname = html_writer::link(
            new moodle_url('/user/profile.php?id=' . $user->userid_moodle),
            $user->firstname . ' ' . $user->lastname
        );
        $cell = new html_table_cell($fullname);
        $row->cells[] = $cell;
        $cell = new html_table_cell(number_format($user->so_tien_da_nop, 0, ',', '.') . ' đồng');
        $row->cells[] = $cell;
        $trang_thai = "<span class='badge badge-warning'>" . get_string('confirm_payment', 'local_th_register') . "</span>";
        $da_nop = "";
        if ($user->trang_thai_nop == 1) {
            $da_nop = "<span class='badge badge-success'>" . get_string('paid', 'local_th_register') . "</span>";
            $trang_thai = "<span class='badge badge-danger'>" . get_string('cancel_confirmation', 'local_th_register') . "</span>";
        }
        $xac_nhan = html_writer::link(
            new moodle_url('/local/th_register/hoa_don/edit.php',
                array(
                    'id' => $dot_mo_mon_id,
                    'userid' => $user->userid_moodle,
                    'action' => 'confirm',
                    'status' => $user->trang_thai_nop,
                )
            ), $trang_thai);
        $cell = new html_table_cell($da_nop . $xac_nhan);
        $row->cells[] = $cell;
        $table->data[] = $row;
        $stt++;
    }
    return html_writer::table($table);

}

// function th_hoa_don_xac_nhan_dong_hoc_phi($dot_mo_mon_id, $ten_dot_mo) {

//     global $DB;

//     $sql = "SELECT DISTINCT hd.*,u.firstname,lastname,username,email FROM {th_hoa_don} hd
//                 JOIN {user} u ON hd.userid_moodle = u.id
//                 JOIN {th_dang_ky_mon_hoc} dkh ON hd.userid_moodle = dkh.userid_moodle
//                 WHERE hd.dot_mo_mon_id = $dot_mo_mon_id";
//     $users = $DB->get_records_sql($sql);

//     // print_object($users);

//     $table = new html_table();
//     // $table->size = array('30%', '70%');
//     $header = array(
//         'Số thứ tự',
//         'Tên tài khoản',
//         'Họ và tên',
//         'Số tiền đã đóng',
//         'Xác nhận',
//     );
//     $table->head = $header;
//     $table->caption = "Các học viên đã nộp hóa đơn " . $ten_dot_mo;
//     $stt = 1;
//     foreach ($users as $key => $user) {
//         $row = new html_table_row();
//         $cell = new html_table_cell($stt);
//         $row->cells[] = $cell;
//         $cell = new html_table_cell($user->username);
//         $row->cells[] = $cell;
//         $fullname = html_writer::link(
//             new moodle_url('/user/profile.php?id=' . $user->userid_moodle),
//             $user->firstname . ' ' . $user->lastname
//         );
//         $cell = new html_table_cell($fullname);
//         $row->cells[] = $cell;
//         $cell = new html_table_cell(number_format($user->so_tien_da_nop, 0, ',', '.') . ' đồng');
//         $row->cells[] = $cell;
//         $cell = new html_table_cell("<input type='checkbox' id='$user->id' name='$user->id' value=''>");
//         $row->cells[] = $cell;
//         $table->data[] = $row;
//         $stt++;
//     }

//     // if (has_capability('local/th_register:view', $context)) {
//     //     $row = new html_table_row();
//     //     $cell = new html_table_cell('Số người đã nộp');
//     //     $cell->header = true;
//     //     $row->cells[] = $cell;
//     //     $cell = new html_table_cell(count($data));
//     //     $cell->header = true;
//     //     $row->cells[] = $cell;
//     //     $table->data[] = $row;
//     // }

//     // if (!has_capability('local/th_register:view', $context)) {
//     //     $row = new html_table_row();
//     //     $cell = new html_table_cell('Trạng thái nộp');
//     //     $cell->header = true;
//     //     $row->cells[] = $cell;
//     //     if ($record) {
//     //         $cell = new html_table_cell("Đã nộp");

//     //     } else {
//     //         $cell = new html_table_cell("Chưa nộp");
//     //     }
//     //     $cell->header = true;
//     //     $row->cells[] = $cell;
//     //     $table->data[] = $row;

//     //     $row = new html_table_row();
//     //     $cell = new html_table_cell('Số tiền đã nộp');
//     //     $cell->header = true;
//     //     $row->cells[] = $cell;
//     //     if ($record) {
//     //         $cell = new html_table_cell(number_format($so_tien_da_nop, 0, ',', '.') . ' VNĐ');

//     //     } else {
//     //         $cell = new html_table_cell('0' . ' VNĐ');
//     //     }
//     //     $cell->header = true;
//     //     $row->cells[] = $cell;
//     //     $table->data[] = $row;
//     // }

//     // $row = new html_table_row();
//     // $cell = new html_table_cell('Ngày bắt đầu');
//     // $cell->header = true;
//     // $row->cells[] = $cell;
//     // $cell = new html_table_cell(date('d/m/Y H:i:s', $tu_ngay));
//     // $cell->header = true;
//     // $row->cells[] = $cell;
//     // $table->data[] = $row;

//     // $row = new html_table_row();
//     // $cell = new html_table_cell('Hạn chót');
//     // $cell->header = true;
//     // $row->cells[] = $cell;
//     // if (time() >= $tu_ngay && time() <= $den_ngay) {
//     //     $cell = new html_table_cell(date('d/m/Y H:i:s', $den_ngay));
//     // } else {
//     //     $cell = new html_table_cell("Đã hết hạn");
//     // }
//     // $cell->header = true;
//     // $row->cells[] = $cell;
//     // $table->data[] = $row;
//     $html = html_writer::table($table);
//     return $html;

// }

// function th_hoa_don_danh_sach_dang_ky_hoc($dot_mo_mon_id, $contextid, $component, $filearea) {

//     global $DB, $CFG;

//     $sql = "SELECT DISTINCT u.* FROM {th_hoa_don} hd
//                 JOIN {user} u ON hd.userid_moodle = u.id
//                 WHERE hd.dot_mo_mon_id = $dot_mo_mon_id";
//     $users1 = $DB->get_records_sql($sql);

//     $sql = "SELECT DISTINCT u.* FROM {th_dang_ky_mon_hoc} dkh
//                 JOIN {user} u ON dkh.userid_moodle = u.id
//                 JOIN {th_dot_mo_has_mon_hoc} dmhmh ON dmhmh.id = dkh.dot_mo_has_mon_hoc_id
//                 WHERE dmhmh.dot_mo_mon_id = $dot_mo_mon_id";
//     $users2 = $DB->get_records_sql($sql);

//     if ($users1 || $users2) {
//         $userid_arr = array();
//         foreach ($users1 as $uid => $user) {
//             $userid_arr[$uid] = $uid;
//         }

//         foreach ($users2 as $uid => $user) {
//             $userid_arr[$uid] = $uid;
//         }

//         $table = new html_table();
//         $header = array(
//             'Ngày đăng ký',
//             // 'Mã sinh viên',
//             'Tên tài khoản',
//             'Họ và tên',
//             // 'Ngày sinh',
//             'Ngành học',
//             'Mã môn',
//             'Tên môn',
//             'Số tín chỉ',
//             'Số tiền một tín chỉ',
//             'Thành tiền',
//             'Mã đăng ký',
//             'Số lần nộp',
//             'Số tiền nộp',
//             'Còn nợ',
//             'Còn dư',
//         );
//         $table->head = $header;
//         $table->attributes = array('class' => 'table', 'border' => '1');
//         $t = 0;
//         foreach ($userid_arr as $userid) {
//             $sql = "SELECT a.*,b.so_tien_da_nop,c.username,firstname,lastname FROM
//                     (SELECT DISTINCT dkh.id,userid_moodle,ma_dang_ky,id_nganh,dkh.ngay_tao,mh.ma_mon,ten_mon,so_tin_chi,dmm.ten_dot_mo,dmm.id as dot_mo_mon_id, dmm.hoc_phi_mot_tin_chi
//                     FROM {th_dot_mo_has_mon_hoc} dmhmh
//                     JOIN {th_dot_mo_mon} dmm ON dmhmh.dot_mo_mon_id = dmm.id
//                     JOIN {th_mon_hoc} mh ON dmhmh.mon_hoc_id = mh.id
//                     JOIN {th_dang_ky_mon_hoc} dkh ON dmhmh.id = dkh.dot_mo_has_mon_hoc_id
//                     LEFT JOIN {files} f ON dkh.userid_moodle = f.userid) a
//                     LEFT JOIN
//                     (SELECT DISTINCT hd.userid_moodle,hd.dot_mo_mon_id,hd.so_tien_da_nop
//                     FROM {th_hoa_don} hd) b
//                     ON a.userid_moodle = b.userid_moodle AND a.dot_mo_mon_id=b.dot_mo_mon_id
//                     LEFT JOIN (SELECT * FROM {user}) c ON c.id = a.userid_moodle
//                     WHERE a.dot_mo_mon_id=$dot_mo_mon_id AND a.userid_moodle = $userid
//                     ORDER BY a.userid_moodle;";
//             $mon_hoc_dang_ky = $DB->get_records_sql($sql);
//             $so_lan_nop = $DB->count_records('files', array('component' => $component, 'filearea' => $filearea, 'contextid' => $contextid, 'userid' => $userid)) - 1;
//             if ($so_lan_nop == -1) {
//                 $so_lan_nop = 0;
//             }
//             // print_object($mon_hoc_dang_ky);
//             // exit;

//             $hoc_phi_mot_tin_chi = 0;
//             $tong_so_tin_chi = 0;
//             $so_tien_da_nop = 0;
//             $ma_dang_ky = '';
//             $ten_tai_khoan = '';
//             $fullname = '';

//             if ($mon_hoc_dang_ky) {
//                 foreach ($mon_hoc_dang_ky as $key => $mon_hoc) {
//                     $hoc_phi_mot_tin_chi = $mon_hoc->hoc_phi_mot_tin_chi;
//                     $tong_so_tin_chi += $mon_hoc->so_tin_chi;
//                     $so_tien_da_nop = $mon_hoc->so_tien_da_nop;
//                     $ten_dot_mo = $mon_hoc->ten_dot_mo;
//                     $ten_tai_khoan = $mon_hoc->username;
//                     $fullname = $mon_hoc->firstname . ' ' . $mon_hoc->lastname;
//                 }
//             } else {
//                 $ten_dot_mo = $DB->get_field('th_dot_mo_mon', 'ten_dot_mo', array('id' => $dot_mo_mon_id));
//                 if ($tmp = $DB->get_record_sql("SELECT * FROM {th_hoa_don} hd WHERE hd.userid_moodle=$userid AND hd.dot_mo_mon_id=$dot_mo_mon_id")) {
//                     $so_tien_da_nop = $tmp->so_tien_da_nop;
//                 }
//             }
//             // $field_ma_hoc_vien = trim(get_config('local_th_register', 'ma_hoc_vien'));
//             // print_object($field_ma_hoc_vien);
//             // exit;
//             // $ma_hoc_vien = $DB->get_field_sql(
//             //     "SELECT info_data.data FROM {user_info_field} as field JOIN {user_info_data} as info_data
//             //     ON info_data.fieldid = field.id
//             //     WHERE field.shortname = '$field_ma_hoc_vien' AND info_data.userid = $userid");

//             $row = new html_table_row();
//             $cell = new html_table_cell();
//             $row->cells[] = $cell;
//             // $cell = new html_table_cell($ma_hoc_vien);
//             // $row->cells[] = $cell;
//             $cell = new html_table_cell($ten_tai_khoan);
//             $row->cells[] = $cell;
//             $cell = new html_table_cell($fullname);
//             $row->cells[] = $cell;
//             // $cell = new html_table_cell('Ngày sinh');
//             // $row->cells[] = $cell;
//             $cell = new html_table_cell();
//             $row->cells[] = $cell;
//             $cell = new html_table_cell('Tổng');
//             $row->cells[] = $cell;
//             $cell = new html_table_cell(count($mon_hoc_dang_ky) . " môn");
//             $row->cells[] = $cell;
//             $cell = new html_table_cell($tong_so_tin_chi);
//             $row->cells[] = $cell;
//             $cell = new html_table_cell(number_format($hoc_phi_mot_tin_chi, 0, ',', '.'));
//             $row->cells[] = $cell;
//             $tong_tien = $tong_so_tin_chi * $hoc_phi_mot_tin_chi;
//             $cell = new html_table_cell(number_format($tong_tien, 0, ',', '.'));
//             $row->cells[] = $cell;
//             $cell = new html_table_cell($ma_dang_ky);
//             $row->cells[] = $cell;
//             $cell = new html_table_cell($so_lan_nop);
//             $row->cells[] = $cell;
//             if (!$so_tien_da_nop) {
//                 $so_tien_da_nop = 0;
//             }
//             $cell = new html_table_cell(number_format($so_tien_da_nop, 0, ',', '.'));
//             $row->cells[] = $cell;
//             if ($tong_so_tin_chi * $hoc_phi_mot_tin_chi - $so_tien_da_nop > 0) {
//                 $no = $tong_so_tin_chi * $hoc_phi_mot_tin_chi - $so_tien_da_nop;
//                 $cell = new html_table_cell(number_format($no, 0, ',', '.'));
//             } else {
//                 $cell = new html_table_cell(0);
//             }
//             $row->cells[] = $cell;
//             if ($tong_so_tin_chi * $hoc_phi_mot_tin_chi - $so_tien_da_nop > 0) {
//                 $cell = new html_table_cell(0);
//             } else {
//                 $du = $so_tien_da_nop - $tong_so_tin_chi * $hoc_phi_mot_tin_chi;
//                 $cell = new html_table_cell(number_format($du, 0, ',', '.'));
//             }
//             $row->cells[] = $cell;
//             $table->data[] = $row;
//             // $rightrows[$userid . '_' . $t] = $row;
//             if (!$mon_hoc_dang_ky) {
//                 $t++;
//                 continue;
//             }

//             foreach ($mon_hoc_dang_ky as $key => $mon_hoc) {
//                 $row = new html_table_row();
//                 $cell = new html_table_cell(date("H:i:s d/m/Y", $mon_hoc->ngay_tao));
//                 $row->cells[] = $cell;
//                 // $cell = new html_table_cell($ma_hoc_vien);
//                 // $row->cells[] = $cell;
//                 $cell = new html_table_cell($ten_tai_khoan);
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell($fullname);
//                 $row->cells[] = $cell;
//                 // $cell = new html_table_cell('Ngày sinh');
//                 // $row->cells[] = $cell;
//                 $cell = new html_table_cell(th_hoa_don_get_ten_nganh($mon_hoc->id_nganh));
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell($mon_hoc->ma_mon);
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell($mon_hoc->ten_mon);
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell($mon_hoc->so_tin_chi);
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell(number_format($mon_hoc->hoc_phi_mot_tin_chi, 0, ',', '.'));
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell(number_format($mon_hoc->so_tin_chi * $mon_hoc->hoc_phi_mot_tin_chi, 0, ',', '.'));
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell($mon_hoc->ma_dang_ky);
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell();
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell();
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell();
//                 $row->cells[] = $cell;
//                 $cell = new html_table_cell();
//                 $row->cells[] = $cell;
//                 // $rightrows[$userid . '_' . $mon_hoc->ma_mon] = $row;
//                 $table->data[] = $row;
//                 $hoc_phi_mot_tin_chi = $mon_hoc->hoc_phi_mot_tin_chi;
//                 $tong_so_tin_chi += $mon_hoc->so_tin_chi;
//                 $so_tien_da_nop = $mon_hoc->so_tien_da_nop;
//                 $t++;
//             }
//         }
//         $table->attributes['style'] = "width: 100%; text-align:center;";
//         $table->align[0] = 'center';

//         $html2 = html_writer::table($table);
//     } else {
//         $html2 = 'Chưa có Học viên nào nộp Hóa đơn nên chưa có Danh sách';
//     }

//     return $html2;
// }
function th_hoa_don_danh_sach_dang_ky_hoc($dot_mo_mon_id, $contextid, $component, $filearea) {

    global $DB, $CFG;

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
            $cell = new html_table_cell(count($mon_hoc_dang_ky) . " môn");
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
        $html2 = get_string('no_students_invoice', 'local_th_register');
    }

    return $html2;
}