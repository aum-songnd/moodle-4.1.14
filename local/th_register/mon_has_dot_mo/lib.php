<?php

function ds_dot_mo_mon()
{
    global $DB;
    $sql           = "SELECT * FROM {th_dot_mo_mon}";
    $ds_dot_mo_mon = $DB->get_records_sql($sql);

    if (!empty($ds_dot_mo_mon)) {
        foreach ($ds_dot_mo_mon as $id => $dot_mo_mon) {
            $ds[$id] = $dot_mo_mon->ten_dot_mo . ',' . $dot_mo_mon->ma_dot_mo_mon;
        }
        return $ds;
    }

}

function th_ds_mon()
{
    global $DB;
    $sql     = "SELECT * FROM {th_mon_hoc}";
    $mon_hoc = $DB->get_records_sql($sql);

    if (!empty($mon_hoc)) {
        foreach ($mon_hoc as $id => $mon) {
            $ds[$id] = $mon->ten_mon . ',' . $mon->ma_mon;
        }
        return $ds;
    }
    
}

function ten_dot_mo($id)
{
    global $DB;
    $sql        = "SELECT * FROM {th_dot_mo_mon} where id = '$id'";
    $dot_mo_mon = $DB->get_record_sql($sql);
    $ten_dot_mo = $dot_mo_mon->ten_dot_mo;
    return $ten_dot_mo;
}

function ten_mon($id)
{
    global $DB;
    $sql     = "SELECT * FROM {th_mon_hoc} where id = '$id'";
    $mon_hoc = $DB->get_record_sql($sql);
    $ten_mon = $mon_hoc->ten_mon;
    return $ten_mon;
}

function th_check_add_course($list_courses, $id_dot_mo, $ten_dot_mo)
{

    global $DB, $USER;

    $checkedcourses                     = new stdClass();
    $checkedcourses->error_messages     = array();
    $checkedcourses->courses            = array();
    $checkedcourses->add_courses        = array();
    $checkedcourses->update_courses     = array();
    $checkedcourses->valid_course_found = 0;

    if (!empty($list_courses)) {

        foreach ($list_courses as $k => $list_course) {
            $line   = $k + 2;
            $ma_mon = trim($list_course);
            $sql    = "SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon'";
            $course = $DB->get_record_sql($sql);

            if (empty($course)) {
                $checkedcourses->error_messages[] = get_string('error_course_not_found', 'local_th_register', ['ma_mon' => $ma_mon, 'line' => $line]);
            } else {
                $mon_hoc_id = $course->id;

                $sql2 = "SELECT * FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_hoc_id' AND dot_mo_mon_id = '$id_dot_mo'";

                $dot_mo_has_mon_hoc = $DB->get_record_sql($sql2);
                if (empty($dot_mo_has_mon_hoc)) {
                    $checkedcourses->valid_course_found += 1;
                    $data                = new stdClass();
                    $data->mon_hoc_id    = $mon_hoc_id;
                    $data->dot_mo_mon_id = $id_dot_mo;
                    $data->ngay_tao      = time();
                    $data->nguoi_tao     = $USER->id;
                    $data->ngay_sua      = null;
                    $data->nguoi_sua     = null;
                    $data->ten_mon       = $course->ten_mon;
                    $data->ma_mon        = $course->ma_mon;
                    $data->ten_dot_mo    = $ten_dot_mo;

                    $checkedcourses->courses[] = $data;
                } else {
                    $ten_mon                          = $course->ten_mon;
                    $checkedcourses->error_messages[] = get_string('error_course_exists_in_dot_mo', 'local_th_register', 
                    ['ten_mon' => $ten_mon, 'ma_mon' => $ma_mon, 'ten_dot_mo' => $ten_dot_mo, 'line' => $line]
                );
                }
            }
        }
    }
    return $checkedcourses;
}

