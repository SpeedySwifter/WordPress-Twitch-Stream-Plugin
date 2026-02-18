<?php
/**
 * Plugin Name: Live Stream Integration
 * Plugin URI: https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin
 * Description: The ultimate WordPress plugin for Twitch stream integration with mobile apps, scheduling, analytics, and multi-language support.
 * Version: 1.7.1
 * Requires at least: 5.8
 * Tested up to: 6.9.1
 * Requires PHP: 7.4
 * Author: SpeedySwifter
 * Author URI: https://github.com/SpeedySwifter
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-twitch-stream
 * Domain Path: /languages
 */

// Sicherheitscheck
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten
define('WP_TWITCH_VERSION', '1.7.1');
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
require_once WP_TWITCH_PLUGIN_DIR . 'includes/twitch-chat-integration.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/stream-recording-download.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/advanced-analytics-dashboard.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/multi-language-support.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/woocommerce-integration.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/membership-plugin-integration.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/advanced-shortcode-builder.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/visual-stream-scheduler.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/mobile-app-integration.php';
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
    
    // Chat Styles
    wp_enqueue_style(
        'wp-twitch-stream-chat',
        WP_TWITCH_PLUGIN_URL . 'assets/css/twitch-chat.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // Recording Download Styles
    wp_enqueue_style(
        'wp-twitch-stream-recording-download',
        WP_TWITCH_PLUGIN_URL . 'assets/css/recording-download.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // Analytics Dashboard Styles
    wp_enqueue_style(
        'wp-twitch-stream-analytics-dashboard',
        WP_TWITCH_PLUGIN_URL . 'assets/css/analytics-dashboard.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // Language Support Styles
    wp_enqueue_style(
        'wp-twitch-stream-language-support',
        WP_TWITCH_PLUGIN_URL . 'assets/css/language-support.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // WooCommerce Integration Styles (only if WooCommerce is active)
    if (class_exists('WooCommerce')) {
        wp_enqueue_style(
            'wp-twitch-stream-woocommerce',
            WP_TWITCH_PLUGIN_URL . 'assets/css/woocommerce-integration.css',
            array(),
            WP_TWITCH_VERSION
        );
    }
    
    // Membership Integration Styles (only if membership plugins are active)
    if (class_exists('MeprPlugin') || defined('RCP_PLUGIN_VERSION') || class_exists('PMPro_Plugin') || 
        class_exists('WC_Memberships') || class_exists('UM') || class_exists('c_ws_plugin__s2member')) {
        wp_enqueue_style(
            'wp-twitch-stream-membership',
            WP_TWITCH_PLUGIN_URL . 'assets/css/membership-integration.css',
            array(),
            WP_TWITCH_VERSION
        );
    }
    
    // Shortcode Builder Styles
    wp_enqueue_style(
        'wp-twitch-stream-shortcode-builder',
        WP_TWITCH_PLUGIN_URL . 'assets/css/shortcode-builder.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // Stream Scheduler Styles
    wp_enqueue_style(
        'wp-twitch-stream-scheduler',
        WP_TWITCH_PLUGIN_URL . 'assets/css/stream-scheduler.css',
        array(),
        WP_TWITCH_VERSION
    );
    
    // Mobile App Styles
    wp_enqueue_style(
        'wp-twitch-stream-mobile-app',
        WP_TWITCH_PLUGIN_URL . 'assets/css/mobile-app.css',
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
    
    // Shortcode Builder Styles for admin
    if (isset($_GET['page']) && $_GET['page'] === 'twitch-shortcode-builder') {
        wp_enqueue_style(
            'wp-twitch-stream-shortcode-builder',
            WP_TWITCH_PLUGIN_URL . 'assets/css/shortcode-builder.css',
            array(),
            WP_TWITCH_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'wp_twitch_enqueue_admin_styles');

// Admin-Scripts laden
function wp_twitch_enqueue_admin_scripts($hook) {
    // Shortcode Builder Scripts
    if (isset($_GET['page']) && $_GET['page'] === 'twitch-shortcode-builder') {
        wp_enqueue_script(
            'wp-twitch-stream-shortcode-builder',
            WP_TWITCH_PLUGIN_URL . 'assets/js/shortcode-builder.js',
            array('jquery', 'wp-util'),
            WP_TWITCH_VERSION,
            true
        );
    }
    
    // Stream Scheduler Scripts
    if (isset($_GET['page']) && $_GET['page'] === 'twitch-stream-scheduler') {
        wp_enqueue_script(
            'wp-twitch-stream-scheduler',
            WP_TWITCH_PLUGIN_URL . 'assets/js/stream-scheduler.js',
            array('jquery', 'wp-util'),
            WP_TWITCH_VERSION,
            true
        );
        
        // Enqueue FullCalendar if available
        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js',
            array(),
            '6.1.8',
            true
        );
        
        // Enqueue jQuery UI for date/time pickers
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    }
    
    // Mobile App Scripts
    if (isset($_GET['page']) && $_GET['page'] === 'twitch-mobile-app') {
        wp_enqueue_script(
            'wp-twitch-stream-mobile-app',
            WP_TWITCH_PLUGIN_URL . 'assets/js/mobile-app.js',
            array('jquery', 'wp-util'),
            WP_TWITCH_VERSION,
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'wp_twitch_enqueue_admin_scripts');

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
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Standard-Optionen setzen
    add_option('twitch_client_id', '');
    add_option('twitch_client_secret', '');
    
    // Datenbank-Tabellen erstellen
    $stream_schedules_sql = "CREATE TABLE {$wpdb->prefix}twitch_stream_schedules (
        id int(11) NOT NULL AUTO_INCREMENT,
        channel varchar(100) NOT NULL,
        title varchar(255) NOT NULL,
        description text,
        start_time datetime NOT NULL,
        end_time datetime NOT NULL,
        timezone varchar(50) DEFAULT 'UTC',
        stream_type varchar(50) DEFAULT 'live',
        category varchar(100),
        tags text,
        thumbnail_url text,
        status enum('scheduled','live','completed','cancelled') DEFAULT 'scheduled',
        is_recurring tinyint(1) DEFAULT 0,
        recurring_pattern varchar(255),
        recurring_end_date date,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY channel (channel),
        KEY start_time (start_time),
        KEY status (status)
    ) $charset_collate;";

    $recurring_patterns_sql = "CREATE TABLE {$wpdb->prefix}twitch_recurring_patterns (
        id int(11) NOT NULL AUTO_INCREMENT,
        pattern_name varchar(255) NOT NULL,
        pattern_type enum('daily','weekly','monthly') NOT NULL,
        pattern_data text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($stream_schedules_sql);
    dbDelta($recurring_patterns_sql);

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
