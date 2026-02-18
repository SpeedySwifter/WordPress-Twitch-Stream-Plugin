<?php
/**
 * Stream Recording Integration for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Stream_Recording {
    
    private $api;
    private $recording_settings;
    
    public function __construct() {
        $this->api = new WP_Twitch_API();
        $this->recording_settings = get_option('twitch_recording_settings', array());
        
        add_action('init', array($this, 'schedule_recording_checks'));
        add_action('wp_twitch_check_recordings', array($this, 'check_and_record_streams'));
        add_action('wp_twitch_process_recording', array($this, 'process_stream_recording'));
        add_action('wp_ajax_twitch_recording_settings', array($this, 'handle_ajax_settings'));
        add_action('wp_ajax_nopriv_twitch_recording_settings', array($this, 'handle_ajax_settings'));
    }
    
    /**
     * Schedule recording checks
     */
    public function schedule_recording_checks() {
        if (!wp_next_scheduled('wp_twitch_check_recordings')) {
            $interval = $this->recording_settings['check_interval'] ?? 'every_minute';
            
            switch ($interval) {
                case 'every_30_seconds':
                    wp_schedule_event(time(), 'wp_twitch_30_seconds', 'wp_twitch_check_recordings');
                    break;
                case 'every_minute':
                    wp_schedule_event(time(), 'wp_twitch_minute', 'wp_twitch_check_recordings');
                    break;
                case 'every_5_minutes':
                    wp_schedule_event(time(), 'wp_twitch_5_minutes', 'wp_twitch_check_recordings');
                    break;
                default:
                    wp_schedule_event(time(), 'wp_twitch_minute', 'wp_twitch_check_recordings');
            }
        }
    }
    
    /**
     * Check and record streams
     */
    public function check_and_record_streams() {
        $channels = $this->get_recording_channels();
        
        foreach ($channels as $channel) {
            $this->check_channel_recording($channel);
        }
    }
    
    /**
     * Check channel recording
     */
    private function check_channel_recording($channel) {
        $stream_data = $this->api->get_complete_stream_info($channel);
        
        if (!$stream_data) {
            return;
        }
        
        $is_live = $stream_data['is_live'];
        $recording_status = $this->get_recording_status($channel);
        
        if ($is_live && !$recording_status['is_recording']) {
            $this->start_recording($channel, $stream_data);
        } elseif (!$is_live && $recording_status['is_recording']) {
            $this->stop_recording($channel, $stream_data);
        } elseif ($is_live && $recording_status['is_recording']) {
            $this->update_recording($channel, $stream_data);
        }
    }
    
    /**
     * Start recording
     */
    private function start_recording($channel, $stream_data) {
        $recording_id = $this->generate_recording_id();
        $started_at = current_time('mysql');
        
        $recording_data = array(
            'id' => $recording_id,
            'channel' => $channel,
            'title' => $stream_data['stream']['title'] ?? '',
            'game' => $stream_data['game']['name'] ?? '',
            'started_at' => $started_at,
            'ended_at' => null,
            'duration' => 0,
            'status' => 'recording',
            'file_path' => '',
            'file_size' => 0,
            'thumbnail_path' => '',
            'viewer_data' => array(),
            'chat_data' => array(),
            'metadata' => array(
                'stream_id' => $stream_data['stream']['id'] ?? '',
                'user_id' => $stream_data['user']['id'] ?? '',
                'language' => $stream_data['stream']['language'] ?? '',
                'tags' => $stream_data['stream']['tags'] ?? array(),
            ),
        );
        
        $this->save_recording($recording_data);
        $this->update_recording_status($channel, true, $recording_id);
        
        // Start background recording process
        wp_schedule_single_event(time(), 'wp_twitch_process_recording', array($recording_id));
        
        // Log recording start
        $this->log_recording_event($channel, 'recording_started', $recording_data);
        
        // Trigger action for custom handling
        do_action('wp_twitch_recording_started', $channel, $recording_id, $stream_data);
    }
    
    /**
     * Stop recording
     */
    private function stop_recording($channel, $stream_data) {
        $recording_status = $this->get_recording_status($channel);
        $recording_id = $recording_status['recording_id'];
        
        if (!$recording_id) {
            return;
        }
        
        $recording_data = $this->get_recording($recording_id);
        if (!$recording_data) {
            return;
        }
        
        $ended_at = current_time('mysql');
        $duration = $this->calculate_recording_duration($recording_data['started_at'], $ended_at);
        
        $recording_data['ended_at'] = $ended_at;
        $recording_data['duration'] = $duration;
        $recording_data['status'] = 'processing';
        
        $this->save_recording($recording_data);
        $this->update_recording_status($channel, false);
        
        // Process final recording
        wp_schedule_single_event(time(), 'wp_twitch_process_recording', array($recording_id));
        
        // Log recording stop
        $this->log_recording_event($channel, 'recording_stopped', $recording_data);
        
        // Trigger action for custom handling
        do_action('wp_twitch_recording_stopped', $channel, $recording_id, $recording_data);
    }
    
    /**
     * Update recording
     */
    private function update_recording($channel, $stream_data) {
        $recording_status = $this->get_recording_status($channel);
        $recording_id = $recording_status['recording_id'];
        
        if (!$recording_id) {
            return;
        }
        
        $recording_data = $this->get_recording($recording_id);
        if (!$recording_data) {
            return;
        }
        
        $current_time = current_time('mysql');
        $duration = $this->calculate_recording_duration($recording_data['started_at'], $current_time);
        
        // Update viewer data
        $viewer_data = array(
            'timestamp' => $current_time,
            'viewers' => $stream_data['stream']['viewer_count'] ?? 0,
            'game' => $stream_data['game']['name'] ?? '',
            'title' => $stream_data['stream']['title'] ?? '',
        );
        
        $recording_data['duration'] = $duration;
        $recording_data['viewer_data'][] = $viewer_data;
        
        // Limit viewer data to prevent memory issues
        if (count($recording_data['viewer_data']) > 1000) {
            $recording_data['viewer_data'] = array_slice($recording_data['viewer_data'], -500);
        }
        
        $this->save_recording($recording_data);
        
        // Trigger action for custom handling
        do_action('wp_twitch_recording_updated', $channel, $recording_id, $recording_data);
    }
    
    /**
     * Process stream recording
     */
    public function process_stream_recording($recording_id) {
        $recording_data = $this->get_recording($recording_id);
        
        if (!$recording_data) {
            return;
        }
        
        $processing_method = $this->recording_settings['processing_method'] ?? 'local';
        
        switch ($processing_method) {
            case 'local':
                $this->process_local_recording($recording_data);
                break;
            case 'cloud':
                $this->process_cloud_recording($recording_data);
                break;
            case 'hybrid':
                $this->process_hybrid_recording($recording_data);
                break;
        }
    }
    
    /**
     * Process local recording
     */
    private function process_local_recording($recording_data) {
        $channel = $recording_data['channel'];
        $recording_id = $recording_data['id'];
        
        // Generate thumbnail
        $thumbnail_path = $this->generate_recording_thumbnail($recording_data);
        
        // Create highlight clips
        $clips = $this->generate_highlight_clips($recording_data);
        
        // Generate statistics
        $statistics = $this->generate_recording_statistics($recording_data);
        
        // Update recording data
        $recording_data['thumbnail_path'] = $thumbnail_path;
        $recording_data['clips'] = $clips;
        $recording_data['statistics'] = $statistics;
        $recording_data['status'] = 'completed';
        $recording_data['processed_at'] = current_time('mysql');
        
        $this->save_recording($recording_data);
        
        // Trigger action for custom handling
        do_action('wp_twitch_recording_processed', $channel, $recording_id, $recording_data);
    }
    
    /**
     * Process cloud recording
     */
    private function process_cloud_recording($recording_data) {
        $channel = $recording_data['channel'];
        $recording_id = $recording_data['id'];
        
        // Upload to cloud storage
        $cloud_url = $this->upload_to_cloud($recording_data);
        
        // Generate cloud thumbnail
        $cloud_thumbnail = $this->generate_cloud_thumbnail($recording_data);
        
        // Update recording data
        $recording_data['file_path'] = $cloud_url;
        $recording_data['thumbnail_path'] = $cloud_thumbnail;
        $recording_data['status'] = 'completed';
        $recording_data['processed_at'] = current_time('mysql');
        
        $this->save_recording($recording_data);
        
        // Trigger action for custom handling
        do_action('wp_twitch_recording_cloud_processed', $channel, $recording_id, $recording_data);
    }
    
    /**
     * Process hybrid recording
     */
    private function process_hybrid_recording($recording_data) {
        $channel = $recording_data['channel'];
        $recording_id = $recording_data['id'];
        
        // Process locally first
        $this->process_local_recording($recording_data);
        
        // Then upload to cloud
        $cloud_url = $this->upload_to_cloud($recording_data);
        
        // Update recording data
        $recording_data['file_path'] = $cloud_url;
        $recording_data['status'] = 'completed';
        $recording_data['processed_at'] = current_time('mysql');
        
        $this->save_recording($recording_data);
        
        // Trigger action for custom handling
        do_action('wp_twitch_recording_hybrid_processed', $channel, $recording_id, $recording_data);
    }
    
    /**
     * Get recording channels
     */
    private function get_recording_channels() {
        return $this->recording_settings['channels'] ?? array();
    }
    
    /**
     * Get recording status
     */
    private function get_recording_status($channel) {
        $statuses = get_option('twitch_recording_statuses', array());
        return $statuses[$channel] ?? array('is_recording' => false, 'recording_id' => null);
    }
    
    /**
     * Update recording status
     */
    private function update_recording_status($channel, $is_recording, $recording_id = null) {
        $statuses = get_option('twitch_recording_statuses', array());
        
        $statuses[$channel] = array(
            'is_recording' => $is_recording,
            'recording_id' => $recording_id,
            'updated_at' => current_time('mysql'),
        );
        
        update_option('twitch_recording_statuses', $statuses);
    }
    
    /**
     * Generate recording ID
     */
    private function generate_recording_id() {
        return 'rec_' . uniqid() . '_' . time();
    }
    
    /**
     * Save recording
     */
    private function save_recording($recording_data) {
        $recordings = get_option('twitch_recordings', array());
        $recordings[$recording_data['id']] = $recording_data;
        
        // Keep only last 100 recordings
        if (count($recordings) > 100) {
            $recordings = array_slice($recordings, -100, null, true);
        }
        
        update_option('twitch_recordings', $recordings);
    }
    
    /**
     * Get recording
     */
    private function get_recording($recording_id) {
        $recordings = get_option('twitch_recordings', array());
        return $recordings[$recording_id] ?? null;
    }
    
    /**
     * Calculate recording duration
     */
    private function calculate_recording_duration($started_at, $ended_at) {
        $start = strtotime($started_at);
        $end = strtotime($ended_at);
        
        return ($end - $start) / 60; // Return in minutes
    }
    
    /**
     * Log recording event
     */
    private function log_recording_event($channel, $event, $data) {
        $logs = get_option('twitch_recording_logs', array());
        
        $logs[] = array(
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'timestamp' => current_time('mysql'),
        );
        
        // Keep only last 1000 logs
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -500);
        }
        
        update_option('twitch_recording_logs', $logs);
    }
    
    /**
     * Generate recording thumbnail
     */
    private function generate_recording_thumbnail($recording_data) {
        $channel = $recording_data['channel'];
        $recording_id = $recording_data['id'];
        
        // Get current stream thumbnail
        $stream_data = $this->api->get_complete_stream_info($channel);
        $thumbnail_url = $stream_data['stream']['thumbnail_url'] ?? '';
        
        if (empty($thumbnail_url)) {
            return '';
        }
        
        // Download and save thumbnail
        $upload_dir = wp_upload_dir();
        $thumbnail_dir = $upload_dir['basedir'] . '/twitch-recordings/thumbnails/';
        $thumbnail_filename = $recording_id . '.jpg';
        $thumbnail_path = $thumbnail_dir . $thumbnail_filename;
        
        // Create directory if it doesn't exist
        if (!file_exists($thumbnail_dir)) {
            wp_mkdir_p($thumbnail_dir);
        }
        
        // Download thumbnail
        $response = wp_remote_get($thumbnail_url);
        
        if (!is_wp_error($response)) {
            $image_data = wp_remote_retrieve_body($response);
            file_put_contents($thumbnail_path, $image_data);
            
            return $upload_dir['baseurl'] . '/twitch-recordings/thumbnails/' . $thumbnail_filename;
        }
        
        return '';
    }
    
    /**
     * Generate highlight clips
     */
    private function generate_highlight_clips($recording_data) {
        $clips = array();
        $viewer_data = $recording_data['viewer_data'];
        
        if (empty($viewer_data)) {
            return $clips;
        }
        
        // Find moments with high viewer count
        $max_viewers = max(array_column($viewer_data, 'viewers'));
        $threshold = $max_viewers * 0.8; // 80% of max viewers
        
        $highlight_moments = array();
        
        foreach ($viewer_data as $index => $data) {
            if ($data['viewers'] >= $threshold) {
                $highlight_moments[] = array(
                    'timestamp' => $data['timestamp'],
                    'viewers' => $data['viewers'],
                    'title' => $data['title'],
                    'game' => $data['game'],
                    'index' => $index,
                );
            }
        }
        
        // Generate clip data for highlights
        foreach ($highlight_moments as $moment) {
            $clips[] = array(
                'id' => 'clip_' . uniqid(),
                'title' => 'Highlight: ' . $moment['title'],
                'timestamp' => $moment['timestamp'],
                'viewers' => $moment['viewers'],
                'game' => $moment['game'],
                'duration' => 30, // 30 seconds
                'type' => 'auto_highlight',
            );
        }
        
        return $clips;
    }
    
    /**
     * Generate recording statistics
     */
    private function generate_recording_statistics($recording_data) {
        $viewer_data = $recording_data['viewer_data'];
        
        if (empty($viewer_data)) {
            return array();
        }
        
        $viewers = array_column($viewer_data, 'viewers');
        $max_viewers = max($viewers);
        $min_viewers = min($viewers);
        $avg_viewers = array_sum($viewers) / count($viewers);
        
        // Calculate viewer growth
        $first_viewers = $viewers[0];
        $last_viewers = $viewers[count($viewers) - 1];
        $viewer_growth = $first_viewers > 0 ? (($last_viewers - $first_viewers) / $first_viewers) * 100 : 0;
        
        // Find peak moments
        $peak_moments = array();
        foreach ($viewer_data as $index => $data) {
            if ($data['viewers'] >= ($max_viewers * 0.9)) {
                $peak_moments[] = array(
                    'timestamp' => $data['timestamp'],
                    'viewers' => $data['viewers'],
                    'title' => $data['title'],
                );
            }
        }
        
        return array(
            'max_viewers' => $max_viewers,
            'min_viewers' => $min_viewers,
            'avg_viewers' => round($avg_viewers),
            'viewer_growth' => round($viewer_growth, 2),
            'peak_moments' => $peak_moments,
            'total_data_points' => count($viewer_data),
        );
    }
    
    /**
     * Upload to cloud
     */
    private function upload_to_cloud($recording_data) {
        // This would integrate with cloud storage services
        // For now, return a placeholder URL
        return 'https://cloud-storage.example.com/recordings/' . $recording_data['id'] . '.mp4';
    }
    
    /**
     * Generate cloud thumbnail
     */
    private function generate_cloud_thumbnail($recording_data) {
        // This would generate and upload thumbnail to cloud
        // For now, return a placeholder URL
        return 'https://cloud-storage.example.com/thumbnails/' . $recording_data['id'] . '.jpg';
    }
    
    /**
     * Get all recordings
     */
    public function get_all_recordings($channel = null, $status = null, $limit = 50) {
        $recordings = get_option('twitch_recordings', array());
        $filtered_recordings = array();
        
        foreach ($recordings as $recording) {
            if ($channel && $recording['channel'] !== $channel) {
                continue;
            }
            
            if ($status && $recording['status'] !== $status) {
                continue;
            }
            
            $filtered_recordings[] = $recording;
        }
        
        // Sort by started_at descending
        usort($filtered_recordings, function($a, $b) {
            return strtotime($b['started_at']) - strtotime($a['started_at']);
        });
        
        return array_slice($filtered_recordings, 0, $limit);
    }
    
    /**
     * Get recording statistics
     */
    public function get_recording_statistics($channel = null) {
        $recordings = $this->get_all_recordings($channel);
        
        $stats = array(
            'total_recordings' => count($recordings),
            'total_duration' => 0,
            'total_viewers' => 0,
            'completed_recordings' => 0,
            'processing_recordings' => 0,
            'failed_recordings' => 0,
        );
        
        foreach ($recordings as $recording) {
            $stats['total_duration'] += $recording['duration'];
            
            if (isset($recording['statistics']['avg_viewers'])) {
                $stats['total_viewers'] += $recording['statistics']['avg_viewers'];
            }
            
            switch ($recording['status']) {
                case 'completed':
                    $stats['completed_recordings']++;
                    break;
                case 'processing':
                    $stats['processing_recordings']++;
                    break;
                case 'failed':
                    $stats['failed_recordings']++;
                    break;
            }
        }
        
        $stats['avg_duration'] = $stats['total_recordings'] > 0 ? $stats['total_duration'] / $stats['total_recordings'] : 0;
        $stats['avg_viewers'] = $stats['total_recordings'] > 0 ? $stats['total_viewers'] / $stats['total_recordings'] : 0;
        
        return $stats;
    }
    
    /**
     * Delete recording
     */
    public function delete_recording($recording_id) {
        $recordings = get_option('twitch_recordings', array());
        
        if (isset($recordings[$recording_id])) {
            $recording = $recordings[$recording_id];
            
            // Delete files
            if (!empty($recording['file_path'])) {
                $this->delete_recording_file($recording['file_path']);
            }
            
            if (!empty($recording['thumbnail_path'])) {
                $this->delete_recording_file($recording['thumbnail_path']);
            }
            
            // Remove from database
            unset($recordings[$recording_id]);
            update_option('twitch_recordings', $recordings);
            
            // Log deletion
            $this->log_recording_event($recording['channel'], 'recording_deleted', $recording);
            
            // Trigger action
            do_action('wp_twitch_recording_deleted', $recording_id, $recording);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete recording file
     */
    private function delete_recording_file($file_path) {
        if (strpos($file_path, wp_upload_dir()['baseurl']) === 0) {
            // Local file
            $file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_path);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        // Cloud files would be handled differently
    }
    
    /**
     * Handle AJAX settings
     */
    public function handle_ajax_settings() {
        check_ajax_referer('twitch_recording_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $action = $_POST['recording_action'] ?? '';
        
        switch ($action) {
            case 'save_settings':
                $this->save_recording_settings();
                break;
            case 'get_recordings':
                $this->get_recordings_ajax();
                break;
            case 'delete_recording':
                $this->delete_recording_ajax();
                break;
            case 'get_statistics':
                $this->get_statistics_ajax();
                break;
        }
        
        wp_die();
    }
    
    /**
     * Save recording settings
     */
    private function save_recording_settings() {
        $settings = array(
            'enabled' => isset($_POST['enabled']),
            'channels' => array_map('sanitize_text_field', $_POST['channels'] ?? array()),
            'check_interval' => sanitize_text_field($_POST['check_interval'] ?? 'every_minute'),
            'processing_method' => sanitize_text_field($_POST['processing_method'] ?? 'local'),
            'auto_thumbnails' => isset($_POST['auto_thumbnails']),
            'auto_clips' => isset($_POST['auto_clips']),
            'max_recording_duration' => intval($_POST['max_recording_duration'] ?? 720), // 12 hours
            'storage_location' => sanitize_text_field($_POST['storage_location'] ?? 'local'),
        );
        
        update_option('twitch_recording_settings', $settings);
        
        wp_send_json_success(array('message' => 'Settings saved successfully'));
    }
    
    /**
     * Get recordings AJAX
     */
    private function get_recordings_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $limit = intval($_POST['limit'] ?? 50);
        
        $recordings = $this->get_all_recordings($channel, $status, $limit);
        
        wp_send_json_success(array('recordings' => $recordings));
    }
    
    /**
     * Delete recording AJAX
     */
    private function delete_recording_ajax() {
        $recording_id = sanitize_text_field($_POST['recording_id'] ?? '');
        
        if ($this->delete_recording($recording_id)) {
            wp_send_json_success(array('message' => 'Recording deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete recording'));
        }
    }
    
    /**
     * Get statistics AJAX
     */
    private function get_statistics_ajax() {
        $channel = sanitize_text_field($_POST['channel'] ?? '');
        $statistics = $this->get_recording_statistics($channel);
        
        wp_send_json_success(array('statistics' => $statistics));
    }
}

// Initialize stream recording
new WP_Twitch_Stream_Recording();
