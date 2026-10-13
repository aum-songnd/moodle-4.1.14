/* ==========================================================================
   IELTS attempt header: banner "Part N" + khoảng câu hỏi, page offset,
   và chuyển nút Back vào header cố định.
   (trước đây nhúng inline trong render_ielts_header())
   ========================================================================== */

// Định nghĩa phòng vệ - file nào trong 3 file (resize.js/ielts_header.js/
// quiz_footer.js) load trước cũng tự tạo được, các file load sau chỉ tái
// sử dụng (idempotent), không phụ thuộc thứ tự load. Xem giải thích đầy đủ
// trong resize.js.
window.thLayoutReady = window.thLayoutReady || (function () {
    var needed = { resize: false, header: false, footer: false };
    var revealed = false;
    var TIMEOUT_MS = 500;

    function reveal() {
        if (revealed) {
            return;
        }
        revealed = true;
        document.documentElement.classList.add("th-layout-ready");
        window.dispatchEvent(new Event("th:layout-ready"));
    }

    function tryReveal() {
        if (needed.resize && needed.header && needed.footer) {
            reveal();
        }
    }

    setTimeout(reveal, TIMEOUT_MS);

    return {
        mark: function (key) {
            if (Object.prototype.hasOwnProperty.call(needed, key)) {
                needed[key] = true;
                tryReveal();
            }
        }
    };
})();

