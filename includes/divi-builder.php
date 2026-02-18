<?php
/**
 * Divi Builder Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Divi_Integration {
    
    public function __construct() {
        add_action('divi_extensions_init', array($this, 'register_divi_extension'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_divi_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Divi Extension registrieren
     */
    public function register_divi_extension() {
        if (!class_exists('ET_Builder_Module')) {
            return;
        }
        
        // Twitch Stream Module
        require_once WP_TWITCH_PLUGIN_DIR . 'includes/divi-modules/stream-module.php';
        new Divi_Twitch_Stream_Module();
        
        // Twitch Grid Module
        require_once WP_TWITCH_PLUGIN_DIR . 'includes/divi-modules/grid-module.php';
        new Divi_Twitch_Grid_Module();
    }
    
    /**
     * Frontend Scripts laden
     */
    public function enqueue_divi_scripts() {
        if (et_core_is_fb_enabled() || et_core_is_builder_active()) {
            wp_enqueue_style(
                'twitch-divi-builder',
                WP_TWITCH_PLUGIN_URL . 'assets/css/divi-builder.css',
                array(),
                WP_TWITCH_VERSION
            );
        }
    }
    
    /**
     * Admin Scripts laden
     */
    public function enqueue_admin_scripts() {
        if (et_core_is_fb_enabled() || et_core_is_builder_active()) {
            wp_enqueue_script(
                'twitch-divi-builder',
                WP_TWITCH_PLUGIN_URL . 'assets/js/divi-builder.js',
                array('jquery'),
                WP_TWITCH_VERSION,
                true
            );
            
            // Divi Builder Daten
            wp_localize_script('twitch-divi-builder', 'twitchDiviData', array(
                'apiConnected' => !empty(get_option('twitch_client_id')) && !empty(get_option('twitch_client_secret')),
                'adminUrl' => admin_url('options-general.php?page=twitch-api-settings'),
                'strings' => array(
                    'channelPlaceholder' => __('z.B. shroud', 'wp-twitch-stream'),
                    'channelsPlaceholder' => __('shroud, ninja, pokimane', 'wp-twitch-stream'),
                    'apiNotConnected' => __('Twitch API nicht verbunden', 'wp-twitch-stream'),
                    'goToSettings' => __('API-Einstellungen', 'wp-twitch-stream'),
                ),
            ));
        }
    }
    
    /**
     * Divi Module Helper Functions
     */
    public static function get_field_defaults($field_type, $default_value = '') {
        $defaults = array(
            'text' => '',
            'number' => 0,
            'select' => '',
            'yes_no' => 'off',
            'color' => '#000000',
            'url' => '',
            'email' => '',
            'phone' => '',
            'date' => '',
            'time' => '',
            'range' => 0,
            'upload' => '',
        );
        
        return isset($defaults[$field_type]) ? $defaults[$field_type] : $default_value;
    }
    
    /**
     * Divi Module Field Helper
     */
    public static function render_field($field) {
        $output = '';
        
        switch ($field['type']) {
            case 'text':
                $output = '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field['value']) . '" placeholder="' . esc_attr($field['placeholder'] ?? '') . '" />';
                break;
                
            case 'number':
                $output = '<input type="number" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field['value']) . '" min="' . esc_attr($field['min'] ?? 0) . '" max="' . esc_attr($field['max'] ?? 999999) . '" />';
                break;
                
            case 'select':
                $output = '<select name="' . esc_attr($field['name']) . '">';
                foreach ($field['options'] as $value => $label) {
                    $output .= '<option value="' . esc_attr($value) . '" ' . selected($field['value'], $value, false) . '>' . esc_html($label) . '</option>';
                }
                $output .= '</select>';
                break;
                
            case 'yes_no':
                $output = '<select name="' . esc_attr($field['name']) . '">';
                $output .= '<option value="on" ' . selected($field['value'], 'on', false) . '>' . __('Ja', 'wp-twitch-stream') . '</option>';
                $output .= '<option value="off" ' . selected($field['value'], 'off', false) . '>' . __('Nein', 'wp-twitch-stream') . '</option>';
                $output .= '</select>';
                break;
                
            case 'color':
                $output = '<input type="color" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field['value']) . '" />';
                break;
                
            default:
                $output = '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field['value']) . '" />';
                break;
        }
        
        return $output;
    }
}

// Initialisierung
function wp_twitch_divi_init() {
    if (class_exists('ET_Builder_Module')) {
        new WP_Twitch_Divi_Integration();
    }
}
add_action('init', 'wp_twitch_divi_init');

