<?php
/**
 * Elementor Widget Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Elementor Integration prüfen
class SPSWIFTER_Twitch_Elementor_Integration {
    
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_widget_category'));
    }
    
    /**
     * Widget Kategorie hinzufügen
     */
    public function add_widget_category($elements_manager) {
        $elements_manager->add_category('twitch-stream', array(
            'title' => __('Twitch Stream', 'speedyswifter-twitch'),
            'icon' => 'fa fa-twitch',
        ));
    }
    
    /**
     * Widgets registrieren
     */
    public function register_widgets() {
        if (!class_exists('Elementor\Widget_Base')) {
            return;
        }
        
        // Twitch Stream Widget
        require_once WP_TWITCH_PLUGIN_DIR . 'includes/elementor-widgets/stream-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \SPSWIFTER_Elementor_Twitch_Stream_Widget());
        
        // Twitch Grid Widget
        require_once WP_TWITCH_PLUGIN_DIR . 'includes/elementor-widgets/grid-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \SPSWIFTER_Elementor_Twitch_Grid_Widget());
    }
}

// Initialisierung
function spswifter_spswifter_twitch_elementor_init() {
    if (did_action('elementor/loaded')) {
        new SPSWIFTER_Twitch_Elementor_Integration();
    }
}
add_action('init', 'spswifter_spswifter_twitch_elementor_init');

// Admin Notice wenn Elementor nicht aktiv
function spswifter_spswifter_twitch_elementor_admin_notice() {
    if (!class_exists('Elementor\Plugin')) {
        return;
    }
    
    if (!did_action('elementor/loaded')) {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen && $screen->id === 'elementor_page_elementor-app') {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php esc_html_e('🎮 SpeedySwifter Twitch: Elementor Widgets sind jetzt verfügbar!', 'speedyswifter-twitch'); ?>
                <a href="<?php echo admin_url('options-general.php?page=twitch-api-settings'); ?>">
                    <?php esc_html_e('API-Einstellungen konfigurieren', 'speedyswifter-twitch'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'spswifter_spswifter_twitch_elementor_admin_notice');
?>
