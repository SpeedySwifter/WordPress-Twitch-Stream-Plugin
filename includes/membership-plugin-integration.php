<?php
/**
 * Membership Plugin Integration for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Membership_Integration {
    
    private $membership_settings;
    private $supported_plugins = array();
    private $active_plugins = array();
    
    public function __construct() {
        // Delay initialization until WordPress is loaded
        add_action('init', array($this, 'init'));
    }

    public function init() {
        $this->membership_settings = $this->get_membership_settings();
        $this->detect_membership_plugins();
        
        add_action('init', array($this, 'init_membership_integration'));
        add_action('plugins_loaded', array($this, 'load_membership_hooks'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_membership_scripts'));
        add_action('wp_ajax_spswifter_twitch_membership', array($this, 'handle_membership_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_membership', array($this, 'handle_membership_ajax'));
        add_action('admin_menu', array($this, 'add_membership_settings_menu'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_membership_shortcodes'));
    }
    
    /**
     * Detect membership plugins
     */
    private function detect_membership_plugins() {
        $this->supported_plugins = array(
            'memberpress' => array(
                'name' => 'MemberPress',
                'class' => 'MeprPlugin',
                'active' => class_exists('MeprPlugin'),
                'version_check' => 'class_exists("MeprPlugin")'
            ),
            'restrict_content_pro' => array(
                'name' => 'Restrict Content Pro',
                'class' => 'RCP_PLUGIN',
                'active' => defined('RCP_PLUGIN_VERSION'),
                'version_check' => 'defined("RCP_PLUGIN_VERSION")'
            ),
            'paid_memberships_pro' => array(
                'name' => 'Paid Memberships Pro',
                'class' => 'PMPro_Plugin',
                'active' => class_exists('PMPro_Plugin'),
                'version_check' => 'class_exists("PMPro_Plugin")'
            ),
            'woocommerce_memberships' => array(
                'name' => 'WooCommerce Memberships',
                'class' => 'WC_Memberships',
                'active' => class_exists('WC_Memberships'),
                'version_check' => 'class_exists("WC_Memberships")'
            ),
            'ultimate_member' => array(
                'name' => 'Ultimate Member',
                'class' => 'UM',
                'active' => class_exists('UM'),
                'version_check' => 'class_exists("UM")'
            ),
            's2member' => array(
                'name' => 's2Member',
                'class' => 'c_ws_plugin__s2member',
                'active' => class_exists('c_ws_plugin__s2member'),
                'version_check' => 'class_exists("c_ws_plugin__s2member")'
            )
        );
        
        foreach ($this->supported_plugins as $key => $plugin) {
            if ($plugin['active']) {
                $this->active_plugins[$key] = $plugin;
            }
        }
    }
    
    /**
     * Initialize membership integration
     */
    public function init_membership_integration() {
        if (empty($this->active_plugins)) {
            return;
        }
        
        // Load specific plugin integrations
        foreach ($this->active_plugins as $plugin_key => $plugin) {
            $this->load_plugin_integration($plugin_key);
        }
        
        // Add membership-specific features
        $this->add_membership_features();
        
        // Add content restrictions
        $this->add_content_restrictions();
        
        // Add membership levels
        $this->add_membership_levels();
    }
    
    /**
     * Load membership hooks
     */
    public function load_membership_hooks() {
        if (empty($this->active_plugins)) {
            return;
        }
        
        // Common membership hooks
        add_action('wp_head', array($this, 'add_membership_head_content'));
        add_filter('the_content', array($this, 'restrict_content_based_on_membership'));
        add_filter('spswifter_twitch_stream_content', array($this, 'filter_stream_content_for_members'));
        add_action('spswifter_twitch_before_stream', array($this, 'check_stream_access'));
        add_action('spswifter_twitch_stream_access_denied', array($this, 'show_membership_upgrade_prompt'));
    }
    
    /**
     * Load plugin integration
     */
    private function load_plugin_integration($plugin_key) {
        switch ($plugin_key) {
            case 'memberpress':
                $this->load_memberpress_integration();
                break;
            case 'restrict_content_pro':
                $this->load_rcp_integration();
                break;
            case 'paid_memberships_pro':
                $this->load_pmpro_integration();
                break;
            case 'woocommerce_memberships':
                $this->load_wc_memberships_integration();
                break;
            case 'ultimate_member':
                $this->load_ultimate_member_integration();
                break;
            case 's2member':
                $this->load_s2member_integration();
                break;
        }
    }
    
    /**
     * Load MemberPress integration
     */
    private function load_memberpress_integration() {
        if (!class_exists('MeprPlugin')) {
            return;
        }
        
        // MemberPress specific hooks
        add_action('mepr_account_nav', array($this, 'add_spswifter_twitch_to_memberpress_account'));
        add_action('mepr_account_content', array($this, 'show_spswifter_twitch_membership_content'));
        add_filter('mepr-rule-conditions', array($this, 'add_spswifter_twitch_rule_conditions'));
        add_action('mepr-process-signup', array($this, 'handle_memberpress_signup'));
        add_action('mepr_transaction-recorded', array($this, 'handle_memberpress_payment'));
    }
    
    /**
     * Load Restrict Content Pro integration
     */
    private function load_rcp_integration() {
        if (!defined('RCP_PLUGIN_VERSION')) {
            return;
        }
        
        // RCP specific hooks
        add_action('rcp_member_level_updated', array($this, 'handle_rcp_level_change'));
        add_action('rcp_after_membership_expire', array($this, 'handle_rcp_expiration'));
        add_filter('rcp_can_access_content', array($this, 'check_rcp_content_access'), 10, 3);
        add_action('rcp_before_subscription_form', array($this, 'add_spswifter_twitch_to_rcp_form'));
    }
    
    /**
     * Load Paid Memberships Pro integration
     */
    private function load_pmpro_integration() {
        if (!class_exists('PMPro_Plugin')) {
            return;
        }
        
        // PMPro specific hooks
        add_action('pmpro_membership_level_after_other_settings', array($this, 'add_spswifter_twitch_pmpro_settings'));
        add_action('pmpro_after_checkout', array($this, 'handle_pmpro_checkout'));
        add_filter('pmpro_has_membership_access_filter', array($this, 'check_pmpro_access'), 10, 4);
        add_action('pmpro_membership_post_membership_saved', array($this, 'save_pmpro_spswifter_twitch_settings'));
    }
    
    /**
     * Load WooCommerce Memberships integration
     */
    private function load_wc_memberships_integration() {
        if (!class_exists('WC_Memberships')) {
            return;
        }
        
        // WC Memberships specific hooks
        add_action('wc_memberships_membership_saved', array($this, 'handle_wc_membership_save'));
        add_filter('wc_memberships_user_has_membership_access', array($this, 'check_wc_membership_access'), 10, 4);
        add_action('wc_memberships_before_members_area_content', array($this, 'add_spswifter_twitch_to_members_area'));
        add_filter('wc_memberships_members_area_my_memberships_actions', array($this, 'add_spswifter_twitch_membership_actions'));
    }
    
    /**
     * Load Ultimate Member integration
     */
    private function load_ultimate_member_integration() {
        if (!class_exists('UM')) {
            return;
        }
        
        // UM specific hooks
        add_action('um_after_account_fields', array($this, 'add_spswifter_twitch_to_um_account'));
        add_action('um_user_register', array($this, 'handle_um_registration'));
        add_filter('um_user_profile_tabs', array($this, 'add_spswifter_twitch_um_profile_tab'));
        add_action('um_profile_content_twitch', array($this, 'show_spswifter_twitch_um_profile_content'));
    }
    
    /**
     * Load s2Member integration
     */
    private function load_s2member_integration() {
        if (!class_exists('c_ws_plugin__s2member')) {
            return;
        }
        
        // s2Member specific hooks
        add_action('ws_plugin__s2member_during_configure_user_roles', array($this, 'add_spswifter_twitch_s2member_roles'));
        add_filter('ws_plugin__s2member_check_specific_level', array($this, 'check_s2member_level_access'));
        add_action('ws_plugin__s2member_after_payment', array($this, 'handle_s2member_payment'));
        add_filter('ws_plugin__s2member_content_filter', array($this, 'filter_s2member_content'));
    }
    
    /**
     * Add membership features
     */
    private function add_membership_features() {
        // Add membership-based stream access
        add_filter('spswifter_twitch_stream_access_level', array($this, 'get_membership_access_level'));
        
        // Add membership badges
        add_filter('spswifter_twitch_chat_user_badges', array($this, 'add_membership_badges'));
        
        // Add membership-specific chat features
        add_filter('spswifter_twitch_chat_permissions', array($this, 'set_chat_permissions'));
        
        // Add membership analytics
        add_filter('spswifter_twitch_analytics_data', array($this, 'add_membership_analytics'));
    }
    
    /**
     * Add content restrictions
     */
    private function add_content_restrictions() {
        // Restrict VODs based on membership
        add_filter('spswifter_twitch_vod_access', array($this, 'restrict_vod_access'));
        
        // Restrict clips based on membership
        add_filter('spswifter_twitch_clip_access', array($this, 'restrict_clip_access'));
        
        // Restrict chat based on membership
        add_filter('spswifter_twitch_chat_access', array($this, 'restrict_chat_access'));
        
        // Restrict analytics based on membership
        add_filter('spswifter_twitch_analytics_access', array($this, 'restrict_analytics_access'));
    }
    
    /**
     * Add membership levels
     */
    private function add_membership_levels() {
        // Define Twitch-specific membership levels
        $this->spswifter_twitch_membership_levels = array(
            'free' => array(
                'name' => 'Free',
                'access' => array('basic_stream', 'chat_read'),
                'features' => array('watch_stream', 'read_chat')
            ),
            'basic' => array(
                'name' => 'Basic',
                'access' => array('basic_stream', 'chat_read', 'vod_basic'),
                'features' => array('watch_stream', 'read_chat', 'basic_vods')
            ),
            'premium' => array(
                'name' => 'Premium',
                'access' => array('full_stream', 'chat_full', 'vod_full', 'clips'),
                'features' => array('watch_stream', 'chat_participate', 'full_vods', 'clips')
            ),
            'vip' => array(
                'name' => 'VIP',
                'access' => array('full_stream', 'chat_full', 'vod_full', 'clips', 'analytics'),
                'features' => array('watch_stream', 'chat_participate', 'full_vods', 'clips', 'analytics', 'special_badges')
            )
        );
    }
    
    /**
     * Register membership shortcodes
     */
    public function register_membership_shortcodes() {
        add_shortcode('spswifter_twitch_membership_content', array($this, 'render_membership_content_shortcode'));
        add_shortcode('spswifter_twitch_membership_required', array($this, 'render_membership_required_shortcode'));
        add_shortcode('spswifter_twitch_membership_levels', array($this, 'render_membership_levels_shortcode'));
        add_shortcode('spswifter_twitch_membership_upgrade', array($this, 'render_membership_upgrade_shortcode'));
        add_shortcode('spswifter_twitch_membership_badge', array($this, 'render_membership_badge_shortcode'));
    }
    
    /**
     * Render membership content shortcode
     */
    public function render_membership_content_shortcode($atts, $content = '') {
        $atts = shortcode_atts(array(
            'level' => 'basic',
            'message' => 'This content requires a membership to view.',
            'upgrade_url' => '',
        ), $atts);
        
        if ($this->user_has_membership_level($atts['level'])) {
            return do_shortcode($content);
        } else {
            $upgrade_url = $atts['upgrade_url'] ?: $this->get_upgrade_url();
            return '<div class="twitch-membership-restricted">' .
                   '<p>' . esc_html($atts['message']) . '</p>' .
                   ($upgrade_url ? '<a href="' . esc_url($upgrade_url) . '" class="twitch-upgrade-btn">Upgrade Now</a>' : '') .
                   '</div>';
        }
    }
    
    /**
     * Render membership required shortcode
     */
    public function render_membership_required_shortcode($atts) {
        $atts = shortcode_atts(array(
            'level' => 'basic',
            'title' => 'Membership Required',
            'message' => 'You need a membership to access this content.',
            'show_upgrade' => 'true',
        ), $atts);
        
        if ($this->user_has_membership_level($atts['level'])) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="twitch-membership-required">
            <div class="twitch-membership-icon">🔒</div>
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <p><?php echo esc_html($atts['message']); ?></p>
            <?php if ($atts['show_upgrade'] === 'true'): ?>
                <a href="<?php echo esc_url($this->get_upgrade_url()); ?>" class="twitch-upgrade-btn">
                    Upgrade to Access
                </a>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render membership levels shortcode
     */
    public function render_membership_levels_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_current' => 'true',
            'show_upgrade' => 'true',
            'style' => 'grid',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-membership-levels twitch-style-<?php echo esc_attr($atts['style']); ?>">
            <?php foreach ($this->spswifter_twitch_membership_levels as $level_key => $level): ?>
                <?php
                $has_access = $this->user_has_membership_level($level_key);
                $is_current = $this->get_current_membership_level() === $level_key;
                ?>
                <div class="twitch-membership-level <?php echo $has_access ? 'has-access' : 'no-access'; ?> <?php echo $is_current ? 'current-level' : ''; ?>">
                    <div class="twitch-level-header">
                        <h3><?php echo esc_html($level['name']); ?></h3>
                        <?php if ($is_current && $atts['show_current'] === 'true'): ?>
                            <span class="twitch-current-badge">Current</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="twitch-level-features">
                        <ul>
                            <?php foreach ($level['features'] as $feature): ?>
                                <li class="<?php echo $has_access ? 'available' : 'unavailable'; ?>">
                                    <?php echo $has_access ? '✓' : '✗'; ?> <?php echo esc_html($feature); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <?php if (!$has_access && $atts['show_upgrade'] === 'true'): ?>
                        <div class="twitch-level-action">
                            <a href="<?php echo esc_url($this->get_upgrade_url($level_key)); ?>" class="twitch-upgrade-btn">
                                Upgrade to <?php echo esc_html($level['name']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render membership upgrade shortcode
     */
    public function render_membership_upgrade_shortcode($atts) {
        $atts = shortcode_atts(array(
            'level' => 'basic',
            'title' => 'Upgrade Your Membership',
            'message' => 'Get access to exclusive content and features.',
            'button_text' => 'Upgrade Now',
        ), $atts);
        
        if ($this->user_has_membership_level($atts['level'])) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="twitch-membership-upgrade">
            <div class="twitch-upgrade-content">
                <h3><?php echo esc_html($atts['title']); ?></h3>
                <p><?php echo esc_html($atts['message']); ?></p>
                <a href="<?php echo esc_url($this->get_upgrade_url($atts['level'])); ?>" class="twitch-upgrade-btn">
                    <?php echo esc_html($atts['button_text']); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render membership badge shortcode
     */
    public function render_membership_badge_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'style' => 'default',
            'show_name' => 'true',
        ), $atts);
        
        $user_level = $this->get_user_membership_level($atts['user_id']);
        
        if (!$user_level || $user_level === 'free') {
            return '';
        }
        
        $level_info = $this->spswifter_twitch_membership_levels[$user_level];
        
        ob_start();
        ?>
        <div class="twitch-membership-badge twitch-style-<?php echo esc_attr($atts['style']); ?> twitch-level-<?php echo esc_attr($user_level); ?>">
            <span class="twitch-badge-icon">👑</span>
            <?php if ($atts['show_name'] === 'true'): ?>
                <span class="twitch-badge-name"><?php echo esc_html($level_info['name']); ?></span>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Check if user has membership level
     */
    private function user_has_membership_level($required_level) {
        $user_level = $this->get_current_membership_level();
        
        if (!$user_level) {
            return $required_level === 'free';
        }
        
        $level_hierarchy = array('free' => 0, 'basic' => 1, 'premium' => 2, 'vip' => 3);
        
        return $level_hierarchy[$user_level] >= $level_hierarchy[$required_level];
    }
    
    /**
     * Get current membership level
     */
    private function get_current_membership_level() {
        if (!is_user_logged_in()) {
            return 'free';
        }
        
        $user_id = get_current_user_id();
        return $this->get_user_membership_level($user_id);
    }
    
    /**
     * Get user membership level
     */
    private function get_user_membership_level($user_id) {
        // Check each active membership plugin
        foreach ($this->active_plugins as $plugin_key => $plugin) {
            $level = $this->get_membership_level_from_plugin($plugin_key, $user_id);
            if ($level && $level !== 'free') {
                return $level;
            }
        }
        
        return 'free';
    }
    
    /**
     * Get membership level from plugin
     */
    private function get_membership_level_from_plugin($plugin_key, $user_id) {
        switch ($plugin_key) {
            case 'memberpress':
                return $this->get_memberpress_level($user_id);
            case 'restrict_content_pro':
                return $this->get_rcp_level($user_id);
            case 'paid_memberships_pro':
                return $this->get_pmpro_level($user_id);
            case 'woocommerce_memberships':
                return $this->get_wc_membership_level($user_id);
            case 'ultimate_member':
                return $this->get_um_level($user_id);
            case 's2member':
                return $this->get_s2member_level($user_id);
            default:
                return 'free';
        }
    }
    
    /**
     * Get MemberPress level
     */
    private function get_memberpress_level($user_id) {
        if (!class_exists('MeprUser')) {
            return 'free';
        }
        
        $user = new MeprUser($user_id);
        $active_memberships = $user->active_product_subscriptions('memberships');
        
        if (empty($active_memberships)) {
            return 'free';
        }
        
        $membership = reset($active_memberships);
        $membership_id = $membership->ID;
        
        // Map MemberPress membership IDs to Twitch levels
        $level_mapping = $this->membership_settings['memberpress_level_mapping'] ?? array();
        
        return $level_mapping[$membership_id] ?? 'basic';
    }
    
    /**
     * Get RCP level
     */
    private function get_rcp_level($user_id) {
        if (!function_exists('rcp_get_membership_level')) {
            return 'free';
        }
        
        $level_id = rcp_get_membership_level($user_id);
        
        if (!$level_id) {
            return 'free';
        }
        
        // Map RCP level IDs to Twitch levels
        $level_mapping = $this->membership_settings['rcp_level_mapping'] ?? array();
        
        return $level_mapping[$level_id] ?? 'basic';
    }
    
    /**
     * Get PMPro level
     */
    private function get_pmpro_level($user_id) {
        if (!function_exists('pmpro_getMembershipLevelForUser')) {
            return 'free';
        }
        
        $level = pmpro_getMembershipLevelForUser($user_id);
        
        if (!$level) {
            return 'free';
        }
        
        $level_id = $level->ID;
        
        // Map PMPro level IDs to Twitch levels
        $level_mapping = $this->membership_settings['pmpro_level_mapping'] ?? array();
        
        return $level_mapping[$level_id] ?? 'basic';
    }
    
    /**
     * Get WooCommerce Membership level
     */
    private function get_wc_membership_level($user_id) {
        if (!class_exists('WC_Memberships_Membership_Plans')) {
            return 'free';
        }
        
        $user_memberships = wc_memberships_get_user_active_memberships($user_id);
        
        if (empty($user_memberships)) {
            return 'free';
        }
        
        $membership = reset($user_memberships);
        $plan_id = $membership->get_plan_id();
        
        // Map WC membership plan IDs to Twitch levels
        $level_mapping = $this->membership_settings['wc_level_mapping'] ?? array();
        
        return $level_mapping[$plan_id] ?? 'basic';
    }
    
    /**
     * Get Ultimate Member level
     */
    private function get_um_level($user_id) {
        if (!class_exists('UM')) {
            return 'free';
        }
        
        um_fetch_user($user_id);
        $role = UM()->roles()->get_user_role($user_id);
        
        // Map UM roles to Twitch levels
        $level_mapping = $this->membership_settings['um_level_mapping'] ?? array();
        
        return $level_mapping[$role] ?? 'basic';
    }
    
    /**
     * Get s2Member level
     */
    private function get_s2member_level($user_id) {
        if (!class_exists('c_ws_plugin__s2member')) {
            return 'free';
        }
        
        $level = c_ws_plugin__s2member::user_access_level($user_id);
        
        // Map s2Member levels to Twitch levels
        $level_mapping = $this->membership_settings['s2member_level_mapping'] ?? array();
        
        return $level_mapping[$level] ?? 'basic';
    }
    
    /**
     * Get upgrade URL
     */
    private function get_upgrade_url($target_level = 'basic') {
        $upgrade_urls = $this->membership_settings['upgrade_urls'] ?? array();
        
        if (isset($upgrade_urls[$target_level])) {
            return $upgrade_urls[$target_level];
        }
        
        // Fallback to default upgrade page
        return get_permalink($this->membership_settings['upgrade_page_id'] ?? 0) ?: home_url('/membership/');
    }
    
    /**
     * Add membership head content
     */
    public function add_membership_head_content() {
        if (is_user_logged_in()) {
            $user_level = $this->get_current_membership_level();
            echo '<meta name="twitch-membership-level" content="' . esc_attr($user_level) . '">';
        }
    }
    
    /**
     * Restrict content based on membership
     */
    public function restrict_content_based_on_membership($content) {
        if (!is_user_logged_in()) {
            return $this->add_restriction_notice($content, 'free');
        }
        
        $user_level = $this->get_current_membership_level();
        
        // Check for membership-specific content restrictions
        if (has_shortcode($content, 'spswifter_twitch_membership_required')) {
            // Let the shortcode handle the restriction
            return $content;
        }
        
        // Add membership badges to content
        if ($user_level !== 'free') {
            $content = $this->add_membership_badges_to_content($content, $user_level);
        }
        
        return $content;
    }
    
    /**
     * Filter stream content for members
     */
    public function filter_stream_content_for_members($content) {
        $user_level = $this->get_current_membership_level();
        
        // Add membership-specific content
        if ($user_level === 'vip') {
            $content .= $this->get_vip_stream_content();
        } elseif ($user_level === 'premium') {
            $content .= $this->get_premium_stream_content();
        } elseif ($user_level === 'basic') {
            $content .= $this->get_basic_stream_content();
        }
        
        return $content;
    }
    
    /**
     * Check stream access
     */
    public function check_stream_access() {
        $user_level = $this->get_current_membership_level();
        
        // Check if user has access to current stream
        if (!$this->user_has_stream_access($user_level)) {
            do_action('spswifter_twitch_stream_access_denied', $user_level);
            wp_die('Access denied. Membership required.');
        }
    }
    
    /**
     * Show membership upgrade prompt
     */
    public function show_membership_upgrade_prompt($current_level) {
        $upgrade_url = $this->get_upgrade_url();
        
        echo '<div class="twitch-membership-upgrade-prompt">';
        echo '<h2>Membership Required</h2>';
        echo '<p>Your current membership level (' . esc_html($current_level) . ') does not include access to this content.</p>';
        echo '<a href="' . esc_url($upgrade_url) . '" class="twitch-upgrade-btn">Upgrade Your Membership</a>';
        echo '</div>';
    }
    
    /**
     * Handle membership AJAX
     */
    public function handle_membership_ajax() {
        check_ajax_referer('spswifter_twitch_membership_nonce', 'nonce');
        
        $action = wp_unslash($_POST['membership_action']) ?? '';
        
        switch ($action) {
            case 'check_access':
                $this->check_membership_access_ajax();
                break;
            case 'get_level':
                $this->get_membership_level_ajax();
                break;
            case 'upgrade_info':
                $this->get_upgrade_info_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Check membership access AJAX
     */
    private function check_membership_access_ajax() {
        $content_type = sanitize_text_field(wp_unslash($_POST['content_type']) ?? '');
        $user_level = $this->get_current_membership_level();
        
        $has_access = $this->user_has_content_access($content_type, $user_level);
        
        wp_send_json_success(array(
            'has_access' => $has_access,
            'user_level' => $user_level,
            'upgrade_url' => $has_access ? null : $this->get_upgrade_url()
        ));
    }
    
    /**
     * Get membership level AJAX
     */
    private function get_membership_level_ajax() {
        $user_id = intval(wp_unslash($_POST['user_id']) ?? get_current_user_id());
        $level = $this->get_user_membership_level($user_id);
        
        wp_send_json_success(array(
            'level' => $level,
            'level_info' => $this->spswifter_twitch_membership_levels[$level] ?? null
        ));
    }
    
    /**
     * Get upgrade info AJAX
     */
    private function get_upgrade_info_ajax() {
        $target_level = sanitize_text_field(wp_unslash($_POST['target_level']) ?? 'basic');
        $current_level = $this->get_current_membership_level();
        
        wp_send_json_success(array(
            'current_level' => $current_level,
            'target_level' => $target_level,
            'target_info' => $this->spswifter_twitch_membership_levels[$target_level] ?? null,
            'upgrade_url' => $this->get_upgrade_url($target_level)
        ));
    }
    
    /**
     * Add membership settings menu
     */
    public function add_membership_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Membership Integration',
            'Membership',
            'manage_options',
            'twitch-membership-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Membership Integration Settings</h1>
            
            <div class="twitch-membership-status">
                <h3>Detected Membership Plugins</h3>
                <div class="twitch-plugin-status">
                    <?php foreach ($this->supported_plugins as $key => $plugin): ?>
                        <div class="twitch-plugin-item <?php echo $plugin['active'] ? 'active' : 'inactive'; ?>">
                            <span class="plugin-status-icon"><?php echo $plugin['active'] ? '✓' : '✗'; ?></span>
                            <span class="plugin-name"><?php echo esc_html($plugin['name']); ?></span>
                            <span class="plugin-status"><?php echo $plugin['active'] ? 'Active' : 'Not Active'; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_membership_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_membership_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Membership Integration</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_membership_settings[enabled]" 
                                   <?php checked($this->membership_settings['enabled'], true); ?> />
                            <label>Enable Twitch integration with membership plugins</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Default Access Level</th>
                        <td>
                            <select name="spswifter_twitch_membership_settings[default_level]">
                                <?php foreach ($this->spswifter_twitch_membership_levels as $level_key => $level): ?>
                                    <option value="<?php echo esc_attr($level_key); ?>" 
                                            <?php selected($this->membership_settings['default_level'], $level_key); ?>>
                                        <?php echo esc_html($level['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Upgrade Page</th>
                        <td>
                            <?php
                            wp_dropdown_pages(array(
                                'name' => 'spswifter_twitch_membership_settings[upgrade_page_id]',
                                'show_option_none' => 'Select a page',
                                'selected' => $this->membership_settings['upgrade_page_id'] ?? 0
                            ));
                            ?>
                            <p class="description">Page where users can upgrade their membership</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Membership Badges</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_membership_settings[show_badges]" 
                                   <?php checked($this->membership_settings['show_badges'], true); ?> />
                            <label>Show membership badges in chat and user profiles</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Restrict VOD Access</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_membership_settings[restrict_vods]" 
                                   <?php checked($this->membership_settings['restrict_vods'], false); ?> />
                            <label>Restrict VOD access based on membership level</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Restrict Chat Access</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_membership_settings[restrict_chat]" 
                                   <?php checked($this->membership_settings['restrict_chat'], false); ?> />
                            <label>Restrict chat access based on membership level</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get membership settings
     */
    private function get_membership_settings() {
        return get_option('spswifter_twitch_membership_settings', array(
            'enabled' => true,
            'default_level' => 'free',
            'upgrade_page_id' => 0,
            'show_badges' => true,
            'restrict_vods' => false,
            'restrict_chat' => false,
            'upgrade_urls' => array(),
            'memberpress_level_mapping' => array(),
            'rcp_level_mapping' => array(),
            'pmpro_level_mapping' => array(),
            'wc_level_mapping' => array(),
            'um_level_mapping' => array(),
            's2member_level_mapping' => array()
        ));
    }
    
    /**
     * Enqueue membership scripts
     */
    public function enqueue_membership_scripts() {
        if (empty($this->active_plugins)) {
            return;
        }
        
        wp_enqueue_style(
            'twitch-membership',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/membership-integration.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-membership',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/membership-integration.js',
            array('jquery'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-membership', 'twitchMembership', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_membership_nonce'),
            'enabled' => $this->membership_settings['enabled'] ?? true,
            'userLevel' => $this->get_current_membership_level(),
            'activePlugins' => array_keys($this->active_plugins),
            'membershipLevels' => $this->spswifter_twitch_membership_levels
        ));
    }
    
    /**
     * Helper methods
     */
    private function user_has_stream_access($level) {
        return in_array('basic_stream', $this->spswifter_twitch_membership_levels[$level]['access'] ?? array());
    }
    
    private function user_has_content_access($content_type, $level) {
        $access = $this->spswifter_twitch_membership_levels[$level]['access'] ?? array();
        return in_array($content_type, $access);
    }
    
    private function add_restriction_notice($content, $required_level) {
        $upgrade_url = $this->get_upgrade_url($required_level);
        return '<div class="twitch-membership-restricted">' .
               '<p>This content requires a ' . esc_html($required_level) . ' membership to view.</p>' .
               '<a href="' . esc_url($upgrade_url) . '" class="twitch-upgrade-btn">Upgrade Now</a>' .
               '</div>' . $content;
    }
    
    private function add_membership_badges_to_content($content, $level) {
        if (!($this->membership_settings['show_badges'] ?? true)) {
            return $content;
        }
        
        $badge = do_shortcode('[spswifter_twitch_membership_badge]');
        return $badge . $content;
    }
    
    private function get_vip_stream_content() {
        return '<div class="twitch-vip-content">👑 VIP Content: Exclusive stream features and access!</div>';
    }
    
    private function get_premium_stream_content() {
        return '<div class="twitch-premium-content">⭐ Premium Content: Enhanced stream experience!</div>';
    }
    
    private function get_basic_stream_content() {
        return '<div class="twitch-basic-content">👍 Basic Content: Standard stream access!</div>';
    }
}

// Initialize membership integration
new SPSWIFTER_Twitch_Membership_Integration();
