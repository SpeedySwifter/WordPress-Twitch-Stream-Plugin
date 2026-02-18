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
        'show_thumbnail' => 'true',
        'show_avatar' => 'true',
        'show_started' => 'false',
        'show_language' => 'false',
        'layout' => 'horizontal' // horizontal, vertical, compact
    ], $atts);

    if (empty($atts['channel'])) {
        return '<p class="twitch-error">❌ Bitte gib einen Twitch-Kanal an.</p>';
    }

    $api = new WP_Twitch_API();
    $info = $api->get_complete_stream_info($atts['channel']);

    if (!$info) {
        return '<div class="twitch-info-offline twitch-info-' . esc_attr($atts['layout']) . '">
            <p>🔴 ' . esc_html($atts['channel']) . ' ist offline</p>
        </div>';
    }

    $user = $info['user'];
    $stream = $info['stream'];
    $game = $info['game'];

    $classes = ['twitch-stream-info', 'twitch-info-' . esc_attr($atts['layout'])];
    if ($info['is_live']) {
        $classes[] = 'twitch-info-live';
    } else {
        $classes[] = 'twitch-info-offline';
    }

    $output = '<div class="' . implode(' ', $classes) . '">';
    
    // Avatar
    if ($atts['show_avatar'] === 'true' && $user && !empty($user['profile_image_url'])) {
        $output .= '<div class="twitch-avatar">';
        $output .= '<img src="' . esc_url($user['profile_image_url']) . '" alt="' . esc_attr($user['display_name']) . ' Avatar" class="twitch-avatar-img">';
        $output .= '</div>';
    }
    
    $output .= '<div class="twitch-info-details">';
    
    // Live Status Badge
    if ($info['is_live']) {
        $output .= '<span class="twitch-live-badge">🔴 LIVE</span>';
    }
    
    // Titel
    if ($atts['show_title'] === 'true') {
        $title = $stream && !empty($stream['title']) ? $stream['title'] : ($user ? $user['display_name'] : $atts['channel']);
        $output .= '<h3 class="twitch-title">' . esc_html($title) . '</h3>';
    }
    
    // Game
    if ($atts['show_game'] === 'true' && $game && !empty($game['name'])) {
        $output .= '<p class="twitch-game">🎮 ' . esc_html($game['name']) . '</p>';
    }
    
    // Zuschauer
    if ($atts['show_viewers'] === 'true' && $stream && isset($stream['viewer_count'])) {
        $output .= '<p class="twitch-viewers">👁️ ' . number_format($stream['viewer_count']) . ' Zuschauer</p>';
    }
    
    // Startzeit
    if ($atts['show_started'] === 'true' && $stream && !empty($stream['started_at'])) {
        $started_time = strtotime($stream['started_at']);
        $duration = human_time_diff($started_time);
        $output .= '<p class="twitch-started">⏰ ' . esc_html($duration) . ' gestartet</p>';
    }
    
    // Sprache
    if ($atts['show_language'] === 'true' && $stream && !empty($stream['language'])) {
        $output .= '<p class="twitch-language">🌍 ' . esc_html(strtoupper($stream['language'])) . '</p>';
    }
    
    // Thumbnail
    if ($atts['show_thumbnail'] === 'true' && $stream && !empty($stream['thumbnail_url'])) {
        $thumbnail_url = str_replace('{width}', '320', str_replace('{height}', '180', $stream['thumbnail_url']));
        $output .= '<div class="twitch-thumbnail">';
        $output .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($user['display_name']) . ' Stream Thumbnail" class="twitch-thumbnail-img">';
        $output .= '</div>';
    }
    
    $output .= '</div></div>';
    
    return $output;
}

add_shortcode('twitch_stream_info', 'wp_twitch_stream_info_shortcode');

/**
 * Shortcode für Multiple Streams Grid
 */
