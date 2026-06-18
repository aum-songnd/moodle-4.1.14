<?php
namespace mod_qaa\output;

use renderable;
use templatable;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

class thread implements renderable, templatable {
    /** @var comment[] */
    public $comments;

    /**
     * @param comment[] $comments
     */
    public function __construct(array $comments) {
        $this->comments = $comments;
    }

    public function export_for_template(renderer_base $output): array {
        $arr = [];
        foreach ($this->comments as $c) {
            $arr[] = $c->export_for_template($output);
        }
        return ['comments' => $arr];
    }
}
