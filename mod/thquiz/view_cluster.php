<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_thquizcluster.
 *
 * @package     mod_thquizcluster
 * @copyright   2023 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once $CFG->dirroot . '/mod/quiz/locallib.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->libdir.'/completionlib.php';
require_once $CFG->dirroot.'/course/format/lib.php';
require_once $CFG->dirroot.'/mod/thquiz/quizcluster/attemptlib.php';
require_once $CFG->dirroot.'/mod/thquiz/renderer.php';
require_once($CFG->dirroot.'/mod/thquiz/quizcluster/locallib.php');

global $CFG, $DB, $PAGE;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);
$cm_cluster_id = $id;

// Activity instance id.
$t = optional_param('t', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('thquiz', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('thquiz', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('thquiz', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('thquiz', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/thquiz/view_cluster.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$accessmanager = get_accessmanager_cluster($cm_cluster_id);
if($accessmanager->is_rule_seb()){
    $PAGE->set_pagelayout('secure');
    $PAGE->blocks->show_only_fake_blocks();
}

$quiz_listening = $moduleinstance->quiz_listening;
$quiz_reading = $moduleinstance->quiz_reading;
$quiz_writing = $moduleinstance->quiz_writing;
$quiz_speaking = $moduleinstance->quiz_speaking;

if ($quiz_listening || $quiz_reading || $quiz_writing || $quiz_speaking) {
    
    // Tạo mảng, loại bỏ các giá trị bị rỗng
    $cmid_quiz_raw = array($quiz_listening, $quiz_reading, $quiz_writing, $quiz_speaking);
    $cmid_quiz = array_filter($cmid_quiz_raw); 
    
    echo $OUTPUT->header();
    $i = 0;
    
    foreach($cmid_quiz as $cmid){
        $i++;
        if ($cmid) {
            if (!$cm = get_coursemodule_from_id('quiz', $cmid)) {
                print_error('invalidcoursemodule');
            }
        }

        echo '<div id="modal'.$i.'" class="modal-quizcluster" data-modal style="display:none;">
                <div class="modal-quizcluster-content">
                    <span class="close" data-close>&times;</span>
                    <div class="modal-cluster-dynamic-body" 
                         data-cmid="'.$cm->id.'" 
                         data-cmclusterid="'.$cm_cluster_id.'" 
                         data-contextid="'.$modulecontext->id.'">
                    </div>
                </div>
            </div>';
    }

    // ĐOẠN HTML VẼ CÁC NÚT NGUYÊN BẢN
    echo '<div class="exam-detail">
            <div class="practice-list__card">
                <div class="practice-list__items flex-grid">';
                
    $j = 1; 

    if ($quiz_listening) {
        echo '      <div class="practice-item -blue">
                        <span class="practice-item__icon -listening"></span>
                        <h6 class="practice-item__title">Listening</h6>
                        <button data-modal-target="#modal'.$j.'" class="practice-item__btn iot-gribt -blue -listening"><i class="fa-solid fa-bolt"></i> Làm bài</button>
                    </div>';
        $j++;
    }

    if ($quiz_reading) {
        echo '      <div class="practice-item -blue">
                        <span class="practice-item__icon -reading"></span>
                        <h6 class="practice-item__title">Reading</h6>
                        <button data-modal-target="#modal'.$j.'" class="practice-item__btn iot-gribt -green -reading"><i class="fa-solid fa-bolt"></i> Làm bài</button>
                    </div>';
        $j++;
    }

    if ($quiz_writing) {
        echo '      <div class="practice-item -blue">
                        <span class="practice-item__icon -writing"></span>
                        <h6 class="practice-item__title">Writing</h6>
                        <button data-modal-target="#modal'.$j.'" class="practice-item__btn iot-gribt -orange -writing"><i class="fa-solid fa-bolt"></i> Làm bài</button>
                    </div>';
        $j++;
    }

    if ($quiz_speaking) {
        echo '      <div class="practice-item -blue">
                        <span class="practice-item__icon -speaking"></span>
                        <h6 class="practice-item__title">Speaking</h6>
                        <button data-modal-target="#modal'.$j.'" class="practice-item__btn iot-gribt -pink -speaking"><i class="fa-solid fa-bolt"></i> Làm bài</button>
                    </div>';
    }

    echo '      </div>
            </div>
        </div>';
    echo $OUTPUT->footer();

} else {
    echo $OUTPUT->header();
    echo 'Chưa chọn quiz cluster';
    echo $OUTPUT->footer();
}
?>

<script type="text/javascript">
require([
    'jquery', 
    'core/fragment', 
    'core/templates'
], function($, Fragment, Templates) {
    $(document).ready(function() {
        
        function closeModalAndRestoreScroll(modal) {
            modal.css('display', 'none');
            modal.find('.modal-cluster-dynamic-body').html('');
            $('.moodle-dialogue-base').remove();
            
            $('body').css('overflow', '');
        }

        $('[data-modal-target]').on('click', function(e) {
            e.preventDefault();
            var targetId = $(this).attr('data-modal-target');
            var modal = $(targetId);
            
            if (modal.length > 0) {
                modal.css('display', 'block');
                $('body').css('overflow', 'hidden');
                
                modal.find('.modal-quizcluster-content').css({
                    'max-height': '70vh',
                    'overflow-y': 'auto'
                });
                
                var bodyContainer = modal.find('.modal-cluster-dynamic-body');
                var cmid = bodyContainer.data('cmid');
                var cmclusterid = bodyContainer.data('cmclusterid');
                var contextId = bodyContainer.data('contextid');
                
                $('.moodle-dialogue-base').remove();
                
                bodyContainer.html('<div style="text-align:center; padding: 50px;"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i><p class="mt-3">Đang tải dữ liệu bài thi...</p></div>');
                
                Fragment.loadFragment('mod_thquiz', 'quiz_view', contextId, { cmid: cmid, cm_cluster_id: cmclusterid })
                    .done(function(html, js) {
                        Templates.replaceNodeContents(bodyContainer, html, js);
                    })
                    .fail(function() {
                        bodyContainer.html('<div class="alert alert-danger">Đã xảy ra lỗi khi tải cấu hình bài thi.</div>');
                    });
            }
        });

        // SỰ KIỆN ĐÓNG MODAL KHI BẤM NÚT DẤU X
        $(document).on('click', '[data-close]', function() {
            var modal = $(this).closest('.modal-quizcluster');
            closeModalAndRestoreScroll(modal);
        });

        // SỰ KIỆN ĐÓNG KHI NHẤN RA NGOÀI KHU VỰC NỀN
        $('.modal-quizcluster').on('click', function(e) {
            if (e.currentTarget === e.target) {
                closeModalAndRestoreScroll($(this));
            }
        });
    });
});
</script>


