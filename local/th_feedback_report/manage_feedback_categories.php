<?php
require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($courseid);
require_capability('local/th_feedback_report:view', context_course::instance($courseid));

$baseurl = new moodle_url('/local/th_feedback_report/manage_feedback_categories.php');
$title_log = get_string('manageerrorcategories', 'local_th_feedback_report');

$PAGE->set_url($baseurl);
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title($title_log);
$PAGE->set_heading($title_log);
$PAGE->navbar->add($title_log, $baseurl);

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// URL cơ bản.
$baseurl = new moodle_url('/local/th_feedback_report/manage_feedback_categories.php');

// Lớp form để thêm/sửa.
class th_error_category_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;

        // Lấy danh sách các parentid có giá trị null.
        $parents = $DB->get_records_select_menu(
            'th_error_categories',
            'parentid IS NULL',
            null,
            'name ASC',
            'id, name'
        );

        // Thêm lựa chọn "Không có parent" vào danh sách.
        $parents = [0 => get_string('noparent', 'local_th_feedback_report')] + $parents;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('text', 'name', get_string('name', 'local_th_feedback_report'), ['size' => '50']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'code', get_string('code', 'local_th_feedback_report'), ['size' => '50']);
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');

        $mform->addElement('autocomplete', 'parentid', get_string('parentid', 'local_th_feedback_report'), $parents);
        $mform->setType('parentid', PARAM_INT);

        $mform->addElement('textarea', 'description', get_string('description', 'local_th_feedback_report'), ['rows' => 5, 'cols' => 50]);
        $mform->setType('description', PARAM_RAW);

        $this->add_action_buttons();
    }
}

// Xử lý các hành động.
if ($action === 'save' && confirm_sesskey()) {
    $mform = new th_error_category_form();
    if ($mform->is_cancelled()) {
        redirect($baseurl);
    } else if ($data = $mform->get_data()) {
        $record = (object)[
            'id' => $data->id,
            'parentid' => $data->parentid == 0 ? null : $data->parentid,
            'name' => $data->name,
            'description' => $data->description,
            'code' => $data->code,
        ];
        if ($record->id) {
            // Kiểm tra xem code có tồn tại không.
            $existingcode = $DB->get_field('th_error_categories', 'code', ['id' => $record->id]);
            if ($existingcode !== $record->code) {
                // Kiểm tra xem code mới có tồn tại không.
                if ($DB->record_exists('th_error_categories', ['code' => $record->code])) {
                    redirect($baseurl, get_string('codealreadyexists', 'local_th_feedback_report'), null, \core\output\notification::NOTIFY_WARNING);
                }
            }
            $DB->update_record('th_error_categories', $record);
            redirect($baseurl, get_string('recordupdated', 'local_th_feedback_report'));
        } else {
            // Kiểm tra xem code có tồn tại không trước khi thêm.
            if ($DB->record_exists('th_error_categories', ['code' => $record->code])) {
                redirect($baseurl, get_string('codealreadyexists', 'local_th_feedback_report'), null, \core\output\notification::NOTIFY_WARNING);
            }
            $DB->insert_record('th_error_categories', $record);
            redirect($baseurl, get_string('recordadded', 'local_th_feedback_report'));
        }
    }
} elseif ($action === 'delete' && confirm_sesskey() && $id) {

    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('th_error_categories', ['id' => $id]);
        redirect($baseurl, get_string('recorddeleted', 'local_th_feedback_report'));
    }
    $strheading = get_string('delete');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $record = $DB->get_record('th_error_categories', ['id' => $id], '*', MUST_EXIST);
    $yesurl = new moodle_url($baseurl, array('id' => $id, 'confirm' => 1, 'action' => 'delete', 'sesskey' => sesskey()));
    $message = get_string('deletecategoryconfirm', 'local_th_feedback_report', ['name' => $record->name]);
    echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_feedback_report/manage_feedback_categories.php'));
    echo $OUTPUT->footer();
    die;
}

// Lấy dữ liệu hiện tại.
$categories = $DB->get_records('th_error_categories');

// Hiển thị giao diện.
echo $OUTPUT->header();
if ($editcontrols = local_th_feedback_report_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading(get_string('manageerrorcategories', 'local_th_feedback_report'));

// Form thêm/sửa.
if ($action === 'edit' && $id) {
    $record = $DB->get_record('th_error_categories', ['id' => $id], '*', MUST_EXIST);
    $mform = new th_error_category_form(null, $record);
    $mform->set_data($record);
    $mform->display();
} else {
    $mform = new th_error_category_form();
    $mform->display();
}

// Hiển thị bảng dữ liệu hiện có.
$table = new html_table();
$table->head = [
    get_string('index', 'local_th_feedback_report'),
    get_string('parentid', 'local_th_feedback_report'),
    get_string('name', 'local_th_feedback_report'),
    get_string('code', 'local_th_feedback_report'),
    get_string('description', 'local_th_feedback_report'),
    get_string('actions', 'local_th_feedback_report')
];

$stt = 0;
foreach ($categories as $category) {
    $stt++;
    $editurl = new moodle_url($baseurl, ['action' => 'edit', 'id' => $category->id]);
    $deleteurl = new moodle_url($baseurl, ['action' => 'delete', 'id' => $category->id, 'sesskey' => sesskey()]);
    $actions = html_writer::link($editurl, get_string('edit', 'local_th_feedback_report')) . ' | ' .
               html_writer::link($deleteurl, get_string('delete', 'local_th_feedback_report'));
    $table->data[] = [$stt, $category->parentid ?? '-', $category->name, $category->code, $category->description ?? '-', $actions];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
