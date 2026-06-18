<?php

defined('MOODLE_INTERNAL') || die();

class block_th_roadmap_report extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_th_roadmap_report');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $COURSE;
        $this->content = new stdClass;
        $context = context_course::instance($COURSE->id);
        if (has_capability('block/th_roadmap_report:view', $context)) {
            $this->content->text = get_string('content', 'block_th_roadmap_report');
            $this->content->footer = html_writer::link(
                $CFG->wwwroot . '/blocks/th_roadmap_report/view.php',
                get_string('view', 'block_th_roadmap_report')
            );
        } else {
            $this->content->footer = 'No Permission!';
        }
        return $this->content;
    }

    function has_config() {
        return true;
    }
}
