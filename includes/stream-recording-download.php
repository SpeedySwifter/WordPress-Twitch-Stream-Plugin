<?php
/**
 * Stream Recording Download for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Recording_Download {
    
    private $download_settings;
    private $recording;
    
    public function __construct() {
        $this->download_settings = $this->get_download_settings();
        $this->recording = new SPSWIFTER_Twitch_Stream_Recording();
        
        add_action('init', array($this, 'register_download_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_download_scripts'));
        add_action('wp_ajax_spswifter_twitch_recording_download', array($this, 'handle_download_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_recording_download', array($this, 'handle_download_ajax'));
        add_action('admin_menu', array($this, 'add_download_settings_menu'));
        
        // Add rewrite rules for direct downloads
        add_action('init', array($this, 'add_download_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_download_query_vars'));
        add_action('template_redirect', array($this, 'handle_direct_download'));
    }
    
    /**
     * Register download shortcodes
     */
    public function register_download_shortcodes() {
        add_shortcode('spswifter_twitch_recording_downloads', array($this, 'render_downloads_shortcode'));
        add_shortcode('spswifter_twitch_recording_download', array($this, 'render_download_shortcode'));
        add_shortcode('spswifter_twitch_recording_player', array($this, 'render_player_shortcode'));
    }
    
    /**
     * Enqueue download scripts
     */
    public function enqueue_download_scripts() {
        wp_enqueue_style(
            'twitch-recording-download',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/recording-download.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-recording-download',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/recording-download.js',
            array('jquery'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-recording-download', 'twitchRecordingDownload', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_recording_download_nonce'),
            'downloadUrl' => home_url('/twitch-recording-download/'),
            'maxFileSize' => $this->download_settings['max_file_size'] ?? 1073741824, // 1GB
            'chunkSize' => $this->download_settings['chunk_size'] ?? 1048576, // 1MB
        ));
    }
    
    /**
     * Render downloads shortcode
     */
    public function render_downloads_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'limit' => '20',
            'status' => 'completed',
            'show_player' => 'true',
            'show_download' => 'true',
            'layout' => 'grid',
            'sort' => 'date_desc',
        ), $atts);
        
        if (empty($atts['channel'])) {
            return '<p class="twitch-recording-error">Bitte geben Sie einen Kanal an: [spswifter_twitch_recording_downloads channel="username"]</p>';
        }
        
        $recordings = $this->recording->get_all_recordings($atts['channel'], $atts['status'], $atts['limit']);
        
        if (empty($recordings)) {
            return '<p class="twitch-recording-no-downloads">Keine Aufnahmen verfügbar</p>';
        }
        
        // Sort recordings
        $recordings = $this->sort_recordings($recordings, $atts['sort']);
        
        ob_start();
        ?>
        <div class="twitch-recording-downloads twitch-recording-<?php echo esc_attr($atts['layout']); ?>">
            <div class="twitch-recording-header">
                <h3>Aufnahmen von <?php echo esc_html($atts['channel']); ?></h3>
                <div class="twitch-recording-filters">
                    <select class="twitch-recording-sort">
                        <option value="date_desc">Neueste zuerst</option>
                        <option value="date_asc">Älteste zuerst</option>
                        <option value="duration_desc">Längste zuerst</option>
                        <option value="duration_asc">Kürzeste zuerst</option>
                        <option value="viewers_desc">Meist Zuschauer</option>
                        <option value="title_asc">Titel A-Z</option>
                    </select>
                </div>
            </div>
            
            <div class="twitch-recording-grid">
                <?php foreach ($recordings as $recording): ?>
                    <?php echo $this->render_recording_card($recording, $atts); ?>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($recordings) >= $atts['limit']): ?>
                <div class="twitch-recording-load-more">
                    <button class="twitch-recording-load-more-btn" data-channel="<?php echo esc_attr($atts['channel']); ?>" data-offset="<?php echo esc_attr($atts['limit']); ?>">
                        Mehr laden
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render download shortcode
     */
    public function render_download_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'channel' => '',
            'show_player' => 'true',
            'show_info' => 'true',
            'button_text' => 'Download',
            'button_style' => 'primary',
        ), $atts);
        
        if (empty($atts['id'])) {
            return '<p class="twitch-recording-error">Bitte geben Sie eine Aufnahme-ID an: [spswifter_twitch_recording_download id="recording_id"]</p>';
        }
        
        $recording = $this->recording->get_recording($atts['id']);
        
        if (!$recording) {
            return '<p class="twitch-recording-error">Aufnahme nicht gefunden</p>';
        }
        
        ob_start();
        ?>
        <div class="twitch-recording-download-single">
            <?php if ($atts['show_player'] === 'true' && !empty($recording['file_path'])): ?>
                <div class="twitch-recording-player">
                    <video controls preload="metadata" style="width: 100%; max-width: 800px;">
                        <source src="<?php echo esc_url($recording['file_path']); ?>" type="video/mp4">
                        Ihr Browser unterstützt keine Video-Wiedergabe.
                    </video>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_info'] === 'true'): ?>
                <div class="twitch-recording-info">
                    <h4><?php echo esc_html($recording['title']); ?></h4>
                    <p class="twitch-recording-meta">
                        <span class="twitch-recording-date"><?php echo esc_html(date('d.m.Y H:i', strtotime($recording['started_at']))); ?></span>
                        <span class="twitch-recording-duration"><?php echo $this->format_duration($recording['duration']); ?></span>
                        <?php if (isset($recording['statistics']['max_viewers'])): ?>
                            <span class="twitch-recording-viewers"><?php echo intval($recording['statistics']['max_viewers']); ?> Zuschauer</span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($recording['game'])): ?>
                        <p class="twitch-recording-game">Spiel: <?php echo esc_html($recording['game']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="twitch-recording-download-actions">
                <?php if ($recording['status'] === 'completed' && !empty($recording['file_path'])): ?>
                    <a href="<?php echo esc_url($this->get_download_url($recording['id'])); ?>" 
                       class="twitch-recording-download-btn twitch-btn-<?php echo esc_attr($atts['button_style']); ?>"
                       data-recording-id="<?php echo esc_attr($recording['id']); ?>">
                        <span class="twitch-btn-icon">⬇️</span>
                        <span class="twitch-btn-text"><?php echo esc_html($atts['button_text']); ?></span>
                        <span class="twitch-btn-size"><?php echo $this->format_file_size($recording['file_size'] ?? 0); ?></span>
                    </a>
                <?php else: ?>
                    <div class="twitch-recording-status twitch-status-<?php echo esc_attr($recording['status']); ?>">
                        <?php echo $this->get_status_text($recording['status']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render player shortcode
     */
    public function render_player_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'channel' => '',
            'width' => '100%',
            'height' => '450',
            'autoplay' => 'false',
            'controls' => 'true',
            'loop' => 'false',
            'poster' => '',
        ), $atts);
        
        if (empty($atts['id'])) {
            return '<p class="twitch-recording-error">Bitte geben Sie eine Aufnahme-ID an: [spswifter_twitch_recording_player id="recording_id"]</p>';
        }
        
        $recording = $this->recording->get_recording($atts['id']);
        
        if (!$recording || empty($recording['file_path'])) {
            return '<p class="twitch-recording-error">Video nicht verfügbar</p>';
        }
        
        $poster = $atts['poster'] ?: ($recording['thumbnail_path'] ?? '');
        
        ob_start();
        ?>
        <div class="twitch-recording-player-wrapper">
            <video 
                id="twitch-player-<?php echo esc_attr($atts['id']); ?>"
                width="<?php echo esc_attr($atts['width']); ?>"
                height="<?php echo esc_attr($atts['height']); ?>"
                <?php echo $atts['autoplay'] === 'true' ? 'autoplay' : ''; ?>
                <?php echo $atts['controls'] === 'true' ? 'controls' : ''; ?>
                <?php echo $atts['loop'] === 'true' ? 'loop' : ''; ?>
                <?php echo !empty($poster) ? 'poster="' . esc_url($poster) . '"' : ''; ?>
                preload="metadata"
                class="twitch-recording-video-player">
                <source src="<?php echo esc_url($recording['file_path']); ?>" type="video/mp4">
                <source src="<?php echo esc_url($recording['file_path']); ?>" type="video/webm">
                Ihr Browser unterstützt keine Video-Wiedergabe.
            </video>
            
            <?php if ($atts['controls'] === 'true'): ?>
                <div class="twitch-recording-player-controls">
                    <div class="twitch-player-progress">
                        <div class="twitch-player-progress-bar">
                            <div class="twitch-player-progress-fill"></div>
                        </div>
                    </div>
                    <div class="twitch-player-buttons">
                        <button class="twitch-player-play-pause">▶️</button>
                        <button class="twitch-player-mute">🔊</button>
                        <div class="twitch-player-volume">
                            <input type="range" min="0" max="100" value="100" class="twitch-player-volume-slider">
                        </div>
                        <div class="twitch-player-time">00:00 / 00:00</div>
                        <button class="twitch-player-fullscreen">⛶</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render recording card
     */
    private function render_recording_card($recording, $atts) {
        ob_start();
        ?>
        <div class="twitch-recording-card" data-recording-id="<?php echo esc_attr($recording['id']); ?>">
            <div class="twitch-recording-thumbnail">
                <?php if (!empty($recording['thumbnail_path'])): ?>
                    <img src="<?php echo esc_url($recording['thumbnail_path']); ?>" alt="<?php echo esc_attr($recording['title']); ?>">
                <?php else: ?>
                    <div class="twitch-recording-no-thumbnail">
                        <span>📹</span>
                    </div>
                <?php endif; ?>
                
                <div class="twitch-recording-duration">
                    <?php echo $this->format_duration($recording['duration']); ?>
                </div>
                
                <?php if ($atts['show_player'] === 'true' && $recording['status'] === 'completed'): ?>
                    <button class="twitch-recording-play-btn" data-recording-id="<?php echo esc_attr($recording['id']); ?>">
                        ▶️
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="twitch-recording-content">
                <h4 class="twitch-recording-title"><?php echo esc_html($recording['title']); ?></h4>
                <p class="twitch-recording-meta">
                    <span class="twitch-recording-date"><?php echo esc_html(date('d.m.Y', strtotime($recording['started_at']))); ?></span>
                    <?php if (isset($recording['statistics']['max_viewers'])): ?>
                        <span class="twitch-recording-viewers"><?php echo intval($recording['statistics']['max_viewers']); ?> Zuschauer</span>
                    <?php endif; ?>
                </p>
                <?php if (!empty($recording['game'])): ?>
                    <p class="twitch-recording-game"><?php echo esc_html($recording['game']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="twitch-recording-actions">
                <?php if ($recording['status'] === 'completed' && !empty($recording['file_path'])): ?>
                    <?php if ($atts['show_download'] === 'true'): ?>
                        <a href="<?php echo esc_url($this->get_download_url($recording['id'])); ?>" 
                           class="twitch-recording-download-btn"
                           data-recording-id="<?php echo esc_attr($recording['id']); ?>">
                            ⬇️ Download
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_player'] === 'true'): ?>
                        <button class="twitch-recording-watch-btn" data-recording-id="<?php echo esc_attr($recording['id']); ?>">
                            🎬 Ansehen
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="twitch-recording-status twitch-status-<?php echo esc_attr($recording['status']); ?>">
                        <?php echo $this->get_status_text($recording['status']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Sort recordings
     */
    private function sort_recordings($recordings, $sort) {
        switch ($sort) {
            case 'date_asc':
                usort($recordings, function($a, $b) {
                    return strtotime($a['started_at']) - strtotime($b['started_at']);
                });
                break;
            case 'date_desc':
                usort($recordings, function($a, $b) {
                    return strtotime($b['started_at']) - strtotime($a['started_at']);
                });
                break;
            case 'duration_asc':
                usort($recordings, function($a, $b) {
                    return $a['duration'] - $b['duration'];
                });
                break;
            case 'duration_desc':
                usort($recordings, function($a, $b) {
                    return $b['duration'] - $a['duration'];
                });
                break;
            case 'viewers_desc':
                usort($recordings, function($a, $b) {
                    $a_viewers = $a['statistics']['max_viewers'] ?? 0;
                    $b_viewers = $b['statistics']['max_viewers'] ?? 0;
                    return $b_viewers - $a_viewers;
                });
                break;
            case 'title_asc':
                usort($recordings, function($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
                break;
        }
        
        return $recordings;
    }
    
    /**
     * Format duration
     */
    private function format_duration($duration) {
        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } else {
            return sprintf('%dm', $minutes);
        }
    }
    
    /**
     * Format file size
     */
    private function format_file_size($bytes) {
        if ($bytes === 0) return '0 B';
        
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 1) . ' ' . $units[$pow];
    }
    
    /**
     * Get status text
     */
    private function get_status_text($status) {
        $status_texts = array(
            'recording' => 'Aufnahme läuft...',
            'processing' => 'Verarbeitung...',
            'completed' => 'Abgeschlossen',
            'failed' => 'Fehlgeschlagen',
        );
        
        return $status_texts[$status] ?? $status;
    }
    
    /**
     * Get download URL
     */
    private function get_download_url($recording_id) {
        return home_url("/twitch-recording-download/?id={$recording_id}");
    }
    
    /**
     * Add download rewrite rules
     */
    public function add_download_rewrite_rules() {
        add_rewrite_rule(
            '^twitch-recording-download/?$',
            'index.php?spswifter_twitch_recording_download=1',
            'top'
        );
        
        flush_rewrite_rules();
    }
    
    /**
     * Add download query vars
     */
    public function add_download_query_vars($query_vars) {
        $query_vars[] = 'spswifter_twitch_recording_download';
        return $query_vars;
    }
    
    /**
     * Handle direct download
     */
    public function handle_direct_download() {
        if (get_query_var('spswifter_twitch_recording_download') && isset($_GET['id'])) {
            $recording_id = sanitize_text_field($_GET['id']);
            $this->process_download($recording_id);
            exit;
        }
    }
    
    /**
     * Process download
     */
    private function process_download($recording_id) {
        $recording = $this->recording->get_recording($recording_id);
        
        if (!$recording || $recording['status'] !== 'completed' || empty($recording['file_path'])) {
            wp_die('Aufnahme nicht gefunden oder nicht verfügbar');
        }
        
        // Check permissions
        if (!$this->can_download($recording)) {
            wp_die('Keine Berechtigung zum Herunterladen');
        }
        
        // Get file path
        $file_path = $this->get_file_path($recording['file_path']);
        
        if (!file_exists($file_path)) {
            wp_die('Datei nicht gefunden');
        }
        
        // Set headers
        $filename = $this->generate_filename($recording);
        $file_size = filesize($file_path);
        
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Output file
        readfile($file_path);
        exit;
    }
    
    /**
     * Check if user can download
     */
    private function can_download($recording) {
        // Check if downloads are enabled
        if (!$this->download_settings['enabled']) {
            return false;
        }
        
        // Check user permissions
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check if public downloads are allowed
        if ($this->download_settings['public_downloads']) {
            return true;
        }
        
        // Check if user is logged in
        if ($this->download_settings['require_login'] && !is_user_logged_in()) {
            return false;
        }
        
        // Check channel permissions
        $allowed_channels = $this->download_settings['allowed_channels'] ?? array();
        if (!empty($allowed_channels) && !in_array($recording['channel'], $allowed_channels)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get file path
     */
    private function get_file_path($file_path) {
        if (strpos($file_path, WP_CONTENT_URL) === 0) {
            return str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $file_path);
        }
        
        return $file_path;
    }
    
    /**
     * Generate filename
     */
    private function generate_filename($recording) {
        $title = sanitize_file_name($recording['title']);
        $date = date('Y-m-d', strtotime($recording['started_at']));
        $channel = sanitize_file_name($recording['channel']);
        
        return "{$channel}_{$date}_{$title}.mp4";
    }
    
    /**
     * Handle download AJAX
     */
    public function handle_download_ajax() {
        check_ajax_referer('spswifter_twitch_recording_download_nonce', 'nonce');
        
        $action = $_POST['download_action'] ?? '';
        
        switch ($action) {
            case 'get_recording':
                $this->get_recording_ajax();
                break;
            case 'download_chunk':
                $this->download_chunk_ajax();
                break;
            case 'get_downloads':
                $this->get_downloads_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get recording AJAX
     */
    private function get_recording_ajax() {
        $recording_id = sanitize_text_field($_POST['recording_id'] ?? '');
        
        if (empty($recording_id)) {
            wp_send_json_error('Recording ID is required');
        }
        
        $recording = $this->recording->get_recording($recording_id);
        
        if (!$recording) {
            wp_send_json_error('Recording not found');
        }
        
        wp_send_json_success(array('recording' => $recording));
    }
    
    /**
     * Download chunk AJAX
     */
    private function download_chunk_ajax() {
        $recording_id = sanitize_text_field($_POST['recording_id'] ?? '');
        $chunk = intval($_POST['chunk'] ?? 0);
        
        if (empty($recording_id)) {
            wp_send_json_error('Recording ID is required');
        }
        
        $recording = $this->recording->get_recording($recording_id);
        
        if (!$recording || !$this->can_download($recording)) {
            wp_send_json_error('Download not allowed');
        }
        
        $file_path = $this->get_file_path($recording['file_path']);
        $chunk_size = $this->download_settings['chunk_size'] ?? 1048576;
        $start_pos = $chunk * $chunk_size;
        
        if (!file_exists($file_path)) {
            wp_send_json_error('File not found');
        }
        
        $file_size = filesize($file_path);
        
        if ($start_pos >= $file_size) {
            wp_send_json_error('Invalid chunk');
        }
        
        $handle = fopen($file_path, 'rb');
        fseek($handle, $start_pos);
        $data = fread($handle, $chunk_size);
        fclose($handle);
        
        wp_send_json_success(array(
            'chunk' => $chunk,
            'data' => base64_encode($data),
            'is_last' => ($start_pos + $chunk_size) >= $file_size,
            'total_size' => $file_size
        ));
    }
    
    /**
     * Get downloads AJAX
     */
    private function get_downloads_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $limit = intval($_POST['limit'] ?? 20);
        $offset = intval($_POST['offset'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? 'completed');
        
        $recordings = $this->recording->get_all_recordings($channel, $status, $limit + $offset);
        $recordings = array_slice($recordings, $offset);
        
        wp_send_json_success(array('recordings' => $recordings));
    }
    
    /**
     * Add download settings menu
     */
    public function add_download_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Recording Downloads',
            'Downloads',
            'manage_options',
            'twitch-recording-downloads',
            array($this, 'render_download_settings_page')
        );
    }
    
    /**
     * Render download settings page
     */
    public function render_download_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Recording Download Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_recording_download_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_recording_download_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Downloads</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_recording_download_settings[enabled]" <?php checked($this->download_settings['enabled'], true); ?> />
                            <label>Enable recording downloads</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Public Downloads</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_recording_download_settings[public_downloads]" <?php checked($this->download_settings['public_downloads'], false); ?> />
                            <label>Allow public downloads (no login required)</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Require Login</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_recording_download_settings[require_login]" <?php checked($this->download_settings['require_login'], true); ?> />
                            <label>Require users to be logged in to download</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Allowed Channels</th>
                        <td>
                            <textarea name="spswifter_twitch_recording_download_settings[allowed_channels]" rows="3" class="large-text"><?php echo esc_textarea(implode("\n", $this->download_settings['allowed_channels'] ?? array())); ?></textarea>
                            <p class="description">One channel per line. Leave empty to allow all channels.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Max File Size</th>
                        <td>
                            <input type="number" name="spswifter_twitch_recording_download_settings[max_file_size]" value="<?php echo esc_attr($this->download_settings['max_file_size'] ?? 1073741824); ?>" step="1048576" min="1048576" class="regular-text" />
                            <p class="description">Maximum file size in bytes (default: 1GB)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Chunk Size</th>
                        <td>
                            <input type="number" name="spswifter_twitch_recording_download_settings[chunk_size]" value="<?php echo esc_attr($this->download_settings['chunk_size'] ?? 1048576); ?>" step="1024" min="1024" class="regular-text" />
                            <p class="description">Chunk size for large downloads in bytes (default: 1MB)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Download Expiration</th>
                        <td>
                            <input type="number" name="spswifter_twitch_recording_download_settings[expiration_days]" value="<?php echo esc_attr($this->download_settings['expiration_days'] ?? 30); ?>" min="1" max="365" class="small-text" />
                            <label>days</label>
                            <p class="description">How long download links remain valid</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Rate Limiting</th>
                        <td>
                            <input type="number" name="spswifter_twitch_recording_download_settings[rate_limit]" value="<?php echo esc_attr($this->download_settings['rate_limit'] ?? 5); ?>" min="1" max="100" class="small-text" />
                            <label>downloads per hour per user</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get download settings
     */
    private function get_download_settings() {
        return get_option('spswifter_twitch_recording_download_settings', array(
            'enabled' => false,
            'public_downloads' => false,
            'require_login' => true,
            'allowed_channels' => array(),
            'max_file_size' => 1073741824, // 1GB
            'chunk_size' => 1048576, // 1MB
            'expiration_days' => 30,
            'rate_limit' => 5,
        ));
    }
    
    /**
     * Get download statistics
     */
    public function get_download_statistics($channel = null) {
        $recordings = $this->recording->get_all_recordings($channel, 'completed');
        
        $stats = array(
            'total_recordings' => count($recordings),
            'total_size' => 0,
            'total_downloads' => 0,
            'average_size' => 0,
            'largest_recording' => null,
            'smallest_recording' => null,
        );
        
        foreach ($recordings as $recording) {
            $file_size = $recording['file_size'] ?? 0;
            $stats['total_size'] += $file_size;
            
            if ($stats['largest_recording'] === null || $file_size > ($stats['largest_recording']['file_size'] ?? 0)) {
                $stats['largest_recording'] = $recording;
            }
            
            if ($stats['smallest_recording'] === null || $file_size < ($stats['smallest_recording']['file_size'] ?? PHP_INT_MAX)) {
                $stats['smallest_recording'] = $recording;
            }
        }
        
        if ($stats['total_recordings'] > 0) {
            $stats['average_size'] = $stats['total_size'] / $stats['total_recordings'];
        }
        
        return $stats;
    }
}

// Initialize recording download
new SPSWIFTER_Twitch_Recording_Download();
