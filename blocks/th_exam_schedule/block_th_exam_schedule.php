<?php
defined('MOODLE_INTERNAL') || die();

class block_th_exam_schedule extends block_base {

    public function init() {
        $this->title = get_string('myschedule', 'block_th_exam_schedule');
    }

    public function has_config() {
        return true;
    }

    /**
     * Moodle sẽ không render block nếu has_content() trả false.
     */
    public function has_content() {
        return $this->should_show_block();
    }

    /**
     * Ẩn block nếu đã qua ngày thi cuối cùng (dựa theo studentcode).
     * Admin (manageimports) luôn hiển thị.
     */
    private function should_show_block(): bool {
        global $DB, $USER;

        // Chưa login/guest thì vẫn cho hiện block (để hiện "pleaselogin").
        if (!isloggedin() || isguestuser()) {
            return true;
        }

        // Admin luôn thấy block.
        if (has_capability('block/th_exam_schedule:manageimports', context_system::instance())) {
            return true;
        }

        // Lấy studentcode từ user profile field theo config shortname.
        $shortname = trim((string)get_config('block_th_exam_schedule', 'student_code'));
        if ($shortname === '') {
            // Chưa cấu hình -> không ẩn.
            return true;
        }

        $studentcode = (string)$DB->get_field_sql("
            SELECT uid.data
              FROM {user_info_data} uid
              JOIN {user_info_field} uif ON uif.id = uid.fieldid
             WHERE uid.userid = :userid
               AND uif.shortname = :shortname
        ", [
            'userid' => $USER->id,
            'shortname' => $shortname,
        ]);
        $studentcode = trim($studentcode);

        // Không có mã học viên -> không ẩn (để user biết thiếu).
        if ($studentcode === '') {
            return true;
        }

        // Lấy ngày thi cuối cùng.
        $maxexamdate = (int)$DB->get_field_sql("
            SELECT MAX(e.examdate)
              FROM {block_thexamschedule_exam} e
             WHERE e.studentcode = :studentcode
        ", ['studentcode' => $studentcode]);

        // Chưa có lịch thi -> không ẩn.
        if (empty($maxexamdate)) {
            return true;
        }

        // So sánh với đầu ngày hôm nay (timezone user).
        $todaystart = usergetmidnight(time());

        // Nếu ngày thi cuối cùng đã qua hết -> ẩn block.
        return ($maxexamdate >= $todaystart);
    }

    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            $this->content->text = get_string('pleaselogin', 'block_th_exam_schedule');
            return $this->content;
        }

        // Nếu tới đây mà should_show_block() = false thì thực tế Moodle đã không gọi render,
        // nhưng để chắc chắn thì return rỗng.
        if (!$this->should_show_block()) {
            $this->content->text = '';
            return $this->content;
        }

        // Hiển thị bảng lịch thi ngay trong block (giới hạn 5 dòng).
        $table = new \block_th_exam_schedule\output\table($USER->id, 10, true);
        $out = $table->render();

        // Link xem đầy đủ.
        $out .= html_writer::div(
            html_writer::link(
                new moodle_url('/blocks/th_exam_schedule/view.php'),
                get_string('viewmyschedule', 'block_th_exam_schedule')
            ),
            'mt-2'
        );

        // Link import cho admin.
        if (has_capability('block/th_exam_schedule:manageimports', context_system::instance())) {
            $out .= html_writer::div(
                html_writer::link(
                    new moodle_url('/blocks/th_exam_schedule/import_courses.php'),
                    get_string('importpage', 'block_th_exam_schedule')
                ),
                'mt-2'
            );
        }

        $this->content->text = $out;
        return $this->content;
    }

    public function applicable_formats() {
        return [
            'site' => true,
            'my' => true,
            'course-view' => true,
        ];
    }
}
