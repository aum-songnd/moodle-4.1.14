<?php
require_once("$CFG->libdir/formslib.php");

class report_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Group report type
        $mform->addGroup($this->get_report_type_group(), 'actiongroup',
            get_string('reporttype', 'local_th_online_exam_manager'), [' '], false);
        $mform->setDefault('action', 'enroll');

        // Lấy action hiện tại để quyết định có hiện filtermode=user hay không
        $currentaction = optional_param('action', 'enroll', PARAM_ALPHA);
        if (!in_array($currentaction, ['enroll', 'result', 'resulthcct'], true)) {
            $currentaction = 'enroll';
        }

        // Group filter mode (resulthcct sẽ không có option user)
        $mform->addGroup($this->get_filter_mode_group($currentaction), 'filtermodegroup',
            get_string('filtermode', 'local_th_online_exam_manager'), [' '], false);
        $mform->setDefault('filtermode', 'category');

        // Courses
        $courses = $this->load_courses();
        $mform->addElement('autocomplete', 'courseids',
            get_string('selectcourses', 'local_th_online_exam_manager'), $courses, ['multiple' => true]);
        $mform->hideIf('courseids', 'filtermode', 'neq', 'category');

        // Examdate
        $mform->addElement('date_selector', 'examdate', get_string('selectexamdate', 'local_th_online_exam_manager'));
        $mform->hideIf('examdate', 'filtermode', 'neq', 'examdate');

        // Users
        $users = $this->load_users();
        $mform->addElement('autocomplete', 'userids',
            get_string('selectusers', 'local_th_online_exam_manager'), $users,
            ['multiple' => true, 'noselectionstring' => get_string('choose', 'moodle')]);

        // Ẩn userids nếu không chọn filtermode=user
        $mform->hideIf('userids', 'filtermode', 'neq', 'user');
        // Và nếu action=resulthcct thì luôn ẩn userids
        $mform->hideIf('userids', 'action', 'eq', 'resulthcct');

        // Submit
        $mform->addElement('submit', 'submitbutton', get_string('exportreport', 'local_th_online_exam_manager'));
        global $PAGE;

        $PAGE->requires->js_amd_inline("
        require(['jquery'], function($) {
            function toggleFiltermodeUser() {
                var action = $('input[name=\"action\"]:checked').val();

                var \$userInput = $('#id_filtermode_user');
                if (!\$userInput.length) return;

                var \$userLabel = \$userInput.closest('label');

                if (action === 'resulthcct') {
                    // Nếu đang chọn user -> ép về category
                    if (\$userInput.is(':checked')) {
                        $('#id_filtermode_category').prop('checked', true).trigger('change');
                    }
                    \$userInput.prop('disabled', true);
                    \$userLabel.hide();
                } else {
                    \$userInput.prop('disabled', false);
                    \$userLabel.show();
                }
            }

            $(document).on('change', 'input[name=\"action\"]', toggleFiltermodeUser);
            $(toggleFiltermodeUser);
        });
        ");


    }

    private function get_report_type_group(): array {
        return [
            $this->_form->createElement('radio', 'action', '', get_string('contestreport', 'local_th_online_exam_manager'), 'enroll'),
            $this->_form->createElement('radio', 'action', '', get_string('resultreport', 'local_th_online_exam_manager'), 'result'),
            $this->_form->createElement('radio', 'action', '', get_string('resultreport', 'local_th_online_exam_manager') . ' theo mẫu', 'resulthcct'),
        ];
    }

    // Nhận $action để ẩn option filtermode=user khi resulthcct
    private function get_filter_mode_group(string $action): array {
        $elements = [
            $this->_form->createElement('radio', 'filtermode', '', get_string('filterbycourse', 'local_th_online_exam_manager'), 'category'),
            $this->_form->createElement('radio', 'filtermode', '', get_string('filterbyexamdate', 'local_th_online_exam_manager'), 'examdate'),
        ];

        if ($action !== 'resulthcct') {
            $elements[] = $this->_form->createElement('radio', 'filtermode', '', get_string('filterbyuser', 'local_th_online_exam_manager'), 'user');
        }

        return $elements;
    }

    private function load_courses(): array {
        global $DB;

        $courses = [];

        $categoryidnumber = get_config('local_th_online_exam_manager', 'categoryidnumber');
        if (empty($categoryidnumber)) {
            return $courses;
        }

        $category = $DB->get_record(
            'course_categories',
            ['idnumber' => $categoryidnumber],
            'id',
            IGNORE_MISSING
        );

        if (!$category) {
            return $courses;
        }

        // Lấy category object
        $cat = \core_course_category::get($category->id);

        // Lấy toàn bộ course trong category + subcategory
        $courseobjects = $cat->get_courses(['recursive' => true]);

        foreach ($courseobjects as $course) {
            if ($course->visible) {
                $courses[$course->id] = $course->fullname;
            }
        }

        return $courses;
    }

    private function load_users(): array {
        global $DB;
        $userlist = [];

        $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
        if (!empty($setting_ma_hoc_vien)) {
            $arr_mahv = explode(',', $setting_ma_hoc_vien);
            $ma_hoc_vien1 = $arr_mahv[0];

            $users = $DB->get_records_sql("
                SELECT u.id, u.firstname, u.lastname, u.username, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, d.data as mhv
                  FROM {user} u
             LEFT JOIN {user_info_data} d ON u.id = d.userid
             LEFT JOIN {user_info_field} i ON i.id = d.fieldid
                 WHERE u.deleted = 0 AND u.suspended = 0 AND u.id <> 1
                   AND i.shortname = '$ma_hoc_vien1'
              ORDER BY lastname ASC, firstname ASC
            ");

            foreach ($users as $user) {
                $userlist[$user->id] = fullname($user) . ',' . $user->username . (!empty($user->mhv) ? ',' . $user->mhv : '');
            }
        } else {
            $users = $DB->get_records_sql("
                SELECT id, firstname, lastname, username, firstnamephonetic, lastnamephonetic, middlename, alternatename
                  FROM {user}
                 WHERE deleted = 0 AND suspended = 0 AND id <> 1
              ORDER BY lastname ASC, firstname ASC
            ");

            foreach ($users as $user) {
                $userlist[$user->id] = fullname($user) . ',' . $user->username;
            }
        }

        return $userlist;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Chặn resulthcct + filtermode=user
        if (($data['action'] ?? '') === 'resulthcct' && ($data['filtermode'] ?? '') === 'user') {
            $errors['filtermodegroup'] = get_string('invaliddata', 'error');
            return $errors;
        }

        if (($data['filtermode'] ?? '') === 'category' && empty($data['courseids'])) {
            $errors['courseids'] = get_string('required');
        }

        if (($data['filtermode'] ?? '') === 'user' && empty($data['userids'])) {
            $errors['userids'] = get_string('required');
        }

        return $errors;
    }
    
}