// Admin Notice für Divi
function wp_twitch_divi_admin_notice() {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen && $screen->id === 'et_divi_options') {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php _e('🎮 Twitch Stream Plugin: Divi Builder Module sind jetzt verfügbar!', 'wp-twitch-stream'); ?>
                <a href="<?php echo admin_url('options-general.php?page=twitch-api-settings'); ?>">
                    <?php _e('API-Einstellungen konfigurieren', 'wp-twitch-stream'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'wp_twitch_divi_admin_notice');

// Divi Visual Builder Integration
function wp_twitch_divi_visual_builder_integration() {
    if (!et_core_is_fb_enabled()) {
        return;
    }
    
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Twitch Stream Module für Visual Builder
            if (typeof window.ET_Builder !== 'undefined') {
                window.ET_Builder.Modules.register('twitch_stream', {
                    name: '<?php _e('Twitch Stream', 'wp-twitch-stream'); ?>',
                    icon: 'video',
                    category: 'Twitch Stream',
                    settings: {
                        channel: {
                            type: 'text',
                            label: '<?php _e('Kanal', 'wp-twitch-stream'); ?>',
                            placeholder: '<?php _e('z.B. shroud', 'wp-twitch-stream'); ?>'
                        },
                        width: {
                            type: 'text',
                            label: '<?php _e('Breite', 'wp-twitch-stream'); ?>',
                            default: '100%'
                        },
                        height: {
                            type: 'number',
                            label: '<?php _e('Höhe', 'wp-twitch-stream'); ?>',
                            default: 480
                        },
                        autoplay: {
                            type: 'yes_no',
                            label: '<?php _e('Autoplay', 'wp-twitch-stream'); ?>',
                            default: 'on'
                        },
                        muted: {
                            type: 'yes_no',
                            label: '<?php _e('Stummgeschaltet', 'wp-twitch-stream'); ?>',
                            default: 'off'
                        },
                        show_info: {
                            type: 'yes_no',
                            label: '<?php _e('Stream-Infos anzeigen', 'wp-twitch-stream'); ?>',
                            default: 'off'
                        }
                    },
                    render: function(props) {
                        var shortcode = '[twitch_stream channel="' + props.channel + '" width="' + props.width + '" height="' + props.height + '" autoplay="' + props.autoplay + '" muted="' + props.muted + '"]';
                        
                        if (props.show_info === 'on') {
                            shortcode += '[twitch_stream_info channel="' + props.channel + '"]';
                        }
                        
                        return shortcode;
                    }
                });
                
                window.ET_Builder.Modules.register('twitch_grid', {
                    name: '<?php _e('Twitch Stream Grid', 'wp-twitch-stream'); ?>',
                    icon: 'grid',
                    category: 'Twitch Stream',
                    settings: {
                        channels: {
                            type: 'text',
                            label: '<?php _e('Kanäle', 'wp-twitch-stream'); ?>',
                            placeholder: '<?php _e('shroud, ninja, pokimane', 'wp-twitch-stream'); ?>'
                        },
                        columns: {
                            type: 'number',
                            label: '<?php _e('Spalten', 'wp-twitch-stream'); ?>',
                            default: 3
                        },
                        layout: {
                            type: 'select',
                            label: '<?php _e('Layout', 'wp-twitch-stream'); ?>',
                            default: 'grid',
                            options: {
                                'grid': '<?php _e('Grid', 'wp-twitch-stream'); ?>',
                                'list': '<?php _e('Liste', 'wp-twitch-stream'); ?>',
                                'masonry': '<?php _e('Masonry', 'wp-twitch-stream'); ?>'
                            }
                        },
                        gap: {
                            type: 'text',
                            label: '<?php _e('Abstand', 'wp-twitch-stream'); ?>',
                            default: '20px'
                        },
                        responsive: {
                            type: 'yes_no',
                            label: '<?php _e('Responsive', 'wp-twitch-stream'); ?>',
                            default: 'on'
                        },
                        show_player: {
                            type: 'yes_no',
                            label: '<?php _e('Player anzeigen', 'wp-twitch-stream'); ?>',
                            default: 'on'
                        },
                        show_info: {
                            type: 'yes_no',
                            label: '<?php _e('Informationen anzeigen', 'wp-twitch-stream'); ?>',
                            default: 'on'
                        }
                    },
                    render: function(props) {
                        var shortcode = '[twitch_streams_grid channels="' + props.channels + '" columns="' + props.columns + '" layout="' + props.layout + '" gap="' + props.gap + '" responsive="' + props.responsive + '" show_player="' + props.show_player + '" show_info="' + props.show_info + '"]';
                        
                        return shortcode;
                    }
                });
            }
        });
    </script>
    <?php
}
add_action('admin_footer', 'wp_twitch_divi_visual_builder_integration');
?>
