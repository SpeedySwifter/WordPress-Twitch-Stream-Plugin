<?php
/**
 * Donation Integration for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Donation_Integration {
    
    private $donation_settings;
    
    public function __construct() {
        $this->donation_settings = $this->get_donation_settings();
        
        add_action('init', array($this, 'register_donation_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_donation_scripts'));
        add_action('admin_menu', array($this, 'add_donation_settings_menu'));
        add_action('wp_ajax_spswifter_twitch_donation_settings', array($this, 'handle_donation_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_donation_settings', array($this, 'handle_donation_ajax'));
    }
    
    /**
     * Register donation shortcodes
     */
    public function register_donation_shortcodes() {
        add_shortcode('spswifter_twitch_donations', array($this, 'render_donations_shortcode'));
        add_shortcode('spswifter_twitch_donation_button', array($this, 'render_donation_button_shortcode'));
        add_shortcode('spswifter_twitch_donation_goal', array($this, 'render_donation_goal_shortcode'));
    }
    
    /**
     * Enqueue donation scripts
     */
    public function enqueue_donation_scripts() {
        wp_enqueue_style(
            'twitch-donations',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/donations.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-donations',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/donations.js',
            array('jquery'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-donations', 'twitchDonations', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_donations_nonce'),
            'currency' => $this->donation_settings['currency'] ?? 'EUR',
            'thankYouMessage' => $this->donation_settings['thank_you_message'] ?? 'Vielen Dank für Ihre Spende!',
        ));
    }
    
    /**
     * Add donation settings menu
     */
    public function add_donation_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Donation Settings',
            'Donations',
            'manage_options',
            'twitch-donation-settings',
            array($this, 'render_donation_settings_page')
        );
    }
    
    /**
     * Render donations shortcode
     */
    public function render_donations_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'grid',
            'show_goal' => 'true',
            'show_stats' => 'true',
            'layout' => 'horizontal',
        ), $atts);
        
        if (!$this->are_donations_enabled()) {
            return '<p class="twitch-donation-disabled">Spenden sind derzeit deaktiviert.</p>';
        }
        
        ob_start();
        ?>
        <div class="twitch-donations-container twitch-donation-<?php echo esc_attr($atts['style']); ?> twitch-donation-<?php echo esc_attr($atts['layout']); ?>">
            
            <?php if ($atts['show_goal'] === 'true' && !empty($this->donation_settings['goal']['amount'])): ?>
                <div class="twitch-donation-goal">
                    <?php echo $this->render_donation_goal(); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_stats'] === 'true'): ?>
                <div class="twitch-donation-stats">
                    <?php echo $this->render_donation_stats(); ?>
                </div>
            <?php endif; ?>
            
            <div class="twitch-donation-buttons">
                <?php if ($this->is_bmc_enabled()): ?>
                    <div class="twitch-donation-button twitch-bmc-button">
                        <?php echo $this->render_bmc_button(); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->is_paypal_enabled()): ?>
                    <div class="twitch-donation-button twitch-paypal-button">
                        <?php echo $this->render_paypal_button(); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($this->donation_settings['custom_message'])): ?>
                <div class="twitch-donation-message">
                    <?php echo wp_kses_post($this->donation_settings['custom_message']); ?>
                </div>
            <?php endif; ?>
            
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render donation button shortcode
     */
    public function render_donation_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'both', // bmc, paypal, both
            'size' => 'medium', // small, medium, large
            'text' => '',
            'style' => 'default', // default, custom
        ), $atts);
        
        if (!$this->are_donations_enabled()) {
            return '';
        }
        
        $buttons = '';
        
        if ($atts['type'] === 'bmc' || $atts['type'] === 'both') {
            if ($this->is_bmc_enabled()) {
                $buttons .= $this->render_bmc_button($atts['size'], $atts['text']);
            }
        }
        
        if ($atts['type'] === 'paypal' || $atts['type'] === 'both') {
            if ($this->is_paypal_enabled()) {
                $buttons .= $this->render_paypal_button($atts['size'], $atts['text']);
            }
        }
        
        return $buttons;
    }
    
    /**
     * Render donation goal shortcode
     */
    public function render_donation_goal_shortcode($atts) {
        $atts = shortcode_atts(array(
            'amount' => '',
            'currency' => $this->donation_settings['currency'] ?? 'EUR',
            'description' => '',
            'show_progress' => 'true',
            'show_percentage' => 'true',
        ), $atts);
        
        if (!$this->are_donations_enabled()) {
            return '';
        }
        
        $goal_amount = !empty($atts['amount']) ? $atts['amount'] : ($this->donation_settings['goal']['amount'] ?? 0);
        $goal_description = !empty($atts['description']) ? $atts['description'] : ($this->donation_settings['goal']['description'] ?? '');
        
        if (empty($goal_amount)) {
            return '';
        }
        
        $current_amount = $this->get_current_donation_amount();
        $percentage = ($current_amount / $goal_amount) * 100;
        
        ob_start();
        ?>
        <div class="twitch-donation-goal-widget">
            <div class="twitch-donation-goal-header">
                <h3>Spendenziel</h3>
                <?php if (!empty($goal_description)): ?>
                    <p><?php echo esc_html($goal_description); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="twitch-donation-goal-progress">
                <div class="twitch-donation-goal-bar">
                    <div class="twitch-donation-goal-fill" style="width: <?php echo min($percentage, 100); ?>%"></div>
                </div>
                
                <div class="twitch-donation-goal-stats">
                    <div class="twitch-donation-goal-current">
                        <span class="twitch-donation-amount"><?php echo $this->format_currency($current_amount); ?></span>
                    </div>
                    <div class="twitch-donation-goal-target">
                        <span class="twitch-donation-amount"><?php echo $this->format_currency($goal_amount); ?></span>
                    </div>
                    <?php if ($atts['show_percentage'] === 'true'): ?>
                        <div class="twitch-donation-goal-percentage">
                            <?php echo round($percentage, 1); ?>%
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render BMC button
     */
    private function render_bmc_button($size = 'medium', $custom_text = '') {
        $bmc_username = $this->donation_settings['bmc']['username'] ?? '';
        $bmc_color = $this->donation_settings['bmc']['color'] ?? '#FFDD00';
        $bmc_text = !empty($custom_text) ? $custom_text : ($this->donation_settings['bmc']['button_text'] ?? 'Buy me a coffee');
        
        if (empty($bmc_username)) {
            return '';
        }
        
        $size_classes = array(
            'small' => 'bmc-small',
            'medium' => 'bmc-medium',
            'large' => 'bmc-large',
        );
        
        $size_class = $size_classes[$size] ?? 'bmc-medium';
        
        ob_start();
        ?>
        <a href="https://www.buymeacoffee.com/<?php echo esc_attr($bmc_username); ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="twitch-bmc-link <?php echo esc_attr($size_class); ?>">
            <img src="<?php echo esc_url(SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/images/bmc-logo.svg'); ?>" 
                 alt="Buy Me A Coffee" 
                 style="height: 30px; width: auto; margin-right: 8px;">
            <span style="color: #000000; font-size: 16px; font-weight: 600;"><?php echo esc_html($bmc_text); ?></span>
        </a>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render PayPal button
     */
    private function render_paypal_button($size = 'medium', $custom_text = '') {
        $paypal_email = $this->donation_settings['paypal']['email'] ?? '';
        $paypal_currency = $this->donation_settings['paypal']['currency'] ?? 'EUR';
        $paypal_text = !empty($custom_text) ? $custom_text : ($this->donation_settings['paypal']['button_text'] ?? 'Spenden');
        
        if (empty($paypal_email)) {
            return '';
        }
        
        $size_classes = array(
            'small' => 'paypal-small',
            'medium' => 'paypal-medium',
            'large' => 'paypal-large',
        );
        
        $size_class = $size_classes[$size] ?? 'paypal-medium';
        
        ob_start();
        ?>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="twitch-paypal-form <?php echo esc_attr($size_class); ?>">
            <input type="hidden" name="cmd" value="_donations">
            <input type="hidden" name="business" value="<?php echo esc_attr($paypal_email); ?>">
            <input type="hidden" name="currency_code" value="<?php echo esc_attr($paypal_currency); ?>">
            <input type="hidden" name="item_name" value="Twitch Stream Plugin Donation">
            <input type="hidden" name="bn" value="SPSWIFTER_Twitch_Plugin_Donation">
            
            <button type="submit" class="twitch-paypal-button">
                <img src="<?php echo esc_url(SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/images/paypal-logo.png'); ?>" 
                     alt="PayPal" 
                     style="height: 24px; width: auto; margin-right: 8px;">
                <span><?php echo esc_html($paypal_text); ?></span>
            </button>
        </form>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render donation goal
     */
    private function render_donation_goal() {
        $goal_amount = $this->donation_settings['goal']['amount'] ?? 0;
        $goal_description = $this->donation_settings['goal']['description'] ?? '';
        
        if (empty($goal_amount)) {
            return '';
        }
        
        $current_amount = $this->get_current_donation_amount();
        $percentage = ($current_amount / $goal_amount) * 100;
        
        ob_start();
        ?>
        <div class="twitch-donation-goal-progress">
            <div class="twitch-donation-goal-info">
                <h4>Spendenziel</h4>
                <?php if (!empty($goal_description)): ?>
                    <p><?php echo esc_html($goal_description); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="twitch-donation-goal-bar">
                <div class="twitch-donation-goal-fill" style="width: <?php echo min($percentage, 100); ?>%"></div>
            </div>
            
            <div class="twitch-donation-goal-amounts">
                <span class="current"><?php echo $this->format_currency($current_amount); ?></span>
                <span class="separator">/</span>
                <span class="target"><?php echo $this->format_currency($goal_amount); ?></span>
                <span class="percentage">(<?php echo round($percentage, 1); ?>%)</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render donation stats
     */
    private function render_donation_stats() {
        $stats = $this->get_donation_stats();
        
        ob_start();
        ?>
        <div class="twitch-donation-stats-grid">
            <div class="twitch-donation-stat">
                <div class="twitch-donation-stat-number"><?php echo $stats['total_donations']; ?></div>
                <div class="twitch-donation-stat-label">Spenden</div>
            </div>
            <div class="twitch-donation-stat">
                <div class="twitch-donation-stat-number"><?php echo $this->format_currency($stats['total_amount']); ?></div>
                <div class="twitch-donation-stat-label">Gesamtbetrag</div>
            </div>
            <div class="twitch-donation-stat">
                <div class="twitch-donation-stat-number"><?php echo $this->format_currency($stats['average_amount']); ?></div>
                <div class="twitch-donation-stat-label">Durchschnitt</div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render donation settings page
     */
    public function render_donation_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Donation Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_donation_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_donation_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Donations</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_donation_settings[enabled]" <?php checked($this->donation_settings['enabled'], true); ?> />
                            <label>Enable donation buttons and features</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Currency</th>
                        <td>
                            <select name="spswifter_twitch_donation_settings[currency]">
                                <option value="EUR" <?php selected($this->donation_settings['currency'], 'EUR'); ?>>EUR (€)</option>
                                <option value="USD" <?php selected($this->donation_settings['currency'], 'USD'); ?>>USD ($)</option>
                                <option value="GBP" <?php selected($this->donation_settings['currency'], 'GBP'); ?>>GBP (£)</option>
                                <option value="CHF" <?php selected($this->donation_settings['currency'], 'CHF'); ?>>CHF (Fr)</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Custom Message</th>
                        <td>
                            <textarea name="spswifter_twitch_donation_settings[custom_message]" rows="3" class="large-text"><?php echo esc_textarea($this->donation_settings['custom_message'] ?? ''); ?></textarea>
                            <p class="description">HTML allowed. Displayed below donation buttons.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Thank You Message</th>
                        <td>
                            <input type="text" name="spswifter_twitch_donation_settings[thank_you_message]" value="<?php echo esc_attr($this->donation_settings['thank_you_message'] ?? ''); ?>" class="regular-text" />
                            <p class="description">Message shown after successful donation.</p>
                        </td>
                    </tr>
                </table>
                
                <h2>Buy Me a Coffee Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable BMC</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_donation_settings[bmc][enabled]" <?php checked($this->donation_settings['bmc']['enabled'], true); ?> />
                            <label>Enable Buy Me a Coffee button</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">BMC Username</th>
                        <td>
                            <input type="text" name="spswifter_twitch_donation_settings[bmc][username]" value="<?php echo esc_attr($this->donation_settings['bmc']['username'] ?? ''); ?>" class="regular-text" />
                            <p class="description">Your Buy Me a Coffee username (without @)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Button Text</th>
                        <td>
                            <input type="text" name="spswifter_twitch_donation_settings[bmc][button_text]" value="<?php echo esc_attr($this->donation_settings['bmc']['button_text'] ?? 'Buy me a coffee'); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <h2>PayPal Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable PayPal</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_donation_settings[paypal][enabled]" <?php checked($this->donation_settings['paypal']['enabled'], true); ?> />
                            <label>Enable PayPal donation button</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">PayPal Email</th>
                        <td>
                            <input type="email" name="spswifter_twitch_donation_settings[paypal][email]" value="<?php echo esc_attr($this->donation_settings['paypal']['email'] ?? ''); ?>" class="regular-text" />
                            <p class="description">Your PayPal email address for donations</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">PayPal Currency</th>
                        <td>
                            <select name="spswifter_twitch_donation_settings[paypal][currency]">
                                <option value="EUR" <?php selected($this->donation_settings['paypal']['currency'], 'EUR'); ?>>EUR (€)</option>
                                <option value="USD" <?php selected($this->donation_settings['paypal']['currency'], 'USD'); ?>>USD ($)</option>
                                <option value="GBP" <?php selected($this->donation_settings['paypal']['currency'], 'GBP'); ?>>GBP (£)</option>
                                <option value="CHF" <?php selected($this->donation_settings['paypal']['currency'], 'CHF'); ?>>CHF (Fr)</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Button Text</th>
                        <td>
                            <input type="text" name="spswifter_twitch_donation_settings[paypal][button_text]" value="<?php echo esc_attr($this->donation_settings['paypal']['button_text'] ?? 'Spenden'); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <h2>Donation Goal</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Goal</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_donation_settings[goal][enabled]" <?php checked($this->donation_settings['goal']['enabled'], true); ?> />
                            <label>Show donation goal progress</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Goal Amount</th>
                        <td>
                            <input type="number" name="spswifter_twitch_donation_settings[goal][amount]" value="<?php echo esc_attr($this->donation_settings['goal']['amount'] ?? ''); ?>" step="0.01" min="0" class="regular-text" />
                            <p class="description">Your donation goal amount</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Goal Description</th>
                        <td>
                            <input type="text" name="spswifter_twitch_donation_settings[goal][description]" value="<?php echo esc_attr($this->donation_settings['goal']['description'] ?? ''); ?>" class="regular-text" />
                            <p class="description">Description of your donation goal</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Handle donation AJAX
     */
    public function handle_donation_ajax() {
        check_ajax_referer('spswifter_twitch_donations_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $action = wp_unslash($_POST['donation_action']) ?? '';
        
        switch ($action) {
            case 'get_stats':
                $this->get_donation_stats_ajax();
                break;
            case 'record_donation':
                $this->record_donation_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get donation stats AJAX
     */
    private function get_donation_stats_ajax() {
        $stats = $this->get_donation_stats();
        
        wp_send_json_success(array('stats' => $stats));
    }
    
    /**
     * Record donation AJAX
     */
    private function record_donation_ajax() {
        $amount = floatval(wp_unslash($_POST['amount']) ?? 0);
        $type = sanitize_text_field(wp_unslash($_POST['type']) ?? '');
        $message = sanitize_text_field(wp_unslash($_POST['message']) ?? '');
        
        if ($amount <= 0) {
            wp_send_json_error('Invalid amount');
        }
        
        $this->record_donation($amount, $type, $message);
        
        wp_send_json_success(array('message' => 'Donation recorded successfully'));
    }
    
    /**
     * Check if donations are enabled
     */
    private function are_donations_enabled() {
        return $this->donation_settings['enabled'] ?? false;
    }
    
    /**
     * Check if BMC is enabled
     */
    private function is_bmc_enabled() {
        return ($this->donation_settings['bmc']['enabled'] ?? false) && !empty($this->donation_settings['bmc']['username']);
    }
    
    /**
     * Check if PayPal is enabled
     */
    private function is_paypal_enabled() {
        return ($this->donation_settings['paypal']['enabled'] ?? false) && !empty($this->donation_settings['paypal']['email']);
    }
    
    /**
     * Get current donation amount
     */
    private function get_current_donation_amount() {
        $donations = get_option('spswifter_twitch_donations', array());
        return array_sum(array_column($donations, 'amount'));
    }
    
    /**
     * Get donation stats
     */
    private function get_donation_stats() {
        $donations = get_option('spswifter_twitch_donations', array());
        
        if (empty($donations)) {
            return array(
                'total_donations' => 0,
                'total_amount' => 0,
                'average_amount' => 0,
                'recent_donations' => array(),
            );
        }
        
        $total_amount = array_sum(array_column($donations, 'amount'));
        $average_amount = count($donations) > 0 ? $total_amount / count($donations) : 0;
        
        // Get recent donations (last 10)
        $recent_donations = array_slice($donations, -10);
        $recent_donations = array_reverse($recent_donations);
        
        return array(
            'total_donations' => count($donations),
            'total_amount' => $total_amount,
            'average_amount' => $average_amount,
            'recent_donations' => $recent_donations,
        );
    }
    
    /**
     * Record donation
     */
    private function record_donation($amount, $type, $message = '') {
        $donations = get_option('spswifter_twitch_donations', array());
        
        $donations[] = array(
            'amount' => $amount,
            'type' => $type,
            'message' => $message,
            'date' => current_time('mysql'),
            'currency' => $this->donation_settings['currency'] ?? 'EUR',
        );
        
        update_option('spswifter_twitch_donations', $donations);
        
        // Trigger action for custom handling
        do_action('spswifter_twitch_donation_recorded', $amount, $type, $message);
    }
    
    /**
     * Format currency
     */
    private function format_currency($amount) {
        $currency = $this->donation_settings['currency'] ?? 'EUR';
        
        $symbols = array(
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'CHF' => 'Fr',
        );
        
        $symbol = $symbols[$currency] ?? $currency;
        
        return $symbol . number_format($amount, 2, ',', '.');
    }
    
    /**
     * Get donation settings
     */
    private function get_donation_settings() {
        return get_option('spswifter_twitch_donation_settings', array(
            'enabled' => false,
            'currency' => 'EUR',
            'custom_message' => '',
            'thank_you_message' => 'Vielen Dank für Ihre Spende!',
            'bmc' => array(
                'enabled' => false,
                'username' => '',
                'button_text' => 'Buy me a coffee',
                'color' => '#FFDD00',
            ),
            'paypal' => array(
                'enabled' => false,
                'email' => '',
                'currency' => 'EUR',
                'button_text' => 'Spenden',
            ),
            'goal' => array(
                'enabled' => false,
                'amount' => 0,
                'description' => '',
            ),
        ));
    }
}

// Initialize donation integration
new SPSWIFTER_Twitch_Donation_Integration();
