/**
 * Twitch Player Functions
 */

(function($) {
    'use strict';

    // Twitch Stream Manager
    var TwitchStreamManager = {
        
        init: function() {
            this.bindEvents();
            this.setupAutoRefresh();
        },

        bindEvents: function() {
            $(document).on('click', '.twitch-refresh-btn', this.refreshStream.bind(this));
            $(document).on('click', '.twitch-toggle-live', this.toggleLiveStatus.bind(this));
        },

        setupAutoRefresh: function() {
            // Alle 5 Minuten Stream-Status aktualisieren
            setInterval(function() {
                $('.twitch-stream-container').each(function() {
                    var $container = $(this);
                    var channel = $container.data('channel');
                    if (channel) {
                        TwitchStreamManager.checkStreamStatus(channel, $container);
                    }
                });
            }, 300000); // 5 Minuten
        },

        refreshStream: function(e) {
            e.preventDefault();
            var $btn = $(e.currentTarget);
            var $container = $btn.closest('.twitch-stream-wrapper');
            var channel = $container.data('channel');

            if (!channel) return;

            $btn.prop('disabled', true).text('Aktualisiere...');

            this.checkStreamStatus(channel, $container, function() {
                $btn.prop('disabled', false).text('Aktualisieren');
            });
        },

        checkStreamStatus: function(channel, $container, callback) {
            var self = this;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'twitch_check_status',
                    channel: channel,
                    nonce: twitch_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateStreamDisplay($container, response.data.is_live);
                    }
                    if (callback) callback();
                },
                error: function() {
                    if (callback) callback();
                }
            });
        },

        updateStreamDisplay: function($container, isLive) {
            var $offlineMsg = $container.find('.twitch-offline');
            var $player = $container.find('.twitch-stream-container');

            if (isLive) {
                $offlineMsg.hide();
                if ($player.length === 0) {
                    // Player neu laden
                    location.reload();
                } else {
                    $player.show();
                }
            } else {
                $offlineMsg.show();
                $player.hide();
            }
        },

        toggleLiveStatus: function(e) {
            e.preventDefault();
            var $btn = $(e.currentTarget);
            var $container = $btn.closest('.twitch-stream-wrapper');
            var $offlineMsg = $container.find('.twitch-offline');
            var $player = $container.find('.twitch-stream-container');

            if ($offlineMsg.is(':visible')) {
                $offlineMsg.hide();
                $player.show();
                $btn.text('Offline-Status anzeigen');
            } else {
                $offlineMsg.show();
                $player.hide();
                $btn.text('Player anzeigen');
            }
        }
    };

    // Twitch Embed Helper
    var TwitchEmbedHelper = {
        
        createEmbed: function(channel, options) {
            options = options || {};
            var defaults = {
                width: '100%',
                height: '480',
                autoplay: true,
                muted: false,
                parent: window.location.hostname
            };

            var settings = $.extend(defaults, options);
            
            var embedUrl = 'https://player.twitch.tv/?' + $.param({
                channel: channel,
                parent: settings.parent,
                autoplay: settings.autoplay,
                muted: settings.muted
            });

            var iframe = $('<iframe>', {
                src: embedUrl,
                width: settings.width,
                height: settings.height,
                frameborder: '0',
                scrolling: 'no',
                allowfullscreen: 'true'
            });

            return $('<div>', {
                class: 'twitch-stream-container'
            }).append(iframe);
        },

        resizeEmbed: function($container, width, height) {
            var $iframe = $container.find('iframe');
            if ($iframe.length) {
                $iframe.attr('width', width).attr('height', height);
            }
        }
    };

    // Lazy Loading für Twitch Streams
    var TwitchLazyLoader = {
        
        init: function() {
            if ('IntersectionObserver' in window) {
                this.setupIntersectionObserver();
            } else {
                // Fallback für ältere Browser
                this.loadAllStreams();
            }
        },

        setupIntersectionObserver: function() {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var $element = $(entry.target);
                        TwitchLazyLoader.loadStream($element);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px'
            });

            $('.twitch-lazy-load').each(function() {
                observer.observe(this);
            });
        },

        loadStream: function($element) {
            var channel = $element.data('channel');
            var options = $element.data('options') || {};
            
            if (channel) {
                var embed = TwitchEmbedHelper.createEmbed(channel, options);
                $element.replaceWith(embed);
            }
        },

        loadAllStreams: function() {
            $('.twitch-lazy-load').each(function() {
                var $element = $(this);
                TwitchLazyLoader.loadStream($element);
            });
        }
    };

    // Error Handler
    var TwitchErrorHandler = {
        
        showError: function(message, $container) {
            var errorHtml = '<div class="twitch-error">' +
                '<p>⚠️ ' + message + '</p>' +
                '</div>';
            
            if ($container) {
                $container.html(errorHtml);
            } else {
                $('.twitch-stream-wrapper').html(errorHtml);
            }
        },

        logError: function(error, context) {
            console.error('Twitch Stream Error:', error, context);
            
            // Optional: Fehler an Server senden
            if (twitch_vars && twitch_vars.error_logging) {
                $.post(ajaxurl, {
                    action: 'twitch_log_error',
                    error: error.message || error,
                    context: context || 'unknown',
                    nonce: twitch_vars.nonce
                });
            }
        }
    };

    // Initialize
    $(document).ready(function() {
        // Prüfen ob Twitch-Variablen vorhanden sind
        if (typeof twitch_vars !== 'undefined') {
            TwitchStreamManager.init();
            TwitchLazyLoader.init();
        }

        // Fallback für fehlende Variablen
        window.twitch_vars = window.twitch_vars || {
            nonce: '',
            error_logging: false
        };
    });

    // Globale Funktionen für externe Nutzung
    window.TwitchStream = {
        refresh: function(channel) {
            var $container = $('.twitch-stream-wrapper[data-channel="' + channel + '"]');
            if ($container.length) {
                TwitchStreamManager.refreshStream({ preventDefault: function() {} });
            }
        },
        
        createEmbed: function(channel, options) {
            return TwitchEmbedHelper.createEmbed(channel, options);
        },
        
        checkStatus: function(channel, callback) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'twitch_check_status',
                    channel: channel,
                    nonce: twitch_vars.nonce
                },
                success: function(response) {
                    if (callback) callback(response.success ? response.data.is_live : false);
                },
                error: function() {
                    if (callback) callback(false);
                }
            });
        }
    };

})(jQuery);
