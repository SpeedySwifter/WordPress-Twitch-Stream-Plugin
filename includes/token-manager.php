<?php

// Sicherheitscheck
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Token Management für Twitch API
 * 
 * Für erweiterte OAuth-Funktionalität siehe:
 * https://github.com/SpeedySwifter/WP-Twitch-Access-Token
 */
class SPSWIFTER_Twitch_Token_Manager {
    
    /**
     * Access Token abrufen oder erneuern
     */
    public static function get_access_token($force_refresh = false) {
        $client_id = get_option('spswifter_twitch_client_id');
        $client_secret = get_option('spswifter_twitch_client_secret');
        
        if (empty($client_id) || empty($client_secret)) {
            return false;
        }
        
        // Token aus Cache laden
        if (!$force_refresh) {
            $token = get_transient('spswifter_twitch_access_token');
            if ($token) {
                return $token;
            }
        }
        
        // Neuen Token anfordern
        $response = wp_remote_post('https://id.twitch.tv/oauth2/token', [
            'timeout' => 30,
            'body' => [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'client_credentials'
            ]
        ]);
        
        if (is_wp_error($response)) {
            error_log('Twitch API Token Error: ' . $response->get_error_message());
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code !== 200 || !isset($data['access_token'])) {
            error_log('Twitch API Token Error: ' . $body);
            return false;
        }
        
        $token = $data['access_token'];
        $expires_in = $data['expires_in'] ?? 432000; // Standard: 5 Tage
        
        // Token cachen (etwas kürzer als die tatsächliche Gültigkeit)
        set_transient('spswifter_twitch_access_token', $token, $expires_in - 3600);
        
        return $token;
    }
    
    /**
     * Token validieren
     */
    public static function validate_token($token) {
        if (empty($token)) {
            return false;
        }
        
        $client_id = get_option('spswifter_twitch_client_id');
        if (empty($client_id)) {
            return false;
        }
        
        $response = wp_remote_get('https://api.twitch.tv/helix/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Client-Id' => $client_id
            ],
            'timeout' => 10
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }
    
    /**
     * Token zwangsläufig erneuern
     */
    public static function refresh_token() {
        delete_transient('spswifter_twitch_access_token');
        return self::get_access_token(true);
    }
    
    /**
     * Alle Twitch-Caches löschen
     */
    public static function clear_all_caches() {
        delete_transient('spswifter_twitch_access_token');
        
        // Alle Stream-Status-Caches löschen
        global $wpdb;
        $transient_names = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_spswifter_twitch_%'"
        );
        
        foreach ($transient_names as $transient_name) {
            delete_transient(str_replace('_transient_', '', $transient_name));
        }
    }
    
    /**
     * API-Limits prüfen
     */
    public static function check_rate_limits() {
        $rate_limit_key = 'spswifter_twitch_api_calls_' . gmdate('Y-m-d-H');
        $calls = get_transient($rate_limit_key) ?: 0;
        
        // Twitch API Limit: 30 calls pro Minute
        if ($calls >= 25) { // Sicherheitspuffer
            return false;
        }
        
        // Counter erhöhen
        set_transient($rate_limit_key, $calls + 1, 3600); // 1 Stunde
        
        return true;
    }
    
    /**
     * Debug-Informationen
     */
    public static function get_debug_info() {
        $info = [
            'client_id_set' => !empty(get_option('spswifter_twitch_client_id')),
            'client_secret_set' => !empty(get_option('spswifter_twitch_client_secret')),
            'token_cached' => get_transient('spswifter_twitch_access_token') !== false,
            'token_valid' => false,
            'last_error' => get_transient('spswifter_twitch_last_error'),
            'api_calls_this_hour' => get_transient('spswifter_twitch_api_calls_' . gmdate('Y-m-d-H')) ?: 0
        ];
        
        if ($info['token_cached']) {
            $token = get_transient('spswifter_twitch_access_token');
            $info['token_valid'] = self::validate_token($token);
        }
        
        return $info;
    }
}

// Admin-Actions
add_action('admin_post_spswifter_twitch_clear_cache', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    SPSWIFTER_Twitch_Token_Manager::clear_all_caches();
    
    wp_redirect(admin_url('options-general.php?page=twitch-api-settings&cleared=1'));
    exit;
});

add_action('admin_post_spswifter_twitch_refresh_token', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $success = SPSWIFTER_Twitch_Token_Manager::refresh_token();
    
    wp_redirect(admin_url('options-general.php?page=twitch-api-settings&refreshed=' . ($success ? '1' : '0')));
    exit;
});
?>
