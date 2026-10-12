define([
    'jquery',
    'core/ajax',
    'core/config',
    'media_videojs/video-lazy'
], function($, Ajax, Config, videojs) {

    function decodeBase64(str) {
        try {
            return atob(str);
        } catch (e) {
            return '';
        }
    }

    return {
        init: function(userId, cmId, savedTime, trackerInterval) {

            var saveInterval = parseInt(trackerInterval, 10);
            if (isNaN(saveInterval) || saveInterval <= 0) {
                saveInterval = 30;
            }

            // Các lần lưu được xếp hàng tuần tự để request luôn đến server đúng thứ tự
            // (tránh việc request percent < 100 đến sau request percent = 100).
            var saveChain = $.Deferred().resolve().promise();
            var attachTimer = null;

            function executeTrackerSetup(player, playerId) {
                if (!player || player._trackerInstalled) {
                    return;
                }

                player._trackerInstalled = true;
                var $mapaContainer = $('#resource-mapa-visualizacao .mapa');
                var rawMapa = decodeBase64(
                    $mapaContainer.attr('data-mapa') || ''
                );
                var mapaArray = [];

                try {
                    if (rawMapa) {
                        mapaArray = JSON.parse(rawMapa);
                    }
                } catch (e) {
                    mapaArray = [];
                }

                var mapalength = 100;
                var lastSavedTime = -1;
                var lastContinuousTime = -1;

                if (!Array.isArray(mapaArray)) {
                    mapaArray = [];
                }
                for (var i = 0; i <= mapalength; i++) {
                    if (typeof mapaArray[i] === 'undefined') {
                        mapaArray[i] = 0;
                    }
                }

                function getPercent() {
                    var watched = 0;
                    for (var k = 0; k <= mapalength; k++) {
                        if (mapaArray[k] == 1) {
                            watched++;
                        }
                    }
                    return Math.min(100, Math.round((watched / mapalength) * 100));
                }

                function getDuration() {
                    var d = player.duration();
                    return (d && isFinite(d) && d > 0) ? Math.floor(d) : 0;
                }

                function renderMapaVisual() {
                    var $cliqueContainer = $('#resource-mapa-visualizacao .clique');

                    if (!$cliqueContainer.length) {
                        return;
                    }

                    var html = '';
                    for (var j = 0; j <= mapalength; j++) {
                        var viewed = mapaArray[j] == 1;
                        html += '<div id="resource-mapa-item-' + j + '" class="mapa-item ' +
                            (viewed ? 'viewed' : '') + '"></div>';
                    }

                    $cliqueContainer.html(html);
                    $cliqueContainer.off('click').on('click', '.mapa-item', function() {
                        var itemId = $(this).attr('id');
                        var index = parseInt(itemId.split('-').pop(), 10);

                        var duration = player.duration();
                        if (duration && isFinite(duration) && duration > 0) {
                            player.currentTime((index / mapalength) * duration);
                        }
                    });

                    var percent = getPercent();
                    var $textContainer = $('#resource-mapa-text');
                    if (!$textContainer.length) {
                        $('#resource-mapa-visualizacao').after(
                            '<div id="resource-mapa-text" style="width: 100%; text-align: center; ' +
                            'margin-top: 10px; font-weight: bold; color: #333;">Your View map: <strong>' +
                            percent + '%</strong></div>'
                        );
                    } else {
                        $textContainer.find('strong').text(percent + '%');
                    }
                }

                function updateMapaUI(currentTime, duration, previousTime) {
                    if (!duration) {
                        return;
                    }

                    var startTime = (previousTime !== undefined) ? previousTime : currentTime;

                    var startPos = Math.floor((startTime / duration) * mapalength);
                    var endPos = Math.floor((currentTime / duration) * mapalength);

                    if (startPos < 0) {
                        startPos = 0;
                    }
                    if (endPos > mapalength) {
                        endPos = mapalength;
                    }
                    if (startPos > endPos) {
                        startPos = endPos;
                    }

                    var changed = false;
                    for (var p = startPos; p <= endPos; p++) {
                        if (mapaArray[p] != 1) {
                            mapaArray[p] = 1;
                            $('#resource-mapa-item-' + p).addClass('viewed');
                            changed = true;
                        }
                    }

                    if (changed) {
                        $('#resource-mapa-text strong').text(getPercent() + '%');
                    }
                }

                /**
                 * Gửi bằng sendBeacon (dùng khi đóng tab / ẩn trang, Ajax thường dễ bị hủy).
                 */
                function sendBeacon(args) {
                    if (!navigator.sendBeacon) {
                        return false;
                    }
                    try {
                        var url = Config.wwwroot + '/lib/ajax/service.php?sesskey=' +
                            encodeURIComponent(Config.sesskey) + '&info=mod_resource_save_video_progress';
                        var body = JSON.stringify([{
                            index: 0,
                            methodname: 'mod_resource_save_video_progress',
                            args: args
                        }]);
                        return navigator.sendBeacon(url, new Blob([body], {type: 'application/json'}));
                    } catch (e) {
                        return false;
                    }
                }

                function saveProgress(currentTime, duration, reason, useBeacon) {

                    if (!duration) {
                        return;
                    }

                    var args = {
                        cmid: Number(cmId),
                        userid: Number(userId),
                        currenttime: Math.floor(Number(currentTime)),
                        duration: Math.floor(Number(duration)),
                        percent: getPercent(),
                        mapa: JSON.stringify(mapaArray)
                    };

                    if (useBeacon && sendBeacon(args)) {
                        return;
                    }

                    saveChain = saveChain.then(function() {
                        return Ajax.call([{
                            methodname: 'mod_resource_save_video_progress',
                            args: args
                        }])[0].then(null, function(error) {
                            // Lỗi không chặn các lần lưu sau.
                            console.error('Save video progress error (' + reason + '):', error);
                        });
                    });
                }

                renderMapaVisual();

                var isResumed = false;
                var isResuming = false;

                function doResume() {
                    if (isResumed) {
                        return;
                    }

                    var targetTime = parseInt(savedTime, 10);
                    var duration = getDuration();

                    // Không có vị trí lưu, quá gần đầu video, hoặc đã gần cuối video (xem dở nhưng đã kết thúc):
                    // phát từ đầu, tránh nhảy thẳng vào cuối.
                    if (isNaN(targetTime) || targetTime <= 5 || (duration > 0 && targetTime >= duration - 5)) {
                        isResumed = true;
                        return;
                    }

                    player.one('play', function() {
                        isResuming = true;
                        player.currentTime(targetTime);

                        player.one('seeked', function() {
                            isResumed = true;
                            isResuming = false;
                        });

                        // Dự phòng: nếu 'seeked' không bắn (HLS/DRM...), vẫn bắt đầu theo dõi sau 5 giây.
                        setTimeout(function() {
                            if (!isResumed) {
                                isResumed = true;
                                isResuming = false;
                            }
                        }, 5000);
                    });
                }

                if (player.duration && !isNaN(player.duration()) && player.duration() > 0) {
                    doResume();
                } else {
                    player.one('loadedmetadata', doResume);
                }

                player.on('timeupdate', function() {
                    if (!isResumed) {
                        return;
                    }

                    if (player.seeking && player.seeking()) {
                        return;
                    }

                    var currentTimeRaw = player.currentTime();
                    var currentSecond = Math.floor(currentTimeRaw);
                    var duration = getDuration();

                    if (!duration) {
                        return;
                    }

                    // Chỉ tô màu khi xem liên tục (khoảng cách < 2 giây), tua thì không tính.
                    var prevTime = (lastContinuousTime >= 0 && (currentTimeRaw - lastContinuousTime) <= 2)
                        ? lastContinuousTime : currentTimeRaw;

                    updateMapaUI(currentTimeRaw, duration, prevTime);

                    lastContinuousTime = currentTimeRaw;

                    if (currentSecond !== lastSavedTime) {
                        if ((currentSecond % saveInterval === 0 && currentSecond > 0) ||
                            currentSecond === duration) {
                            lastSavedTime = currentSecond;
                            saveProgress(currentSecond, duration, 'AUTO');
                        }
                    }
                });

                player.on('seeked', function() {
                    if (!isResumed || isResuming) {
                        return;
                    }

                    var duration = getDuration();

                    lastSavedTime = Math.floor(player.currentTime());
                    lastContinuousTime = player.currentTime();

                    // Không tô màu vị trí vừa tua tới: nếu không, bấm lần lượt các ô sơ đồ
                    // sẽ đạt 100% mà chưa xem thật. Chỉ lưu vị trí mới.
                    saveProgress(lastSavedTime, duration, 'SEEK');
                });

                player.on('pause', function() {
                    if (!isResumed || isResuming || player.ended()) {
                        return;
                    }
                    if (player.seeking && player.seeking()) {
                        return;
                    }

                    var duration = getDuration();
                    var currentSecond = Math.floor(player.currentTime());
                    if (duration > 0 && currentSecond > 0) {
                        lastSavedTime = currentSecond;
                        saveProgress(currentSecond, duration, 'PAUSE');
                    }
                });

                player.on('ended', function() {
                    if (!isResumed) {
                        return;
                    }

                    var duration = getDuration();
                    if (!duration) {
                        return;
                    }

                    updateMapaUI(duration, duration);

                    saveProgress(duration, duration, 'ENDED');
                });

                function saveOnLeave() {
                    if (!isResumed || isResuming) {
                        return;
                    }
                    var duration = getDuration();
                    var currentSecond = Math.floor(player.currentTime());
                    if (duration > 0 && currentSecond > 0) {
                        saveProgress(currentSecond, duration, 'LEAVE', true);
                    }
                }

                // Đóng tab / chuyển trang.
                window.addEventListener('pagehide', saveOnLeave);
                // Chuyển tab / thu nhỏ trình duyệt (trên mobile pagehide không đáng tin cậy).
                document.addEventListener('visibilitychange', function() {
                    if (document.visibilityState === 'hidden' && !player.paused()) {
                        saveOnLeave();
                    }
                });

                // Đã gắn xong: ngừng quét player.
                if (attachTimer) {
                    clearInterval(attachTimer);
                    attachTimer = null;
                }
            }

            function attachPlayers() {

                $('.video-js').each(function() {

                    var playerId = this.id;

                    if (!playerId) {
                        return;
                    }

                    try {

                        var player = videojs.getPlayer(playerId);

                        if (!player) {

                            try {
                                player = videojs(playerId);
                            } catch (e) {
                                return;
                            }
                        }

                        if (player) {

                            player.ready(function() {

                                executeTrackerSetup(this, playerId);
                            });
                        }

                    } catch (e) {

                        console.error('Attach error:', e);
                    }
                });
            }

            $(function() {

                attachPlayers();

                attachTimer = setInterval(attachPlayers, 1000);

                // Không quét mãi mãi: dừng sau 60 giây nếu không có player.
                setTimeout(function() {
                    if (attachTimer) {
                        clearInterval(attachTimer);
                        attachTimer = null;
                    }
                }, 60000);
            });
        }
    };
});