(function () {

    var header = document.querySelector(".th-ielts-header");
    if (!header) {
        // Không có header -> phần "header" của layout coi như đã xong, không
        // được chặn reveal (xem thLayoutReady trong resize.js) chờ mãi.
        if (window.thLayoutReady) {
            window.thLayoutReady.mark("header");
        }
        return;
    }

    var banner  = document.getElementById("th-part-banner");
    var titleEl = banner ? banner.querySelector(".th-part-banner-title") : null;
    var descEl  = banner ? banner.querySelector(".th-part-banner-desc") : null;

    var pendingHashValue = window.thPendingHash || "";
    var hasPendingHash   = !!(pendingHashValue && pendingHashValue.length > 1);
    var hashScrollLocked = false; // khoá hẳn khi hết thời gian chờ layout ổn định
    var lastCorrection   = 0;     // tổng độ bù đã áp dụng, để lần sau chỉ bù phần CHÊNH LỆCH mới

   
    function _isScrollable(node) {
        if (!node || node.nodeType !== 1) {
            return false;
        }
        var style = window.getComputedStyle(node);
        if (style.overflowY !== "auto" && style.overflowY !== "scroll") {
            return false;
        }
        return node.scrollHeight > node.clientHeight + 1;
    }

    function _getScrollableAncestor(el) {
        var node = el.parentElement;
        while (node && node !== document.documentElement) {
            if (_isScrollable(node)) {
                return node;
            }
            node = node.parentElement;
        }
        return null;
    }

    function scrollHashTargetWithinColumn(targetEl) {
      
        var portalContainer = targetEl.closest(
            '.th-answercontainer-portal, [data-th-portal-active="1"]'
        );

        var col = (portalContainer && portalContainer.thPlaceholder)
            ? portalContainer.thPlaceholder.closest(".th-col-left, .th-col-right")
            : targetEl.closest(".th-col-left, .th-col-right");

        if (!col) {
            col = _getScrollableAncestor(targetEl);
        }

        if (!col) {
            return false;
        }

        var colRect    = col.getBoundingClientRect();
        var targetRect = targetEl.getBoundingClientRect();
        var padding    = 16; // đệm thêm cho thoáng, giống "extra" bản cũ
        var delta      = targetRect.top - colRect.top - padding;

        if (Math.abs(delta) > 0.5) {
            col.scrollTop += delta;
        }

        return true;
    }

    function reAlignHashScroll() {
        if (hashScrollLocked || !hasPendingHash) {
            return;
        }

        if (isSplitIntoColumns()) {
            var hashId  = pendingHashValue.slice(1);
            var hashTgt = hashId ? document.getElementById(hashId) : null;

            if (hashTgt && scrollHashTargetWithinColumn(hashTgt)) {
                // Cuộn nội bộ cột thành công -> KHÔNG để cả trang cuộn theo.
                if (window.pageYOffset > 0) {
                    window.scrollTo(0, 0);
                }
                return;
            }
          
        }

        if (window.pageYOffset < 1) {
            // Trang chưa thực sự bị cuộn xuống (vd hash trỏ tới phần tử
            // ở đầu trang) -> không cần bù trừ.
            hashScrollLocked = true;
            return;
        }

        var headerH = header.offsetHeight;
        var bannerH = banner ? banner.offsetHeight : 0;
        var extra   = 20; // đệm thêm cho thoáng
        var target  = headerH + bannerH + extra;
        var delta   = target - lastCorrection;

        if (Math.abs(delta) > 0.5) {
            window.scrollBy({
                top:      -delta,
                left:     0,
                behavior: "auto"
            });
            lastCorrection = target;
        }
    }

    function getQuestionRange(partEl) {
        var nums = [];
        partEl.querySelectorAll("a.th-sub-btn").forEach(function (btn) {
            var qs = btn.getAttribute("data-qstart");
            var qe = btn.getAttribute("data-qend");
            if (qs && qe) {
                nums.push(parseInt(qs, 10), parseInt(qe, 10));
            } else {
                var n = parseInt(btn.textContent, 10);
                if (!isNaN(n)) {
                    nums.push(n);
                }
            }
        });
        if (!nums.length) {
            return null;
        }
        return {
            start: Math.min.apply(null, nums),
            end:   Math.max.apply(null, nums)
        };
    }

    function setPageOffset() {
        var headerH = header.offsetHeight;
        var bannerH = banner ? banner.offsetHeight : 0;
        document.documentElement.style.setProperty(
            "--th-page-offset",
            (headerH + bannerH) + "px"
        );
        // Lần đầu setPageOffset() chạy nghĩa là --th-page-offset đã có giá
        // trị THẬT (không còn dùng fallback 64px sai lệch) -> báo "header"
        // xong cho thLayoutReady (idempotent, gọi lại nhiều lần vô hại).
        if (window.thLayoutReady) {
            window.thLayoutReady.mark("header");
        }
    }

    function getActiveQueElements() {
        var container = document.getElementById("th-quiz-questions");
        if (!container) {
            return [];
        }
        return Array.prototype.slice.call(container.querySelectorAll(".que")).filter(
            function (queEl) {
                var formulation = queEl.querySelector(".formulation");
                return !!formulation && !formulation.classList.contains("th-force-hidden");
            }
        );
    }

    function getActiveEssayEl() {
        var queEls = getActiveQueElements();
        for (var i = 0; i < queEls.length; i++) {
            var el = queEls[i].querySelector(".qtype_essay_editor, .qtype_essay_response");
            if (el) {
                return el;
            }
        }
        return null;
    }

    function hasEssayQuestion() {
        return !!getActiveEssayEl();
    }

    function hasAudioQuestion() {
        var queEls = getActiveQueElements();
        return queEls.some(function (queEl) {
            return !!queEl.querySelector("audio, .video-js");
        });
    }

    function getActiveEssaySlot(essayEl) {
        var qDiv = essayEl.closest("[id^='question-']");
        if (!qDiv) {
            return null;
        }
        var m = qDiv.id.match(/^question-\d+-(\d+)$/);
        return m ? parseInt(m[1], 10) : null;
    }

    function getQuizTimeLimitMinutes() {
        return (typeof window.thQuizTimeLimitMinutes === "number" && window.thQuizTimeLimitMinutes > 0)
            ? window.thQuizTimeLimitMinutes
            : null;
    }

    function getEssayMinWordLimit(essayEl) {
        if (!essayEl || !window.thEssayWordLimits) {
            return null;
        }
        var slot = getActiveEssaySlot(essayEl);
        if (slot === null) {
            return null;
        }
        var limit = window.thEssayWordLimits[slot];
        return (limit && limit.min > 0) ? limit.min : null;
    }

    function isSplitIntoColumns() {
        return document.documentElement.classList.contains("th-has-split");
    }

    function updateBanner() {
        var activePart = document.querySelector(
            "#th-footer-parts-container .th-footer-part.th-active-part"
        );
        if (!activePart) {
            return;
        }

        var labelEl   = activePart.querySelector(".th-part-label");
        var partLabel = labelEl ? labelEl.textContent : "";
        var range     = getQuestionRange(activePart);

        titleEl.textContent = partLabel;

        if (hasEssayQuestion()) {
            var minWords    = getEssayMinWordLimit(getActiveEssayEl());
            var timeMinutes = getQuizTimeLimitMinutes();

            var timeText = timeMinutes
                ? "You should spend about " + timeMinutes + " minutes on this task."
                : "";
            var wordText = minWords
                ? " Write at least " + minWords + " words."
                : "";

            descEl.textContent = (timeText + wordText).trim();
        } else if (range) {
            if (range.start === range.end) {
                descEl.textContent = "Answer question " + range.start;
            } else if (hasAudioQuestion()) {
                descEl.textContent = "Listen and answer questions " + range.start + "\u2013" + range.end;
            } else {
                descEl.textContent = "Read the text and answer questions " + range.start + "\u2013" + range.end;
            }
        } else {
            descEl.textContent = "";
        }

        setPageOffset();

        requestAnimationFrame(function () {
            requestAnimationFrame(reAlignHashScroll);
        });
    }

    function bindObserver(container) {
        var mo = new MutationObserver(function () {
            updateBanner();
        });
        mo.observe(container, {
            attributes:      true,
            attributeFilter: ["class"],
            subtree:         true,
            childList:       true
        });
    }

    if (banner) {
        var containerNow = document.getElementById("th-footer-parts-container");
        if (containerNow && containerNow.querySelector(".th-footer-part")) {
            // Đã có sẵn (vd script chạy muộn, sau khi DOM đã build xong) ->
            // khỏi chờ, chạy ngay.
            bindObserver(containerNow);
            updateBanner();
        } else {
            // FIX (mượt hơn): trước đây setInterval mỗi 150ms mới kiểm tra
            // lại 1 lần, luôn tốn tới gần 150ms vô ích dù DOM có thể đã sẵn
            // sàng sớm hơn nhiều. Đổi sang MutationObserver để phản ứng
            // NGAY tại thời điểm #th-footer-parts-container/.th-footer-part
            // thực sự xuất hiện trong DOM, rút ngắn thời gian trang còn bị
            // ẩn chờ (xem thLayoutReady/.th-layout-ready trong resize.css).
            var finderObserver = new MutationObserver(function () {
                var container = document.getElementById("th-footer-parts-container");
                if (container && container.querySelector(".th-footer-part")) {
                    finderObserver.disconnect();
                    bindObserver(container);
                    updateBanner();
                }
            });
            finderObserver.observe(document.body, {
                childList: true,
                subtree:   true
            });
        }
    } else {
        setPageOffset();
    }

    window.addEventListener("resize", setPageOffset);
    window.addEventListener("load", function () {
        setPageOffset();
        reAlignHashScroll();
    });

    if (hasPendingHash) {
        window.addEventListener("th:layout-settled", function () {
            requestAnimationFrame(reAlignHashScroll);
        });

        setTimeout(function () {
            hashScrollLocked = true;
        }, 900);
    }

})();

(function () {
    function moveBackButton() {
        var slot = document.querySelector(".th-ielts-header-back-slot");
        if (!slot || slot.dataset.thMoved === "1") {
            return;
        }
        var backBtn = document.querySelector('a.btn.btn-secondary[href*="mod/quiz/view.php"]');
        if (!backBtn) {
            return;
        }
        var wrapperRow = backBtn.closest(".container-fluid.tertiary-navigation");
        slot.appendChild(backBtn);
        slot.dataset.thMoved = "1";
        if (wrapperRow) {
            wrapperRow.style.display = "none";
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", moveBackButton);
    } else {
        moveBackButton();
    }
})();

// The quiz logo is rendered server-side from theme_lambda by mod_quiz_renderer.php.
