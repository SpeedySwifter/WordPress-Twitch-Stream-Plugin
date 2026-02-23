<?php
/**
 * Divi Builder Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Divi_Integration {
    
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
        require_once SPSWIFTER_TWITCH_PLUGIN_DIR . 'includes/divi-modules/stream-module.php';
        new Divi_Twitch_Stream_Module();
        
        // Twitch Grid Module
        require_once SPSWIFTER_TWITCH_PLUGIN_DIR . 'includes/divi-modules/grid-module.php';
        new Divi_Twitch_Grid_Module();
    }
    
    /**
     * Frontend Scripts laden
     */
    public function enqueue_divi_scripts() {
        if (et_core_is_fb_enabled() || et_core_is_builder_active()) {
            wp_enqueue_style(
                'twitch-divi-builder',
                SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/divi-builder.css',
                array(),
                SPSWIFTER_TWITCH_VERSION
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
                SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/divi-builder.js',
                array('jquery'),
                SPSWIFTER_TWITCH_VERSION,
                true
            );
            
            // Divi Builder Daten
            wp_localize_script('twitch-divi-builder', 'twitchDiviData', array(
                'apiConnected' => !empty(get_option('spswifter_twitch_client_id')) && !empty(get_option('spswifter_twitch_client_secret')),
                'adminUrl' => admin_url('options-general.php?page=twitch-api-settings'),
                'strings' => array(
                    'channelPlaceholder' => __('z.B. shroud', 'speedyswifter-twitch'),
                    'channelsPlaceholder' => __('shroud, ninja, pokimane', 'speedyswifter-twitch'),
                    'apiNotConnected' => __('Twitch API nicht verbunden', 'speedyswifter-twitch'),
                    'goToSettings' => __('API-Einstellungen', 'speedyswifter-twitch'),
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
                $output .= '<option value="on" ' . selected($field['value'], 'on', false) . '>' . __('Ja', 'speedyswifter-twitch') . '</option>';
                $output .= '<option value="off" ' . selected($field['value'], 'off', false) . '>' . __('Nein', 'speedyswifter-twitch') . '</option>';
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
function spswifter_spswifter_twitch_divi_init() {
    if (class_exists('ET_Builder_Module')) {
        new SPSWIFTER_Twitch_Divi_Integration();
    }
}
add_action('init', 'spswifter_spswifter_twitch_divi_init');

// Admin Notice für Divi
function spswifter_spswifter_twitch_divi_admin_notice() {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen && $screen->id === 'et_divi_options') {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php esc_html_e('🎮 SpeedySwifter Twitch: Divi Builder Module sind jetzt verfügbar!', 'speedyswifter-twitch'); ?>
                <a href="<?php echo admin_url('options-general.php?page=twitch-api-settings'); ?>">
                    <?php esc_html_e('API-Einstellungen konfigurieren', 'speedyswifter-twitch'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'spswifter_spswifter_twitch_divi_admin_notice');

// Divi Visual Builder Integration
function spswifter_spswifter_twitch_divi_visual_builder_integration() {
    if (!et_core_is_fb_enabled()) {
        return;
    }
    
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Twitch Stream Module für Visual Builder
            if (typeof window.ET_Builder !== 'undefined') {
                window.ET_Builder.Modules.register('spswifter_twitch_stream', {
                    name: '<?php esc_html__('Twitch Stream', 'speedyswifter-twitch'); ?>',
                    icon: 'video',
                    category: 'Twitch Stream',
                    settings: {
                        channel: {
                            type: 'text',
                            label: '<?php esc_html__('Kanal', 'speedyswifter-twitch'); ?>',
                            placeholder: '<?php esc_html__('z.B. shroud', 'speedyswifter-twitch'); ?>'
                        },
                        width: {
                            type: 'text',
                            label: '<?php esc_html__('Breite', 'speedyswifter-twitch'); ?>',
                            default: '100%'
                        },
                        height: {
                            type: 'number',
                            label: '<?php esc_html__('Höhe', 'speedyswifter-twitch'); ?>',
                            default: 480
                        },
                        autoplay: {
                            type: 'yes_no',
                            label: '<?php esc_html__('Autoplay', 'speedyswifter-twitch'); ?>',
                            default: 'on'
                        },
                        muted: {
                            type: 'yes_no',
                            label: '<?php esc_html__('Stummgeschaltet', 'speedyswifter-twitch'); ?>',
                            default: 'off'
                        },
                        show_info: {
                            type: 'yes_no',
                            label: '<?php esc_html__('Stream-Infos anzeigen', 'speedyswifter-twitch'); ?>',
                            default: 'off'
                        }
                    },
                    render: function(props) {
                        var shortcode = '[spswifter_twitch_stream channel="' + props.channel + '" width="' + props.width + '" height="' + props.height + '" autoplay="' + props.autoplay + '" muted="' + props.muted + '"]';
                        
                        if (props.show_info === 'on') {
                            shortcode += '[spswifter_twitch_stream_info channel="' + props.channel + '"]';
                        }
                        
                        return shortcode;
                    }
                });
                
                window.ET_Builder.Modules.register('spswifter_twitch_grid', {
                    name: '<?php esc_html__('Twitch Stream Grid', 'speedyswifter-twitch'); ?>',
                    icon: 'grid',
                    category: 'Twitch Stream',
                    settings: {
                        channels: {
                            type: 'text',
                            label: '<?php esc_html__('Kanäle', 'speedyswifter-twitch'); ?>',
                            placeholder: '<?php esc_html__('shroud, ninja, pokimane', 'speedyswifter-twitch'); ?>'
                        },
                        columns: {
                            type: 'number',
                            label: '<?php esc_html__('Spalten', 'speedyswifter-twitch'); ?>',
                            default: 3
                        },
                        layout: {
                            type: 'select',
                            label: '<?php esc_html__('Layout', 'speedyswifter-twitch'); ?>',
                            default: 'grid',
                            options: {
                                'grid': '<?php esc_html__('Grid', 'speedyswifter-twitch'); ?>',
                                'list': '<?php esc_html__('Liste', 'speedyswifter-twitch'); ?>',
                                'masonry': '<?php esc_html__('Masonry', 'speedyswifter-twitch'); ?>'
                            }
                        },
                        gap: {
                            type: 'text',
                            label: '<?php esc_html__('Abstand', 'speedyswifter-twitch'); ?>',
                            default: '20px'
                        },
                        responsive: {
                            type: 'yes_no',
                            label: '<?php esc_html__('Responsive', 'speedyswifter-twitch'); ?>',
                            default: 'on'
                        },
                        show_player: {
                            type: 'yes_no',
                            label: '<?php esc_html__('Player anzeigen', 'speedyswifter-twitch'); ?>',
                            default: 'on'
                        },
                        show_info: {
                            type: 'yes_no',
                            label: '<?php esc_html__('Informationen anzeigen', 'speedyswifter-twitch'); ?>',
                            default: 'on'
                        }
                    },
                    render: function(props) {
                        var shortcode = '[spswifter_twitch_streams_grid channels="' + props.channels + '" columns="' + props.columns + '" layout="' + props.layout + '" gap="' + props.gap + '" responsive="' + props.responsive + '" show_player="' + props.show_player + '" show_info="' + props.show_info + '"]';
                        
                        return shortcode;
                    }
                });
            }
        });
    </script>
    <?php
}
add_action('admin_footer', 'spswifter_spswifter_twitch_divi_visual_builder_integration');
?>
