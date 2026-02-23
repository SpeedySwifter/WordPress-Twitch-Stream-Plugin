<?php
/**
 * Multi-Channel Dashboard for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Dashboard {
    
    private $api;
    private $analytics;
    
    public function __construct() {
        $this->api = new SPSWIFTER_Twitch_API();
        $this->analytics = new SPSWIFTER_Twitch_Analytics();
        
        add_action('admin_menu', array($this, 'add_dashboard_menu'));
        add_action('wp_ajax_spswifter_twitch_dashboard_data', array($this, 'handle_dashboard_data'));
        add_action('wp_ajax_nopriv_spswifter_twitch_dashboard_data', array($this, 'handle_dashboard_data'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_scripts'));
    }
    
    /**
     * Add dashboard menu
     */
    public function add_dashboard_menu() {
        add_menu_page(
            'Twitch Dashboard',
            'Twitch Dashboard',
            'manage_options',
            'twitch-dashboard',
            array($this, 'render_dashboard_page'),
            'dashicons-video-alt',
            30
        );
        
        add_submenu_page(
            'twitch-dashboard',
            'Overview',
            'Overview',
            'manage_options',
            'twitch-dashboard',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'twitch-dashboard',
            'Analytics',
            'Analytics',
            'manage_options',
            'twitch-analytics',
            array($this, 'render_analytics_page')
        );
        
        add_submenu_page(
            'twitch-dashboard',
            'Recordings',
            'Recordings',
            'manage_options',
            'twitch-recordings',
            array($this, 'render_recordings_page')
        );
        
        add_submenu_page(
            'twitch-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'twitch-dashboard-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue dashboard scripts
     */
    public function enqueue_dashboard_scripts() {
        if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'twitch-dashboard') === 0) {
            wp_enqueue_script(
                'twitch-dashboard',
                WP_TWITCH_PLUGIN_URL . 'assets/js/dashboard.js',
                array('jquery', 'wp-api'),
                WP_TWITCH_VERSION,
                true
            );
            
            wp_enqueue_style(
                'twitch-dashboard',
                WP_TWITCH_PLUGIN_URL . 'assets/css/dashboard.css',
                array(),
                WP_TWITCH_VERSION
            );
            
            wp_localize_script('twitch-dashboard', 'twitchDashboard', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('spswifter_twitch_dashboard_nonce'),
                'apiUrl' => rest_url('spswifter-twitch/v1/'),
            ));
        }
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        $channels = $this->get_dashboard_channels();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Multi-Channel Dashboard</h1>
            
            <div class="twitch-dashboard-container">
                <!-- Channel Overview -->
                <div class="twitch-dashboard-section">
                    <h2>Channel Overview</h2>
                    <div class="twitch-channel-grid" id="twitch-channel-grid">
                        <!-- Channels will be loaded here -->
                    </div>
                </div>
                
                <!-- Live Streams -->
                <div class="twitch-dashboard-section">
                    <h2>Live Streams</h2>
                    <div class="twitch-live-streams" id="twitch-live-streams">
                        <!-- Live streams will be loaded here -->
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="twitch-dashboard-section">
                    <h2>Recent Activity</h2>
                    <div class="twitch-recent-activity" id="twitch-recent-activity">
                        <!-- Activity will be loaded here -->
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="twitch-dashboard-section">
                    <h2>Quick Stats</h2>
                    <div class="twitch-quick-stats" id="twitch-quick-stats">
                        <!-- Stats will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        $channels = $this->get_dashboard_channels();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Analytics</h1>
            
            <div class="twitch-analytics-container">
                <!-- Analytics Controls -->
                <div class="twitch-analytics-controls">
                    <select id="analytics-channel">
                        <option value="all">All Channels</option>
                        <?php foreach ($channels as $channel): ?>
                            <option value="<?php echo esc_attr($channel); ?>"><?php echo esc_html($channel); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="analytics-period">
                        <option value="day">Last 24 Hours</option>
                        <option value="week" selected>Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                        <option value="year">Last Year</option>
                    </select>
                    
                    <button id="refresh-analytics" class="button button-primary">Refresh</button>
                </div>
                
                <!-- Analytics Charts -->
                <div class="twitch-analytics-charts">
                    <div class="twitch-chart-container">
                        <h3>Viewer Trends</h3>
                        <div class="twitch-chart" id="viewer-trends-chart">
                            <!-- Chart will be rendered here -->
                        </div>
                    </div>
                    
                    <div class="twitch-chart-container">
                        <h3>Stream Duration</h3>
                        <div class="twitch-chart" id="duration-chart">
                            <!-- Chart will be rendered here -->
                        </div>
                    </div>
                    
                    <div class="twitch-chart-container">
                        <h3>Engagement Metrics</h3>
                        <div class="twitch-chart" id="engagement-chart">
                            <!-- Chart will be rendered here -->
                        </div>
                    </div>
                    
                    <div class="twitch-chart-container">
                        <h3>Growth Analytics</h3>
                        <div class="twitch-chart" id="growth-chart">
                            <!-- Chart will be rendered here -->
                        </div>
                    </div>
                </div>
                
                <!-- Analytics Table -->
                <div class="twitch-analytics-table">
                    <h3>Detailed Analytics</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Channel</th>
                                <th>Total Streams</th>
                                <th>Total Hours</th>
                                <th>Avg Viewers</th>
                                <th>Peak Viewers</th>
                                <th>New Followers</th>
                                <th>Total Bits</th>
                                <th>Engagement Score</th>
                            </tr>
                        </thead>
                        <tbody id="analytics-table-body">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render recordings page
     */
    public function render_recordings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Stream Recordings</h1>
            
            <div class="twitch-recordings-container">
                <!-- Recording Controls -->
                <div class="twitch-recordings-controls">
                    <select id="recording-channel">
                        <option value="all">All Channels</option>
                    </select>
                    
                    <select id="recording-status">
                        <option value="all">All Status</option>
                        <option value="recording">Recording</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                    
                    <button id="refresh-recordings" class="button button-primary">Refresh</button>
                </div>
                
                <!-- Recording Stats -->
                <div class="twitch-recording-stats" id="twitch-recording-stats">
                    <!-- Stats will be loaded here -->
                </div>
                
                <!-- Recordings Table -->
                <div class="twitch-recordings-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Channel</th>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Max Viewers</th>
                                <th>Started</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recordings-table-body">
                            <!-- Recordings will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $settings = $this->get_dashboard_settings();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Dashboard Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_dashboard_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_dashboard_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Dashboard Channels</th>
                        <td>
                            <textarea name="spswifter_twitch_dashboard_settings[channels]" rows="5" class="large-text"><?php echo esc_textarea(implode("\n", $settings['channels'] ?? array())); ?></textarea>
                            <p class="description">Enter one channel per line</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Auto Refresh Interval</th>
                        <td>
                            <select name="spswifter_twitch_dashboard_settings[refresh_interval]">
                                <option value="30" <?php selected($settings['refresh_interval'], 30); ?>>30 seconds</option>
                                <option value="60" <?php selected($settings['refresh_interval'], 60); ?>>1 minute</option>
                                <option value="300" <?php selected($settings['refresh_interval'], 300); ?>>5 minutes</option>
                                <option value="600" <?php selected($settings['refresh_interval'], 600); ?>>10 minutes</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Recording</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_dashboard_settings[enable_recording]" <?php checked($settings['enable_recording'], true); ?> />
                            <label>Enable stream recording for dashboard channels</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Analytics</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_dashboard_settings[enable_analytics]" <?php checked($settings['enable_analytics'], true); ?> />
                            <label>Enable advanced analytics tracking</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Chart Type</th>
                        <td>
                            <select name="spswifter_twitch_dashboard_settings[chart_type]">
                                <option value="line" <?php selected($settings['chart_type'], 'line'); ?>>Line Chart</option>
                                <option value="bar" <?php selected($settings['chart_type'], 'bar'); ?>>Bar Chart</option>
                                <option value="area" <?php selected($settings['chart_type'], 'area'); ?>>Area Chart</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Dark Mode</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_dashboard_settings[dark_mode]" <?php checked($settings['dark_mode'], true); ?> />
                            <label>Enable dark mode for dashboard</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Handle dashboard data AJAX
     */
    public function handle_dashboard_data() {
        check_ajax_referer('spswifter_twitch_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $action = $_POST['dashboard_action'] ?? '';
        
        switch ($action) {
            case 'get_overview':
                $this->get_overview_data();
                break;
            case 'get_analytics':
                $this->get_analytics_data();
                break;
            case 'get_recordings':
                $this->get_recordings_data();
                break;
            case 'get_live_streams':
                $this->get_live_streams_data();
                break;
            case 'get_activity':
                $this->get_activity_data();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get overview data
     */
    private function get_overview_data() {
        $channels = $this->get_dashboard_channels();
        $overview_data = array();
        
        foreach ($channels as $channel) {
            $stream_data = $this->api->get_complete_stream_info($channel);
            $real_time_data = $this->analytics->get_real_time_analytics($channel);
            
            $overview_data[] = array(
                'channel' => $channel,
                'is_live' => $stream_data['is_live'] ?? false,
                'title' => $stream_data['stream']['title'] ?? '',
                'game' => $stream_data['game']['name'] ?? '',
                'viewers' => $stream_data['stream']['viewer_count'] ?? 0,
                'started_at' => $stream_data['stream']['started_at'] ?? '',
                'thumbnail_url' => $stream_data['stream']['thumbnail_url'] ?? '',
                'profile_image_url' => $stream_data['user']['profile_image_url'] ?? '',
                'followers' => $stream_data['user']['followers'] ?? 0,
                'duration' => $real_time_data['live_duration'] ?? 0,
            );
        }
        
        wp_send_json_success(array('channels' => $overview_data));
    }
    
    /**
     * Get analytics data
     */
    private function get_analytics_data() {
        $channel = $_POST['channel'] ?? 'all';
        $period = $_POST['period'] ?? 'week';
        
        if ($channel === 'all') {
            $channels = $this->get_dashboard_channels();
            $analytics_data = $this->analytics->get_multi_channel_analytics($channels, $period);
        } else {
            $analytics_data = $this->analytics->get_channel_analytics($channel, $period);
        }
        
        wp_send_json_success(array('analytics' => $analytics_data));
    }
    
    /**
     * Get recordings data
     */
    private function get_recordings_data() {
        $channel = $_POST['channel'] ?? 'all';
        $status = $_POST['status'] ?? 'all';
        $limit = $_POST['limit'] ?? 50;
        
        $recording = new SPSWIFTER_Twitch_Stream_Recording();
        
        if ($channel === 'all') {
            $recordings = $recording->get_all_recordings(null, $status, $limit);
        } else {
            $recordings = $recording->get_all_recordings($channel, $status, $limit);
        }
        
        $statistics = $recording->get_recording_statistics($channel === 'all' ? null : $channel);
        
        wp_send_json_success(array(
            'recordings' => $recordings,
            'statistics' => $statistics
        ));
    }
    
    /**
     * Get live streams data
     */
    private function get_live_streams_data() {
        $channels = $this->get_dashboard_channels();
        $live_streams = array();
        
        foreach ($channels as $channel) {
            $stream_data = $this->api->get_complete_stream_info($channel);
            
            if ($stream_data && $stream_data['is_live']) {
                $live_streams[] = array(
                    'channel' => $channel,
                    'title' => $stream_data['stream']['title'] ?? '',
                    'game' => $stream_data['game']['name'] ?? '',
                    'viewers' => $stream_data['stream']['viewer_count'] ?? 0,
                    'started_at' => $stream_data['stream']['started_at'] ?? '',
                    'thumbnail_url' => $stream_data['stream']['thumbnail_url'] ?? '',
                    'profile_image_url' => $stream_data['user']['profile_image_url'] ?? '',
                );
            }
        }
        
        // Sort by viewers descending
        usort($live_streams, function($a, $b) {
            return $b['viewers'] - $a['viewers'];
        });
        
        wp_send_json_success(array('live_streams' => $live_streams));
    }
    
    /**
     * Get activity data
     */
    private function get_activity_data() {
        $channels = $this->get_dashboard_channels();
        $activity_data = array();
        
        // Get recent follows
        $follow_events = get_option('spswifter_twitch_follow_events', array());
        $recent_follows = array();
        
        foreach ($follow_events as $event) {
            if (in_array($event['channel'], $channels)) {
                $recent_follows[] = array(
                    'type' => 'follow',
                    'channel' => $event['channel'],
                    'user' => $event['follower'],
                    'timestamp' => $event['followed_at'],
                );
            }
        }
        
        // Get recent subscribes
        $subscribe_events = get_option('spswifter_twitch_subscribe_events', array());
        $recent_subscribes = array();
        
        foreach ($subscribe_events as $event) {
            if (in_array($event['channel'], $channels)) {
                $recent_subscribes[] = array(
                    'type' => 'subscribe',
                    'channel' => $event['channel'],
                    'user' => $event['subscriber'],
                    'tier' => $event['tier'],
                    'timestamp' => $event['created_at'],
                );
            }
        }
        
        // Get recent cheers
        $cheer_events = get_option('spswifter_twitch_cheer_events', array());
        $recent_cheers = array();
        
        foreach ($cheer_events as $event) {
            if (in_array($event['channel'], $channels)) {
                $recent_cheers[] = array(
                    'type' => 'cheer',
                    'channel' => $event['channel'],
                    'user' => $event['user'],
                    'bits' => $event['bits'],
                    'timestamp' => $event['created_at'],
                );
            }
        }
        
        // Combine and sort all activity
        $all_activity = array_merge($recent_follows, $recent_subscribes, $recent_cheers);
        usort($all_activity, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Return only last 20 activities
        $activity_data = array_slice($all_activity, 0, 20);
        
        wp_send_json_success(array('activity' => $activity_data));
    }
    
    /**
     * Get dashboard channels
     */
    private function get_dashboard_channels() {
        $settings = $this->get_dashboard_settings();
        return $settings['channels'] ?? array();
    }
    
    /**
     * Get dashboard settings
     */
    private function get_dashboard_settings() {
        return get_option('spswifter_twitch_dashboard_settings', array(
            'channels' => array(),
            'refresh_interval' => 60,
            'enable_recording' => false,
            'enable_analytics' => true,
            'chart_type' => 'line',
            'dark_mode' => false,
        ));
    }
    
    /**
     * Get dashboard data for API
     */
    public function get_dashboard_data($channels = null, $period = 'week') {
        if (!$channels) {
            $channels = $this->get_dashboard_channels();
        }
        
        $dashboard_data = array(
            'overview' => $this->get_overview_data_array($channels),
            'analytics' => $this->analytics->get_multi_channel_analytics($channels, $period),
            'live_streams' => $this->get_live_streams_array($channels),
            'activity' => $this->get_activity_array($channels),
            'recordings' => $this->get_recordings_array($channels),
            'statistics' => $this->get_statistics_array($channels),
        );
        
        return $dashboard_data;
    }
    
    /**
     * Get overview data array
     */
    private function get_overview_data_array($channels) {
        $overview_data = array();
        
        foreach ($channels as $channel) {
            $stream_data = $this->api->get_complete_stream_info($channel);
            $real_time_data = $this->analytics->get_real_time_analytics($channel);
            
            $overview_data[$channel] = array(
                'is_live' => $stream_data['is_live'] ?? false,
                'title' => $stream_data['stream']['title'] ?? '',
                'game' => $stream_data['game']['name'] ?? '',
                'viewers' => $stream_data['stream']['viewer_count'] ?? 0,
                'started_at' => $stream_data['stream']['started_at'] ?? '',
                'thumbnail_url' => $stream_data['stream']['thumbnail_url'] ?? '',
                'profile_image_url' => $stream_data['user']['profile_image_url'] ?? '',
                'followers' => $stream_data['user']['followers'] ?? 0,
                'duration' => $real_time_data['live_duration'] ?? 0,
            );
        }
        
        return $overview_data;
    }
    
    /**
     * Get live streams array
     */
    private function get_live_streams_array($channels) {
        $live_streams = array();
        
        foreach ($channels as $channel) {
            $stream_data = $this->api->get_complete_stream_info($channel);
            
            if ($stream_data && $stream_data['is_live']) {
                $live_streams[$channel] = array(
                    'title' => $stream_data['stream']['title'] ?? '',
                    'game' => $stream_data['game']['name'] ?? '',
                    'viewers' => $stream_data['stream']['viewer_count'] ?? 0,
                    'started_at' => $stream_data['stream']['started_at'] ?? '',
                    'thumbnail_url' => $stream_data['stream']['thumbnail_url'] ?? '',
                    'profile_image_url' => $stream_data['user']['profile_image_url'] ?? '',
                );
            }
        }
        
        return $live_streams;
    }
    
    /**
     * Get activity array
     */
    private function get_activity_array($channels) {
        $activity_data = array();
        
        // Get recent events from all channels
        $follow_events = get_option('spswifter_twitch_follow_events', array());
        $subscribe_events = get_option('spswifter_twitch_subscribe_events', array());
        $cheer_events = get_option('spswifter_twitch_cheer_events', array());
        
        foreach ($follow_events as $event) {
            if (in_array($event['channel'], $channels)) {
                $activity_data[] = array(
                    'type' => 'follow',
                    'channel' => $event['channel'],
                    'user' => $event['follower'],
                    'timestamp' => $event['followed_at'],
                );
            }
        }
        
        foreach ($subscribe_events as $event) {
            if (in_array($event['channel'], $channels)) {
                $activity_data[] = array(
                    'type' => 'subscribe',
                    'channel' => $event['channel'],
                    'user' => $event['subscriber'],
                    'tier' => $event['tier'],
                    'timestamp' => $event['created_at'],
                );
            }
        }
        
        foreach ($cheer_events as $event) {
            if (in_array($event['channel'], $channels)) {
                $activity_data[] = array(
                    'type' => 'cheer',
                    'channel' => $event['channel'],
                    'user' => $event['user'],
                    'bits' => $event['bits'],
                    'timestamp' => $event['created_at'],
                );
            }
        }
        
        // Sort by timestamp descending
        usort($activity_data, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($activity_data, 0, 50);
    }
    
    /**
     * Get recordings array
     */
    private function get_recordings_array($channels) {
        $recording = new SPSWIFTER_Twitch_Stream_Recording();
        $recordings = array();
        
        foreach ($channels as $channel) {
            $channel_recordings = $recording->get_all_recordings($channel);
            $recordings[$channel] = $channel_recordings;
        }
        
        return $recordings;
    }
    
    /**
     * Get statistics array
     */
    private function get_statistics_array($channels) {
        $recording = new SPSWIFTER_Twitch_Stream_Recording();
        $statistics = array();
        
        foreach ($channels as $channel) {
            $statistics[$channel] = $recording->get_recording_statistics($channel);
        }
        
        return $statistics;
    }
}

// Initialize dashboard
new SPSWIFTER_Twitch_Dashboard();
