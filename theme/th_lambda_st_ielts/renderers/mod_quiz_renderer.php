<?php
defined('MOODLE_INTERNAL') || die();

class theme_th_lambda_st_ielts_mod_quiz_renderer extends theme_th_lambda_st_mod_quiz_renderer {

    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {

        $output = '';

        $output .= html_writer::start_tag('form', [
            'action'         => new moodle_url($attemptobj->processattempt_url(), ['cmid' => $attemptobj->get_cmid()]),
            'method'         => 'post',
            'enctype'        => 'multipart/form-data',
            'accept-charset' => 'utf-8',
            'id'             => 'responseform',
        ]);
        $output .= html_writer::start_tag('div');

        $firstQ  = '';
        $remainQ = '';
        $ques    = 0;
        foreach ($slots as $slot) {
            $ques++;
            $rendered = $attemptobj->render_question(
                $slot,
                false,
                $this,
                $attemptobj->attempt_url($slot, $page),
                $this
            );
            if ($ques == 1) {
                $firstQ = $rendered;
            } else {
                $remainQ .= $rendered;
            }
        }

        $DOM = new DOMDocument;
        libxml_use_internal_errors(true);
        $DOM->loadHTML($firstQ);
        libxml_use_internal_errors(false);
        $item = $DOM->getElementById('th_mycustomwidth');

        if ($item) {
            $width        = (int) $item->getAttribute('value');
            $remain_width = 12 - $width;

            if (strpos($firstQ, 'thvstepcluster') !== false) {
                $grid = "<div class='row-fluid'>
                    <div class='span12 overflow-auto'>$firstQ</div>
                </div>";
            } else {
                $grid = "<div class='row-fluid'>
                    <div class='span$width overflow-auto' style='height: 90vh;'>$firstQ</div>
                    <div class='span$remain_width overflow-auto' style='height: 90vh;'>$remainQ</div>
                </div>";
            }
            $output .= $grid;

            $output .= '<style>
                .que.thvstepcluster {
                    display: grid;
                    grid-template-columns: ' . $width . 'fr ' . $remain_width . 'fr;
                    gap: 1rem;
                    overflow: hidden;
                }
                .que.thvstepcluster > .description {
                    grid-column: 1;
                    height: 90vh;
                    overflow: auto;
                    margin: 0 !important;
                    position: sticky;
                    top: 0;
                    align-self: start;
                }
                .que.thvstepcluster > .que:not(.description) {
                    grid-column: 2;
                    overflow-y: auto;
                    max-height: 100%;
                }
                .que.thvstepcluster > .content {
                    height: 90vh;
                    overflow: auto;
                }
            </style>';
        } else {
            $output .= $firstQ . $remainQ;
        }

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

        return $output;
    }

    // =========================================================================

    public function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free') {
        $arrowLeft = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>';
        $arrowRight = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
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

    public function navigation_panel(quiz_nav_panel_base $panel) {
        $this->page->requires->js_init_call(
            'M.mod_quiz.nav.init',
            null,
            false,
            quiz_get_js_module()
        );

        $endbits = $panel->render_end_bits($this);
        $buttons = $panel->get_question_buttons();

        $output = '';

        // =====================================================================
        // CSS
        // =====================================================================
        $output .= html_writer::tag('style', '

/* ===== HIDE MOODLE DEFAULT CHROME ===== */
.breadcrumb-nav, nav[aria-label="breadcrumb"], .breadcrumb,
.page-header-nav, #mod_quiz_navblock, .block_quiz_navblock,
[data-block="quiz_navblock"], .footerlinks, #page-footer, .row, #page-navbar, #page-header-nav,
.singlebutton, #quiz-navblock, .navbar, #page-header, .mt-5, .info {
    display: none !important;
}
#block-region-side-pre,
aside#block-region-side-pre,
.block-region[data-blockregion="side-pre"] { display: none !important; }
#region-bs-main-and-pre { width: 100% !important; }
#region-main.span8, #region-main.span9 {
    width: 100% !important;
    margin-left: 0 !important;
    float: none !important;
}
#page-content, #region-main, .que, #responseform {
    padding-left: 0 !important;
    margin-left: 0 !important;
}
#page, #page-wrapper { padding-left: 0 !important; margin-left: 0 !important; }
body { padding-bottom: 56px !important; }
#wrapper { border-top: 0 !important; }

