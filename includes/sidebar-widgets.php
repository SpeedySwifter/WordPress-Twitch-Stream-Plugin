<?php
/**
 * Sidebar Widgets für Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_VOD_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'wp_twitch_vod_widget',
            __('Twitch VOD', 'wp-twitch-stream'),
            array('description' => __('Zeigt Twitch Videos oder VODs an', 'wp-twitch-stream'))
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
            echo wp_twitch_vod_shortcode($vod_atts);
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
            echo wp_twitch_vod_shortcode($vod_atts);
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
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('channel'); ?>"><?php _e('Kanal:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo esc_attr($channel); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('video_id'); ?>"><?php _e('Video ID:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('video_id'); ?>" name="<?php echo $this->get_field_name('video_id'); ?>" type="text" value="<?php echo esc_attr($video_id); ?>">
            <small><?php _e('Optional: Zeigt ein spezifisches Video an', 'wp-twitch-stream'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Anzahl:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="1" max="20">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Typ:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                <option value="archive" <?php selected($type, 'archive'); ?>><?php _e('Archive', 'wp-twitch-stream'); ?></option>
                <option value="upload" <?php selected($type, 'upload'); ?>><?php _e('Uploads', 'wp-twitch-stream'); ?></option>
                <option value="highlight" <?php selected($type, 'highlight'); ?>><?php _e('Highlights', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Breite:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Höhe:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo esc_attr($height); ?>" min="200" max="1080">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php _e('Autoplay:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>">
                <option value="true" <?php selected($autoplay, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($autoplay, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_info'); ?>"><?php _e('Infos anzeigen:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_info'); ?>" name="<?php echo $this->get_field_name('show_info'); ?>">
                <option value="true" <?php selected($show_info, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($show_info, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php _e('Thumbnail:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>">
                <option value="true" <?php selected($show_thumbnail, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($show_thumbnail, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>"><?php _e('Layout:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
                <option value="grid" <?php selected($layout, 'grid'); ?>><?php _e('Grid', 'wp-twitch-stream'); ?></option>
                <option value="list" <?php selected($layout, 'list'); ?>><?php _e('Liste', 'wp-twitch-stream'); ?></option>
                <option value="single" <?php selected($layout, 'single'); ?>><?php _e('Einzelnes Video', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
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

class WP_Twitch_Clips_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'wp_twitch_clips_widget',
            __('Twitch Clips', 'wp-twitch-stream'),
            array('description' => __('Zeigt Twitch Clips an', 'wp-twitch-stream'))
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
            echo wp_twitch_clips_shortcode($clip_atts);
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
            echo wp_twitch_clips_shortcode($clip_atts);
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
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('channel'); ?>"><?php _e('Kanal:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo esc_attr($channel); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('clip_id'); ?>"><?php _e('Clip ID:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('clip_id'); ?>" name="<?php echo $this->get_field_name('clip_id'); ?>" type="text" value="<?php echo esc_attr($clip_id); ?>">
            <small><?php _e('Optional: Zeigt einen spezifischen Clip an', 'wp-twitch-stream'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Anzahl:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="1" max="20">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Breite:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Höhe:', 'wp-twitch-stream'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo esc_attr($height); ?>" min="200" max="1080">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php _e('Autoplay:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>">
                <option value="true" <?php selected($autoplay, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($autoplay, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_info'); ?>"><?php _e('Infos anzeigen:', 'wp-twitch-stream'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_info'); ?>" name="<?php echo $this->get_field_name('show_info'); ?>">
                <option value="true" <?php selected($show_info, 'true'); ?>><?php _e('Ja', 'wp-twitch-stream'); ?></option>
                <option value="false" <?php selected($show_info, 'false'); ?>><?php _e('Nein', 'wp-twitch-stream'); ?></option>
            </select>
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
function wp_twitch_register_sidebar_widgets() {
    register_widget('WP_Twitch_VOD_Widget');
    register_widget('WP_Twitch_Clips_Widget');
}
add_action('widgets_init', 'wp_twitch_register_sidebar_widgets');
