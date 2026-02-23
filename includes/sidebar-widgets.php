<?php
/**
 * Sidebar Widgets für Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_VOD_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'spswifter_twitch_vod_widget',
            __('Twitch VOD', 'speedyswifter-stream-integrator-for-twitch'),
            array('description' => __('Zeigt Twitch Videos oder VODs an', 'speedyswifter-stream-integrator-for-twitch'))
        );
    }
    
    public function widget($args, $instance) {
        $channel = !empty($instance['channel']) ? $instance['channel'] : '';
        $video_id = !empty($instance['video_id']) ? $instance['video_id'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : '5';
        $type = !empty($instance['type']) ? $instance['type'] : 'archive';
        $width = !empty($instance['width']) ? $instance['width'] : '100%';
        $height = !empty($instance['height']) ? $instance['height'] : '300';
        $autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 'false';
        $show_info = !empty($instance['show_info']) ? $instance['show_info'] : 'true';
        $show_thumbnail = !empty($instance['show_thumbnail']) ? $instance['show_thumbnail'] : 'true';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'grid';
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        if (!empty($video_id)) {
            // Spezifisches Video
            $vod_atts = array(
                'video_id' => $video_id,
                'width' => $width,
                'height' => $height,
                'autoplay' => $autoplay,
                'show_info' => $show_info,
                'show_thumbnail' => $show_thumbnail,
                'layout' => 'single'
            );
            echo spswifter_twitch_vod_shortcode($vod_atts);
        } else {
            // Video-Liste
            $vod_atts = array(
                'channel' => $channel,
                'limit' => $limit,
                'type' => $type,
                'width' => $width,
                'height' => $height,
                'show_info' => $show_info,
                'show_thumbnail' => $show_thumbnail,
                'layout' => $layout
            );
            echo spswifter_twitch_vod_shortcode($vod_atts);
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $channel = !empty($instance['channel']) ? $instance['channel'] : '';
        $video_id = !empty($instance['video_id']) ? $instance['video_id'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : '5';
        $type = !empty($instance['type']) ? $instance['type'] : 'archive';
        $width = !empty($instance['width']) ? $instance['width'] : '100%';
        $height = !empty($instance['height']) ? $instance['height'] : '300';
        $autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 'false';
        $show_info = !empty($instance['show_info']) ? $instance['show_info'] : 'true';
        $show_thumbnail = !empty($instance['show_thumbnail']) ? $instance['show_thumbnail'] : 'true';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'grid';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Titel:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('channel'); ?>"><?php esc_html_e('Kanal:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo esc_attr($channel); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('video_id'); ?>"><?php esc_html_e('Video ID:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('video_id'); ?>" name="<?php echo $this->get_field_name('video_id'); ?>" type="text" value="<?php echo esc_attr($video_id); ?>">
            <small><?php esc_html_e('Optional: Zeigt ein spezifisches Video an', 'speedyswifter-stream-integrator-for-twitch'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php esc_html_e('Anzahl:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="1" max="20">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php esc_html_e('Typ:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                <option value="archive" <?php selected($type, 'archive'); ?>><?php esc_html_e('Archive', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="upload" <?php selected($type, 'upload'); ?>><?php esc_html_e('Uploads', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="highlight" <?php selected($type, 'highlight'); ?>><?php esc_html_e('Highlights', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php esc_html_e('Breite:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php esc_html_e('Höhe:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo esc_attr($height); ?>" min="200" max="1080">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php esc_html_e('Autoplay:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>">
                <option value="true" <?php selected($autoplay, 'true'); ?>><?php esc_html_e('Ja', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="false" <?php selected($autoplay, 'false'); ?>><?php esc_html_e('Nein', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_info'); ?>"><?php esc_html_e('Infos anzeigen:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_info'); ?>" name="<?php echo $this->get_field_name('show_info'); ?>">
                <option value="true" <?php selected($show_info, 'true'); ?>><?php esc_html_e('Ja', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="false" <?php selected($show_info, 'false'); ?>><?php esc_html_e('Nein', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php esc_html_e('Thumbnail:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>">
                <option value="true" <?php selected($show_thumbnail, 'true'); ?>><?php esc_html_e('Ja', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="false" <?php selected($show_thumbnail, 'false'); ?>><?php esc_html_e('Nein', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>"><?php esc_html_e('Layout:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
                <option value="grid" <?php selected($layout, 'grid'); ?>><?php esc_html_e('Grid', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="list" <?php selected($layout, 'list'); ?>><?php esc_html_e('Liste', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="single" <?php selected($layout, 'single'); ?>><?php esc_html_e('Einzelnes Video', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <?php
    }
    
    public function upgmdate($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['channel'] = (!empty($new_instance['channel'])) ? sanitize_text_field($new_instance['channel']) : '';
        $instance['video_id'] = (!empty($new_instance['video_id'])) ? sanitize_text_field($new_instance['video_id']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? sanitize_text_field($new_instance['limit']) : '';
        $instance['type'] = (!empty($new_instance['type'])) ? sanitize_text_field($new_instance['type']) : '';
        $instance['width'] = (!empty($new_instance['width'])) ? sanitize_text_field($new_instance['width']) : '';
        $instance['height'] = (!empty($new_instance['height'])) ? sanitize_text_field($new_instance['height']) : '';
        $instance['autoplay'] = (!empty($new_instance['autoplay'])) ? sanitize_text_field($new_instance['autoplay']) : '';
        $instance['show_info'] = (!empty($new_instance['show_info'])) ? sanitize_text_field($new_instance['show_info']) : '';
        $instance['show_thumbnail'] = (!empty($new_instance['show_thumbnail'])) ? sanitize_text_field($new_instance['show_thumbnail']) : '';
        $instance['layout'] = (!empty($new_instance['layout'])) ? sanitize_text_field($new_instance['layout']) : '';
        
        return $instance;
    }
}

class SPSWIFTER_Twitch_Clips_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'spswifter_twitch_clips_widget',
            __('Twitch Clips', 'speedyswifter-stream-integrator-for-twitch'),
            array('description' => __('Zeigt Twitch Clips an', 'speedyswifter-stream-integrator-for-twitch'))
        );
    }
    
    public function widget($args, $instance) {
        $channel = !empty($instance['channel']) ? $instance['channel'] : '';
        $clip_id = !empty($instance['clip_id']) ? $instance['clip_id'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : '5';
        $width = !empty($instance['width']) ? $instance['width'] : '100%';
        $height = !empty($instance['height']) ? $instance['height'] : '300';
        $autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 'false';
        $show_info = !empty($instance['show_info']) ? $instance['show_info'] : 'true';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'grid';
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        if (!empty($clip_id)) {
            // Spezifischer Clip
            $clip_atts = array(
                'clip_id' => $clip_id,
                'width' => $width,
                'height' => $height,
                'autoplay' => $autoplay,
                'show_info' => $show_info,
                'layout' => 'single'
            );
            echo spswifter_twitch_clips_shortcode($clip_atts);
        } else {
            // Clip-Liste
            $clip_atts = array(
                'channel' => $channel,
                'limit' => $limit,
                'width' => $width,
                'height' => $height,
                'autoplay' => $autoplay,
                'show_info' => $show_info,
                'layout' => $layout
            );
            echo spswifter_twitch_clips_shortcode($clip_atts);
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $channel = !empty($instance['channel']) ? $instance['channel'] : '';
        $clip_id = !empty($instance['clip_id']) ? $instance['clip_id'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : '5';
        $width = !empty($instance['width']) ? $instance['width'] : '100%';
        $height = !empty($instance['height']) ? $instance['height'] : '300';
        $autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 'false';
        $show_info = !empty($instance['show_info']) ? $instance['show_info'] : 'true';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'grid';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Titel:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('channel'); ?>"><?php esc_html_e('Kanal:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo esc_attr($channel); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('clip_id'); ?>"><?php esc_html_e('Clip ID:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('clip_id'); ?>" name="<?php echo $this->get_field_name('clip_id'); ?>" type="text" value="<?php echo esc_attr($clip_id); ?>">
            <small><?php esc_html_e('Optional: Zeigt einen spezifischen Clip an', 'speedyswifter-stream-integrator-for-twitch'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php esc_html_e('Anzahl:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="1" max="20">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php esc_html_e('Breite:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php esc_html_e('Höhe:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo esc_attr($height); ?>" min="200" max="1080">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php esc_html_e('Autoplay:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>">
                <option value="true" <?php selected($autoplay, 'true'); ?>><?php esc_html_e('Ja', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="false" <?php selected($autoplay, 'false'); ?>><?php esc_html_e('Nein', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_info'); ?>"><?php esc_html_e('Infos anzeigen:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_info'); ?>" name="<?php echo $this->get_field_name('show_info'); ?>">
                <option value="true" <?php selected($show_info, 'true'); ?>><?php esc_html_e('Ja', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="false" <?php selected($show_info, 'false'); ?>><?php esc_html_e('Nein', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>"><?php esc_html_e('Layout:', 'speedyswifter-stream-integrator-for-twitch'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
                <option value="grid" <?php selected($layout, 'grid'); ?>><?php esc_html_e('Grid', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
                <option value="list" <?php selected($layout, 'list'); ?>><?php esc_html_e('Liste', 'speedyswifter-stream-integrator-for-twitch'); ?></option>
            </select>
        </p>
        <?php
    }
    
    public function upgmdate($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['channel'] = (!empty($new_instance['channel'])) ? sanitize_text_field($new_instance['channel']) : '';
        $instance['clip_id'] = (!empty($new_instance['clip_id'])) ? sanitize_text_field($new_instance['clip_id']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? sanitize_text_field($new_instance['limit']) : '';
        $instance['width'] = (!empty($new_instance['width'])) ? sanitize_text_field($new_instance['width']) : '';
        $instance['height'] = (!empty($new_instance['height'])) ? sanitize_text_field($new_instance['height']) : '';
        $instance['autoplay'] = (!empty($new_instance['autoplay'])) ? sanitize_text_field($new_instance['autoplay']) : '';
        $instance['show_info'] = (!empty($new_instance['show_info'])) ? sanitize_text_field($new_instance['show_info']) : '';
        $instance['layout'] = (!empty($new_instance['layout'])) ? sanitize_text_field($new_instance['layout']) : '';
        
        return $instance;
    }
}

// Widgets registrieren
function spswifter_twitch_register_sidebar_widgets() {
    register_widget('SPSWIFTER_Twitch_VOD_Widget');
    register_widget('SPSWIFTER_Twitch_Clips_Widget');
}
add_action('widgets_init', 'spswifter_twitch_register_sidebar_widgets');
