<?php
/**
 * Advanced Caching Options for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Cache {
    
    private $cache_settings;
    private $cache_engine;
    
    public function __construct() {
        // Delay initialization until WordPress is loaded
        add_action('init', array($this, 'init'));
    }

    public function init() {
        $this->cache_settings = $this->get_cache_settings();
        $this->cache_engine = $this->init_cache_engine();

        add_action('init', array($this, 'schedule_cache_cleanup'));
        add_action('spswifter_spswifter_twitch_cleanup_cache', array($this, 'cleanup_expired_cache'));
        add_action('wp_ajax_spswifter_twitch_cache_management', array($this, 'handle_cache_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_cache_management', array($this, 'handle_cache_ajax'));
    }
    
    /**
     * Initialize cache engine
     */
    private function init_cache_engine() {
        $engine = $this->cache_settings['engine'] ?? 'transient';
        
        switch ($engine) {
            case 'redis':
                return new SPSWIFTER_Twitch_Cache_Redis();
            case 'memcached':
                return new SPSWIFTER_Twitch_Cache_Memcached();
            case 'file':
                return new SPSWIFTER_Twitch_Cache_File();
            case 'transient':
            default:
                return new SPSWIFTER_Twitch_Cache_Transient();
        }
    }
    
    /**
     * Get cache value
     */
    public function get($key, $default = false) {
        return $this->cache_engine->get($key, $default);
    }
    
    /**
     * Set cache value
     */
    public function set($key, $value, $expiration = 300) {
        return $this->cache_engine->set($key, $value, $expiration);
    }
    
    /**
     * Delete cache value
     */
    public function delete($key) {
        return $this->cache_engine->delete($key);
    }
    
    /**
     * Clear all cache
     */
    public function clear_all() {
        return $this->cache_engine->clear_all();
    }
    
    /**
     * Get cache status
     */
    public function get_cache_status() {
        return $this->cache_engine->get_status();
    }
    
    /**
     * Warm cache
     */
    public function warm_cache() {
        $channels = $this->get_cache_channels();
        
        foreach ($channels as $channel) {
            $this->warm_channel_cache($channel);
        }
    }
    
    /**
     * Warm channel cache
     */
    private function warm_channel_cache($channel) {
        $api = new SPSWIFTER_Twitch_API();
        
        // Cache stream data
        $stream_data = $api->get_complete_stream_info($channel);
        if ($stream_data) {
            $this->set("spswifter_twitch_stream_{$channel}", $stream_data, $this->get_cache_duration('stream'));
        }
        
        // Cache user info
        $user_info = $api->get_user_info($channel);
        if ($user_info) {
            $this->set("spswifter_twitch_user_{$channel}", $user_info, $this->get_cache_duration('user'));
        }
        
        // Cache videos
        $videos = $api->get_channel_videos($channel, 20, 'archive');
        if ($videos) {
            $this->set("spswifter_twitch_videos_{$channel}", $videos, $this->get_cache_duration('videos'));
        }
        
        // Cache clips
        $clips = $api->get_channel_clips($channel, 20);
        if ($clips) {
            $this->set("spswifter_twitch_clips_{$channel}", $clips, $this->get_cache_duration('clips'));
        }
    }
    
    /**
     * Get cache channels
     */
    private function get_cache_channels() {
        return $this->cache_settings['channels'] ?? array();
    }
    
    /**
     * Get cache duration
     */
    private function get_cache_duration($type) {
        $durations = $this->cache_settings['durations'] ?? array();
        
        switch ($type) {
            case 'stream':
                return $durations['stream'] ?? 300; // 5 minutes
            case 'user':
                return $durations['user'] ?? 3600; // 1 hour
            case 'videos':
                return $durations['videos'] ?? 1800; // 30 minutes
            case 'clips':
                return $durations['clips'] ?? 1800; // 30 minutes
            case 'analytics':
                return $durations['analytics'] ?? 300; // 5 minutes
            default:
                return 300;
        }
    }
    
    /**
     * Schedule cache cleanup
     */
    public function schedule_cache_cleanup() {
        if (!wp_next_scheduled('spswifter_spswifter_twitch_cleanup_cache')) {
            $interval = $this->cache_settings['cleanup_interval'] ?? 'hourly';
            wp_schedule_event(time(), $interval, 'spswifter_spswifter_twitch_cleanup_cache');
        }
    }
    
    /**
     * Cleanup expired cache
     */
    public function cleanup_expired_cache() {
        $this->cache_engine->cleanup_expired();
        
        // Log cleanup
        $this->log_cache_event('cleanup_completed', array(
            'timestamp' => current_time('mysql'),
            'engine' => $this->cache_settings['engine'],
        ));
    }
    
    /**
     * Handle cache AJAX
     */
    public function handle_cache_ajax() {
        check_ajax_referer('spswifter_twitch_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $action = $_POST['cache_action'] ?? '';
        
        switch ($action) {
            case 'get_status':
                $this->get_cache_status_ajax();
                break;
            case 'clear_cache':
                $this->clear_cache_ajax();
                break;
            case 'warm_cache':
                $this->warm_cache_ajax();
                break;
            case 'get_statistics':
                $this->get_cache_statistics_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get cache status AJAX
     */
    private function get_cache_status_ajax() {
        $status = $this->get_cache_status();
        
        wp_send_json_success(array('status' => $status));
    }
    
    /**
     * Clear cache AJAX
     */
    private function clear_cache_ajax() {
        $cache_type = $_POST['cache_type'] ?? 'all';
        
        switch ($cache_type) {
            case 'all':
                $result = $this->clear_all();
                break;
            case 'stream':
                $result = $this->clear_stream_cache();
                break;
            case 'user':
                $result = $this->clear_user_cache();
                break;
            case 'videos':
                $result = $this->clear_videos_cache();
                break;
            case 'clips':
                $result = $this->clear_clips_cache();
                break;
            case 'analytics':
                $result = $this->clear_analytics_cache();
                break;
            default:
                wp_send_json_error('Invalid cache type');
        }
        
        if ($result) {
            wp_send_json_success(array('message' => 'Cache cleared successfully'));
        } else {
            wp_send_json_error('Failed to clear cache');
        }
    }
    
    /**
     * Warm cache AJAX
     */
    private function warm_cache_ajax() {
        $this->warm_cache();
        
        wp_send_json_success(array('message' => 'Cache warmed successfully'));
    }
    
    /**
     * Get cache statistics AJAX
     */
    private function get_cache_statistics_ajax() {
        $statistics = $this->get_cache_statistics();
        
        wp_send_json_success(array('statistics' => $statistics));
    }
    
    /**
     * Clear stream cache
     */
    private function clear_stream_cache() {
        $channels = $this->get_cache_channels();
        $cleared = 0;
        
        foreach ($channels as $channel) {
            if ($this->delete("spswifter_twitch_stream_{$channel}")) {
                $cleared++;
            }
        }
        
        return $cleared > 0;
    }
    
    /**
     * Clear user cache
     */
    private function clear_user_cache() {
        $channels = $this->get_cache_channels();
        $cleared = 0;
        
        foreach ($channels as $channel) {
            if ($this->delete("spswifter_twitch_user_{$channel}")) {
                $cleared++;
            }
        }
        
        return $cleared > 0;
    }
    
    /**
     * Clear videos cache
     */
    private function clear_videos_cache() {
        $channels = $this->get_cache_channels();
        $cleared = 0;
        
        foreach ($channels as $channel) {
            if ($this->delete("spswifter_twitch_videos_{$channel}")) {
                $cleared++;
            }
        }
        
        return $cleared > 0;
    }
    
    /**
     * Clear clips cache
     */
    private function clear_clips_cache() {
        $channels = $this->get_cache_channels();
        $cleared = 0;
        
        foreach ($channels as $channel) {
            if ($this->delete("spswifter_twitch_clips_{$channel}")) {
                $cleared++;
            }
        }
        
        return $cleared > 0;
    }
    
    /**
     * Clear analytics cache
     */
    private function clear_analytics_cache() {
        $channels = $this->get_cache_channels();
        $cleared = 0;
        
        foreach ($channels as $channel) {
            foreach (array('day', 'week', 'month', 'year') as $period) {
                if ($this->delete("spswifter_twitch_analytics_{$channel}_{$period}")) {
                    $cleared++;
                }
            }
        }
        
        return $cleared > 0;
    }
    
    /**
     * Get cache statistics
     */
    public function get_cache_statistics() {
        $statistics = array(
            'engine' => $this->cache_settings['engine'],
            'total_keys' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'hit_rate' => 0,
            'memory_usage' => 0,
            'oldest_entry' => null,
            'newest_entry' => null,
        );
        
        // Get statistics from cache engine
        $engine_stats = $this->cache_engine->get_statistics();
        $statistics = array_merge($statistics, $engine_stats);
        
        // Calculate hit rate
        if ($statistics['cache_hits'] + $statistics['cache_misses'] > 0) {
            $statistics['hit_rate'] = ($statistics['cache_hits'] / ($statistics['cache_hits'] + $statistics['cache_misses'])) * 100;
        }
        
        return $statistics;
    }
    
    /**
     * Get cache settings
     */
    private function get_cache_settings() {
        return get_option('spswifter_twitch_cache_settings', array(
            'engine' => 'transient',
            'enabled' => true,
            'channels' => array(),
            'durations' => array(
                'stream' => 300,
                'user' => 3600,
                'videos' => 1800,
                'clips' => 1800,
                'analytics' => 300,
            ),
            'cleanup_interval' => 'hourly',
            'compression' => false,
            'encryption' => false,
            'cache_tags' => true,
            'cache_groups' => true,
        ));
    }
    
    /**
     * Log cache event
     */
    private function log_cache_event($event, $data) {
        $logs = get_option('spswifter_twitch_cache_logs', array());
        
        $logs[] = array(
            'event' => $event,
            'data' => $data,
            'timestamp' => current_time('mysql'),
        );
        
        // Keep only last 1000 logs
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -500);
        }
        
        update_option('spswifter_twitch_cache_logs', $logs);
    }
    
    /**
     * Get cache logs
     */
    public function get_cache_logs($limit = 100) {
        $logs = get_option('spswifter_twitch_cache_logs', array());
        
        // Sort by timestamp descending
        usort($logs, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($logs, 0, $limit);
    }
    
    /**
     * Optimize cache
     */
    public function optimize_cache() {
        $this->cache_engine->optimize();
        
        $this->log_cache_event('optimization_completed', array(
            'timestamp' => current_time('mysql'),
            'engine' => $this->cache_settings['engine'],
        ));
    }
    
    /**
     * Get cache size
     */
    public function get_cache_size() {
        return $this->cache_engine->get_size();
    }
    
    /**
     * Export cache configuration
     */
    public function export_configuration() {
        return array(
            'settings' => $this->cache_settings,
            'statistics' => $this->get_cache_statistics(),
            'logs' => $this->get_cache_logs(50),
        );
    }
    
    /**
     * Import cache configuration
     */
    public function import_configuration($config) {
        if (isset($config['settings'])) {
            update_option('spswifter_twitch_cache_settings', $config['settings']);
            $this->cache_settings = $config['settings'];
            $this->cache_engine = $this->init_cache_engine();
        }
        
        return true;
    }
}

/**
 * Transient Cache Engine
 */
class SPSWIFTER_Twitch_Cache_Transient {
    
    public function get($key, $default = false) {
        return get_transient($key, $default);
    }
    
    public function set($key, $value, $expiration = 300) {
        return set_transient($key, $value, $expiration);
    }
    
    public function delete($key) {
        return delete_transient($key);
    }
    
    public function clear_all() {
        global $wpdb;
        
        $prefix = '_transient_spswifter_twitch_';
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                $prefix . '%'
            )
        );
        
        return $result !== false;
    }
    
    public function get_status() {
        global $wpdb;
        
        $prefix = '_transient_spswifter_twitch_';
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s",
                $prefix . '%'
            )
        );
        
        return array(
            'engine' => 'transient',
            'total_keys' => intval($count),
            'cache_hits' => 0,
            'cache_misses' => 0,
            'memory_usage' => 0,
        );
    }
    
    public function cleanup_expired() {
        // WordPress automatically cleans expired transients
        return true;
    }
    
    public function get_statistics() {
        return array();
    }
    
    public function optimize() {
        return true;
    }
    
    public function get_size() {
        global $wpdb;
        
        $prefix = '_transient_spswifter_twitch_';
        $size = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(LENGTH(option_value)) FROM $wpdb->options WHERE option_name LIKE %s",
                $prefix . '%'
            )
        );
        
        return intval($size);
    }
}

