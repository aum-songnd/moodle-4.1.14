<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/outputfactories.php');

class th_lambda_st_ielts_renderer_factory extends theme_overridden_renderer_factory {

    public function get_renderer(moodle_page $page, $component, $subtype = null, $target = null) {
        global $CFG;

        error_log("=== get_renderer called: component={$component} subtype={$subtype} ===");

        if ($component === 'qtype_multichoice' && $subtype === 'multi') {
            error_log("=== MATCH multichoice multi ===");
            require_once($CFG->dirroot . '/question/type/rendererbase.php');
            require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');
            require_once($CFG->dirroot . '/theme/th_lambda_st_ielts/classes/output/qtype_multichoice_renderer.php');
            return new theme_th_lambda_st_ielts_qtype_multichoice_multi_renderer($page, $target);
        }

        if ($component === 'qtype_thvstepcluster' && $subtype === null) {
            error_log("=== MATCH thvstepcluster ===");
            require_once($CFG->dirroot . '/question/type/thvstepcluster/classes/output/renderer.php');
            require_once($CFG->dirroot . '/theme/th_lambda_st_ielts/classes/output/qtype_thvstepcluster_renderer.php');
            return new theme_th_lambda_st_ielts_qtype_thvstepcluster_renderer($page, $target);
        }

        return parent::get_renderer($page, $component, $subtype, $target);
    }
}