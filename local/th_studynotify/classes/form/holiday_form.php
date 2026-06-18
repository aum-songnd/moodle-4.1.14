<?php
namespace local_th_studynotify\form;

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;
require_once($CFG->libdir . '/formslib.php');

class holiday_form extends \moodleform {

    protected function definition() {
        global $DB;

        $mform  = $this->_form;
        $record = $this->_customdata['record'] ?? null;

        // Hidden action
        $mform->addElement('hidden', 'action', optional_param('action', 'add', PARAM_ALPHA));
        $mform->setType('action', PARAM_ALPHA);

        // Hidden id
        $mform->addElement('hidden', 'id', $record->id ?? 0);
        $mform->setType('id', PARAM_INT);

        // Hidden sesskey
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_RAW);

        // ===== ĐỢT MỞ MÔN: DISTINCT THEO STARTDATE, CHỈ HIỂN THỊ NGÀY =====
        $sql = "SELECT DISTINCT c.startdate
                  FROM {course} c
                  JOIN {enrol} e ON e.courseid = c.id
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
                 WHERE c.visible = 1
                   AND c.startdate > 0
              ORDER BY c.startdate DESC";

        $rows = $DB->get_records_sql($sql);

        $options = [0 => get_string('holiday_batch_choose', 'local_th_studynotify')];
        foreach ($rows as $row) {
            $label = date('d/m/Y', $row->startdate);
            // value = startdate (timestamp)
            $options[$row->startdate] = $label;
        }

        $mform->addElement(
            'select',
            'batchstart',
            get_string('holiday_batch', 'local_th_studynotify'),
            $options
        );
        $mform->setType('batchstart', PARAM_INT);
        $mform->addRule('batchstart', null, 'required', null, 'client');

        // ===== Ngày bắt đầu / kết thúc nghỉ lễ =====
        $mform->addElement('date_selector', 'timestart',
            get_string('holiday_start', 'local_th_studynotify'));
        $mform->setType('timestart', PARAM_INT);
        $mform->addRule('timestart', null, 'required');

        $mform->addElement('date_selector', 'timeend',
            get_string('holiday_end', 'local_th_studynotify'));
        $mform->setType('timeend', PARAM_INT);
        $mform->addRule('timeend', null, 'required');

        if (!$record) {
            $mform->setDefault('timestart', time());
            $mform->setDefault('timeend', time());
            $mform->setDefault('batchstart', 0);
        }

        // Mô tả
        $mform->addElement('text', 'description', get_string('description'));
        $mform->setType('description', PARAM_TEXT);

        $this->add_action_buttons();

        if ($record) {
            $this->set_data($record);
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['batchstart']) || (int)$data['batchstart'] === 0) {
            $errors['batchstart'] =
                get_string('holiday_batch_required', 'local_th_studynotify');
        }

        if (!empty($data['timestart']) && !empty($data['timeend']) &&
            $data['timeend'] < $data['timestart']) {
            $errors['timeend'] =
                get_string('holiday_date_error', 'local_th_studynotify');
        }

        return $errors;
    }
}
