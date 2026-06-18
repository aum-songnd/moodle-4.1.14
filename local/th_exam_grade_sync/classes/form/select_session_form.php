<?php
namespace local_th_exam_grade_sync\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class select_session_form extends \moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $categoryidnumber = get_config('local_th_exam_grade_sync', 'categoryidnumber');

        if (empty($categoryidnumber)) {
            $mform->addElement(
                'static',
                'nocategoryconfig',
                '',
                get_string('nocategoryconfig', 'local_th_exam_grade_sync')
            );
            return;
        }

        $category = $DB->get_record(
            'course_categories',
            ['idnumber' => $categoryidnumber],
            '*',
            IGNORE_MULTIPLE
        );

        if (!$category) {
            $mform->addElement(
                'static',
                'nocategory',
                '',
                get_string('categorynotfound', 'local_th_exam_grade_sync', $categoryidnumber)
            );
            return;
        }

        // =======================================
        // LẤY DANH SÁCH CATEGORY + SUB-CATEGORY
        // =======================================
        $catids = [$category->id];

        // Lấy tất cả sub-category dựa vào path.
        // Ví dụ: category có path = '/1/3' thì subcat có path bắt đầu '/1/3/'.
        $subcats = $DB->get_records_select_menu(
            'course_categories',
            'path LIKE :path',
            ['path' => $category->path . '/%'],
            '',
            'id, id'
        );

        if (!empty($subcats)) {
            $catids = array_merge($catids, array_keys($subcats));
        }

        list($catinsql, $catparams) = $DB->get_in_or_equal($catids, SQL_PARAMS_NAMED, 'catid');

        /* =========================
         * RADIO: CHỌN KIỂU
         * ========================= */
        $radioarray = [];
        $radioarray[] = $mform->createElement(
            'radio',
            'selecttype',
            '',
            get_string('selectbydate', 'local_th_exam_grade_sync'),
            'bydate'
        );
        $radioarray[] = $mform->createElement(
            'radio',
            'selecttype',
            '',
            get_string('selectbycourse', 'local_th_exam_grade_sync'),
            'bycourse'
        );

        $mform->addGroup(
            $radioarray,
            'selecttypegroup',
            get_string('selecttype', 'local_th_exam_grade_sync'),
            [' '],
            false
        );

        $mform->setDefault('selecttype', 'bydate');

        /* =========================
         * OPTION 1: CHỌN ĐỢT THI
         * ========================= */
        $datesql = "
            SELECT DISTINCT startdate
              FROM {course}
             WHERE category $catinsql
               AND startdate > 0
             ORDER BY startdate DESC
        ";

        $dates = $DB->get_records_sql($datesql, $catparams);

        $dateoptions = [];
        foreach ($dates as $d) {
            $dateoptions[$d->startdate] = userdate($d->startdate, '%d/%m/%Y');
        }

        $mform->addElement(
            'autocomplete',
            'examdate',
            get_string('selectexamdate', 'local_th_exam_grade_sync'),
            $dateoptions
        );
        $mform->setType('examdate', PARAM_INT);
        $mform->hideIf('examdate', 'selecttype', 'neq', 'bydate');

        /* =========================
         * OPTION 2: CHỌN PHÒNG THI
         * ========================= */
        $coursesql = "
            SELECT id, fullname, startdate
              FROM {course}
             WHERE category $catinsql
             ORDER BY startdate DESC, timecreated DESC
        ";

        $courses = $DB->get_records_sql($coursesql, $catparams);

        $courseoptions = [];
        foreach ($courses as $c) {
            $courseoptions[$c->id] =
                $c->fullname . ' (' . userdate($c->startdate, '%d/%m/%Y') . ')';
        }

        $mform->addElement(
            'autocomplete',
            'examcourseid',
            get_string('selectexamcourse', 'local_th_exam_grade_sync'),
            $courseoptions
        );
        $mform->setType('examcourseid', PARAM_INT);
        $mform->hideIf('examcourseid', 'selecttype', 'neq', 'bycourse');

        $this->add_action_buttons(false, get_string('viewlist', 'local_th_exam_grade_sync'));
    }

    /**
     * Chuẩn hóa dữ liệu:
     * nếu chọn phòng thi → map sang examdate
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return null;
        }

        return $data;
    }

    public function validation($data, $files) {
        $errors = [];
    
        if ($data['selecttype'] === 'bydate') {
            if (empty($data['examdate'])) {
                $errors['examdate'] = get_string('required');
            }
        }
    
        if ($data['selecttype'] === 'bycourse') {
            if (empty($data['examcourseid'])) {
                $errors['examcourseid'] = get_string('required');
            }
        }
    
        return $errors;
    }
    
}