<?php
/**
 * Advanced Analytics Dashboard for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Analytics_Dashboard {
    
    private $analytics;
    private $dashboard_settings;
    
    public function __construct() {
        $this->analytics = new WP_Twitch_Advanced_Analytics();
        $this->dashboard_settings = $this->get_dashboard_settings();
        
        add_action('admin_menu', array($this, 'add_dashboard_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_scripts'));
        add_action('wp_ajax_twitch_analytics_dashboard', array($this, 'handle_dashboard_ajax'));
        add_action('wp_ajax_nopriv_twitch_analytics_dashboard', array($this, 'handle_dashboard_ajax'));
        add_action('init', array($this, 'register_dashboard_shortcodes'));
    }
    
    /**
     * Add dashboard menu
     */
    public function add_dashboard_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Analytics Dashboard',
            'Analytics',
            'manage_options',
            'twitch-analytics-dashboard',
            array($this, 'render_dashboard_page')
        );
    }
    
    /**
     * Register dashboard shortcodes
     */
    public function register_dashboard_shortcodes() {
        add_shortcode('twitch_analytics_dashboard', array($this, 'render_dashboard_shortcode'));
        add_shortcode('twitch_analytics_widget', array($this, 'render_widget_shortcode'));
        add_shortcode('twitch_analytics_chart', array($this, 'render_chart_shortcode'));
    }
    
    /**
     * Enqueue dashboard scripts
     */
    public function enqueue_dashboard_scripts() {
        wp_enqueue_style(
            'twitch-analytics-dashboard',
            WP_TWITCH_PLUGIN_URL . 'assets/css/analytics-dashboard.css',
            array(),
            WP_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-analytics-dashboard',
            WP_TWITCH_PLUGIN_URL . 'assets/js/analytics-dashboard.js',
            array('jquery', 'chart-js'),
            WP_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-analytics-dashboard', 'twitchAnalyticsDashboard', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('twitch_analytics_dashboard_nonce'),
            'refreshInterval' => $this->dashboard_settings['refresh_interval'] ?? 30000,
            'chartColors' => array(
                'primary' => '#9146ff',
                'secondary' => '#7928ca',
                'success' => '#28a745',
                'warning' => '#ffc107',
                'danger' => '#dc3545',
                'info' => '#17a2b8',
            ),
        ));
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        ?>
        <div class="wrap twitch-analytics-dashboard-admin">
            <h1 class="wp-heading-inline">Twitch Analytics Dashboard</h1>
            
            <div class="twitch-dashboard-filters">
                <select id="twitch-channel-filter" class="twitch-select">
                    <option value="">Alle Kanäle</option>
                    <?php 
                    $channels = $this->get_user_channels();
                    foreach ($channels as $channel): 
                    ?>
                        <option value="<?php echo esc_attr($channel); ?>"><?php echo esc_html($channel); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select id="twitch-date-range" class="twitch-select">
                    <option value="7">Letzte 7 Tage</option>
                    <option value="30">Letzte 30 Tage</option>
                    <option value="90">Letzte 90 Tage</option>
                    <option value="365">Letztes Jahr</option>
                    <option value="custom">Benutzerdefiniert</option>
                </select>
                
                <button id="twitch-refresh-dashboard" class="button button-primary">
                    <span class="dashicons dashicons-update"></span> Aktualisieren
                </button>
            </div>
            
            <div class="twitch-dashboard-grid">
                <!-- Overview Cards -->
                <div class="twitch-dashboard-row">
                    <div class="twitch-card twitch-card-3">
                        <div class="twitch-card-header">
                            <h3>Gesamt-Zuschauer</h3>
                            <span class="twitch-card-icon">👥</span>
                        </div>
                        <div class="twitch-card-content">
                            <div class="twitch-card-value" id="total-viewers">0</div>
                            <div class="twitch-card-change" id="viewers-change">+0%</div>
                        </div>
                    </div>
                    
                    <div class="twitch-card twitch-card-3">
                        <div class="twitch-card-header">
                            <h3>Gesamt-Dauer</h3>
                            <span class="twitch-card-icon">⏱️</span>
                        </div>
                        <div class="twitch-card-content">
                            <div class="twitch-card-value" id="total-duration">0h</div>
                            <div class="twitch-card-change" id="duration-change">+0%</div>
                        </div>
                    </div>
                    
                    <div class="twitch-card twitch-card-3">
                        <div class="twitch-card-header">
                            <h3>Gesamt-Einnahmen</h3>
                            <span class="twitch-card-icon">💰</span>
                        </div>
                        <div class="twitch-card-content">
                            <div class="twitch-card-value" id="total-revenue">€0</div>
                            <div class="twitch-card-change" id="revenue-change">+0%</div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="twitch-dashboard-row">
                    <div class="twitch-card twitch-card-2">
                        <div class="twitch-card-header">
                            <h3>Zuschauer-Trend</h3>
                            <div class="twitch-card-actions">
                                <button class="twitch-chart-type" data-chart="viewers" data-type="line">Linie</button>
                                <button class="twitch-chart-type" data-chart="viewers" data-type="bar">Balken</button>
                            </div>
                        </div>
                        <div class="twitch-card-content">
                            <canvas id="viewers-chart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    
                    <div class="twitch-card twitch-card-2">
                        <div class="twitch-card-header">
                            <h3>Einnahmen-Trend</h3>
                            <div class="twitch-card-actions">
                                <button class="twitch-chart-type" data-chart="revenue" data-type="line">Linie</button>
                                <button class="twitch-chart-type" data-chart="revenue" data-type="bar">Balken</button>
                            </div>
                        </div>
                        <div class="twitch-card-content">
                            <canvas id="revenue-chart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Top Content -->
                <div class="twitch-dashboard-row">
                    <div class="twitch-card twitch-card-2">
                        <div class="twitch-card-header">
                            <h3>Top Streams</h3>
                        </div>
                        <div class="twitch-card-content">
                            <div class="twitch-table-container">
                                <table class="twitch-table">
                                    <thead>
                                        <tr>
                                            <th>Titel</th>
                                            <th>Datum</th>
                                            <th>Zuschauer</th>
                                            <th>Dauer</th>
                                        </tr>
                                    </thead>
                                    <tbody id="top-streams">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="twitch-card twitch-card-2">
                        <div class="twitch-card-header">
                            <h3>Top Spiele</h3>
                        </div>
                        <div class="twitch-card-content">
                            <div class="twitch-table-container">
                                <table class="twitch-table">
                                    <thead>
                                        <tr>
                                            <th>Spiel</th>
                                            <th>Streams</th>
                                            <th>Zuschauer</th>
                                            <th>Dauer</th>
                                        </tr>
                                    </thead>
                                    <tbody id="top-games">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Analytics -->
                <div class="twitch-dashboard-row">
                    <div class="twitch-card twitch-card-1">
                        <div class="twitch-card-header">
                            <h3>Detaillierte Analytics</h3>
                            <div class="twitch-card-actions">
                                <button class="twitch-export-btn" data-format="csv">CSV Export</button>
                                <button class="twitch-export-btn" data-format="json">JSON Export</button>
                            </div>
                        </div>
                        <div class="twitch-card-content">
                            <div class="twitch-analytics-tabs">
                                <button class="twitch-tab active" data-tab="overview">Übersicht</button>
                                <button class="twitch-tab" data-tab="engagement">Engagement</button>
                                <button class="twitch-tab" data-tab="monetization">Monetarisierung</button>
                                <button class="twitch-tab" data-tab="audience">Zielgruppe</button>
                            </div>
                            
                            <div class="twitch-tab-content">
                                <div class="twitch-tab-pane active" id="overview-tab">
                                    <div class="twitch-stats-grid">
                                        <div class="twitch-stat">
                                            <label>Durchschn. Zuschauer:</label>
                                            <span id="avg-viewers">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Peak Zuschauer:</label>
                                            <span id="peak-viewers">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Gesamt Streams:</label>
                                            <span id="total-streams">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Durchschn. Dauer:</label>
                                            <span id="avg-duration">0h</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="twitch-tab-pane" id="engagement-tab">
                                    <div class="twitch-stats-grid">
                                        <div class="twitch-stat">
                                            <label>Chat-Nachrichten:</label>
                                            <span id="chat-messages">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Unique Chatter:</label>
                                            <span id="unique-chatters">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Clips erstellt:</label>
                                            <span id="clips-created">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Follows:</label>
                                            <span id="follows">0</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="twitch-tab-pane" id="monetization-tab">
                                    <div class="twitch-stats-grid">
                                        <div class="twitch-stat">
                                            <label>Donationen:</label>
                                            <span id="donations">€0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Abonnements:</label>
                                            <span id="subscriptions">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Bits:</label>
                                            <span id="bits">0</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Werbeeinnahmen:</label>
                                            <span id="ad-revenue">€0</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="twitch-tab-pane" id="audience-tab">
                                    <div class="twitch-stats-grid">
                                        <div class="twitch-stat">
                                            <label>Haupt-Land:</label>
                                            <span id="top-country">-</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Sprachen:</label>
                                            <span id="languages">-</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Altersgruppe:</label>
                                            <span id="age-group">-</span>
                                        </div>
                                        <div class="twitch-stat">
                                            <label>Geschlecht:</label>
                                            <span id="gender">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render dashboard shortcode
     */
    public function render_dashboard_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'period' => '30',
            'show_charts' => 'true',
            'show_tables' => 'true',
            'layout' => 'full',
            'theme' => 'default',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-analytics-dashboard twitch-dashboard-<?php echo esc_attr($atts['layout']); ?> twitch-theme-<?php echo esc_attr($atts['theme']); ?>" 
             data-channel="<?php echo esc_attr($atts['channel']); ?>" 
             data-period="<?php echo esc_attr($atts['period']); ?>">
            
            <div class="twitch-dashboard-header">
                <h2>Twitch Analytics Dashboard</h2>
                <div class="twitch-dashboard-controls">
                    <select class="twitch-channel-select">
                        <option value="">Alle Kanäle</option>
                        <?php 
                        $channels = $this->get_user_channels();
                        foreach ($channels as $channel): 
                        ?>
                            <option value="<?php echo esc_attr($channel); ?>"><?php echo esc_html($channel); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="twitch-period-select">
                        <option value="7">7 Tage</option>
                        <option value="30" selected>30 Tage</option>
                        <option value="90">90 Tage</option>
                    </select>
                    <button class="twitch-refresh-btn">🔄</button>
                </div>
            </div>
            
            <?php if ($atts['show_charts'] === 'true'): ?>
                <div class="twitch-dashboard-charts">
                    <div class="twitch-chart-container">
                        <h3>Zuschauer-Trend</h3>
                        <canvas id="shortcode-viewers-chart"></canvas>
                    </div>
                    <div class="twitch-chart-container">
                        <h3>Einnahmen-Trend</h3>
                        <canvas id="shortcode-revenue-chart"></canvas>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_tables'] === 'true'): ?>
                <div class="twitch-dashboard-tables">
                    <div class="twitch-table-container">
                        <h3>Top Streams</h3>
                        <table class="twitch-table">
                            <thead>
                                <tr>
                                    <th>Titel</th>
                                    <th>Datum</th>
                                    <th>Zuschauer</th>
                                    <th>Dauer</th>
                                </tr>
                            </thead>
                            <tbody class="twitch-top-streams">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render widget shortcode
     */
    public function render_widget_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'type' => 'overview',
            'period' => '7',
            'size' => 'medium',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-analytics-widget twitch-widget-<?php echo esc_attr($atts['type']); ?> twitch-widget-<?php echo esc_attr($atts['size']); ?>" 
             data-channel="<?php echo esc_attr($atts['channel']); ?>" 
             data-period="<?php echo esc_attr($atts['period']); ?>">
            
            <?php if ($atts['type'] === 'overview'): ?>
                <div class="twitch-widget-header">
                    <h4>Stream Analytics</h4>
                </div>
                <div class="twitch-widget-content">
                    <div class="twitch-widget-stat">
                        <span class="twitch-widget-label">Zuschauer</span>
                        <span class="twitch-widget-value" id="widget-viewers">0</span>
                    </div>
                    <div class="twitch-widget-stat">
                        <span class="twitch-widget-label">Streams</span>
                        <span class="twitch-widget-value" id="widget-streams">0</span>
                    </div>
                    <div class="twitch-widget-stat">
                        <span class="twitch-widget-label">Dauer</span>
                        <span class="twitch-widget-value" id="widget-duration">0h</span>
                    </div>
                </div>
            <?php elseif ($atts['type'] === 'chart'): ?>
                <div class="twitch-widget-header">
                    <h4>Zuschauer-Trend</h4>
                </div>
                <div class="twitch-widget-content">
                    <canvas id="widget-chart" width="300" height="150"></canvas>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render chart shortcode
     */
    public function render_chart_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'type' => 'viewers',
            'period' => '30',
            'width' => '400',
            'height' => '200',
            'style' => 'line',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-analytics-chart" 
             data-channel="<?php echo esc_attr($atts['channel']); ?>" 
             data-type="<?php echo esc_attr($atts['type']); ?>" 
             data-period="<?php echo esc_attr($atts['period']); ?>">
            <canvas width="<?php echo esc_attr($atts['width']); ?>" height="<?php echo esc_attr($atts['height']); ?>"></canvas>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle dashboard AJAX
     */
    public function handle_dashboard_ajax() {
        check_ajax_referer('twitch_analytics_dashboard_nonce', 'nonce');
        
        $action = $_POST['analytics_action'] ?? '';
        
        switch ($action) {
            case 'get_overview':
                $this->get_overview_ajax();
                break;
            case 'get_chart_data':
                $this->get_chart_data_ajax();
                break;
            case 'get_top_streams':
                $this->get_top_streams_ajax();
                break;
            case 'get_top_games':
                $this->get_top_games_ajax();
                break;
            case 'export_data':
                $this->export_data_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get overview AJAX
     */
    private function get_overview_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $period = intval($_POST['period'] ?? 30);
        
        $overview = $this->analytics->get_overview_stats($channel, $period);
        
        wp_send_json_success(array('overview' => $overview));
    }
    
    /**
     * Get chart data AJAX
     */
    private function get_chart_data_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $period = intval($_POST['period'] ?? 30);
        $type = sanitize_text_field($_POST['chart_type'] ?? 'viewers');
        
        $chart_data = $this->analytics->get_chart_data($channel, $period, $type);
        
        wp_send_json_success(array('chart_data' => $chart_data));
    }
    
    /**
     * Get top streams AJAX
     */
    private function get_top_streams_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $period = intval($_POST['period'] ?? 30);
        $limit = intval($_POST['limit'] ?? 10);
        
        $top_streams = $this->analytics->get_top_streams($channel, $period, $limit);
        
        wp_send_json_success(array('top_streams' => $top_streams));
    }
    
    /**
     * Get top games AJAX
     */
    private function get_top_games_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $period = intval($_POST['period'] ?? 30);
        $limit = intval($_POST['limit'] ?? 10);
        
        $top_games = $this->analytics->get_top_games($channel, $period, $limit);
        
        wp_send_json_success(array('top_games' => $top_games));
    }
    
    /**
     * Export data AJAX
     */
    private function export_data_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $period = intval($_POST['period'] ?? 30);
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        
        $data = $this->analytics->export_analytics_data($channel, $period, $format);
        
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="twitch-analytics.csv"');
            echo $data;
        } elseif ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="twitch-analytics.json"');
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        
        exit;
    }
    
    /**
     * Get user channels
     */
    private function get_user_channels() {
        // This would get the user's connected Twitch channels
        return array('channel1', 'channel2', 'channel3');
    }
    
    /**
     * Get dashboard settings
     */
    private function get_dashboard_settings() {
        return get_option('twitch_analytics_dashboard_settings', array(
            'refresh_interval' => 30000,
            'default_period' => 30,
            'chart_animation' => true,
            'show_trends' => true,
            'enable_export' => true,
            'max_data_points' => 100,
        ));
    }
    
    /**
     * Add dashboard settings menu
     */
    public function add_dashboard_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Analytics Settings',
            'Analytics Settings',
            'manage_options',
            'twitch-analytics-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Analytics Dashboard Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('twitch_analytics_dashboard_settings'); ?>
                <?php do_settings_sections('twitch_analytics_dashboard_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Refresh Interval</th>
                        <td>
                            <input type="number" name="twitch_analytics_dashboard_settings[refresh_interval]" 
                                   value="<?php echo esc_attr($this->dashboard_settings['refresh_interval'] ?? 30000); ?>" 
                                   min="5000" max="300000" step="1000" class="regular-text" />
                            <p class="description">Auto-refresh interval in milliseconds (default: 30000 = 30 seconds)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Default Period</th>
                        <td>
                            <select name="twitch_analytics_dashboard_settings[default_period]">
                                <option value="7" <?php selected($this->dashboard_settings['default_period'], 7); ?>>7 Tage</option>
                                <option value="30" <?php selected($this->dashboard_settings['default_period'], 30); ?>>30 Tage</option>
                                <option value="90" <?php selected($this->dashboard_settings['default_period'], 90); ?>>90 Tage</option>
                                <option value="365" <?php selected($this->dashboard_settings['default_period'], 365); ?>>1 Jahr</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Chart Animation</th>
                        <td>
                            <input type="checkbox" name="twitch_analytics_dashboard_settings[chart_animation]" 
                                   <?php checked($this->dashboard_settings['chart_animation'], true); ?> />
                            <label>Enable chart animations</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Trends</th>
                        <td>
                            <input type="checkbox" name="twitch_analytics_dashboard_settings[show_trends]" 
                                   <?php checked($this->dashboard_settings['show_trends'], true); ?> />
                            <label>Show trend indicators</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Export</th>
                        <td>
                            <input type="checkbox" name="twitch_analytics_dashboard_settings[enable_export]" 
                                   <?php checked($this->dashboard_settings['enable_export'], true); ?> />
                            <label>Enable data export functionality</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Max Data Points</th>
                        <td>
                            <input type="number" name="twitch_analytics_dashboard_settings[max_data_points]" 
                                   value="<?php echo esc_attr($this->dashboard_settings['max_data_points'] ?? 100); ?>" 
                                   min="10" max="1000" step="10" class="regular-text" />
                            <p class="description">Maximum number of data points to display in charts</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize analytics dashboard
new WP_Twitch_Analytics_Dashboard();
