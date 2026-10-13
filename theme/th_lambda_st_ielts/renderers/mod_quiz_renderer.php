<?php
defined('MOODLE_INTERNAL') || die();

class theme_th_lambda_st_ielts_mod_quiz_renderer extends theme_th_lambda_st_mod_quiz_renderer
{


    public function review_page(
        quiz_attempt $attemptobj,
        $slots,
        $page,
        $showall,
        $lastpage,
        mod_quiz_display_options $displayoptions,
        $summarydata
    ) {
        global $PAGE;

        $this->render_attempt_lock_hotspot();

        $PAGE->requires->js_init_code("
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('th-quiz-review');
            });
        ");

        $output = (string) parent::review_page(
            $attemptobj,
            $slots,
            $page,
            $showall,
            $lastpage,
            $displayoptions,
            $summarydata
        );

        $output = $this->wrap_review_questions_for_split($output);

        return $this->render_review_page_css()
            . $this->render_split_columns_assets()
            . $output;
    }

    private function wrap_review_questions_for_split(string $html): string
    {
        if (stripos($html, 'que') === false) {
            return $html;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="th-review-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $questionnodes = $xpath->query(
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' que ')]"
        );

        if (!$questionnodes || $questionnodes->length === 0) {
            return $html;
        }

        $nodelist = iterator_to_array($questionnodes);
        $topnodes = [];
        foreach ($nodelist as $node) {
            $isnested = false;
            foreach ($nodelist as $other) {
                if ($other !== $node && $this->dom_node_contains($other, $node)) {
                    $isnested = true;
                    break;
                }
            }
            if (!$isnested) {
                $topnodes[] = $node;
            }
        }

        if (empty($topnodes)) {
            return $html;
        }

        $firstnode = $topnodes[0];
        $parent = $firstnode->parentNode;
        if (!$parent) {
            return $html;
        }

        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('id', 'th-quiz-questions');
        $wrapper->setAttribute('class', 'th-quiz-questions-wrapper');

        $parent->insertBefore($wrapper, $firstnode);

        foreach ($topnodes as $node) {
            // appendChild trên node đã tồn tại trong DOM sẽ tự move (không copy).
            $wrapper->appendChild($node);
        }

        $root = $dom->getElementById('th-review-root');
        if (!$root) {
            return $html;
        }

        $innerhtml = '';
        foreach ($root->childNodes as $child) {
            $innerhtml .= $dom->saveHTML($child);
        }

        return $innerhtml;
    }

    private function dom_node_contains(DOMNode $ancestor, DOMNode $node): bool
    {
        $parent = $node->parentNode;
        while ($parent) {
            if ($parent === $ancestor) {
                return true;
            }
            $parent = $parent->parentNode;
        }
        return false;
    }

   private function render_review_page_css(): string
{
    return html_writer::tag('style', '
        body.th-quiz-review #page {
            margin: 0 auto !important;
            max-width: 100% !important;
        }
        [role="main"] .generaltable.generalbox.quizreviewsummary {
            margin-top: 10px !important;
        }
        [role="main"] .submitbtns {
            display: none !important;
            overflow: hidden;
        }      
    ');
}

    public function render_attempt_lock_hotspot()
    {
        global $PAGE;

        $PAGE->requires->js_init_code("
        (function () {

            var ANSWER_AREA_SELECTOR = '.qtype_essay_editor, .qtype_essay_response';

            function isInsideAnswerArea(el) {
                return !!(el.closest && el.closest(ANSWER_AREA_SELECTOR));
            }

            function lock() {

                document.querySelectorAll('.hotspot-editable').forEach(function(el) {
                    if (isInsideAnswerArea(el)) {
                        return;
                    }
                    el.setAttribute('contenteditable', 'false');
                    el.style.pointerEvents = 'none';
                });

                document.querySelectorAll('[contenteditable=\"true\"]').forEach(function(el) {
                    if (isInsideAnswerArea(el)) {
                        return;
                    }
                    el.setAttribute('contenteditable', 'false');
                });

                document.querySelectorAll('.editor_atto_toolbar').forEach(function(el) {
                    if (isInsideAnswerArea(el)) {
                        return;
                    }
                    el.style.display = 'none';
                });
            }

            var lockScheduled = false;
            function scheduleLock() {
                if (lockScheduled) {
                    return;
                }
                lockScheduled = true;
                requestAnimationFrame(function () {
                    lockScheduled = false;
                    lock();
                });
            }

            const observer = new MutationObserver(function () {
                scheduleLock();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            document.addEventListener('DOMContentLoaded', lock);

        })();
    ");
    }

    public function render_quiz_time_limit(quiz_attempt $attemptobj)
    {
        global $PAGE;

        $timelimit = (int) $attemptobj->get_quiz()->timelimit;
        if ($timelimit <= 0) {
            return;
        }

        $minutes = (int) round($timelimit / 60);
        $PAGE->requires->js_init_code("window.thQuizTimeLimitMinutes = {$minutes};");
    }


    public function render_essay_word_limits(quiz_attempt $attemptobj, array $slots)
    {
        global $PAGE;

        $limits = [];
        foreach ($slots as $slot) {
            $qa       = $attemptobj->get_question_attempt($slot);
            $question = $qa->get_question();

            if ($question->qtype->name() !== 'essay') {
                continue;
            }

            $min = (int)($question->minwordlimit ?? 0);
            $max = (int)($question->maxwordlimit ?? 0);

            if ($min > 0 || $max > 0) {
                $limits[$slot] = ['min' => $min, 'max' => $max];
            }
        }

        if (empty($limits)) {
            return;
        }

        $json = json_encode($limits);
        $PAGE->requires->js_init_code("window.thEssayWordLimits = {$json};");
    }


    public function summary_page($attemptobj, $displayoptions)
    {
        $output  = $this->render_ielts_header($attemptobj->get_quiz()->name,0,0,0, false);
        $output .= $this->render_summary_page_css();
        $output .= parent::summary_page($attemptobj, $displayoptions);
        return $output;
    }

    private function render_summary_page_css(): string
    {
        return html_writer::tag('style', '
            [role="main"] .table-responsive,
            [role="main"] h2,
            [role="main"] h3,
            [role="main"] #quiz-timer-wrapper,
            [role="main"] .singlebutton {
                display: none !important;
            }
            [role="main"] {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                min-height: calc(100vh - 160px);
                text-align: center;
                gap: 1rem;
            }
            [role="main"] .submitbtns.mdl-align {
                width: auto;
                margin: 0;
            }
        ');
    }

    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage)
    {

        global $DB;
        $output = '';

        $quizid = $attemptobj->get_quiz()->id;

        $ieltssettings = $DB->get_record(
            'local_th_ieltsquizaudio',
            [
                'quizid' => $quizid
            ]
        );

        $default_audio_resume_offset = get_config('theme_th_lambda_st_ielts', 'audio_resume_offset');
        $resumeoffset = ($ieltssettings) ? (int)$ieltssettings->resumeoffset : $default_audio_resume_offset;

        $attemptid = $attemptobj->get_attemptid();
        
        // Header giống mẫu (logo IELTS + tên bài thi + icon bên phải).
        $output .= $this->render_ielts_header(
        $attemptobj->get_quiz()->name,
        $resumeoffset,
        $default_audio_resume_offset,
        $attemptobj->get_attemptid());
        
        $output .= html_writer::start_tag('div', [
            'id' => 'th-ielts-header-audio',
            'data-attemptid' => $attemptid,
        ]);
        $output .= html_writer::end_tag('div');

        $this->page->requires->js('/theme/th_lambda_st_ielts/javascript/ielts_audio.js');

        $output .= html_writer::start_tag('form', [
            'action'         => new moodle_url($attemptobj->processattempt_url(), ['cmid' => $attemptobj->get_cmid()]),
            'method'         => 'post',
            'enctype'        => 'multipart/form-data',
            'accept-charset' => 'utf-8',
            'id'             => 'responseform',
        ]);
        $output .= html_writer::start_tag('div');

        $slots = array_values(array_unique($slots));

        $questionshtml = '';
        foreach ($slots as $slot) {
            $questionshtml .= $attemptobj->render_question(
                $slot,
                false,
                $this,
                $attemptobj->attempt_url($slot, $page),
                $this
            );
        }

        $output .= html_writer::tag('div', $questionshtml, [
            'id'    => 'th-quiz-questions',
            'class' => 'th-quiz-questions-wrapper',
        ]);

        $output .= $this->render_split_columns_assets();

        $this->render_essay_word_limits($attemptobj, $slots);
        $this->render_quiz_time_limit($attemptobj);

        $navmethod = $attemptobj->get_quiz()->navmethod;
        $output   .= $this->attempt_navigation_buttons(
            $page,
            $attemptobj->is_last_page($page),
            $navmethod
        );

        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'attempt',   'value' => $attemptobj->get_attemptid()]);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'thispage',  'value' => $page, 'id' => 'followingpage']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'nextpage',  'value' => $nextpage]);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'timeup',    'value' => '0', 'id' => 'timeup']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey',   'value' => sesskey()]);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'scrollpos', 'value' => '', 'id' => 'scrollpos']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'slots',     'value' => implode(',', $attemptobj->get_active_slots($page))]);

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');
        $output .= $this->connection_warning();
        $this->render_attempt_lock_hotspot();
        return $output;
    }
    public function render_split_columns_assets()
    {
        $output = '';

        $cssurl = new moodle_url('/theme/th_lambda_st_ielts/style/resize.css');
        $output .= html_writer::empty_tag('link', [
            'rel'  => 'stylesheet',
            'type' => 'text/css',
            'href' => $cssurl->out(false),
        ]);

        // Echo thẳng thẻ <script src> vào chuỗi output thay vì dùng
        // $PAGE->requires->js(), vì hàm đó chỉ xuất ra JS ở footer của
        // trang. Ở review_page(), hàm này được gọi SAU khi
        // parent::review_page() đã render (và có thể đã flush) xong
        // footer, nên $PAGE->requires->js() gọi lúc đó sẽ bị lỡ mất,
        // script không bao giờ xuất hiện trong HTML trả về. Echo thẳng
        // thẻ <script> đảm bảo nó luôn có mặt trong chuỗi trả về, không
        // phụ thuộc thời điểm gọi.
        $jsurl = new moodle_url('/theme/th_lambda_st_ielts/javascript/resize.js');
        $output .= html_writer::tag('script', '', [
            'src' => $jsurl->out(false),
        ]);

        return $output;
    }


    public function render_ielts_header($quizname = '', $resumeoffset = 0, $default_audio_resume_offset = 0, $attemptid = 0, $showbanner = true)
    {

        // ── FIX: chặn trình duyệt tự nhảy (native fragment scroll) tới
        //    #hash NGAY TỪ ĐẦU. Trước đây trình duyệt tự cuộn tới phần tử
        //    có id khớp #hash trước khi ielts_header.js/resize.js kịp
        //    chạy - nhưng trình duyệt không biết header cố định
        //    (.th-ielts-header) nên cuộn quá đà, đẩy nội dung lên che mất
        //    banner. Sau đó resize.js khoá overflow:hidden lên html/body
        //    để dồn cuộn cho 2 cột trái/phải, nhưng MỘT KHI overflow đã
        //    hidden thì không còn cách nào set lại scrollY bằng JS nữa ->
        //    trang "đứng hình" đúng ngay vị trí lệch đó.
        //    Giải pháp triệt để: xoá #hash khỏi URL bằng history.replaceState
        //    NGAY LÚC parser vừa chạy tới đây (luôn nằm TRƯỚC phần tử đích
        //    ở dưới trang trong luồng HTML) - nhờ vậy trình duyệt không
        //    còn hash nào để tự nhảy tới nữa. Giá trị hash gốc được lưu
        //    tạm vào window.thPendingHash để ielts_header.js tự cuộn thủ
        //    công, hoàn toàn kiểm soát được, sau khi layout (tách cột/portal
        //    hoá) đã ổn định.
        $output = '<script>' .
            '(function(){' .
            'if (window.location.hash && window.location.hash.length > 1) {' .
            'window.thPendingHash = window.location.hash;' .
            'if (window.history && window.history.replaceState) {' .
            'window.history.replaceState(null, "", window.location.pathname + window.location.search);' .
            '}' .
            '}' .
            '})();' .
            '</script>';

        // Use the same underlying logo storage as the inherited th_lambda_st theme.
        $parentlogo = theme_config::load('lambda')->setting_file_url('logo', 'logo');
        $cssurl  = new moodle_url('/theme/th_lambda_st_ielts/style/ielts_header.css');

        $output .= html_writer::empty_tag('link', [
            'rel'  => 'stylesheet',
            'type' => 'text/css',
            'href' => $cssurl->out(false),
        ]);

        $output .= html_writer::start_tag('header', ['class' => 'th-ielts-header']);

        // Bên trái: logo + tên bài thi (quiz name).
        $output .= html_writer::start_tag('div', ['class' => 'th-ielts-header-left']);
        if (!empty($parentlogo)) {
            $output .= html_writer::empty_tag('img', [
                'src'   => $parentlogo,
                'alt'   => 'Site logo',
                'class' => 'th-ielts-header-logo',
            ]);
        }

        $output .= html_writer::start_tag('div', ['class' => 'th-ielts-header-info']);
        $output .= html_writer::tag('span', s($quizname), ['class' => 'th-ielts-header-title']);
        $output .= html_writer::tag('div', '', [
            'id' => 'th-ielts-header-audio',
            'class' => 'th-ielts-header-audio',
            'data-resumeoffset' => $resumeoffset,
            'data-default-audio-resume-offset' => $default_audio_resume_offset,
            'data-attemptid' => $attemptid,
        ]);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        // Bên phải: slot chứa nút Back (được JS chuyển vào) + các icon.
        $output .= html_writer::start_tag('div', ['class' => 'th-ielts-header-right']);
        $output .= html_writer::tag('span', '', ['class' => 'th-ielts-header-back-slot']);
        $output .= html_writer::tag('span', '', ['class' => 'th-ielts-header-icon']);
        $output .= html_writer::tag('span', '', ['class' => 'th-ielts-header-icon']);
        $output .= html_writer::tag('span', '', ['class' => 'th-ielts-header-icon']);
        $output .= html_writer::tag('span', '', ['class' => 'th-ielts-header-icon th-ielts-header-icon-edit']);
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('header');

        if ($showbanner) {
            $output .= html_writer::start_tag('div', ['id' => 'th-part-banner', 'class' => 'th-part-banner']);
            $output .= html_writer::tag('span', '', ['class' => 'th-part-banner-title']);
            $output .= html_writer::tag('span', '', ['class' => 'th-part-banner-desc']);
            $output .= html_writer::end_tag('div');
        }

        $this->page->requires->js('/theme/th_lambda_st_ielts/javascript/ielts_header.js');

        return $output;
    }

    // =========================================================================

    public function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free')
    {
        $arrowLeft = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>';
        $arrowRight = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
        </svg>';

        $output  = '';
        $output .= html_writer::start_tag('div', ['class' => 'th-custom-nav-buttons']);

        $output .= html_writer::start_tag('label', [
            'class' => 'th-nav-btn mod_quiz-prev-nav',
            'title' => get_string('navigateprevious', 'quiz'),
        ]);
        $output .= $arrowLeft;
        $output .= html_writer::empty_tag('input', [
            'type'  => 'submit',
            'name'  => 'previous',
            'value' => get_string('navigateprevious', 'quiz'),
            'style' => 'display:none',
            'id'    => 'mod_quiz-prev-nav',
        ]);
        $this->page->requires->js_call_amd('core_form/submit', 'init', ['mod_quiz-prev-nav']);
        $output .= html_writer::end_tag('label');

        $output .= html_writer::start_tag('label', [
            'class' => 'th-nav-btn mod_quiz-next-nav',
            'title' => $lastpage ? get_string('endtest', 'quiz') : get_string('navigatenext', 'quiz'),
        ]);
        $output .= $arrowRight;
        $output .= html_writer::empty_tag('input', [
            'type'  => 'submit',
            'name'  => 'next',
            'value' => $lastpage ? get_string('endtest', 'quiz') : get_string('navigatenext', 'quiz'),
            'style' => 'display:none',
            'id'    => 'mod_quiz-next-nav',
        ]);
        $this->page->requires->js_call_amd('core_form/submit', 'init', ['mod_quiz-next-nav']);
        $output .= html_writer::end_tag('label');

        $output .= html_writer::end_tag('div');
        return $output;
    }

    // =========================================================================

    public function navigation_panel(quiz_nav_panel_base $panel)
    {
        $thallowedpagetypes = ['mod-quiz-attempt', 'mod-quiz-summary', 'mod-quiz-review'];
        if (!in_array($this->page->pagetype, $thallowedpagetypes, true)) {
            return parent::navigation_panel($panel);
        }

        $issummary = ($this->page->pagetype === 'mod-quiz-summary');
        $isreview  = ($this->page->pagetype === 'mod-quiz-review');
        $attemptid = optional_param('attempt', 0, PARAM_INT);
        $cmid      = $this->page->cm ? (int) $this->page->cm->id : optional_param('cmid', 0, PARAM_INT);

        $this->page->requires->js_init_call(
            'M.mod_quiz.nav.init',
            null,
            false,
            quiz_get_js_module()
        );

        $output = '';

        // Trang review (xem lại bài đã nộp) dùng "Finish"/"Hoàn thành",
        // trang attempt (đang làm bài) dùng "Submit"/"Nộp bài".
        $submittext = $isreview
            ? get_string('quizfinish', 'theme_th_lambda_st_ielts')
            : get_string('quizsubmit', 'theme_th_lambda_st_ielts');

        $output .= '<script>'
            . 'window.thQuizAttemptId = ' . $attemptid . ';'
            . 'window.thQuizCmid = ' . $cmid . ';'
            . 'window.thQuizIsSummaryPage = ' . ($issummary ? 'true' : 'false') . ';'
            . 'window.thQuizIsReviewPage = ' . ($isreview ? 'true' : 'false') . ';'
            . 'window.thQuizSubmitText = ' . json_encode($submittext) . ';'
            . '</script>';

        $this->page->requires->css('/theme/th_lambda_st_ielts/style/quiz_footer.css');

        if ($issummary) {
            $output .= '<div class="th-ielts-footer" id="th-ielts-footer">';
            $output .= '<div id="th-footer-parts-container" style="display:flex;align-items:stretch;flex:1 1 0;min-width:0;overflow:hidden;"></div>';
            $output .= '</div>';

            $this->page->requires->js('/theme/th_lambda_st_ielts/javascript/quiz_footer.js');

            return $output;
        }

        $endbits = $panel->render_end_bits($this);
        $buttons = $panel->get_question_buttons();

        $slotpagemap = [];
        if ($attemptid) {
            try {
                $navattemptobj = \quiz_attempt::create($attemptid);
                foreach ($navattemptobj->get_slots() as $navslot) {
                    $slotpagemap[$navslot] = $navattemptobj->get_question_page($navslot);
                }
            } catch (\Exception $e) {
                $slotpagemap = [];
            }
        }
        $output .= '<script>window.thQuizSlotPages = ' . json_encode($slotpagemap) . ';</script>';

        $output .= '<div id="th-nav-source">';
        foreach ($buttons as $button) {
            $output .= $this->render($button);
        }
        $output .= '</div>';
        $output .= '<div class="th-ielts-footer" id="th-ielts-footer">';
        $output .= '<div id="th-footer-parts-container" style="display:flex;align-items:stretch;flex:1 1 0;min-width:0;overflow:hidden;"></div>';
        $output .= '<div class="th-footer-finish th-footer-finish-text">' . $endbits . '</div>';
        $output .= '</div>';

        $this->page->requires->js('/theme/th_lambda_st_ielts/javascript/quiz_footer.js');

        return $output;
    }
}