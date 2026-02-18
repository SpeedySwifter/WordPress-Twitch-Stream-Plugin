/**
 * Oxygen Builder Integration JavaScript
 */

(function($) {
    'use strict';

    // Oxygen Builder Integration
    if (typeof oxygen !== 'undefined') {
        
        // Twitch Stream Component
        oxygen.addComponent('twitch_stream', {
            name: 'Twitch Stream',
            icon: 'oxygen-icon-video',
            category: 'Twitch Stream',
            html: '<div class="twitch-stream-oxygen" data-component="twitch_stream"><div class="twitch-preview-placeholder"><span class="dashicons dashicons-video-alt3"></span><p>Twitch Stream Component</p></div></div>',
            params: {
                channel: {
                    type: 'text',
                    label: 'Kanal',
                    placeholder: 'z.B. shroud',
                    help: 'Gib den Twitch-Benutzernamen ein'
                },
                width: {
                    type: 'text',
                    label: 'Breite',
                    default: '100%',
                    placeholder: '100% oder 800px'
                },
                height: {
                    type: 'number',
                    label: 'Höhe',
                    default: 480,
                    min: 200,
                    max: 1080
                },
                autoplay: {
                    type: 'checkbox',
                    label: 'Autoplay',
                    default: true
                },
                muted: {
                    type: 'checkbox',
                    label: 'Stummgeschaltet',
                    default: false
                },
                show_info: {
                    type: 'checkbox',
                    label: 'Stream-Infos anzeigen',
                    default: false
                },
                info_layout: {
                    type: 'select',
                    label: 'Info Layout',
                    default: 'horizontal',
                    options: {
                        'horizontal': 'Horizontal',
                        'vertical': 'Vertikal',
                        'compact': 'Kompakt'
                    }
                }
            },
            advanced: {
                id: 'twitch_stream_' + Math.random().toString(36).substr(2, 9),
                class: 'twitch-stream-oxygen',
                wrapper_class: 'twitch-stream-wrapper'
            },
            render: function(component) {
                var params = component.params;
                var shortcode = '[twitch_stream channel="' + params.channel + '" width="' + params.width + '" height="' + params.height + '" autoplay="' + params.autoplay + '" muted="' + params.muted + '"]';
                
                if (params.show_info) {
                    shortcode += '[twitch_stream_info channel="' + params.channel + '" layout="' + params.info_layout + '"]';
                }
                
                return shortcode;
            }
        });

        // Twitch Grid Component
        oxygen.addComponent('twitch_grid', {
            name: 'Twitch Stream Grid',
            icon: 'oxygen-icon-grid',
            category: 'Twitch Stream',
            html: '<div class="twitch-grid-oxygen" data-component="twitch_grid"><div class="twitch-preview-placeholder"><span class="dashicons dashicons-grid-view"></span><p>Twitch Grid Component</p></div></div>',
            params: {
                channels: {
                    type: 'text',
                    label: 'Kanäle',
                    placeholder: 'shroud, ninja, pokimane',
                    help: 'Kommagetrennte Liste von Twitch-Kanälen'
                },
                columns: {
                    type: 'number',
                    label: 'Spalten',
                    default: 3,
                    min: 1,
                    max: 6
                },
                layout: {
                    type: 'select',
                    label: 'Layout',
                    default: 'grid',
                    options: {
                        'grid': 'Grid',
                        'list': 'Liste',
                        'masonry': 'Masonry'
                    }
                },
                gap: {
                    type: 'text',
                    label: 'Abstand',
                    default: '20px',
                    placeholder: '20px'
                },
                responsive: {
                    type: 'checkbox',
                    label: 'Responsive',
                    default: true
                },
                show_player: {
                    type: 'checkbox',
                    label: 'Player anzeigen',
                    default: true
                },
                show_info: {
                    type: 'checkbox',
                    label: 'Informationen anzeigen',
                    default: true
                },
                player_height: {
                    type: 'number',
                    label: 'Player Höhe',
                    default: 200,
                    min: 100,
                    max: 400
                }
            },
            advanced: {
                id: 'twitch_grid_' + Math.random().toString(36).substr(2, 9),
                class: 'twitch-grid-oxygen',
                wrapper_class: 'twitch-grid-wrapper'
            },
            render: function(component) {
                var params = component.params;
                var shortcode = '[twitch_streams_grid channels="' + params.channels + '" columns="' + params.columns + '" layout="' + params.layout + '" gap="' + params.gap + '" responsive="' + params.responsive + '" show_player="' + params.show_player + '" show_info="' + params.show_info + '"]';
                
                return shortcode;
            }
        });

        // Live Preview Update
        oxygen.on('component:updated', function(component) {
            if (component.type === 'twitch_stream' || component.type === 'twitch_grid') {
                updateTwitchPreview(component);
            }
        });

        function updateTwitchPreview(component) {
            var $element = $('.oxygen-component[data-id="' + component.id + '"]');
            var $preview = $element.find('.twitch-preview-placeholder');
            
            if ($preview.length) {
                var params = component.params;
                var previewHtml = '';
                
                if (component.type === 'twitch_stream') {
                    previewHtml = '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-video-alt3"></span><p>Twitch Stream: ' + (params.channel || 'Kein Kanal') + '</p><p><em>Vorschau im Frontend</em></p></div>';
                } else if (component.type === 'twitch_grid') {
                    var channelCount = params.channels ? params.channels.split(',').length : 0;
                    previewHtml = '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-grid-view"></span><p>Twitch Grid: ' + channelCount + ' Kanäle</p><p><em>Vorschau im Frontend</em></p></div>';
                }
                
                $preview.replaceWith(previewHtml);
            }
        }

        // API Status Check
        function checkTwitchApiStatus() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'twitch_check_api_status',
                    nonce: twitchOxygenData.nonce
                },
                success: function(response) {
                    if (response.success && !response.data.connected) {
                        showApiWarning();
                    }
                }
            });
        }

        function showApiWarning() {
            if (!$('.twitch-api-warning').length) {
                var warning = '<div class="twitch-api-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 12px; margin: 10px 0;">' +
                    '<p style="margin: 0; color: #856404;">' +
                    '<strong>⚠️ Twitch API nicht verbunden!</strong> ' +
                    '<a href="' + twitchOxygenData.adminUrl + '" target="_blank">API-Einstellungen konfigurieren</a>' +
                    '</p>' +
                    '</div>';
                
                $('.oxygen-sidebar').prepend(warning);
            }
        }

        // Initialize
        if (typeof twitchOxygenData !== 'undefined') {
            checkTwitchApiStatus();
        }
    }

})(jQuery);