/**
 * File Cache Engine
 */
class SPSWIFTER_Twitch_Cache_File {
    
    private $cache_dir;
    
    public function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/twitch-stream/';
        
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }
    
    public function get($key, $default = false) {
        $file_path = $this->get_file_path($key);
        
        if (!file_exists($file_path)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file_path));
        
        if ($data['expires'] < time()) {
            unlink($file_path);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $expiration = 300) {
        $file_path = $this->get_file_path($key);
        
        $data = array(
            'value' => $value,
            'expires' => time() + $expiration,
            'created' => time(),
        );
        
        return file_put_contents($file_path, serialize($data)) !== false;
    }
    
    public function delete($key) {
        $file_path = $this->get_file_path($key);
        
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return true;
    }
    
    public function clear_all() {
        $files = glob($this->cache_dir . '*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted > 0;
    }
    
    public function get_status() {
        $files = glob($this->cache_dir . '*');
        $total_size = 0;
        $expired_count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
                
                $data = unserialize(file_get_contents($file));
                if ($data['expires'] < time()) {
                    $expired_count++;
                }
            }
        }
        
        return array(
            'engine' => 'file',
            'total_keys' => count($files),
            'cache_hits' => 0,
            'cache_misses' => 0,
            'memory_usage' => $total_size,
            'expired_keys' => $expired_count,
        );
    }
    
    public function cleanup_expired() {
        $files = glob($this->cache_dir . '*');
        $cleaned = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                
                if ($data['expires'] < time()) {
                    unlink($file);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    public function get_statistics() {
        return array();
    }
    
    public function optimize() {
        return true;
    }
    
    public function get_size() {
        $files = glob($this->cache_dir . '*');
        $total_size = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
            }
        }
        
        return $total_size;
    }
    
    private function get_file_path($key) {
        return $this->cache_dir . md5($key) . '.cache';
    }
}

