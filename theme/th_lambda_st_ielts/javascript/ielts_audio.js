(function () {
  const header = document.getElementById("th-ielts-header-audio");
  if (!header) return;

  const audios = Array.from(
    document.querySelectorAll(".que.description audio"),
  );
  audioLength = audios.length;
  const defaultAudioResumeOffset = parseInt(header.dataset.defaultAudioResumeOffset,10);
  const resumeOffset = parseInt(header.dataset.resumeoffset, 10) || defaultAudioResumeOffset;

  console.log("IELTS audio resume defaultAudioResumeOffset:", defaultAudioResumeOffset);
  console.log("IELTS audio resume offset:", resumeOffset);

  if (audioLength === 1) {
    const resumeAfterReload = true;
    const attemptId = header.dataset.attemptid;

    const audio = document.querySelector(".que.description audio");
    if (!audio) return;

    // popup
    const showResumeOverlay = function () {
      if (document.getElementById("th-audio-resume-overlay")) return;

      const overlay = document.createElement("div");
      overlay.id = "th-audio-resume-overlay";
      overlay.innerHTML = `
            <div class="th-audio-resume-dialog">
                <div class="th-icon-audio">
                    <i class="fa fa-headphones" aria-hidden="true"></i>
                </div>

                <div class="th-audio-resume-message">
                    You will be listening to an audio clip during this test.
                    You will not be permitted to pause or rewind the audio
                    while answering the questions.
                </div>

                <div class="th-audio-resume-instruction">
                    To continue, click Play.
                </div>

                <button type="button" class="btn-play" id="th-audio-resume-button">
                    <span class="th-audio-play-circle" aria-hidden="true">
                        <span class="th-audio-play-triangle"></span>
                    </span>
                    <span>Play</span>
                </button>
            </div>
        `;
      document.body.appendChild(overlay);

      const resumeButton = document.getElementById("th-audio-resume-button");
      resumeButton.addEventListener("click", async function () {
        try {
          audio.muted = false;
          audio.volume = 1;

          const storageKey = "quiz_audio_attempt_" + attemptId;
          let resumeTime = 0;
          if (resumeAfterReload) {
            const savedTime = parseFloat(localStorage.getItem(storageKey));
            if (!isNaN(savedTime)) {
              resumeTime = Math.max(0, savedTime - resumeOffset);
              audio.currentTime = Math.min(
                resumeTime,
                Math.max(0, audio.duration - 0.1),
              );
            }
          }

          await audio.play();
          overlay.remove();
        } catch (error) {
          console.error("IELTS audio play failed:", error);
        }
      });
    };

    showResumeOverlay();

    // icon loa
    const target = document.getElementById("th-ielts-header-audio");
    target.innerHTML = `<i class="fa fa-volume-up th-listening-icon" aria-hidden="true"></i>`;
    target.appendChild(audio);

    const desc = audio.closest(".que.description");
    if (desc) desc.style.display = "none";

    document.body.classList.add("th-has-listening-audio-description");

    audio.controls = false;
    audio.autoplay = false;
    audio.muted = false;
    audio.volume = 1;
    audio.controlsList = "nodownload noplaybackrate noremoteplayback";
    audio.disablePictureInPicture = true;

    let resumeTime = 0;
    if (resumeAfterReload) {
      const storageKey = "quiz_audio_attempt_" + attemptId;
      audio.addEventListener("timeupdate", function () {
        if (Number.isFinite(audio.currentTime)) {
          localStorage.setItem(storageKey, audio.currentTime.toString());
        }
      });
      window.addEventListener("pagehide", function () {
        if (Number.isFinite(audio.currentTime)) {
          localStorage.setItem(storageKey, audio.currentTime.toString());
        }
      });
    }
  } else {
    const audios = Array.from(
      document.querySelectorAll(".que.description audio"),
    );
    if (!audios.length) return;

    const audioState = {};
    const attemptId = header.dataset.attemptid;
    let started = false;
    document.addEventListener("th:partchanged", function (event) {
      pauseAllAudio();
      started = false;
    });
    audios.forEach((audio, index) => {
      const partId = audio.dataset.partid || index;

      const storageKey = "quiz_audio_attempt_" + attemptId + "_part_" + partId;
      const savedTime = parseFloat(localStorage.getItem(storageKey));

      audioState[partId] = {
        audio: audio,
        currentTime: Number.isFinite(savedTime) ? savedTime : 0,
        storageKey: storageKey,
        restored: false,
      };

      function restoreAudioTime() {
        const state = audioState[partId];

        if (state.restored) return;
        if (!Number.isFinite(audio.duration)) return;

        audio.currentTime = Math.min(
          state.currentTime,
          Math.max(0, audio.duration - 0.1),
        );

        state.restored = true;
      }

      audio.addEventListener("loadedmetadata", restoreAudioTime);

      if (audio.readyState >= 1) {
        restoreAudioTime();
      }

      audio.addEventListener("timeupdate", function () {
        const state = audioState[partId];

        if (!state.restored) return;
        if (!Number.isFinite(audio.currentTime)) return;

        state.currentTime = audio.currentTime;
        localStorage.setItem(state.storageKey, audio.currentTime.toString());
      });

      audio.addEventListener("pause", function () {
        const state = audioState[partId];

        if (!state.restored) return;
        if (!Number.isFinite(audio.currentTime)) return;

        state.currentTime = audio.currentTime;
        localStorage.setItem(state.storageKey, audio.currentTime.toString());
      });
    });

    window.addEventListener("pagehide", function () {
      Object.values(audioState).forEach(function (state) {
        if (!state.restored) return;
        if (!Number.isFinite(state.audio.currentTime)) return;

        state.currentTime = state.audio.currentTime;

        localStorage.setItem(
          state.storageKey,
          state.audio.currentTime.toString(),
        );
      });
    });

    const style = document.createElement("style");
    style.innerHTML = `
        .th-single-audio::-webkit-media-controls-panel,
        .th-single-audio::-webkit-media-controls-volume-slider,
        .th-single-audio::-webkit-media-controls-play-button {
            pointer-events: none !important;
        }
        .th-single-audio::-webkit-media-controls-play-button {
            pointer-events: auto !important;
        }
        .vjs-control-bar {
            pointer-events: none !important;
        }
        .video-js .vjs-big-play-button,
        .video-js .vjs-play-control {
            pointer-events: auto !important;
        }
    `;
    document.head.appendChild(style);

    // Chặn click vào nút Pause
    document.addEventListener(
      "click",
      (e) => {
        const btn = e.target.closest(".vjs-play-control");
        if (!btn) return;

        if (!started) {
          started = true;
          return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();
      },
      true,
    );

    document.addEventListener(
      "keydown",
      (e) => {
        if (!started) return;

        const key = e.key.toLowerCase();

        if (key === "k" || key === " ") {
          e.preventDefault();
          e.stopPropagation();
          e.stopImmediatePropagation();
        }
      },
      true,
    );

    function pauseAllAudio() {
      Object.values(audioState).forEach(function (state) {
        const audio = state.audio;

        if (!audio.paused) {
          state.currentTime = audio.currentTime;

          localStorage.setItem(state.storageKey, audio.currentTime.toString());

          audio.pause();
        }
      });
    }
  }
})();
