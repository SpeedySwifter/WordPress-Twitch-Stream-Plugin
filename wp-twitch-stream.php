<?php
/**
 * Plugin Name: WordPress Twitch Stream
 * Plugin URI: https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin
 * Description: Bindet Twitch-Streams per Shortcode ein mit Live-Status-Erkennung
 * Version: 1.3.0
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
define('WP_TWITCH_VERSION', '1.3.0');
define('WP_TWITCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_TWITCH_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once WP_TWITCH_PLUGIN_DIR . 'includes/twitch-api.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/shortcode.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/token-manager.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/gutenberg-block.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/page-builder-compatibility.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/cookie-integration.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/sidebar-widgets.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/rest-api.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/webhook-support.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/advanced-analytics.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/stream-recording.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/multi-channel-dashboard.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/custom-css-builder.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/advanced-cache.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/donation-integration.php';
require_once WP_TWITCH_PLUGIN_DIR . 'admin/settings-page.php';

// Plugin initialisieren
add_action('plugins_loaded', 'wp_twitch_init');

function wp_twitch_init() {
    load_plugin_textdomain('wp-twitch-stream', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Frontend-Styles laden
function wp_twitch_enqueue_frontend_styles() {
    wp_enqueue_style(
        'wp-twitch-stream-frontend',
        WP_TWITCH_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // VOD und Clips Styles
    wp_enqueue_style(
        'wp-twitch-stream-vod-clips',
        WP_TWITCH_PLUGIN_URL . 'assets/css/vod-clips.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // Donation Styles
    wp_enqueue_style(
        'wp-twitch-stream-donations',
        WP_TWITCH_PLUGIN_URL . 'assets/css/donations.css',
        array(),
        WP_TWITCH_VERSION
    );
}
add_action('wp_enqueue_scripts', 'wp_twitch_enqueue_frontend_styles');

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

// Gutenberg Block Assets laden
function wp_twitch_enqueue_block_assets() {
    // Block Editor Script
    wp_enqueue_script(
        'twitch-stream-block',
        WP_TWITCH_PLUGIN_URL . 'assets/js/block.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor'),
        WP_TWITCH_VERSION,
        true
    );

    // Block Editor Style
    wp_enqueue_style(
        'twitch-stream-block-style',
        WP_TWITCH_PLUGIN_URL . 'assets/css/block.css',
        array(),
        WP_TWITCH_VERSION
    );
}
add_action('enqueue_block_editor_assets', 'wp_twitch_enqueue_block_assets');

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
