<?php
/**
 * Elementor Twitch Stream Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Elementor_Twitch_Stream_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'twitch-stream';
    }

    public function get_title() {
        return __('Twitch Stream', 'speedyswifter-stream-integrator-for-twitch');
    }

    public function get_icon() {
        return 'fa fa-twitch';
    }

    public function get_categories() {
        return array('twitch-stream');
    }

    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Stream Einstellungen', 'speedyswifter-stream-integrator-for-twitch'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'channel',
            array(
                'label' => __('Twitch Kanal', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('z.B. shroud', 'speedyswifter-stream-integrator-for-twitch'),
                'description' => __('Gib den Twitch-Benutzernamen ein', 'speedyswifter-stream-integrator-for-twitch'),
            )
        );

        $this->add_control(
            'width',
            array(
                'label' => __('Breite', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '100%',
                'placeholder' => __('100% oder 800px', 'speedyswifter-stream-integrator-for-twitch'),
            )
        );

        $this->add_control(
            'height',
            array(
                'label' => __('Höhe', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 480,
                'min' => 200,
                'max' => 1080,
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label' => __('Autoplay', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
            )
        );

        $this->add_control(
            'muted',
            array(
                'label' => __('Stummgeschaltet', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
            )
        );

        $this->end_controls_section();

        // Stream Info Section
        $this->start_controls_section(
            'info_section',
            array(
                'label' => __('Stream-Informationen', 'speedyswifter-stream-integrator-for-twitch'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_info',
            array(
                'label' => __('Stream-Infos anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
            )
        );

        $this->add_control(
            'info_layout',
            array(
                'label' => __('Info Layout', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => array(
                    'horizontal' => __('Horizontal', 'speedyswifter-stream-integrator-for-twitch'),
                    'vertical' => __('Vertikal', 'speedyswifter-stream-integrator-for-twitch'),
                    'compact' => __('Kompakt', 'speedyswifter-stream-integrator-for-twitch'),
                ),
                'condition' => array(
                    'show_info' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_avatar',
            array(
                'label' => __('Avatar anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
                'condition' => array(
                    'show_info' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_thumbnail',
            array(
                'label' => __('Thumbnail anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
                'condition' => array(
                    'show_info' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_game',
            array(
                'label' => __('Spiel anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
                'condition' => array(
                    'show_info' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_viewers',
            array(
                'label' => __('Zuschauer anzeigen', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Ja', 'speedyswifter-stream-integrator-for-twitch'),
                'label_off' => __('Nein', 'speedyswifter-stream-integrator-for-twitch'),
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
                'label' => __('Styling', 'speedyswifter-stream-integrator-for-twitch'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'border',
                'label' => __('Rahmen', 'speedyswifter-stream-integrator-for-twitch'),
                'selector' => '{{WRAPPER}} .twitch-stream-container',
            )
        );

        $this->add_control(
            'border_radius',
            array(
                'label' => __('Eckradius', 'speedyswifter-stream-integrator-for-twitch'),
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
                    '{{WRAPPER}} .twitch-stream-container' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'box_shadow',
                'label' => __('Schatten', 'speedyswifter-stream-integrator-for-twitch'),
                'selector' => '{{WRAPPER}} .twitch-stream-container',
            )
        );

        $this->add_responsive_control(
            'margin',
            array(
                'label' => __('Abstand', 'speedyswifter-stream-integrator-for-twitch'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .twitch-stream-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['channel'])) {
            echo '<div class="elementor-alert elementor-alert-warning">' . 
                 __('Bitte gib einen Twitch-Kanal an.', 'speedyswifter-stream-integrator-for-twitch') . 
                 '</div>';
            return;
        }

        // Stream Shortcode
        $stream_atts = array(
            'channel' => $settings['channel'],
            'width' => $settings['width'],
            'height' => $settings['height'],
            'autoplay' => $settings['autoplay'] === 'yes' ? 'true' : 'false',
            'muted' => $settings['muted'] === 'yes' ? 'true' : 'false',
        );

        echo spswifter_twitch_stream_shortcode($stream_atts);

        // Stream Info
        if ($settings['show_info'] === 'yes') {
            $info_atts = array(
                'channel' => $settings['channel'],
                'layout' => $settings['info_layout'],
                'show_avatar' => $settings['show_avatar'] === 'yes' ? 'true' : 'false',
                'show_thumbnail' => $settings['show_thumbnail'] === 'yes' ? 'true' : 'false',
                'show_game' => $settings['show_game'] === 'yes' ? 'true' : 'false',
                'show_viewers' => $settings['show_viewers'] === 'yes' ? 'true' : 'false',
                'show_title' => 'true',
            );

            echo spswifter_twitch_stream_info_shortcode($info_atts);
        }
    }

    protected function _content_template() {
        ?>
        <#
        if (settings.channel) {
            var streamShortcode = '[spswifter_twitch_stream channel="' + settings.channel + '" width="' + settings.width + '" height="' + settings.height + '" autoplay="' + (settings.autoplay ? 'true' : 'false') + '" muted="' + (settings.muted ? 'true' : 'false') + '"]';
            
            print(streamShortcode);
            
            if (settings.show_info) {
                var infoShortcode = '[spswifter_twitch_stream_info channel="' + settings.channel + '" layout="' + settings.info_layout + '" show_avatar="' + (settings.show_avatar ? 'true' : 'false') + '" show_thumbnail="' + (settings.show_thumbnail ? 'true' : 'false') + '" show_game="' + (settings.show_game ? 'true' : 'false') + '" show_viewers="' + (settings.show_viewers ? 'true' : 'false') + '"]';
                print(infoShortcode);
            }
        } else {
            print('<div class="elementor-alert elementor-alert-warning">Bitte gib einen Twitch-Kanal an.</div>');
        }
        #>
        <?php
    }
}
?>