function wp_twitch_streams_grid_shortcode($atts) {
    $atts = shortcode_atts([
        'channels' => '',
        'columns' => '3',
        'show_player' => 'true',
        'show_info' => 'true',
        'layout' => 'grid', // grid, list, masonry
        'gap' => '20px',
        'responsive' => 'true'
    ], $atts);

    if (empty($atts['channels'])) {
        return '<p class="twitch-error">❌ Bitte gib mindestens einen Kanal an (channels="channel1,channel2,channel3").</p>';
    }

    $channels = array_map('trim', explode(',', $atts['channels']));
    $columns = intval($atts['columns']);
    $columns = max(1, min(6, $columns)); // Zwischen 1-6 Spalten

    $grid_classes = [
        'twitch-streams-grid',
        'twitch-grid-' . esc_attr($atts['layout']),
        'twitch-columns-' . $columns
    ];

    if ($atts['responsive'] === 'true') {
        $grid_classes[] = 'twitch-responsive';
    }

    $output = '<div class="' . implode(' ', $grid_classes) . '" style="--twitch-gap: ' . esc_attr($atts['gap']) . ';">';

    foreach ($channels as $channel) {
        $output .= '<div class="twitch-grid-item">';
        
        // Stream Player (wenn live)
        if ($atts['show_player'] === 'true') {
            $player_atts = [
                'channel' => $channel,
                'width' => '100%',
                'height' => '200',
                'autoplay' => 'false',
                'muted' => 'true'
            ];
            $output .= wp_twitch_stream_shortcode($player_atts);
        }
        
        // Stream Info
        if ($atts['show_info'] === 'true') {
            $info_atts = [
                'channel' => $channel,
                'show_title' => 'true',
                'show_game' => 'true',
                'show_viewers' => 'true',
                'show_thumbnail' => 'true',
                'show_avatar' => 'false',
                'layout' => 'compact'
            ];
            $output .= wp_twitch_stream_info_shortcode($info_atts);
        }
        
        $output .= '</div>';
    }

    $output .= '</div>';
    
    // Grid CSS hinzufügen
    $output .= '<style>
    .twitch-streams-grid {
        display: grid;
        gap: var(--twitch-gap, 20px);
        margin: 20px 0;
    }
    
    .twitch-streams-grid.twitch-grid-grid {
        grid-template-columns: repeat(' . $columns . ', 1fr);
    }
    
    .twitch-streams-grid.twitch-grid-list {
        grid-template-columns: 1fr;
    }
    
    .twitch-streams-grid.twitch-grid-masonry {
        column-count: ' . $columns . ';
        column-gap: var(--twitch-gap, 20px);
    }
    
    .twitch-streams-grid.twitch-grid-masonry .twitch-grid-item {
        break-inside: avoid;
        margin-bottom: var(--twitch-gap, 20px);
    }
    
    .twitch-grid-item {
        background: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .twitch-grid-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .twitch-responsive.twitch-columns-1 { grid-template-columns: 1fr; }
    .twitch-responsive.twitch-columns-2 { grid-template-columns: repeat(2, 1fr); }
    .twitch-responsive.twitch-columns-3 { grid-template-columns: repeat(3, 1fr); }
    .twitch-responsive.twitch-columns-4 { grid-template-columns: repeat(4, 1fr); }
    .twitch-responsive.twitch-columns-5 { grid-template-columns: repeat(5, 1fr); }
    .twitch-responsive.twitch-columns-6 { grid-template-columns: repeat(6, 1fr); }
    
    @media (max-width: 1200px) {
        .twitch-responsive.twitch-columns-6 { grid-template-columns: repeat(4, 1fr); }
        .twitch-responsive.twitch-columns-5 { grid-template-columns: repeat(4, 1fr); }
        .twitch-responsive.twitch-columns-4 { grid-template-columns: repeat(3, 1fr); }
    }
    
    @media (max-width: 768px) {
        .twitch-responsive.twitch-columns-6 { grid-template-columns: repeat(2, 1fr); }
        .twitch-responsive.twitch-columns-5 { grid-template-columns: repeat(2, 1fr); }
        .twitch-responsive.twitch-columns-4 { grid-template-columns: repeat(2, 1fr); }
        .twitch-responsive.twitch-columns-3 { grid-template-columns: repeat(2, 1fr); }
        .twitch-responsive.twitch-columns-2 { grid-template-columns: 1fr; }
        .twitch-responsive.twitch-columns-1 { grid-template-columns: 1fr; }
    }
    
    @media (max-width: 480px) {
        .twitch-responsive.twitch-columns-6,
        .twitch-responsive.twitch-columns-5,
        .twitch-responsive.twitch-columns-4,
        .twitch-responsive.twitch-columns-3,
        .twitch-responsive.twitch-columns-2,
        .twitch-responsive.twitch-columns-1 {
            grid-template-columns: 1fr;
        }
    }
    </style>';

    return $output;
}

add_shortcode('twitch_streams_grid', 'wp_twitch_streams_grid_shortcode');

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
