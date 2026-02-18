<?php
/**
 * Universal Page Builder Compatibility Layer
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Page_Builder_Compatibility {
    
    private $supported_builders = array(
        'elementor' => false,
        'oxygen' => false,
        'divi' => false,
        'gutenberg' => false,
        'beaver_builder' => false,
        'brizy' => false,
        'visual_composer' => false,
        'wpbakery' => false,
        'fusion_builder' => false,
        'siteorigin' => false,
        'thrive' => false,
        'elementor_pro' => false,
    );
    
    public function __construct() {
        $this->detect_builders();
        $this->init_compatibility();
        add_action('init', array($this, 'register_universal_widgets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_compatibility_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Aktive Page Builder erkennen
     */
    private function detect_builders() {
        // Elementor
        $this->supported_builders['elementor'] = class_exists('Elementor\Plugin');
        $this->supported_builders['elementor_pro'] = class_exists('ElementorPro\Plugin');
        
        // Oxygen Builder
        $this->supported_builders['oxygen'] = defined('OXYGEN_VSB_VERSION');
        
        // Divi Builder
        $this->supported_builders['divi'] = class_exists('ET_Builder_Module');
        
        // Gutenberg (WordPress 5.0+)
        $this->supported_builders['gutenberg'] = function_exists('register_block_type');
        
        // Beaver Builder
        $this->supported_builders['beaver_builder'] = class_exists('FLBuilderModel');
        
        // Brizy
        $this->supported_builders['brizy'] = defined('BRIZY_VERSION');
        
        // Visual Composer
        $this->supported_builders['visual_composer'] = class_exists('WPBakeryShortCodesContainer');
        $this->supported_builders['wpbakery'] = $this->supported_builders['visual_composer'];
        
        // Fusion Builder
        $this->supported_builders['fusion_builder'] = class_exists('FusionBuilder');
        
        // SiteOrigin
        $this->supported_builders['siteorigin'] = class_exists('SiteOrigin_Panels');
        
        // Thrive Architect
        $this->supported_builders['thrive'] = class_exists('Thrive_Leads');
    }
    
    /**
     * Compatibility initialisieren
     */
    private function init_compatibility() {
        // Elementor Integration
        if ($this->supported_builders['elementor']) {
            require_once WP_TWITCH_PLUGIN_DIR . 'includes/elementor-widget.php';
        }
        
        // Oxygen Builder Integration
        if ($this->supported_builders['oxygen']) {
            require_once WP_TWITCH_PLUGIN_DIR . 'includes/oxygen-builder.php';
        }
        
        // Divi Builder Integration
        if ($this->supported_builders['divi']) {
            require_once WP_TWITCH_PLUGIN_DIR . 'includes/divi-builder.php';
        }
        
        // Gutenberg Integration (bereits in gutenberg-block.php)
        if ($this->supported_builders['gutenberg']) {
            require_once WP_TWITCH_PLUGIN_DIR . 'includes/gutenberg-block.php';
        }
        
        // Universal Shortcode Support
        add_action('init', array($this, 'add_universal_shortcodes'));
        
        // Universal Widget Support
        add_action('widgets_init', array($this, 'register_universal_widgets'));
    }
    
    /**
     * Universal Shortcodes registrieren
     */
    public function add_universal_shortcodes() {
        // Universal Twitch Stream Shortcode
        add_shortcode('twitch_stream_universal', array($this, 'render_universal_stream'));
        
        // Universal Twitch Grid Shortcode
        add_shortcode('twitch_grid_universal', array($this, 'render_universal_grid'));
        
        // Universal Twitch Info Shortcode
        add_shortcode('twitch_info_universal', array($this, 'render_universal_info'));
    }
    
    /**
     * Universal Widgets registrieren
     */
    public function register_universal_widgets() {
        // WordPress Widget für Twitch Stream
        register_widget('WP_Twitch_Stream_Widget');
        
        // WordPress Widget für Twitch Grid
        register_widget('WP_Twitch_Grid_Widget');
    }
    
    /**
     * Universal Stream Shortcode
     */
    public function render_universal_stream($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'width' => '100%',
            'height' => '480',
            'autoplay' => 'true',
            'muted' => 'false',
            'responsive' => 'true',
            'builder' => $this->get_current_builder(),
        ), $atts);
        
        return wp_twitch_stream_shortcode($atts);
    }
    
    /**
     * Universal Grid Shortcode
     */
    public function render_universal_grid($atts) {
        $atts = shortcode_atts(array(
            'channels' => '',
            'columns' => '3',
            'layout' => 'grid',
            'gap' => '20px',
            'responsive' => 'true',
            'show_player' => 'true',
            'show_info' => 'true',
            'builder' => $this->get_current_builder(),
        ), $atts);
        
        return wp_twitch_streams_grid_shortcode($atts);
    }
    
    /**
     * Universal Info Shortcode
     */
    public function render_universal_info($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'layout' => 'horizontal',
            'show_title' => 'true',
            'show_game' => 'true',
            'show_viewers' => 'true',
            'show_thumbnail' => 'true',
            'show_avatar' => 'true',
            'builder' => $this->get_current_builder(),
        ), $atts);
        
        return wp_twitch_stream_info_shortcode($atts);
    }
    
    /**
     * Aktuellen Builder erkennen
     */
    private function get_current_builder() {
        if ($this->is_elementor_active()) {
            return 'elementor';
        } elseif ($this->is_oxygen_active()) {
            return 'oxygen';
        } elseif ($this->is_divi_active()) {
            return 'divi';
        } elseif ($this->is_gutenberg_active()) {
            return 'gutenberg';
        } elseif ($this->is_beaver_builder_active()) {
            return 'beaver_builder';
        } elseif ($this->is_visual_composer_active()) {
            return 'visual_composer';
        } elseif ($this->is_fusion_builder_active()) {
            return 'fusion_builder';
        } elseif ($this->is_siteorigin_active()) {
            return 'siteorigin';
        } elseif ($this->is_thrive_active()) {
            return 'thrive';
        }
        
        return 'default';
    }
    
    /**
     * Builder Status Checks
     */
    private function is_elementor_active() {
        return $this->supported_builders['elementor'] && 
               (defined('ELEMENTOR_VERSION') || 
                isset($_GET['elementor-preview']) || 
                isset($_GET['action']) && $_GET['action'] === 'elementor');
    }
    
    private function is_oxygen_active() {
        return $this->supported_builders['oxygen'] && 
               (isset($_GET['ct_builder']) || 
                isset($_GET['oxygen_iframe']));
    }
    
    private function is_divi_active() {
        return $this->supported_builders['divi'] && 
               (et_core_is_fb_enabled() || 
                et_core_is_builder_active() || 
                isset($_GET['et_fb']));
    }
    
    private function is_gutenberg_active() {
        return $this->supported_builders['gutenberg'] && 
               (function_exists('use_block_editor_for_post_type') || 
                isset($_GET['context']) && $_GET['context'] === 'edit');
    }
    
    private function is_beaver_builder_active() {
        return $this->supported_builders['beaver_builder'] && 
               (FLBuilderModel::is_builder_active() || 
                isset($_GET['fl_builder']));
    }
    
    private function is_visual_composer_active() {
        return $this->supported_builders['visual_composer'] && 
               (defined('WPB_VC_VERSION') || 
                isset($_GET['vc_action']));
    }
    
    private function is_fusion_builder_active() {
        return $this->supported_builders['fusion_builder'] && 
               (class_exists('FusionBuilder') || 
                isset($_GET['fusion_builder']));
    }
    
    private function is_siteorigin_active() {
        return $this->supported_builders['siteorigin'] && 
               (SiteOrigin_Panels::is_active() || 
                isset($_GET['siteorigin_panels']));
    }
    
    private function is_thrive_active() {
        return $this->supported_builders['thrive'] && 
               (class_exists('Thrive_Leads') || 
                isset($_GET['tve']));
    }
    
    /**
     * Compatibility Scripts laden
     */
    public function enqueue_compatibility_scripts() {
        // Universal CSS für alle Page Builder
        wp_enqueue_style(
            'twitch-page-builder-compatibility',
            WP_TWITCH_PLUGIN_URL . 'assets/css/page-builder-compatibility.css',
            array(),
            WP_TWITCH_VERSION
        );
        
        // Builder-spezifische Scripts
        if ($this->is_elementor_active()) {
            wp_enqueue_script(
                'twitch-elementor-compatibility',
                WP_TWITCH_PLUGIN_URL . 'assets/js/elementor-compatibility.js',
                array('jquery'),
                WP_TWITCH_VERSION,
                true
            );
        }
        
        if ($this->is_divi_active()) {
            wp_enqueue_script(
                'twitch-divi-compatibility',
                WP_TWITCH_PLUGIN_URL . 'assets/js/divi-compatibility.js',
                array('jquery'),
                WP_TWITCH_VERSION,
                true
            );
        }
    }
    
    /**
     * Admin Scripts laden
     */
    public function enqueue_admin_scripts() {
        $screen = get_current_screen();
        
        if ($screen && $this->is_builder_admin_screen($screen->id)) {
            wp_enqueue_script(
                'twitch-page-builder-admin',
                WP_TWITCH_PLUGIN_URL . 'assets/js/page-builder-admin.js',
                array('jquery'),
                WP_TWITCH_VERSION,
                true
            );
            
            wp_localize_script('twitch-page-builder-admin', 'twitchBuilderData', array(
                'supportedBuilders' => $this->supported_builders,
                'currentBuilder' => $this->get_current_builder(),
                'apiConnected' => !empty(get_option('twitch_client_id')) && !empty(get_option('twitch_client_secret')),
                'adminUrl' => admin_url('options-general.php?page=twitch-api-settings'),
                'strings' => array(
                    'apiNotConnected' => __('Twitch API nicht verbunden', 'wp-twitch-stream'),
                    'goToSettings' => __('API-Einstellungen', 'wp-twitch-stream'),
                    'builderNotSupported' => __('Builder wird nicht unterstützt', 'wp-twitch-stream'),
                ),
            ));
        }
    }
    
    /**
     * Builder Admin Screen erkennen
     */
    private function is_builder_admin_screen($screen_id) {
        $builder_screens = array(
            'elementor_page_elementor-app',
            'ct_oxygen_settings',
            'et_divi_options',
            'page_builder_page_fl-builder-settings',
            'vc-general',
            'fusion-builder-settings',
            'siteorigin_panels_settings_page',
        );
        
        return in_array($screen_id, $builder_screens) || 
               strpos($screen_id, 'elementor') !== false || 
               strpos($screen_id, 'oxygen') !== false || 
               strpos($screen_id, 'divi') !== false;
    }
    
    /**
     * Unterstützte Builder abrufen
     */
    public function get_supported_builders() {
        return $this->supported_builders;
    }
    
    /**
     * Builder Status abrufen
     */
    public function get_builder_status($builder) {
        return isset($this->supported_builders[$builder]) ? $this->supported_builders[$builder] : false;
    }
}

