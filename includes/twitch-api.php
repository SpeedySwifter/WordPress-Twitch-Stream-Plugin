<?php
/**
 * Twitch API Handler
 */
class WP_Twitch_API {
    private $client_id;
    private $client_secret;
    private $access_token;

    public function __construct() {
        $this->client_id = get_option('twitch_client_id');
        $this->client_secret = get_option('twitch_client_secret');
        $this->access_token = $this->get_access_token();
    }

    /**
     * Access Token abrufen (mit Caching)
     */
    private function get_access_token() {
        // Token aus Cache laden
        $token = get_transient('twitch_access_token');

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
                $token = $data['access_token'];

                // Token für 50 Tage cachen
                set_transient('twitch_access_token', $token, 50 * DAY_IN_SECONDS);
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
        $cache_key = 'twitch_stream_status_' . sanitize_key($channel);
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
            delete_transient('twitch_stream_status_' . sanitize_key($channel));
        } else {
            // Alle Stream-Caches löschen
            global $wpdb;
            $transient_names = $wpdb->get_col(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_twitch_stream_status_%'"
            );
            
            foreach ($transient_names as $transient_name) {
                delete_transient(str_replace('_transient_', '', $transient_name));
            }
        }
    }
}
?>
