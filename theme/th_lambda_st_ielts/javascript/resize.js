// ── FIX (mượt giao diện lúc bắt đầu bài): trước đây trang render xong 1
//    cột full-width rồi resize.js/ielts_header.js/quiz_footer.js mới lần
//    lượt tách cột, đo lại page-offset, và điền nội dung banner/footer -
//    người dùng thấy rõ layout "gãy"/nhảy trước mắt. Giải pháp: ẩn khối
//    nội dung chính bằng CSS mặc định (xem resize.css), 3 script tự báo
//    "xong phần của mình" qua thLayoutReady.mark(), khi đủ cả 3 (hoặc hết
//    TIMEOUT_MS an toàn) mới gắn class .th-layout-ready lên <html> để hiện
//    ra 1 lần bằng fade-in ngắn, thay vì để người dùng thấy từng bước dựng.
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
        // Báo cho code chạy sau (vd sync() của portal answercontainer trong
        // resize.js) biết đúng thời điểm trang thực sự lộ ra, để không phải
        // đoán qua thời gian - xem giải thích ở nơi dùng event này.
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

    var CONTAINER_ID = "th-quiz-questions";
    var STORAGE_KEY = "th_quiz_split_ratio";

    var portalSyncFns = [];
    var portalSyncScheduled = false;

    function syncAllPortalsNow() {
        portalSyncFns.forEach(function (fn) {
            fn();
        });
    }

    function scheduleSyncAllPortals() {
        if (portalSyncScheduled) {
            return;
        }
        portalSyncScheduled = true;
        requestAnimationFrame(function () {
            portalSyncScheduled = false;
            syncAllPortalsNow();
        });
    }

    // ── FIX: "Clear my choice" của qtype_multichoice KHÔNG được Moodle thêm
    //    mới vào DOM khi chọn đáp án - nó luôn có sẵn, chỉ ẩn/hiện qua CSS
    //    phản ứng theo thuộc tính "checked" (property) của radio. Thay đổi
    //    property này KHÔNG kích hoạt MutationObserver (chỉ theo dõi cây
    //    DOM/attribute), nên không thể phát hiện gián tiếp một cách đáng
    //    tin cậy. Giải pháp chắc chắn: gắn thẳng vào HÀNH ĐỘNG của người
    //    dùng (chọn đáp án / bấm Clear my choice) để ép đồng bộ lại NGAY,
    //    thay vì cố đoán khi nào layout đổi qua quan sát DOM gián tiếp.
    function hardResyncAll() {
        scheduleSyncAllPortals();
        // CSS phản ứng theo :checked cần 1 nhịp để trình duyệt tính lại
        // layout trước khi getBoundingClientRect() trong sync() cho kết quả
        // đúng - gọi lặp lại vài lần ở các mốc thời gian khác nhau để chắc
        // ăn dù cơ chế ẩn/hiện là CSS thuần hay có transition/animation.
        setTimeout(scheduleSyncAllPortals, 50);
        setTimeout(scheduleSyncAllPortals, 200);
    }

    function bindAnswerChangeResync() {
        var root = document.getElementById(CONTAINER_ID);
        if (!root || root.dataset.thResyncBound === "1") {
            return;
        }
        root.dataset.thResyncBound = "1";

        // "change" cho radio/checkbox (đã chọn/bỏ chọn đáp án) - bubbles nên
        // delegate được từ container gốc, không cần gắn riêng từng input.
        root.addEventListener("change", hardResyncAll, true);
        // Nút "Clear my choice" là <a role="button">, không phát "change"
        // trên chính nó (nó chỉ gián tiếp bỏ chọn radio) - bắt thêm "click"
        // để chắc chắn không bị lỡ nhịp.
        root.addEventListener("click", function (e) {
            if (e.target.closest(".qtype_multichoice_clearchoice")) {
                hardResyncAll();
            }
        }, true);
    }

    function init() {
        var container = document.getElementById(CONTAINER_ID);
        if (!container || container.dataset.thSplitDone === "1") {
            // Không có gì để tách cột (vd trang không phải attempt) -> không
            // được chặn reveal chờ mãi, báo xong ngay.
            window.thLayoutReady.mark("resize");
            return;
        }

        var bigQuestions = Array.prototype.slice.call(container.children).filter(function (el) {
            return el.classList && el.classList.contains("que");
        });

        bigQuestions.forEach(function (bigQuestion, bqIndex) {
            var formulation = bigQuestion.querySelector(".formulation");
            if (formulation) {
                splitFormulation(formulation, bqIndex === 0);
            }
        });

        container.dataset.thSplitDone = "1";

        bindAnswerChangeResync();
        triggerReflow();
        window.thLayoutReady.mark("resize");
    }

    function triggerReflow() {
        function fire() {
            window.dispatchEvent(new Event("resize"));
            // Báo cho ielts_header.js biết layout (portal hoá answercontainer,
            // tách cột trái/phải) vừa được tính lại - dùng để canh lại scroll
            // khi vào trang kèm #hash mà không bị đè/lệch do resize.js đổi
            // chiều cao nội dung sau khi trình duyệt đã tự nhảy tới anchor.
            window.dispatchEvent(new Event("th:layout-settled"));
        }
        fire();
        if (window.requestAnimationFrame) {
            requestAnimationFrame(fire);
        }
        setTimeout(fire, 150);
        setTimeout(fire, 500);
    }

    function splitFormulation(formulation, isFirstPart) {
        if (formulation.dataset.thSplitDone === "1") {
            return;
        }

        if (isFirstPart) {
            formulation.classList.add("th-first-part");
        }

        var parts = Array.prototype.slice.call(formulation.children);

        if (!parts.length) {
            formulation.dataset.thSplitDone = "1";
            return;
        }

        var splitIndex = -1;
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i];
            if (part.id === "th_left" ||
                (part.querySelector && part.querySelector("#th_left"))) {
                splitIndex = i;
                break;
            }
        }

        if (splitIndex === -1) {
            wrapSingleColumn(formulation, parts);
            return;
        }

        var left = document.createElement("div");
        left.className = "th-col-left";

        var resizer = document.createElement("div");
        resizer.className = "th-col-resizer";
        resizer.innerHTML =
            "<span class=\"th-col-resizer-highlight\"></span>" +
            "<span class=\"th-col-resizer-handle\">" +
                "<svg class=\"th-col-resizer-icon\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">" +
                    "<path d=\"M8 8L4 12L8 16\" stroke=\"#8a8a8a\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>" +
                    "<path d=\"M16 8L20 12L16 16\" stroke=\"#8a8a8a\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>" +
                    "<line x1=\"4\" y1=\"12\" x2=\"20\" y2=\"12\" stroke=\"#8a8a8a\" stroke-width=\"2\"/>" +
                "</svg>" +
            "</span>";

        var right = document.createElement("div");
        right.className = "th-col-right";

        var pendingPortals = [];

        parts.forEach(function (part, idx) {
            extractRightContent(part, right);

            var hasOwnLeft = idx <= splitIndex ||
                part.id === "th_left" ||
                (part.querySelector && part.querySelector("#th_left"));

            (hasOwnLeft ? left : right).appendChild(part);

            if (
                part.querySelector("fieldset.ablock") ||
                part.querySelector(".answercontainer") ||
                part.matches("fieldset.ablock") ||
                part.matches(".answercontainer")
            ) {
                extractAnswerFieldset(part, right, pendingPortals);
                extractAnswerContainer(part, right, pendingPortals);
            }
        });

        // FIX: câu hỏi không có answercontainer/fieldset.ablock (vd cluster,
        // hoặc bất kỳ dạng nào chỉ có feedback text/video đơn thuần) thì
        // .outcome (chứa .feedback/.generalfeedback, có thể có video) không
        // được extractAnswerFieldset/extractAnswerContainer ở trên "portal"
        // vào cột nào cả - nó vẫn đứng yên ở vị trí gốc, là sibling của
        // .formulation trong .content, NGOÀI layout 2 cột th-split. Kết quả:
        // trên trang review, feedback (video) của các câu thuộc Part đang bị
        // ẩn (do quiz_footer.js chỉ toggle display của .formulation, không
        // đụng .outcome) vẫn hiển thị, trôi nổi lên đầu trang. Chuyển hẳn
        // .outcome vào cuối th-col-right ở đây vừa hiển thị đúng chỗ, vừa tự
        // động bị ẩn/hiện cùng .formulation vì giờ nó nằm bên trong.
        moveOutcomeIntoRightIfNeeded(formulation, right);

        formulation.appendChild(left);
        formulation.appendChild(resizer);
        formulation.appendChild(right);
        formulation.classList.add("th-split");
        formulation.dataset.thSplitDone = "1";

        if (window.thPendingHash && window.thPendingHash.length > 1) {
            window.scrollTo(0, 0);
        }

        document.documentElement.classList.add("th-has-split");
        document.body.classList.add("th-has-split");


        pendingPortals.forEach(function (runInitialSync) {
            runInitialSync();
        });

        applySavedRatio(left);
        initResizer(formulation, left, resizer);
    }

    function wrapSingleColumn(formulation, parts) {
        var single = document.createElement("div");
        single.className = "th-col-left th-col-single";

        parts.forEach(function (part) {
            single.appendChild(part);
        });

        moveOutcomeIntoRightIfNeeded(formulation, single);

        formulation.appendChild(single);
        formulation.classList.add("th-split", "th-single-col");
        formulation.dataset.thSplitDone = "1";

        document.documentElement.classList.add("th-has-split");
        document.body.classList.add("th-has-split");
    }

    function moveOutcomeIntoRightIfNeeded(formulation, right) {
        var content = formulation.closest(".content");
        if (!content) {
            return;
        }

        var outcome = null;
        var children = Array.prototype.slice.call(content.children);
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList && children[i].classList.contains("outcome")) {
                outcome = children[i];
                break;
            }
        }

        // Chỉ xử lý khi outcome CHƯA bị portalAnswerContainer (dùng cho
        // answercontainer/fieldset.ablock) di chuyển đi rồi - nếu nó vẫn còn
        // là con trực tiếp của .content (đứng yên vị trí gốc), nghĩa là câu
        // hỏi này không có answercontainer nên chưa ai đụng tới, cần tự tay
        // chuyển vào cuối cột phải.
        if (!outcome || outcome.parentElement !== content) {
            return;
        }

        right.appendChild(outcome);
    }

    function extractRightContent(root, right) {
        var markers = [];

        if (root.id === "th_right") {
            markers.push(root);
        } else if (root.querySelectorAll) {
            markers = Array.prototype.slice.call(root.querySelectorAll("#th_right"));
        }

        markers.forEach(function (marker) {
            while (marker.firstChild) {
                right.appendChild(marker.firstChild);
            }
        });
    }

    // Moodle render câu multichoice/checkbox dạng <fieldset class="ablock">
    // chứa <div class="answer">...</div>. Class này KHÁC với ".answercontainer"
    // (dùng cho ddwtos/matching) nên trước đây không được nhận diện.
    // QUAN TRỌNG: không được appendChild (di chuyển DOM thật) vì CSS gốc của
    // Moodle để căn checkbox + label cùng 1 hàng (display:flex trên .r0) có
    // thể phụ thuộc vào đúng vị trí/cha của fieldset trong cây DOM ban đầu;
    // đổi cha sẽ làm rule đó không còn khớp selector nữa (checkbox/label bị
    // rớt xuống 2 hàng). Vì vậy dùng lại đúng cơ chế "portal" (giữ nguyên vị
    // trí DOM gốc, chỉ định vị lại bằng position:fixed) giống answercontainer.
    function extractAnswerFieldset(root, right, pendingPortals) {
        var fieldsets = [];

        if (root.matches && root.matches("fieldset.ablock")) {
            fieldsets.push(root);
        } else if (root.querySelectorAll) {
            fieldsets = Array.prototype.slice.call(
                root.querySelectorAll("fieldset.ablock")
            );
        }

        fieldsets.forEach(function (fieldset) {
            var outcome = findOutcomeSibling(fieldset);
            var runInitialSync = portalAnswerContainer(fieldset, right, outcome);
            if (runInitialSync) {
                pendingPortals.push(runInitialSync);
            }
        });
    }

    function findOutcomeSibling(el) {
        var content = el.closest && el.closest(".content");
        if (!content) {
            return null;
        }
        var children = Array.prototype.slice.call(content.children);
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList && children[i].classList.contains("outcome")) {
                return children[i];
            }
        }
        return null;
    }

    function extractAnswerContainer(root, right, pendingPortals) {
        var containers = [];

        if (root.classList && root.classList.contains("answercontainer")) {
            containers.push(root);
        } else if (root.querySelectorAll) {
            containers = Array.prototype.slice.call(
                root.querySelectorAll(".answercontainer")
            );
        }

        containers.forEach(function (container) {
            var outcome = findOutcomeSibling(container);
            var runInitialSync = portalAnswerContainer(container, right, outcome);
            if (runInitialSync) {
                pendingPortals.push(runInitialSync);
            }
        });
    }

    function portalAnswerContainer(container, right, insertBefore) {
        if (container.dataset.thPortalActive === "1") {
            return null;
        }
        container.dataset.thPortalActive = "1";

        var placeholder = document.createElement("div");
        placeholder.className = "th-answercontainer-slot";
        
        var isStuckInLeft = !!(container.closest && container.closest(".th-col-left"));
        if (isStuckInLeft) {
            right.appendChild(placeholder);
        } else if (container.parentNode) {
            container.parentNode.insertBefore(
                placeholder,
                container.nextSibling
            );
        } else {
            right.appendChild(placeholder);
        }

        if (insertBefore && placeholder.parentNode) {
            placeholder.after(insertBefore);
        }

        container.classList.add("th-answercontainer-portal");


        container.thPlaceholder = placeholder;
        placeholder.thContainer = container;

        container.addEventListener("wheel", function (e) {
            // deltaMode 0 = pixel (chuột/trackpad thường gặp), 1 = line,
            // 2 = page. Quy đổi tương đối để cuộn mượt trong mọi trường hợp.
            var delta = e.deltaY;
            if (e.deltaMode === 1) {
                delta *= 16;
            } else if (e.deltaMode === 2) {
                delta *= right.clientHeight;
            }

            var maxScrollTop = right.scrollHeight - right.clientHeight;
            if (maxScrollTop > 0) {
                var next = right.scrollTop + delta;
                right.scrollTop = Math.min(maxScrollTop, Math.max(0, next));
                e.preventDefault();
            }
        }, { passive: false });

        function sync() {
            var rect = placeholder.getBoundingClientRect();
            container.style.position = "fixed";
            container.style.left = rect.left + "px";
            container.style.top = rect.top + "px";
            container.style.width = rect.width + "px";
            container.style.margin = "0";

            // FIX (khỏi lộ đáp án trước cả trang): container này là
            // position:fixed nên nằm NGOÀI vùng bị CSS "html:not(.th-layout-ready)
            // ... { visibility:hidden !important }" ẩn theo ancestor (xem
            // resize.css) - bên dưới tự set style.visibility="visible" trực
            // tiếp trên chính nó, mà inline style đó lại ĐÈ lên trạng thái ẩn
            // của ancestor. Nếu sync() chạy trước khi <html> có class
            // "th-layout-ready" (vd lúc mới dựng layout, trước khi header/
            // footer báo xong), đáp án sẽ hiện ra sớm rồi vài trăm ms~1.5s
            // sau cả trang mới hiện theo timeout của thLayoutReady - đúng
            // hiện tượng "đáp án hiện trước, cả trang hiện sau ~2s". Chặn ở
            // đây: chưa layout-ready thì luôn giữ ẩn, và đăng ký sync lại
            // ngay khi trang thực sự reveal (xem listener "th:layout-ready"
            // bên dưới) để cập nhật đúng trạng thái ngay lúc đó.
            if (!document.documentElement.classList.contains("th-layout-ready")) {
                container.style.visibility = "hidden";
                return;
            }

            if (container.dataset.thClipSuspended === "1") {
                container.style.zIndex = "1000";
                return;
            }
            container.style.zIndex = "5";

          
            var colRect = right.getBoundingClientRect();
            var outOfView = (rect.bottom <= colRect.top) || (rect.top >= colRect.bottom);

            if (outOfView) {
                container.style.visibility = "hidden";
            } else {
                container.style.visibility = "visible";
                var clipTop    = Math.max(0, colRect.top - rect.top);
                var clipBottom = Math.max(0, rect.bottom - colRect.bottom);
                container.style.clipPath =
                    "inset(" + clipTop + "px 0px " + clipBottom + "px 0px)";
            }
        }

        function syncPlaceholderHeight() {
            placeholder.style.height = container.offsetHeight + "px";
        }

        // FIX (mượt hơn khi cuộn): trước đây "scroll"/"resize" gọi thẳng
        // sync() đồng bộ - trong lúc cuộn cột trái/phải, sự kiện scroll bắn
        // ra rất nhiều lần/giây, mỗi lần đều forced-reflow (getBoundingClientRect)
        // + ghi style cho TỪNG portal -> giật khi có nhiều answercontainer.
        // Gom lại về đúng 1 lần/khung hình bằng rAF, giống cơ chế
        // scheduleSyncAllPortals() đã dùng cho mutation/resize-observer.
        var portalSyncScheduledLocal = false;
        function scheduleSync() {
            if (portalSyncScheduledLocal) {
                return;
            }
            portalSyncScheduledLocal = true;
            requestAnimationFrame(function () {
                portalSyncScheduledLocal = false;
                sync();
            });
        }

        window.addEventListener("resize", scheduleSync);
        window.addEventListener("scroll", scheduleSync, true);
        // Trang vừa reveal (class "th-layout-ready" vừa được gắn) -> chạy lại
        // sync() ngay để thoát khỏi trạng thái ép-ẩn ở trên, tính đúng
        // visibility/clip-path theo vị trí cuộn thật hiện tại.
        window.addEventListener("th:layout-ready", scheduleSync);

        var dragObserver = new window.MutationObserver(function () {
            var dragging = !!container.querySelector(".beingdragged");
            if (dragging && container.dataset.thClipSuspended !== "1") {
                container.dataset.thClipSuspended = "1";
                container.style.clipPath = "none";
                container.style.visibility = "visible";
                sync();
            } else if (!dragging && container.dataset.thClipSuspended === "1") {
                delete container.dataset.thClipSuspended;
                // Đáp án vừa được thả - tính lại clip-path/visibility đúng
                // theo vị trí cuộn hiện tại.
                sync();
            }
        });
        dragObserver.observe(container, {
            attributes: true,
            attributeFilter: ["class"],
            subtree: true
        });

        // Bắt các trường hợp nội dung bên trong container đổi (vd: link
        // "Clear my choice" của multichoice xuất hiện/biến mất sau khi
        // chọn/bỏ chọn đáp án) mà không chắc luôn kích hoạt ResizeObserver
        // (vd nếu Moodle ẩn/hiện bằng cách khác thay vì đổi box-size ngay
        // lập tức). CHỈ theo dõi childList/subtree (không theo dõi
        // attributes/style) để tránh vòng lặp vô hạn, vì sync() bên dưới tự
        // ghi vào style của chính container này.
        var contentObserver = new window.MutationObserver(function () {
            syncPlaceholderHeight();
            sync();
            // Placeholder này đổi kích thước sẽ đẩy các câu hỏi/placeholder
            // NẰM DƯỚI dịch chuyển trong dòng chảy trang - phải đồng bộ lại
            // toàn bộ portal khác, nếu không chúng đứng yên ở toạ độ cũ và
            // chồng/lệch lên câu hỏi phía trên (bug "Clear my choice" đẩy
            // các câu dưới đè lên đề).
            scheduleSyncAllPortals();
        });
        contentObserver.observe(container, {
            childList: true,
            subtree: true
        });

        portalSyncFns.push(function () {
            syncPlaceholderHeight();
            sync();
        });


        if (window.ResizeObserver) {
            var ro = new ResizeObserver(function () {
                syncPlaceholderHeight();
                sync();
                // Placeholder này đổi chiều cao (vd: "Clear my choice" vừa
                // xuất hiện sau khi chọn đáp án) sẽ đẩy các placeholder/câu
                // hỏi NẰM DƯỚI dịch chuyển trong dòng chảy bình thường.
                // Các container position:fixed khác không tự biết để tính
                // lại vị trí (ResizeObserver của chúng chỉ bắt được thay đổi
                // kích thước của chính chúng) -> phải chủ động đồng bộ lại
                // TOÀN BỘ portal, nếu không chúng sẽ đứng yên ở toạ độ cũ và
                // bị đè/chồng lên câu hỏi phía trên.
                scheduleSyncAllPortals();
            });
            ro.observe(container);
        }

        return function runInitialSync() {
            sync();
            syncPlaceholderHeight();
        
            window.dispatchEvent(new Event("resize"));
        };
    }

    function applySavedRatio(left) {
        var ratio = 50;
        try {
            var saved = parseFloat(sessionStorage.getItem(STORAGE_KEY));
            if (!isNaN(saved) && saved >= 15 && saved <= 85) {
                ratio = saved;
            }
        } catch (e) {}
        left.style.flexBasis = ratio + "%";
    }

    function clamp(value) {
        return Math.min(85, Math.max(15, value));
    }

    function saveRatio(ratio) {
        try {
            sessionStorage.setItem(STORAGE_KEY, ratio);
        } catch (e) {}
    }

    function initResizer(boundsEl, left, resizer) {
        var dragging = false;

        function move(clientX) {
            var rect = boundsEl.getBoundingClientRect();
            var ratio = clamp(((clientX - rect.left) / rect.width) * 100);
            left.style.flexBasis = ratio + "%";

            scheduleSyncAllPortals();
        }

        resizer.addEventListener("mousedown", function (e) {
            dragging = true;
            resizer.classList.add("th-resizing");
            document.body.style.userSelect = "none";
            e.preventDefault();
        });

        document.addEventListener("mousemove", function (e) {
            if (dragging) {
                move(e.clientX);
            }
        });

        document.addEventListener("mouseup", function () {
            if (!dragging) {
                return;
            }
            dragging = false;
            resizer.classList.remove("th-resizing");
            document.body.style.userSelect = "";
            saveRatio(parseFloat(left.style.flexBasis));
            triggerReflow();
        });

        resizer.addEventListener("touchstart", function () {
            dragging = true;
            resizer.classList.add("th-resizing");
        }, { passive: true });

        document.addEventListener("touchmove", function (e) {
            if (dragging && e.touches.length) {
                move(e.touches[0].clientX);
            }
        }, { passive: true });

        document.addEventListener("touchend", function () {
            if (!dragging) {
                return;
            }
            dragging = false;
            resizer.classList.remove("th-resizing");
            saveRatio(parseFloat(left.style.flexBasis));
            triggerReflow();
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

})();

(function () {

    var RESPONSE_SELECTOR = ".qtype_essay_response";

    function countWords(text) {
        text = text.trim();
        if (text === "") {
            return 0;
        }
        return text.split(/\s+/).length;
    }

    function _staticResponseText(responseEl) {
        var clone = responseEl.cloneNode(true);
        var ownCounter = clone.querySelector(".th-word-count");
        if (ownCounter) {
            ownCounter.remove();
        }

        clone.querySelectorAll("br, p, div, li, tr").forEach(function (el) {
            el.insertAdjacentText("afterend", " ");
        });
        return clone.textContent || "";
    }

    function getEditorText(responseEl) {
        // Atto editor: nội dung nằm trong div contenteditable.
        var editable = responseEl.querySelector('[contenteditable="true"]');
        if (editable) {
            return editable.innerText || "";
        }
        // Plain text area (khi tắt HTML editor).
        var textarea = responseEl.querySelector("textarea");
        if (textarea) {
            return textarea.value || "";
        }
        // Trang review: bài làm đã nộp, render tĩnh, không editor.
        return _staticResponseText(responseEl);
    }

    function ensureCounter(responseEl) {
        var counter = responseEl.querySelector(".th-word-count");
        if (!counter) {
            counter = document.createElement("div");
            counter.className = "th-word-count";
            responseEl.appendChild(counter);
        }
        return counter;
    }

    function updateCounter(responseEl) {
        var counter = ensureCounter(responseEl);
        var words = countWords(getEditorText(responseEl));
        counter.textContent = "Word count: " + words;
    }

    function bindResponse(responseEl) {
        // Tránh gắn listener nhiều lần lên cùng 1 ô trả lời.
        if (responseEl.dataset.thWordCountBound) {
            updateCounter(responseEl);
            return;
        }
        responseEl.dataset.thWordCountBound = "1";

        updateCounter(responseEl);

        var editable = responseEl.querySelector('[contenteditable="true"]');
        var textarea = responseEl.querySelector("textarea");

        if (editable) {
            editable.addEventListener("input", function () { updateCounter(responseEl); });
            editable.addEventListener("keyup",  function () { updateCounter(responseEl); });
        }
        if (textarea) {
            textarea.addEventListener("input", function () { updateCounter(responseEl); });
        }
    }

    function scan() {
        document.querySelectorAll(RESPONSE_SELECTOR).forEach(bindResponse);
    }

    var scanScheduled = false;
    function scheduleScan() {
        if (scanScheduled) {
            return;
        }
        scanScheduled = true;
        requestAnimationFrame(function () {
            scanScheduled = false;
            scan();
        });
    }

    // FIX: script này có thể chạy trước khi <body> tồn tại (vd đặt trong
    // <head> không có defer) - lúc đó document.body là null, khiến
    // observer.observe(null, ...) ném lỗi "parameter 1 is not of type
    // 'Node'" ngay khi load trang. Đợi document.body sẵn sàng rồi mới gắn
    // observer, giống cách "scan" đã đợi DOMContentLoaded bên dưới.
    function startObserving() {
        var observer = new MutationObserver(function () {
            scheduleScan();
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    if (document.body) {
        startObserving();
    } else {
        document.addEventListener("DOMContentLoaded", startObserving);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", scan);
    } else {
        scan();
    }

})();