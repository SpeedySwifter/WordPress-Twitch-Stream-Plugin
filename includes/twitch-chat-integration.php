<?php
/**
 * Twitch Chat Integration for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Chat_Integration {
    
    private $api;
    private $chat_settings;
    private $websocket;
    
    public function __construct() {
        $this->chat_settings = $this->get_chat_settings();
        $this->api = new SPSWIFTER_Twitch_API();
        
        add_action('init', array($this, 'register_chat_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_chat_scripts'));
        add_action('wp_ajax_spswifter_twitch_chat', array($this, 'handle_chat_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_chat', array($this, 'handle_chat_ajax'));
        add_action('wp_ajax_spswifter_twitch_chat_messages', array($this, 'handle_chat_messages_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_chat_messages', array($this, 'handle_chat_messages_ajax'));
    }
    
    /**
     * Register chat shortcodes
     */
    public function register_chat_shortcodes() {
        add_shortcode('spswifter_twitch_chat', array($this, 'render_chat_shortcode'));
        add_shortcode('spswifter_twitch_chat_embed', array($this, 'render_chat_embed_shortcode'));
        add_shortcode('spswifter_twitch_chat_recent', array($this, 'render_recent_chat_shortcode'));
    }
    
    /**
     * Enqueue chat scripts
     */
    public function enqueue_chat_scripts() {
        wp_enqueue_style(
            'twitch-chat',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/twitch-chat.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-chat',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/twitch-chat.js',
            array('jquery'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-chat', 'twitchChat', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_chat_nonce'),
            'websocketUrl' => $this->get_websocket_url(),
            'channel' => $this->chat_settings['default_channel'] ?? '',
            'theme' => $this->chat_settings['theme'] ?? 'dark',
            'maxMessages' => $this->chat_settings['max_messages'] ?? 100,
        ));
    }
    
    /**
     * Render chat shortcode
     */
    public function render_chat_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'width' => '100%',
            'height' => '500',
            'theme' => $this->chat_settings['theme'] ?? 'dark',
            'show_recent' => 'true',
            'max_messages' => '50',
            'show_input' => 'true',
            'show_header' => 'true',
            'layout' => 'default',
        ), $atts);
        
        if (empty($atts['channel'])) {
            return '<p class="twitch-chat-error">Bitte geben Sie einen Kanal an: [spswifter_twitch_chat channel="username"]</p>';
        }
        
        ob_start();
        ?>
        <div class="twitch-chat-container twitch-chat-<?php echo esc_attr($atts['theme']); ?> twitch-chat-<?php echo esc_attr($atts['layout']); ?>" 
             data-channel="<?php echo esc_attr($atts['channel']); ?>"
             data-max-messages="<?php echo esc_attr($atts['max_messages']); ?>"
             style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;">
            
            <?php if ($atts['show_header'] === 'true'): ?>
                <div class="twitch-chat-header">
                    <div class="twitch-chat-title">
                        <span class="twitch-chat-channel">#<?php echo esc_html($atts['channel']); ?></span>
                        <span class="twitch-chat-status">
                            <span class="twitch-chat-viewers">0 Zuschauer</span>
                            <span class="twitch-chat-live-indicator">🔴 LIVE</span>
                        </span>
                    </div>
                    <div class="twitch-chat-controls">
                        <button class="twitch-chat-toggle-emoji">😊</button>
                        <button class="twitch-chat-toggle-settings">⚙️</button>
                        <button class="twitch-chat-clear">🗑️</button>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="twitch-chat-messages" id="twitch-chat-<?php echo esc_attr($atts['channel']); ?>">
                <?php if ($atts['show_recent'] === 'true'): ?>
                    <?php echo $this->render_recent_messages($atts['channel'], $atts['max_messages']); ?>
                <?php endif; ?>
            </div>
            
            <?php if ($atts['show_input'] === 'true'): ?>
                <div class="twitch-chat-input-container">
                    <div class="twitch-chat-emoji-picker" style="display: none;">
                        <div class="twitch-emoji-grid">
                            <?php echo $this->render_emoji_grid(); ?>
                        </div>
                    </div>
                    <input type="text" 
                           class="twitch-chat-input" 
                           placeholder="Nachricht eingeben..." 
                           maxlength="500">
                    <button class="twitch-chat-send">Senden</button>
                </div>
            <?php endif; ?>
            
            <div class="twitch-chat-settings-panel" style="display: none;">
                <div class="twitch-chat-settings-content">
                    <div class="twitch-chat-setting">
                        <label>Theme:</label>
                        <select class="twitch-chat-theme-select">
                            <option value="dark">Dark</option>
                            <option value="light">Light</option>
                            <option value="blue">Blue</option>
                        </select>
                    </div>
                    <div class="twitch-chat-setting">
                        <label>Font Size:</label>
                        <select class="twitch-chat-font-size">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    <div class="twitch-chat-setting">
                        <label>Show Timestamps:</label>
                        <input type="checkbox" class="twitch-chat-show-timestamps" checked>
                    </div>
                    <div class="twitch-chat-setting">
                        <label>Show Badges:</label>
                        <input type="checkbox" class="twitch-chat-show-badges" checked>
                    </div>
                    <div class="twitch-chat-setting">
                        <label>Filter Emotes:</label>
                        <input type="checkbox" class="twitch-chat-filter-emotes">
                    </div>
                </div>
            </div>
            
            <div class="twitch-chat-loading" id="twitch-chat-loading-<?php echo esc_attr($atts['channel']); ?>">
                <div class="twitch-chat-spinner"></div>
                <span>Chat wird geladen...</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render chat embed shortcode
     */
    public function render_chat_embed_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'width' => '100%',
            'height' => '500',
            'theme' => 'dark',
            'parent' => '',
        ), $atts);
        
        if (empty($atts['channel'])) {
            return '<p class="twitch-chat-error">Bitte geben Sie einen Kanal an: [spswifter_twitch_chat_embed channel="username"]</p>';
        }
        
        $parent = $atts['parent'] ?: wp_unslash($_SERVER['HTTP_HOST']);
        
        ob_start();
        ?>
        <div class="twitch-chat-embed-container">
            <iframe 
                src="https://www.twitch.tv/embed/<?php echo esc_attr($atts['channel']); ?>/chat?parent=<?php echo esc_attr($parent); ?>&darkpopout=<?php echo $atts['theme'] === 'dark' ? 'true' : 'false'; ?>"
                height="<?php echo esc_attr($atts['height']); ?>"
                width="<?php echo esc_attr($atts['width']); ?>"
                frameborder="0"
                scrolling="no"
                allowfullscreen="true">
            </iframe>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render recent chat shortcode
     */
    public function render_recent_chat_shortcode($atts) {
        $atts = shortcode_atts(array(
            'channel' => '',
            'limit' => '10',
            'show_timestamp' => 'true',
            'show_badges' => 'true',
            'theme' => 'dark',
        ), $atts);
        
        if (empty($atts['channel'])) {
            return '<p class="twitch-chat-error">Bitte geben Sie einen Kanal an: [spswifter_twitch_chat_recent channel="username"]</p>';
        }
        
        return $this->render_recent_messages($atts['channel'], $atts['limit'], $atts);
    }
    
    /**
     * Render recent messages
     */
    private function render_recent_messages($channel, $limit, $atts = array()) {
        $messages = $this->get_recent_messages($channel, $limit);
        
        if (empty($messages)) {
            return '<div class="twitch-chat-no-messages">Keine aktuellen Nachrichten</div>';
        }
        
        $show_timestamp = $atts['show_timestamp'] ?? 'true';
        $show_badges = $atts['show_badges'] ?? 'true';
        
        ob_start();
        foreach ($messages as $message) {
            ?>
            <div class="twitch-chat-message" data-user="<?php echo esc_attr($message['user']); ?>">
                <?php if ($show_timestamp === 'true'): ?>
                    <span class="twitch-chat-timestamp"><?php echo esc_html($message['timestamp']); ?></span>
                <?php endif; ?>
                
                <?php if ($show_badges === 'true' && !empty($message['badges'])): ?>
                    <span class="twitch-chat-badges">
                        <?php foreach ($message['badges'] as $badge): ?>
                            <span class="twitch-chat-badge twitch-badge-<?php echo esc_attr($badge); ?>"></span>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
                
                <span class="twitch-chat-username" style="color: <?php echo esc_attr($message['color']); ?>">
                    <?php echo esc_html($message['user']); ?>:
                </span>
                
                <span class="twitch-chat-message-text">
                    <?php echo $this->parse_message_emotes($message['message'], $message['emotes'] ?? array()); ?>
                </span>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get recent messages
     */
    private function get_recent_messages($channel, $limit = 10) {
        // This would typically connect to Twitch IRC or use a chat API
        // For now, we'll return mock data
        return array(
            array(
                'user' => 'TestUser1',
                'message' => 'Great stream! 👍',
                'timestamp' => gmdate('H:i'),
                'color' => '#FF0000',
                'badges' => array('subscriber'),
                'emotes' => array(),
            ),
            array(
                'user' => 'TestUser2',
                'message' => 'Hello everyone!',
                'timestamp' => gmdate('H:i', strtotime('-5 minutes')),
                'color' => '#0000FF',
                'badges' => array(),
                'emotes' => array(),
            ),
            array(
                'user' => 'TestUser3',
                'message' => 'Love the content!',
                'timestamp' => gmdate('H:i', strtotime('-10 minutes')),
                'color' => '#00FF00',
                'badges' => array('vip'),
                'emotes' => array(),
            ),
        );
    }
    
    /**
     * Parse message emotes
     */
    private function parse_message_emotes($message, $emotes) {
        // This would parse Twitch emotes and replace them with images
        // For now, we'll return the message as-is
        return esc_html($message);
    }
    
    /**
     * Render emoji grid
     */
    private function render_emoji_grid() {
        $emojis = array('😀', '😂', '😍', '🤣', '😎', '👍', '❤️', '🔥', '💯', '🎉', '🎮', '🎯', '⚡', '💎', '🌟', '✨');
        
        ob_start();
        foreach ($emojis as $emoji) {
            echo '<button class="twitch-emoji-btn" data-emoji="' . esc_attr($emoji) . '">' . esc_html($emoji) . '</button>';
        }
        return ob_get_clean();
    }
    
    /**
     * Handle chat AJAX
     */
    public function handle_chat_ajax() {
        check_ajax_referer('spswifter_twitch_chat_nonce', 'nonce');
        
        $action = wp_unslash($_POST['chat_action']) ?? '';
        
        switch ($action) {
            case 'connect':
                $this->connect_chat_ajax();
                break;
            case 'disconnect':
                $this->disconnect_chat_ajax();
                break;
            case 'send_message':
                $this->send_message_ajax();
                break;
            case 'get_messages':
                $this->get_messages_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Handle chat messages AJAX
     */
    public function handle_chat_messages_ajax() {
        check_ajax_referer('spswifter_twitch_chat_nonce', 'nonce');
        
        $channel = sanitize_text_field(wp_unslash($_POST['channel']) ?? '');
        $limit = intval(wp_unslash($_POST['limit']) ?? 50);
        
        if (empty($channel)) {
            wp_send_json_error('Channel is required');
        }
        
        $messages = $this->get_recent_messages($channel, $limit);
        
        wp_send_json_success(array('messages' => $messages));
    }
    
    /**
     * Connect chat AJAX
     */
    private function connect_chat_ajax() {
        $channel = sanitize_text_field(wp_unslash($_POST['channel']) ?? '');
        
        if (empty($channel)) {
            wp_send_json_error('Channel is required');
        }
        
        // This would connect to Twitch IRC
        // For now, we'll simulate a connection
        wp_send_json_success(array(
            'connected' => true,
            'channel' => $channel,
            'message' => 'Connected to chat'
        ));
    }
    
    /**
     * Disconnect chat AJAX
     */
    private function disconnect_chat_ajax() {
        // This would disconnect from Twitch IRC
        wp_send_json_success(array('disconnected' => true));
    }
    
    /**
     * Send message AJAX
     */
    private function send_message_ajax() {
        $message = sanitize_text_field(wp_unslash($_POST['message']) ?? '');
        $channel = sanitize_text_field(wp_unslash($_POST['channel']) ?? '');
        
        if (empty($message) || empty($channel)) {
            wp_send_json_error('Message and channel are required');
        }
        
        // This would send the message to Twitch IRC
        // For now, we'll simulate sending
        wp_send_json_success(array(
            'sent' => true,
            'message' => $message,
            'channel' => $channel
        ));
    }
    
    /**
     * Get messages AJAX
     */
    private function get_messages_ajax() {
        $channel = sanitize_text_field(wp_unslash($_POST['channel']) ?? '');
        $limit = intval(wp_unslash($_POST['limit']) ?? 50);
        
        if (empty($channel)) {
            wp_send_json_error('Channel is required');
        }
        
        $messages = $this->get_recent_messages($channel, $limit);
        
        wp_send_json_success(array('messages' => $messages));
    }
    
    /**
     * Get websocket URL
     */
    private function get_websocket_url() {
        // This would return the Twitch websocket URL
        // For now, we'll return a placeholder
        return 'wss://irc-ws.chat.twitch.tv:443/';
    }
    
    /**
     * Get chat settings
     */
    private function get_chat_settings() {
        return get_option('spswifter_twitch_chat_settings', array(
            'enabled' => false,
            'default_channel' => '',
            'theme' => 'dark',
            'max_messages' => 100,
            'show_timestamps' => true,
            'show_badges' => true,
            'filter_emotes' => false,
            'font_size' => 'medium',
            'allow_anonymous' => true,
            'require_login' => false,
            'moderation_enabled' => false,
            'blocked_words' => array(),
            'allowed_commands' => array('!uptime', '!socials', '!commands'),
        ));
    }
    
    /**
     * Add chat settings menu
     */
    public function add_chat_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Chat Settings',
            'Chat',
            'manage_options',
            'twitch-chat-settings',
            array($this, 'render_chat_settings_page')
        );
    }
    
    /**
     * Render chat settings page
     */
    public function render_chat_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Chat Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_chat_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_chat_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Chat Integration</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[enabled]" <?php checked($this->chat_settings['enabled'], true); ?> />
                            <label>Enable Twitch chat integration</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Default Channel</th>
                        <td>
                            <input type="text" name="spswifter_twitch_chat_settings[default_channel]" value="<?php echo esc_attr($this->chat_settings['default_channel'] ?? ''); ?>" class="regular-text" />
                            <p class="description">Default Twitch channel for chat widgets</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Default Theme</th>
                        <td>
                            <select name="spswifter_twitch_chat_settings[theme]">
                                <option value="dark" <?php selected($this->chat_settings['theme'], 'dark'); ?>>Dark</option>
                                <option value="light" <?php selected($this->chat_settings['theme'], 'light'); ?>>Light</option>
                                <option value="blue" <?php selected($this->chat_settings['theme'], 'blue'); ?>>Blue</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Max Messages</th>
                        <td>
                            <input type="number" name="spswifter_twitch_chat_settings[max_messages]" value="<?php echo esc_attr($this->chat_settings['max_messages'] ?? 100); ?>" min="10" max="1000" class="small-text" />
                            <p class="description">Maximum number of messages to display</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Timestamps</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[show_timestamps]" <?php checked($this->chat_settings['show_timestamps'], true); ?> />
                            <label>Show message timestamps</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Badges</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[show_badges]" <?php checked($this->chat_settings['show_badges'], true); ?> />
                            <label>Show user badges (moderator, subscriber, etc.)</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Filter Emotes</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[filter_emotes]" <?php checked($this->chat_settings['filter_emotes'], false); ?> />
                            <label>Filter Twitch emotes from messages</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Allow Anonymous Messages</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[allow_anonymous]" <?php checked($this->chat_settings['allow_anonymous'], true); ?> />
                            <label>Allow non-logged-in users to view chat</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Require Login to Chat</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[require_login]" <?php checked($this->chat_settings['require_login'], false); ?> />
                            <label>Require users to be logged in to send messages</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Moderation</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_chat_settings[moderation_enabled]" <?php checked($this->chat_settings['moderation_enabled'], false); ?> />
                            <label>Enable basic moderation features</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Blocked Words</th>
                        <td>
                            <textarea name="spswifter_twitch_chat_settings[blocked_words]" rows="3" class="large-text"><?php echo esc_textarea(implode("\n", $this->chat_settings['blocked_words'] ?? array())); ?></textarea>
                            <p class="description">One word per line. These words will be filtered from chat.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Allowed Commands</th>
                        <td>
                            <textarea name="spswifter_twitch_chat_settings[allowed_commands]" rows="3" class="large-text"><?php echo esc_textarea(implode("\n", $this->chat_settings['allowed_commands'] ?? array('!uptime', '!socials', '!commands'))); ?></textarea>
                            <p class="description">One command per line. These commands will be processed.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get chat statistics
     */
    public function get_chat_statistics($channel) {
        // This would return real chat statistics
        return array(
            'total_messages' => 0,
            'unique_users' => 0,
            'active_users' => 0,
            'emotes_used' => 0,
            'commands_used' => 0,
            'moderation_actions' => 0,
        );
    }
    
    /**
     * Moderate message
     */
    public function moderate_message($message, $user) {
        $blocked_words = $this->chat_settings['blocked_words'] ?? array();
        
        foreach ($blocked_words as $word) {
            if (stripos($message, $word) !== false) {
                return false; // Message blocked
            }
        }
        
        return true; // Message allowed
    }
    
    /**
     * Process command
     */
    public function process_command($command, $user, $channel) {
        $allowed_commands = $this->chat_settings['allowed_commands'] ?? array();
        
        if (!in_array($command, $allowed_commands)) {
            return 'Unknown command. Available commands: ' . implode(', ', $allowed_commands);
        }
        
        switch ($command) {
            case '!uptime':
                return 'Stream has been live for X hours';
            case '!socials':
                return 'Twitter: @username | Discord: discord.gg/invite';
            case '!commands':
                return 'Available commands: ' . implode(', ', $allowed_commands);
            default:
                return 'Command processed';
        }
    }
    
    /**
     * Get user color
     */
    public function get_user_color($username) {
        // This would get the user's actual color from Twitch
        // For now, we'll generate a consistent color based on username
        $hash = md5($username);
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Get user badges
     */
    public function get_user_badges($username, $channel) {
        // This would get the user's actual badges from Twitch
        // For now, we'll return empty array
        return array();
    }
}

// Initialize chat integration
new SPSWIFTER_Twitch_Chat_Integration();
