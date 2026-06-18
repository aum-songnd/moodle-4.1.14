<?php
defined('MOODLE_INTERNAL') || die();

class mod_qaa_renderer extends plugin_renderer_base {
    public function render_thread(\mod_qaa\output\thread $thread): string {
        return $this->render_from_template('mod_qaa/thread', $thread->export_for_template($this));
    }

    public function render_thread_list(\mod_qaa\output\thread_list $list): string {
        return $this->render_from_template('mod_qaa/thread_list', $list->export_for_template($this));
    }
}
