/**
 * Divi Builder Integration JavaScript
 */

(function($) {
    'use strict';

    // Divi Builder Integration
    if (typeof et_pb_builder !== 'undefined') {
        
        // Twitch Stream Module
        et_pb_builder.register_module('twitch_stream', {
            name: 'Twitch Stream',
            icon: 'video',
            category: 'Twitch Stream',
            settings: {
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
                    type: 'yes_no',
                    label: 'Autoplay',
                    default: 'on'
                },
                muted: {
                    type: 'yes_no',
                    label: 'Stummgeschaltet',
                    default: 'off'
                },
                show_info: {
                    type: 'yes_no',
                    label: 'Stream-Infos anzeigen',
                    default: 'off'
                },
                info_layout: {
                    type: 'select',
                    label: 'Info Layout',
                    default: 'horizontal',
                    options: {
                        'horizontal': 'Horizontal',
                        'vertical': 'Vertikal',
                        'compact': 'Kompakt'
                    },
                    show_if: {
                        show_info: 'on'
                    }
                },
                show_avatar: {
                    type: 'yes_no',
                    label: 'Avatar anzeigen',
                    default: 'on',
                    show_if: {
                        show_info: 'on'
                    }
                },
                show_thumbnail: {
                    type: 'yes_no',
                    label: 'Thumbnail anzeigen',
                    default: 'on',
                    show_if: {
                        show_info: 'on'
                    }
                },
                show_game: {
                    type: 'yes_no',
                    label: 'Spiel anzeigen',
                    default: 'on',
                    show_if: {
                        show_info: 'on'
                    }
                },
                show_viewers: {
                    type: 'yes_no',
                    label: 'Zuschauer anzeigen',
                    default: 'on',
                    show_if: {
                        show_info: 'on'
                    }
                }
            },
            render: function(props) {
                var shortcode = '[twitch_stream channel="' + props.channel + '" width="' + props.width + '" height="' + props.height + '" autoplay="' + props.autoplay + '" muted="' + props.muted + '"]';
                
                if (props.show_info === 'on') {
                    shortcode += '[twitch_stream_info channel="' + props.channel + '" layout="' + props.info_layout + '" show_avatar="' + props.show_avatar + '" show_thumbnail="' + props.show_thumbnail + '" show_game="' + props.show_game + '" show_viewers="' + props.show_viewers + '"]';
                }
                
                return shortcode;
            }
        });

        // Twitch Grid Module
        et_pb_builder.register_module('twitch_grid', {
            name: 'Twitch Stream Grid',
            icon: 'grid',
            category: 'Twitch Stream',
            settings: {
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
                    type: 'yes_no',
                    label: 'Responsive',
                    default: 'on'
                },
                show_player: {
                    type: 'yes_no',
                    label: 'Player anzeigen',
                    default: 'on'
                },
                show_info: {
                    type: 'yes_no',
                    label: 'Informationen anzeigen',
                    default: 'on'
                },
                player_height: {
                    type: 'number',
                    label: 'Player Höhe',
                    default: 200,
                    min: 100,
                    max: 400,
                    show_if: {
                        show_player: 'on'
                    }
                },
                info_layout: {
                    type: 'select',
                    label: 'Info Layout',
                    default: 'compact',
                    options: {
                        'horizontal': 'Horizontal',
                        'vertical': 'Vertikal',
                        'compact': 'Kompakt'
                    },
                    show_if: {
                        show_info: 'on'
                    }
                }
            },
            render: function(props) {
                var shortcode = '[twitch_streams_grid channels="' + props.channels + '" columns="' + props.columns + '" layout="' + props.layout + '" gap="' + props.gap + '" responsive="' + props.responsive + '" show_player="' + props.show_player + '" show_info="' + props.show_info + '"]';
                
                return shortcode;
            }
        });

        // Visual Builder Integration
        if (typeof et_fb !== 'undefined') {
            // Add Twitch modules to Visual Builder
            et_fb.add_module('twitch_stream', {
                name: 'Twitch Stream',
                icon: 'video',
                category: 'Twitch Stream',
                attrs: {
                    channel: {
                        type: 'text',
                        label: 'Kanal',
                        placeholder: 'z.B. shroud'
                    },
                    width: {
                        type: 'text',
                        label: 'Breite',
                        default: '100%'
                    },
                    height: {
                        type: 'number',
                        label: 'Höhe',
                        default: 480
                    },
                    autoplay: {
                        type: 'yes_no',
                        label: 'Autoplay',
                        default: 'on'
                    },
                    muted: {
                        type: 'yes_no',
                        label: 'Stummgeschaltet',
                        default: 'off'
                    },
                    show_info: {
                        type: 'yes_no',
                        label: 'Stream-Infos anzeigen',
                        default: 'off'
                    }
                },
                render: function(attrs) {
                    return '[twitch_stream channel="' + attrs.channel + '" width="' + attrs.width + '" height="' + attrs.height + '" autoplay="' + attrs.autoplay + '" muted="' + attrs.muted + '"]';
                }
            });

            et_fb.add_module('twitch_grid', {
                name: 'Twitch Stream Grid',
                icon: 'grid',
                category: 'Twitch Stream',
                attrs: {
                    channels: {
                        type: 'text',
                        label: 'Kanäle',
                        placeholder: 'shroud, ninja, pokimane'
                    },
                    columns: {
                        type: 'number',
                        label: 'Spalten',
                        default: 3
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
                        default: '20px'
                    },
                    responsive: {
                        type: 'yes_no',
                        label: 'Responsive',
                        default: 'on'
                    },
                    show_player: {
                        type: 'yes_no',
                        label: 'Player anzeigen',
                        default: 'on'
                    },
                    show_info: {
                        type: 'yes_no',
                        label: 'Informationen anzeigen',
                        default: 'on'
                    }
                },
                render: function(attrs) {
                    return '[twitch_streams_grid channels="' + attrs.channels + '" columns="' + attrs.columns + '" layout="' + attrs.layout + '" gap="' + attrs.gap + '" responsive="' + attrs.responsive + '" show_player="' + attrs.show_player + '" show_info="' + attrs.show_info + '"]';
                }
            });
        }
    }

    // API Status Check
    function checkTwitchApiStatus() {
        if (typeof twitchDiviData !== 'undefined') {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'twitch_check_api_status',
                    nonce: twitchDiviData.nonce
                },
                success: function(response) {
                    if (response.success && !response.data.connected) {
                        showApiWarning();
                    }
                }
            });
        }
    }

    function showApiWarning() {
        if (!$('.twitch-api-warning').length && $('.et_pb_module_settings').length) {
            var warning = '<div class="twitch-api-warning et-pb-notification et-pb-notification-warning" style="margin: 10px 0;">' +
                '<p style="margin: 0;">' +
                '<strong>⚠️ Twitch API nicht verbunden!</strong> ' +
                '<a href="' + (typeof twitchDiviData !== 'undefined' ? twitchDiviData.adminUrl : '#') + '" target="_blank">API-Einstellungen konfigurieren</a>' +
                '</p>' +
                '</div>';
            
            $('.et_pb_module_settings').prepend(warning);
        }
    }

    // Initialize
    $(document).ready(function() {
        checkTwitchApiStatus();
        
        // Live preview updates
        $(document).on('change', '.et_pb_setting_twitch_stream input, .et_pb_setting_twitch_grid input, .et_pb_setting_twitch_stream select, .et_pb_setting_twitch_grid select', function() {
            var $module = $(this).closest('.et_pb_module');
            if ($module.length) {
                updateTwitchPreview($module);
            }
        });

        function updateTwitchPreview($module) {
            var $preview = $module.find('.et_pb_module_preview');
            if ($preview.length) {
                var moduleType = $module.hasClass('et_pb_twitch_stream') ? 'stream' : 'grid';
                var previewHtml = '';
                
                if (moduleType === 'stream') {
                    var channel = $module.find('input[name="channel"]').val() || 'Kein Kanal';
                    previewHtml = '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-video-alt3"></span><p>Twitch Stream: ' + channel + '</p><p><em>Vorschau im Frontend</em></p></div>';
                } else {
                    var channels = $module.find('input[name="channels"]').val() || '';
                    var channelCount = channels ? channels.split(',').length : 0;
                    previewHtml = '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-grid-view"></span><p>Twitch Grid: ' + channelCount + ' Kanäle</p><p><em>Vorschau im Frontend</em></p></div>';
                }
                
                $preview.html(previewHtml);
            }
        }
    });

})(jQuery);
