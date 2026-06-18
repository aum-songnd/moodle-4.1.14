<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class th_syncexamqbank_form1 extends moodleform {

    function definition() {
        global $DB, $CFG, $OUTPUT;
        $mform = $this->_form;

        // Tìm danh mục khóa học có idnumber = 'THI'
        $category = $DB->get_record('course_categories', ['idnumber' => 'THI']);

        if (!$category) {
            $message = html_writer::div(
                $OUTPUT->notification(
                    get_string('categorynotfound', 'local_th_syncexamqbank'),
                    'warning'
                ),
                'mt-3'
            );
            $mform->addElement('html', $message);
            return;
        }

        // Lấy danh sách startdate từ các khóa học trong danh mục đó
        // $records = $DB->get_records_sql("
        //     SELECT DISTINCT startdate 
        //     FROM {course} 
        //     WHERE category = :catid AND startdate > 0
        //     ORDER BY startdate ASC
        // ", ['catid' => $category->id]);

        // Lấy danh sách startdate từ các khóa học trong danh mục THI và tất cả danh mục con
        $records = $DB->get_records_sql("
        SELECT DISTINCT c.startdate
        FROM {course} c
        JOIN {course_categories} cc ON cc.id = c.category
        WHERE (cc.id = :catid OR cc.path LIKE :pathlike)
        AND c.startdate > 0
        ORDER BY c.startdate ASC
        ", [
        'catid'    => $category->id,
        'pathlike' => $category->path . '/%'   // tất cả category có path bắt đầu từ category THI
        ]);

        $dotoptions = [];
        foreach ($records as $record) {
            $dotoptions[$record->startdate] = userdate($record->startdate, '%d/%m/%Y');
        }

        if (empty($dotoptions)) {
            $mform->addElement('static', 'nodate', '', get_string('nocoursestartdate', 'local_th_syncexamqbank'));
            return;
        }

        // Trường chọn đợt thi
        $options = [
            'multiple' => false,
            'noselectionstring' => get_string('selectadate', 'local_th_syncexamqbank'),
        ];

        $mform->addElement(
            'autocomplete',
            'startdate',
            get_string('selectexamdate', 'local_th_syncexamqbank'),
            $dotoptions,
            $options
        );
        $mform->addRule('startdate', null, 'required', null, 'client');

        // --- Hai nút hành động ---
        $buttonarray = [];

        // Nút đồng bộ
        $buttonarray[] = $mform->createElement('submit', 'syncbutton', get_string('syncquestions', 'local_th_syncexamqbank'));

        // Nút xem lịch sử
        $historyurl = new moodle_url('/local/th_syncexamqbank/history.php');
        $buttonarray[] = $mform->createElement('button', 'viewhistory', get_string('viewhistory', 'local_th_syncexamqbank'),
            ['onclick' => "window.location.href='{$historyurl}'"]
        );

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
