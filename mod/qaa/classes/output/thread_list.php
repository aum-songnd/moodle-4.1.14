<?php
namespace mod_qaa\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

class thread_list implements renderable, templatable {
    /** @var array */
    protected $rows;
    public function __construct(array $rows) { $this->rows = $rows; }

    public function export_for_template(renderer_base $output): array {
        return ['rows' => $this->rows];
    }
}
