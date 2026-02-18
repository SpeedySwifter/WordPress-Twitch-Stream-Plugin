<?php
/**
 * Divi Builder Twitch Stream Module
 */

if (!defined('ABSPATH')) {
    exit;
}

class Divi_Twitch_Stream_Module extends ET_Builder_Module {
    
    public function init() {
        $this->name = __('Twitch Stream', 'wp-twitch-stream');
        $this->slug = 'et_pb_twitch_stream';
        $this->vb_support = 'on';
        $this->main_css_element = '%%order_class%%';
        
        $this->settings_modal_toggles = array(
            'general' => array(
                'toggles' => array(
                    'main_content' => __('Stream Einstellungen', 'wp-twitch-stream'),
                    'stream_info' => __('Stream-Informationen', 'wp-twitch-stream'),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'layout' => __('Layout', 'wp-twitch-stream'),
                    'animation' => __('Animation', 'wp-twitch-stream'),
                ),
            ),
        );
    }
    
    public function get_fields() {
        return array(
            'channel' => array(
                'label' => __('Twitch Kanal', 'wp-twitch-stream'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'description' => __('Gib den Twitch-Benutzernamen ein', 'wp-twitch-stream'),
                'toggle_slug' => 'main_content',
            ),
            'width' => array(
                'label' => __('Breite', 'wp-twitch-stream'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'default' => '100%',
                'toggle_slug' => 'main_content',
            ),
            'height' => array(
                'label' => __('Höhe', 'wp-twitch-stream'),
                'type' => 'number',
                'option_category' => 'basic_option',
                'default' => 480,
                'toggle_slug' => 'main_content',
            ),
            'autoplay' => array(
                'label' => __('Autoplay', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'main_content',
            ),
            'muted' => array(
                'label' => __('Stummgeschaltet', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'off',
                'toggle_slug' => 'main_content',
            ),
            'show_info' => array(
                'label' => __('Stream-Infos anzeigen', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'off',
                'toggle_slug' => 'stream_info',
            ),
            'info_layout' => array(
                'label' => __('Info Layout', 'wp-twitch-stream'),
                'type' => 'select',
                'option_category' => 'basic_option',
                'default' => 'horizontal',
                'options' => array(
                    'horizontal' => __('Horizontal', 'wp-twitch-stream'),
                    'vertical' => __('Vertikal', 'wp-twitch-stream'),
                    'compact' => __('Kompakt', 'wp-twitch-stream'),
                ),
                'toggle_slug' => 'stream_info',
                'show_if' => array(
                    'show_info' => 'on',
                ),
            ),
            'show_avatar' => array(
                'label' => __('Avatar anzeigen', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'stream_info',
                'show_if' => array(
                    'show_info' => 'on',
                ),
            ),
            'show_thumbnail' => array(
                'label' => __('Thumbnail anzeigen', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'stream_info',
                'show_if' => array(
                    'show_info' => 'on',
                ),
            ),
            'show_game' => array(
                'label' => __('Spiel anzeigen', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'stream_info',
                'show_if' => array(
                    'show_info' => 'on',
                ),
            ),
            'show_viewers' => array(
                'label' => __('Zuschauer anzeigen', 'wp-twitch-stream'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'stream_info',
                'show_if' => array(
                    'show_info' => 'on',
                ),
            ),
            'admin_label' => array(
                'label' => __('Admin Label', 'wp-twitch-stream'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'toggle_slug' => 'main_content',
            ),
            'module_id' => array(
                'label' => __('CSS ID', 'wp-twitch-stream'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'toggle_slug' => 'layout',
            ),
            'module_class' => array(
                'label' => __('CSS Class', 'wp-twitch-stream'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'toggle_slug' => 'layout',
            ),
        );
    }
    
    public function render($attrs, $content = null, $render_slug = '') {
        $channel = $this->props['channel'];
        $width = $this->props['width'];
        $height = $this->props['height'];
        $autoplay = $this->props['autoplay'];
        $muted = $this->props['muted'];
        $show_info = $this->props['show_info'];
        $info_layout = $this->props['info_layout'];
        $show_avatar = $this->props['show_avatar'];
        $show_thumbnail = $this->props['show_thumbnail'];
        $show_game = $this->props['show_game'];
        $show_viewers = $this->props['show_viewers'];
        
        if (empty($channel)) {
            return '<div class="et_pb_module et_pb_alert et_pb_alert_error">' . 
                   __('Bitte gib einen Twitch-Kanal an.', 'wp-twitch-stream') . 
                   '</div>';
        }
        
        // Stream Shortcode
        $stream_atts = array(
            'channel' => $channel,
            'width' => $width,
            'height' => $height,
            'autoplay' => $autoplay === 'on' ? 'true' : 'false',
            'muted' => $muted === 'on' ? 'true' : 'false',
        );
        
        $output = wp_twitch_stream_shortcode($stream_atts);
        
        // Stream Info
        if ($show_info === 'on') {
            $info_atts = array(
                'channel' => $channel,
                'layout' => $info_layout,
                'show_avatar' => $show_avatar === 'on' ? 'true' : 'false',
                'show_thumbnail' => $show_thumbnail === 'on' ? 'true' : 'false',
                'show_game' => $show_game === 'on' ? 'true' : 'false',
                'show_viewers' => $show_viewers === 'on' ? 'true' : 'false',
                'show_title' => 'true',
            );
            
            $output .= wp_twitch_stream_info_shortcode($info_atts);
        }
        
        // Divi Wrapper Classes
        $output = sprintf(
            '<div class="et_pb_module et_pb_twitch_stream %1$s">%2$s</div>',
            $this->module_classname($render_slug),
            $output
        );
        
        return $output;
    }
    
    public function advanced_fields() {
        return array(
            'fonts' => false,
            'background' => false,
            'borders' => false,
            'box_shadow' => false,
            'button' => false,
            'filters' => false,
            'sizing' => false,
            'spacing' => false,
            'transform' => false,
            'animation' => false,
            'interactions' => false,
        );
    }
}