function upload_add_course($courses)
{
    global $DB;
    foreach ($courses as $k => $course) {
        $dataobjects                = new stdClass();
        $dataobjects->dot_mo_mon_id = $course->dot_mo_mon_id;
        $dataobjects->mon_hoc_id    = $course->mon_hoc_id;
        $dataobjects->ngay_tao      = $course->ngay_tao;
        $dataobjects->nguoi_tao     = $course->nguoi_tao;
        $dataobjects->ngay_sua      = $course->ngay_sua;
        $dataobjects->nguoi_sua     = $course->nguoi_sua;

        $DB->insert_record('th_dot_mo_has_mon_hoc', $dataobjects, false);
    }
}

function th_display_table_error($errors)
{
    $table1       = new html_table($errors);
    $table1->head = array(
        get_string('no.', 'local_th_register'), 
        get_string('hints', 'local_th_register')
    );
    $stt          = 0;
    foreach ($errors as $k => $error) {
        $stt            = $stt + 1;
        $row            = new html_table_row();
        $cell           = new html_table_cell($stt);
        $row->cells[]   = $cell;
        $cell           = new html_table_cell($error);
        $row->cells[]   = $cell;
        $table1->data[] = $row;
    }
    $html1 = html_writer::table($table1);
    return $html1;
}

function th_display_table_upload_course($courses)
{
    global $DB;
    $table       = new html_table();
    $table->head = array(
        get_string('no.', 'local_th_register'),  
        get_string('ten_mon', 'local_th_register'), 
        get_string('ma_mon', 'local_th_register'),
        get_string('batch_opening', 'local_th_register'),
        get_string('status', 'local_th_register')
    );
    $stt         = 0;
    foreach ($courses as $k => $course) {
        $stt          = $stt + 1;
        $row          = new html_table_row();
        $cell         = new html_table_cell($stt);
        $row->cells[] = $cell;

        $cell         = new html_table_cell($course->ten_mon);
        $row->cells[] = $cell;
        $cell         = new html_table_cell($course->ma_mon);
        $row->cells[] = $cell;
        $cell         = new html_table_cell($course->ten_dot_mo);
        $row->cells[] = $cell;
        $cell         = new html_table_cell();
        $cell->text   = html_writer::tag('span', get_string('course_will_be_added', 'local_th_register'),
            array('class' => 'badge badge-success'));
        $row->cells[]  = $cell;
        $table->data[] = $row;
    }
    $html = html_writer::table($table);
    return $html;
}

function local_th_mon_has_dot_mo_controls(context $context, moodle_url $currenturl)
{
    $tabs       = array();
    $currenttab = 'view';
    $view       = new moodle_url('/local/th_register/mon_has_dot_mo/index.php');

    if (has_capability('local/th_register:view', $context)) {
        $addurl = new moodle_url('/local/th_register/mon_has_dot_mo/index.php');
        $tabs[] = new tabobject('view', $addurl, get_string('courses', 'local_th_register'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view';
        }
    }
    if (has_capability('local/th_register:view', $context)) {
        $addurl = new moodle_url('/local/th_register/mon_has_dot_mo/edit.php');
        $tabs[] = new tabobject('edit', $addurl, get_string('tab_add', 'local_th_register'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'edit';
        }
    }
    if (has_capability('local/th_register:view', $context)) {
        $addurl = new moodle_url('/local/th_register/mon_has_dot_mo/add.php');
        $tabs[] = new tabobject('add', $addurl, get_string('tab_add_bulk', 'local_th_register'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'add';
        }
    }

    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

function th_register_get_ten_mon_hoc($id_mon_hoc)
{
    global $DB;
    $sql     = "SELECT * FROM {th_mon_hoc} where id = '$id_mon_hoc'";
    $record  = $DB->get_record_sql($sql);
    $ten_mon = $record->ten_mon;
    return $ten_mon;
}

function th_register_get_ten_dot_mo_mon($id_dot_mo)
{
    global $DB;
    $sql        = "SELECT * FROM {th_dot_mo_mon} where id = '$id_dot_mo'";
    $record     = $DB->get_record_sql($sql);
    $ten_dot_mo = $record->ten_dot_mo;
    return $ten_dot_mo;
}
