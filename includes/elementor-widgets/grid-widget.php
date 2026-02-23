<?php
/**
 * Elementor Twitch Grid Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Elementor_Twitch_Grid_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'twitch-grid';
    }

    public function get_title() {
        return __('Twitch Stream Grid', 'speedyswifter-twitch');
    }

    public function get_icon() {
        return 'fa fa-th';
    }

    public function get_categories() {
        return array('twitch-stream');
    }

    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Grid Einstellungen', 'speedyswifter-twitch'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'channels',
            array(
                'label' => __('Twitch Kanäle', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('shroud, ninja, pokimane', 'speedyswifter-twitch'),
                'description' => __('Kommagetrennte Liste von Twitch-Kanälen', 'speedyswifter-twitch'),
            )
        );

        $this->add_control(
            'columns',
            array(
                'label' => __('Spalten', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 6,
            )
        );

        $this->add_control(
            'layout',
            array(
                'label' => __('Layout', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => array(
                    'grid' => __('Grid', 'speedyswifter-twitch'),
                    'list' => __('Liste', 'speedyswifter-twitch'),
                    'masonry' => __('Masonry', 'speedyswifter-twitch'),
                ),
            )
        );

        $this->add_control(
            'gap',
            array(
                'label' => __('Abstand', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '20px',
                'placeholder' => __('20px', 'speedyswifter-twitch'),
            )
        );

        $this->add_control(
            'responsive',
            array(
                'label' => __('Responsive', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-twitch'),
                'label_off' => __('Nein', 'speedyswifter-twitch'),
            )
        );

        $this->end_controls_section();

        // Display Section
        $this->start_controls_section(
            'display_section',
            array(
                'label' => __('Anzeige-Optionen', 'speedyswifter-twitch'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_player',
            array(
                'label' => __('Player anzeigen', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-twitch'),
                'label_off' => __('Nein', 'speedyswifter-twitch'),
            )
        );

        $this->add_control(
            'show_info',
            array(
                'label' => __('Informationen anzeigen', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-twitch'),
                'label_off' => __('Nein', 'speedyswifter-twitch'),
            )
        );

        $this->add_control(
            'player_height',
            array(
                'label' => __('Player Höhe', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 200,
                'min' => 100,
                'max' => 400,
                'condition' => array(
                    'show_player' => 'yes',
                ),
            )
        );

        $this->add_control(
            'info_layout',
            array(
                'label' => __('Info Layout', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'compact',
                'options' => array(
                    'horizontal' => __('Horizontal', 'speedyswifter-twitch'),
                    'vertical' => __('Vertikal', 'speedyswifter-twitch'),
                    'compact' => __('Kompakt', 'speedyswifter-twitch'),
                ),
                'condition' => array(
                    'show_info' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Grid Styling', 'speedyswifter-twitch'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'item_border',
                'label' => __('Item Rahmen', 'speedyswifter-twitch'),
                'selector' => '{{WRAPPER}} .twitch-grid-item',
            )
        );

        $this->add_control(
            'item_border_radius',
            array(
                'label' => __('Item Eckradius', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px', '%'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                    '%' => array(
                        'min' => 0,
                        'max' => 100,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 8,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .twitch-grid-item' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'item_box_shadow',
                'label' => __('Item Schatten', 'speedyswifter-twitch'),
                'selector' => '{{WRAPPER}} .twitch-grid-item',
            )
        );

        $this->add_responsive_control(
            'grid_margin',
            array(
                'label' => __('Grid Abstand', 'speedyswifter-twitch'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .twitch-streams-grid' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['channels'])) {
            echo '<div class="elementor-alert elementor-alert-warning">' . 
                 __('Bitte gib mindestens einen Twitch-Kanal an.', 'speedyswifter-twitch') . 
                 '</div>';
            return;
        }

        // Grid Shortcode
        $grid_atts = array(
            'channels' => $settings['channels'],
            'columns' => $settings['columns'],
            'layout' => $settings['layout'],
            'gap' => $settings['gap'],
            'responsive' => $settings['responsive'] === 'yes' ? 'true' : 'false',
            'show_player' => $settings['show_player'] === 'yes' ? 'true' : 'false',
            'show_info' => $settings['show_info'] === 'yes' ? 'true' : 'false',
        );

        echo spswifter_spswifter_twitch_streams_grid_shortcode($grid_atts);
    }

    protected function _content_template() {
        ?>
        <#
        if (settings.channels) {
            var gridShortcode = '[spswifter_twitch_streams_grid channels="' + settings.channels + '" columns="' + settings.columns + '" layout="' + settings.layout + '" gap="' + settings.gap + '" responsive="' + (settings.responsive ? 'true' : 'false') + '" show_player="' + (settings.show_player ? 'true' : 'false') + '" show_info="' + (settings.show_info ? 'true' : 'false') + '"]';
            print(gridShortcode);
        } else {
            print('<div class="elementor-alert elementor-alert-warning">Bitte gib mindestens einen Twitch-Kanal an.</div>');
        }
        #>
        <?php
    }
}
?>