.back-to-top{
   bottom: 120px !important;
}
.error-button {
    bottom: 60px !important;
}
/* =====================================================================
   FIXED FOOTER BAR
===================================================================== */
.th-ielts-footer {
    position: fixed !important;
    bottom: 0;
    left: 0;
    right: 0;
    height: 52px;
    background: #ffffff;
    display: flex;
    align-items: stretch;
    z-index: 9999;
    font-family: inherit;
}

/* =====================================================================
   PART SECTIONS
===================================================================== */
.th-footer-part {
    display: flex;
    align-items: center;
    flex: 1 1 0 !important;
    padding: 0 12px;
    position: relative;
    min-width: 0 !important;
    gap: 8px;
    overflow: hidden;
    cursor: pointer;
}
.th-footer-part:last-of-type { border-right: none; }

.th-part-label {
    font-size: 12px;
    color: #333;
    white-space: nowrap;
    flex-shrink: 0;
    font-weight: 400;
}
.th-part-count {
    font-size: 12px;
    color: #888;
    white-space: nowrap;
    flex-shrink: 0;
}
.th-footer-part.th-active-part {
    flex: 3 1 0 !important;
}
.th-footer-part.th-active-part .th-part-label {
    font-weight: 700;
    color: #111;
    border-top: 2px solid #c1c1c1;
    padding-top: 2px;
    padding-inline: 20px;
}
.th-footer-part.th-active-part .th-part-count {
    display: none;
}
.th-footer-part.th-active-part::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}

/* =====================================================================
   QUESTION NUMBER LIST
===================================================================== */
.th-footer-qn-list {
    display: none !important;
}
.th-footer-part.th-active-part .th-footer-qn-list {
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    gap: 3px !important;
    overflow: hidden !important;
    flex: 1 1 0;
    min-width: 0;
}

/* ── question button ── */
.th-sub-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 24px !important;
    height: 24px !important;
    padding: 0 3px !important;
    border-radius: 0 !important;
    font-size: 11px !important;
    font-weight: 400 !important;
    border: none !important;
    border-top: 2px solid #c1c1c1 !important;
    background: transparent !important;
    color: #888 !important;
    white-space: nowrap !important;
    text-decoration: none !important;
    cursor: pointer !important;
    flex-shrink: 0 !important;
    transition: color 0.1s !important;
}
.th-sub-btn:hover {
    color: #111 !important;
    text-decoration: none !important;
}
.th-sub-btn.complete {
    border-top: 2px solid #4caf50 !important;
    color: #256029 !important;
}
.th-sub-btn.flagged {
    border-top: 2px solid #ff9800 !important;
    color: #bf5a00 !important;
}
.th-sub-btn.notyetanswered {
    color: #c1c1c1 !important;
}

/* ── Câu đang được focus (prev/next hoặc click) ── */
.th-sub-btn.th-current {
    border: 2px solid #2a6c96!important;
    background: #ffffff !important;
    color: #111111 !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding-top: 0 !important;
}

/* Hidden source container */
#th-nav-source { display: none !important; }

