(function () {
  function initDdimageortextLabels() {
    document
      .querySelectorAll(
        ".que.ddimageortext, .que.ddwtos, .que.ddimageortextquestion, .que.thddwtos",
      )
      .forEach(function (question) {
        waitAndApplyLabels(question, 0);
      });
      bindDropEvents();
  }

  function waitAndApplyLabels(question, retry) {

    var marker = question.querySelector(
      ".th_question_answer, #th_question_answer",
    );

    if (!marker) {
      return;
    }

    var range = marker.getAttribute("value");

    if (!range) {
      return;
    }

    range = range.replace(/[“”]/g, "").replace(/"/g, "").trim();

    var parts = range.split("-");
    var start = parseInt(parts[0], 10);
    var end = parseInt(parts[1], 10);

    if (isNaN(start)) {
      return;
    }

    // ddwtos / thddwtos dùng span.drop (place1, place2, ...) thay vì .hotspot
    var isDdwtos =
      question.classList.contains("ddwtos") ||
      question.classList.contains("thddwtos");

    var dropzones = Array.prototype.slice.call(
      question.querySelectorAll(
        ".hotspot, .hotspot-editable, .atto-thimageeditor-hotspot, .drop",
      ),
    );

    if (dropzones.length === 0) {
      dropzones = Array.prototype.slice.call(
        document.querySelectorAll(".que.ddimageortext .hotspot"),
      );
    }

    if (dropzones.length === 0 && retry < 40) {
      setTimeout(function () {
        waitAndApplyLabels(question, retry + 1);
      }, 200);
      return;
    }

    if (dropzones.length === 0) {
      console.warn("TH hotspot: không tìm thấy hotspot");
      return;
    }

    if (isDdwtos) {
      dropzones.forEach(function (dropzone) {
        var match = dropzone.className.match(/place(\d+)/);
        if (match) {
          var placeNum = parseInt(match[1], 10);
          dropzone.setAttribute("data-label", start + (placeNum - 1));
        }
      });
      return;
    }

    if (!isNaN(end)) {
      dropzones = dropzones.slice(0, end - start + 1);
    }

    dropzones.forEach(function (dropzone, index) {
      dropzone.setAttribute("data-label", start + index);
    });
  }


  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initDdimageortextLabels);
  } else {
    initDdimageortextLabels();
  }

  window.addEventListener("load", initDdimageortextLabels);
})();


function bindDropEvents() {
  // FIX (mượt hơn khi tương tác): trước đây observer theo dõi toàn bộ
  // document.body (mọi mutation trên cả trang: timer đồng hồ thi, audio,
  // portal resize.js, footer...) chỉ để toggle 1 class trên hotspot -> phí
  // rất nhiều, gây giật nhẹ khi kéo-thả vì callback bị gọi liên tục dù
  // phần lớn mutation chẳng liên quan gì tới drag-drop. Thu hẹp về đúng
  // vùng câu hỏi (#th-quiz-questions) nếu có, fallback về body khi không
  // tìm thấy (trang không theo cấu trúc IELTS chuẩn).
  var scanRoot = document.getElementById("th-quiz-questions") || document.body;

  function updateHotspotAnswerStates() {
    document.querySelectorAll(".que.ddwtos .hotspot, .que.ddimageortext .hotspot, .que.thddwtos .drop, .que.ddwtos .drop")
      .forEach(function (hotspot) {

        const hasAnswer =
          hotspot.querySelector(".draghome") !== null ||
          hotspot.classList.contains("placed") ||
          hotspot.innerHTML.trim() !== "";

        if (hasAnswer) {
          hotspot.classList.add("has-answer");
        } else {
          hotspot.classList.remove("has-answer");
        }

      });
  }

  // FIX (mượt hơn khi kéo-thả): trong lúc kéo, DOM trong vùng câu hỏi thay
  // đổi rất nhiều lần liên tiếp (mỗi nhịp animation của thư viện kéo-thả),
  // khiến callback này (quét lại TOÀN BỘ hotspot mỗi lần) bị gọi liên tục
  // dù phần lớn mutation không cần xử lý ngay. Gom lại về đúng 1 lần/khung
  // hình bằng requestAnimationFrame để giảm số lần quét DOM khi có nhiều
  // mutation dồn dập, không đổi kết quả cuối cùng.
  var hotspotUpdateScheduled = false;
  function scheduleHotspotUpdate() {
    if (hotspotUpdateScheduled) {
      return;
    }
    hotspotUpdateScheduled = true;
    requestAnimationFrame(function () {
      hotspotUpdateScheduled = false;
      updateHotspotAnswerStates();
    });
  }

  const observer = new MutationObserver(scheduleHotspotUpdate);

  observer.observe(scanRoot, {
    childList: true,
    subtree: true
  });
}