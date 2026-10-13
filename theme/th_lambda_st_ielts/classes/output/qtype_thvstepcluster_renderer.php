<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/thvstepcluster/classes/output/renderer.php');

class theme_th_lambda_st_ielts_qtype_thvstepcluster_renderer extends qtype_thvstepcluster_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $html = parent::formulation_and_controls($qa, $options);

        $question_original = $qa->get_question();
        $usageid = $qa->get_usage_id();
        $slot    = $qa->get_slot();
        $outerid = $qa->get_outer_question_div_unique_id();
        $correctcountmap = [];
        // Đếm index i giống như renderer gốc (biến $i trong renderer gốc)
        $i = 0;
        foreach ($question_original->question_items_instance as $key => $question) {
            if ($question->get_type_name() !== 'multichoice') continue;

            $ismultiple = $question instanceof qtype_multichoice_multi_question;
            if (!$ismultiple) {
                $i++;
                continue;
            }

            // Đếm số đáp án đúng
            $correctcount = 0;
            foreach ($question->answers as $answer) {
                if ($answer->fraction > 0) $correctcount++;
            }
    // Lưu lại.
    $correctcountmap['answer' . $key] = $correctcount;
   
            // qid được tạo trong renderer gốc: $qa->get_outer_question_div_unique_id() . '-' . $i
            $qid = $outerid . '-' . $i;

            // Inject data-correct-count vào div có id=$qid
            $html = str_replace(
                'id="' . $qid . '" class="que multichoice',
                'id="' . $qid . '" class="que multichoice" data-correct-count="' . $correctcount . '"',
                $html
            );

            $i++;
        }

        // Inject JS một lần
        static $js_injected = false;
        if (!$js_injected) {
            $js_injected = true;
      $html .= "
<script>
(function() {
    function applyLimit() {
        document.querySelectorAll('.que.multichoice[data-correct-count]').forEach(function(container) {
            var limit = parseInt(container.getAttribute('data-correct-count'));
            if (!limit) return;
            if (container.getAttribute('data-limit-init')) return;
            container.setAttribute('data-limit-init', '1');

            var checkboxes = container.querySelectorAll('input[type=\"checkbox\"]');
            if (!checkboxes.length) return;

            console.log('container:', container.id, 'limit:', limit, 'checkboxes:', checkboxes.length);

            // Kiểm tra trạng thái ban đầu ngay khi init
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

            // Áp dụng ngay khi load (cho trường hợp đã có đáp án được chọn sẵn)
            updateDisabled();

            // Lắng nghe thay đổi
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', updateDisabled);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyLimit);
    } else {
        applyLimit();
    }
})();
</script>";
        }

$html .= '<script>
window.thClusterCorrectCount = Object.assign(
    window.thClusterCorrectCount || {},
    ' . json_encode($correctcountmap) . '
);

console.log("Cluster correct counts", window.thClusterCorrectCount);
</script>';
        return $html;
    }
}