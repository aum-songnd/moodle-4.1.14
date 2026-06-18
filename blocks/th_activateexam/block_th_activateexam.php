<?php
class block_th_activateexam extends block_base {
    
    public function init() {
        $this->title = get_string('heading', 'block_th_activateexam');
    }
    public function has_config() {
        return true;
    }
    
    public function get_content() {
        global $COURSE, $USER, $OUTPUT, $PAGE, $DB;
    
        if ($this->content !== null) {
            return $this->content;
        }
    
        $this->content = new stdClass();
        $this->content->text = '';
    
        $context = context_course::instance($COURSE->id);
    
        if (!has_capability('block/th_activateexam:view', $context)) {
            return $this->content;
        }
    
        // Lấy trạng thái phê duyệt gần nhất
        $lastlog = $DB->get_records('block_th_activateexam_log', ['courseid' => $COURSE->id], 'timecreated DESC', '*', 0, 1);
        $approved = false;
    
        if ($lastlog) {
            $record = reset($lastlog);
            $approved = ($record->action === 'approve' && $record->oldavailability);
        }
    
        $approveurl = new moodle_url('/blocks/th_activateexam/approve.php',
            ['courseid' => $COURSE->id, 'action' => 'approve']);
        $unapproveurl = new moodle_url('/blocks/th_activateexam/approve.php',
            ['courseid' => $COURSE->id, 'action' => 'unapprove']);
    
        if (has_capability('block/th_activateexam:active', $context)) {
            if ($approved) {
                // Khi đã phê duyệt hiện nút bỏ phê duyệt
                $button1 = html_writer::link($unapproveurl, html_writer::tag('button',
                    '<i class="fa fa-times-circle"></i> ' . get_string('unapprove', 'block_th_activateexam'),
                    ['class' => 'btn-approve-status btn-unapprove-default']
                ));
                $button2 = html_writer::tag('button',
                    '<i class="fa fa-check-circle"></i> ' . get_string('approve', 'block_th_activateexam'),
                    ['class' => 'btn-approve-status btn-approve-active']
                );
            } else {
                // Khi chưa phê duyệt hiện nút phê duyệt
                $button1 = html_writer::tag('button',
                    '<i class="fa fa-times-circle"></i> ' . get_string('unapprove', 'block_th_activateexam'),
                    ['class' => 'btn-approve-status btn-unapprove-active']
                );
                $button2 = html_writer::link($approveurl, html_writer::tag('button',
                    '<i class="fa fa-check-circle"></i> ' . get_string('approve', 'block_th_activateexam'),
                    ['class' => 'btn-approve-status btn-approve-default']
                ));
            }
    
            $this->content->text .= $button1 . $button2;
        }
    
        $this->content->text .= html_writer::div(
            get_string('description', 'block_th_activateexam'),
            'description'
        );
    
        return $this->content;
    }

    public function instance_delete() {
        global $DB, $COURSE;

        // Xóa tất cả dữ liệu liên quan tới instance này.
        if (!empty($this->instance->id)) {
            $DB->delete_records('block_th_activateexam_log', [
                'courseid' => $COURSE->id
            ]);
        }

        return true; // Bắt buộc phải return true.
    }
     
}