/**
 * Redis Cache Engine (placeholder)
 */
class SPSWIFTER_Twitch_Cache_Redis {
    
    public function get($key, $default = false) {
        // Redis implementation would go here
        return $default;
    }
    
    public function set($key, $value, $expiration = 300) {
        // Redis implementation would go here
        return false;
    }
    
    public function delete($key) {
        // Redis implementation would go here
        return false;
    }
    
    public function clear_all() {
        // Redis implementation would go here
        return false;
    }
    
    public function get_status() {
        return array(
            'engine' => 'redis',
            'total_keys' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'memory_usage' => 0,
        );
    }
    
    public function cleanup_expired() {
        return true;
    }
    
    public function get_statistics() {
        return array();
    }
    
    public function optimize() {
        return true;
    }
    
    public function get_size() {
        return 0;
    }
}

/**
 * Memcached Cache Engine (placeholder)
 */
class SPSWIFTER_Twitch_Cache_Memcached {
    
    public function get($key, $default = false) {
        // Memcached implementation would go here
        return $default;
    }
    
    public function set($key, $value, $expiration = 300) {
        // Memcached implementation would go here
        return false;
    }
    
    public function delete($key) {
        // Memcached implementation would go here
        return false;
    }
    
    public function clear_all() {
        // Memcached implementation would go here
        return false;
    }
    
    public function get_status() {
        return array(
            'engine' => 'memcached',
            'total_keys' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'memory_usage' => 0,
        );
    }
    
    public function cleanup_expired() {
        return true;
    }
    
    public function get_statistics() {
        return array();
    }
    
    public function optimize() {
        return true;
    }
    
    public function get_size() {
        return 0;
    }
}

// Initialize advanced cache
new SPSWIFTER_Twitch_Cache();
