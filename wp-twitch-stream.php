<?php
/**
 * Plugin Name: WordPress Twitch Stream
 * Plugin URI: https://github.com/SpeedySwifter/wp-twitch-stream
 * Description: Bindet Twitch-Streams per Shortcode ein mit Live-Status-Erkennung
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: SpeedySwifter
 * Author URI: https://github.com/SpeedySwifter
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wp-twitch-stream
 * Domain Path: /languages
 */

// Sicherheitscheck
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten
define('WP_TWITCH_VERSION', '1.0.0');
define('WP_TWITCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_TWITCH_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once WP_TWITCH_PLUGIN_DIR . 'includes/twitch-api.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/shortcode.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/token-manager.php';
require_once WP_TWITCH_PLUGIN_DIR . 'admin/settings-page.php';

// Plugin initialisieren
add_action('plugins_loaded', 'wp_twitch_init');

function wp_twitch_init() {
    load_plugin_textdomain('wp-twitch-stream', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Frontend-Styles laden
function wp_twitch_enqueue_styles() {
    wp_enqueue_style(
        'wp-twitch-stream-frontend',
        WP_TWITCH_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WP_TWITCH_VERSION
    );
}
add_action('wp_enqueue_scripts', 'wp_twitch_enqueue_styles');

// Admin-Styles laden
function wp_twitch_enqueue_admin_styles() {
    wp_enqueue_style(
        'wp-twitch-stream-admin',
        WP_TWITCH_PLUGIN_URL . 'admin/admin-styles.css',
        array(),
        WP_TWITCH_VERSION
    );
}
add_action('admin_enqueue_scripts', 'wp_twitch_enqueue_admin_styles');

// Aktivierungs-Hook
register_activation_hook(__FILE__, 'wp_twitch_activate');
function wp_twitch_activate() {
    // Standard-Optionen setzen
    add_option('twitch_client_id', '');
    add_option('twitch_client_secret', '');
    
    // Transients löschen bei Aktivierung
    delete_transient('twitch_access_token');
}

// Deaktivierungs-Hook
register_deactivation_hook(__FILE__, 'wp_twitch_deactivate');
function wp_twitch_deactivate() {
    // Transients löschen bei Deaktivierung
    delete_transient('twitch_access_token');
}
?>
