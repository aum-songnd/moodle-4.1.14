<?php
require '../../../config.php';
require_once $CFG->dirroot . '/local/th_register/hoa_don/th_register_hoa_don_form.php';
require_once $CFG->dirroot . '/local/th_register/hoa_don/lib.php';

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:register', context_course::instance($COURSE->id));

$dot_mo_mon_id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$action = optional_param('action', '', PARAM_RAW);
$userid = optional_param('userid', 0, PARAM_INT);

$context = context_system::instance();
$baseurl = new moodle_url('/local/th_register/hoa_don/edit.php?id=' . $dot_mo_mon_id);
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', get_string('list_paid_students', 'local_th_register'), current_language()));
$PAGE->navbar->add(get_string('manage_invoices', 'local_th_register'), $baseurl);
$PAGE->set_title(get_string('invoice_title', 'local_th_register'));

if (!$DB->record_exists('th_dot_mo_mon', array('id' => $dot_mo_mon_id))) {
    // khong co dot mo
    $PAGE->set_title(get_string('invoice_title', 'local_th_register'));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('invalid_course_batch', 'local_th_register'));
    echo $OUTPUT->footer();
    die;
}
$data = $DB->get_record('th_dot_mo_mon', array('id' => $dot_mo_mon_id));
$ma_dot_mo_mon = $data->ma_dot_mo_mon;
$ten_dot_mo = $data->ten_dot_mo;
$tu_ngay = $data->tu_ngay;
$den_ngay = $data->den_ngay;

$component = 'local_th_register';
$filearea = 'dot' . $dot_mo_mon_id;
$contextid = $context->id;

// if (has_capability('local/th_register:view', $context)) {
$html = th_hoa_don_table_tinh_trang_nop($dot_mo_mon_id, $tu_ngay, $den_ngay, 1);