// WordPress Widgets für universelle Unterstützung
class WP_Twitch_Stream_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'wp_twitch_stream_widget',
            __('Twitch Stream', 'wp-twitch-stream'),
            array('description' => __('Zeigt einen Twitch Stream an', 'wp-twitch-stream'))
        );
    }
    
    public function widget($args, $instance) {
        $channel = !empty($instance['channel']) ? $instance['channel'] : '';
        $width = !empty($instance['width']) ? $instance['width'] : '100%';
        $height = !empty($instance['height']) ? $instance['height'] : '300';
        $autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 'false';
        $muted = !empty($instance['muted']) ? $instance['muted'] : 'true';
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $stream_atts = array(
            'channel' => $channel,
            'width' => $width,
            'height' => $height,
            'autoplay' => $autoplay,
            'muted' => $muted,
        );
        
        echo wp_twitch_stream_shortcode($stream_atts);
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $channel = !empty($instance['channel']) ? $instance['channel'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $width = !empty($instance['width']) ? $instance['width'] : '100%';
        $height = !empty($instance['height']) ? $instance['height'] : '300';
        $autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 'false';
        $muted = !empty($instance['muted']) ? $instance['muted'] : 'true';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('channel'); ?>"><?php _e('Kanal:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo esc_attr($channel); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Breite:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Höhe:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo esc_attr($height); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php _e('Autoplay:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>">
                <option value="true" <?php selected($autoplay, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($autoplay, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('muted'); ?>"><?php _e('Stumm:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('muted'); ?>" name="<?php echo $this->get_field_name('muted'); ?>">
                <option value="true" <?php selected($muted, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($muted, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['channel'] = (!empty($new_instance['channel'])) ? sanitize_text_field($new_instance['channel']) : '';
        $instance['width'] = (!empty($new_instance['width'])) ? sanitize_text_field($new_instance['width']) : '';
        $instance['height'] = (!empty($new_instance['height'])) ? sanitize_text_field($new_instance['height']) : '';
        $instance['autoplay'] = (!empty($new_instance['autoplay'])) ? sanitize_text_field($new_instance['autoplay']) : '';
        $instance['muted'] = (!empty($new_instance['muted'])) ? sanitize_text_field($new_instance['muted']) : '';
        
        return $instance;
    }
}

class WP_Twitch_Grid_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'wp_twitch_grid_widget',
            __('Twitch Stream Grid', 'wp-twitch-stream'),
            array('description' => __('Zeigt mehrere Twitch Streams im Grid an', 'wp-twitch-stream'))
        );
    }
    
    public function widget($args, $instance) {
        $channels = !empty($instance['channels']) ? $instance['channels'] : '';
        $columns = !empty($instance['columns']) ? $instance['columns'] : '2';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'grid';
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $grid_atts = array(
            'channels' => $channels,
            'columns' => $columns,
            'layout' => $layout,
            'show_player' => 'false',
            'show_info' => 'true',
        );
        
        echo wp_twitch_streams_grid_shortcode($grid_atts);
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $channels = !empty($instance['channels']) ? $instance['channels'] : '';
        $columns = !empty($instance['columns']) ? $instance['columns'] : '2';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'grid';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('channels'); ?>"><?php _e('Kanäle:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('channels'); ?>" name="<?php echo $this->get_field_name('channels'); ?>" type="text" value="<?php echo esc_attr($channels); ?>">
            <small><?php _e('Kommagetrennt: kanal1, kanal2, kanal3', 'wp-twitch-stream'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Spalten:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('columns'); ?>" name="<?php echo $this->get_field_name('columns'); ?>" type="number" value="<?php echo esc_attr($columns); ?>" min="1" max="4">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>"><?php _e('Layout:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
                <option value="grid" <?php selected($layout, 'grid'); ?>><?php _e('Grid', 'wp-twitch-stream'); ?></option>
                <option value="list" <?php selected($layout, 'list'); ?>><?php _e('Liste', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['channels'] = (!empty($new_instance['channels'])) ? sanitize_text_field($new_instance['channels']) : '';
        $instance['columns'] = (!empty($new_instance['columns'])) ? sanitize_text_field($new_instance['columns']) : '';
        $instance['layout'] = (!empty($new_instance['layout'])) ? sanitize_text_field($new_instance['layout']) : '';
        
        return $instance;
    }
}

// Initialisierung
function wp_twitch_page_builder_compatibility_init() {
    new WP_Twitch_Page_Builder_Compatibility();
}
add_action('init', 'wp_twitch_page_builder_compatibility_init');

// Admin Notice für Page Builder Compatibility
function wp_twitch_page_builder_admin_notice() {
    $compatibility = new WP_Twitch_Page_Builder_Compatibility();
    $supported_builders = $compatibility->get_supported_builders();
    $active_builders = array_filter($supported_builders);
    
    if (!empty($active_builders)) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php _e('🎮 Twitch Stream Plugin: Kompatibel mit ', 'wp-twitch-stream'); ?>
                <strong><?php echo implode(', ', array_keys($active_builders)); ?></strong>
                <?php _e('Page Buildern!', 'wp-twitch-stream'); ?>
                <a href="<?php echo admin_url('options-general.php?page=twitch-api-settings'); ?>">
                    <?php _e('API-Einstellungen konfigurieren', 'wp-twitch-stream'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'wp_twitch_page_builder_admin_notice');
?>
