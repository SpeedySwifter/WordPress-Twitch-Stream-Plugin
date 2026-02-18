<?php
/**
 * Advanced Shortcode Builder for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Advanced_Shortcode_Builder {
    
    private $shortcode_definitions;
    private $builder_settings;
    private $supported_shortcodes = array();
    
    public function __construct() {
        $this->shortcode_definitions = $this->get_shortcode_definitions();
        $this->builder_settings = $this->get_builder_settings();
        
        add_action('init', array($this, 'init_shortcode_builder'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shortcode_builder_scripts'));
        add_action('wp_ajax_twitch_shortcode_builder', array($this, 'handle_shortcode_builder_ajax'));
        add_action('wp_ajax_nopriv_twitch_shortcode_builder', array($this, 'handle_shortcode_builder_ajax'));
        add_action('admin_menu', array($this, 'add_shortcode_builder_menu'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_builder_shortcodes'));
    }
    
    /**
     * Initialize shortcode builder
     */
    public function init_shortcode_builder() {
        $this->load_supported_shortcodes();
        $this->add_shortcode_builder_filters();
    }
    
    /**
     * Load supported shortcodes
     */
    private function load_supported_shortcodes() {
        $this->supported_shortcodes = array(
            'twitch_stream' => array(
                'name' => 'Twitch Stream',
                'description' => 'Display a Twitch live stream',
                'category' => 'stream',
                'icon' => '🎥',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'description' => 'The Twitch channel username',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'width' => array(
                        'type' => 'number',
                        'label' => 'Width',
                        'description' => 'Player width in pixels',
                        'default' => 560,
                        'min' => 320,
                        'max' => 1920
                    ),
                    'height' => array(
                        'type' => 'number',
                        'label' => 'Height',
                        'description' => 'Player height in pixels',
                        'default' => 315,
                        'min' => 180,
                        'max' => 1080
                    ),
                    'autoplay' => array(
                        'type' => 'checkbox',
                        'label' => 'Autoplay',
                        'description' => 'Automatically start playing the stream'
                    ),
                    'muted' => array(
                        'type' => 'checkbox',
                        'label' => 'Muted',
                        'description' => 'Start with audio muted'
                    ),
                    'parent' => array(
                        'type' => 'text',
                        'label' => 'Parent Domain',
                        'description' => 'Domain name for embedding (required by Twitch)',
                        'placeholder' => 'yourdomain.com'
                    ),
                    'theme' => array(
                        'type' => 'select',
                        'label' => 'Theme',
                        'options' => array(
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ),
                        'default' => 'light'
                    ),
                    'layout' => array(
                        'type' => 'select',
                        'label' => 'Layout',
                        'options' => array(
                            'video-with-chat' => 'Video with Chat',
                            'video' => 'Video Only',
                            'chat' => 'Chat Only'
                        ),
                        'default' => 'video-with-chat'
                    )
                )
            ),
            'twitch_chat' => array(
                'name' => 'Twitch Chat',
                'description' => 'Display Twitch chat separately',
                'category' => 'chat',
                'icon' => '💬',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'description' => 'The Twitch channel username',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'width' => array(
                        'type' => 'number',
                        'label' => 'Width',
                        'description' => 'Chat width in pixels',
                        'default' => 350,
                        'min' => 300,
                        'max' => 800
                    ),
                    'height' => array(
                        'type' => 'number',
                        'label' => 'Height',
                        'description' => 'Chat height in pixels',
                        'default' => 500,
                        'min' => 400,
                        'max' => 1000
                    ),
                    'parent' => array(
                        'type' => 'text',
                        'label' => 'Parent Domain',
                        'description' => 'Domain name for embedding',
                        'placeholder' => 'yourdomain.com'
                    ),
                    'theme' => array(
                        'type' => 'select',
                        'label' => 'Theme',
                        'options' => array(
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ),
                        'default' => 'dark'
                    )
                )
            ),
            'twitch_follow_button' => array(
                'name' => 'Follow Button',
                'description' => 'Twitch follow button',
                'category' => 'social',
                'icon' => '👤',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'description' => 'The Twitch channel username',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'size' => array(
                        'type' => 'select',
                        'label' => 'Button Size',
                        'options' => array(
                            'small' => 'Small',
                            'large' => 'Large'
                        ),
                        'default' => 'large'
                    ),
                    'theme' => array(
                        'type' => 'select',
                        'label' => 'Theme',
                        'options' => array(
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ),
                        'default' => 'dark'
                    )
                )
            ),
            'twitch_subscribe_button' => array(
                'name' => 'Subscribe Button',
                'description' => 'Twitch subscribe button',
                'category' => 'social',
                'icon' => '⭐',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'description' => 'The Twitch channel username',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'theme' => array(
                        'type' => 'select',
                        'label' => 'Theme',
                        'options' => array(
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ),
                        'default' => 'dark'
                    )
                )
            ),
            'twitch_clips' => array(
                'name' => 'Twitch Clips',
                'description' => 'Display channel clips',
                'category' => 'content',
                'icon' => '🎬',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'description' => 'The Twitch channel username',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'limit' => array(
                        'type' => 'number',
                        'label' => 'Number of Clips',
                        'description' => 'How many clips to show',
                        'default' => 10,
                        'min' => 1,
                        'max' => 100
                    ),
                    'layout' => array(
                        'type' => 'select',
                        'label' => 'Layout',
                        'options' => array(
                            'grid' => 'Grid',
                            'list' => 'List',
                            'carousel' => 'Carousel'
                        ),
                        'default' => 'grid'
                    ),
                    'sort' => array(
                        'type' => 'select',
                        'label' => 'Sort By',
                        'options' => array(
                            'views' => 'Views',
                            'created_at' => 'Date Created'
                        ),
                        'default' => 'views'
                    )
                )
            ),
            'twitch_vod' => array(
                'name' => 'Twitch VOD',
                'description' => 'Display past broadcasts',
                'category' => 'content',
                'icon' => '📼',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'description' => 'The Twitch channel username',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'limit' => array(
                        'type' => 'number',
                        'label' => 'Number of VODs',
                        'description' => 'How many VODs to show',
                        'default' => 5,
                        'min' => 1,
                        'max' => 100
                    ),
                    'type' => array(
                        'type' => 'select',
                        'label' => 'VOD Type',
                        'options' => array(
                            'archive' => 'Past Broadcasts',
                            'highlight' => 'Highlights',
                            'upload' => 'Uploads'
                        ),
                        'default' => 'archive'
                    ),
                    'layout' => array(
                        'type' => 'select',
                        'label' => 'Layout',
                        'options' => array(
                            'grid' => 'Grid',
                            'list' => 'List'
                        ),
                        'default' => 'grid'
                    )
                )
            ),
            'twitch_donations' => array(
                'name' => 'Donation Integration',
                'description' => 'Donation buttons and forms',
                'category' => 'monetization',
                'icon' => '💰',
                'parameters' => array(
                    'type' => array(
                        'type' => 'select',
                        'label' => 'Donation Type',
                        'options' => array(
                            'buymeacoffee' => 'Buy Me a Coffee',
                            'paypal' => 'PayPal',
                            'both' => 'Both'
                        ),
                        'default' => 'both'
                    ),
                    'buymeacoffee_username' => array(
                        'type' => 'text',
                        'label' => 'Buy Me a Coffee Username',
                        'description' => 'Your Buy Me a Coffee username',
                        'placeholder' => 'e.g., yourusername'
                    ),
                    'paypal_email' => array(
                        'type' => 'email',
                        'label' => 'PayPal Email',
                        'description' => 'Your PayPal email address',
                        'placeholder' => 'your@email.com'
                    ),
                    'style' => array(
                        'type' => 'select',
                        'label' => 'Button Style',
                        'options' => array(
                            'default' => 'Default',
                            'minimal' => 'Minimal',
                            'compact' => 'Compact'
                        ),
                        'default' => 'default'
                    ),
                    'text' => array(
                        'type' => 'text',
                        'label' => 'Button Text',
                        'description' => 'Custom button text',
                        'default' => 'Support the Stream'
                    )
                )
            ),
            'twitch_chat_integration' => array(
                'name' => 'Advanced Chat',
                'description' => 'Advanced chat integration',
                'category' => 'chat',
                'icon' => '💬',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'height' => array(
                        'type' => 'number',
                        'label' => 'Chat Height',
                        'default' => 400,
                        'min' => 200,
                        'max' => 800
                    ),
                    'theme' => array(
                        'type' => 'select',
                        'label' => 'Theme',
                        'options' => array(
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ),
                        'default' => 'dark'
                    ),
                    'show_emojis' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Emojis',
                        'default' => true
                    ),
                    'show_badges' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Badges',
                        'default' => true
                    ),
                    'allow_commands' => array(
                        'type' => 'checkbox',
                        'label' => 'Allow Commands',
                        'default' => true
                    )
                )
            ),
            'twitch_recording_download' => array(
                'name' => 'Recording Download',
                'description' => 'Stream recording downloads',
                'category' => 'content',
                'icon' => '📥',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'limit' => array(
                        'type' => 'number',
                        'label' => 'Number of Recordings',
                        'default' => 10,
                        'min' => 1,
                        'max' => 50
                    ),
                    'download_type' => array(
                        'type' => 'select',
                        'label' => 'Download Type',
                        'options' => array(
                            'direct' => 'Direct Download',
                            'external' => 'External Link',
                            'both' => 'Both'
                        ),
                        'default' => 'both'
                    ),
                    'show_thumbnails' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Thumbnails',
                        'default' => true
                    ),
                    'sort_by' => array(
                        'type' => 'select',
                        'label' => 'Sort By',
                        'options' => array(
                            'date' => 'Date',
                            'views' => 'Views',
                            'duration' => 'Duration'
                        ),
                        'default' => 'date'
                    )
                )
            ),
            'twitch_analytics' => array(
                'name' => 'Analytics Dashboard',
                'description' => 'Stream analytics display',
                'category' => 'analytics',
                'icon' => '📊',
                'parameters' => array(
                    'channel' => array(
                        'type' => 'text',
                        'label' => 'Twitch Channel',
                        'required' => true,
                        'placeholder' => 'e.g., shroud'
                    ),
                    'time_range' => array(
                        'type' => 'select',
                        'label' => 'Time Range',
                        'options' => array(
                            '24h' => 'Last 24 Hours',
                            '7d' => 'Last 7 Days',
                            '30d' => 'Last 30 Days',
                            '90d' => 'Last 90 Days'
                        ),
                        'default' => '7d'
                    ),
                    'metrics' => array(
                        'type' => 'multiselect',
                        'label' => 'Metrics to Show',
                        'options' => array(
                            'viewers' => 'Viewer Count',
                            'followers' => 'Followers',
                            'views' => 'Total Views',
                            'subscribers' => 'Subscribers'
                        ),
                        'default' => array('viewers', 'followers', 'views')
                    ),
                    'chart_type' => array(
                        'type' => 'select',
                        'label' => 'Chart Type',
                        'options' => array(
                            'line' => 'Line Chart',
                            'bar' => 'Bar Chart',
                            'area' => 'Area Chart'
                        ),
                        'default' => 'line'
                    ),
                    'show_live_stats' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Live Stats',
                        'default' => true
                    )
                )
            ),
            'twitch_membership_content' => array(
                'name' => 'Membership Content',
                'description' => 'Membership-restricted content',
                'category' => 'membership',
                'icon' => '🔒',
                'parameters' => array(
                    'level' => array(
                        'type' => 'select',
                        'label' => 'Required Level',
                        'options' => array(
                            'free' => 'Free',
                            'basic' => 'Basic',
                            'premium' => 'Premium',
                            'vip' => 'VIP'
                        ),
                        'default' => 'basic'
                    ),
                    'message' => array(
                        'type' => 'textarea',
                        'label' => 'Restriction Message',
                        'description' => 'Message shown to users without access',
                        'default' => 'This content requires a membership to view.'
                    ),
                    'upgrade_url' => array(
                        'type' => 'url',
                        'label' => 'Upgrade URL',
                        'description' => 'URL for membership upgrade',
                        'placeholder' => 'https://yoursite.com/upgrade'
                    ),
                    'show_upgrade_button' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Upgrade Button',
                        'default' => true
                    )
                )
            ),
            'twitch_shortcode_builder' => array(
                'name' => 'Shortcode Builder',
                'description' => 'Interactive shortcode builder',
                'category' => 'builder',
                'icon' => '🔧',
                'parameters' => array(
                    'theme' => array(
                        'type' => 'select',
                        'label' => 'Builder Theme',
                        'options' => array(
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ),
                        'default' => 'light'
                    ),
                    'show_preview' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Live Preview',
                        'default' => true
                    ),
                    'compact_mode' => array(
                        'type' => 'checkbox',
                        'label' => 'Compact Mode',
                        'default' => false
                    ),
                    'allowed_shortcodes' => array(
                        'type' => 'multiselect',
                        'label' => 'Allowed Shortcodes',
                        'options' => array(
                            'twitch_stream' => 'Stream',
                            'twitch_chat' => 'Chat',
                            'twitch_clips' => 'Clips',
                            'twitch_vod' => 'VOD',
                            'twitch_donations' => 'Donations',
                            'twitch_analytics' => 'Analytics',
                            'twitch_membership_content' => 'Membership Content'
                        ),
                        'default' => array('twitch_stream', 'twitch_chat', 'twitch_clips')
                    )
                )
            )
        );
    }
    
    /**
     * Add shortcode builder filters
     */
    private function add_shortcode_builder_filters() {
        add_filter('twitch_shortcode_builder_categories', array($this, 'get_shortcode_categories'));
        add_filter('twitch_shortcode_builder_templates', array($this, 'get_shortcode_templates'));
        add_filter('twitch_shortcode_builder_presets', array($this, 'get_shortcode_presets'));
    }
    
    /**
     * Register builder shortcodes
     */
    public function register_builder_shortcodes() {
        add_shortcode('twitch_shortcode_builder', array($this, 'render_shortcode_builder_shortcode'));
        add_shortcode('twitch_shortcode_generator', array($this, 'render_shortcode_generator_shortcode'));
        add_shortcode('twitch_shortcode_presets', array($this, 'render_shortcode_presets_shortcode'));
    }
    
    /**
     * Render shortcode builder shortcode
     */
    public function render_shortcode_builder_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'theme' => 'light',
            'show_preview' => 'true',
            'compact_mode' => 'false',
            'allowed_shortcodes' => '',
            'default_category' => 'stream',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-shortcode-builder" 
             data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-show-preview="<?php echo esc_attr($atts['show_preview']); ?>"
             data-compact-mode="<?php echo esc_attr($atts['compact_mode']); ?>"
             data-allowed-shortcodes="<?php echo esc_attr($atts['allowed_shortcodes']); ?>"
             data-default-category="<?php echo esc_attr($atts['default_category']); ?>">
            
            <div class="twitch-builder-header">
                <h3 class="twitch-builder-title">
                    <span class="twitch-builder-icon">🔧</span>
                    Twitch Shortcode Builder
                </h3>
                <div class="twitch-builder-controls">
                    <button class="twitch-builder-reset">
                        <span class="twitch-btn-icon">🔄</span>
                        Reset
                    </button>
                    <button class="twitch-builder-copy">
                        <span class="twitch-btn-icon">📋</span>
                        Copy Code
                    </button>
                </div>
            </div>
            
            <div class="twitch-builder-content">
                <div class="twitch-builder-sidebar">
                    <div class="twitch-builder-categories">
                        <h4>Categories</h4>
                        <div class="twitch-category-tabs">
                            <?php foreach ($this->get_shortcode_categories() as $category_key => $category): ?>
                                <button class="twitch-category-tab" data-category="<?php echo esc_attr($category_key); ?>">
                                    <span class="twitch-category-icon"><?php echo esc_html($category['icon']); ?></span>
                                    <span class="twitch-category-name"><?php echo esc_html($category['name']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="twitch-builder-shortcodes">
                        <h4>Shortcodes</h4>
                        <div class="twitch-shortcode-list">
                            <?php foreach ($this->supported_shortcodes as $shortcode_key => $shortcode): ?>
                                <div class="twitch-shortcode-item" 
                                     data-shortcode="<?php echo esc_attr($shortcode_key); ?>"
                                     data-category="<?php echo esc_attr($shortcode['category']); ?>">
                                    <span class="twitch-shortcode-icon"><?php echo esc_html($shortcode['icon']); ?></span>
                                    <div class="twitch-shortcode-info">
                                        <span class="twitch-shortcode-name"><?php echo esc_html($shortcode['name']); ?></span>
                                        <span class="twitch-shortcode-desc"><?php echo esc_html($shortcode['description']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="twitch-builder-main">
                    <div class="twitch-builder-form">
                        <div class="twitch-form-header">
                            <h4>Select a shortcode to configure</h4>
                            <p>Choose from the sidebar or use presets below</p>
                        </div>
                        
                        <div class="twitch-presets-section">
                            <h5>Quick Presets</h5>
                            <div class="twitch-presets-grid">
                                <?php foreach ($this->get_shortcode_presets() as $preset_key => $preset): ?>
                                    <button class="twitch-preset-btn" data-preset="<?php echo esc_attr($preset_key); ?>">
                                        <span class="twitch-preset-icon"><?php echo esc_html($preset['icon']); ?></span>
                                        <span class="twitch-preset-name"><?php echo esc_html($preset['name']); ?></span>
                                        <span class="twitch-preset-desc"><?php echo esc_html($preset['description']); ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="twitch-builder-preview">
                        <div class="twitch-preview-header">
                            <h4>Live Preview</h4>
                            <div class="twitch-preview-controls">
                                <button class="twitch-preview-refresh">
                                    <span class="twitch-btn-icon">🔄</span>
                                    Refresh
                                </button>
                                <label class="twitch-preview-toggle">
                                    <input type="checkbox" checked>
                                    <span>Auto-refresh</span>
                                </label>
                            </div>
                        </div>
                        <div class="twitch-preview-content">
                            <div class="twitch-preview-placeholder">
                                <span class="twitch-preview-icon">👀</span>
                                <p>Select a shortcode to see the preview</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="twitch-builder-output">
                    <div class="twitch-output-header">
                        <h4>Generated Shortcode</h4>
                        <div class="twitch-output-actions">
                            <button class="twitch-output-copy">
                                <span class="twitch-btn-icon">📋</span>
                                Copy
                            </button>
                            <button class="twitch-output-test">
                                <span class="twitch-btn-icon">🧪</span>
                                Test
                            </button>
                        </div>
                    </div>
                    <div class="twitch-output-content">
                        <pre class="twitch-shortcode-output"><code>// Select a shortcode to generate code</code></pre>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render shortcode generator shortcode
     */
    public function render_shortcode_generator_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'shortcode' => 'twitch_stream',
            'show_form' => 'true',
            'show_output' => 'true',
            'theme' => 'light',
        ), $atts);
        
        if (!isset($this->supported_shortcodes[$atts['shortcode']])) {
            return '<p>Shortcode not found: ' . esc_html($atts['shortcode']) . '</p>';
        }
        
        $shortcode_data = $this->supported_shortcodes[$atts['shortcode']];
        
        ob_start();
        ?>
        <div class="twitch-shortcode-generator" 
             data-shortcode="<?php echo esc_attr($atts['shortcode']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>">
            
            <div class="twitch-generator-header">
                <h4>
                    <span class="twitch-generator-icon"><?php echo esc_html($shortcode_data['icon']); ?></span>
                    <?php echo esc_html($shortcode_data['name']); ?> Generator
                </h4>
                <p><?php echo esc_html($shortcode_data['description']); ?></p>
            </div>
            
            <?php if ($atts['show_form'] === 'true'): ?>
                <div class="twitch-generator-form">
                    <?php foreach ($shortcode_data['parameters'] as $param_key => $param): ?>
                        <div class="twitch-form-field twitch-field-<?php echo esc_attr($param['type']); ?>">
                            <label for="twitch-param-<?php echo esc_attr($param_key); ?>">
                                <?php echo esc_html($param['label']); ?>
                                <?php if (isset($param['required']) && $param['required']): ?>
                                    <span class="twitch-required">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if ($param['type'] === 'text' || $param['type'] === 'email' || $param['type'] === 'url'): ?>
                                <input type="<?php echo esc_attr($param['type']); ?>" 
                                       id="twitch-param-<?php echo esc_attr($param_key); ?>"
                                       data-param="<?php echo esc_attr($param_key); ?>"
                                       placeholder="<?php echo esc_attr($param['placeholder'] ?? ''); ?>"
                                       value="<?php echo esc_attr($param['default'] ?? ''); ?>"
                                       <?php if (isset($param['required']) && $param['required']): ?>required<?php endif; ?>>
                            
                            <?php elseif ($param['type'] === 'number'): ?>
                                <input type="number" 
                                       id="twitch-param-<?php echo esc_attr($param_key); ?>"
                                       data-param="<?php echo esc_attr($param_key); ?>"
                                       min="<?php echo esc_attr($param['min'] ?? ''); ?>"
                                       max="<?php echo esc_attr($param['max'] ?? ''); ?>"
                                       value="<?php echo esc_attr($param['default'] ?? ''); ?>"
                                       <?php if (isset($param['required']) && $param['required']): ?>required<?php endif; ?>>
                            
                            <?php elseif ($param['type'] === 'textarea'): ?>
                                <textarea id="twitch-param-<?php echo esc_attr($param_key); ?>"
                                          data-param="<?php echo esc_attr($param_key); ?>"
                                          placeholder="<?php echo esc_attr($param['placeholder'] ?? ''); ?>"
                                          rows="3"
                                          <?php if (isset($param['required']) && $param['required']): ?>required<?php endif; ?>><?php echo esc_textarea($param['default'] ?? ''); ?></textarea>
                            
                            <?php elseif ($param['type'] === 'select'): ?>
                                <select id="twitch-param-<?php echo esc_attr($param_key); ?>" 
                                        data-param="<?php echo esc_attr($param_key); ?>"
                                        <?php if (isset($param['required']) && $param['required']): ?>required<?php endif; ?>>
                                    <?php foreach ($param['options'] as $option_key => $option_label): ?>
                                        <option value="<?php echo esc_attr($option_key); ?>" 
                                                <?php if (isset($param['default']) && $param['default'] === $option_key): ?>selected<?php endif; ?>>
                                            <?php echo esc_html($option_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            
                            <?php elseif ($param['type'] === 'multiselect'): ?>
                                <select id="twitch-param-<?php echo esc_attr($param_key); ?>" 
                                        data-param="<?php echo esc_attr($param_key); ?>"
                                        multiple
                                        <?php if (isset($param['required']) && $param['required']): ?>required<?php endif; ?>>
                                    <?php foreach ($param['options'] as $option_key => $option_label): ?>
                                        <option value="<?php echo esc_attr($option_key); ?>" 
                                                <?php if (isset($param['default']) && in_array($option_key, $param['default'])): ?>selected<?php endif; ?>>
                                            <?php echo esc_html($option_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            
                            <?php elseif ($param['type'] === 'checkbox'): ?>
                                <label class="twitch-checkbox-label">
                                    <input type="checkbox" 
                                           id="twitch-param-<?php echo esc_attr($param_key); ?>"
                                           data-param="<?php echo esc_attr($param_key); ?>"
                                           <?php if (isset($param['default']) && $param['default']): ?>checked<?php endif; ?>>
                                    <span class="twitch-checkbox-text"><?php echo esc_html($param['label']); ?></span>
                                </label>
                            <?php endif; ?>
                            
                            <?php if (isset($param['description'])): ?>
                                <p class="twitch-field-description"><?php echo esc_html($param['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="twitch-generator-actions">
                        <button class="twitch-generate-btn">
                            <span class="twitch-btn-icon">⚡</span>
                            Generate Shortcode
                        </button>
                        <button class="twitch-preview-btn">
                            <span class="twitch-btn-icon">👁️</span>
                            Preview
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_output'] === 'true'): ?>
                <div class="twitch-generator-output">
                    <div class="twitch-output-header">
                        <h5>Generated Shortcode</h5>
                        <button class="twitch-copy-shortcode">
                            <span class="twitch-btn-icon">📋</span>
                            Copy
                        </button>
                    </div>
                    <pre class="twitch-shortcode-result"><code>// Configure parameters above to generate shortcode</code></pre>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render shortcode presets shortcode
     */
    public function render_shortcode_presets_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => 0,
            'theme' => 'light',
        ), $atts);
        
        $presets = $this->get_shortcode_presets();
        
        if (!empty($atts['category'])) {
            $presets = array_filter($presets, function($preset) use ($atts) {
                return $preset['category'] === $atts['category'];
            });
        }
        
        if ($atts['limit'] > 0) {
            $presets = array_slice($presets, 0, $atts['limit']);
        }
        
        ob_start();
        ?>
        <div class="twitch-shortcode-presets" data-theme="<?php echo esc_attr($atts['theme']); ?>">
            <div class="twitch-presets-header">
                <h4>Shortcode Presets</h4>
                <p>Quick-start templates for common use cases</p>
            </div>
            
            <div class="twitch-presets-grid">
                <?php foreach ($presets as $preset_key => $preset): ?>
                    <div class="twitch-preset-card">
                        <div class="twitch-preset-header">
                            <span class="twitch-preset-icon"><?php echo esc_html($preset['icon']); ?></span>
                            <h5><?php echo esc_html($preset['name']); ?></h5>
                        </div>
                        <p class="twitch-preset-description"><?php echo esc_html($preset['description']); ?></p>
                        <div class="twitch-preset-code">
                            <pre><code><?php echo esc_html($preset['shortcode']); ?></code></pre>
                        </div>
                        <div class="twitch-preset-actions">
                            <button class="twitch-use-preset" data-preset="<?php echo esc_attr($preset_key); ?>">
                                <span class="twitch-btn-icon">✨</span>
                                Use Preset
                            </button>
                            <button class="twitch-copy-preset" data-code="<?php echo esc_attr($preset['shortcode']); ?>">
                                <span class="twitch-btn-icon">📋</span>
                                Copy Code
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get shortcode categories
     */
    public function get_shortcode_categories() {
        return array(
            'stream' => array(
                'name' => 'Stream',
                'icon' => '🎥',
                'description' => 'Live stream and video content'
            ),
            'chat' => array(
                'name' => 'Chat',
                'icon' => '💬',
                'description' => 'Chat and interaction features'
            ),
            'content' => array(
                'name' => 'Content',
                'icon' => '🎬',
                'description' => 'Clips, VODs, and recordings'
            ),
            'social' => array(
                'name' => 'Social',
                'icon' => '👥',
                'description' => 'Follow, subscribe, and social features'
            ),
            'monetization' => array(
                'name' => 'Monetization',
                'icon' => '💰',
                'description' => 'Donations and monetization tools'
            ),
            'analytics' => array(
                'name' => 'Analytics',
                'icon' => '📊',
                'description' => 'Statistics and analytics'
            ),
            'membership' => array(
                'name' => 'Membership',
                'icon' => '🔒',
                'description' => 'Membership and access control'
            ),
            'builder' => array(
                'name' => 'Builder',
                'icon' => '🔧',
                'description' => 'Shortcode building tools'
            )
        );
    }
    
    /**
     * Get shortcode presets
     */
    public function get_shortcode_presets() {
        return array(
            'basic_stream' => array(
                'name' => 'Basic Stream',
                'description' => 'Simple live stream embed',
                'icon' => '🎥',
                'category' => 'stream',
                'shortcode' => '[twitch_stream channel="yourchannel" width="560" height="315"]'
            ),
            'stream_with_chat' => array(
                'name' => 'Stream + Chat',
                'description' => 'Stream with integrated chat',
                'icon' => '📺',
                'category' => 'stream',
                'shortcode' => '[twitch_stream channel="yourchannel" layout="video-with-chat" theme="dark"]'
            ),
            'chat_only' => array(
                'name' => 'Chat Only',
                'description' => 'Standalone chat embed',
                'icon' => '💬',
                'category' => 'chat',
                'shortcode' => '[twitch_chat channel="yourchannel" width="350" height="500" theme="dark"]'
            ),
            'follow_button' => array(
                'name' => 'Follow Button',
                'description' => 'Twitch follow button',
                'icon' => '👤',
                'category' => 'social',
                'shortcode' => '[twitch_follow_button channel="yourchannel" theme="dark" size="large"]'
            ),
            'subscribe_button' => array(
                'name' => 'Subscribe Button',
                'description' => 'Twitch subscribe button',
                'icon' => '⭐',
                'category' => 'social',
                'shortcode' => '[twitch_subscribe_button channel="yourchannel" theme="dark"]'
            ),
            'channel_clips' => array(
                'name' => 'Channel Clips',
                'description' => 'Display channel clips',
                'icon' => '🎬',
                'category' => 'content',
                'shortcode' => '[twitch_clips channel="yourchannel" limit="10" layout="grid" sort="views"]'
            ),
            'past_broadcasts' => array(
                'name' => 'Past Broadcasts',
                'description' => 'Show past VODs',
                'icon' => '📼',
                'category' => 'content',
                'shortcode' => '[twitch_vod channel="yourchannel" limit="5" type="archive" layout="grid"]'
            ),
            'donation_buttons' => array(
                'name' => 'Donation Buttons',
                'description' => 'Buy Me a Coffee and PayPal',
                'icon' => '💰',
                'category' => 'monetization',
                'shortcode' => '[twitch_donations type="both" buymeacoffee_username="yourusername" paypal_email="your@email.com"]'
            ),
            'advanced_chat' => array(
                'name' => 'Advanced Chat',
                'description' => 'Full-featured chat integration',
                'icon' => '💬',
                'category' => 'chat',
                'shortcode' => '[twitch_chat_integration channel="yourchannel" theme="dark" show_emojis="true" show_badges="true" allow_commands="true"]'
            ),
            'recording_downloads' => array(
                'name' => 'Recording Downloads',
                'description' => 'Stream recording downloads',
                'icon' => '📥',
                'category' => 'content',
                'shortcode' => '[twitch_recording_download channel="yourchannel" limit="10" download_type="both" show_thumbnails="true"]'
            ),
            'analytics_dashboard' => array(
                'name' => 'Analytics Dashboard',
                'description' => 'Stream analytics display',
                'icon' => '📊',
                'category' => 'analytics',
                'shortcode' => '[twitch_analytics channel="yourchannel" time_range="7d" chart_type="line" show_live_stats="true"]'
            ),
            'membership_content' => array(
                'name' => 'Membership Content',
                'description' => 'Restrict content by membership',
                'icon' => '🔒',
                'category' => 'membership',
                'shortcode' => '[twitch_membership_content level="premium"]Your premium content here[/twitch_membership_content]'
            ),
            'shortcode_builder' => array(
                'name' => 'Shortcode Builder',
                'description' => 'Interactive shortcode builder',
                'icon' => '🔧',
                'category' => 'builder',
                'shortcode' => '[twitch_shortcode_builder theme="light" show_preview="true" default_category="stream"]'
            )
        );
    }
    
    /**
     * Get shortcode templates
     */
    public function get_shortcode_templates() {
        return array(
            'stream_page' => array(
                'name' => 'Stream Page',
                'description' => 'Complete stream page template',
                'icon' => '📄',
                'shortcodes' => array(
                    '[twitch_stream channel="yourchannel" layout="video-with-chat" theme="dark" width="800" height="450"]',
                    '[twitch_follow_button channel="yourchannel" theme="dark"]',
                    '[twitch_donations type="both" buymeacoffee_username="yourusername" paypal_email="your@email.com"]'
                )
            ),
            'content_showcase' => array(
                'name' => 'Content Showcase',
                'description' => 'Showcase clips and VODs',
                'icon' => '🎪',
                'shortcodes' => array(
                    '[twitch_clips channel="yourchannel" limit="6" layout="grid"]',
                    '[twitch_vod channel="yourchannel" limit="3" type="highlight" layout="list"]'
                )
            ),
            'community_page' => array(
                'name' => 'Community Page',
                'description' => 'Chat-focused community page',
                'icon' => '👥',
                'shortcodes' => array(
                    '[twitch_chat_integration channel="yourchannel" theme="dark" height="600"]',
                    '[twitch_subscribe_button channel="yourchannel" theme="dark"]',
                    '[twitch_analytics channel="yourchannel" time_range="24h" metrics="viewers,followers"]'
                )
            ),
            'premium_page' => array(
                'name' => 'Premium Page',
                'description' => 'Membership-restricted content',
                'icon' => '👑',
                'shortcodes' => array(
                    '[twitch_membership_content level="premium"]Exclusive premium content[/twitch_membership_content]',
                    '[twitch_recording_download channel="yourchannel" limit="20" download_type="direct"]',
                    '[twitch_analytics channel="yourchannel" time_range="30d" show_live_stats="true"]'
                )
            )
        );
    }
    
    /**
     * Handle shortcode builder AJAX
     */
    public function handle_shortcode_builder_ajax() {
        check_ajax_referer('twitch_shortcode_builder_nonce', 'nonce');
        
        $action = $_POST['builder_action'] ?? '';
        
        switch ($action) {
            case 'generate_shortcode':
                $this->generate_shortcode_ajax();
                break;
            case 'validate_shortcode':
                $this->validate_shortcode_ajax();
                break;
            case 'preview_shortcode':
                $this->preview_shortcode_ajax();
                break;
            case 'save_preset':
                $this->save_preset_ajax();
                break;
            case 'load_preset':
                $this->load_preset_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Generate shortcode AJAX
     */
    private function generate_shortcode_ajax() {
        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');
        $parameters = $_POST['parameters'] ?? array();
        
        if (!isset($this->supported_shortcodes[$shortcode])) {
            wp_send_json_error('Invalid shortcode');
            return;
        }
        
        $shortcode_data = $this->supported_shortcodes[$shortcode];
        $generated_shortcode = $this->build_shortcode($shortcode, $parameters);
        
        wp_send_json_success(array(
            'shortcode' => $generated_shortcode,
            'formatted' => $this->format_shortcode_for_display($generated_shortcode)
        ));
    }
    
    /**
     * Validate shortcode AJAX
     */
    private function validate_shortcode_ajax() {
        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');
        $parameters = $_POST['parameters'] ?? array();
        
        $validation_errors = array();
        
        if (!isset($this->supported_shortcodes[$shortcode])) {
            $validation_errors[] = 'Invalid shortcode selected';
        } else {
            $shortcode_data = $this->supported_shortcodes[$shortcode];
            
            foreach ($shortcode_data['parameters'] as $param_key => $param) {
                if (isset($param['required']) && $param['required']) {
                    $value = $parameters[$param_key] ?? '';
                    if (empty($value)) {
                        $validation_errors[] = $param['label'] . ' is required';
                    }
                }
                
                // Type validation
                if (isset($parameters[$param_key])) {
                    $value = $parameters[$param_key];
                    $validation_error = $this->validate_parameter_value($param, $value);
                    if ($validation_error) {
                        $validation_errors[] = $validation_error;
                    }
                }
            }
        }
        
        wp_send_json_success(array(
            'valid' => empty($validation_errors),
            'errors' => $validation_errors
        ));
    }
    
    /**
     * Preview shortcode AJAX
     */
    private function preview_shortcode_ajax() {
        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');
        $parameters = $_POST['parameters'] ?? array();
        
        $generated_shortcode = $this->build_shortcode($shortcode, $parameters);
        
        // Generate preview HTML (simplified for security)
        $preview_html = $this->generate_preview_html($shortcode, $parameters);
        
        wp_send_json_success(array(
            'shortcode' => $generated_shortcode,
            'preview' => $preview_html
        ));
    }
    
    /**
     * Save preset AJAX
     */
    private function save_preset_ajax() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $preset_name = sanitize_text_field($_POST['preset_name'] ?? '');
        $preset_data = $_POST['preset_data'] ?? array();
        
        if (empty($preset_name) || empty($preset_data)) {
            wp_send_json_error('Invalid preset data');
            return;
        }
        
        $presets = get_option('twitch_shortcode_builder_presets', array());
        $preset_key = sanitize_key($preset_name);
        
        $presets[$preset_key] = array(
            'name' => $preset_name,
            'data' => $preset_data,
            'created' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );
        
        update_option('twitch_shortcode_builder_presets', $presets);
        
        wp_send_json_success(array(
            'preset_key' => $preset_key,
            'message' => 'Preset saved successfully'
        ));
    }
    
    /**
     * Load preset AJAX
     */
    private function load_preset_ajax() {
        $preset_key = sanitize_key($_POST['preset_key'] ?? '');
        
        $presets = get_option('twitch_shortcode_builder_presets', array());
        
        if (!isset($presets[$preset_key])) {
            wp_send_json_error('Preset not found');
            return;
        }
        
        wp_send_json_success(array(
            'preset' => $presets[$preset_key]
        ));
    }
    
    /**
     * Build shortcode from parameters
     */
    private function build_shortcode($shortcode, $parameters) {
        $atts = array();
        
        foreach ($parameters as $key => $value) {
            if (!empty($value) || $value === '0' || $value === false) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $atts[] = $key . '="' . esc_attr($value) . '"';
            }
        }
        
        $atts_string = !empty($atts) ? ' ' . implode(' ', $atts) : '';
        
        return '[' . $shortcode . $atts_string . ']';
    }
    
    /**
     * Format shortcode for display
     */
    private function format_shortcode_for_display($shortcode) {
        return '<code>' . esc_html($shortcode) . '</code>';
    }
    
    /**
     * Generate preview HTML
     */
    private function generate_preview_html($shortcode, $parameters) {
        // Create a simplified preview for security
        $preview_content = '<div class="twitch-preview-placeholder">';
        $preview_content .= '<span class="twitch-preview-icon">👀</span>';
        $preview_content .= '<p>Shortcode Preview</p>';
        $preview_content .= '<small>' . esc_html($this->build_shortcode($shortcode, $parameters)) . '</small>';
        $preview_content .= '</div>';
        
        return $preview_content;
    }
    
    /**
     * Validate parameter value
     */
    private function validate_parameter_value($param, $value) {
        switch ($param['type']) {
            case 'email':
                if (!is_email($value)) {
                    return $param['label'] . ' must be a valid email address';
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return $param['label'] . ' must be a valid URL';
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    return $param['label'] . ' must be a number';
                }
                if (isset($param['min']) && $value < $param['min']) {
                    return $param['label'] . ' must be at least ' . $param['min'];
                }
                if (isset($param['max']) && $value > $param['max']) {
                    return $param['label'] . ' must be no more than ' . $param['max'];
                }
                break;
        }
        
        return false;
    }
    
    /**
     * Add shortcode builder menu
     */
    public function add_shortcode_builder_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Shortcode Builder',
            'Shortcode Builder',
            'edit_posts',
            'twitch-shortcode-builder',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Shortcode Builder</h1>
            
            <div class="twitch-builder-admin-notice">
                <p>Use this tool to generate Twitch shortcodes with a visual interface. Copy the generated code and paste it into your posts, pages, or widgets.</p>
            </div>
            
            <?php echo do_shortcode('[twitch_shortcode_builder theme="light" show_preview="true" compact_mode="false"]'); ?>
            
            <div class="twitch-builder-help">
                <h3>Available Shortcodes</h3>
                <div class="twitch-shortcode-docs">
                    <?php foreach ($this->supported_shortcodes as $shortcode_key => $shortcode): ?>
                        <div class="twitch-shortcode-doc">
                            <h4>
                                <span class="twitch-doc-icon"><?php echo esc_html($shortcode['icon']); ?></span>
                                <?php echo esc_html($shortcode['name']); ?>
                                <code>[<?php echo esc_html($shortcode_key); ?>]</code>
                            </h4>
                            <p><?php echo esc_html($shortcode['description']); ?></p>
                            
                            <?php if (!empty($shortcode['parameters'])): ?>
                                <div class="twitch-doc-parameters">
                                    <h5>Parameters:</h5>
                                    <ul>
                                        <?php foreach ($shortcode['parameters'] as $param_key => $param): ?>
                                            <li>
                                                <code><?php echo esc_html($param_key); ?></code>
                                                <?php if (isset($param['required']) && $param['required']): ?>
                                                    <span class="twitch-required">*</span>
                                                <?php endif; ?>
                                                - <?php echo esc_html($param['label']); ?>
                                                <?php if (isset($param['default'])): ?>
                                                    (default: <?php echo esc_html($param['default']); ?>)
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue shortcode builder scripts
     */
    public function enqueue_shortcode_builder_scripts() {
        wp_enqueue_style(
            'twitch-shortcode-builder',
            WP_TWITCH_PLUGIN_URL . 'assets/css/shortcode-builder.css',
            array(),
            WP_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-shortcode-builder',
            WP_TWITCH_PLUGIN_URL . 'assets/js/shortcode-builder.js',
            array('jquery', 'wp-util'),
            WP_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-shortcode-builder', 'twitchShortcodeBuilder', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('twitch_shortcode_builder_nonce'),
            'shortcodes' => $this->supported_shortcodes,
            'categories' => $this->get_shortcode_categories(),
            'presets' => $this->get_shortcode_presets(),
            'strings' => array(
                'copySuccess' => 'Shortcode copied to clipboard!',
                'copyError' => 'Failed to copy shortcode',
                'validationError' => 'Please fix the validation errors',
                'previewError' => 'Failed to load preview',
                'presetSaved' => 'Preset saved successfully',
                'presetLoaded' => 'Preset loaded successfully'
            )
        ));
    }
    
    /**
     * Get shortcode definitions
     */
    private function get_shortcode_definitions() {
        return $this->supported_shortcodes;
    }
    
    /**
     * Get builder settings
     */
    private function get_builder_settings() {
        return get_option('twitch_shortcode_builder_settings', array(
            'enabled' => true,
            'default_theme' => 'light',
            'show_preview' => true,
            'compact_mode' => false,
            'allowed_shortcodes' => array_keys($this->supported_shortcodes),
            'custom_presets' => array()
        ));
    }
}

// Initialize advanced shortcode builder
new WP_Twitch_Advanced_Shortcode_Builder();