// xac nhan hoc phi
if ($action == 'confirm' and has_capability('local/th_register:confirm_dong_hoc_phi', $context)) {

    if ($confirm and confirm_sesskey()) {
        $trang_thai_nop = $DB->get_field('th_hoa_don', 'trang_thai_nop',
            array(
                'userid_moodle' => $userid,
                'dot_mo_mon_id' => $dot_mo_mon_id,
            )
        );
        $trang_thai_nop = $trang_thai_nop ? 0 : 1;

        $DB->set_field('th_hoa_don', 'trang_thai_nop', $trang_thai_nop,
            array('userid_moodle' => $userid,
                'dot_mo_mon_id' => $dot_mo_mon_id,
            )
        );

        redirect($baseurl . '&action=list',
            get_string('success'), null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    if (!$user = $DB->get_record('user', array('id' => $userid))) {
        print_error('invaliduserid');
    }
    $fullname = html_writer::link(
        new moodle_url('/user/profile.php?id=' . $userid),
        $user->firstname . ' ' . $user->lastname
    );

    // $PAGE->set_heading("Xác nhận thanh toán");
    echo $OUTPUT->header();

    $yesurl = new moodle_url('/local/th_register/hoa_don/edit.php',
        array(
            'id' => $dot_mo_mon_id,
            'userid' => $userid,
            'confirm' => 1,
            'action' => 'confirm',
            'sesskey' => sesskey(),
        )
    );
    $trang_thai_nop = $DB->get_field('th_hoa_don', 'trang_thai_nop',
        array(
            'userid_moodle' => $userid,
            'dot_mo_mon_id' => $dot_mo_mon_id,
        )
    );

    $a = new stdClass();
    $a->fullname = $fullname;
    if ($trang_thai_nop == 1) {
        $message = get_string('confirm_unpaid', 'local_th_register', $a);
    } else {
        $message = get_string('confirm_paid', 'local_th_register', $a);
    }
    echo $OUTPUT->confirm($message, $yesurl, $baseurl . '&action=list');
    echo $OUTPUT->footer();
    die;
}

// download hoa don
if ($action == 'download' and has_capability('local/th_register:download_hoa_don', $context)) {

    th_hoa_don_download($ten_dot_mo, $contextid, $component, $filearea);
}
// danh sach nop hoa don
if ($action == 'list' and has_capability('local/th_register:confirm_dong_hoc_phi', $context)) {
    $strheading = get_string('list_paid_students', 'local_th_register') . $ten_dot_mo;
    $PAGE->set_title($strheading);
    $PAGE->navbar->add($strheading, $baseurl);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    echo th_hoa_don_danh_sach_nop_hoa_don($dot_mo_mon_id);
    echo $OUTPUT->footer();
    die;
}

if ($action == 'view' and has_capability('local/th_register:view_danh_sach_dong_hoc_phi', $context)) {
    $html2 = th_hoa_don_danh_sach_dang_ky_hoc($dot_mo_mon_id, $contextid, $component, $filearea);
}
// }

$userid = $USER->id;
$sql = "SELECT f.*,hd.so_tien_da_nop
            FROM {th_hoa_don} hd JOIN {files} f ON hd.link_anh_hoa_don = f.id
            WHERE hd.userid_moodle = $userid AND hd.dot_mo_mon_id = $dot_mo_mon_id";
$record = $DB->get_record_sql($sql);

if (has_capability('local/th_register:register', $context) and time() >= $tu_ngay and time() <= $den_ngay) {
    // Xoa hoa don
    // if ($action == 'delete' and $record) {
    //     $fs = get_file_storage();
    //     if ($confirm and confirm_sesskey()) {
    //         $itemid = $DB->get_record('files', array('id' => $record->id), 'itemid')->itemid;
    //         $fs->delete_area_files($contextid, $component, $filearea, $itemid);
    //         // $id = $DB->get_record('th_hoa_don', array('userid_moodle' => $USER->id, 'dot_mo_mon_id' => $dot_mo_mon_id))->id;
    //         $DB->delete_records('th_hoa_don', ['userid_moodle' => $USER->id, 'dot_mo_mon_id' => $dot_mo_mon_id]);
    //         redirect($baseurl, get_string('success'), null,
    //             \core\output\notification::NOTIFY_SUCCESS);
    //     }
    //     $strheading = get_string('delete');
    //     $PAGE->set_title($strheading);
    //     echo $OUTPUT->header();
    //     echo $OUTPUT->heading($strheading);

    //     $yesurl = new moodle_url('/local/th_register/hoa_don/edit.php', array('id' => $dot_mo_mon_id, 'confirm' => 1, 'action' => 'delete', 'sesskey' => sesskey()));
    //     $message = "Bạn có chắc chắn muốn xóa Hóa đơn";
    //     echo $OUTPUT->confirm($message, $yesurl, $baseurl);
    //     echo $OUTPUT->footer();
    //     die;
    // }

    // if (th_hoa_don_check_dang_ky($dot_mo_mon_id, $userid)) {
    //     $html = th_hoa_don_table_tinh_trang_nop($dot_mo_mon_id, $tu_ngay, $den_ngay, $record ? 1 : 0);
    // }
    if ($action == 'add') {

        $editform = new th_register_add_form($baseurl . '&action=' . 'add');
        if ($record) {
            $fileid = $record->id;
            $itemid = $record->itemid;
            $so_tien_da_nop = $record->so_tien_da_nop;

            if (empty($entry->id)) {
                $entry = new stdClass;
                $entry->id = null;
            }

            $draftitemid = file_get_submitted_draft_itemid('attachments');
            file_prepare_draft_area($draftitemid, $contextid, $component, $filearea, $itemid);
            $entry->attachments = $draftitemid;
            $entry->id = $dot_mo_mon_id;
            $entry->so_tien_da_nop = $so_tien_da_nop;
            $editform->set_data($entry);
            // print_object($entry);
        } else {
            $editform->set_data(['id' => $dot_mo_mon_id]);
        }
        if ($editform->is_cancelled()) {
            redirect($baseurl);

        } else if ($data = $editform->get_data()) {

            $draftitemid = $data->attachments;
            $so_tien_da_nop = $data->so_tien_da_nop;
            $so_tien_da_nop = str_replace(',', '', $so_tien_da_nop);
            $so_tien_da_nop = str_replace('.', '', $so_tien_da_nop);

            if (!$file = $DB->get_record_sql("SELECT itemid FROM {files} WHERE component=? AND filearea=? AND contextid=? ORDER BY itemid DESC LIMIT 1", array($component, $filearea, $contextid))) {
                $itemid = 1;
            } else {
                if ($record) {
                    $itemid = $record->itemid;
                } else {
                    $itemid = $file->itemid + 1;
                }
            }

            file_save_draft_area_files($draftitemid, $contextid, $component, $filearea, $itemid);
            if (!$record) {
                //them hoa_don
                $files = $DB->get_record_sql("
                        SELECT id FROM {files}
                        WHERE component=? AND filearea=? AND contextid=? AND itemid=? AND userid=? AND filename='.' ORDER BY id DESC",
                    array($component, $filearea, $contextid, $itemid, $userid));
                $record = new stdClass();
                $record->userid_moodle = $USER->id;
                $record->dot_mo_mon_id = $dot_mo_mon_id;
                $record->link_anh_hoa_don = $files->id;
                $record->so_tien_da_nop = $so_tien_da_nop;
                $record->trang_thai_nop = 0;
                $record->ngay_tao = time();
                $DB->insert_record('th_hoa_don', $record);
            } else {
                // cap nhat hoa_don
                $hoa_don = $DB->get_record('th_hoa_don', array('userid_moodle' => $USER->id, 'dot_mo_mon_id' => $dot_mo_mon_id));
                $hoa_don->so_tien_da_nop = $so_tien_da_nop;
                $DB->update_record('th_hoa_don', $hoa_don);
            }

            redirect($baseurl, get_string('success'), null,
                \core\output\notification::NOTIFY_SUCCESS);
        }

    }

}

echo $OUTPUT->header();
if (has_capability('local/th_register:register', $context)
    and th_hoa_don_check_dang_ky($dot_mo_mon_id, $userid) or has_capability('local/th_register:view', $context)) {
    $invoice_title = get_string('invoice_title', 'local_th_register');
    echo $OUTPUT->heading($invoice_title . ' ' . $ten_dot_mo);
} else {
    echo $OUTPUT->heading(get_string('register_before_payment', 'local_th_register'));
}
$html = th_hoa_don_table_tinh_trang_nop($dot_mo_mon_id, $tu_ngay, $den_ngay, $record ? 1 : 0);
echo $html;

if (isset($actions)) {
    echo $actions;
}
if (isset($html2)) {
    echo $html2;
}

if (isset($editform)) {
    echo $editform->display();
}
echo $OUTPUT->footer();
?>
<script type="text/javascript">

    $(document).ready(function() {
        if ($("input#id_so_tien_da_nop")){

            $("input#id_so_tien_da_nop").on('input',function(){
                this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
            });

            var text_input = $("input#id_so_tien_da_nop");
            var so_tien_da_nop = new Intl.NumberFormat('vnd').format(text_input.val());

            if (so_tien_da_nop.indexOf(".") != -1){
                 so_tien_da_nop = so_tien_da_nop.replace(/[.]/g, ",");
            }
            text_input.val(so_tien_da_nop);
            $("input#id_so_tien_da_nop").on('input',function(){
                so_tien_da_nop = text_input.val().replace(/[.]/g, ",");
                var tmp = text_input.val().replace(/,/g, "");
                var so_tien_da_nop = new Intl.NumberFormat('vnd').format(tmp);
                so_tien_da_nop = so_tien_da_nop.replace(/[.]/g, ",");
                text_input.val(so_tien_da_nop);
            });
        }
        function copyTextToClipboard() { 
            const element = document.getElementById('myElement'); 
            const range = document.createRange(); 
            range.selectNode(element); 
            window.getSelection().removeAllRanges(); 
            window.getSelection().addRange(range); 
            document.execCommand('copy'); 
            window.getSelection().removeAllRanges(); 
        } 
 
        const copyButton = document.getElementById('copyButton'); 
        copyButton.addEventListener('click', copyTextToClipboard);
    });
</script>
