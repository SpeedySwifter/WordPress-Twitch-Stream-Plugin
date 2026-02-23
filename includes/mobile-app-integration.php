<?php
/**
 * Mobile App Integration for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Mobile_App_Integration {
    
    private $mobile_settings;
    private $pwa_manifest;
    private $push_settings;
    
    public function __construct() {
        // Delay initialization until WordPress is loaded
        add_action('init', array($this, 'init'));
    }

    public function init() {
        $this->mobile_settings = $this->get_mobile_settings();
        $this->pwa_manifest = $this->get_pwa_manifest();
        $this->push_settings = $this->get_push_settings();
        
        add_action('init', array($this, 'init_mobile_app_integration'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_mobile_app_scripts'));
        add_action('wp_head', array($this, 'add_mobile_meta_tags'));
        add_action('wp_ajax_spswifter_twitch_mobile_app', array($this, 'handle_mobile_app_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_mobile_app', array($this, 'handle_mobile_app_ajax'));
        add_action('admin_menu', array($this, 'add_mobile_app_menu'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_mobile_shortcodes'));
        
        // PWA hooks
        add_action('wp_ajax_spswifter_twitch_pwa_subscribe', array($this, 'handle_pwa_subscription'));
        add_action('wp_ajax_nopriv_spswifter_twitch_pwa_subscribe', array($this, 'handle_pwa_subscription'));
        
        // Mobile detection
        add_filter('spswifter_twitch_mobile_device', array($this, 'detect_mobile_device'));
        add_filter('spswifter_twitch_pwa_manifest', array($this, 'filter_pwa_manifest'));
        
        // Push notification hooks
        add_action('spswifter_twitch_stream_started', array($this, 'send_stream_started_notification'), 10, 2);
        add_action('spswifter_twitch_stream_ended', array($this, 'send_stream_ended_notification'), 10, 2);
        add_action('spswifter_twitch_new_follower', array($this, 'send_follower_notification'), 10, 1);
        
        // Service worker registration
        add_action('wp_footer', array($this, 'register_service_worker'));
    }
    
    /**
     * Initialize mobile app integration
     */
    public function init_mobile_app_integration() {
        $this->create_mobile_tables();
        $this->register_pwa_endpoints();
        $this->setup_mobile_features();
        $this->add_mobile_filters();
    }
    
    /**
     * Create mobile app database tables
     */
    private function create_mobile_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Push subscriptions table
        $push_table = $wpdb->prefix . 'spswifter_twitch_push_subscriptions';
        $push_sql = "CREATE TABLE $push_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            endpoint text NOT NULL,
            p256dh text NOT NULL,
            auth text NOT NULL,
            user_agent text,
            subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_used datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY endpoint (endpoint(255)),
            KEY user_id (user_id),
            KEY active (active)
        ) $charset_collate;";
        
        // Mobile sessions table
        $session_table = $wpdb->prefix . 'spswifter_twitch_mobile_sessions';
        $session_sql = "CREATE TABLE $session_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            device_info text,
            last_activity datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY user_id (user_id),
            KEY last_activity (last_activity)
        ) $charset_collate;";
        
        // Mobile notifications table
        $notification_table = $wpdb->prefix . 'spswifter_twitch_mobile_notifications';
        $notification_sql = "CREATE TABLE $notification_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            title varchar(255) NOT NULL,
            body text,
            icon varchar(255),
            badge varchar(255),
            url varchar(500),
            data text,
            sent_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY sent_at (sent_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($push_sql);
        dbDelta($session_sql);
        dbDelta($notification_sql);
    }
    
    /**
     * Register PWA endpoints
     */
    private function register_pwa_endpoints() {
        add_rewrite_rule('^twitch-pwa-manifest\.json/?', 'index.php?spswifter_twitch_pwa_manifest=1', 'top');
        add_rewrite_rule('^twitch-service-worker\.js/?', 'index.php?spswifter_twitch_service_worker=1', 'top');
        add_rewrite_rule('^twitch-offline\.html/?', 'index.php?spswifter_twitch_offline_page=1', 'top');
        
        add_filter('query_vars', function($vars) {
            $vars[] = 'spswifter_twitch_pwa_manifest';
            $vars[] = 'spswifter_twitch_service_worker';
            $vars[] = 'spswifter_twitch_offline_page';
            return $vars;
        });
        
        add_action('parse_request', array($this, 'handle_pwa_requests'));
    }
    
    /**
     * Handle PWA requests
     */
    public function handle_pwa_requests($wp) {
        if (isset($wp->query_vars['spswifter_twitch_pwa_manifest'])) {
            $this->serve_pwa_manifest();
            exit;
        }
        
        if (isset($wp->query_vars['spswifter_twitch_service_worker'])) {
            $this->serve_service_worker();
            exit;
        }
        
        if (isset($wp->query_vars['spswifter_twitch_offline_page'])) {
            $this->serve_offline_page();
            exit;
        }
    }
    
    /**
     * Setup mobile features
     */
    private function setup_mobile_features() {
        // Add mobile-specific features
        add_theme_support('custom-background');
        add_theme_support('custom-logo');
        
        // Mobile viewport meta tag
        add_action('wp_head', array($this, 'add_viewport_meta'), 1);
        
        // Apple touch icons
        add_action('wp_head', array($this, 'add_apple_touch_icons'));
        
        // Mobile app banner
        add_action('wp_head', array($this, 'add_mobile_app_banner'));
    }
    
    /**
     * Add mobile filters
     */
    private function add_mobile_filters() {
        // Mobile-optimized content filters
        add_filter('the_content', array($this, 'optimize_content_for_mobile'));
        add_filter('wp_nav_menu', array($this, 'optimize_menu_for_mobile'), 10, 2);
        add_filter('post_thumbnail_html', array($this, 'optimize_images_for_mobile'), 10, 5);
        
        // PWA manifest filters
        add_filter('spswifter_twitch_pwa_manifest_data', array($this, 'enhance_pwa_manifest'));
        
        // Mobile detection filters
        add_filter('spswifter_twitch_is_mobile_device', array($this, 'detect_mobile_device'));
        add_filter('spswifter_twitch_mobile_user_agent', array($this, 'get_mobile_user_agent'));
    }
    
    /**
     * Register mobile shortcodes
     */
    public function register_mobile_shortcodes() {
        add_shortcode('spswifter_twitch_mobile_app', array($this, 'render_mobile_app_shortcode'));
        add_shortcode('spswifter_twitch_pwa_install', array($this, 'render_pwa_install_shortcode'));
        add_shortcode('spswifter_twitch_mobile_menu', array($this, 'render_mobile_menu_shortcode'));
        add_shortcode('spswifter_twitch_mobile_streams', array($this, 'render_mobile_streams_shortcode'));
        add_shortcode('spswifter_twitch_push_notifications', array($this, 'render_push_notifications_shortcode'));
    }
    
    /**
     * Render mobile app shortcode
     */
    public function render_mobile_app_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'theme' => 'auto',
            'show_install' => 'true',
            'show_notifications' => 'true',
            'fullscreen' => 'false',
            'orientation' => 'any',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-mobile-app" 
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-show-install="<?php echo esc_attr($atts['show_install']); ?>"
             data-show-notifications="<?php echo esc_attr($atts['show_notifications']); ?>"
             data-fullscreen="<?php echo esc_attr($atts['fullscreen']); ?>"
             data-orientation="<?php echo esc_attr($atts['orientation']); ?>">
            
            <div class="twitch-mobile-header">
                <div class="twitch-mobile-nav">
                    <button class="twitch-mobile-menu-toggle">
                        <span class="twitch-menu-icon">☰</span>
                    </button>
                    <div class="twitch-mobile-title">
                        <span class="twitch-app-icon">📱</span>
                        Twitch Mobile
                    </div>
                </div>
                
                <div class="twitch-mobile-actions">
                    <button class="twitch-notification-toggle">
                        <span class="twitch-bell-icon">🔔</span>
                        <span class="twitch-notification-count">0</span>
                    </button>
                    <button class="twitch-pwa-install-btn" style="display: none;">
                        <span class="twitch-install-icon">⬇️</span>
                        Install App
                    </button>
                </div>
            </div>
            
            <div class="twitch-mobile-content">
                <div class="twitch-mobile-sidebar">
                    <nav class="twitch-mobile-nav-menu">
                        <a href="#streams" class="twitch-nav-item active">
                            <span class="twitch-nav-icon">🎥</span>
                            <span class="twitch-nav-text">Streams</span>
                        </a>
                        <a href="#schedule" class="twitch-nav-item">
                            <span class="twitch-nav-icon">📅</span>
                            <span class="twitch-nav-text">Schedule</span>
                        </a>
                        <a href="#chat" class="twitch-nav-item">
                            <span class="twitch-nav-icon">💬</span>
                            <span class="twitch-nav-text">Chat</span>
                        </a>
                        <a href="#profile" class="twitch-nav-item">
                            <span class="twitch-nav-icon">👤</span>
                            <span class="twitch-nav-text">Profile</span>
                        </a>
                        <a href="#settings" class="twitch-nav-item">
                            <span class="twitch-nav-icon">⚙️</span>
                            <span class="twitch-nav-text">Settings</span>
                        </a>
                    </nav>
                </div>
                
                <div class="twitch-mobile-main">
                    <div class="twitch-mobile-section active" id="streams-section">
                        <?php echo do_shortcode('[spswifter_twitch_mobile_streams]'); ?>
                    </div>
                    
                    <div class="twitch-mobile-section" id="schedule-section">
                        <?php echo do_shortcode('[spswifter_twitch_stream_scheduler view="list" theme="dark"]'); ?>
                    </div>
                    
                    <div class="twitch-mobile-section" id="chat-section">
                        <?php echo do_shortcode('[spswifter_twitch_chat channel="yourchannel" theme="dark" height="400"]'); ?>
                    </div>
                    
                    <div class="twitch-mobile-section" id="profile-section">
                        <div class="twitch-mobile-profile">
                            <div class="twitch-profile-header">
                                <div class="twitch-profile-avatar">
                                    <?php echo get_avatar(get_current_user_id(), 64); ?>
                                </div>
                                <div class="twitch-profile-info">
                                    <h3><?php echo wp_get_current_user()->display_name; ?></h3>
                                    <p>Level: <span class="twitch-user-level">Viewer</span></p>
                                </div>
                            </div>
                            <div class="twitch-profile-stats">
                                <div class="twitch-stat-item">
                                    <span class="twitch-stat-number">0</span>
                                    <span class="twitch-stat-label">Streams Watched</span>
                                </div>
                                <div class="twitch-stat-item">
                                    <span class="twitch-stat-number">0</span>
                                    <span class="twitch-stat-label">Hours Watched</span>
                                </div>
                                <div class="twitch-stat-item">
                                    <span class="twitch-stat-number">0</span>
                                    <span class="twitch-stat-label">Following</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="twitch-mobile-section" id="settings-section">
                        <div class="twitch-mobile-settings">
                            <div class="twitch-setting-group">
                                <h4>Notifications</h4>
                                <label class="twitch-setting-item">
                                    <input type="checkbox" class="twitch-push-enabled" checked>
                                    <span>Push Notifications</span>
                                </label>
                                <label class="twitch-setting-item">
                                    <input type="checkbox" class="twitch-stream-notifications" checked>
                                    <span>Stream Start Notifications</span>
                                </label>
                                <label class="twitch-setting-item">
                                    <input type="checkbox" class="twitch-follower-notifications">
                                    <span>Follower Notifications</span>
                                </label>
                            </div>
                            
                            <div class="twitch-setting-group">
                                <h4>Appearance</h4>
                                <label class="twitch-setting-item">
                                    <input type="radio" name="theme" value="light" class="twitch-theme-light">
                                    <span>Light Theme</span>
                                </label>
                                <label class="twitch-setting-item">
                                    <input type="radio" name="theme" value="dark" class="twitch-theme-dark" checked>
                                    <span>Dark Theme</span>
                                </label>
                                <label class="twitch-setting-item">
                                    <input type="radio" name="theme" value="auto" class="twitch-theme-auto">
                                    <span>Auto Theme</span>
                                </label>
                            </div>
                            
                            <div class="twitch-setting-group">
                                <h4>Quality</h4>
                                <label class="twitch-setting-item">
                                    <input type="radio" name="quality" value="auto" class="twitch-quality-auto" checked>
                                    <span>Auto Quality</span>
                                </label>
                                <label class="twitch-setting-item">
                                    <input type="radio" name="quality" value="high" class="twitch-quality-high">
                                    <span>High Quality</span>
                                </label>
                                <label class="twitch-setting-item">
                                    <input type="radio" name="quality" value="low" class="twitch-quality-low">
                                    <span>Low Quality (Data Saver)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="twitch-mobile-footer">
                <div class="twitch-mobile-tabs">
                    <button class="twitch-tab-btn active" data-section="streams">
                        <span class="twitch-tab-icon">🎥</span>
                        <span class="twitch-tab-text">Streams</span>
                    </button>
                    <button class="twitch-tab-btn" data-section="schedule">
                        <span class="twitch-tab-icon">📅</span>
                        <span class="twitch-tab-text">Schedule</span>
                    </button>
                    <button class="twitch-tab-btn" data-section="chat">
                        <span class="twitch-tab-icon">💬</span>
                        <span class="twitch-tab-text">Chat</span>
                    </button>
                    <button class="twitch-tab-btn" data-section="profile">
                        <span class="twitch-tab-icon">👤</span>
                        <span class="twitch-tab-text">Profile</span>
                    </button>
                </div>
            </div>
            
            <!-- PWA Install Prompt -->
            <div class="twitch-pwa-install-prompt" style="display: none;">
                <div class="twitch-install-content">
                    <div class="twitch-install-icon">📱</div>
                    <div class="twitch-install-text">
                        <h3>Install Twitch Mobile App</h3>
                        <p>Get the full mobile experience with offline access and push notifications.</p>
                    </div>
                    <div class="twitch-install-actions">
                        <button class="twitch-install-accept">Install</button>
                        <button class="twitch-install-dismiss">Later</button>
                    </div>
                </div>
            </div>
            
            <!-- Notification Panel -->
            <div class="twitch-notification-panel">
                <div class="twitch-notification-header">
                    <h4>Notifications</h4>
                    <button class="twitch-notification-close">&times;</button>
                </div>
                <div class="twitch-notification-list">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render PWA install shortcode
     */
    public function render_pwa_install_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'text' => 'Install App',
            'icon' => '⬇️',
            'theme' => 'primary',
            'size' => 'medium',
        ), $atts);
        
        ob_start();
        ?>
        <button class="twitch-pwa-install-btn twitch-theme-<?php echo esc_attr($atts['theme']); ?> twitch-size-<?php echo esc_attr($atts['size']); ?>">
            <span class="twitch-install-icon"><?php echo esc_html($atts['icon']); ?></span>
            <span class="twitch-install-text"><?php echo esc_html($atts['text']); ?></span>
        </button>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render mobile menu shortcode
     */
    public function render_mobile_menu_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'position' => 'left',
            'theme' => 'dark',
            'animation' => 'slide',
        ), $atts);
        
        ob_start();
        ?>
        <nav class="twitch-mobile-menu twitch-position-<?php echo esc_attr($atts['position']); ?> twitch-theme-<?php echo esc_attr($atts['theme']); ?> twitch-animation-<?php echo esc_attr($atts['animation']); ?>">
            <div class="twitch-menu-header">
                <div class="twitch-menu-logo">
                    <span class="twitch-logo-icon">📺</span>
                    <span class="twitch-logo-text">Twitch</span>
                </div>
                <button class="twitch-menu-close">&times;</button>
            </div>
            
            <ul class="twitch-menu-items">
                <li class="twitch-menu-item">
                    <a href="#streams">
                        <span class="twitch-menu-icon">🎥</span>
                        <span class="twitch-menu-text">Live Streams</span>
                    </a>
                </li>
                <li class="twitch-menu-item">
                    <a href="#schedule">
                        <span class="twitch-menu-icon">📅</span>
                        <span class="twitch-menu-text">Stream Schedule</span>
                    </a>
                </li>
                <li class="twitch-menu-item">
                    <a href="#clips">
                        <span class="twitch-menu-icon">🎬</span>
                        <span class="twitch-menu-text">Clips</span>
                    </a>
                </li>
                <li class="twitch-menu-item">
                    <a href="#following">
                        <span class="twitch-menu-icon">❤️</span>
                        <span class="twitch-menu-text">Following</span>
                    </a>
                </li>
                <li class="twitch-menu-item">
                    <a href="#profile">
                        <span class="twitch-menu-icon">👤</span>
                        <span class="twitch-menu-text">Profile</span>
                    </a>
                </li>
                <li class="twitch-menu-item">
                    <a href="#settings">
                        <span class="twitch-menu-icon">⚙️</span>
                        <span class="twitch-menu-text">Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="twitch-menu-footer">
                <div class="twitch-user-status">
                    <div class="twitch-user-avatar">
                        <?php echo get_avatar(get_current_user_id(), 32); ?>
                    </div>
                    <div class="twitch-user-info">
                        <span class="twitch-username"><?php echo wp_get_current_user()->display_name; ?></span>
                        <span class="twitch-user-role">Viewer</span>
                    </div>
                </div>
            </div>
        </nav>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render mobile streams shortcode
     */
    public function render_mobile_streams_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'channel' => '',
            'limit' => 10,
            'show_offline' => 'true',
            'autoplay' => 'false',
            'quality' => 'auto',
        ), $atts);
        
        $streams = $this->get_mobile_streams($atts['channel'], $atts['limit']);
        
        ob_start();
        ?>
        <div class="twitch-mobile-streams" 
             data-autoplay="<?php echo esc_attr($atts['autoplay']); ?>"
             data-quality="<?php echo esc_attr($atts['quality']); ?>">
            
            <?php if (empty($streams)): ?>
                <div class="twitch-no-streams">
                    <div class="twitch-no-streams-icon">📺</div>
                    <h3>No Live Streams</h3>
                    <p>Check back later for live content.</p>
                </div>
            <?php else: ?>
                <div class="twitch-streams-grid">
                    <?php foreach ($streams as $stream): ?>
                        <div class="twitch-mobile-stream-card" data-stream-id="<?php echo esc_attr($stream->id); ?>">
                            <div class="twitch-stream-thumbnail">
                                <img src="<?php echo esc_url($stream->thumbnail_url); ?>" 
                                     alt="<?php echo esc_attr($stream->title); ?>"
                                     loading="lazy">
                                <div class="twitch-stream-overlay">
                                    <div class="twitch-stream-status">
                                        <span class="twitch-live-badge">LIVE</span>
                                        <span class="twitch-viewer-count">
                                            <span class="twitch-viewer-icon">👁️</span>
                                            <?php echo number_format($stream->viewer_count); ?>
                                        </span>
                                    </div>
                                    <div class="twitch-stream-actions">
                                        <button class="twitch-watch-btn">Watch</button>
                                        <button class="twitch-follow-btn" data-channel="<?php echo esc_attr($stream->channel); ?>">
                                            <span class="twitch-follow-icon">❤️</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="twitch-stream-info">
                                <h4 class="twitch-stream-title"><?php echo esc_html($stream->title); ?></h4>
                                <p class="twitch-stream-channel"><?php echo esc_html($stream->channel); ?></p>
                                <div class="twitch-stream-meta">
                                    <span class="twitch-game-name"><?php echo esc_html($stream->game_name); ?></span>
                                    <span class="twitch-stream-duration"><?php echo esc_html($this->format_stream_duration($stream->started_at)); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_offline'] === 'true'): ?>
                <div class="twitch-offline-streams">
                    <h4>Offline Channels</h4>
                    <div class="twitch-offline-grid">
                        <?php
                        $offline_channels = $this->get_offline_channels($atts['channel'], 6);
                        foreach ($offline_channels as $channel):
                        ?>
                            <div class="twitch-offline-channel">
                                <div class="twitch-channel-avatar">
                                    <img src="<?php echo esc_url($channel->logo); ?>" alt="<?php echo esc_attr($channel->display_name); ?>">
                                </div>
                                <div class="twitch-channel-info">
                                    <h5><?php echo esc_html($channel->display_name); ?></h5>
                                    <p>Last seen: <?php echo esc_html($this->format_last_seen($channel->updated_at)); ?></p>
                                </div>
                                <button class="twitch-notify-btn" data-channel="<?php echo esc_attr($channel->name); ?>">
                                    <span class="twitch-notify-icon">🔔</span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render push notifications shortcode
     */
    public function render_push_notifications_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'show_settings' => 'true',
            'show_history' => 'false',
            'limit' => 10,
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-push-notifications">
            <?php if ($atts['show_settings'] === 'true'): ?>
                <div class="twitch-notification-settings">
                    <h4>Notification Settings</h4>
                    <div class="twitch-notification-options">
                        <label class="twitch-notification-option">
                            <input type="checkbox" class="twitch-push-enabled" checked>
                            <span>Enable Push Notifications</span>
                        </label>
                        <label class="twitch-notification-option">
                            <input type="checkbox" class="twitch-stream-notifications" checked>
                            <span>Stream Start Alerts</span>
                        </label>
                        <label class="twitch-notification-option">
                            <input type="checkbox" class="twitch-follower-notifications">
                            <span>Follower Notifications</span>
                        </label>
                        <label class="twitch-notification-option">
                            <input type="checkbox" class="twitch-reminder-notifications" checked>
                            <span>Stream Reminders</span>
                        </label>
                    </div>
                    
                    <div class="twitch-notification-test">
                        <button class="twitch-test-notification">
                            <span class="twitch-test-icon">🔔</span>
                            Test Notification
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_history'] === 'true'): ?>
                <div class="twitch-notification-history">
                    <h4>Recent Notifications</h4>
                    <div class="twitch-notification-list">
                        <!-- Notification history will be loaded here -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle mobile app AJAX
     */
    public function handle_mobile_app_ajax() {
        check_ajax_referer('spswifter_twitch_mobile_app_nonce', 'nonce');
        
        $action = $_POST['mobile_action'] ?? '';
        
        switch ($action) {
            case 'get_streams':
                $this->get_mobile_streams_ajax();
                break;
            case 'update_settings':
                $this->update_mobile_settings_ajax();
                break;
            case 'send_test_notification':
                $this->send_test_notification_ajax();
                break;
            case 'get_notifications':
                $this->get_notifications_ajax();
                break;
            case 'mark_notification_read':
                $this->mark_notification_read_ajax();
                break;
            case 'get_user_stats':
                $this->get_user_stats_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Handle PWA subscription
     */
    public function handle_pwa_subscription() {
        $raw_body = file_get_contents('php://input');
        if (empty($raw_body)) {
            wp_send_json_error('Empty request body', 400);
            return;
        }

        $subscription = json_decode(wp_unslash($raw_body), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON', 400);
            return;
        }
        
        $user_id = get_current_user_id();
        $result  = $this->save_push_subscription($user_id, $subscription);
        
        if ($result) {
            wp_send_json_success('Subscription saved');
        } else {
            wp_send_json_error('Failed to save subscription');
        }
    }
    
    /**
     * Serve PWA manifest
     */
    public function serve_pwa_manifest() {
        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=3600');
        
        $manifest = $this->pwa_manifest;
        $manifest['start_url'] = home_url('/');
        $manifest['scope'] = home_url('/');
        
        echo json_encode($manifest);
    }
    
    /**
     * Serve service worker
     */
    public function serve_service_worker() {
        header('Content-Type: application/javascript');
        header('Cache-Control: public, max-age=3600');
        header('Service-Worker-Allowed: /');
        
        $sw_content = $this->get_service_worker_content();
        echo $sw_content;
    }
    
    /**
     * Serve offline page
     */
    public function serve_offline_page() {
        header('Content-Type: text/html');
        header('Cache-Control: public, max-age=3600');
        
        $offline_content = $this->get_offline_page_content();
        echo $offline_content;
    }
    
    /**
     * Add viewport meta tag
     */
    public function add_viewport_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">';
    }
    
    /**
     * Add Apple touch icons
     */
    public function add_apple_touch_icons() {
        $icon_url = $this->mobile_settings['app_icon'] ?? SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/images/app-icon.png';
        
        echo '<link rel="apple-touch-icon" href="' . esc_url($icon_url) . '">';
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($icon_url) . '">';
        echo '<meta name="apple-mobile-web-app-capable" content="yes">';
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">';
        echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr($this->pwa_manifest['name']) . '">';
    }
    
    /**
     * Add mobile app banner
     */
    public function add_mobile_app_banner() {
        if (!$this->mobile_settings['smart_app_banner']) return;
        
        echo '<meta name="apple-itunes-app" content="app-id=' . esc_attr($this->mobile_settings['ios_app_id']) . ', app-argument=' . esc_url(home_url()) . '">';
    }
    
    /**
     * Add mobile meta tags
     */
    public function add_mobile_meta_tags() {
        // Theme color
        echo '<meta name="theme-color" content="' . esc_attr($this->mobile_settings['theme_color'] ?? '#9146ff') . '">';
        echo '<meta name="msapplication-TileColor" content="' . esc_attr($this->mobile_settings['theme_color'] ?? '#9146ff') . '">';
        
        // PWA manifest
        echo '<link rel="manifest" href="' . esc_url(home_url('/twitch-pwa-manifest.json')) . '">';
        
        // Canonical URL
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        echo '<link rel="canonical" href="' . esc_url(home_url(sanitize_url($request_uri))) . '">';
    }
    
    /**
     * Register service worker
     */
    public function register_service_worker() {
        if (!$this->is_mobile_device()) return;
        
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo esc_url(home_url('/twitch-service-worker.js')); ?>')
                    .then(function(registration) {
                        console.log('Service Worker registered:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
        </script>
        <?php
    }
    
    /**
     * Get service worker content
     */
    private function get_service_worker_content() {
        $cache_name = 'twitch-mobile-v' . SPSWIFTER_TWITCH_VERSION;
        
        return "
        const CACHE_NAME = '{$cache_name}';
        const urlsToCache = [
            '/',
            '" . trailingslashit(SPSWIFTER_TWITCH_PLUGIN_URL) . "assets/css/mobile-app.css',
            '" . trailingslashit(SPSWIFTER_TWITCH_PLUGIN_URL) . "assets/js/mobile-app.js',
            '" . home_url('/twitch-offline.html') . "'
        ];

        self.addEventListener('install', function(event) {
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then(function(cache) {
                        return cache.addAll(urlsToCache);
                    })
            );
        });

        self.addEventListener('fetch', function(event) {
            event.respondWith(
                caches.match(event.request)
                    .then(function(response) {
                        if (response) {
                            return response;
                        }
                        return fetch(event.request);
                    })
                    .catch(function() {
                        return caches.match('" . home_url('/twitch-offline.html') . "');
                    })
            );
        });

        self.addEventListener('activate', function(event) {
            event.waitUntil(
                caches.keys().then(function(cacheNames) {
                    return Promise.all(
                        cacheNames.map(function(cacheName) {
                            if (cacheName !== CACHE_NAME) {
                                return caches.delete(cacheName);
                            }
                        })
                    );
                })
            );
        });

        self.addEventListener('push', function(event) {
            if (!event.data) return;
            
            const data = event.data.json();
            
            const options = {
                body: data.body,
                icon: data.icon || '" . trailingslashit(SPSWIFTER_TWITCH_PLUGIN_URL) . "assets/images/notification-icon.png',
                badge: data.badge || '" . trailingslashit(SPSWIFTER_TWITCH_PLUGIN_URL) . "assets/images/badge-icon.png',
                vibrate: [200, 100, 200],
                data: data.data || {},
                actions: [
                    {
                        action: 'view',
                        title: 'View'
                    },
                    {
                        action: 'dismiss',
                        title: 'Dismiss'
                    }
                ]
            };
            
            event.waitUntil(
                self.registration.showNotification(data.title, options)
            );
        });

        self.addEventListener('notificationclick', function(event) {
            event.notification.close();
            
            if (event.action === 'view') {
                const url = event.notification.data.url || '/';
                event.waitUntil(
                    clients.openWindow(url)
                );
            }
        });
        ";
    }
    
    /**
     * Get offline page content
     */
    private function get_offline_page_content() {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>You are offline - Twitch Mobile</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    text-align: center;
                    padding: 50px 20px;
                    background: linear-gradient(135deg, #9146ff 0%, #7928ca 100%);
                    color: white;
                    min-height: 100vh;
                    margin: 0;
                }
                .offline-content {
                    max-width: 400px;
                    margin: 0 auto;
                }
                .offline-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                h1 {
                    margin-bottom: 16px;
                    font-size: 24px;
                }
                p {
                    margin-bottom: 30px;
                    opacity: 0.9;
                }
                .retry-btn {
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    padding: 12px 24px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: all 0.3s ease;
                }
                .retry-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
            </style>
        </head>
        <body>
            <div class="offline-content">
                <div class="offline-icon">📱</div>
                <h1>You are offline</h1>
                <p>Please check your internet connection and try again.</p>
                <button class="retry-btn" onclick="window.location.reload()">Try Again</button>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Add mobile app menu
     */
    public function add_mobile_app_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Mobile App',
            'Mobile App',
            'manage_options',
            'twitch-mobile-app',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Mobile App Integration</h1>
            
            <div class="twitch-mobile-notice">
                <p>Configure your mobile app integration, PWA settings, and push notifications. Enable the mobile experience for your users.</p>
            </div>
            
            <div class="twitch-mobile-admin">
                <form method="post" action="options.php">
                    <?php settings_fields('spswifter_twitch_mobile_settings'); ?>
                    <?php do_settings_sections('spswifter_twitch_mobile_settings'); ?>
                    
                    <div class="twitch-admin-tabs">
                        <div class="twitch-tab-buttons">
                            <button type="button" class="twitch-admin-tab active" data-tab="general">General</button>
                            <button type="button" class="twitch-admin-tab" data-tab="pwa">PWA</button>
                            <button type="button" class="twitch-admin-tab" data-tab="notifications">Notifications</button>
                            <button type="button" class="twitch-admin-tab" data-tab="analytics">Analytics</button>
                        </div>
                        
                        <div class="twitch-tab-content">
                            <!-- General Settings -->
                            <div class="twitch-admin-tab-panel active" id="general-tab">
                                <h2>Mobile App General Settings</h2>
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Enable Mobile App</th>
                                        <td>
                                            <input type="checkbox" name="spswifter_twitch_mobile_settings[enabled]" 
                                                   <?php checked($this->mobile_settings['enabled'], true); ?> />
                                            <label>Enable mobile app features and PWA</label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">App Name</th>
                                        <td>
                                            <input type="text" name="spswifter_twitch_mobile_settings[app_name]" 
                                                   value="<?php echo esc_attr($this->mobile_settings['app_name'] ?? 'Twitch Mobile'); ?>" 
                                                   class="regular-text" />
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">App Icon</th>
                                        <td>
                                            <input type="url" name="spswifter_twitch_mobile_settings[app_icon]" 
                                                   value="<?php echo esc_attr($this->mobile_settings['app_icon'] ?? ''); ?>" 
                                                   class="regular-text" />
                                            <p class="description">URL to your app icon (512x512 recommended)</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Theme Color</th>
                                        <td>
                                            <input type="color" name="spswifter_twitch_mobile_settings[theme_color]" 
                                                   value="<?php echo esc_attr($this->mobile_settings['theme_color'] ?? '#9146ff'); ?>" />
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Smart App Banner</th>
                                        <td>
                                            <input type="checkbox" name="spswifter_twitch_mobile_settings[smart_app_banner]" 
                                                   <?php checked($this->mobile_settings['smart_app_banner'], true); ?> />
                                            <label>Show iOS smart app banner</label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">iOS App ID</th>
                                        <td>
                                            <input type="text" name="spswifter_twitch_mobile_settings[ios_app_id]" 
                                                   value="<?php echo esc_attr($this->mobile_settings['ios_app_id'] ?? ''); ?>" 
                                                   class="regular-text" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- PWA Settings -->
                            <div class="twitch-admin-tab-panel" id="pwa-tab">
                                <h2>Progressive Web App Settings</h2>
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Enable PWA</th>
                                        <td>
                                            <input type="checkbox" name="spswifter_twitch_mobile_settings[enable_pwa]" 
                                                   <?php checked($this->mobile_settings['enable_pwa'], true); ?> />
                                            <label>Enable Progressive Web App features</label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Display Mode</th>
                                        <td>
                                            <select name="spswifter_twitch_mobile_settings[display_mode]">
                                                <option value="standalone" <?php selected($this->mobile_settings['display_mode'], 'standalone'); ?>>Standalone</option>
                                                <option value="fullscreen" <?php selected($this->mobile_settings['display_mode'], 'fullscreen'); ?>>Fullscreen</option>
                                                <option value="minimal-ui" <?php selected($this->mobile_settings['display_mode'], 'minimal-ui'); ?>>Minimal UI</option>
                                                <option value="browser" <?php selected($this->mobile_settings['display_mode'], 'browser'); ?>>Browser</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Orientation</th>
                                        <td>
                                            <select name="spswifter_twitch_mobile_settings[orientation]">
                                                <option value="any" <?php selected($this->mobile_settings['orientation'], 'any'); ?>>Any</option>
                                                <option value="portrait" <?php selected($this->mobile_settings['orientation'], 'portrait'); ?>>Portrait</option>
                                                <option value="landscape" <?php selected($this->mobile_settings['orientation'], 'landscape'); ?>>Landscape</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Cache Strategy</th>
                                        <td>
                                            <select name="spswifter_twitch_mobile_settings[cache_strategy]">
                                                <option value="network-first" <?php selected($this->mobile_settings['cache_strategy'], 'network-first'); ?>>Network First</option>
                                                <option value="cache-first" <?php selected($this->mobile_settings['cache_strategy'], 'cache-first'); ?>>Cache First</option>
                                                <option value="stale-while-revalidate" <?php selected($this->mobile_settings['cache_strategy'], 'stale-while-revalidate'); ?>>Stale While Revalidate</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Notification Settings -->
                            <div class="twitch-admin-tab-panel" id="notifications-tab">
                                <h2>Push Notification Settings</h2>
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Enable Push Notifications</th>
                                        <td>
                                            <input type="checkbox" name="spswifter_twitch_mobile_settings[push_enabled]" 
                                                   <?php checked($this->mobile_settings['push_enabled'], true); ?> />
                                            <label>Enable push notifications</label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">VAPID Keys</th>
                                        <td>
                                            <p>Configure your VAPID keys for push notifications:</p>
                                            <input type="text" name="spswifter_twitch_mobile_settings[vapid_public]" 
                                                   value="<?php echo esc_attr($this->mobile_settings['vapid_public'] ?? ''); ?>" 
                                                   class="regular-text" placeholder="Public Key" />
                                            <br><br>
                                            <input type="text" name="spswifter_twitch_mobile_settings[vapid_private]" 
                                                   value="<?php echo esc_attr($this->mobile_settings['vapid_private'] ?? ''); ?>" 
                                                   class="regular-text" placeholder="Private Key" />
                                            <p class="description">Generate VAPID keys for secure push notifications</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Stream Start Notifications</th>
                                        <td>
                                            <input type="checkbox" name="spswifter_twitch_mobile_settings[notify_stream_start]" 
                                                   <?php checked($this->mobile_settings['notify_stream_start'], true); ?> />
                                            <label>Send notifications when streams start</label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">Follower Notifications</th>
                                        <td>
                                            <input type="checkbox" name="spswifter_twitch_mobile_settings[notify_followers]" 
                                                   <?php checked($this->mobile_settings['notify_followers'], false); ?> />
                                            <label>Send notifications for new followers</label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Analytics -->
                            <div class="twitch-admin-tab-panel" id="analytics-tab">
                                <h2>Mobile Analytics</h2>
                                
                                <div class="twitch-mobile-stats">
                                    <div class="twitch-stat-card">
                                        <h3>PWA Installs</h3>
                                        <div class="twitch-stat-number" id="pwa-installs">0</div>
                                    </div>
                                    
                                    <div class="twitch-stat-card">
                                        <h3>Push Subscribers</h3>
                                        <div class="twitch-stat-number" id="push-subscribers">0</div>
                                    </div>
                                    
                                    <div class="twitch-stat-card">
                                        <h3>Mobile Sessions</h3>
                                        <div class="twitch-stat-number" id="mobile-sessions">0</div>
                                    </div>
                                    
                                    <div class="twitch-stat-card">
                                        <h3>Notifications Sent</h3>
                                        <div class="twitch-stat-number" id="notifications-sent">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue mobile app scripts
     */
    public function enqueue_mobile_app_scripts() {
        wp_enqueue_style(
            'spswifter-twitch-mobile-app',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/mobile-app.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'spswifter-twitch-mobile-app',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/mobile-app.js',
            array('jquery', 'wp-util'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('spswifter-twitch-mobile-app', 'twitchMobileApp', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_mobile_app_nonce'),
            'pwaEnabled' => $this->mobile_settings['enable_pwa'] ?? true,
            'pushEnabled' => $this->mobile_settings['push_enabled'] ?? true,
            'isMobile' => $this->is_mobile_device(),
            'userId' => get_current_user_id(),
            'settings' => $this->mobile_settings,
            'strings' => array(
                'installApp' => 'Install App',
                'appInstalled' => 'App Installed!',
                'notificationEnabled' => 'Notifications enabled',
                'notificationDisabled' => 'Notifications disabled',
                'offlineMessage' => 'You are currently offline',
                'retry' => 'Retry',
                'dismiss' => 'Dismiss'
            )
        ));
    }
    
    /**
     * Helper methods
     */
    private function get_mobile_settings() {
        return get_option('spswifter_twitch_mobile_settings', array(
            'enabled' => true,
            'app_name' => 'Twitch Mobile',
            'app_icon' => '',
            'theme_color' => '#9146ff',
            'enable_pwa' => true,
            'display_mode' => 'standalone',
            'orientation' => 'any',
            'cache_strategy' => 'network-first',
            'push_enabled' => true,
            'vapid_public' => '',
            'vapid_private' => '',
            'notify_stream_start' => true,
            'notify_followers' => false,
            'smart_app_banner' => false,
            'ios_app_id' => ''
        ));
    }
    
    private function get_pwa_manifest() {
        return array(
            'name' => $this->mobile_settings['app_name'] ?? 'Twitch Mobile',
            'short_name' => 'Twitch',
            'description' => 'Watch Twitch streams on mobile',
            'start_url' => '/',
            'display' => $this->mobile_settings['display_mode'] ?? 'standalone',
            'orientation' => $this->mobile_settings['orientation'] ?? 'any',
            'theme_color' => $this->mobile_settings['theme_color'] ?? '#9146ff',
            'background_color' => '#ffffff',
            'icons' => array(
                array(
                    'src' => $this->mobile_settings['app_icon'] ?: SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/images/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->mobile_settings['app_icon'] ?: SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/images/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                )
            ),
            'categories' => array('entertainment', 'social'),
            'lang' => get_locale(),
            'dir' => is_rtl() ? 'rtl' : 'ltr'
        );
    }
    
    private function get_push_settings() {
        return array(
            'enabled' => $this->mobile_settings['push_enabled'] ?? true,
            'vapid_public' => $this->mobile_settings['vapid_public'] ?? '',
            'vapid_private' => $this->mobile_settings['vapid_private'] ?? '',
            'server_key' => $this->mobile_settings['server_key'] ?? ''
        );
    }
    
    private function is_mobile_device() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        return preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $user_agent);
    }
    
    private function detect_mobile_device() {
        return $this->is_mobile_device();
    }
    
    private function get_mobile_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    }
    
    private function optimize_content_for_mobile($content) {
        if (!$this->is_mobile_device()) return $content;
        
        // Add mobile-specific classes and optimizations
        return $content;
    }
    
    private function optimize_menu_for_mobile($nav_menu, $args) {
        if (!$this->is_mobile_device()) return $nav_menu;
        
        // Optimize menu for mobile
        return $nav_menu;
    }
    
    private function optimize_images_for_mobile($html, $post_id, $post_thumbnail_id, $size, $attr) {
        if (!$this->is_mobile_device()) return $html;
        
        // Add responsive image attributes
        return $html;
    }
    
    private function filter_pwa_manifest($manifest) {
        return array_merge($this->pwa_manifest, $manifest);
    }
    
    private function enhance_pwa_manifest($data) {
        return array_merge($this->pwa_manifest, $data);
    }
    
    private function get_mobile_streams($channel = '', $limit = 10) {
        // Get live streams optimized for mobile
        return array(); // Implementation would fetch from Twitch API
    }
    
    private function get_offline_channels($channel = '', $limit = 6) {
        // Get offline channels the user follows
        return array();
    }
    
    private function format_stream_duration($started_at) {
        $start = strtotime($started_at);
        $now = time();
        $diff = $now - $start;
        
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        } else {
            return $minutes . 'm';
        }
    }
    
    private function format_last_seen($updated_at) {
        $updated = strtotime($updated_at);
        $now = time();
        $diff = $now - $updated;
        
        if ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } else {
            return floor($diff / 86400) . ' days ago';
        }
    }
    
    private function save_push_subscription($user_id, $subscription) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'spswifter_twitch_push_subscriptions';
        
        $data = array(
            'user_id' => $user_id,
            'endpoint' => $subscription['endpoint'],
            'p256dh' => $subscription['keys']['p256dh'],
            'auth' => $subscription['keys']['auth'],
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
            'active' => 1
        );
        
        return $wpdb->replace($table_name, $data);
    }
    
    private function send_stream_started_notification($channel, $stream_data) {
        if (!$this->mobile_settings['notify_stream_start']) return;
        
        $this->send_push_notification(
            'Stream Started!',
            $channel . ' is now live: ' . $stream_data['title'],
            array('url' => home_url('/stream/' . $channel))
        );
    }
    
    private function send_stream_ended_notification($channel, $stream_data) {
        // Optional: send notification when stream ends
    }
    
    private function send_follower_notification($channel) {
        if (!$this->mobile_settings['notify_followers']) return;
        
        $this->send_push_notification(
            'New Follower!',
            'Someone followed ' . $channel,
            array('url' => home_url('/channel/' . $channel))
        );
    }
    
    private function send_push_notification($title, $body, $data = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'spswifter_twitch_push_subscriptions';
        
        $subscriptions = $wpdb->get_results("SELECT * FROM $table_name WHERE active = 1");
        
        foreach ($subscriptions as $sub) {
            $this->send_web_push($sub, $title, $body, $data);
        }
    }
    
    private function send_web_push($subscription, $title, $body, $data) {
        // Implementation would use Web Push library
        // This is a placeholder for the actual implementation
        return true;
    }
}

// Initialize mobile app integration
new SPSWIFTER_Twitch_Mobile_App_Integration();
