/**
 * Stream Recording Download JavaScript
 */

(function($) {
    'use strict';

    // Initialize download functionality
    $(document).ready(function() {
        initDownloadHandlers();
        initPlayerHandlers();
        initFilterHandlers();
        initLoadMoreHandlers();
    });

    /**
     * Initialize download handlers
     */
    function initDownloadHandlers() {
        // Handle download button clicks
        $(document).on('click', '.twitch-recording-download-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var recordingId = $btn.data('recording-id');
            
            if (recordingId) {
                handleDownload(recordingId, $btn);
            }
        });

        // Handle watch button clicks
        $(document).on('click', '.twitch-recording-watch-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var recordingId = $btn.data('recording-id');
            
            if (recordingId) {
                handleWatch(recordingId, $btn);
            }
        });

        // Handle play button clicks
        $(document).on('click', '.twitch-recording-play-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var recordingId = $btn.data('recording-id');
            
            if (recordingId) {
                handlePlay(recordingId, $btn);
            }
        });
    }

    /**
     * Initialize player handlers
     */
    function initPlayerHandlers() {
        // Custom video player controls
        $(document).on('click', '.twitch-player-play-pause', function() {
            var $player = $(this).closest('.twitch-recording-player-wrapper').find('video');
            
            if ($player.get(0).paused) {
                $player.get(0).play();
                $(this).text('⏸️');
            } else {
                $player.get(0).pause();
                $(this).text('▶️');
            }
        });

        // Mute/Unmute
        $(document).on('click', '.twitch-player-mute', function() {
            var $player = $(this).closest('.twitch-recording-player-wrapper').find('video');
            var $volumeSlider = $(this).siblings('.twitch-player-volume').find('.twitch-player-volume-slider');
            
            if ($player.get(0).muted) {
                $player.get(0).muted = false;
                $(this).text('🔊');
                $volumeSlider.val($player.get(0).volume * 100);
            } else {
                $player.get(0).muted = true;
                $(this).text('🔇');
                $volumeSlider.val(0);
            }
        });

        // Volume control
        $(document).on('input', '.twitch-player-volume-slider', function() {
            var $player = $(this).closest('.twitch-recording-player-wrapper').find('video');
            var volume = $(this).val() / 100;
            var $muteBtn = $(this).closest('.twitch-player-volume').siblings('.twitch-player-mute');
            
            $player.get(0).volume = volume;
            $player.get(0).muted = volume === 0;
            
            if (volume === 0) {
                $muteBtn.text('🔇');
            } else {
                $muteBtn.text('🔊');
            }
        });

        // Progress bar
        $(document).on('click', '.twitch-player-progress-bar', function(e) {
            var $player = $(this).closest('.twitch-recording-player-wrapper').find('video');
            var $progressBar = $(this);
            var $progressFill = $progressBar.find('.twitch-player-progress-fill');
            
            var rect = $progressBar[0].getBoundingClientRect();
            var percent = (e.clientX - rect.left) / rect.width;
            var time = percent * $player.get(0).duration;
            
            $player.get(0).currentTime = time;
            updateProgressBar($player);
        });

        // Fullscreen
        $(document).on('click', '.twitch-player-fullscreen', function() {
            var $player = $(this).closest('.twitch-recording-player-wrapper');
            
            if (!document.fullscreenElement) {
                $player.get(0).requestFullscreen().catch(err => {
                    console.log('Error attempting to enable fullscreen:', err.message);
                });
            } else {
                document.exitFullscreen();
            }
        });

        // Video time update
        $(document).on('timeupdate', '.twitch-recording-video-player', function() {
            updateProgressBar($(this));
            updateTimeDisplay($(this));
        });

        // Video loaded metadata
        $(document).on('loadedmetadata', '.twitch-recording-video-player', function() {
            updateTimeDisplay($(this));
        });

        // Video ended
        $(document).on('ended', '.twitch-recording-video-player', function() {
            var $playPauseBtn = $(this).closest('.twitch-recording-player-wrapper').find('.twitch-player-play-pause');
            $playPauseBtn.text('▶️');
        });
    }

    /**
     * Initialize filter handlers
     */
    function initFilterHandlers() {
        // Sort dropdown
        $(document).on('change', '.twitch-recording-sort', function() {
            var sort = $(this).val();
            var $container = $(this).closest('.twitch-recording-downloads');
            var channel = $container.find('.twitch-recording-header h3').text().replace('Aufnahmen von ', '');
            
            sortRecordings(channel, sort);
        });
    }

    /**
     * Initialize load more handlers
     */
    function initLoadMoreHandlers() {
        // Load more button
        $(document).on('click', '.twitch-recording-load-more-btn', function() {
            var $btn = $(this);
            var channel = $btn.data('channel');
            var offset = $btn.data('offset');
            
            loadMoreRecordings(channel, offset, $btn);
        });
    }

    /**
     * Handle download
     */
    function handleDownload(recordingId, $btn) {
        // Check if download is already in progress
        if ($btn.hasClass('loading')) {
            return;
        }

        // Add loading state
        $btn.addClass('loading');
        
        // Get recording info
        $.ajax({
            url: twitchRecordingDownload.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_recording_download',
                download_action: 'get_recording',
                recording_id: recordingId,
                nonce: twitchRecordingDownload.nonce
            },
            success: function(response) {
                if (response.success && response.data.recording) {
                    var recording = response.data.recording;
                    var fileSize = recording.file_size || 0;
                    
                    // Check file size
                    if (fileSize > twitchRecordingDownload.maxFileSize) {
                        showError('Datei ist zu groß für den Download');
                        $btn.removeClass('loading');
                        return;
                    }
                    
                    // Start download
                    startDownload(recording, $btn);
                } else {
                    showError('Aufnahme nicht gefunden');
                    $btn.removeClass('loading');
                }
            },
            error: function() {
                showError('Netzwerkfehler. Bitte versuchen Sie es erneut.');
                $btn.removeClass('loading');
            }
        });
    }

    /**
     * Start download
     */
    function startDownload(recording, $btn) {
        var downloadUrl = twitchRecordingDownload.downloadUrl + '?id=' + recording.id;
        
        // Create temporary link for download
        var $link = $('<a>')
            .attr('href', downloadUrl)
            .attr('download', generateFilename(recording))
            .attr('target', '_blank')
            .hide();
        
        $('body').append($link);
        $link[0].click();
        $link.remove();
        
        // Remove loading state after a delay
        setTimeout(function() {
            $btn.removeClass('loading');
        }, 1000);
        
        // Track download
        trackDownload(recording.id);
    }

    /**
     * Handle watch
     */
    function handleWatch(recordingId, $btn) {
        // Get recording info
        $.ajax({
            url: twitchRecordingDownload.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_recording_download',
                download_action: 'get_recording',
                recording_id: recordingId,
                nonce: twitchRecordingDownload.nonce
            },
            success: function(response) {
                if (response.success && response.data.recording) {
                    var recording = response.data.recording;
                    openVideoModal(recording);
                } else {
                    showError('Aufnahme nicht gefunden');
                }
            },
            error: function() {
                showError('Netzwerkfehler. Bitte versuchen Sie es erneut.');
            }
        });
    }

    /**
     * Handle play
     */
    function handlePlay(recordingId, $btn) {
        // Similar to watch but inline
        handleWatch(recordingId, $btn);
    }

    /**
     * Open video modal
     */
    function openVideoModal(recording) {
        // Create modal
        var $modal = $('<div class="twitch-recording-modal">')
            .html(createVideoModalHtml(recording))
            .appendTo('body');
        
        // Show modal
        setTimeout(function() {
            $modal.addClass('show');
        }, 10);
        
        // Close handlers
        $modal.on('click', '.twitch-modal-close', function() {
            closeModal($modal);
        });
        
        $modal.on('click', '.twitch-modal-overlay', function() {
            closeModal($modal);
        });
        
        // ESC key to close
        $(document).on('keydown.twitch-modal', function(e) {
            if (e.keyCode === 27) {
                closeModal($modal);
            }
        });
    }

    /**
     * Create video modal HTML
     */
    function createVideoModalHtml(recording) {
        var poster = recording.thumbnail_path || '';
        var filename = generateFilename(recording);
        
        return `
            <div class="twitch-modal-overlay"></div>
            <div class="twitch-modal-content">
                <div class="twitch-modal-header">
                    <h3>${escapeHtml(recording.title)}</h3>
                    <button class="twitch-modal-close">&times;</button>
                </div>
                <div class="twitch-modal-body">
                    <video controls preload="metadata" style="width: 100%; max-width: 800px;" poster="${escapeHtml(poster)}">
                        <source src="${escapeHtml(recording.file_path)}" type="video/mp4">
                        Ihr Browser unterstützt keine Video-Wiedergabe.
                    </video>
                    <div class="twitch-modal-info">
                        <p><strong>Datum:</strong> ${formatDate(recording.started_at)}</p>
                        <p><strong>Dauer:</strong> ${formatDuration(recording.duration)}</p>
                        <p><strong>Spiel:</strong> ${escapeHtml(recording.game || 'Unbekannt')}</p>
                        ${recording.statistics && recording.statistics.max_viewers ? 
                            `<p><strong>Zuschauer:</strong> ${recording.statistics.max_viewers}</p>` : ''}
                    </div>
                </div>
                <div class="twitch-modal-footer">
                    <a href="${escapeHtml(recording.file_path)}" download="${escapeHtml(filename)}" class="twitch-btn twitch-btn-primary">
                        ⬇️ Download
                    </a>
                    <button class="twitch-btn twitch-btn-secondary twitch-modal-close">Schließen</button>
                </div>
            </div>
        `;
    }

    /**
     * Close modal
     */
    function closeModal($modal) {
        $modal.removeClass('show');
        setTimeout(function() {
            $modal.remove();
            $(document).off('keydown.twitch-modal');
        }, 300);
    }

    /**
     * Sort recordings
     */
    function sortRecordings(channel, sort) {
        var $container = $('.twitch-recording-downloads');
        var $grid = $container.find('.twitch-recording-grid');
        var $cards = $grid.find('.twitch-recording-card');
        
        // Convert cards to array for sorting
        var cards = $cards.toArray();
        
        // Sort based on selected option
        cards.sort(function(a, b) {
            var $a = $(a);
            var $b = $(b);
            
            switch (sort) {
                case 'date_asc':
                    return getRecordingDate($a) - getRecordingDate($b);
                case 'date_desc':
                    return getRecordingDate($b) - getRecordingDate($a);
                case 'duration_asc':
                    return getRecordingDuration($a) - getRecordingDuration($b);
                case 'duration_desc':
                    return getRecordingDuration($b) - getRecordingDuration($a);
                case 'viewers_desc':
                    return getRecordingViewers($b) - getRecordingViewers($a);
                case 'title_asc':
                    return getRecordingTitle($a).localeCompare(getRecordingTitle($b));
                default:
                    return 0;
            }
        });
        
        // Re-append sorted cards
        $grid.empty().append(cards);
        
        // Re-attach animations
        $grid.find('.twitch-recording-card').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
        });
    }

    /**
     * Load more recordings
     */
    function loadMoreRecordings(channel, offset, $btn) {
        $btn.prop('disabled', true).text('Laden...');
        
        $.ajax({
            url: twitchRecordingDownload.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_recording_download',
                download_action: 'get_downloads',
                channel: channel,
                limit: 20,
                offset: offset,
                nonce: twitchRecordingDownload.nonce
            },
            success: function(response) {
                if (response.success && response.data.recordings) {
                    var recordings = response.data.recordings;
                    var $grid = $('.twitch-recording-grid');
                    
                    recordings.forEach(function(recording) {
                        var $card = $(createRecordingCardHtml(recording));
                        $grid.append($card);
                    });
                    
                    // Update button
                    if (recordings.length < 20) {
                        $btn.text('Keine weiteren Aufnahmen').prop('disabled', true);
                    } else {
                        $btn.prop('disabled', false).text('Mehr laden');
                        $btn.data('offset', offset + 20);
                    }
                } else {
                    $btn.prop('disabled', false).text('Mehr laden');
                    showError('Fehler beim Laden weiterer Aufnahmen');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Mehr laden');
                showError('Netzwerkfehler. Bitte versuchen Sie es erneut.');
            }
        });
    }

    /**
     * Create recording card HTML
     */
    function createRecordingCardHtml(recording) {
        var thumbnail = recording.thumbnail_path ? 
            `<img src="${escapeHtml(recording.thumbnail_path)}" alt="${escapeHtml(recording.title)}">` :
            `<div class="twitch-recording-no-thumbnail"><span>📹</span></div>`;
        
        var duration = formatDuration(recording.duration);
        var date = formatDate(recording.started_at);
        var viewers = recording.statistics && recording.statistics.max_viewers ? 
            `<span class="twitch-recording-viewers">${recording.statistics.max_viewers} Zuschauer</span>` : '';
        var game = recording.game ? 
            `<p class="twitch-recording-game">${escapeHtml(recording.game)}</p>` : '';
        
        var actions = '';
        if (recording.status === 'completed' && recording.file_path) {
            actions = `
                <a href="${escapeHtml(twitchRecordingDownload.downloadUrl)}?id=${recording.id}" 
                   class="twitch-recording-download-btn" data-recording-id="${recording.id}">
                    ⬇️ Download
                </a>
                <button class="twitch-recording-watch-btn" data-recording-id="${recording.id}">
                    🎬 Ansehen
                </button>
            `;
        } else {
            actions = `<div class="twitch-recording-status twitch-status-${recording.status}">${getStatusText(recording.status)}</div>`;
        }
        
        return `
            <div class="twitch-recording-card" data-recording-id="${recording.id}">
                <div class="twitch-recording-thumbnail">
                    ${thumbnail}
                    <div class="twitch-recording-duration">${duration}</div>
                    <button class="twitch-recording-play-btn" data-recording-id="${recording.id}">▶️</button>
                </div>
                <div class="twitch-recording-content">
                    <h4 class="twitch-recording-title">${escapeHtml(recording.title)}</h4>
                    <p class="twitch-recording-meta">
                        <span class="twitch-recording-date">${date}</span>
                        ${viewers}
                    </p>
                    ${game}
                </div>
                <div class="twitch-recording-actions">
                    ${actions}
                </div>
            </div>
        `;
    }

    /**
     * Update progress bar
     */
    function updateProgressBar($player) {
        var $progressBar = $player.closest('.twitch-recording-player-wrapper').find('.twitch-player-progress-bar');
        var $progressFill = $progressBar.find('.twitch-player-progress-fill');
        
        if ($player.length && $progressBar.length && $progressFill.length) {
            var percent = ($player.get(0).currentTime / $player.get(0).duration) * 100;
            $progressFill.css('width', percent + '%');
        }
    }

    /**
     * Update time display
     */
    function updateTimeDisplay($player) {
        var $timeDisplay = $player.closest('.twitch-recording-player-wrapper').find('.twitch-player-time');
        
        if ($player.length && $timeDisplay.length) {
            var current = formatTime($player.get(0).currentTime);
            var duration = formatTime($player.get(0).duration);
            $timeDisplay.text(current + ' / ' + duration);
        }
    }

    /**
     * Get recording date
     */
    function getRecordingDate($card) {
        var dateText = $card.find('.twitch-recording-date').text();
        return new Date(dateText).getTime();
    }

    /**
     * Get recording duration
     */
    function getRecordingDuration($card) {
        var durationText = $card.find('.twitch-recording-duration').text();
        // Parse duration format (e.g., "2h 30m" or "45m")
        var match = durationText.match(/(\d+)h?\s*(\d+)m?/);
        if (match) {
            var hours = parseInt(match[1]) || 0;
            var minutes = parseInt(match[2]) || 0;
            return hours * 60 + minutes;
        }
        return 0;
    }

    /**
     * Get recording viewers
     */
    function getRecordingViewers($card) {
        var viewersText = $card.find('.twitch-recording-viewers').text();
        var match = viewersText.match(/(\d+)/);
        return match ? parseInt(match[1]) : 0;
    }

    /**
     * Get recording title
     */
    function getRecordingTitle($card) {
        return $card.find('.twitch-recording-title').text();
    }

    /**
     * Generate filename
     */
    function generateFilename(recording) {
        var title = sanitizeFilename(recording.title);
        var date = formatDate(recording.started_at, 'YYYY-MM-DD');
        var channel = sanitizeFilename(recording.channel);
        
        return `${channel}_${date}_${title}.mp4`;
    }

    /**
     * Track download
     */
    function trackDownload(recordingId) {
        // This would send tracking data to server
        console.log('Download tracked:', recordingId);
    }

    /**
     * Show error message
     */
    function showError(message) {
        // Remove existing errors
        $('.twitch-recording-error').remove();
        
        // Create error element
        var $error = $('<div class="twitch-recording-error">')
            .text(message)
            .prependTo('.twitch-recording-downloads');
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $error.fadeOut(function() {
                $error.remove();
            });
        }, 5000);
    }

    /**
     * Format date
     */
    function formatDate(dateString, format = 'DD.MM.YYYY') {
        var date = new Date(dateString);
        
        if (format === 'YYYY-MM-DD') {
            return date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(date.getDate()).padStart(2, '0');
        }
        
        return date.getDate() + '.' + 
               (date.getMonth() + 1) + '.' + 
               date.getFullYear();
    }

    /**
     * Format duration
     */
    function formatDuration(minutes) {
        var hours = Math.floor(minutes / 60);
        var mins = minutes % 60;
        
        if (hours > 0) {
            return `${hours}h ${mins}m`;
        } else {
            return `${mins}m`;
        }
    }

    /**
     * Format time
     */
    function formatTime(seconds) {
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var secs = Math.floor(seconds % 60);
        
        if (hours > 0) {
            return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        } else {
            return `${minutes}:${String(secs).padStart(2, '0')}`;
        }
    }

    /**
     * Get status text
     */
    function getStatusText(status) {
        var statusTexts = {
            'recording': 'Aufnahme läuft...',
            'processing': 'Verarbeitung...',
            'completed': 'Abgeschlossen',
            'failed': 'Fehlgeschlagen'
        };
        
        return statusTexts[status] || status;
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Sanitize filename
     */
    function sanitizeFilename(filename) {
        return filename.replace(/[^\w\s-]/g, '').replace(/\s+/g, '_');
    }

    /**
     * Expose functions globally
     */
    window.TwitchRecordingDownload = {
        download: handleDownload,
        watch: handleWatch,
        play: handlePlay,
        sort: sortRecordings,
        loadMore: loadMoreRecordings
    };

})(jQuery);
