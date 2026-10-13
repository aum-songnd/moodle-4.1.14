<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/rendererbase.php');
require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');

class theme_th_lambda_st_ielts_qtype_multichoice_multi_renderer
    extends qtype_multichoice_multi_renderer {

    public function formulation_and_controls(
        question_attempt $qa,
        question_display_options $options
    ) {
        $question = $qa->get_question();

        $correctcount = 0;
        foreach ($question->answers as $answer) {
            if ($answer->fraction > 0) {
                $correctcount++;
            }
        }

        $html = parent::formulation_and_controls($qa, $options);

        $inputname = $qa->get_qt_field_name('choice0');
        $prefix = str_replace('choice0', '', $inputname);

        $js = "
        <script>
        (function() {
            var limit = {$correctcount};
            var prefix = '{$prefix}';

            function applyLimit() {
                var anyCheckbox = document.querySelector('input[name=\"' + prefix + 'choice0\"]');
                if (!anyCheckbox) {
                    console.log('checkbox not found, prefix=' + prefix);
                    return;
                }

                var container = anyCheckbox.closest('.que.multichoice');
                if (!container) return;
                if (container.getAttribute('data-limit-init')) return;
                container.setAttribute('data-limit-init', '1');

                var checkboxes = container.querySelectorAll('input[type=\"checkbox\"]');
                console.log('container:', container.id, 'limit:', limit, 'checkboxes:', checkboxes.length);
                if (!checkboxes.length) return;

                function updateDisabled() {
                    var checked = container.querySelectorAll('input[type=\"checkbox\"]:checked');
                    checkboxes.forEach(function(c) {
                        if (!c.checked) {
                            c.disabled = checked.length >= limit;
                        } else {
                            c.disabled = false;
                        }
                    });
                }

                updateDisabled();
                checkboxes.forEach(function(cb) {
                    cb.addEventListener('change', updateDisabled);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyLimit);
            } else {
                applyLimit();
            }
        })();
        </script>";

        return $html . $js;
    }
}