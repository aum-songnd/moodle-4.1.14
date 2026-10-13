/**
 * IELTS Quiz Footer Navigation Module
 *
 * Handles the dynamic footer navigation bar for IELTS quiz attempts,
 * including part management, sub-question tracking, and state persistence.
 */

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
  "use strict";

  function init() {
    if (typeof jQuery === "undefined") {
      console.error("jQuery required for quiz_footer");
      // Không thể build footer -> không được chặn reveal (thLayoutReady,
      // định nghĩa trong resize.js) chờ mãi.
      if (window.thLayoutReady) {
        window.thLayoutReady.mark("footer");
      }
      return;
    }
    var $ = jQuery;

    function _hotspotLabelsReady() {
      var hotspots = document.querySelectorAll(
        ".hotspot, .hotspot-editable, .atto-thimageeditor-hotspot",
      );
      if (hotspots.length === 0) {
        return true;
      }
      for (var i = 0; i < hotspots.length; i++) {
        if (!hotspots[i].hasAttribute("data-label")) {
          return false;
        }
      }
      return true;
    }

    function _waitForHotspotLabelsThenInit(retry) {
      if (_hotspotLabelsReady() || retry >= 1) {
        _runFooterInit();
        return;
      }
      setTimeout(function () {
        _waitForHotspotLabelsThenInit(retry + 1);
      }, 200);
    }

    $(document).ready(function () {
      _waitForHotspotLabelsThenInit(0);
    });

    // ── Snapshot: lưu/phục hồi nguyên trạng footer qua sessionStorage, để
    //    trang summary không phải (và không thể) tính lại từ đầu ───────────
    function _footerSnapshotKey() {
      return window.thQuizAttemptId
        ? "th_quiz_footer_snapshot_" + window.thQuizAttemptId
        : null;
    }

    // Dựng lại URL tuyệt đối về trang attempt cho 1 câu hỏi, từ dữ liệu tin
    // cậy do PHP bơm ra (thQuizAttemptId/thQuizCmid) - KHÔNG dựa vào việc
    // resolve href gốc, vì nhiều nút câu hỏi chỉ có href="#" (không phải
    // URL thật, Moodle xử lý điều hướng trong trang hoàn toàn bằng JS),
    // resolve kiểu đó có thể ra thiếu tham số "attempt"/"cmid".
    function _absAttemptUrl(originalHref, slotNum) {
      var attemptId = window.thQuizAttemptId;
      if (!attemptId) {
        return null;
      }

      var raw = originalHref || "";
      var hashIdx = raw.indexOf("#");
      var query = hashIdx !== -1 ? raw.slice(0, hashIdx) : raw;
      var frag = hashIdx !== -1 ? raw.slice(hashIdx + 1) : "";

      // Ưu tiên page THẬT lấy từ map slot->page do PHP bơm ra
      // (window.thQuizSlotPages, xem mod_quiz_renderer::navigation_panel()).
      // KHÔNG tin href gốc: Moodle điều hướng giữa các page bằng JS/form
      // submit (module.js), nên phần lớn nút chỉ có href="#"/"#slotid",
      // không mang page thật - nếu dựa vào đó, mọi nút sẽ bị hiểu nhầm là
      // page 0 (Part 1). Chỉ dùng regex href làm phương án dự phòng cuối.
      var page = null;
      var slotPages = window.thQuizSlotPages;
      if (slotPages && slotNum != null && slotPages[slotNum] != null) {
        page = String(slotPages[slotNum]);
      }
      if (page === null) {
        var pageMatch = query.match(/[?&]page=(\d+)/);
        page = pageMatch ? pageMatch[1] : "0";
      }

      var url =
        window.location.pathname +
        "?attempt=" +
        encodeURIComponent(attemptId) +
        "&page=" +
        encodeURIComponent(page);
      if (window.thQuizCmid) {
        url += "&cmid=" + encodeURIComponent(window.thQuizCmid);
      }
      if (frag) {
        url += "#" + frag;
      }
      return url;
    }

    function _saveFooterSnapshot(footer) {
      var key = _footerSnapshotKey();
      if (!footer || !key || window.thQuizIsSummaryPage) {
        return;
      }
      // Chỉ cache #th-footer-parts-container (danh sách Part/câu hỏi), KHÔNG
      // cache .th-footer-finish - nút Finish có href/behavior gắn theo trang
      // attempt, giữ lại sẽ đụng với nút "Submit all and finish" gốc của
      // Moodle ở trang summary.
      var container = footer.querySelector("#th-footer-parts-container");
      if (!container) {
        return;
      }

      // Clone rồi thay href gốc (thường chỉ là "#" hoặc "#slotid", không
      // phải URL đầy đủ) bằng URL tuyệt đối tự dựng, để bấm ở summary vẫn
      // quay đúng về attempt kèm đủ tham số attempt/cmid.
      var liveBtns = container.querySelectorAll("a.th-sub-btn");
      var clone = container.cloneNode(true);
      var cloneBtns = clone.querySelectorAll("a.th-sub-btn");
      liveBtns.forEach(function (liveBtn, i) {
        var slotNum = liveBtn.dataset.thSlot;
        var abs = _absAttemptUrl(liveBtn.getAttribute("href"), slotNum);
        if (abs && cloneBtns[i]) {
          cloneBtns[i].setAttribute("href", abs);
        }
      });

      try {
        sessionStorage.setItem(key, clone.innerHTML);
      } catch (e) {
        // sessionStorage đầy / bị chặn (private mode) -> bỏ qua, không chặn quiz.
      }
    }

    function _restoreFooterSnapshot(footer) {
      var key = _footerSnapshotKey();
      if (!footer) {
        return;
      }
      var container = footer.querySelector("#th-footer-parts-container");
      if (!container || !key) {
        return;
      }
      var html;
      try {
        html = sessionStorage.getItem(key);
      } catch (e) {
        html = null;
      }
      if (!html) {
        // Chưa từng đi qua trang attempt trong phiên này (vd mở thẳng link
        // summary) -> không có gì để phục hồi, để trống thay vì tính sai.
        return;
      }
      // href của các nút Part/câu hỏi được cache nguyên từ attempt (trỏ
      // thẳng tới attempt.php#slot tương ứng) nên vẫn bấm để quay lại đúng
      // câu hỏi được - đây là điều hướng trang bình thường, không cần JS.
      container.innerHTML = html;
    }

    // Ở trang summary, toàn bộ logic build/tính đáp án phía dưới không chạy
    // (return sớm ở _runFooterInit), nên phần chuyển "Part nào đang active"
    // (để mở rộng danh sách câu hỏi của Part đó) không còn ai xử lý. Gắn
    // riêng 1 handler tối giản chỉ để toggle class active - không đụng tới
    // subMap/formulation vì summary không có nội dung câu hỏi thật để ẩn/hiện.
    function _bindSummaryPartSwitch(footer) {
      var container = footer.querySelector("#th-footer-parts-container");
      if (!container) {
        return;
      }
      container.addEventListener("click", function (e) {
        // Bấm thẳng vào 1 câu hỏi (đã có href thật trỏ về attempt) -> để
        // trình duyệt điều hướng bình thường, không can thiệp.
        if (e.target.closest("a.th-sub-btn")) {
          return;
        }
        var part = e.target.closest(".th-footer-part");
        if (!part || part.classList.contains("th-active-part")) {
          return;
        }
        container.querySelectorAll(".th-footer-part").forEach(function (p) {
          p.classList.remove("th-active-part");
        });
        part.classList.add("th-active-part");
      });
    }

    // ── SUMMARY: đếm lại số câu chưa trả lời theo đúng cách footer đang tính
    //    (weight qua data-th-weight, "complete"/"flagged" = đã trả lời) rồi
    //    ghi đè vào modal "Submit all and finish" gốc của Moodle
    //    (mod_quiz/submission_confirmation.mustache), vì cách đếm mặc định
    //    của Moodle (theo #mod_quiz_navblock gốc) không khớp với các dạng
    //    câu hỏi custom (cluster/multianswer weight) của footer này ─────────
    function _summaryCountUnanswered(footer) {
      var container = footer
        ? footer.querySelector("#th-footer-parts-container")
        : null;
      if (!container) {
        return null;
      }
      var total = 0;
      container.querySelectorAll("a.th-sub-btn").forEach(function (b) {
        var w = parseInt(b.dataset.thWeight, 10) || 1;
        var cls = b.className;
        var answered =
          cls.indexOf("complete") !== -1 || cls.indexOf("flagged") !== -1;
        if (!answered) {
          total += w;
        }
      });
      return total;
    }

    function _setUnansweredBox(box, count) {
      if (count <= 0) {
        box.style.display = "none";
        return;
      }
      box.style.display = "";
      var applyText = function (text) {
        box.textContent = text;
      };
      if (typeof require === "function") {
        require(["core/str"], function (Str) {
          Str.get_string(
            "submission_confirmation_unanswered",
            "mod_quiz",
            count,
          )
            .then(applyText)
            .catch(function () {
              box.textContent = box.textContent.replace(/\d+/, String(count));
            });
        });
      } else {
        box.textContent = box.textContent.replace(/\d+/, String(count));
      }
    }

    function _handleConfirmationContainer(container, count) {
      if (container.dataset.thUnansweredPatched === "1") {
        return;
      }
      container.dataset.thUnansweredPatched = "1";

      var box = container.querySelector(".alert-warning");
      if (!box) {
        if (count <= 0) {
          return;
        }
        // Moodle tính hasunanswered=false nhưng theo cách đếm của mình vẫn
        // còn câu chưa trả lời -> tự thêm khối cảnh báo.
        box = document.createElement("div");
        box.className = "alert alert-warning";
        container.appendChild(box);
      }
      _setUnansweredBox(box, count);
    }

    function _patchSubmissionConfirmationCount(footer) {
      var count = _summaryCountUnanswered(footer);
      if (count === null) {
        return;
      }

      document
        .querySelectorAll(".quiz-submission-confirmation-content")
        .forEach(function (c) {
          _handleConfirmationContainer(c, count);
        });

      // Modal "Submit all and finish" được Moodle tạo động khi bấm nút, nên
      // cần theo dõi DOM để bắt đúng thời điểm nó xuất hiện.
      new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          mutation.addedNodes.forEach(function (node) {
            if (node.nodeType !== 1) {
              return;
            }
            if (
              node.classList &&
              node.classList.contains("quiz-submission-confirmation-content")
            ) {
              _handleConfirmationContainer(node, count);
            }
            if (node.querySelectorAll) {
              node
                .querySelectorAll(".quiz-submission-confirmation-content")
                .forEach(function (c) {
                  _handleConfirmationContainer(c, count);
                });
            }
          });
        });
      }).observe(document.body, { childList: true, subtree: true });
    }

    function _runFooterInit() {
      // ── 1. Move footer to <body> ──────────────────────────────────────────
      var footer = document.getElementById("th-ielts-footer");
      if (footer) {
        document.body.appendChild(footer);
      }

      // Footer (server-render sẵn) đã ở đúng chỗ trong <body>, các bước bên
      // dưới chỉ gắn thêm trạng thái/hành vi lên nút có sẵn - báo "footer"
      // xong ngay tại đây cho thLayoutReady để không giữ trang ẩn lâu hơn
      // cần thiết.
      if (window.thLayoutReady) {
        window.thLayoutReady.mark("footer");
      }

      // ── SUMMARY: không tính lại gì cả, chỉ phục hồi nguyên trạng footer
      //    đã lưu lúc còn ở trang attempt rồi dừng, bỏ qua toàn bộ logic
      //    build/scan #responseform bên dưới (không áp dụng được ở đây) ─────
      if (window.thQuizIsSummaryPage) {
        _restoreFooterSnapshot(footer);
        _bindSummaryPartSwitch(footer);
        _patchSubmissionConfirmationCount(footer);
        return;
      }

      var $scanRoot = $("#th-quiz-questions").length
        ? $("#th-quiz-questions")
        : $("#responseform");
      var isReviewPage = !!window.thQuizIsReviewPage;

      var TH_REVIEW_STATE_CLASSES = [
        "correct",
        "partiallycorrect",
        "incorrect",
        "notanswered",
        "invalidanswer",
      ];

      var TH_REVIEW_STATE_ALIAS = {
        partiallycorrect: "incorrect",
        invalidanswer: "incorrect",
      };

      function _findReviewStateClass(el) {
        var node = el;
        while (node && node !== document.body) {
          if (node.classList) {
            for (var i = 0; i < TH_REVIEW_STATE_CLASSES.length; i++) {
              if (node.classList.contains(TH_REVIEW_STATE_CLASSES[i])) {
                var found = TH_REVIEW_STATE_CLASSES[i];
                return TH_REVIEW_STATE_ALIAS[found] || found;
              }
            }
          }
          node = node.parentElement;
        }
        return null;
      }

      function _ddwtoDropZoneElByElementId(elementId, queEl) {
        if (!elementId || !queEl) {
          return null;
        }
        var m = elementId.match(/_p(\d+)$/);
        if (!m) {
          return null;
        }
        var placeNum = m[1];
        return queEl.querySelector(
          ".hotspot.place" +
            placeNum +
            ", .hotspot-editable.place" +
            placeNum +
            ", .atto-thimageeditor-hotspot.place" +
            placeNum +
            ", .drop.place" +
            placeNum +
            ', .drop[data-place="' +
            placeNum +
            '"]',
        );
      }

      function _ddwtoFeedbackIconFor(dropZoneEl) {
        if (!dropZoneEl) {
          return null;
        }
        var node = dropZoneEl.nextElementSibling;
        var guard = 0;
        while (node && guard < 6) {
          if (
            node.tagName === "IMG" &&
            /\bicon\b/.test(node.className || "") &&
            node.hasAttribute("src")
          ) {
            return node;
          }
          if (node.classList && node.classList.contains("drop")) {

            return null;
          }
          node = node.nextElementSibling;
          guard++;
        }
        return null;
      }

      function _ddwtoStateFromIcon(elementId, queEl) {
        var zone = _ddwtoDropZoneElByElementId(elementId, queEl);
        var icon = _ddwtoFeedbackIconFor(zone);
        if (!icon) {
          return null;
        }
        var src = icon.getAttribute("src") || "";
        if (src.indexOf("grade_incorrect") !== -1) {
          return "incorrect";
        }
        if (src.indexOf("grade_partiallycorrect") !== -1) {
          return "incorrect"; // đồng bộ với TH_REVIEW_STATE_ALIAS hiện có
        }
        if (src.indexOf("grade_correct") !== -1) {
          return "correct";
        }
        return null;
      }

      function _reviewStateClassFor(elementId, fallbackQueEl) {

        var ddState = _ddwtoStateFromIcon(elementId, fallbackQueEl);
        if (ddState) {
          return "th-review-" + ddState;
        }

        var refEl = elementId ? document.getElementById(elementId) : null;
        var state =
          _findReviewStateClass(refEl) || _findReviewStateClass(fallbackQueEl);
        return "th-review-" + (state || "notanswered");
      }

      // ── FIX (multichoice radio/checkbox): tra đúng/sai theo đúng option mà
      //    học viên đã chọn (sub.checkedElementIds), KHÔNG dùng sub.elementId
      //    (luôn là option đầu tiên trong nhóm - xem chú thích lúc build
      //    subMap). Trả về null nếu sub không phải multichoice (không có
      //    checkedElementIds) để nơi gọi tự rơi về _reviewStateClassFor cũ.
      function _multichoiceReviewStateFor(sub, fallbackQueEl) {
        var ids = sub && sub.checkedElementIds;
        if (!ids || ids.length === 0) {
          return null;
        }
        var sawIncorrect = false;
        var sawUnknown = false;
        for (var i = 0; i < ids.length; i++) {
          var cls = _reviewStateClassFor(ids[i], fallbackQueEl);
          if (cls === "th-review-incorrect") {
            sawIncorrect = true;
          } else if (cls !== "th-review-correct") {
            // Không xác định được rõ ràng (vd option chọn không mang class
            // riêng) -> không đủ tin cậy, để nơi gọi rơi về logic cũ.
            sawUnknown = true;
          }
        }
        if (sawIncorrect) {
          // Chỉ cần 1 lựa chọn sai là đủ để cả câu (hoặc cả nhóm checkbox)
          // hiện đỏ - không phụ thuộc vào class tổng ở .que.
          return "th-review-incorrect";
        }
        if (sawUnknown) {
          return null;
        }
        return "th-review-correct";
      }

      function _wasSubAttempted(slotNum, key, sub) {
        if (sub.limit && sub.limit > 1) {
          var cnt =
            (checkedCounts[slotNum] && checkedCounts[slotNum][key]) || 0;
          return cnt > 0;
        }
        return !!sub.answered;
      }

      function _isAnsweredBtnClass(cls) {
        if (isReviewPage) {
          return cls.indexOf("th-review-notanswered") === -1;
        }
        return cls.indexOf("complete") !== -1 || cls.indexOf("flagged") !== -1;
      }

      // ── 2. Replace finish-button content with localized text
      //      (vd "Submit" / "Nộp bài", lấy từ window.thQuizSubmitText do
      //      PHP bơm ra qua get_string('quizsubmit', 'theme_th_lambda_st_ielts'))
      //      thay vì icon SVG như trước ─────────────────────────────────────
      var finishWrap = document.querySelector(".th-footer-finish");
      if (finishWrap) {
        var finishBtn = finishWrap.querySelector(
          'a, button, input[type="submit"]',
        );
        if (finishBtn) {
          var submitText = window.thQuizSubmitText || "Submit";
          if (finishBtn.tagName === "INPUT") {
            // input[type=submit] hiển thị chữ qua thuộc tính value, không
            // có childNode để xoá/insert như a/button.
            finishBtn.value = submitText;
          } else {
            while (finishBtn.firstChild) {
              finishBtn.removeChild(finishBtn.firstChild);
            }
            finishBtn.appendChild(document.createTextNode(submitText));
          }
        }
      }

      function _resetFormChangeChecker() {
        window.onbeforeunload = null;
        try {
          if (typeof require === "function") {
            require(["core_form/changechecker"], function (changechecker) {
              try {
                changechecker.resetAllFormDirtyStates();
              } catch (e) {
                // API có thể khác giữa các phiên bản Moodle -> bỏ qua an toàn.
              }
            });
          }
        } catch (e) {
          // requirejs có thể chưa sẵn sàng / không tồn tại -> bỏ qua.
        }
      }

      var _finishing = false;
      function _doFinish() {
        if (_finishing) {
          return;
        }
        _finishing = true;
        _saveFooterSnapshot(footer);

        var endLink = finishWrap
          ? finishWrap.querySelector("a.endtestlink")
          : null;
        var summaryUrl = endLink ? endLink.getAttribute("href") : null;
        var form = document.getElementById("responseform");

        if (!form || !summaryUrl) {
          _resetFormChangeChecker();
          if (summaryUrl) {
            window.location.href = summaryUrl;
          }
          return;
        }

        _resetFormChangeChecker();

        var formData = new FormData(form);
        formData.set("nextpage", "-1");

        fetch(form.getAttribute("action"), {
          method: "POST",
          body: formData,
          redirect: "manual",
        }).finally(function () {
          _finishing = false;
          window.location.href = summaryUrl;
        });
      }

      // ── 3. Finish button click ────────────────────────────────────────────
      if (finishWrap) {
        var endLink = finishWrap.querySelector("a.endtestlink");
        if (endLink) {
          finishWrap.addEventListener("click", function (e) {
            e.preventDefault();
            _doFinish();
          });
        }
      }

      // ── 3b. Rút gọn text option placeholder (value="0") của select chỉ còn
      //        số (ví dụ "14 Choose" -> "14"). Dùng MutationObserver để bắt cả
      //        các select được render/thêm sau này, VÀ để tự sửa lại nếu bị
      //        script khác (Moodle) ghi đè text gốc sau đó ─────────────────
      function _shortenSelectPlaceholders() {
        document
          .querySelectorAll(
            ".que select.custom-select option[value='0'], .que select.custom-select option[value='']",
          )
          .forEach(function (opt) {
            var match = opt.textContent.match(/\d+/);
            if (match && opt.textContent !== match[0]) {
              opt.textContent = match[0];
            }
          });
      }

      _shortenSelectPlaceholders();

      var _placeholderObserver = new MutationObserver(function () {
        _shortenSelectPlaceholders();
      });
      _placeholderObserver.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
      });

      var subMap = {};
      var subOrder = {};
      var checkedCounts = {};

      var DDWTOS_P_RE = /^(answer\d*_)?p\d+$/;

      $scanRoot
        .find("input[name], select[name], textarea[name]")
        .each(function () {
          var name = $(this).attr("name") || "";
          var m = name.match(/^q(\d+):(\d+)_(.+)$/);
          if (!m) {
            return;
          }
          var slot = parseInt(m[2], 10);
          var rest = m[3];

          if (
            /sequencecheck|flagged|submit|timeup|scrollpos|sesskey|attempt$|thispage|nextpage|slots|itemid$/.test(
              rest,
            ) ||
            /^answer\d*format$/.test(rest)
          ) {
            return;
          }

          var el = this;
          var tag = el.tagName.toLowerCase();
          var type = (el.type || "").toLowerCase();

          // ── Tự gán id cho select thiếu id, tránh elementId rỗng khiến nút
          //    footer fallback về href gốc và gây reload/nhảy đầu trang ──────
          if (tag === "select" && !el.id) {
            el.id =
              "th-auto-" + slot + "-" + rest.replace(/[^a-zA-Z0-9_-]/g, "");
          }

          // ── Tính key ──────────────────────────────────────────────────────
          var key;
          if (type === "hidden" && DDWTOS_P_RE.test(rest)) {
            key = rest; // ddwtos / image hotspot: mỗi vị trí kéo-thả (p1, p2, ...) là 1 key riêng
          } else if (tag === "select" && /_(p)?\d+$/.test(rest)) {
            key = rest;
          } else {
            key = rest.split("_")[0]; // còn lại (kể cả checkbox _choiceN) gộp theo rootKey
          }
          // ─────────────────────────────────────────────────────────────────

          var filled = false;
          if (tag === "select") {
            var val = $(el).val();
            filled = val !== null && val !== "" && val !== "0";
          } else if (tag === "textarea") {
            filled = $.trim($(el).val()) !== "";
          } else if (type === "radio" || type === "checkbox") {
            filled = el.checked;
          } else if (type === "hidden" && DDWTOS_P_RE.test(rest)) {
            // ddwtos / image hotspot: filled nếu value không rỗng và không phải '0'
            filled = $.trim(el.value) !== "" && el.value !== "0";
          } else if (
            type !== "hidden" &&
            type !== "submit" &&
            type !== "button"
          ) {
            filled = $.trim($(el).val()) !== "";
          }

          // ── Lấy elementId ─────────────────────────────────────────────────
          var elId = "";
          if (type !== "hidden") {
            // Element tương tác thực: lấy id của chính nó hoặc wrapper
            elId = el.id || "";
            if (!elId) {
              var wrap = el.closest("[id]");
              elId = wrap ? wrap.id : "";
            }
          } else if (DDWTOS_P_RE.test(rest)) {
            var wrap = el.closest("[id]");
            elId = wrap ? wrap.id : "";
          }
          // ─────────────────────────────────────────────────────────────────

          if (!subMap[slot]) {
            subMap[slot] = {};
            subOrder[slot] = [];
          }
          if (!subMap[slot][key]) {
            subMap[slot][key] = { answered: filled, elementId: elId };
            subOrder[slot].push(key);
          } else {
            // Cập nhật answered nếu filled
            if (filled) {
              subMap[slot][key].answered = true;
            }
            // Cập nhật elementId nếu entry cũ chưa có
            if (!subMap[slot][key].elementId && elId) {
              subMap[slot][key].elementId = elId;
            }
          }

          // ── FIX: với radio/checkbox (multichoice), "elementId" ở trên luôn bị
          //    khoá vào option ĐẦU TIÊN trong nhóm (vì chỉ set khi entry chưa có
          //    elementId), bất kể học viên chọn option nào. Ở trang review, Moodle
          //    chỉ gắn class correct/incorrect lên chính option ĐÃ ĐƯỢC CHỌN, không
          //    gắn lên các option còn lại. Nếu tra theo option đầu tiên (thường
          //    không phải option được chọn), _reviewStateClassFor() sẽ không tìm
          //    thấy class ở gần đó và bị "leo" lên tới class tổng của cả câu hỏi
          //    (.que), dẫn tới hiện sai màu (vd đáp án đúng nhưng vẫn hiện đỏ).
          //    => lưu riêng id của (các) option đã checked để tra đúng/sai.
          if ((type === "radio" || type === "checkbox") && el.checked && elId) {
            if (!subMap[slot][key].checkedElementIds) {
              subMap[slot][key].checkedElementIds = [];
            }
            subMap[slot][key].checkedElementIds.push(elId);
          }
          // ─────────────────────────────────────────────────────────────────

          // ── NEW: lấy limit (số đáp án cần chọn) cho checkbox group từ data-correct-count ──
          if (type === "checkbox") {
            if (subMap[slot][key].limit === undefined) {
              var multiContainer = el.closest(".que.multichoice");
              var limitAttr = multiContainer
                ? multiContainer.getAttribute("data-correct-count")
                : null;
              var limitVal = limitAttr ? parseInt(limitAttr, 10) : 0;
              subMap[slot][key].limit = limitVal > 0 ? limitVal : 1;
            }
            if (!checkedCounts[slot]) {
              checkedCounts[slot] = {};
            }
            if (checkedCounts[slot][key] === undefined) {
              checkedCounts[slot][key] = 0;
            }
            if (el.checked) {
              checkedCounts[slot][key]++;
            }
          }
          // ─────────────────────────────────────────────────────────────────
        });

      // ── NEW: với group checkbox có limit > 1, "answered" chỉ true khi đã chọn đủ số lượng yêu cầu ──
      Object.keys(subMap).forEach(function (slotKey) {
        Object.keys(subMap[slotKey]).forEach(function (key) {
          var sub = subMap[slotKey][key];
          if (sub.limit && sub.limit > 1) {
            var checkedNum =
              (checkedCounts[slotKey] && checkedCounts[slotKey][key]) || 0;
            sub.answered = checkedNum >= sub.limit;
          }
        });
      });
      // ─────────────────────────────────────────────────────────────────────

      // ── 5. Read source buttons, group by href anchor ──────────────────────
      var anchorOrder = [];
      var anchorGroups = {};

      $("#th-nav-source")
        .find("a.qnbutton")
        .each(function () {
          var href = this.getAttribute("href") || "";
          var anchor = href.replace(/^.*#/, "");
          if (!anchor) {
            return;
          }
          if (!anchorGroups[anchor]) {
            anchorGroups[anchor] = [];
            anchorOrder.push(anchor);
          }
          anchorGroups[anchor].push(this);
        });

      // ── 6. Detect active anchor from URL hash ─────────────────────────────.
      var currentHash = (window.thPendingHash || "").replace(/^#/, "");

      // ── 7. Build parts and inject into footer ─────────────────────────────
      var container = document.getElementById("th-footer-parts-container");
      var globalCounter = 1;

      var anchorToQueIds = {};

      function _extractAnswerIndex(key) {
        var m = key.match(/^answer(\d+)/);
        if (m) {
          return parseInt(m[1], 10);
        }
        var m2 = key.match(/(\d+)(?!.*\d)/);
        if (m2) {
          return parseInt(m2[1], 10);
        }
        return 0;
      }

      function _getHotspotOrder(elementId) {
        if (!elementId) {
          return null;
        }
        var el = document.getElementById(elementId);
        if (!el) {
          return null;
        }
        var hotspot = el.closest(
          ".hotspot, .hotspot-editable, .atto-thimageeditor-hotspot",
        );
        if (!hotspot) {
          return null;
        }

        var orderAttr = hotspot.getAttribute("data-label");
        if (orderAttr === null) {
          return null;
        }
        var n = parseInt(orderAttr, 10);
        return isNaN(n) ? null : n;
      }

      function _getQueElForKey(slotNum, key) {
        var sub = subMap[slotNum] && subMap[slotNum][key];
        if (!sub || !sub.elementId) {
          return null;
        }
        var el = document.getElementById(sub.elementId);
        if (!el) {
          return null;
        }
        return el.closest(".que");
      }

      function _getQuestionAnswerRangeForQueEl(queEl) {
        if (!queEl) {
          return null;
        }
        var marker = queEl.querySelector(
          ".th_question_answer, #th_question_answer",
        );
        if (!marker) {
          return null;
        }
        var range = marker.getAttribute("value");
        if (!range) {
          return null;
        }
        range = range.replace(/[“”]/g, "").replace(/"/g, "").trim();
        var parts = range.split("-");
        var start = parseInt(parts[0], 10);
        if (isNaN(start)) {
          return null;
        }
        var end = parts.length > 1 ? parseInt(parts[1], 10) : NaN;
        return { start: start, end: isNaN(end) ? null : end };
      }

      anchorOrder.forEach(function (anchor, partIdx) {
        var srcButtons = anchorGroups[anchor];
        var partNum = partIdx + 1;

        var btnEls = [];
        var queIds = [];

        var targetHashes = [];

        srcButtons.forEach(function (srcBtn) {
          var idMatch = (srcBtn.id || "").match(/quiznavbutton(\d+)/);
          var baseClass = srcBtn.className || "";
          var isFlagged = baseClass.indexOf("flagged") !== -1;
          var btnHref = srcBtn.getAttribute("href") || "#" + anchor;

          var hrefAnchor = btnHref.replace(/^.*#/, "");
          if (hrefAnchor && queIds.indexOf(hrefAnchor) === -1) {
            queIds.push(hrefAnchor);
          }

          if (!idMatch) {
            var groupHasRealSubs = srcButtons.some(function (other) {
              if (other === srcBtn) {
                return false;
              }
              var om = (other.id || "").match(/quiznavbutton(\d+)/);
              if (!om) {
                return false;
              }
              var os = subMap[parseInt(om[1], 10)];
              return os && Object.keys(os).length > 0;
            });
            if (groupHasRealSubs) {
              return;
            }

            var b = document.createElement("a");
            b.href = btnHref;
            if (isReviewPage) {
              var anchorEl0 = hrefAnchor
                ? document.getElementById(hrefAnchor)
                : null;
              var queEl0 = anchorEl0
                ? anchorEl0.closest(".que") ||
                  (anchorEl0.classList.contains("que") ? anchorEl0 : null)
                : null;
              b.className =
                "th-sub-btn " + _reviewStateClassFor(hrefAnchor, queEl0);
            } else {
              b.className = "th-sub-btn notyetanswered";
            }
            b.textContent = globalCounter;
            b.title = "Question " + globalCounter;
            b.dataset.thWeight = "1";

            var slotElForNoId = hrefAnchor
              ? document.getElementById(hrefAnchor)
              : null;
            var queForNoId = slotElForNoId
              ? slotElForNoId.closest("[id^='question-']") ||
                (slotElForNoId.id && /^question-/.test(slotElForNoId.id)
                  ? slotElForNoId
                  : null)
              : null;
            var slotMatchForNoId = queForNoId
              ? queForNoId.id.match(/^question-\d+-(\d+)$/)
              : null;
            if (slotMatchForNoId) {
              b.dataset.thSlot = slotMatchForNoId[1];
            }
            targetHashes.push(hrefAnchor);
            btnEls.push(b);
            globalCounter++;
            return;
          }

          var slotNum = parseInt(idMatch[1], 10);
          var subs = subOrder[slotNum] ? subOrder[slotNum].slice() : [];

          var isSplitLayout = false;
          for (var si = 0; si < subs.length; si++) {
            var refElId = subMap[slotNum][subs[si]].elementId;
            if (!refElId) {
              continue;
            }
            var refEl = document.getElementById(refElId);
            if (
              refEl &&
              (refEl.closest(".formulation.th-split") ||
                refEl.closest(".th-col-left, .th-col-right"))
            ) {
              isSplitLayout = true;
              break;
            }
          }

          // ── Gom subs theo .que thực sự của từng key, và xếp thứ tự các
          //    nhóm .que theo VỊ TRÍ THẬT trong DOM (compareDocumentPosition)
          //    - KHÔNG dựa vào thứ tự xuất hiện trong `subs` (mảng scan gốc),
          //    vì bước sort isSplitLayout ngay dưới đây sort theo số hậu tố
          //    lấy từ tên field, mà các câu hỏi con khác dạng trong cùng 1
          //    cluster có thể đặt tên field theo quy ước khác nhau (vd cloze
          //    dùng "answerN", multianswer/cloze lồng trong cluster dùng
          //    "subXY" với X = số con trong câu hỏi con, Y = vị trí câu hỏi
          //    con đó trong cluster) khiến so sánh số học bị sai: "answer8"
          //    (vị trí cluster 8) < "sub16" (vị trí cluster 6, câu con 1) về
          //    mặt số học dù que của "sub16" thực sự đứng TRƯỚC "answer8"
          //    trên trang -> multianswer bị đẩy lên trước cloze. Tính nhóm
          //    theo DOM position thật trước, rồi mới numeric-sort làm
          //    tie-break TRONG cùng 1 nhóm .que, để tránh lỗi này.
          var _queGroupOrder = [];
          var _keyToQueEl = {};
          subs.forEach(function (key) {
            var que = _getQueElForKey(slotNum, key);
            _keyToQueEl[key] = que;
            if (que && _queGroupOrder.indexOf(que) === -1) {
              _queGroupOrder.push(que);
            }
          });
          if (_queGroupOrder.length > 1) {
            _queGroupOrder.sort(function (qa, qb) {
              if (qa === qb) {
                return 0;
              }
              var pos = qa.compareDocumentPosition(qb);
              if (pos & Node.DOCUMENT_POSITION_FOLLOWING) {
                return -1;
              }
              if (pos & Node.DOCUMENT_POSITION_PRECEDING) {
                return 1;
              }
              return 0;
            });
          }

          if (isSplitLayout) {
            subs = subs.slice().sort(function (a, b) {
              var qa = _keyToQueEl[a];
              var qb = _keyToQueEl[b];
              var ga = qa ? _queGroupOrder.indexOf(qa) : Infinity;
              var gb = qb ? _queGroupOrder.indexOf(qb) : Infinity;
              if (ga !== gb) {
                return ga - gb;
              }
              return _extractAnswerIndex(a) - _extractAnswerIndex(b);
            });
          }

          // FIX: khi 1 slot chứa ≥2 câu hỏi cloze con nằm ở 2 (hoặc nhiều)
          // .que khác nhau (vd 2 đoạn hội thoại/đoạn văn cloze nối tiếp nhau),
          // nếu các <input> của chúng không nằm liền mạch trong DOM theo đúng
          // thứ tự đọc (bị xen kẽ input-A1, input-B1, input-A2, input-B2,...)
          // thì đánh số tuần tự theo thứ tự scan sẽ ra kết quả xen kẽ kiểu
          // "21 22 23 24..." bị tách thành "21 23 25..." (đoạn A) xen giữa
          // "22 24 26..." (đoạn B). Dùng lại _queGroupOrder/_keyToQueEl (đã
          // tính theo DOM position thật ở trên) để phát hiện + sửa, chỉ khi
          // thực sự bị xen kẽ (để không đụng tới các trường hợp bình thường,
          // vốn dĩ đã đúng thứ tự).
          if (_queGroupOrder.length > 1) {
            var _lastGroupIdx = -1;
            var _isInterleaved = false;
            for (var ii = 0; ii < subs.length; ii++) {
              var q = _keyToQueEl[subs[ii]];
              var gIdx = q ? _queGroupOrder.indexOf(q) : -1;
              if (gIdx !== -1) {
                if (gIdx < _lastGroupIdx) {
                  _isInterleaved = true;
                  break;
                }
                _lastGroupIdx = gIdx;
              }
            }
            if (_isInterleaved) {
              subs = subs.slice().sort(function (a, b) {
                var qa = _keyToQueEl[a];
                var qb = _keyToQueEl[b];
                var ga = qa ? _queGroupOrder.indexOf(qa) : Infinity;
                var gb = qb ? _queGroupOrder.indexOf(qb) : Infinity;
                if (ga !== gb) {
                  return ga - gb;
                }
                return _extractAnswerIndex(a) - _extractAnswerIndex(b);
              });
            }
          }

          var hasHotspotOrder = subs.some(function (key) {
            return (
              DDWTOS_P_RE.test(key) &&
              _getHotspotOrder(subMap[slotNum][key].elementId) !== null
            );
          });

          if (hasHotspotOrder) {
            subs = subs.slice().sort(function (a, b) {
              var oa = _getHotspotOrder(subMap[slotNum][a].elementId);
              var ob = _getHotspotOrder(subMap[slotNum][b].elementId);
              oa = oa === null ? Infinity : oa;
              ob = ob === null ? Infinity : ob;
              return oa - ob;
            });
          }

          var _lastQueEl = null;
          var _lastQRange = null;

          function _applyMarkerForKey(key) {
            var que = _getQueElForKey(slotNum, key);
            if (que === _lastQueEl) {
              return;
            }
            if (_lastQRange && _lastQRange.end !== null) {
              globalCounter = _lastQRange.end + 1;
            }
            _lastQueEl = que;
            var newRange = _getQuestionAnswerRangeForQueEl(que);
            _lastQRange = newRange;
            if (newRange) {
              if (globalCounter !== newRange.start) {
                console.warn(
                  "[quiz_footer] Lệch số câu: globalCounter tự nhiên=" +
                    globalCounter +
                    " nhưng marker th_question_answer yêu cầu bắt đầu=" +
                    newRange.start +
                    " (slot " +
                    slotNum +
                    ", key " +
                    key +
                    ")",
                );
              }
              // FIX: khi 1 slot có ≥2 câu hỏi cloze con (nhiều .que khác nhau
              // trong cùng slot), mỗi .que mới đều gọi tới đây. Nếu marker của
              // .que sau lại trỏ về 1 số NHỎ HƠN HOẶC BẰNG số đang đếm (marker
              // trùng lặp / copy-paste chưa cập nhật giữa các câu con), tuyệt
              // đối KHÔNG được lùi globalCounter lại - làm vậy sẽ khiến các
              // câu con sau bị đánh trùng số với câu con trước (vd tất cả cùng
              // hiện "21"). Chỉ áp dụng marker khi nó thực sự tiến VỀ PHÍA
              // TRƯỚC (>= số đang đếm tự nhiên); marker lùi bị bỏ qua, để đếm
              // tự nhiên tiếp tục chạy.
              if (newRange.start >= globalCounter) {
                globalCounter = newRange.start;
              } else {
                console.warn(
                  "[quiz_footer] Bỏ qua marker th_question_answer bị lùi số (start=" +
                    newRange.start +
                    " < globalCounter=" +
                    globalCounter +
                    ") - có thể do nhiều câu hỏi cloze con trong slot " +
                    slotNum +
                    " dùng chung 1 marker; đang giữ đếm tự nhiên.",
                );
              }
            } else if (que) {
              console.warn(
                "[quiz_footer] Không tìm thấy marker th_question_answer cho slot " +
                  slotNum +
                  ", key " +
                  key +
                  " - đang dùng đếm tự nhiên (globalCounter=" +
                  globalCounter +
                  ")",
              );
            }
          }

          if (subs.length === 0) {
            var _emptySubQueEl = null;
            if (hrefAnchor) {
              var _anchorEl = document.getElementById(hrefAnchor);
              _emptySubQueEl = _anchorEl ? _anchorEl.closest(".que") : null;
              if (
                !_emptySubQueEl &&
                _anchorEl &&
                _anchorEl.classList.contains("que")
              ) {
                _emptySubQueEl = _anchorEl;
              }
            }
            var _emptySubRange =
              _getQuestionAnswerRangeForQueEl(_emptySubQueEl);
            if (_emptySubRange) {
              if (globalCounter !== _emptySubRange.start) {
                console.warn(
                  "[quiz_footer] Lệch số câu (slot rỗng sub): globalCounter tự nhiên=" +
                    globalCounter +
                    " nhưng marker yêu cầu=" +
                    _emptySubRange.start +
                    " (slot " +
                    slotNum +
                    ")",
                );
              }
              globalCounter = _emptySubRange.start;
            }
            var b = document.createElement("a");
            b.href = btnHref;
            if (isReviewPage) {
              b.className =
                "th-sub-btn " +
                _reviewStateClassFor(hrefAnchor, _emptySubQueEl);
            } else {
              b.className = "th-sub-btn notyetanswered";
            }
            b.textContent = globalCounter;
            b.title = "Question " + globalCounter;
            b.dataset.thWeight = "1";
            b.dataset.thSlot = slotNum;
            targetHashes.push(hrefAnchor);
            btnEls.push(b);
            globalCounter =
              _emptySubRange && _emptySubRange.end !== null
                ? _emptySubRange.end + 1
                : globalCounter + 1;
          } else {
            subs.forEach(function (key) {
              _applyMarkerForKey(key);
              var sub = subMap[slotNum][key];
              var correctCount = sub.limit && sub.limit > 1 ? sub.limit : 1;
              if (!sub.elementId) {
                var prefix = key.split("_")[0];
                var hasSibling = subs.some(function (otherKey) {
                  return (
                    otherKey !== key &&
                    (/^answer\d*_choice\d+$/.test(otherKey) ||
                      DDWTOS_P_RE.test(otherKey)) &&
                    otherKey.split("_")[0] === prefix &&
                    subMap[slotNum][otherKey].elementId
                  );
                });
                if (hasSibling) {
                  return;
                }
              }

              var b = document.createElement("a");
              b.href = sub.elementId
                ? btnHref.split("#")[0] + "#" + sub.elementId
                : btnHref;
              targetHashes.push(sub.elementId || hrefAnchor);
              b.dataset.thSlot = slotNum;
              var cls = "th-sub-btn";
              if (isReviewPage) {
                // Ở review: màu sắc phản ánh đúng/sai/chưa trả lời. Chưa
                // trả lời luôn ưu tiên hiển thị xám (notanswered), dù Moodle
                // có gắn class "incorrect" cho ô trống hay không. Chỉ khi đã
                // thực sự điền/chọn mới tra đúng/sai/1 phần từ Moodle.
                // "flagged" (đánh dấu để xem lại lúc làm bài) chỉ là badge
                // phụ, không thay thế màu.
                if (!_wasSubAttempted(slotNum, key, sub)) {
                  cls += " th-review-notanswered";
                } else {
                  // FIX: với multichoice (radio/checkbox), tra theo đúng option
                  // đã chọn trước - tránh bị leo lên class tổng của .que (vốn có
                  // thể là "partiallycorrect"/"incorrect" dù đáp án này đúng).
                  var mcState = _multichoiceReviewStateFor(sub, _lastQueEl);
                  cls +=
                    " " +
                    (mcState || _reviewStateClassFor(sub.elementId, _lastQueEl));
                }
                if (isFlagged) {
                  cls += " flagged";
                }
              } else if (isFlagged) {
                cls += " flagged";
              } else if (sub.answered) {
                cls += " complete";
              } else {
                cls += " notyetanswered";
              }
              b.className = cls;

              var label;

              if (correctCount > 1) {
                var nums = [];
                for (var i = 0; i < correctCount; i++) {
                  nums.push(globalCounter + i);
                }
                label = nums.join("-");
                // console.log('correctCount > 1, nums=', nums, 'label=', label);
                globalCounter += correctCount;
              } else {
                label = String(globalCounter);
                // console.log('correctCount <= 1, label=', label);
                globalCounter++;
              }

              // console.log('final label=', label, 'b.textContent before=', b.textContent);
              b.textContent = label;
              b.title = "Question " + label;
              // NEW: 1 button có thể đại diện cho nhiều câu hỏi con (cluster
              // multianswer, vd nhãn "11-12" = 2 câu). Lưu lại số câu thực tế
              // để đếm đúng trong "X of Y" ở footer, thay vì luôn tính là 1.
              b.dataset.thWeight = String(correctCount > 0 ? correctCount : 1);
              btnEls.push(b);
            });
          }
          if (_lastQRange && _lastQRange.end !== null) {
            globalCounter = _lastQRange.end + 1;
          }
        });

        anchorToQueIds[anchor] = queIds;

        // NEW: đếm theo "weight" (số câu hỏi thực tế mà 1 button đại diện)
        // thay vì đếm số button, để câu multianswer (vd nhãn "11-12") được
        // tính đủ 2 câu chứ không phải 1.
        var answered = 0;
        var total = 0;
        btnEls.forEach(function (b) {
          var w = parseInt(b.dataset.thWeight, 10) || 1;
          total += w;
          if (_isAnsweredBtnClass(b.className)) {
            answered += w;
          }
        });

        var isActive =
          currentHash === anchor ||
          targetHashes.indexOf(currentHash) !== -1 ||
          (partIdx === 0 && !currentHash);

        var partDiv = document.createElement("div");
        partDiv.className =
          "th-footer-part" + (isActive ? " th-active-part" : "");
        partDiv.setAttribute("data-anchor", anchor);

        var labelEl = document.createElement("span");
        labelEl.className = "th-part-label";
        labelEl.textContent = "Part " + partNum;

        var countEl = document.createElement("span");
        countEl.className = "th-part-count";
        countEl.textContent = answered + " of " + total;

        var listEl = document.createElement("div");
        listEl.className = "th-footer-qn-list";
        btnEls.forEach(function (b) {
          listEl.appendChild(b);
        });

        partDiv.appendChild(labelEl);
        partDiv.appendChild(countEl);
        partDiv.appendChild(listEl);
        container.appendChild(partDiv);
      });

      if (window.thPendingHash) {
        var hash = window.thPendingHash.replace(/^#/, "");

        container.querySelectorAll(".th-sub-btn").forEach(function (btn) {
          if ((btn.getAttribute("href") || "").split("#")[1] === hash) {
            btn.classList.add("th-active");

            requestAnimationFrame(function () {
              btn.click();
            });
          }
        });
      }
      if (
        !container.querySelector(".th-active-part") &&
        container.firstElementChild
      ) {
        container.firstElementChild.classList.add("th-active-part");
      }

      function _hideFormulation(el) {
        el.classList.add("th-force-hidden");
        el.style.setProperty("display", "none", "important");
      }

      function _showFormulation(el) {
        el.classList.remove("th-force-hidden");
        el.style.removeProperty("display");
      }

      function _updateFormulationVisibility() {
        var activePart = container.querySelector(
          ".th-footer-part.th-active-part",
        );
        var activeAnchor = activePart
          ? activePart.getAttribute("data-anchor")
          : "";

        document.querySelectorAll(".que .formulation").forEach(function (el) {
          _hideFormulation(el);
        });

        if (!activeAnchor) {
          document.querySelectorAll(".que .formulation").forEach(function (el) {
            _showFormulation(el);
          });
          return;
        }

        var queIds = anchorToQueIds[activeAnchor] || [];

        queIds.forEach(function (queId) {
          var queEl = document.getElementById(queId);
          if (!queEl) {
            return;
          }
          var queWrap = queEl.closest(".que") || queEl;
          queWrap.querySelectorAll(".formulation").forEach(function (f) {
            _showFormulation(f);
          });
        });

        if (queIds.length === 0) {
          var anchorEl = document.getElementById(activeAnchor);
          if (anchorEl) {
            var queWrap = anchorEl.closest(".que") || anchorEl;
            queWrap.querySelectorAll(".formulation").forEach(function (f) {
              _showFormulation(f);
            });
          }
        }

        window.dispatchEvent(new Event("resize"));
        if (window.requestAnimationFrame) {
          requestAnimationFrame(function () {
            window.dispatchEvent(new Event("resize"));
          });
        }
      }

      function _getAllBtns() {
        return Array.from(container.querySelectorAll("a.th-sub-btn"));
      }

      function _pauseAllMedia() {
        var mediaEls = document.querySelectorAll("video, audio");
        mediaEls.forEach(function (m) {
          if (!m.paused) {
            m.pause();
          }
        });
        if (window.videojs && typeof videojs.getPlayers === "function") {
          var players = videojs.getPlayers();
          Object.keys(players).forEach(function (key) {
            var p = players[key];
            if (p && !p.paused()) {
              p.pause();
            }
          });
        }
      }

      function _activateBtn(btn) {
        _pauseAllMedia();
        _getAllBtns().forEach(function (b) {
          b.classList.remove("th-current");
        });
        btn.classList.add("th-current");

        var part = btn.closest(".th-footer-part");
        if (part && !part.classList.contains("th-active-part")) {
          container.querySelectorAll(".th-footer-part").forEach(function (p) {
            p.classList.remove("th-active-part");
          });
          part.classList.add("th-active-part");
          _updateFormulationVisibility();

          document.dispatchEvent(
            new CustomEvent("th:partchanged", {
              detail: {
                partId: part.dataset.partid || part.id,
              },
            }),
          );
        }

        if (btn.scrollIntoView) {
          btn.scrollIntoView({ block: "nearest", inline: "nearest" });
        }

        var href = btn.getAttribute("href") || "";
        var hashIdx = href.lastIndexOf("#");
        var targetId = hashIdx !== -1 ? href.slice(hashIdx + 1) : "";
        var targetEl = targetId ? document.getElementById(targetId) : null;

        if (targetEl) {
          var scrollTarget = targetEl;
          var queWrap = null;

          // ── Xử lý đặc biệt cho ddwtos ──
          if (
            targetEl.tagName.toLowerCase() === "input" &&
            (targetEl.type || "").toLowerCase() === "hidden"
          ) {
            var pMatch = (targetEl.name || "").match(/_p(\d+)$/);
            if (pMatch) {
              var placeNum = pMatch[1];
              queWrap = targetEl.closest(".que");
              if (queWrap) {
                var dropZone = queWrap.querySelector(
                  'span.place[data-place="' +
                    placeNum +
                    '"],' +
                    'span[data-place="' +
                    placeNum +
                    '"],' +
                    '.drop[data-place="' +
                    placeNum +
                    '"],' +
                    ".place" +
                    placeNum,
                );
                if (dropZone) scrollTarget = dropZone;
              }
            }
          }

          function _isScrollableNode(node) {
            if (!node || node.nodeType !== 1) {
              return false;
            }
            var style = window.getComputedStyle(node);
            if (style.overflowY !== "auto" && style.overflowY !== "scroll") {
              return false;
            }
            return node.scrollHeight > node.clientHeight + 1;
          }

          function _findScrollableAncestor(el) {
            var node = el.parentElement;
            while (node && node !== document.documentElement) {
              if (_isScrollableNode(node)) {
                return node;
              }
              node = node.parentElement;
            }
            return null;
          }

          function scrollElIntoCol(el) {
            var formulation =
              el.closest(".formulation.th-split") || el.closest(".formulation");
            if (formulation) {
              var col =
                el.closest(".th-col-left, .th-col-right") ||
                _findScrollableAncestor(el);
              if (col) {
                var colRect = col.getBoundingClientRect();
                var targetRect = el.getBoundingClientRect();
                var offset = targetRect.top - colRect.top + col.scrollTop - 250;
                col.scrollTo({
                  top: Math.max(offset, 0),
                  behavior: "smooth",
                });
                return;
              }
            }
            el.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
          }

          function _findDescMarkerForNumber(scopeEl, num) {
            if (!scopeEl) {
              return null;
            }
            var markers = scopeEl.querySelectorAll(
              ".th_question_answer, #th_question_answer",
            );
            if (markers.length === 0) {
              return null;
            }
            if (num !== null) {
              for (var i = 0; i < markers.length; i++) {
                var range = markers[i].getAttribute("value");
                if (!range) {
                  continue;
                }
                range = range.replace(/[“”]/g, "").replace(/"/g, "").trim();
                var parts = range.split("-");
                var start = parseInt(parts[0], 10);
                if (isNaN(start)) {
                  continue;
                }
                var end = parts.length > 1 ? parseInt(parts[1], 10) : start;
                if (isNaN(end)) {
                  end = start;
                }
                if (num >= start && num <= end) {
                  return markers[i];
                }
              }
            }
            return markers[0];
          }

          function _parseBtnNumber(b) {
            var txt = (b.textContent || "").trim();
            var m = txt.match(/\d+/);
            return m ? parseInt(m[0], 10) : null;
          }

          function _colOf(el) {
            return el ? el.closest(".th-col-left, .th-col-right") : null;
          }

          function _doScrolls() {
            if (scrollTarget.closest(".qtext")) {
              scrollElIntoCol(scrollTarget);
              return;
            }

            var queForDesc =
              scrollTarget.closest(".que") ||
              (targetEl && targetEl.closest(".que"));
            var btnNum = _parseBtnNumber(btn);
            var marker = queForDesc
              ? _findDescMarkerForNumber(queForDesc, btnNum)
              : null;
            var descTarget = marker
              ? marker.closest(".qtext") || marker.parentElement || marker
              : null;

            if (descTarget) {
              scrollElIntoCol(descTarget);
            }

            var descCol = _colOf(descTarget);
            var answerCol = _colOf(scrollTarget);

            if (
              !descTarget ||
              (descCol && answerCol && descCol !== answerCol)
            ) {
              scrollElIntoCol(scrollTarget);
            }
          }

          _doScrolls();

          (function () {
            var scopeEl =
              scrollTarget.closest(".que") ||
              (targetEl && targetEl.closest(".que"));
            if (!scopeEl) {
              return;
            }
            var pendingImgs = Array.prototype.filter.call(
              scopeEl.querySelectorAll("img"),
              function (img) {
                return !img.complete;
              },
            );
            if (pendingImgs.length === 0) {
              return;
            }
            var remaining = pendingImgs.length;
            pendingImgs.forEach(function (img) {
              function _onImgDone() {
                img.removeEventListener("load", _onImgDone);
                img.removeEventListener("error", _onImgDone);
                remaining--;
                if (remaining <= 0) {
                  _doScrolls();
                }
              }
              img.addEventListener("load", _onImgDone);
              img.addEventListener("error", _onImgDone);
            });
          })();

          if (queWrap) {
            var answerContainer = queWrap.querySelector(".answercontainer");
            var placeholder = answerContainer && answerContainer.thPlaceholder;
            if (placeholder) {
              scrollElIntoCol(placeholder);
            }
          }

          if (scrollTarget.focus) {
            scrollTarget.focus({ preventScroll: true });
          }
        } else if (href) {
          var currentUrl = window.location.href.split("#")[0];
          var hrefBase = href.split("#")[0];
          if (hrefBase && hrefBase !== currentUrl) {
            window.location.href = href;
          }
        }
      }

      function _updatePrevState() {
        var all = _getAllBtns();
        var current = container.querySelector("a.th-sub-btn.th-current");
        var idx = current ? all.indexOf(current) : -1;
        var prevLabel = document.querySelector(".th-nav-btn.mod_quiz-prev-nav");
        var prevInput = document.getElementById("mod_quiz-prev-nav");
        var isFirst = idx <= 0;
        if (prevLabel) {
          prevLabel.classList.toggle("disabled", isFirst);
        }
        if (prevInput) {
          prevInput.disabled = isFirst;
        }
      }

      function _navStep(direction) {
        var all = _getAllBtns();
        if (!all.length) {
          return false;
        }

        var current = container.querySelector("a.th-sub-btn.th-current");
        var idx = current ? all.indexOf(current) : -1;
        var nextIdx;

        if (direction === "prev") {
          if (idx <= 0) {
            return false;
          }
          nextIdx = idx - 1;
        } else {
          if (idx === -1) {
            nextIdx = 0;
          } else {
            nextIdx = idx + 1;
          }
        }

        if (nextIdx >= all.length) {
          return false;
        }

        _activateBtn(all[nextIdx]);
        _updatePrevState();
        return true;
      }

      // ── 8. Click handler footer ───────────────────────────────────────────
      container.addEventListener("click", function (e) {
        var link = e.target.closest("a.th-sub-btn");
        if (link) {
          e.preventDefault();
          e.stopPropagation();
          _activateBtn(link);
          _updatePrevState();
          _saveFooterSnapshot(footer);
          return;
        }

        var part = e.target.closest(".th-footer-part");
        if (!part) {
          return;
        }

        if (part.classList.contains("th-active-part")) {
          return;
        }

        container.querySelectorAll(".th-footer-part").forEach(function (p) {
          p.classList.remove("th-active-part");
        });
        part.classList.add("th-active-part");
        _updateFormulationVisibility();

        document.dispatchEvent(
          new CustomEvent("th:partchanged", {
            detail: {
              partId: part.dataset.partid || part.id,
            },
          }),
        );

        var firstBtn = part.querySelector(".th-sub-btn");
        if (firstBtn) {
          _activateBtn(firstBtn);
          _updatePrevState();
        }
        _saveFooterSnapshot(footer);
      });

      // ── 9. Khởi tạo ──────────────────────────────────────────────────────
      _updateFormulationVisibility();
      _updatePrevState();
      _saveFooterSnapshot(footer);

      var prevInput = document.getElementById("mod_quiz-prev-nav");
      var nextInput = document.getElementById("mod_quiz-next-nav");

      if (prevInput) {
        prevInput.addEventListener(
          "click",
          function (e) {
            var handled = _navStep("prev");
            if (handled) {
              e.preventDefault();
              e.stopImmediatePropagation();
            }
          },
          true,
        );
      }

      if (nextInput) {
        nextInput.addEventListener(
          "click",
          function (e) {
            var handled = _navStep("next");
            if (handled) {
              e.preventDefault();
              e.stopImmediatePropagation();
            } else {
              var endLink = finishWrap
                ? finishWrap.querySelector("a.endtestlink")
                : null;
              if (endLink) {
                e.preventDefault();
                e.stopImmediatePropagation();
                _doFinish();
              }
            }
          },
          true,
        );
      }

      // ── 10. Build lookup maps: elementId / name → th-sub-btn ─────────────
      var idToBtn = {};
      var nameToBtn = {};

      _getAllBtns().forEach(function (btn) {
        var href = btn.getAttribute("href") || "";
        var hashIdx = href.lastIndexOf("#");
        var targetId = hashIdx !== -1 ? href.slice(hashIdx + 1) : "";
        if (targetId) {
          idToBtn[targetId] = btn;
        }
      });

      _getAllBtns().forEach(function (btn) {
        var qNum = btn.textContent.trim();

        var href = btn.getAttribute("href") || "";
        var hashIdx = href.lastIndexOf("#");
        var targetId = hashIdx !== -1 ? href.slice(hashIdx + 1) : "";

        if (!targetId) {
          return;
        }

        var targetEl = document.getElementById(targetId);
        if (!targetEl) {
          return;
        }

        var inputEl = null;
        var elTag = (targetEl.tagName || "").toLowerCase();
        var elType = (targetEl.getAttribute("type") || "").toLowerCase();

        if (
          elTag === "input" &&
          elType === "hidden" &&
          /_p\d+$/.test(targetEl.name || "")
        ) {
          return;
        }

        if (
          elTag === "input" &&
          !/^(hidden|submit|button|checkbox|radio)$/.test(elType)
        ) {
          inputEl = targetEl;
        } else if (elTag === "select" || elTag === "textarea") {
          inputEl = targetEl;
        } else {
          inputEl = targetEl.querySelector(
            'input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"]), select, textarea',
          );
        }

        if (!inputEl) {
          return;
        }

        var inputTag = (inputEl.tagName || "").toLowerCase();

        if (inputTag === "select") {
          var firstOpt = inputEl.options[0];

          if (firstOpt && !/^\d+\./.test((firstOpt.text || "").trim())) {
            firstOpt.text = qNum + " " + firstOpt.text;
          }
        } else {
          var existingPH = inputEl.getAttribute("placeholder") || "";

          if (!/^\d+\./.test(existingPH.trim())) {
            inputEl.setAttribute("placeholder", qNum + " " + existingPH);
          }
        }
      });

      // Map name → btn: dùng subMap để tìm elementId của từng key
      $scanRoot
        .find("input[name], select[name], textarea[name]")
        .each(function () {
          var elName = this.name || "";
          var m = elName.match(/^q\d+:(\d+)_(.+)$/);
          if (!m) {
            return;
          }
          var slot = parseInt(m[1], 10);
          var rest = m[2];
          var type = (this.type || "").toLowerCase();
          var tag = this.tagName.toLowerCase();

          var key;
          if (type === "hidden" && DDWTOS_P_RE.test(rest)) {
            key = rest; // ddwtos / image hotspot: mỗi vị trí kéo-thả (p1, p2, ...) là 1 key riêng
          } else if (tag === "select" && /_(p)?\d+$/.test(rest)) {
            key = rest;
          } else {
            key = rest.split("_")[0]; // còn lại (kể cả checkbox _choiceN) gộp theo rootKey
          }

          if (!subMap[slot] || !subMap[slot][key]) {
            return;
          }
          var elementId = subMap[slot][key].elementId;
          if (elementId && idToBtn[elementId]) {
            nameToBtn[elName] = idToBtn[elementId];
          }
        });

      // ── 11. Sync th-current khi user tương tác trực tiếp vào form element ─
      $scanRoot.find("input, select, textarea").each(function () {
        var el = this;
        var type = (el.type || "").toLowerCase();
        if (/hidden|submit|button/.test(type)) {
          return;
        }

        var matchedBtn = nameToBtn[el.name] || idToBtn[el.id] || null;
        if (!matchedBtn) {
          return;
        }

        var btn = matchedBtn;
        $(el).on("change click", function () {
          if (!btn.classList.contains("th-current")) {
            _activateBtn(btn);
            _updatePrevState();
          }
        });
      });

      // ── 12. Live update trạng thái button khi user trả lời ───────────────
      function _isKeyAnswered(slotNum, key) {
        var answered = false;

        // NEW: với group checkbox có limit > 1, cần đếm tổng số checked
        // trong group và so sánh với limit thay vì "bất kỳ 1 ô nào được check".
        var sub = (subMap[slotNum] && subMap[slotNum][key]) || null;
        var limit = sub && sub.limit ? sub.limit : 1;
        var checkboxCount = 0;

        $scanRoot
          .find("input[name], select[name], textarea[name]")
          .each(function () {
            var n = $(this).attr("name") || "";
            var mm = n.match(/^q\d+:(\d+)_(.+)$/);
            if (!mm) {
              return;
            }
            var s = parseInt(mm[1], 10);
            var rest = mm[2];
            var t = (this.type || "").toLowerCase();
            var tag = this.tagName.toLowerCase();

            // Tính key theo cùng logic
            var k;
            if (t === "hidden" && DDWTOS_P_RE.test(rest)) {
              k = rest;
            } else if (tag === "select" && /_(p)?\d+$/.test(rest)) {
              k = rest;
            } else {
              k = rest.split("_")[0];
            }

            if (s !== slotNum || k !== key) {
              return;
            }

            var el = this;
            if (tag === "select") {
              var val = $(el).val();
              if (val !== null && val !== "" && val !== "0") {
                answered = true;
              }
            } else if (tag === "textarea") {
              if ($.trim($(el).val()) !== "") {
                answered = true;
              }
            } else if (t === "radio" || t === "checkbox") {
              if (t === "checkbox" && limit > 1) {
                // NEW: group nhiều checkbox có limit → đếm số lượng đã check
                if (el.checked) {
                  checkboxCount++;
                }
              } else if (el.checked) {
                answered = true;
              }
            } else if (t === "hidden" && DDWTOS_P_RE.test(rest)) {
              if ($.trim(el.value) !== "" && el.value !== "0") {
                answered = true;
              }
            } else if (!/hidden|submit|button/.test(t)) {
              if ($.trim($(el).val()) !== "") {
                answered = true;
              }
            }
          });

        // NEW: chỉ "answered" khi đã chọn đủ số lượng yêu cầu (limit)
        if (limit > 1) {
          answered = checkboxCount >= limit;
        }

        return answered;
      }

      function _updateBtnState(btn) {
        if (btn.classList.contains("flagged")) {
          return;
        }

        var href = btn.getAttribute("href") || "";
        var hashIdx = href.lastIndexOf("#");
        var targetId = hashIdx !== -1 ? href.slice(hashIdx + 1) : "";

        Object.keys(subMap).forEach(function (slotNum) {
          Object.keys(subMap[slotNum]).forEach(function (key) {
            var sub = subMap[slotNum][key];
            if (sub.elementId !== targetId) {
              return;
            }
            var nowAnswered = _isKeyAnswered(parseInt(slotNum, 10), key);
            sub.answered = nowAnswered;
            btn.classList.remove("complete", "notyetanswered");
            btn.classList.add(nowAnswered ? "complete" : "notyetanswered");
          });
        });
      }

      function _updatePartCount(partEl) {
        var btns = Array.from(partEl.querySelectorAll("a.th-sub-btn"));
        var answered = 0;
        var total = 0;
        btns.forEach(function (b) {
          var w = parseInt(b.dataset.thWeight, 10) || 1;
          total += w;

          if (_isAnsweredBtnClass(b.className)) {
            answered += w;
          }
        });
        var countEl = partEl.querySelector(".th-part-count");
        if (countEl) {
          countEl.textContent = answered + " of " + total;
        }
      }

      $scanRoot.find("input, select, textarea").each(function () {
        var el = this;
        var type = (el.type || "").toLowerCase();
        if (/hidden|submit|button/.test(type)) {
          return;
        }

        $(el).on("change input", function () {
          var btn = nameToBtn[el.name] || idToBtn[el.id] || null;
          if (!btn) {
            var wrap = el.closest("[id]");
            if (wrap) {
              btn = idToBtn[wrap.id] || null;
            }
          }
          if (!btn) {
            return;
          }

          _updateBtnState(btn);

          var partEl = btn.closest(".th-footer-part");
          if (partEl) {
            _updatePartCount(partEl);
          }
          _saveFooterSnapshot(footer);
        });
      });

      var ddwtoInputs = [];
      $scanRoot.find('input[type="hidden"]').each(function () {
        var n = this.name || "";
        var m = n.match(/^q\d+:(\d+)_(.+)$/);
        if (!m) {
          return;
        }
        var rest = m[2];
        if (DDWTOS_P_RE.test(rest)) {
          ddwtoInputs.push(this);
        }
      });

      function _dropZoneForDdwtoInput(inp) {
        var queEl = inp.closest(".que");
        if (!queEl) {
          return null;
        }
        var pMatch = (inp.name || "").match(/_p(\d+)$/);
        if (!pMatch) {
          return null;
        }
        var placeNum = pMatch[1];
        return queEl.querySelector(
          ".hotspot.place" +
            placeNum +
            ", .hotspot-editable.place" +
            placeNum +
            ", .atto-thimageeditor-hotspot.place" +
            placeNum +
            ", .drop.place" +
            placeNum +
            ', .drop[data-place="' +
            placeNum +
            '"]',
        );
      }

      function _btnByQuestionLabel(label) {
        if (!label) {
          return null;
        }
        var target = String(label);
        var all = _getAllBtns();
        for (var i = 0; i < all.length; i++) {
          if (all[i].textContent.trim() === target) {
            return all[i];
          }
        }
        return null;
      }

      ddwtoInputs.forEach(function (inp) {
        var dropZone = _dropZoneForDdwtoInput(inp);
        var wrap = inp.closest("[id]");
        var clickTarget = dropZone || wrap;
        if (!clickTarget) {
          return;
        }

        clickTarget.addEventListener(
          "click",
          function () {
            var btn = null;
            if (dropZone && dropZone.hasAttribute("data-label")) {
              btn = _btnByQuestionLabel(dropZone.getAttribute("data-label"));
            }
            if (!btn && wrap) {
              btn = idToBtn[wrap.id] || null;
            }
            if (!btn) {
              return;
            }
            if (!btn.classList.contains("th-current")) {
              _activateBtn(btn);
              _updatePrevState();
            }
          },
          true,
        );
      });

      ddwtoInputs.forEach(function (inp) {
        $(inp).on("change input", function () {
          var btn = nameToBtn[inp.name] || null;
          if (!btn) {
            return;
          }
          _updateBtnState(btn);
          var partEl = btn.closest(".th-footer-part");
          if (partEl) {
            _updatePartCount(partEl);
          }
          _saveFooterSnapshot(footer);
        });
      });

      if (ddwtoInputs.length > 0) {
        var _ddObserver = new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            if (
              mutation.type === "attributes" &&
              mutation.attributeName === "value"
            ) {
              var el = mutation.target;
              var btn = nameToBtn[el.name] || null;
              if (!btn) {
                return;
              }
              _updateBtnState(btn);
              var partEl = btn.closest(".th-footer-part");
              if (partEl) {
                _updatePartCount(partEl);
              }
              _saveFooterSnapshot(footer);
            }
          });
        });
        ddwtoInputs.forEach(function (inp) {
          _ddObserver.observe(inp, {
            attributes: true,
            attributeFilter: ["value"],
          });
        });
      }
    }
  }

  // Auto-run khi document ready hoặc ngay lập tức nếu đã ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();