<?php
/**
 * Twitch API Handler
 */
class SPSWIFTER_Twitch_API {
    private $client_id;
    private $client_secret;
    private $access_token;

    public function __construct() {
        // Delay initialization until WordPress is loaded
        add_action('init', array($this, 'init'));
    }

    public function init() {
        $this->client_id = get_option('spswifter_twitch_client_id');
        $this->client_secret = get_option('spswifter_twitch_client_secret');
        $this->access_token = $this->get_access_token();
    }

    /**
     * Access Token abrufen (mit Caching)
     */
    private function get_access_token() {
        // Token aus Cache laden
        $token = get_transient('spswifter_twitch_access_token');

        if (!$token) {
            $response = wp_remote_post('https://id.twitch.tv/oauth2/token', [
                'body' => [
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type' => 'client_credentials'
                ]
            ]);

            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                
                // Check if access_token exists in response
                if (isset($data['access_token'])) {
                    $token = $data['access_token'];

                    // Token für 50 Tage cachen
                    set_transient('spswifter_twitch_access_token', $token, 50 * DAY_IN_SECONDS);
                } else {
                    // Log error for debugging
                    error_log('Twitch API Error: No access_token in response');
                    $token = null;
                }
            } else {
                // API call failed
                $token = null;
            }
        }

        return $token;
    }

    /**
     * Stream-Status prüfen
     */
    public function is_stream_live($channel) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/streams?user_login={$channel}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return !empty($data['data']);
        }

        return false;
    }

    /**
     * Stream-Daten abrufen
     */
    public function get_stream_data($channel) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/streams?user_login={$channel}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0] ?? null;
        }

        return null;
    }

    /**
     * Benutzer-Informationen abrufen
     */
    public function get_user_info($channel) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/users?login={$channel}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0] ?? null;
        }

        return null;
    }

    /**
     * Spiel-Informationen abrufen
     */
    public function get_game_info($game_id) {
        if (empty($game_id)) return null;

        $response = wp_remote_get(
            "https://api.twitch.tv/helix/games?id={$game_id}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0] ?? null;
        }

        return null;
    }

    /**
     * Vollständige Stream-Informationen abrufen
     */
    public function get_complete_stream_info($channel) {
        $stream_data = $this->get_stream_data($channel);
        $user_info = $this->get_user_info($channel);

        if (!$stream_data && !$user_info) {
            return null;
        }

        $info = [
            'is_live' => !empty($stream_data),
            'user' => $user_info,
            'stream' => $stream_data,
            'game' => null
        ];

        if ($stream_data && !empty($stream_data['game_id'])) {
            $info['game'] = $this->get_game_info($stream_data['game_id']);
        }

        return $info;
    }

    /**
     * VODs (Videos) von einem Kanal abrufen
     */
    public function get_channel_videos($channel, $limit = 10, $type = 'archive') {
        $user_info = $this->get_user_info($channel);
        
        if (!$user_info || empty($user_info['id'])) {
            return null;
        }

        $user_id = $user_info['id'];
        
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/videos?user_id={$user_id}&first={$limit}&type={$type}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'] ?? [];
        }

        return array();
    }

    /**
     * Spezifisches VOD abrufen
     */
    public function get_video($video_id) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/videos?id={$video_id}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0] ?? null;
        }

        return null;
    }

    /**
     * Clips von einem Kanal abrufen
     */
    public function get_channel_clips($channel, $limit = 10) {
        $user_info = $this->get_user_info($channel);
        
        if (!$user_info || empty($user_info['id'])) {
            return null;
        }

        $user_id = $user_info['id'];
        
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/clips?broadcaster_id={$user_id}&first={$limit}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'] ?? [];
        }

        return array();
    }

    /**
     * Spezifischen Clip abrufen
     */
    public function get_clip($clip_id) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/clips?id={$clip_id}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0] ?? null;
        }

        return null;
    }

    /**
     * VOD-URL für Embed erstellen
     */
    public function get_vod_embed_url($video_id, $autoplay = false, $muted = false) {
        $domain = $_SERVER['HTTP_HOST'];
        
        if (in_array($domain, ['localhost', '127.0.0.1'])) {
            $domain = 'localhost';
        }
        
        $params = array(
            'video' => $video_id,
            'parent' => $domain,
            'autoplay' => $autoplay ? 'true' : 'false',
            'muted' => $muted ? 'true' : 'false'
        );
        
        return 'https://player.twitch.tv/?' . http_build_query($params);
    }

    /**
     * Clip-URL für Embed erstellen
     */
    public function get_clip_embed_url($clip_id, $autoplay = false) {
        $domain = $_SERVER['HTTP_HOST'];
        
        if (in_array($domain, ['localhost', '127.0.0.1'])) {
            $domain = 'localhost';
        }
        
        $params = array(
            'clip' => $clip_id,
            'parent' => $domain,
            'autoplay' => $autoplay ? 'true' : 'false'
        );
        
        return 'https://clips.twitch.tv/embed?' . http_build_query($params);
    }

    /**
     * Benutzer-ID abrufen
     */
    public function get_user_id($username) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/users?login={$username}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0]['id'] ?? null;
        }

        return null;
    }

    /**
     * API-Verbindung testen
     */
    public function test_connection() {
        if (empty($this->client_id) || empty($this->client_secret)) {
            return [
                'success' => false,
                'message' => 'Client ID oder Client Secret fehlt.'
            ];
        }

        $token = $this->get_access_token();
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Konnte keinen Access Token abrufen.'
            ];
        }

        return [
            'success' => true,
            'message' => 'API-Verbindung erfolgreich.'
        ];
    }

    /**
     * Cache für Stream-Status
     */
    public function get_cached_stream_status($channel, $cache_time = 300) {
        $cache_key = 'spswifter_twitch_stream_status_' . sanitize_key($channel);
        $cached_status = get_transient($cache_key);

        if ($cached_status !== false) {
            return $cached_status;
        }

        $is_live = $this->is_stream_live($channel);
        set_transient($cache_key, $is_live, $cache_time);

        return $is_live;
    }

    /**
     * Stream-Cache löschen
     */
    public function clear_stream_cache($channel = null) {
        if ($channel) {
            delete_transient('spswifter_twitch_stream_status_' . sanitize_key($channel));
        } else {
            // Alle Stream-Caches löschen
            global $wpdb;
            $transient_names = $wpdb->get_col(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_spswifter_twitch_stream_status_%'"
            );
            
            foreach ($transient_names as $transient_name) {
                delete_transient(str_replace('_transient_', '', $transient_name));
            }
        }
    }
}
?>
