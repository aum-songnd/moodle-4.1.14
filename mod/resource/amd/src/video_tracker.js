define([
    'jquery',
    'core/ajax',
    'media_videojs/video-lazy'
], function($, Ajax, videojs) {

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
                var timeBeforeSeek = 0;
                var lastSavedTime = -1;
                var lastContinuousTime = -1;

                function renderMapaVisual() {
                    var $cliqueContainer = $('#resource-mapa-visualizacao .clique'); 
                    
                    if (!$cliqueContainer.length) {
                        return;
                    }

                    var html = '';
                    var watched = 0;

                    for (var i = 0; i <= mapalength; i++) {
                        var viewed = mapaArray[i] == 1;
                        if (viewed) watched++;
                        
                        html += '<div id="resource-mapa-item-' + i + '" class="mapa-item ' + (viewed ? 'viewed' : '') + '"></div>';
                    }

                    $cliqueContainer.html(html);
                    $cliqueContainer.off('click').on('click', '.mapa-item', function() {
                        var itemId = $(this).attr('id');
                        var index = parseInt(itemId.split('-').pop());
                        
                        var duration = player.duration();
                        if (duration && duration > 0) {
                            var targetTime = (index / mapalength) * duration;
                            player.currentTime(targetTime);
                            
                        }
                    });

                    var percent = Math.round((watched / mapalength) * 100);
                    if (percent > 100) percent = 100;

                    var $textContainer = $('#resource-mapa-text');
                    if (!$textContainer.length) {
                        $('#resource-mapa-visualizacao').after('<div id="resource-mapa-text" style="width: 100%; text-align: center; margin-top: 10px; font-weight: bold; color: #333;">Your View map: <strong>' + percent + '%</strong></div>');
                    } else {
                        $textContainer.find('strong').text(percent + '%');
                    }
                }

                function updateMapaUI(currentTime, duration, previousTime) {
                    if (!duration) return;

                    var startTime = (previousTime !== undefined) ? previousTime : currentTime;
                    
                    var startPos = Math.floor((startTime / duration) * mapalength);
                    var endPos = Math.floor((currentTime / duration) * mapalength);

                    if (startPos < 0) startPos = 0;
                    if (endPos > mapalength) endPos = mapalength; 
                    if (startPos > endPos) startPos = endPos;

                    var changed = false;
                    for (var p = startPos; p <= endPos; p++) {
                        if (mapaArray[p] != 1) {
                            mapaArray[p] = 1;
                            $('#resource-mapa-item-' + p).addClass('viewed');
                            changed = true;
                        }
                    }

                    if (changed) {
                        var watched = 0;
                        for (var i = 0; i <= mapalength; i++) {
                            if (mapaArray[i] == 1) watched++;
                        }
                        var percent = Math.round((watched / mapalength) * 100);
                        if (percent > 100) percent = 100;
                        $('#resource-mapa-text strong').text(percent + '%');
                    }
                }

                function saveProgress(currentTime, duration, reason) {

                    if (!duration) {
                        return;
                    }

                    var watched = 0;
                    for (var i = 0; i <= mapalength; i++) {
                        if (mapaArray[i] == 1) watched++;
                    }
                    var percent = Math.round((watched / mapalength) * 100);

                    if (percent > 100) {
                        percent = 100;
                    }

                    Ajax.call([{
                        methodname: 'mod_resource_save_video_progress',
                        args: {
                            cmid: Number(cmId),
                            userid: Number(userId),
                            currenttime: Number(currentTime),
                            duration: Number(duration),
                            percent: Number(percent),
                            mapa: JSON.stringify(mapaArray)
                        }
                    }])[0]
                    .done(function(response) {
                    })
                    .fail(function(error) {
                        console.error(
                            '🔴 Save error:',
                            error
                        );
                    });
                }

                if (!Array.isArray(mapaArray)) {
                    mapaArray = [];
                }
                for (var i = 0; i <= mapalength; i++) {
                    if (typeof mapaArray[i] === 'undefined') {
                        mapaArray[i] = 0;
                    }
                }
                
                renderMapaVisual(); 

                var isResumed = false;
                var isResuming = false;

                function doResume() {
                    if (isResumed) return;

                    var targetTime = parseInt(savedTime, 10);

                    if (!savedTime || targetTime <= 5) {
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
                    });
                }

                if (player.duration && !isNaN(player.duration()) && player.duration() > 0) {
                    doResume();
                } else {
                    player.one('loadedmetadata', doResume);
                }

                player.on(
                    'timeupdate',
                    function() {
                        if (!isResumed) return;

                        if (
                            player.seeking &&
                            player.seeking()
                        ) {
                            return;
                        }

                        var currentTimeRaw = player.currentTime();
                        timeBeforeSeek = currentTimeRaw;
                        var currentSecond = Math.floor(currentTimeRaw);
                        var duration = Math.floor(player.duration());

                        // Tính toán thời gian hợp lệ để fill màu (chỉ fill nếu xem liên tục, khoảng cách < 2 giây)
                        var prevTime = (lastContinuousTime >= 0 && (currentTimeRaw - lastContinuousTime) <= 2) ? lastContinuousTime : currentTimeRaw;

                        updateMapaUI(currentTimeRaw, duration, prevTime);

                        lastContinuousTime = currentTimeRaw;

                        if (
                            currentSecond !==
                                lastSavedTime &&
                            duration > 0
                        ) {

                            if (
                                (currentSecond % saveInterval === 0 && currentSecond > 0) ||
                                currentSecond === duration
                            ) {

                                lastSavedTime =
                                    currentSecond;

                                saveProgress(
                                    currentSecond,
                                    duration,
                                    'AUTO'
                                );
                            }
                        }
                    }
                );

                player.on(
                    'seeking',
                    function() {
                    }
                );

                player.on(
                    'seeked',
                    function() {
                        if (!isResumed || isResuming) return;

                        var currentSecond =
                            Math.floor(
                                player.currentTime()
                            );

                        var duration =
                            Math.floor(
                                player.duration()
                            );

                        lastSavedTime = currentSecond;
                        lastContinuousTime = player.currentTime();

                        updateMapaUI(currentSecond, duration);

                        saveProgress(
                            currentSecond,
                            duration,
                            'SEEK'
                        );
                    }
                );

                player.on(
                    'ended',
                    function() {
                        if (!isResumed) return;

                        var duration = Math.floor(player.duration());

                        updateMapaUI(duration, duration);

                        saveProgress(duration, duration, 'ENDED');
                    }
                );

                window.addEventListener('beforeunload', function() {
                    if (isResumed && player) {
                        var currentSecond = Math.floor(player.currentTime());
                        var duration = Math.floor(player.duration());
                        if (duration > 0 && currentSecond > 0) {
                            saveProgress(currentSecond, duration, 'UNLOAD');
                        }
                    }
                });
            }

            function attachPlayers() {

                $('.video-js').each(function() {

                    var playerId = this.id;

                    if (!playerId) {
                        return;
                    }

                    try {

                        var player =
                            videojs.getPlayer(playerId);

                        if (!player) {

                            try {
                                player = videojs(playerId);
                            } catch (e) {
                                return;
                            }
                        }

                        if (player) {

                            player.ready(function() {

                                executeTrackerSetup(
                                    this,
                                    playerId
                                );
                            });
                        }

                    } catch (e) {

                        console.error(
                            'Attach error:',
                            e
                        );
                    }
                });
            }

            $(function() {

                attachPlayers();

                setInterval(
                    attachPlayers,
                    1000
                );
            });
        }
    };
});