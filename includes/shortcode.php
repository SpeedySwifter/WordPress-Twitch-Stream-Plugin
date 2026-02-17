<?php
/**
 * Shortcode Handler
 */

/**
 * Shortcode: [twitch_stream channel="beispiel"]
 */
function wp_twitch_stream_shortcode($atts) {
    $atts = shortcode_atts([
        'channel' => '',
        'width' => '100%',
        'height' => '480',
        'autoplay' => 'true',
        'muted' => 'false'
    ], $atts);

    if (empty($atts['channel'])) {
        return '<p class="twitch-error">Bitte gib einen Twitch-Kanal an.</p>';
    }

    $api = new WP_Twitch_API();
    
    // API-Verbindung testen
    $connection_test = $api->test_connection();
    if (!$connection_test['success']) {
        return '<div class="twitch-error">
            <p>⚠️ API-Verbindungsfehler: ' . esc_html($connection_test['message']) . '</p>
            <p>Bitte überprüfe deine API-Einstellungen unter <a href="' . admin_url('options-general.php?page=twitch-api-settings') . '">Einstellungen → Twitch API</a>.</p>
        </div>';
    }

    // Stream-Status mit Cache prüfen
    $is_live = $api->get_cached_stream_status($atts['channel']);

    if (!$is_live) {
        return '<div class="twitch-offline">
            <p>🔴 Stream ist derzeit offline</p>
            <p>Folge <a href="https://twitch.tv/' . esc_attr($atts['channel']) . '" target="_blank">@' . esc_html($atts['channel']) . '</a> um benachrichtigt zu werden!</p>
        </div>';
    }

    // Twitch Embed
    $domain = $_SERVER['HTTP_HOST'];
    
    // Localhost-Unterstützung
    if (in_array($domain, ['localhost', '127.0.0.1'])) {
        $domain = 'localhost';
    }
    
    $embed_url = sprintf(
        'https://player.twitch.tv/?channel=%s&parent=%s&autoplay=%s&muted=%s',
        urlencode($atts['channel']),
        urlencode($domain),
        $atts['autoplay'],
        $atts['muted']
    );

    return sprintf(
        '<div class="twitch-stream-container">
            <iframe
                src="%s"
                width="%s"
                height="%s"
                frameborder="0"
                scrolling="no"
                allowfullscreen="true">
            </iframe>
        </div>',
        esc_url($embed_url),
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );
}

add_shortcode('twitch_stream', 'wp_twitch_stream_shortcode');

/**
 * Shortcode für Stream-Info
 */
function wp_twitch_stream_info_shortcode($atts) {
    $atts = shortcode_atts([
        'channel' => '',
        'show_title' => 'true',
        'show_game' => 'true',
        'show_viewers' => 'true',
        'show_thumbnail' => 'false'
    ], $atts);

    if (empty($atts['channel'])) {
        return '<p class="twitch-error">Bitte gib einen Twitch-Kanal an.</p>';
    }

    $api = new WP_Twitch_API();
    $stream_data = $api->get_stream_data($atts['channel']);

    if (!$stream_data) {
        return '<div class="twitch-info-offline">
            <p>🔴 ' . esc_html($atts['channel']) . ' ist offline</p>
        </div>';
    }

    $output = '<div class="twitch-stream-info">';
    
    // Thumbnail
    if ($atts['show_thumbnail'] === 'true' && !empty($stream_data['thumbnail_url'])) {
        $thumbnail_url = str_replace('{width}', '320', str_replace('{height}', '180', $stream_data['thumbnail_url']));
        $output .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($stream_data['user_name']) . ' Stream Thumbnail" class="twitch-thumbnail">';
    }
    
    $output .= '<div class="twitch-info-details">';
    
    // Titel
    if ($atts['show_title'] === 'true' && !empty($stream_data['title'])) {
        $output .= '<h3 class="twitch-title">' . esc_html($stream_data['title']) . '</h3>';
    }
    
    // Game
    if ($atts['show_game'] === 'true' && !empty($stream_data['game_name'])) {
        $output .= '<p class="twitch-game">🎮 ' . esc_html($stream_data['game_name']) . '</p>';
    }
    
    // Zuschauer
    if ($atts['show_viewers'] === 'true' && isset($stream_data['viewer_count'])) {
        $output .= '<p class="twitch-viewers">👁️ ' . number_format($stream_data['viewer_count']) . ' Zuschauer</p>';
    }
    
    $output .= '</div></div>';
    
    return $output;
}

add_shortcode('twitch_stream_info', 'wp_twitch_stream_info_shortcode');

/**
 * AJAX-Handler für Live-Status-Prüfung
 */
function wp_twitch_check_stream_status() {
    check_ajax_referer('twitch_stream_nonce', 'nonce');
    
    $channel = sanitize_text_field($_POST['channel']);
    if (empty($channel)) {
        wp_send_json_error(['message' => 'Kein Kanal angegeben']);
    }
    
    $api = new WP_Twitch_API();
    $is_live = $api->get_cached_stream_status($channel);
    
    wp_send_json_success(['is_live' => $is_live]);
}

add_action('wp_ajax_twitch_check_status', 'wp_twitch_check_stream_status');
add_action('wp_ajax_nopriv_twitch_check_status', 'wp_twitch_check_stream_status');
?>
