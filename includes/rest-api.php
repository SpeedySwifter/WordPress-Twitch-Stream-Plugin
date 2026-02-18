<?php
/**
 * REST API Endpoints for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_REST_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Stream endpoints
        register_rest_route('wp-twitch-stream/v1', '/streams', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_streams'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'channels' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Comma-separated list of channel names',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 100,
                ),
                'offset' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0,
                    'minimum' => 0,
                ),
            ),
        ));
        
        // Single stream endpoint
        register_rest_route('wp-twitch-stream/v1', '/streams/(?P<channel>[a-zA-Z0-9_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_stream'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'channel' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Twitch channel name',
                ),
            ),
        ));
        
        // VOD endpoints
        register_rest_route('wp-twitch-stream/v1', '/videos', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_videos'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'channel' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Twitch channel name',
                ),
                'type' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('archive', 'upload', 'highlight'),
                    'default' => 'archive',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 100,
                ),
            ),
        ));
        
        // Clips endpoints
        register_rest_route('wp-twitch-stream/v1', '/clips', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_clips'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'channel' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Twitch channel name',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 100,
                ),
            ),
        ));
        
        // Analytics endpoints
        register_rest_route('wp-twitch-stream/v1', '/analytics/(?P<channel>[a-zA-Z0-9_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'channel' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Twitch channel name',
                ),
                'period' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('day', 'week', 'month', 'year'),
                    'default' => 'week',
                ),
            ),
        ));
        
        // Dashboard endpoint
        register_rest_route('wp-twitch-stream/v1', '/dashboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard_data'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'channels' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Comma-separated list of channel names',
                ),
                'period' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('day', 'week', 'month'),
                    'default' => 'week',
                ),
            ),
        ));
        
        // Settings endpoint
        register_rest_route('wp-twitch-stream/v1', '/settings', array(
            'methods' => array('GET', 'POST'),
            'callback' => array($this, 'handle_settings'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));
        
        // Cache management endpoint
        register_rest_route('wp-twitch-stream/v1', '/cache/(?P<action>[a-z]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_cache'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'action' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('clear', 'warm', 'status'),
                ),
            ),
        ));
    }
    
    /**
     * Check API permissions
     */
    public function check_permissions() {
        // Allow public access for read operations
        return true;
    }
    
    /**
     * Check admin permissions
     */
    public function check_admin_permissions() {
        return current_user_can('manage_options');
    }
    
    /**
     * Get multiple streams
     */
    public function get_streams($request) {
        $channels = $request->get_param('channels');
        $limit = $request->get_param('limit');
        $offset = $request->get_param('offset');
        
        $api = new WP_Twitch_API();
        $streams = array();
        
        if ($channels) {
            $channel_list = explode(',', $channels);
            $channel_list = array_map('trim', $channel_list);
            $channel_list = array_slice($channel_list, $offset, $limit);
            
            foreach ($channel_list as $channel) {
                $stream_data = $api->get_complete_stream_info($channel);
                if ($stream_data) {
                    $streams[] = $this->format_stream_response($stream_data);
                }
            }
        } else {
            // Get top streams if no channels specified
            $streams = $this->get_top_streams($limit, $offset);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $streams,
            'total' => count($streams),
            'limit' => $limit,
            'offset' => $offset,
        ), 200);
    }
    
    /**
     * Get single stream
     */
    public function get_stream($request) {
        $channel = $request->get_param('channel');
        
        $api = new WP_Twitch_API();
        $stream_data = $api->get_complete_stream_info($channel);
        
        if (!$stream_data) {
            return new WP_Error(
                'stream_not_found',
                'Stream not found or offline',
                array('status' => 404)
            );
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $this->format_stream_response($stream_data),
        ), 200);
    }
    
    /**
     * Get videos
     */
    public function get_videos($request) {
        $channel = $request->get_param('channel');
        $type = $request->get_param('type');
        $limit = $request->get_param('limit');
        
        $api = new WP_Twitch_API();
        $videos = $api->get_channel_videos($channel, $limit, $type);
        
        if (!$videos) {
            return new WP_Error(
                'videos_not_found',
                'No videos found',
                array('status' => 404)
            );
        }
        
        $formatted_videos = array();
        foreach ($videos as $video) {
            $formatted_videos[] = $this->format_video_response($video);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $formatted_videos,
            'total' => count($formatted_videos),
        ), 200);
    }
    
    /**
     * Get clips
     */
    public function get_clips($request) {
        $channel = $request->get_param('channel');
        $limit = $request->get_param('limit');
        
        $api = new WP_Twitch_API();
        $clips = $api->get_channel_clips($channel, $limit);
        
        if (!$clips) {
            return new WP_Error(
                'clips_not_found',
                'No clips found',
                array('status' => 404)
            );
        }
        
        $formatted_clips = array();
        foreach ($clips as $clip) {
            $formatted_clips[] = $this->format_clip_response($clip);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $formatted_clips,
            'total' => count($formatted_clips),
        ), 200);
    }
    
    /**
     * Get analytics
     */
    public function get_analytics($request) {
        $channel = $request->get_param('channel');
        $period = $request->get_param('period');
        
        $analytics = new WP_Twitch_Analytics();
        $data = $analytics->get_channel_analytics($channel, $period);
        
        if (!$data) {
            return new WP_Error(
                'analytics_not_found',
                'Analytics data not available',
                array('status' => 404)
            );
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $data,
        ), 200);
    }
    
    /**
     * Get dashboard data
     */
    public function get_dashboard_data($request) {
        $channels = $request->get_param('channels');
        $period = $request->get_param('period');
        
        $dashboard = new WP_Twitch_Dashboard();
        $data = $dashboard->get_dashboard_data($channels, $period);
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $data,
        ), 200);
    }
    
    /**
     * Handle settings
     */
    public function handle_settings($request) {
        $method = $request->get_method();
        
        if ($method === 'GET') {
            $settings = array(
                'client_id' => get_option('twitch_client_id'),
                'api_connected' => get_option('twitch_api_connected', false),
                'cache_enabled' => get_option('twitch_cache_enabled', true),
                'cache_duration' => get_option('twitch_cache_duration', 300),
                'analytics_enabled' => get_option('twitch_analytics_enabled', false),
                'webhook_enabled' => get_option('twitch_webhook_enabled', false),
            );
            
            return new WP_REST_Response(array(
                'success' => true,
                'data' => $settings,
            ), 200);
        } elseif ($method === 'POST') {
            $settings = $request->get_json_params();
            
            foreach ($settings as $key => $value) {
                update_option('twitch_' . $key, $value);
            }
            
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Settings updated successfully',
            ), 200);
        }
    }
    
    /**
     * Handle cache operations
     */
    public function handle_cache($request) {
        $action = $request->get_param('action');
        $cache = new WP_Twitch_Cache();
        
        switch ($action) {
            case 'clear':
                $cache->clear_all_cache();
                $message = 'Cache cleared successfully';
                break;
            case 'warm':
                $cache->warm_cache();
                $message = 'Cache warmed successfully';
                break;
            case 'status':
                $status = $cache->get_cache_status();
                return new WP_REST_Response(array(
                    'success' => true,
                    'data' => $status,
                ), 200);
            default:
                return new WP_Error(
                    'invalid_action',
                    'Invalid cache action',
                    array('status' => 400)
                );
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => $message,
        ), 200);
    }
    
    /**
     * Format stream response
     */
    private function format_stream_response($stream_data) {
        return array(
            'channel' => $stream_data['user']['login'],
            'display_name' => $stream_data['user']['display_name'],
            'is_live' => $stream_data['is_live'],
            'title' => $stream_data['stream']['title'] ?? '',
            'game' => $stream_data['game']['name'] ?? '',
            'viewers' => $stream_data['stream']['viewer_count'] ?? 0,
            'started_at' => $stream_data['stream']['started_at'] ?? '',
            'thumbnail_url' => $stream_data['stream']['thumbnail_url'] ?? '',
            'profile_image_url' => $stream_data['user']['profile_image_url'] ?? '',
            'followers' => $stream_data['user']['followers'] ?? 0,
            'language' => $stream_data['stream']['language'] ?? '',
            'tags' => $stream_data['stream']['tags'] ?? array(),
        );
    }
    
    /**
     * Format video response
     */
    private function format_video_response($video) {
        return array(
            'id' => $video['id'],
            'title' => $video['title'],
            'description' => $video['description'] ?? '',
            'created_at' => $video['created_at'],
            'published_at' => $video['published_at'],
            'url' => $video['url'],
            'thumbnail_url' => $video['thumbnail_url'],
            'viewable' => $video['viewable'],
            'view_count' => $video['view_count'],
            'language' => $video['language'],
            'type' => $video['type'],
            'duration' => $video['duration'],
            'user_id' => $video['user_id'],
            'user_name' => $video['user_name'],
            'user_login' => $video['user_login'],
        );
    }
    
    /**
     * Format clip response
     */
    private function format_clip_response($clip) {
        return array(
            'id' => $clip['id'],
            'url' => $clip['url'],
            'embed_url' => $clip['embed_url'],
            'broadcaster_id' => $clip['broadcaster_id'],
            'broadcaster_name' => $clip['broadcaster_name'],
            'creator_id' => $clip['creator_id'],
            'creator_name' => $clip['creator_name'],
            'video_id' => $clip['video_id'] ?? '',
            'game_id' => $clip['game_id'] ?? '',
            'language' => $clip['language'],
            'title' => $clip['title'],
            'view_count' => $clip['view_count'],
            'created_at' => $clip['created_at'],
            'thumbnail_url' => $clip['thumbnail_url'],
            'duration' => $clip['duration'],
        );
    }
    
    /**
     * Get top streams
     */
    private function get_top_streams($limit = 10, $offset = 0) {
        $api = new WP_Twitch_API();
        
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/streams?first={$limit}",
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api->access_token,
                    'Client-Id' => $api->client_id
                )
            )
        );
        
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            $streams = array();
            
            if (!empty($data['data'])) {
                foreach ($data['data'] as $stream) {
                    $streams[] = array(
                        'channel' => $stream['user_login'],
                        'display_name' => $stream['user_name'],
                        'is_live' => true,
                        'title' => $stream['title'],
                        'game' => $stream['game_name'],
                        'viewers' => $stream['viewer_count'],
                        'started_at' => $stream['started_at'],
                        'thumbnail_url' => $stream['thumbnail_url'],
                        'profile_image_url' => '',
                        'followers' => 0,
                        'language' => $stream['language'],
                        'tags' => $stream['tags'] ?? array(),
                    );
                }
            }
            
            return $streams;
        }
        
        return array();
    }
}

// Initialize REST API
new WP_Twitch_REST_API();
