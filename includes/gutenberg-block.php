<?php
/**
 * Gutenberg Block für Twitch Stream Integration
 */

// Block registrieren
function wp_twitch_register_block() {
    // Block Script registrieren
    wp_register_script(
        'twitch-stream-block',
        WP_TWITCH_PLUGIN_URL . 'assets/js/block.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor'),
        WP_TWITCH_VERSION,
        true
    );

    // Block Style registrieren
    wp_register_style(
        'twitch-stream-block-style',
        WP_TWITCH_PLUGIN_URL . 'assets/css/block.css',
        array(),
        WP_TWITCH_VERSION
    );

    // Block Type registrieren
    register_block_type('wp-twitch-stream/stream', array(
        'editor_script' => 'twitch-stream-block',
        'editor_style' => 'twitch-stream-block-style',
        'style' => 'wp-twitch-stream-frontend',
        'attributes' => array(
            'channel' => array(
                'type' => 'string',
                'default' => '',
            ),
            'width' => array(
                'type' => 'string',
                'default' => '100%',
            ),
            'height' => array(
                'type' => 'string',
                'default' => '480',
            ),
            'autoplay' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'muted' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'showInfo' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'infoLayout' => array(
                'type' => 'string',
                'default' => 'horizontal',
            ),
        ),
        'render_callback' => 'wp_twitch_render_block_stream',
    ));

    // Grid Block registrieren
    register_block_type('wp-twitch-stream/grid', array(
        'editor_script' => 'twitch-stream-block',
        'editor_style' => 'twitch-stream-block-style',
        'style' => 'wp-twitch-stream-frontend',
        'attributes' => array(
            'channels' => array(
                'type' => 'string',
                'default' => '',
            ),
            'columns' => array(
                'type' => 'number',
                'default' => 3,
            ),
            'layout' => array(
                'type' => 'string',
                'default' => 'grid',
            ),
            'showPlayer' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'showInfo' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'gap' => array(
                'type' => 'string',
                'default' => '20px',
            ),
            'responsive' => array(
                'type' => 'boolean',
                'default' => true,
            ),
        ),
        'render_callback' => 'wp_twitch_render_block_grid',
    ));
}
add_action('init', 'wp_twitch_register_block');

/**
 * Stream Block Render Callback
 */
function wp_twitch_render_block_stream($attributes) {
    $atts = array(
        'channel' => $attributes['channel'] ?? '',
        'width' => $attributes['width'] ?? '100%',
        'height' => $attributes['height'] ?? '480',
        'autoplay' => $attributes['autoplay'] ? 'true' : 'false',
        'muted' => $attributes['muted'] ? 'true' : 'false',
    );

    $output = wp_twitch_stream_shortcode($atts);

    // Stream Info hinzufügen
    if ($attributes['showInfo'] && !empty($attributes['channel'])) {
        $info_atts = array(
            'channel' => $attributes['channel'],
            'layout' => $attributes['infoLayout'] ?? 'horizontal',
            'show_title' => 'true',
            'show_game' => 'true',
            'show_viewers' => 'true',
            'show_thumbnail' => 'true',
            'show_avatar' => 'true',
        );
        $output .= wp_twitch_stream_info_shortcode($info_atts);
    }

    return $output;
}

/**
 * Grid Block Render Callback
 */
function wp_twitch_render_block_grid($attributes) {
    $atts = array(
        'channels' => $attributes['channels'] ?? '',
        'columns' => $attributes['columns'] ?? 3,
        'layout' => $attributes['layout'] ?? 'grid',
        'show_player' => $attributes['showPlayer'] ? 'true' : 'false',
        'show_info' => $attributes['showInfo'] ? 'true' : 'false',
        'gap' => $attributes['gap'] ?? '20px',
        'responsive' => $attributes['responsive'] ? 'true' : 'false',
    );

    return wp_twitch_streams_grid_shortcode($atts);
}

/**
 * Block Category hinzufügen
 */
function wp_twitch_add_block_category($categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'twitch-stream',
                'title' => __('Twitch Stream', 'wp-twitch-stream'),
                'icon' => 'video-alt3',
            ),
        )
    );
}
add_filter('block_categories_all', 'wp_twitch_add_block_category');

/**
 * API-Daten für Block Editor bereitstellen
 */
function wp_twitch_block_editor_data() {
    wp_localize_script('twitch-stream-block', 'twitchBlockData', array(
        'apiConnected' => !empty(get_option('twitch_client_id')) && !empty(get_option('twitch_client_secret')),
        'adminUrl' => admin_url('options-general.php?page=twitch-api-settings'),
        'strings' => array(
            'channelPlaceholder' => __('Twitch Kanalname eingeben...', 'wp-twitch-stream'),
            'channelsPlaceholder' => __('Kanal1, Kanal2, Kanal3', 'wp-twitch-stream'),
            'apiNotConnected' => __('Twitch API nicht verbunden. Bitte konfiguriere die API-Einstellungen.', 'wp-twitch-stream'),
            'goToSettings' => __('Zu den Einstellungen', 'wp-twitch-stream'),
        ),
    ));
}
add_action('enqueue_block_editor_assets', 'wp_twitch_block_editor_data');
?>
