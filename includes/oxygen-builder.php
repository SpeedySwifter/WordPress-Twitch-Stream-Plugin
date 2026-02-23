<?php
/**
 * Oxygen Builder Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Oxygen_Integration {
    
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
        $this->register_spswifter_twitch_stream_component();
        
        // Twitch Grid Component
        $this->register_spswifter_twitch_grid_component();
    }
    
    /**
     * Twitch Stream Component
     */
    private function register_spswifter_twitch_stream_component() {
        $component = array(
            'name' => 'spswifter_twitch_stream',
            'title' => __('Twitch Stream', 'speedyswifter-stream-integrator-for-twitch'),
            'description' => __('Bettet einen Twitch Stream ein', 'speedyswifter-stream-integrator-for-twitch'),
            'icon' => 'oxygen-icon-video',
            'wrapper' => true,
            'params' => array(
                'channel' => array(
                    'type' => 'text',
                    'name' => __('Kanal', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '',
                    'placeholder' => __('z.B. shroud', 'speedyswifter-stream-integrator-for-twitch'),
                    'help' => __('Gib den Twitch-Benutzernamen ein', 'speedyswifter-stream-integrator-for-twitch'),
                ),
                'width' => array(
                    'type' => 'text',
                    'name' => __('Breite', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '100%',
                    'placeholder' => __('100% oder 800px', 'speedyswifter-stream-integrator-for-twitch'),
                ),
                'height' => array(
                    'type' => 'number',
                    'name' => __('Höhe', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => 480,
                    'min' => 200,
                    'max' => 1080,
                ),
                'autoplay' => array(
                    'type' => 'checkbox',
                    'name' => __('Autoplay', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '1',
                ),
                'muted' => array(
                    'type' => 'checkbox',
                    'name' => __('Stummgeschaltet', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '0',
                ),
                'show_info' => array(
                    'type' => 'checkbox',
                    'name' => __('Stream-Infos anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '0',
                ),
                'info_layout' => array(
                    'type' => 'select',
                    'name' => __('Info Layout', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => 'horizontal',
                    'options' => array(
                        'horizontal' => __('Horizontal', 'speedyswifter-stream-integrator-for-twitch'),
                        'vertical' => __('Vertikal', 'speedyswifter-stream-integrator-for-twitch'),
                        'compact' => __('Kompakt', 'speedyswifter-stream-integrator-for-twitch'),
                    ),
                ),
            ),
            'advanced' => array(
                'id' => 'spswifter_twitch_stream_' . uniqid(),
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
    private function register_spswifter_twitch_grid_component() {
        $component = array(
            'name' => 'spswifter_twitch_grid',
            'title' => __('Twitch Stream Grid', 'speedyswifter-stream-integrator-for-twitch'),
            'description' => __('Zeigt mehrere Twitch Streams im Grid an', 'speedyswifter-stream-integrator-for-twitch'),
            'icon' => 'oxygen-icon-grid',
            'wrapper' => true,
            'params' => array(
                'channels' => array(
                    'type' => 'text',
                    'name' => __('Kanäle', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '',
                    'placeholder' => __('shroud, ninja, pokimane', 'speedyswifter-stream-integrator-for-twitch'),
                    'help' => __('Kommagetrennte Liste von Twitch-Kanälen', 'speedyswifter-stream-integrator-for-twitch'),
                ),
                'columns' => array(
                    'type' => 'number',
                    'name' => __('Spalten', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => 3,
                    'min' => 1,
                    'max' => 6,
                ),
                'layout' => array(
                    'type' => 'select',
                    'name' => __('Layout', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => 'grid',
                    'options' => array(
                        'grid' => __('Grid', 'speedyswifter-stream-integrator-for-twitch'),
                        'list' => __('Liste', 'speedyswifter-stream-integrator-for-twitch'),
                        'masonry' => __('Masonry', 'speedyswifter-stream-integrator-for-twitch'),
                    ),
                ),
                'gap' => array(
                    'type' => 'text',
                    'name' => __('Abstand', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '20px',
                    'placeholder' => __('20px', 'speedyswifter-stream-integrator-for-twitch'),
                ),
                'responsive' => array(
                    'type' => 'checkbox',
                    'name' => __('Responsive', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '1',
                ),
                'show_player' => array(
                    'type' => 'checkbox',
                    'name' => __('Player anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '1',
                ),
                'show_info' => array(
                    'type' => 'checkbox',
                    'name' => __('Informationen anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => '1',
                ),
                'player_height' => array(
                    'type' => 'number',
                    'name' => __('Player Höhe', 'speedyswifter-stream-integrator-for-twitch'),
                    'value' => 200,
                    'min' => 100,
                    'max' => 400,
                ),
            ),
            'advanced' => array(
                'id' => 'spswifter_twitch_grid_' . uniqid(),
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
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/oxygen-builder.js',
            array('oxygen-builder'),
            SPSWIFTER_TWITCH_VERSION,
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
                SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/oxygen-frontend.css',
                array(),
                SPSWIFTER_TWITCH_VERSION
            );
        }
    }
    
    /**
     * Component Rendering
     */
    public static function render_component($component, $atts) {
        $output = '';
        
        switch ($component) {
            case 'spswifter_twitch_stream':
                $output = self::render_spswifter_twitch_stream($atts);
                break;
            case 'spswifter_twitch_grid':
                $output = self::render_spswifter_twitch_grid($atts);
                break;
        }
        
        return $output;
    }
    
    /**
     * Twitch Stream rendern
     */
    private static function render_spswifter_twitch_stream($atts) {
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
        
        $output = spswifter_twitch_stream_shortcode($stream_atts);
        
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
            
            $output .= spswifter_twitch_stream_info_shortcode($info_atts);
        }
        
        return $output;
    }
    
    /**
     * Twitch Grid rendern
     */
    private static function render_spswifter_twitch_grid($atts) {
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
        
        return spswifter_twitch_streams_grid_shortcode($grid_atts);
    }
}

// Oxygen Builder Hook
function spswifter_twitch_oxygen_render_component($output, $component, $atts) {
    if (strpos($component, 'spswifter_twitch_') === 0) {
        return SPSWIFTER_Twitch_Oxygen_Integration::render_component($component, $atts);
    }
    return $output;
}
add_filter('oxygen_vsb_component_render', 'spswifter_twitch_oxygen_render_component', 10, 3);

// Initialisierung
function spswifter_twitch_oxygen_init() {
    if (defined('OXYGEN_VSB_VERSION')) {
        new SPSWIFTER_Twitch_Oxygen_Integration();
    }
}
add_action('init', 'spswifter_twitch_oxygen_init');

// Admin Notice für Oxygen
function spswifter_twitch_oxygen_admin_notice() {
    if (!defined('OXYGEN_VSB_VERSION')) {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen && $screen->id === 'ct_oxygen_settings') {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php esc_html_e('🎮 SpeedySwifter Twitch: Oxygen Builder Components sind jetzt verfügbar!', 'speedyswifter-stream-integrator-for-twitch'); ?>
                <a href="<?php echo admin_url('options-general.php?page=twitch-api-settings'); ?>">
                    <?php esc_html_e('API-Einstellungen konfigurieren', 'speedyswifter-stream-integrator-for-twitch'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'spswifter_twitch_oxygen_admin_notice');
?>
