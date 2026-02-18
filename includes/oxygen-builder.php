<?php
/**
 * Oxygen Builder Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Oxygen_Integration {
    
    public function __construct() {
        add_action('oxygen_add_plus_components', array($this, 'register_components'));
        add_action('oxygen_enqueue_ui_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    /**
     * Oxygen Components registrieren
     */
    public function register_components() {
        // Twitch Stream Component
        $this->register_twitch_stream_component();
        
        // Twitch Grid Component
        $this->register_twitch_grid_component();
    }
    
    /**
     * Twitch Stream Component
     */
    private function register_twitch_stream_component() {
        $component = array(
            'name' => 'twitch_stream',
            'title' => __('Twitch Stream', 'wp-twitch-stream'),
            'description' => __('Bettet einen Twitch Stream ein', 'wp-twitch-stream'),
            'icon' => 'oxygen-icon-video',
            'wrapper' => true,
            'params' => array(
                'channel' => array(
                    'type' => 'text',
                    'name' => __('Kanal', 'wp-twitch-stream'),
                    'value' => '',
                    'placeholder' => __('z.B. shroud', 'wp-twitch-stream'),
                    'help' => __('Gib den Twitch-Benutzernamen ein', 'wp-twitch-stream'),
                ),
                'width' => array(
                    'type' => 'text',
                    'name' => __('Breite', 'wp-twitch-stream'),
                    'value' => '100%',
                    'placeholder' => __('100% oder 800px', 'wp-twitch-stream'),
                ),
                'height' => array(
                    'type' => 'number',
                    'name' => __('Höhe', 'wp-twitch-stream'),
                    'value' => 480,
                    'min' => 200,
                    'max' => 1080,
                ),
                'autoplay' => array(
                    'type' => 'checkbox',
                    'name' => __('Autoplay', 'wp-twitch-stream'),
                    'value' => '1',
                ),
                'muted' => array(
                    'type' => 'checkbox',
                    'name' => __('Stummgeschaltet', 'wp-twitch-stream'),
                    'value' => '0',
                ),
                'show_info' => array(
                    'type' => 'checkbox',
                    'name' => __('Stream-Infos anzeigen', 'wp-twitch-stream'),
                    'value' => '0',
                ),
                'info_layout' => array(
                    'type' => 'select',
                    'name' => __('Info Layout', 'wp-twitch-stream'),
                    'value' => 'horizontal',
                    'options' => array(
                        'horizontal' => __('Horizontal', 'wp-twitch-stream'),
                        'vertical' => __('Vertikal', 'wp-twitch-stream'),
                        'compact' => __('Kompakt', 'wp-twitch-stream'),
                    ),
                ),
            ),
            'advanced' => array(
                'id' => 'twitch_stream_' . uniqid(),
                'class' => 'twitch-stream-oxygen',
                'wrapper_class' => 'twitch-stream-wrapper',
            ),
        );
        
        // Component registrieren
        if (function_exists('oxygen_vsb_register_component')) {
            oxygen_vsb_register_component($component);
        }
    }
    
    /**
     * Twitch Grid Component
     */
    private function register_twitch_grid_component() {
        $component = array(
            'name' => 'twitch_grid',
            'title' => __('Twitch Stream Grid', 'wp-twitch-stream'),
            'description' => __('Zeigt mehrere Twitch Streams im Grid an', 'wp-twitch-stream'),
            'icon' => 'oxygen-icon-grid',
            'wrapper' => true,
            'params' => array(
                'channels' => array(
                    'type' => 'text',
                    'name' => __('Kanäle', 'wp-twitch-stream'),
                    'value' => '',
                    'placeholder' => __('shroud, ninja, pokimane', 'wp-twitch-stream'),
                    'help' => __('Kommagetrennte Liste von Twitch-Kanälen', 'wp-twitch-stream'),
                ),
                'columns' => array(
                    'type' => 'number',
                    'name' => __('Spalten', 'wp-twitch-stream'),
                    'value' => 3,
                    'min' => 1,
                    'max' => 6,
                ),
                'layout' => array(
                    'type' => 'select',
                    'name' => __('Layout', 'wp-twitch-stream'),
                    'value' => 'grid',
                    'options' => array(
                        'grid' => __('Grid', 'wp-twitch-stream'),
                        'list' => __('Liste', 'wp-twitch-stream'),
                        'masonry' => __('Masonry', 'wp-twitch-stream'),
                    ),
                ),
                'gap' => array(
                    'type' => 'text',
                    'name' => __('Abstand', 'wp-twitch-stream'),
                    'value' => '20px',
                    'placeholder' => __('20px', 'wp-twitch-stream'),
                ),
                'responsive' => array(
                    'type' => 'checkbox',
                    'name' => __('Responsive', 'wp-twitch-stream'),
                    'value' => '1',
                ),
                'show_player' => array(
                    'type' => 'checkbox',
                    'name' => __('Player anzeigen', 'wp-twitch-stream'),
                    'value' => '1',
                ),
                'show_info' => array(
                    'type' => 'checkbox',
                    'name' => __('Informationen anzeigen', 'wp-twitch-stream'),
                    'value' => '1',
                ),
                'player_height' => array(
                    'type' => 'number',
                    'name' => __('Player Höhe', 'wp-twitch-stream'),
                    'value' => 200,
                    'min' => 100,
                    'max' => 400,
                ),
            ),
            'advanced' => array(
                'id' => 'twitch_grid_' . uniqid(),
                'class' => 'twitch-grid-oxygen',
                'wrapper_class' => 'twitch-grid-wrapper',
            ),
        );
        
        // Component registrieren
        if (function_exists('oxygen_vsb_register_component')) {
            oxygen_vsb_register_component($component);
        }
    }
    
    /**
     * Oxygen UI Scripts laden
     */
    public function enqueue_scripts() {
        // Oxygen Builder JavaScript
        wp_enqueue_script(
            'twitch-oxygen-builder',
            WP_TWITCH_PLUGIN_URL . 'assets/js/oxygen-builder.js',
            array('oxygen-builder'),
            WP_TWITCH_VERSION,
            true
        );
    }
    
    /**
     * Frontend Scripts laden
     */
    public function enqueue_frontend_scripts() {
        // Nur im Oxygen Builder laden
        if (defined('OXYGEN_VSB_VERSION')) {
            wp_enqueue_style(
                'twitch-oxygen-frontend',
                WP_TWITCH_PLUGIN_URL . 'assets/css/oxygen-frontend.css',
                array(),
                WP_TWITCH_VERSION
            );
        }
    }
    
    /**
     * Component Rendering
     */
    public static function render_component($component, $atts) {
        $output = '';
        
        switch ($component) {
            case 'twitch_stream':
                $output = self::render_twitch_stream($atts);
                break;
            case 'twitch_grid':
                $output = self::render_twitch_grid($atts);
                break;
        }
        
        return $output;
    }
    
    /**
     * Twitch Stream rendern
     */
    private static function render_twitch_stream($atts) {
        $channel = $atts['channel'] ?? '';
        $width = $atts['width'] ?? '100%';
        $height = $atts['height'] ?? '480';
        $autoplay = ($atts['autoplay'] ?? '0') === '1' ? 'true' : 'false';
        $muted = ($atts['muted'] ?? '0') === '1' ? 'true' : 'false';
        $show_info = ($atts['show_info'] ?? '0') === '1';
        $info_layout = $atts['info_layout'] ?? 'horizontal';
        
        if (empty($channel)) {
            return '<div class="twitch-error">Bitte gib einen Twitch-Kanal an.</div>';
        }
        
        // Stream Shortcode
        $stream_atts = array(
            'channel' => $channel,
            'width' => $width,
            'height' => $height,
            'autoplay' => $autoplay,
            'muted' => $muted,
        );
        
        $output = wp_twitch_stream_shortcode($stream_atts);
        
        // Stream Info
        if ($show_info) {
            $info_atts = array(
                'channel' => $channel,
                'layout' => $info_layout,
                'show_title' => 'true',
                'show_game' => 'true',
                'show_viewers' => 'true',
                'show_thumbnail' => 'true',
                'show_avatar' => 'true',
            );
            
            $output .= wp_twitch_stream_info_shortcode($info_atts);
        }
        
        return $output;
    }
    
    /**
     * Twitch Grid rendern
     */
    private static function render_twitch_grid($atts) {
        $channels = $atts['channels'] ?? '';
        $columns = intval($atts['columns'] ?? 3);
        $layout = $atts['layout'] ?? 'grid';
        $gap = $atts['gap'] ?? '20px';
        $responsive = ($atts['responsive'] ?? '1') === '1';
        $show_player = ($atts['show_player'] ?? '1') === '1';
        $show_info = ($atts['show_info'] ?? '1') === '1';
        $player_height = $atts['player_height'] ?? '200';
        
        if (empty($channels)) {
            return '<div class="twitch-error">Bitte gib mindestens einen Twitch-Kanal an.</div>';
        }
        
        // Grid Shortcode
        $grid_atts = array(
            'channels' => $channels,
            'columns' => $columns,
            'layout' => $layout,
            'gap' => $gap,
            'responsive' => $responsive ? 'true' : 'false',
            'show_player' => $show_player ? 'true' : 'false',
            'show_info' => $show_info ? 'true' : 'false',
        );
        
        return wp_twitch_streams_grid_shortcode($grid_atts);
    }
}

// Oxygen Builder Hook
function wp_twitch_oxygen_render_component($output, $component, $atts) {
    if (strpos($component, 'twitch_') === 0) {
        return WP_Twitch_Oxygen_Integration::render_component($component, $atts);
    }
    return $output;
}
add_filter('oxygen_vsb_component_render', 'wp_twitch_oxygen_render_component', 10, 3);

// Initialisierung
function wp_twitch_oxygen_init() {
    if (defined('OXYGEN_VSB_VERSION')) {
        new WP_Twitch_Oxygen_Integration();
    }
}
add_action('init', 'wp_twitch_oxygen_init');

// Admin Notice für Oxygen
function wp_twitch_oxygen_admin_notice() {
    if (!defined('OXYGEN_VSB_VERSION')) {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen && $screen->id === 'ct_oxygen_settings') {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php _e('🎮 Twitch Stream Plugin: Oxygen Builder Components sind jetzt verfügbar!', 'wp-twitch-stream'); ?>
                <a href="<?php echo admin_url('options-general.php?page=twitch-api-settings'); ?>">
                    <?php _e('API-Einstellungen konfigurieren', 'wp-twitch-stream'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'wp_twitch_oxygen_admin_notice');
?>
