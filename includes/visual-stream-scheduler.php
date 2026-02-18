<?php
/**
 * Visual Stream Scheduler for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Visual_Stream_Scheduler {
    
    private $scheduler_settings;
    private $stream_schedules;
    private $recurring_patterns;
    
    public function __construct() {
        $this->scheduler_settings = $this->get_scheduler_settings();
        $this->stream_schedules = $this->get_stream_schedules();
        $this->recurring_patterns = $this->get_recurring_patterns();
        
        add_action('init', array($this, 'init_stream_scheduler'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_stream_scheduler_scripts'));
        add_action('wp_ajax_twitch_stream_scheduler', array($this, 'handle_stream_scheduler_ajax'));
        add_action('wp_ajax_nopriv_twitch_stream_scheduler', array($this, 'handle_stream_scheduler_ajax'));
        add_action('admin_menu', array($this, 'add_stream_scheduler_menu'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_scheduler_shortcodes'));
        
        // Schedule cleanup
        add_action('twitch_scheduler_cleanup', array($this, 'cleanup_old_schedules'));
        if (!wp_next_scheduled('twitch_scheduler_cleanup')) {
            wp_schedule_event(time(), 'daily', 'twitch_scheduler_cleanup');
        }
    }
    
    /**
     * Initialize stream scheduler
     */
    public function init_stream_scheduler() {
        $this->create_scheduler_tables();
        $this->load_scheduler_features();
    }
    
    /**
     * Create scheduler database tables
     */
    private function create_scheduler_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Stream schedules table
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            channel varchar(100) NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            start_time datetime NOT NULL,
            end_time datetime NOT NULL,
            timezone varchar(50) DEFAULT 'UTC',
            stream_type enum('live','premiere','rerun') DEFAULT 'live',
            category varchar(100),
            tags text,
            is_recurring tinyint(1) DEFAULT 0,
            recurring_pattern varchar(50),
            recurring_end_date datetime,
            status enum('scheduled','live','completed','cancelled') DEFAULT 'scheduled',
            twitch_stream_id varchar(50),
            thumbnail_url text,
            vod_url text,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY channel_time (channel, start_time),
            KEY status_time (status, start_time),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        // Recurring patterns table
        $recurring_table = $wpdb->prefix . 'twitch_recurring_patterns';
        $recurring_sql = "CREATE TABLE $recurring_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            pattern_type enum('daily','weekly','monthly','custom') NOT NULL,
            pattern_data text NOT NULL,
            start_date date NOT NULL,
            end_date date,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($recurring_sql);
    }
    
    /**
     * Load scheduler features
     */
    private function load_scheduler_features() {
        // Add timezone support
        add_filter('twitch_scheduler_timezones', array($this, 'get_supported_timezones'));
        
        // Add stream types
        add_filter('twitch_scheduler_stream_types', array($this, 'get_stream_types'));
        
        // Add categories
        add_filter('twitch_scheduler_categories', array($this, 'get_stream_categories'));
        
        // Integration with Twitch API
        add_action('twitch_stream_started', array($this, 'handle_stream_started'), 10, 2);
        add_action('twitch_stream_ended', array($this, 'handle_stream_ended'), 10, 2);
    }
    
    /**
     * Register scheduler shortcodes
     */
    public function register_scheduler_shortcodes() {
        add_shortcode('twitch_stream_scheduler', array($this, 'render_stream_scheduler_shortcode'));
        add_shortcode('twitch_stream_calendar', array($this, 'render_stream_calendar_shortcode'));
        add_shortcode('twitch_upcoming_streams', array($this, 'render_upcoming_streams_shortcode'));
        add_shortcode('twitch_stream_schedule', array($this, 'render_stream_schedule_shortcode'));
    }
    
    /**
     * Render stream scheduler shortcode
     */
    public function render_stream_scheduler_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'channel' => '',
            'view' => 'calendar',
            'theme' => 'light',
            'height' => '600px',
            'show_controls' => 'true',
            'allow_booking' => 'false',
            'timezone' => wp_timezone_string(),
            'categories' => '',
            'limit' => 50,
        ), $atts);
        
        if (!$atts['channel'] && !current_user_can('manage_options')) {
            return '<p>Please specify a channel or log in as administrator.</p>';
        }
        
        $channel = $atts['channel'] ?: $this->get_current_user_channel();
        
        ob_start();
        ?>
        <div class="twitch-stream-scheduler" 
             data-channel="<?php echo esc_attr($channel); ?>"
             data-view="<?php echo esc_attr($atts['view']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-height="<?php echo esc_attr($atts['height']); ?>"
             data-show-controls="<?php echo esc_attr($atts['show_controls']); ?>"
             data-allow-booking="<?php echo esc_attr($atts['allow_booking']); ?>"
             data-timezone="<?php echo esc_attr($atts['timezone']); ?>"
             data-categories="<?php echo esc_attr($atts['categories']); ?>"
             data-limit="<?php echo esc_attr($atts['limit']); ?>">
            
            <div class="twitch-scheduler-header">
                <div class="twitch-scheduler-title">
                    <h3>
                        <span class="twitch-scheduler-icon">📅</span>
                        Stream Scheduler
                        <?php if ($channel): ?>
                            <span class="twitch-scheduler-channel">for <?php echo esc_html($channel); ?></span>
                        <?php endif; ?>
                    </h3>
                </div>
                
                <div class="twitch-scheduler-controls">
                    <div class="twitch-view-selector">
                        <button class="twitch-view-btn active" data-view="calendar">
                            <span class="twitch-view-icon">📅</span>
                            Calendar
                        </button>
                        <button class="twitch-view-btn" data-view="list">
                            <span class="twitch-view-icon">📋</span>
                            List
                        </button>
                        <button class="twitch-view-btn" data-view="timeline">
                            <span class="twitch-view-icon">⏰</span>
                            Timeline
                        </button>
                    </div>
                    
                    <div class="twitch-scheduler-actions">
                        <button class="twitch-add-stream-btn">
                            <span class="twitch-btn-icon">➕</span>
                            Schedule Stream
                        </button>
                        <button class="twitch-bulk-actions-btn">
                            <span class="twitch-btn-icon">⚙️</span>
                            Bulk Actions
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="twitch-scheduler-filters">
                <div class="twitch-filter-group">
                    <label>Date Range:</label>
                    <input type="date" class="twitch-date-filter" id="twitch-start-date">
                    <span>to</span>
                    <input type="date" class="twitch-date-filter" id="twitch-end-date">
                </div>
                
                <div class="twitch-filter-group">
                    <label>Status:</label>
                    <select class="twitch-status-filter" multiple>
                        <option value="scheduled">Scheduled</option>
                        <option value="live">Live</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="twitch-filter-group">
                    <label>Category:</label>
                    <select class="twitch-category-filter">
                        <option value="">All Categories</option>
                        <?php foreach ($this->get_stream_categories() as $cat_key => $category): ?>
                            <option value="<?php echo esc_attr($cat_key); ?>"><?php echo esc_html($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button class="twitch-clear-filters-btn">
                    <span class="twitch-btn-icon">🗑️</span>
                    Clear Filters
                </button>
            </div>
            
            <div class="twitch-scheduler-content">
                <div class="twitch-calendar-view" id="twitch-calendar-view">
                    <!-- Calendar will be rendered here -->
                </div>
                
                <div class="twitch-list-view" id="twitch-list-view" style="display: none;">
                    <!-- List view will be rendered here -->
                </div>
                
                <div class="twitch-timeline-view" id="twitch-timeline-view" style="display: none;">
                    <!-- Timeline will be rendered here -->
                </div>
            </div>
            
            <div class="twitch-scheduler-sidebar">
                <div class="twitch-upcoming-streams">
                    <h4>Upcoming Streams</h4>
                    <div class="twitch-upcoming-list">
                        <!-- Upcoming streams will be loaded here -->
                    </div>
                </div>
                
                <div class="twitch-stream-stats">
                    <h4>This Month</h4>
                    <div class="twitch-stats-grid">
                        <div class="twitch-stat-item">
                            <span class="twitch-stat-number" id="total-streams">0</span>
                            <span class="twitch-stat-label">Total Streams</span>
                        </div>
                        <div class="twitch-stat-item">
                            <span class="twitch-stat-number" id="live-streams">0</span>
                            <span class="twitch-stat-label">Live Now</span>
                        </div>
                        <div class="twitch-stat-item">
                            <span class="twitch-stat-number" id="hours-streamed">0</span>
                            <span class="twitch-stat-label">Hours Streamed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render stream calendar shortcode
     */
    public function render_stream_calendar_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'channel' => '',
            'view' => 'month',
            'theme' => 'light',
            'height' => '500px',
            'show_navigation' => 'true',
            'timezone' => wp_timezone_string(),
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-stream-calendar" 
             data-channel="<?php echo esc_attr($atts['channel']); ?>"
             data-view="<?php echo esc_attr($atts['view']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-height="<?php echo esc_attr($atts['height']); ?>"
             data-show-navigation="<?php echo esc_attr($atts['show_navigation']); ?>"
             data-timezone="<?php echo esc_attr($atts['timezone']); ?>">
            
            <div class="twitch-calendar-header">
                <button class="twitch-calendar-prev">&larr;</button>
                <h3 class="twitch-calendar-title">Stream Calendar</h3>
                <button class="twitch-calendar-next">&rarr;</button>
            </div>
            
            <div class="twitch-calendar-grid">
                <!-- Calendar grid will be rendered here -->
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render upcoming streams shortcode
     */
    public function render_upcoming_streams_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'channel' => '',
            'limit' => 5,
            'show_category' => 'true',
            'show_description' => 'false',
            'theme' => 'light',
            'layout' => 'list',
        ), $atts);
        
        $upcoming_streams = $this->get_upcoming_streams($atts['channel'], $atts['limit']);
        
        ob_start();
        ?>
        <div class="twitch-upcoming-streams-widget" 
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-layout="<?php echo esc_attr($atts['layout']); ?>">
            
            <h3 class="twitch-widget-title">Upcoming Streams</h3>
            
            <?php if (empty($upcoming_streams)): ?>
                <p class="twitch-no-streams">No upcoming streams scheduled.</p>
            <?php else: ?>
                <div class="twitch-streams-<?php echo esc_attr($atts['layout']); ?>">
                    <?php foreach ($upcoming_streams as $stream): ?>
                        <div class="twitch-stream-item" data-stream-id="<?php echo esc_attr($stream->id); ?>">
                            <div class="twitch-stream-header">
                                <div class="twitch-stream-time">
                                    <div class="twitch-stream-date"><?php echo esc_html(date_i18n('M j', strtotime($stream->start_time))); ?></div>
                                    <div class="twitch-stream-clock"><?php echo esc_html(date_i18n('g:i A', strtotime($stream->start_time))); ?></div>
                                </div>
                                
                                <?php if ($atts['show_category'] === 'true' && $stream->category): ?>
                                    <span class="twitch-stream-category"><?php echo esc_html($this->get_category_name($stream->category)); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="twitch-stream-content">
                                <h4 class="twitch-stream-title"><?php echo esc_html($stream->title); ?></h4>
                                
                                <?php if ($atts['show_description'] === 'true' && $stream->description): ?>
                                    <p class="twitch-stream-description"><?php echo esc_html(wp_trim_words($stream->description, 20)); ?></p>
                                <?php endif; ?>
                                
                                <div class="twitch-stream-meta">
                                    <span class="twitch-stream-channel"><?php echo esc_html($stream->channel); ?></span>
                                    <span class="twitch-stream-duration">
                                        <?php echo esc_html($this->format_duration(strtotime($stream->end_time) - strtotime($stream->start_time))); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="twitch-stream-actions">
                                <button class="twitch-remind-btn" data-stream-id="<?php echo esc_attr($stream->id); ?>">
                                    <span class="twitch-btn-icon">🔔</span>
                                    Remind Me
                                </button>
                                <a href="#" class="twitch-calendar-btn" data-stream-id="<?php echo esc_attr($stream->id); ?>">
                                    <span class="twitch-btn-icon">📅</span>
                                    Add to Calendar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render stream schedule shortcode
     */
    public function render_stream_schedule_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'channel' => '',
            'days' => 7,
            'theme' => 'light',
            'show_weekends' => 'true',
            'timezone' => wp_timezone_string(),
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-stream-schedule" 
             data-channel="<?php echo esc_attr($atts['channel']); ?>"
             data-days="<?php echo esc_attr($atts['days']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-show-weekends="<?php echo esc_attr($atts['show_weekends']); ?>"
             data-timezone="<?php echo esc_attr($atts['timezone']); ?>">
            
            <div class="twitch-schedule-header">
                <h3>Weekly Stream Schedule</h3>
                <div class="twitch-schedule-legend">
                    <span class="twitch-legend-item scheduled">Scheduled</span>
                    <span class="twitch-legend-item live">Live</span>
                    <span class="twitch-legend-item completed">Completed</span>
                </div>
            </div>
            
            <div class="twitch-schedule-grid">
                <!-- Schedule grid will be rendered here -->
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle stream scheduler AJAX
     */
    public function handle_stream_scheduler_ajax() {
        check_ajax_referer('twitch_stream_scheduler_nonce', 'nonce');
        
        $action = $_POST['scheduler_action'] ?? '';
        
        switch ($action) {
            case 'get_streams':
                $this->get_streams_ajax();
                break;
            case 'save_stream':
                $this->save_stream_ajax();
                break;
            case 'delete_stream':
                $this->delete_stream_ajax();
                break;
            case 'update_stream':
                $this->update_stream_ajax();
                break;
            case 'get_calendar_data':
                $this->get_calendar_data_ajax();
                break;
            case 'bulk_update':
                $this->bulk_update_ajax();
                break;
            case 'set_reminder':
                $this->set_reminder_ajax();
                break;
            case 'get_stats':
                $this->get_stats_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get streams AJAX
     */
    private function get_streams_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $limit = intval($_POST['limit'] ?? 50);
        
        $streams = $this->get_filtered_streams($channel, $start_date, $end_date, $status, $category, $limit);
        
        wp_send_json_success(array(
            'streams' => $streams,
            'total' => count($streams)
        ));
    }
    
    /**
     * Save stream AJAX
     */
    private function save_stream_ajax() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $stream_data = array(
            'channel' => sanitize_text_field($_POST['channel'] ?? ''),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'description' => wp_kses_post($_POST['description'] ?? ''),
            'start_time' => sanitize_text_field($_POST['start_time'] ?? ''),
            'end_time' => sanitize_text_field($_POST['end_time'] ?? ''),
            'timezone' => sanitize_text_field($_POST['timezone'] ?? 'UTC'),
            'stream_type' => sanitize_text_field($_POST['stream_type'] ?? 'live'),
            'category' => sanitize_text_field($_POST['category'] ?? ''),
            'tags' => sanitize_text_field($_POST['tags'] ?? ''),
            'is_recurring' => intval($_POST['is_recurring'] ?? 0),
            'recurring_pattern' => sanitize_text_field($_POST['recurring_pattern'] ?? ''),
            'recurring_end_date' => sanitize_text_field($_POST['recurring_end_date'] ?? ''),
            'thumbnail_url' => esc_url_raw($_POST['thumbnail_url'] ?? ''),
        );
        
        // Validate data
        $validation = $this->validate_stream_data($stream_data);
        if (!$validation['valid']) {
            wp_send_json_error($validation['errors']);
            return;
        }
        
        $stream_id = $this->save_stream($stream_data);
        
        if ($stream_id) {
            wp_send_json_success(array(
                'stream_id' => $stream_id,
                'message' => 'Stream scheduled successfully'
            ));
        } else {
            wp_send_json_error('Failed to save stream');
        }
    }
    
    /**
     * Delete stream AJAX
     */
    private function delete_stream_ajax() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $stream_id = intval($_POST['stream_id'] ?? 0);
        
        if ($this->delete_stream($stream_id)) {
            wp_send_json_success('Stream deleted successfully');
        } else {
            wp_send_json_error('Failed to delete stream');
        }
    }
    
    /**
     * Update stream AJAX
     */
    private function update_stream_ajax() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $stream_id = intval($_POST['stream_id'] ?? 0);
        $updates = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'description' => wp_kses_post($_POST['description'] ?? ''),
            'start_time' => sanitize_text_field($_POST['start_time'] ?? ''),
            'end_time' => sanitize_text_field($_POST['end_time'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? ''),
            'category' => sanitize_text_field($_POST['category'] ?? ''),
            'tags' => sanitize_text_field($_POST['tags'] ?? ''),
        );
        
        if ($this->update_stream($stream_id, $updates)) {
            wp_send_json_success('Stream updated successfully');
        } else {
            wp_send_json_error('Failed to update stream');
        }
    }
    
    /**
     * Get calendar data AJAX
     */
    private function get_calendar_data_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        
        $calendar_data = $this->get_calendar_events($channel, $start_date, $end_date);
        
        wp_send_json_success($calendar_data);
    }
    
    /**
     * Bulk update AJAX
     */
    private function bulk_update_ajax() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $stream_ids = array_map('intval', $_POST['stream_ids'] ?? array());
        $updates = $_POST['updates'] ?? array();
        
        $updated = $this->bulk_update_streams($stream_ids, $updates);
        
        wp_send_json_success(array(
            'updated' => $updated,
            'message' => $updated . ' streams updated successfully'
        ));
    }
    
    /**
     * Set reminder AJAX
     */
    private function set_reminder_ajax() {
        $stream_id = intval($_POST['stream_id'] ?? 0);
        $reminder_time = sanitize_text_field($_POST['reminder_time'] ?? '15');
        
        $user_id = get_current_user_id();
        
        if ($this->set_stream_reminder($user_id, $stream_id, $reminder_time)) {
            wp_send_json_success('Reminder set successfully');
        } else {
            wp_send_json_error('Failed to set reminder');
        }
    }
    
    /**
     * Get stats AJAX
     */
    private function get_stats_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $period = sanitize_text_field($_POST['period'] ?? 'month');
        
        $stats = $this->get_stream_stats($channel, $period);
        
        wp_send_json_success($stats);
    }
    
    /**
     * Add stream scheduler menu
     */
    public function add_stream_scheduler_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Stream Scheduler',
            'Stream Scheduler',
            'edit_posts',
            'twitch-stream-scheduler',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Stream Scheduler</h1>
            
            <div class="twitch-scheduler-admin-notice">
                <p>Schedule and manage your Twitch streams with this visual calendar interface. Create recurring streams, set reminders, and track your streaming schedule.</p>
            </div>
            
            <?php echo do_shortcode('[twitch_stream_scheduler channel="" view="calendar" allow_booking="false"]'); ?>
            
            <div class="twitch-scheduler-settings">
                <h2>Scheduler Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('twitch_scheduler_settings'); ?>
                    <?php do_settings_sections('twitch_scheduler_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Scheduler</th>
                            <td>
                                <input type="checkbox" name="twitch_scheduler_settings[enabled]" 
                                       <?php checked($this->scheduler_settings['enabled'], true); ?> />
                                <label>Enable the stream scheduler feature</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Default Timezone</th>
                            <td>
                                <select name="twitch_scheduler_settings[default_timezone]">
                                    <?php foreach ($this->get_supported_timezones() as $tz_key => $tz_name): ?>
                                        <option value="<?php echo esc_attr($tz_key); ?>" 
                                                <?php selected($this->scheduler_settings['default_timezone'], $tz_key); ?>>
                                            <?php echo esc_html($tz_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Allow Public Booking</th>
                            <td>
                                <input type="checkbox" name="twitch_scheduler_settings[allow_public_booking]" 
                                       <?php checked($this->scheduler_settings['allow_public_booking'], true); ?> />
                                <label>Allow visitors to request stream times</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Email Notifications</th>
                            <td>
                                <input type="checkbox" name="twitch_scheduler_settings[email_notifications]" 
                                       <?php checked($this->scheduler_settings['email_notifications'], true); ?> />
                                <label>Send email notifications for scheduled streams</label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Auto-sync with Twitch</th>
                            <td>
                                <input type="checkbox" name="twitch_scheduler_settings[auto_sync_twitch]" 
                                       <?php checked($this->scheduler_settings['auto_sync_twitch'], true); ?> />
                                <label>Automatically sync stream status with Twitch API</label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue stream scheduler scripts
     */
    public function enqueue_stream_scheduler_scripts() {
        wp_enqueue_style(
            'twitch-stream-scheduler',
            WP_TWITCH_PLUGIN_URL . 'assets/css/stream-scheduler.css',
            array(),
            WP_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-stream-scheduler',
            WP_TWITCH_PLUGIN_URL . 'assets/js/stream-scheduler.js',
            array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
            WP_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-stream-scheduler', 'twitchStreamScheduler', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('twitch_stream_scheduler_nonce'),
            'currentUser' => get_current_user_id(),
            'timezones' => $this->get_supported_timezones(),
            'streamTypes' => $this->get_stream_types(),
            'categories' => $this->get_stream_categories(),
            'settings' => $this->scheduler_settings,
            'strings' => array(
                'saveSuccess' => 'Stream saved successfully!',
                'saveError' => 'Failed to save stream',
                'deleteSuccess' => 'Stream deleted successfully',
                'deleteConfirm' => 'Are you sure you want to delete this stream?',
                'reminderSet' => 'Reminder set successfully',
                'loading' => 'Loading...',
                'noStreams' => 'No streams found',
                'dragToReschedule' => 'Drag to reschedule',
                'clickToEdit' => 'Click to edit',
                'doubleClickToEdit' => 'Double-click to edit'
            )
        ));
    }
    
    /**
     * Get scheduler settings
     */
    private function get_scheduler_settings() {
        return get_option('twitch_scheduler_settings', array(
            'enabled' => true,
            'default_timezone' => wp_timezone_string(),
            'allow_public_booking' => false,
            'email_notifications' => true,
            'auto_sync_twitch' => true,
            'default_duration' => 120, // minutes
            'max_advance_booking' => 90, // days
            'reminder_times' => array(15, 30, 60, 1440), // minutes
            'calendar_views' => array('month', 'week', 'day'),
            'working_hours' => array('start' => '09:00', 'end' => '23:00')
        ));
    }
    
    /**
     * Get stream schedules
     */
    private function get_stream_schedules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_time ASC");
    }
    
    /**
     * Get recurring patterns
     */
    private function get_recurring_patterns() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_recurring_patterns';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    }
    
    /**
     * Get supported timezones
     */
    public function get_supported_timezones() {
        return array(
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Berlin' => 'Berlin',
            'Europe/Paris' => 'Paris',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Australia/Sydney' => 'Sydney',
        );
    }
    
    /**
     * Get stream types
     */
    public function get_stream_types() {
        return array(
            'live' => array(
                'name' => 'Live Stream',
                'icon' => '🔴',
                'color' => '#ff0000'
            ),
            'premiere' => array(
                'name' => 'Premiere',
                'icon' => '🎬',
                'color' => '#ff6b6b'
            ),
            'rerun' => array(
                'name' => 'Rerun',
                'icon' => '🔄',
                'color' => '#6b6bff'
            )
        );
    }
    
    /**
     * Get stream categories
     */
    public function get_stream_categories() {
        return array(
            'gaming' => array(
                'name' => 'Gaming',
                'icon' => '🎮',
                'color' => '#9146ff'
            ),
            'talk' => array(
                'name' => 'Talk Show',
                'icon' => '💬',
                'color' => '#00ff88'
            ),
            'music' => array(
                'name' => 'Music',
                'icon' => '🎵',
                'color' => '#ff0088'
            ),
            'creative' => array(
                'name' => 'Creative',
                'icon' => '🎨',
                'color' => '#ffaa00'
            ),
            'educational' => array(
                'name' => 'Educational',
                'icon' => '📚',
                'color' => '#00aaff'
            ),
            'community' => array(
                'name' => 'Community',
                'icon' => '👥',
                'color' => '#aa00ff'
            )
        );
    }
    
    /**
     * Helper methods
     */
    private function get_current_user_channel() {
        // Get user's Twitch channel from user meta or settings
        return get_user_meta(get_current_user_id(), 'twitch_channel', true) ?: '';
    }
    
    private function get_upcoming_streams($channel = '', $limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $where = "WHERE start_time > NOW() AND status = 'scheduled'";
        if ($channel) {
            $where .= $wpdb->prepare(" AND channel = %s", $channel);
        }
        
        $sql = "SELECT * FROM $table_name $where ORDER BY start_time ASC LIMIT %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $limit));
    }
    
    private function get_filtered_streams($channel, $start_date, $end_date, $status, $category, $limit) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $where = array("1=1");
        $values = array();
        
        if ($channel) {
            $where[] = "channel = %s";
            $values[] = $channel;
        }
        
        if ($start_date) {
            $where[] = "start_time >= %s";
            $values[] = $start_date . ' 00:00:00';
        }
        
        if ($end_date) {
            $where[] = "start_time <= %s";
            $values[] = $end_date . ' 23:59:59';
        }
        
        if ($status) {
            $statuses = explode(',', $status);
            $placeholders = str_repeat('%s,', count($statuses) - 1) . '%s';
            $where[] = "status IN ($placeholders)";
            $values = array_merge($values, $statuses);
        }
        
        if ($category) {
            $where[] = "category = %s";
            $values[] = $category;
        }
        
        $sql = "SELECT * FROM $table_name WHERE " . implode(' AND ', $where) . " ORDER BY start_time ASC LIMIT %d";
        $values[] = $limit;
        
        return $wpdb->get_results($wpdb->prepare($sql, $values));
    }
    
    private function validate_stream_data($data) {
        $errors = array();
        
        if (empty($data['channel'])) {
            $errors[] = 'Channel is required';
        }
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['start_time']) || empty($data['end_time'])) {
            $errors[] = 'Start and end times are required';
        } elseif (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
            $errors[] = 'End time must be after start time';
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
    
    private function save_stream($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $data['created_by'] = get_current_user_id();
        $data['updated_at'] = current_time('mysql');
        
        if (isset($data['id'])) {
            // Update existing
            $wpdb->update($table_name, $data, array('id' => $data['id']));
            return $data['id'];
        } else {
            // Insert new
            $wpdb->insert($table_name, $data);
            return $wpdb->insert_id;
        }
    }
    
    private function delete_stream($stream_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        return $wpdb->delete($table_name, array('id' => $stream_id));
    }
    
    private function update_stream($stream_id, $updates) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        return $wpdb->update($table_name, $updates, array('id' => $stream_id));
    }
    
    private function get_calendar_events($channel, $start_date, $end_date) {
        $streams = $this->get_filtered_streams($channel, $start_date, $end_date, '', '', 1000);
        
        $events = array();
        foreach ($streams as $stream) {
            $category_info = $this->get_stream_categories()[$stream->category] ?? array('name' => 'General', 'color' => '#9146ff');
            
            $events[] = array(
                'id' => $stream->id,
                'title' => $stream->title,
                'start' => $stream->start_time,
                'end' => $stream->end_time,
                'description' => $stream->description,
                'channel' => $stream->channel,
                'category' => $stream->category,
                'status' => $stream->status,
                'backgroundColor' => $category_info['color'],
                'borderColor' => $category_info['color'],
                'textColor' => '#ffffff',
                'className' => 'twitch-stream-event twitch-status-' . $stream->status
            );
        }
        
        return $events;
    }
    
    private function bulk_update_streams($stream_ids, $updates) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $updated = 0;
        foreach ($stream_ids as $stream_id) {
            if ($this->update_stream($stream_id, $updates)) {
                $updated++;
            }
        }
        
        return $updated;
    }
    
    private function set_stream_reminder($user_id, $stream_id, $reminder_time) {
        // Store reminder in user meta
        $reminders = get_user_meta($user_id, 'twitch_stream_reminders', true) ?: array();
        $reminders[$stream_id] = array(
            'reminder_time' => $reminder_time,
            'set_at' => current_time('mysql')
        );
        
        return update_user_meta($user_id, 'twitch_stream_reminders', $reminders);
    }
    
    private function get_stream_stats($channel, $period = 'month') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $date_format = $period === 'month' ? 'DATE_FORMAT(start_time, "%Y-%m")' : 'DATE_FORMAT(start_time, "%Y-%m-%d")';
        
        $where = $channel ? $wpdb->prepare("WHERE channel = %s", $channel) : "WHERE 1=1";
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_streams,
                SUM(CASE WHEN status = 'live' THEN 1 ELSE 0 END) as live_streams,
                SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_minutes
            FROM $table_name 
            $where AND start_time >= DATE_SUB(NOW(), INTERVAL 1 $period)
        ");
        
        return array(
            'total_streams' => intval($stats->total_streams),
            'live_streams' => intval($stats->live_streams),
            'hours_streamed' => round($stats->total_minutes / 60, 1)
        );
    }
    
    private function get_category_name($category_key) {
        $categories = $this->get_stream_categories();
        return $categories[$category_key]['name'] ?? 'General';
    }
    
    private function format_duration($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } else {
            return sprintf('%dm', $minutes);
        }
    }
    
    private function handle_stream_started($channel, $stream_data) {
        // Update stream status when it goes live
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $wpdb->update(
            $table_name,
            array('status' => 'live', 'twitch_stream_id' => $stream_data['id']),
            array(
                'channel' => $channel,
                'status' => 'scheduled',
                'start_time' <= current_time('mysql'),
                'end_time' >= current_time('mysql')
            )
        );
    }
    
    private function handle_stream_ended($channel, $stream_data) {
        // Update stream status when it ends
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        $wpdb->update(
            $table_name,
            array('status' => 'completed'),
            array('twitch_stream_id' => $stream_data['id'])
        );
    }
    
    public function cleanup_old_schedules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'twitch_stream_schedules';
        
        // Delete completed streams older than 90 days
        $wpdb->query($wpdb->prepare("
            DELETE FROM $table_name 
            WHERE status = 'completed' 
            AND end_time < DATE_SUB(NOW(), INTERVAL 90 DAY)
        "));
        
        // Delete cancelled streams older than 30 days
        $wpdb->query($wpdb->prepare("
            DELETE FROM $table_name 
            WHERE status = 'cancelled' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        "));
    }
}

// Initialize visual stream scheduler
new WP_Twitch_Visual_Stream_Scheduler();
