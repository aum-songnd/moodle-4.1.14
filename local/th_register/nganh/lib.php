<?php
function local_th_register_nganh_controls(context $context, moodle_url $currenturl) {
    $tabs = array();
    $currenttab = 'view';
    $view = new moodle_url('/local/th_register/nganh/index.php');

    if (has_capability('local/th_register:view', $context)) {
        $addurl = new moodle_url('/local/th_register/nganh/index.php');
        $tabs[] = new tabobject('view', $addurl, get_string('all'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view';
        }
    }
    if (has_capability('local/th_register:view', $context)) {
        $addurl = new moodle_url('/local/th_register/nganh/edit.php');
        $tabs[] = new tabobject('edit', $addurl, get_string('add'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'edit';
        }
    }
    if (has_capability('local/th_register:view', $context)) {
        $addurl = new moodle_url('/local/th_register/nganh/add.php');
        $tabs[] = new tabobject('add', $addurl, get_string('them_nganh_bulk', 'local_th_register'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'add';
        }
    }

    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

function th_register_check_add_nganh($lists) {
    global $DB, $USER, $CFG;

    $checked = new stdClass();
    $checked->error = array();
    $checked->add = array();
    $checked->valid_time_found = 0;

    if (!empty($lists)) {

        foreach ($lists as $k => $tr) {
            $line = $k + 2;
            $list = explode(",", $tr);
            $ma_nganh = trim($list[0]);
            $ten_nganh = trim($list[1]);
            $hoc_phi_mot_tin_chi = trim($list[2]);
            if ($ma_nganh == '') {
                $checked->error[] = get_string('error_empty_ma_nganh', 'local_th_register', ['ma_nganh' => $ma_nganh, 'line' => $line]);
                continue;
            }
            if (strlen($ma_nganh) > 10) {
                $checked->error[] = get_string('error_ma_nganh_too_long', 'local_th_register',[
                    'ma_nganh' => $ma_nganh,
                    'line' => $line
                ]);
                continue;
            }
            if ($ten_nganh == '') {
                $checked->error[] = get_string('error_empty_ten_nganh', 'local_th_register',['line' => $line]);
                continue;
            }
            if (strlen($ten_nganh) > 45) {
                $checked->error[] = get_string('error_ten_nganh_too_long', 'local_th_register', ['ten_nganh' => $ten_nganh, 'line' => $line]);
                continue;
            }
            if ($hoc_phi_mot_tin_chi == '') {
                $checked->error[] = get_string('error_empty_hoc_phi', 'local_th_register', ['line' => $line]);
                continue;
            }
            if (!is_numeric($hoc_phi_mot_tin_chi)) {
                $checked->error[] = get_string('error_invalid_hoc_phi', 'local_th_register', $line);
                continue;
            }
            if ($hoc_phi_mot_tin_chi <= 0) {
                $checked->error[] = get_string('error_hoc_phi_must_be_greater_than_zero', 'local_th_register', ['line' => $line]);
                continue;
            }
            if (strlen($hoc_phi_mot_tin_chi) > 10) {
                $checked->error[] = get_string('error_hoc_phi_too_long', 'local_th_register', ['line' => $line]);
                continue;
            }
            $sql = "SELECT * FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'";
            $check = $DB->get_record_sql($sql);

            if (!empty($check)) {
                $checked->error[] = get_string('error_nganh_exists', 'local_th_register',[
                    'ma_nganh' => $ma_nganh,
                    'line' => $line
                ]); 
            } else {
                $checked->valid_time_found += 1;
                $record = new stdClass();
                $record->ma_nganh = $ma_nganh;
                $record->ten_nganh = $ten_nganh;
                $record->hoc_phi_mot_tin_chi = $hoc_phi_mot_tin_chi;
                $checked->add[] = $record;
            }
        }
    }
    return $checked;
}

function th_display_table_add_nganh($data_override) {
    global $DB;
    $table = new html_table();
    $table->head = array(get_string('no.', 'local_th_register'),
        get_string('ma_nganh', 'local_th_register'),
        get_string('ten_nganh', 'local_th_register'),
        get_string('hoc_phi_mot_tin_chi', 'local_th_register'),
        get_string('status'));
    $stt = 0;
    foreach ($data_override as $k => $data) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;

        $cell = new html_table_cell($data->ma_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ten_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->hoc_phi_mot_tin_chi);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span', get_string('se_duoc_them', 'local_th_register'),
            array('class' => 'badge badge-success'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
    $html = html_writer::table($table);
    return $html;
}

function th_register_table_add_nganh_error($errors) {
    $table1 = new html_table($errors);
    $table1->head = array('STT', get_string('hints', 'local_th_register'));
    $stt = 0;
    foreach ($errors as $k => $error) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($error);
        $row->cells[] = $cell;
        $table1->data[] = $row;
    }
    $html1 = html_writer::table($table1);
    return $html1;
}

function th_register_check_update_nganh($lists) {
    global $DB, $USER, $CFG;

    $checked = new stdClass();
    $checked->error = array();
    $checked->update = array();
    $checked->no_update = array();
    $checked->valid_time_found = 0;

    if (!empty($lists)) {

        foreach ($lists as $k => $tr) {
            $line = $k + 2;
            $list = explode(",", $tr);
            $ma_nganh = trim($list[0]);
            $ten_nganh = trim($list[1]);
            $hoc_phi_mot_tin_chi = trim($list[2]);
            if ($ma_nganh == '') {
                $checked->error[] = get_string('error_empty_ma_nganh', 'local_th_register', ['ma_nganh' => $ma_nganh, 'line' => $line]);
                continue;
            }
            if (strlen($ma_nganh) > 10) {
                $checked->error[] =    $checked->error[] = get_string('error_ma_nganh_too_long', 'local_th_register',[
                    'ma_nganh' => $ma_nganh,
                    'line' => $line
                ]);
                continue;
            }
            if ($ten_nganh == '') {
                $checked->error[] = get_string('error_empty_ten_nganh', 'local_th_register',['line' => $line]);
                continue;
            }
            if (strlen($ten_nganh) > 45) {
                $checked->error[] =  $checked->error[] = get_string('error_ten_nganh_too_long', 'local_th_register', 
                ['ten_nganh' => $ten_nganh, 'line' => $line]);
                continue;
            }
            if ($hoc_phi_mot_tin_chi == '') {
                $checked->error[] = get_string('error_empty_hoc_phi', 'local_th_register', ['line' => $line]);
                continue;
            }
            if (!is_numeric($hoc_phi_mot_tin_chi)) {
                $checked->error[] =  get_string('error_invalid_hoc_phi', 'local_th_register', $line);
                continue;
            }
            if ($hoc_phi_mot_tin_chi <= 0) {
                $checked->error[] = get_string('error_hoc_phi_must_be_greater_than_zero', 'local_th_register', ['line' => $line]);
                continue;
            }
            if (strlen($hoc_phi_mot_tin_chi) > 10) {
                $checked->error[] = get_string('error_credit_fee_length', 'local_th_register', $line);
                continue;
            }
            $sql = "SELECT * FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'";
            $check = $DB->get_record_sql($sql);

            if (empty($check)) {
                $checked->error[] = get_string('error_major_not_exist', 'local_th_register', ['ten_nganh' => $ten_nganh, 'ma_nganh' => $ma_nganh, 'line' => $line]);
            } else {
                $checked->valid_time_found += 1;
                $record = new stdClass();
                $record->id = $check->id;
                $record->ma_nganh = $ma_nganh;
                $record->ten_nganh = $ten_nganh;
                $record->hoc_phi_mot_tin_chi = $hoc_phi_mot_tin_chi;
                if ($DB->record_exists('th_nganh_hoc', ['ma_nganh' => $ma_nganh, 'ten_nganh' => $ten_nganh, 'hoc_phi_mot_tin_chi' => $hoc_phi_mot_tin_chi])) {
                    $checked->no_update[] = $record;
                } else {
                    $checked->update[] = $record;
                }
            }
        }
    }
    return $checked;
}

function th_register_table_update_nganh($update, $no_update) {
    global $DB;
    $table = new html_table();
    $table->head = array(get_string('no.', 'local_th_register'),
        get_string('ma_nganh', 'local_th_register'),
        get_string('ten_nganh', 'local_th_register'),
        get_string('hoc_phi_mot_tin_chi', 'local_th_register'),
        get_string('status'));
    $stt = 1;
    foreach ($update as $data) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;

        $cell = new html_table_cell($data->ma_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ten_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->hoc_phi_mot_tin_chi);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span', get_string('se_duoc_cap_nhat', 'local_th_register'),
            array('class' => 'badge badge-success'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
    foreach ($no_update as $data) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;

        $cell = new html_table_cell($data->ma_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ten_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->hoc_phi_mot_tin_chi);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span',
            get_string('khong_cap_nhat', 'local_th_register'),
            array('class' => 'badge badge-secondary'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
    $html = html_writer::table($table);
    return $html;
}

function th_register_check_add_and_update_nganh($lists) {
    global $DB, $USER, $CFG;

    $checked = new stdClass();
    $checked->add = array();
    $checked->update = array();
    $checked->no_update = array();
    $checked->valid_time_found = 0;

    if (!empty($lists)) {

        foreach ($lists as $k => $tr) {
            $line = $k + 2;
            $list = explode(",", $tr);
            $ma_nganh = trim($list[0]);
            $ten_nganh = trim($list[1]);
            $hoc_phi_mot_tin_chi = trim($list[2]);
            if ($ma_nganh == '') {
                $checked->error[] = get_string('error_empty_ma_nganh', 'local_th_register', ['ma_nganh' => $ma_nganh, 'line' => $line]);
                continue;
            }
            if (strlen($ma_nganh) > 10) {
                $checked->error[] = get_string('error_ma_nganh_too_long', 'local_th_register',['ma_nganh' => $ma_nganh, 'line' => $line]);
                continue;
            }
            if ($ten_nganh == '') {
                $checked->error[] = get_string('error_empty_ten_nganh', 'local_th_register',['line' => $line]);
                continue;
            }
            if (strlen($ten_nganh) > 45) {
                $checked->error[] = get_string('error_ten_nganh_too_long', 'local_th_register', ['ten_nganh' => $ten_nganh, 'line' => $line]);
                continue;
            }
            if ($hoc_phi_mot_tin_chi == '') {
                $checked->error[] = get_string('error_empty_hoc_phi', 'local_th_register', ['line' => $line]);
                continue;
            }
            if (!is_numeric($hoc_phi_mot_tin_chi)) {
                $checked->error[] = get_string('error_invalid_hoc_phi', 'local_th_register', $line);
                continue;
            }
            if ($hoc_phi_mot_tin_chi <= 0) {
                $checked->error[] = get_string('error_hoc_phi_must_be_greater_than_zero', 'local_th_register', ['line' => $line]);
                continue;
            }
            if (strlen($hoc_phi_mot_tin_chi) > 10) {
                $checked->error[] = get_string('error_hoc_phi_too_long', 'local_th_register', ['line' => $line]);
                continue;
            }
            $sql = "SELECT * FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'";
            $check = $DB->get_record_sql($sql);

            if (empty($check)) {
                $checked->valid_time_found += 1;
                $record = new stdClass();
                $record->ma_nganh = $ma_nganh;
                $record->ten_nganh = $ten_nganh;
                $record->hoc_phi_mot_tin_chi = $hoc_phi_mot_tin_chi;
                $checked->add[] = $record;

            } else {

                $checked->valid_time_found += 1;
                $record = new stdClass();
                $record->ma_nganh = $ma_nganh;
                $record->ten_nganh = $ten_nganh;
                $record->hoc_phi_mot_tin_chi = $hoc_phi_mot_tin_chi;
                if ($DB->record_exists('th_nganh_hoc', ['ma_nganh' => $ma_nganh, 'ten_nganh' => $ten_nganh, 'hoc_phi_mot_tin_chi' => $hoc_phi_mot_tin_chi])) {
                    $checked->no_update[] = $record;
                } else {
                    $record->id = $check->id;
                    $checked->update[] = $record;
                }
            }
        }
    }
    return $checked;
}

function th_register_table_add_and_update_nganh($add, $update, $no_update) {
    global $DB;
    $table = new html_table();
    $table->head = array(get_string('no.', 'local_th_register'),
        get_string('ma_nganh', 'local_th_register'),
        get_string('ten_nganh', 'local_th_register'),
        get_string('hoc_phi_mot_tin_chi', 'local_th_register'),
        get_string('status'));
    $stt = 0;
    foreach ($add as $data) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ma_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ten_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->hoc_phi_mot_tin_chi);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span', get_string('se_duoc_them', 'local_th_register'),
            array('class' => 'badge badge-success'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
    foreach ($update as $data) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ma_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ten_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->hoc_phi_mot_tin_chi);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span', get_string('se_duoc_cap_nhat', 'local_th_register'),
            array('class' => 'badge badge-success'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
    foreach ($no_update as $data) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ma_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ten_nganh);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->hoc_phi_mot_tin_chi);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span',
            get_string('khong_cap_nhat', 'local_th_register'),
            array('class' => 'badge badge-secondary'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
    $html = html_writer::table($table);
    return $html;
}
