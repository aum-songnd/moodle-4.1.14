<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class th_syncexamqbank_form2 extends moodleform {

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
        'pathlike' => $category->path . '/%'
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
        // Nút xem lịch sử đồng bộ (đổi sang submit)
        $buttonarray[] = $mform->createElement(
            'submit',
            'viewhistory',
            get_string('submit')
        );

        // Nút quay lại trang đồng bộ
        $syncurl = new moodle_url('/local/th_syncexamqbank/view.php');
        $buttonarray[] = $mform->createElement(
            'button',
            'backtosync',
            get_string('backtosyncpage', 'local_th_syncexamqbank'),
            ['onclick' => "window.location.href='{$syncurl}'"]
        );

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
