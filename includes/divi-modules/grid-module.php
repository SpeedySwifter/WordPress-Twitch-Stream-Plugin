<?php
/**
 * Divi Builder Twitch Grid Module
 */

if (!defined('ABSPATH')) {
    exit;
}

class Divi_Twitch_Grid_Module extends ET_Builder_Module {
    
    public function init() {
        $this->name = __('Twitch Stream Grid', 'speedyswifter-twitch');
        $this->slug = 'et_pb_spswifter_twitch_grid';
        $this->vb_support = 'on';
        $this->main_css_element = '%%order_class%%';
        
        $this->settings_modal_toggles = array(
            'general' => array(
                'toggles' => array(
                    'main_content' => __('Grid Einstellungen', 'speedyswifter-twitch'),
                    'display_options' => __('Anzeige-Optionen', 'speedyswifter-twitch'),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'layout' => __('Layout', 'speedyswifter-twitch'),
                    'animation' => __('Animation', 'speedyswifter-twitch'),
                ),
            ),
        );
    }
    
    public function get_fields() {
        return array(
            'channels' => array(
                'label' => __('Twitch Kanäle', 'speedyswifter-twitch'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'description' => __('Kommagetrennte Liste von Twitch-Kanälen', 'speedyswifter-twitch'),
                'toggle_slug' => 'main_content',
            ),
            'columns' => array(
                'label' => __('Spalten', 'speedyswifter-twitch'),
                'type' => 'number',
                'option_category' => 'basic_option',
                'default' => 3,
                'toggle_slug' => 'main_content',
            ),
            'layout' => array(
                'label' => __('Layout', 'speedyswifter-twitch'),
                'type' => 'select',
                'option_category' => 'basic_option',
                'default' => 'grid',
                'options' => array(
                    'grid' => __('Grid', 'speedyswifter-twitch'),
                    'list' => __('Liste', 'speedyswifter-twitch'),
                    'masonry' => __('Masonry', 'speedyswifter-twitch'),
                ),
                'toggle_slug' => 'main_content',
            ),
            'gap' => array(
                'label' => __('Abstand', 'speedyswifter-twitch'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'default' => '20px',
                'toggle_slug' => 'main_content',
            ),
            'responsive' => array(
                'label' => __('Responsive', 'speedyswifter-twitch'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'main_content',
            ),
            'show_player' => array(
                'label' => __('Player anzeigen', 'speedyswifter-twitch'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'display_options',
            ),
            'show_info' => array(
                'label' => __('Informationen anzeigen', 'speedyswifter-twitch'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'default' => 'on',
                'toggle_slug' => 'display_options',
            ),
            'player_height' => array(
                'label' => __('Player Höhe', 'speedyswifter-twitch'),
                'type' => 'number',
                'option_category' => 'basic_option',
                'default' => 200,
                'toggle_slug' => 'display_options',
                'show_if' => array(
                    'show_player' => 'on',
                ),
            ),
            'info_layout' => array(
                'label' => __('Info Layout', 'speedyswifter-twitch'),
                'type' => 'select',
                'option_category' => 'basic_option',
                'default' => 'compact',
                'options' => array(
                    'horizontal' => __('Horizontal', 'speedyswifter-twitch'),
                    'vertical' => __('Vertikal', 'speedyswifter-twitch'),
                    'compact' => __('Kompakt', 'speedyswifter-twitch'),
                ),
                'toggle_slug' => 'display_options',
                'show_if' => array(
                    'show_info' => 'on',
                ),
            ),
            'admin_label' => array(
                'label' => __('Admin Label', 'speedyswifter-twitch'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'toggle_slug' => 'main_content',
            ),
            'module_id' => array(
                'label' => __('CSS ID', 'speedyswifter-twitch'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'toggle_slug' => 'layout',
            ),
            'module_class' => array(
                'label' => __('CSS Class', 'speedyswifter-twitch'),
                'type' => 'text',
                'option_category' => 'basic_option',
                'toggle_slug' => 'layout',
            ),
        );
    }
    
    public function render($attrs, $content = null, $render_slug = '') {
        $channels = $this->props['channels'];
        $columns = $this->props['columns'];
        $layout = $this->props['layout'];
        $gap = $this->props['gap'];
        $responsive = $this->props['responsive'];
        $show_player = $this->props['show_player'];
        $show_info = $this->props['show_info'];
        $player_height = $this->props['player_height'];
        $info_layout = $this->props['info_layout'];
        
        if (empty($channels)) {
            return '<div class="et_pb_module et_pb_alert et_pb_alert_error">' . 
                   __('Bitte gib mindestens einen Twitch-Kanal an.', 'speedyswifter-twitch') . 
                   '</div>';
        }
        
        // Grid Shortcode
        $grid_atts = array(
            'channels' => $channels,
            'columns' => $columns,
            'layout' => $layout,
            'gap' => $gap,
            'responsive' => $responsive === 'on' ? 'true' : 'false',
            'show_player' => $show_player === 'on' ? 'true' : 'false',
            'show_info' => $show_info === 'on' ? 'true' : 'false',
        );
        
        $output = spswifter_spswifter_twitch_streams_grid_shortcode($grid_atts);
        
        // Divi Wrapper Classes
        $output = sprintf(
            '<div class="et_pb_module et_pb_spswifter_twitch_grid %1$s">%2$s</div>',
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