/* =====================================================================
   FINISH BUTTON
===================================================================== */
.th-footer-finish {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    min-width: 70px;
    background: #f5f5f5;
    cursor: pointer;
}
.th-footer-finish:hover { background: #000 !important; }
.th-footer-finish .btn,
.th-footer-finish input[type="submit"],
.th-footer-finish button,
.th-footer-finish a.btn {
    background: transparent !important;
    border: none !important;
    color: #555 !important;
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    border-radius: 4px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.th-footer-finish:hover .btn,
.th-footer-finish:hover input[type="submit"],
.th-footer-finish:hover button,
.th-footer-finish:hover a.btn,
.th-footer-finish:hover .endtestlink {
    background: transparent !important;
    color: #ffffff !important;
}
.endtestlink { color: #555555 !important; }
.endtestlink:hover { color: #fff !important; }

/* =====================================================================
   PREV / NEXT NAVIGATION BUTTONS
===================================================================== */
.th-custom-nav-buttons {
    position: fixed !important;
    bottom: 200px !important;
    right: 0 !important;
    z-index: 10001 !important;
    display: flex !important;
    flex-direction: row !important;
    gap: 3px !important;
    height: 52px !important;
}
.th-nav-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 52px !important;
    height: 52px !important;
    background: #111111 !important;
    color: #ffffff !important;
    border: none !important;
    cursor: pointer !important;
    transition: background 0.15s !important;
    margin: 0 !important;
    padding: 0 !important;
    border-radius: 5px !important;
    text-decoration: none !important;
}
.th-nav-btn:hover { background: #2a2a2a !important; }
.th-nav-btn.disabled {
    background: #777777 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}
.th-nav-btn svg {
    width: 22px !important;
    height: 22px !important;
    stroke: #ffffff !important;
    display: block !important;
}

        ');

        // =====================================================================
        // RENDER: hidden source + footer shell
        // =====================================================================
        $output .= '<div id="th-nav-source">';
        foreach ($buttons as $button) {
            $output .= $this->render($button);
        }
        $output .= '</div>';

        $output .= '<div class="th-ielts-footer" id="th-ielts-footer">';
        $output .= '<div id="th-footer-parts-container" style="display:flex;align-items:stretch;flex:1 1 0;min-width:0;overflow:hidden;"></div>';
        $output .= '<div class="th-footer-finish">' . $endbits . '</div>';
        $output .= '</div>';

        // =====================================================================
        // JS
        // =====================================================================
        $this->page->requires->js_amd_inline("
require(['jquery'], function(\$) {
    \$(document).ready(function() {

        // ── 1. Move footer to <body> ──────────────────────────────────────────
        var footer = document.getElementById('th-ielts-footer');
        if (footer) { document.body.appendChild(footer); }

        // ── 2. Replace finish-button with checkmark SVG ───────────────────────
        var finishWrap = document.querySelector('.th-footer-finish');
        if (finishWrap) {
            var finishBtn = finishWrap.querySelector('a, button, input[type=\"submit\"]');
            if (finishBtn) {
                while (finishBtn.firstChild) { finishBtn.removeChild(finishBtn.firstChild); }
                var svgNS = 'http://www.w3.org/2000/svg';
                var svg   = document.createElementNS(svgNS, 'svg');
                svg.setAttribute('width',           '20');
                svg.setAttribute('height',          '20');
                svg.setAttribute('viewBox',         '0 0 24 24');
                svg.setAttribute('fill',            'none');
                svg.setAttribute('stroke',          'currentColor');
                svg.setAttribute('stroke-width',    '4');
                svg.setAttribute('stroke-linecap',  'round');
                svg.setAttribute('stroke-linejoin', 'round');
                var poly = document.createElementNS(svgNS, 'polyline');
                poly.setAttribute('points', '4 13 9 18 20 7');
                svg.appendChild(poly);
                finishBtn.appendChild(svg);
            }

            // ── 3. Navigate to summary page on finish click ───────────────────
            var endLink = finishWrap.querySelector('a.endtestlink');
            if (endLink) {
                var summaryHref = endLink.getAttribute('href');
                finishWrap.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (summaryHref) {
                        window.location.href = summaryHref;
                    }
                });
            }
        }

        // ── 4. Build subMap from form fields ─────────────────────────────────
        var subMap = {};
        \$('#responseform').find('input[name], select[name], textarea[name]').each(function() {
            var name = \$(this).attr('name') || '';
            var m = name.match(/^q(\\d+):(\\d+)_(.+)\$/);
            if (!m) { return; }
            var slot    = parseInt(m[2], 10);
            var rest    = m[3];
            if (/sequencecheck|flagged|submit|timeup|scrollpos|sesskey|attempt\$|thispage|nextpage|slots/.test(rest)) { return; }
            var rootKey = rest.split('_')[0];
            if (!subMap[slot]) { subMap[slot] = {}; }
            var el    = this;
            var tag   = el.tagName.toLowerCase();
            var type  = (el.type || '').toLowerCase();
            var filled = false;
            if (tag === 'select') {
                var val = \$(el).val();
                filled = (val !== null && val !== '' && val !== '0');
            } else if (tag === 'textarea') {
                filled = \$.trim(\$(el).val()) !== '';
            } else if (type === 'radio' || type === 'checkbox') {
                filled = el.checked;
            } else if (type !== 'hidden' && type !== 'submit' && type !== 'button') {
                filled = \$.trim(\$(el).val()) !== '';
            }
            if (!subMap[slot][rootKey]) {
                var elId = el.id || '';
                if (!elId) {
                    var wrap = el.closest('[id]');
                    elId = wrap ? wrap.id : '';
                }
                subMap[slot][rootKey] = { answered: filled, elementId: elId };
            } else if (filled) {
                subMap[slot][rootKey].answered = true;
            }
        });

        // ── 5. Read source buttons, group by href anchor ──────────────────────
        var anchorOrder  = [];
        var anchorGroups = {};

        \$('#th-nav-source').find('a.qnbutton').each(function() {
            var href   = this.getAttribute('href') || '';
            var anchor = href.replace(/^.*#/, '');
            if (!anchor) { return; }
            if (!anchorGroups[anchor]) {
                anchorGroups[anchor] = [];
                anchorOrder.push(anchor);
            }
            anchorGroups[anchor].push(this);
        });

        // ── 6. Detect active anchor from URL hash ─────────────────────────────
        var currentHash = window.location.hash.replace(/^#/, '');

        // ── 7. Build parts and inject into footer ─────────────────────────────
        var container     = document.getElementById('th-footer-parts-container');
        var globalCounter = 1;

        // anchorToQueIds: lưu mapping anchor → danh sách id của .que tương ứng
        var anchorToQueIds = {};

        anchorOrder.forEach(function(anchor, partIdx) {
            var srcButtons = anchorGroups[anchor];
            var partNum    = partIdx + 1;

            var btnEls = [];
            var queIds = [];

            srcButtons.forEach(function(srcBtn) {
                var idMatch   = (srcBtn.id || '').match(/quiznavbutton(\\d+)/);
                var baseClass = srcBtn.className || '';
                var isFlagged = baseClass.indexOf('flagged') !== -1;
                var btnHref   = srcBtn.getAttribute('href') || ('#' + anchor);

                // Thu thập id của .que từ href của button
                var hrefAnchor = btnHref.replace(/^.*#/, '');
                if (hrefAnchor && queIds.indexOf(hrefAnchor) === -1) {
                    queIds.push(hrefAnchor);
                }

                if (!idMatch) {
                    var b = document.createElement('a');
                    b.href = btnHref;
                    b.className = 'th-sub-btn notyetanswered';
                    b.textContent = globalCounter;
                    b.title = 'Question ' + globalCounter;
                    btnEls.push(b);
                    globalCounter++;
                    return;
                }

                var slotNum = parseInt(idMatch[1], 10);
                var subs    = subMap[slotNum] ? Object.keys(subMap[slotNum]) : [];

                if (subs.length <= 1) {
                    var b = document.createElement('a');
                    var subElId = subs.length === 1 ? subMap[slotNum][subs[0]].elementId : '';
                    b.href = subElId ? (btnHref.split('#')[0] + '#' + subElId) : btnHref;
                    var cls = 'th-sub-btn';
                    if (isFlagged) {
                        cls += ' flagged';
                    } else {
                        var isAnswered = subs.length === 1 && subMap[slotNum][subs[0]].answered;
                        cls += isAnswered ? ' complete' : ' notyetanswered';
                    }
                    b.className   = cls;
                    b.textContent = globalCounter;
                    b.title       = 'Question ' + globalCounter;
                    btnEls.push(b);
                    globalCounter++;
                } else {
                    subs.forEach(function(key) {
                        var sub = subMap[slotNum][key];
                        var b   = document.createElement('a');
                        b.href = sub.elementId ? (btnHref.split('#')[0] + '#' + sub.elementId) : btnHref;
                        var cls = 'th-sub-btn';
                        if (isFlagged)         { cls += ' flagged'; }
                        else if (sub.answered) { cls += ' complete'; }
                        else                   { cls += ' notyetanswered'; }
                        b.className   = cls;
                        b.textContent = globalCounter;
                        b.title       = 'Question ' + globalCounter;
                        btnEls.push(b);
                        globalCounter++;
                    });
                }
            });

            anchorToQueIds[anchor] = queIds;

            var answered = btnEls.filter(function(b) {
                return b.className.indexOf('complete') !== -1 || b.className.indexOf('flagged') !== -1;
            }).length;
            var total = btnEls.length;

            var isActive = (currentHash === anchor) || (partIdx === 0 && !currentHash);

            var partDiv = document.createElement('div');
            partDiv.className = 'th-footer-part' + (isActive ? ' th-active-part' : '');
            partDiv.setAttribute('data-anchor', anchor);

            var labelEl = document.createElement('span');
            labelEl.className   = 'th-part-label';
            labelEl.textContent = 'Part ' + partNum;

            var countEl = document.createElement('span');
            countEl.className   = 'th-part-count';
            countEl.textContent = answered + ' of ' + total;

            var listEl = document.createElement('div');
            listEl.className = 'th-footer-qn-list';
            btnEls.forEach(function(b) { listEl.appendChild(b); });

            partDiv.appendChild(labelEl);
            partDiv.appendChild(countEl);
            partDiv.appendChild(listEl);
            container.appendChild(partDiv);
        });

        // Fallback: nếu không có part nào active thì mark cái đầu
        if (!container.querySelector('.th-active-part') && container.firstElementChild) {
            container.firstElementChild.classList.add('th-active-part');
        }

        // =====================================================================
        // ── HELPERS ──────────────────────────────────────────────────────────
        // =====================================================================

        // ── Ẩn/hiện .formulation theo active part ────────────────────────────
        function _updateFormulationVisibility() {
            var activePart   = container.querySelector('.th-footer-part.th-active-part');
            var activeAnchor = activePart ? activePart.getAttribute('data-anchor') : '';

            // Ẩn tất cả .formulation trước
            document.querySelectorAll('.que .formulation').forEach(function(el) {
                el.style.display = 'none';
            });

            if (!activeAnchor) {
                // Fallback: hiện tất cả
                document.querySelectorAll('.que .formulation').forEach(function(el) {
                    el.style.display = '';
                });
                return;
            }

            // Lấy danh sách queId của part đang active
            var queIds = anchorToQueIds[activeAnchor] || [];

            // Tìm tất cả .que chứa id nằm trong queIds và hiện .formulation của chúng
            queIds.forEach(function(queId) {
                var queEl = document.getElementById(queId);
                if (!queEl) { return; }
                // Tìm .que cha gần nhất hoặc chính nó
                var queWrap = queEl.closest('.que') || queEl;
                queWrap.querySelectorAll('.formulation').forEach(function(f) {
                    f.style.display = '';
                });
            });

            // Nếu không map được queId nào, fallback: dùng anchor trực tiếp
            if (queIds.length === 0) {
                var anchorEl = document.getElementById(activeAnchor);
                if (anchorEl) {
                    var queWrap = anchorEl.closest('.que') || anchorEl;
                    queWrap.querySelectorAll('.formulation').forEach(function(f) {
                        f.style.display = '';
                    });
                }
            }
        }

        function _getAllBtns() {
            return Array.from(container.querySelectorAll('a.th-sub-btn'));
        }

        function _activateBtn(btn) {
            // Xoá trạng thái current cũ
            _getAllBtns().forEach(function(b) { b.classList.remove('th-current'); });
            btn.classList.add('th-current');

            // Cập nhật active part theo btn được chọn
            var part = btn.closest('.th-footer-part');
            if (part && !part.classList.contains('th-active-part')) {
                container.querySelectorAll('.th-footer-part').forEach(function(p) {
                    p.classList.remove('th-active-part');
                });
                part.classList.add('th-active-part');
                _updateFormulationVisibility();
            }

            // Scroll btn vào view trong footer list (tránh bị overflow ẩn)
            if (btn.scrollIntoView) {
                btn.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }

            // Scroll đến element đích trong nội dung trang
            var href     = btn.getAttribute('href') || '';
            var hashIdx  = href.lastIndexOf('#');
            var targetId = hashIdx !== -1 ? href.slice(hashIdx + 1) : '';
            var targetEl = targetId ? document.getElementById(targetId) : null;
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetEl.focus({ preventScroll: true });
            } else if (href) {
                window.location.href = href;
            }
        }

        function _updatePrevState() {
            var all     = _getAllBtns();
            var current = container.querySelector('a.th-sub-btn.th-current');
            var idx     = current ? all.indexOf(current) : -1;
            var prevLabel = document.querySelector('.th-nav-btn.mod_quiz-prev-nav');
            var prevInput = document.getElementById('mod_quiz-prev-nav');
            var isFirst = (idx <= 0);
            if (prevLabel) { prevLabel.classList.toggle('disabled', isFirst); }
            if (prevInput) { prevInput.disabled = isFirst; }
        }

        function _navStep(direction) {
            var all     = _getAllBtns();
            if (!all.length) { return false; }

            var current = container.querySelector('a.th-sub-btn.th-current');
            var idx     = current ? all.indexOf(current) : -1;
            var nextIdx;

            if (direction === 'prev') {
                if (idx <= 0) { return false; }
                nextIdx = idx - 1;
            } else {
                if (idx === -1) { nextIdx = 0; }
                else            { nextIdx = idx + 1; }
            }

            if (nextIdx >= all.length) { return false; }

            _activateBtn(all[nextIdx]);
            _updatePrevState();
            return true;
        }

        // ── 8. Click handler footer ───────────────────────────────────────────
        container.addEventListener('click', function(e) {
            var link = e.target.closest('a.th-sub-btn');
            if (link) {
                e.preventDefault();
                _activateBtn(link);
                _updatePrevState();
                return;
            }

            var part = e.target.closest('.th-footer-part');
            if (!part) { return; }

            if (part.classList.contains('th-active-part')) { return; }

            container.querySelectorAll('.th-footer-part').forEach(function(p) {
                p.classList.remove('th-active-part');
            });
            part.classList.add('th-active-part');
            _updateFormulationVisibility();

            var firstBtn = part.querySelector('.th-sub-btn');
            if (firstBtn) {
                _activateBtn(firstBtn);
                _updatePrevState();
            }
        });

        // ── 9. Khởi tạo ──────────────────────────────────────────────────────
        _updateFormulationVisibility(); // ẩn/hiện formulation theo part active ban đầu
        _updatePrevState();

        var prevInput = document.getElementById('mod_quiz-prev-nav');
        var nextInput = document.getElementById('mod_quiz-next-nav');

        if (prevInput) {
            prevInput.addEventListener('click', function(e) {
                var handled = _navStep('prev');
                if (handled) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            }, true);
        }

        if (nextInput) {
            nextInput.addEventListener('click', function(e) {
                var handled = _navStep('next');
                if (handled) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            }, true);
        }

    });
});
        ");

        return $output;
    }
}