/**
 * Twitch Stream Gutenberg Blocks
 */

(function (blocks, element, components, editor) {
    'use strict';

    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var RangeControl = components.RangeControl;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;
    var Disabled = components.Disabled;
    var Notice = components.Notice;

    // Twitch Stream Block
    blocks.registerBlockType('wp-twitch-stream/stream', {
        title: twitchBlockData.strings.blockTitle || 'Twitch Stream',
        description: twitchBlockData.strings.blockDescription || 'Bettet einen Twitch Stream mit Live-Status Erkennung ein',
        icon: 'video-alt3',
        category: 'twitch-stream',
        attributes: {
            channel: {
                type: 'string',
                default: '',
            },
            width: {
                type: 'string',
                default: '100%',
            },
            height: {
                type: 'string',
                default: '480',
            },
            autoplay: {
                type: 'boolean',
                default: true,
            },
            muted: {
                type: 'boolean',
                default: false,
            },
            showInfo: {
                type: 'boolean',
                default: false,
            },
            infoLayout: {
                type: 'string',
                default: 'horizontal',
            },
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var isSelected = props.isSelected;

            function onChangeChannel(newChannel) {
                setAttributes({ channel: newChannel });
            }

            function onChangeWidth(newWidth) {
                setAttributes({ width: newWidth });
            }

            function onChangeHeight(newHeight) {
                setAttributes({ height: newHeight });
            }

            function onChangeAutoplay(newAutoplay) {
                setAttributes({ autoplay: newAutoplay });
            }

            function onChangeMuted(newMuted) {
                setAttributes({ muted: newMuted });
            }

            function onChangeShowInfo(newShowInfo) {
                setAttributes({ showInfo: newShowInfo });
            }

            function onChangeInfoLayout(newInfoLayout) {
                setAttributes({ infoLayout: newInfoLayout });
            }

            if (!twitchBlockData.apiConnected) {
                return el(
                    'div',
                    { className: 'twitch-block-notice' },
                    el(
                        Notice,
                        { status: 'warning', isDismissible: false },
                        el('p', {}, twitchBlockData.strings.apiNotConnected),
                        el(
                            'a',
                            {
                                href: twitchBlockData.adminUrl,
                                className: 'button button-primary'
                            },
                            twitchBlockData.strings.goToSettings
                        )
                    )
                );
            }

            return el(
                'div',
                { className: 'twitch-block-editor' },
                isSelected && el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Twitch Stream Einstellungen', initialOpen: true },
                        el(TextControl, {
                            label: 'Kanalname',
                            value: attributes.channel,
                            onChange: onChangeChannel,
                            placeholder: twitchBlockData.strings.channelPlaceholder,
                            help: 'Gib den Twitch-Benutzernamen ein'
                        }),
                        el(TextControl, {
                            label: 'Breite',
                            value: attributes.width,
                            onChange: onChangeWidth,
                            placeholder: '100%',
                            help: 'Breite des Players (z.B. 100%, 800px)'
                        }),
                        el(TextControl, {
                            label: 'Höhe',
                            value: attributes.height,
                            onChange: onChangeHeight,
                            placeholder: '480',
                            help: 'Höhe des Players in Pixeln'
                        }),
                        el(ToggleControl, {
                            label: 'Autoplay',
                            checked: attributes.autoplay,
                            onChange: onChangeAutoplay,
                            help: 'Stream automatisch starten'
                        }),
                        el(ToggleControl, {
                            label: 'Stummgeschaltet',
                            checked: attributes.muted,
                            onChange: onChangeMuted,
                            help: 'Audio standardmäßig stumm schalten'
                        }),
                        el(ToggleControl, {
                            label: 'Stream-Informationen anzeigen',
                            checked: attributes.showInfo,
                            onChange: onChangeShowInfo,
                            help: 'Zusätzliche Informationen zum Stream anzeigen'
                        }),
                        attributes.showInfo && el(
                            SelectControl,
                            {
                                label: 'Info-Layout',
                                value: attributes.infoLayout,
                                options: [
                                    { label: 'Horizontal', value: 'horizontal' },
                                    { label: 'Vertikal', value: 'vertical' },
                                    { label: 'Kompakt', value: 'compact' }
                                ],
                                onChange: onChangeInfoLayout
                            }
                        )
                    )
                ),
                el(
                    'div',
                    { className: 'twitch-block-preview' },
                    attributes.channel ? 
                        el('div', { 
                            className: 'twitch-preview-content',
                            dangerouslySetInnerHTML: {
                                __html: '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-video-alt3"></span><p>Twitch Stream: ' + attributes.channel + '</p><p><em>Vorschau im Frontend</em></p></div>'
                            }
                        }) :
                        el('div', { 
                            className: 'twitch-preview-placeholder',
                            dangerouslySetInnerHTML: {
                                __html: '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-video-alt3"></span><p>Wähle einen Twitch-Kanal aus</p></div>'
                            }
                        })
                )
            );
        },
        save: function () {
            return null; // Server-side rendering
        },
    });

    // Twitch Grid Block
    blocks.registerBlockType('wp-twitch-stream/grid', {
        title: twitchBlockData.strings.gridTitle || 'Twitch Stream Grid',
        description: twitchBlockData.strings.gridDescription || 'Zeigt mehrere Twitch Streams in einem Grid an',
        icon: 'grid-view',
        category: 'twitch-stream',
        attributes: {
            channels: {
                type: 'string',
                default: '',
            },
            columns: {
                type: 'number',
                default: 3,
            },
            layout: {
                type: 'string',
                default: 'grid',
            },
            showPlayer: {
                type: 'boolean',
                default: true,
            },
            showInfo: {
                type: 'boolean',
                default: true,
            },
            gap: {
                type: 'string',
                default: '20px',
            },
            responsive: {
                type: 'boolean',
                default: true,
            },
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var isSelected = props.isSelected;

            function onChangeChannels(newChannels) {
                setAttributes({ channels: newChannels });
            }

            function onChangeColumns(newColumns) {
                setAttributes({ columns: newColumns });
            }

            function onChangeLayout(newLayout) {
                setAttributes({ layout: newLayout });
            }

            function onChangeShowPlayer(newShowPlayer) {
                setAttributes({ showPlayer: newShowPlayer });
            }

            function onChangeShowInfo(newShowInfo) {
                setAttributes({ showInfo: newShowInfo });
            }

            function onChangeGap(newGap) {
                setAttributes({ gap: newGap });
            }

            function onChangeResponsive(newResponsive) {
                setAttributes({ responsive: newResponsive });
            }

            if (!twitchBlockData.apiConnected) {
                return el(
                    'div',
                    { className: 'twitch-block-notice' },
                    el(
                        Notice,
                        { status: 'warning', isDismissible: false },
                        el('p', {}, twitchBlockData.strings.apiNotConnected),
                        el(
                            'a',
                            {
                                href: twitchBlockData.adminUrl,
                                className: 'button button-primary'
                            },
                            twitchBlockData.strings.goToSettings
                        )
                    )
                );
            }

            return el(
                'div',
                { className: 'twitch-block-editor' },
                isSelected && el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Twitch Grid Einstellungen', initialOpen: true },
                        el(TextControl, {
                            label: 'Kanäle',
                            value: attributes.channels,
                            onChange: onChangeChannels,
                            placeholder: twitchBlockData.strings.channelsPlaceholder,
                            help: 'Kommagetrennte Liste von Twitch-Kanälen'
                        }),
                        el(RangeControl, {
                            label: 'Spalten',
                            value: attributes.columns,
                            onChange: onChangeColumns,
                            min: 1,
                            max: 6,
                            help: 'Anzahl der Spalten im Grid'
                        }),
                        el(SelectControl, {
                            label: 'Layout',
                            value: attributes.layout,
                            options: [
                                { label: 'Grid', value: 'grid' },
                                { label: 'Liste', value: 'list' },
                                { label: 'Masonry', value: 'masonry' }
                            ],
                            onChange: onChangeLayout
                        }),
                        el(ToggleControl, {
                            label: 'Player anzeigen',
                            checked: attributes.showPlayer,
                            onChange: onChangeShowPlayer,
                            help: 'Twitch Player im Grid anzeigen'
                        }),
                        el(ToggleControl, {
                            label: 'Informationen anzeigen',
                            checked: attributes.showInfo,
                            onChange: onChangeShowInfo,
                            help: 'Stream-Informationen anzeigen'
                        }),
                        el(TextControl, {
                            label: 'Abstand',
                            value: attributes.gap,
                            onChange: onChangeGap,
                            placeholder: '20px',
                            help: 'Abstand zwischen den Grid-Items'
                        }),
                        el(ToggleControl, {
                            label: 'Responsive',
                            checked: attributes.responsive,
                            onChange: onChangeResponsive,
                            help: 'Responsive Breakpoints aktivieren'
                        })
                    )
                ),
                el(
                    'div',
                    { className: 'twitch-block-preview' },
                    attributes.channels ? 
                        el('div', { 
                            className: 'twitch-preview-content',
                            dangerouslySetInnerHTML: {
                                __html: '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-grid-view"></span><p>Twitch Grid: ' + attributes.channels.split(',').length + ' Kanäle</p><p><em>Vorschau im Frontend</em></p></div>'
                            }
                        }) :
                        el('div', { 
                            className: 'twitch-preview-placeholder',
                            dangerouslySetInnerHTML: {
                                __html: '<div class="twitch-preview-placeholder"><span class="dashicons dashicons-grid-view"></span><p>Füge Twitch-Kanäle hinzu</p></div>'
                            }
                        })
                )
            );
        },
        save: function () {
            return null; // Server-side rendering
        },
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.editor
);
