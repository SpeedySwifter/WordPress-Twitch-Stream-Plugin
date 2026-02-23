<?php
/**
 * WooCommerce Integration for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    return;
}

class SPSWIFTER_Twitch_WooCommerce_Integration {
    
    private $woo_settings;
    private $spswifter_twitch_api;
    
    public function __construct() {
        $this->woo_settings = $this->get_woo_settings();
        $this->spswifter_twitch_api = new SPSWIFTER_Twitch_API();
        
        add_action('init', array($this, 'init_woocommerce_integration'));
        add_action('plugins_loaded', array($this, 'load_woocommerce_hooks'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_woocommerce_scripts'));
        add_action('wp_ajax_spswifter_twitch_woo_product', array($this, 'handle_woo_product_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_woo_product', array($this, 'handle_woo_product_ajax'));
        add_action('admin_menu', array($this, 'add_woo_settings_menu'));
        
        // WooCommerce hooks
        add_action('woocommerce_before_single_product', array($this, 'add_spswifter_twitch_product_info'));
        add_action('woocommerce_after_single_product_summary', array($this, 'add_spswifter_twitch_stream_button'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_spswifter_twitch_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_spswifter_twitch_product_fields'));
        add_action('woocommerce_before_cart', array($this, 'add_spswifter_twitch_cart_notice'));
        add_action('woocommerce_before_checkout_form', array($this, 'add_spswifter_twitch_checkout_notice'));
        add_action('woocommerce_thankyou', array($this, 'add_spswifter_twitch_order_notice'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_woo_shortcodes'));
    }
    
    /**
     * Initialize WooCommerce integration
     */
    public function init_woocommerce_integration() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Add Twitch product type
        $this->register_spswifter_twitch_product_type();
        
        // Add custom product tabs
        add_filter('woocommerce_product_tabs', array($this, 'add_spswifter_twitch_product_tabs'));
        
        // Add custom cart item data
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_spswifter_twitch_cart_item_data'), 10, 2);
        
        // Add custom order item meta
        add_action('woocommerce_add_order_item_meta', array($this, 'add_spswifter_twitch_order_item_meta'), 10, 3);
        
        // Display custom cart item data
        add_filter('woocommerce_get_item_data', array($this, 'display_spswifter_twitch_cart_item_data'), 10, 2);
        
        // Display custom order item meta
        add_action('woocommerce_order_item_meta_end', array($this, 'display_spswifter_twitch_order_item_meta'), 10, 3);
    }
    
    /**
     * Load WooCommerce hooks
     */
    public function load_woocommerce_hooks() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Additional WooCommerce-specific hooks
        add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'customize_add_to_cart_text'));
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'customize_loop_add_to_cart_text'));
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'customize_loop_add_to_cart_link'), 10, 2);
    }
    
    /**
     * Register Twitch product type
     */
    private function register_spswifter_twitch_product_type() {
        // This would register a custom product type for Twitch-related products
        // For now, we'll use existing product types with custom fields
    }
    
    /**
     * Register WooCommerce shortcodes
     */
    public function register_woo_shortcodes() {
        add_shortcode('spswifter_twitch_woo_products', array($this, 'render_woo_products_shortcode'));
        add_shortcode('spswifter_twitch_woo_product', array($this, 'render_woo_product_shortcode'));
        add_shortcode('spswifter_twitch_woo_cart', array($this, 'render_woo_cart_shortcode'));
        add_shortcode('spswifter_twitch_woo_checkout', array($this, 'render_woo_checkout_shortcode'));
    }
    
    /**
     * Render WooCommerce products shortcode
     */
    public function render_woo_products_shortcode($atts) {
        if (!class_exists('WooCommerce')) {
            return '<p class="twitch-woo-error">WooCommerce is not active</p>';
        }
        
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => '12',
            'columns' => '4',
            'orderby' => 'date',
            'order' => 'DESC',
            'spswifter_twitch_only' => 'false',
            'show_stream' => 'true',
            'show_price' => 'true',
            'show_add_to_cart' => 'true',
        ), $atts);
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $atts['category'],
                ),
            );
        }
        
        if ($atts['spswifter_twitch_only'] === 'true') {
            $args['meta_query'] = array(
                array(
                    'key' => '_spswifter_twitch_enabled',
                    'value' => 'yes',
                ),
            );
        }
        
        $products = get_posts($args);
        
        if (empty($products)) {
            return '<p class="twitch-woo-no-products">No products found</p>';
        }
        
        ob_start();
        ?>
        <div class="twitch-woo-products twitch-woo-columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($products as $product_post): ?>
                <?php
                $product = wc_get_product($product_post->ID);
                $spswifter_twitch_enabled = get_post_meta($product_post->ID, '_spswifter_twitch_enabled', true) === 'yes';
                $spswifter_twitch_channel = get_post_meta($product_post->ID, '_spswifter_twitch_channel', true);
                $spswifter_twitch_stream_url = get_post_meta($product_post->ID, '_spswifter_twitch_stream_url', true);
                ?>
                <div class="twitch-woo-product">
                    <div class="twitch-woo-product-image">
                        <?php echo $product->get_image(); ?>
                        <?php if ($spswifter_twitch_enabled && $atts['show_stream'] === 'true' && $spswifter_twitch_stream_url): ?>
                            <div class="twitch-woo-stream-badge">
                                <span class="twitch-badge-icon">🔴</span>
                                <span class="twitch-badge-text">Live</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="twitch-woo-product-content">
                        <h3 class="twitch-woo-product-title">
                            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </a>
                        </h3>
                        
                        <?php if ($atts['show_price'] === 'true'): ?>
                            <div class="twitch-woo-product-price">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($spswifter_twitch_enabled && $atts['show_stream'] === 'true' && $spswifter_twitch_channel): ?>
                            <div class="twitch-woo-product-stream">
                                <span class="twitch-stream-info">
                                    🎮 <?php echo esc_html($spswifter_twitch_channel); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_add_to_cart'] === 'true'): ?>
                            <div class="twitch-woo-product-actions">
                                <?php woocommerce_template_loop_add_to_cart(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WooCommerce product shortcode
     */
    public function render_woo_product_shortcode($atts) {
        if (!class_exists('WooCommerce')) {
            return '<p class="twitch-woo-error">WooCommerce is not active</p>';
        }
        
        $atts = shortcode_atts(array(
            'id' => '',
            'show_stream' => 'true',
            'show_price' => 'true',
            'show_add_to_cart' => 'true',
            'show_description' => 'true',
        ), $atts);
        
        if (empty($atts['id'])) {
            return '<p class="twitch-woo-error">Product ID is required</p>';
        }
        
        $product = wc_get_product(intval($atts['id']));
        
        if (!$product) {
            return '<p class="twitch-woo-error">Product not found</p>';
        }
        
        $spswifter_twitch_enabled = get_post_meta($atts['id'], '_spswifter_twitch_enabled', true) === 'yes';
        $spswifter_twitch_channel = get_post_meta($atts['id'], '_spswifter_twitch_channel', true);
        $spswifter_twitch_stream_url = get_post_meta($atts['id'], '_spswifter_twitch_stream_url', true);
        
        ob_start();
        ?>
        <div class="twitch-woo-product-single">
            <div class="twitch-woo-product-header">
                <div class="twitch-woo-product-image">
                    <?php echo $product->get_image('woocommerce_single'); ?>
                    <?php if ($spswifter_twitch_enabled && $atts['show_stream'] === 'true' && $spswifter_twitch_stream_url): ?>
                        <div class="twitch-woo-stream-badge">
                            <span class="twitch-badge-icon">🔴</span>
                            <span class="twitch-badge-text">Live</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="twitch-woo-product-info">
                    <h2 class="twitch-woo-product-title"><?php echo esc_html($product->get_name()); ?></h2>
                    
                    <?php if ($atts['show_price'] === 'true'): ?>
                        <div class="twitch-woo-product-price">
                            <?php echo $product->get_price_html(); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($spswifter_twitch_enabled && $atts['show_stream'] === 'true' && $spswifter_twitch_channel): ?>
                        <div class="twitch-woo-product-stream">
                            <span class="twitch-stream-info">
                                🎮 <?php echo esc_html($spswifter_twitch_channel); ?>
                            </span>
                            <?php if ($spswifter_twitch_stream_url): ?>
                                <a href="<?php echo esc_url($spswifter_twitch_stream_url); ?>" class="twitch-stream-link" target="_blank">
                                    Watch Stream
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_add_to_cart'] === 'true'): ?>
                        <div class="twitch-woo-product-actions">
                            <?php woocommerce_template_single_add_to_cart(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($atts['show_description'] === 'true'): ?>
                <div class="twitch-woo-product-description">
                    <h3>Description</h3>
                    <?php echo $product->get_description(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WooCommerce cart shortcode
     */
    public function render_woo_cart_shortcode($atts) {
        if (!class_exists('WooCommerce')) {
            return '<p class="twitch-woo-error">WooCommerce is not active</p>';
        }
        
        $atts = shortcode_atts(array(
            'show_spswifter_twitch_items' => 'true',
            'show_stream_notice' => 'true',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-woo-cart">
            <?php if ($atts['show_stream_notice'] === 'true' && $this->has_spswifter_twitch_products_in_cart()): ?>
                <div class="twitch-woo-cart-notice">
                    <div class="twitch-notice-content">
                        <span class="twitch-notice-icon">🎮</span>
                        <span class="twitch-notice-text">
                            You have Twitch-related products in your cart! 
                            Check out the stream while shopping.
                        </span>
                    </div>
                    <div class="twitch-notice-actions">
                        <a href="#" class="twitch-watch-stream-btn">Watch Stream</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php echo do_shortcode('[woocommerce_cart]'); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WooCommerce checkout shortcode
     */
    public function render_woo_checkout_shortcode($atts) {
        if (!class_exists('WooCommerce')) {
            return '<p class="twitch-woo-error">WooCommerce is not active</p>';
        }
        
        $atts = shortcode_atts(array(
            'show_spswifter_twitch_items' => 'true',
            'show_stream_notice' => 'true',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-woo-checkout">
            <?php if ($atts['show_stream_notice'] === 'true' && $this->has_spswifter_twitch_products_in_cart()): ?>
                <div class="twitch-woo-checkout-notice">
                    <div class="twitch-notice-content">
                        <span class="twitch-notice-icon">🎮</span>
                        <span class="twitch-notice-text">
                            Complete your purchase while watching the stream!
                        </span>
                    </div>
                    <div class="twitch-notice-actions">
                        <a href="#" class="twitch-watch-stream-btn">Watch Stream</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php echo do_shortcode('[woocommerce_checkout]'); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Add Twitch product tabs
     */
    public function add_spswifter_twitch_product_tabs($tabs) {
        global $product;
        
        $spswifter_twitch_enabled = get_post_meta($product->get_id(), '_spswifter_twitch_enabled', true) === 'yes';
        
        if ($spswifter_twitch_enabled) {
            $tabs['spswifter_twitch_stream'] = array(
                'title' => __('Twitch Stream', 'speedyswifter-stream-integrator-for-twitch'),
                'priority' => 50,
                'callback' => array($this, 'render_spswifter_twitch_stream_tab'),
            );
        }
        
        return $tabs;
    }
    
    /**
     * Render Twitch stream tab
     */
    public function render_spswifter_twitch_stream_tab() {
        global $product;
        
        $spswifter_twitch_channel = get_post_meta($product->get_id(), '_spswifter_twitch_channel', true);
        $spswifter_twitch_stream_url = get_post_meta($product->get_id(), '_spswifter_twitch_stream_url', true);
        $spswifter_twitch_chat_embed = get_post_meta($product->get_id(), '_spswifter_twitch_chat_embed', true);
        
        echo '<div class="twitch-stream-tab">';
        
        if ($spswifter_twitch_stream_url) {
            echo '<div class="twitch-stream-embed">';
            echo '<iframe src="' . esc_url($spswifter_twitch_stream_url) . '" frameborder="0" allowfullscreen></iframe>';
            echo '</div>';
        }
        
        if ($spswifter_twitch_chat_embed && $spswifter_twitch_channel) {
            echo '<div class="twitch-chat-embed">';
            echo '<iframe src="https://www.twitch.tv/embed/' . esc_attr($spswifter_twitch_channel) . '/chat" frameborder="0"></iframe>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Add Twitch product fields
     */
    public function add_spswifter_twitch_product_fields($product) {
        woocommerce_wp_text_input(array(
            'id' => '_spswifter_twitch_channel',
            'label' => __('Twitch Channel', 'speedyswifter-stream-integrator-for-twitch'),
            'placeholder' => 'channel_name',
            'desc_tip' => true,
            'description' => __('Enter the Twitch channel name associated with this product', 'speedyswifter-stream-integrator-for-twitch'),
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_spswifter_twitch_stream_url',
            'label' => __('Stream URL', 'speedyswifter-stream-integrator-for-twitch'),
            'placeholder' => 'https://www.twitch.tv/channel_name/embed',
            'desc_tip' => true,
            'description' => __('Enter the embed URL for the Twitch stream', 'speedyswifter-stream-integrator-for-twitch'),
        ));
        
        woocommerce_wp_checkbox(array(
            'id' => '_spswifter_twitch_enabled',
            'label' => __('Enable Twitch Integration', 'speedyswifter-stream-integrator-for-twitch'),
            'description' => __('Enable Twitch features for this product', 'speedyswifter-stream-integrator-for-twitch'),
        ));
        
        woocommerce_wp_checkbox(array(
            'id' => '_spswifter_twitch_chat_embed',
            'label' => __('Embed Chat', 'speedyswifter-stream-integrator-for-twitch'),
            'description' => __('Show embedded chat on product page', 'speedyswifter-stream-integrator-for-twitch'),
        ));
    }
    
    /**
     * Save Twitch product fields
     */
    public function save_spswifter_twitch_product_fields($post_id) {
        $spswifter_twitch_channel = isset(wp_unslash($_POST['_spswifter_twitch_channel'])) ? sanitize_text_field(wp_unslash($_POST['_spswifter_twitch_channel'])) : '';
        $spswifter_twitch_stream_url = isset(wp_unslash($_POST['_spswifter_twitch_stream_url'])) ? sanitize_url(wp_unslash($_POST['_spswifter_twitch_stream_url'])) : '';
        $spswifter_twitch_enabled = isset(wp_unslash($_POST['_spswifter_twitch_enabled'])) ? 'yes' : 'no';
        $spswifter_twitch_chat_embed = isset(wp_unslash($_POST['_spswifter_twitch_chat_embed'])) ? 'yes' : 'no';
        
        update_post_meta($post_id, '_spswifter_twitch_channel', $spswifter_twitch_channel);
        update_post_meta($post_id, '_spswifter_twitch_stream_url', $spswifter_twitch_stream_url);
        update_post_meta($post_id, '_spswifter_twitch_enabled', $spswifter_twitch_enabled);
        update_post_meta($post_id, '_spswifter_twitch_chat_embed', $spswifter_twitch_chat_embed);
    }
    
    /**
     * Add Twitch cart item data
     */
    public function add_spswifter_twitch_cart_item_data($cart_item_data, $product_id) {
        $spswifter_twitch_enabled = get_post_meta($product_id, '_spswifter_twitch_enabled', true) === 'yes';
        $spswifter_twitch_channel = get_post_meta($product_id, '_spswifter_twitch_channel', true);
        
        if ($spswifter_twitch_enabled) {
            $cart_item_data['spswifter_twitch_enabled'] = $spswifter_twitch_enabled;
            $cart_item_data['spswifter_twitch_channel'] = $spswifter_twitch_channel;
        }
        
        return $cart_item_data;
    }
    
    /**
     * Add Twitch order item meta
     */
    public function add_spswifter_twitch_order_item_meta($item_id, $values, $cart_item_key) {
        if (isset($values['spswifter_twitch_enabled'])) {
            wc_add_order_item_meta($item_id, '_spswifter_twitch_enabled', $values['spswifter_twitch_enabled']);
        }
        
        if (isset($values['spswifter_twitch_channel'])) {
            wc_add_order_item_meta($item_id, '_spswifter_twitch_channel', $values['spswifter_twitch_channel']);
        }
    }
    
    /**
     * Display Twitch cart item data
     */
    public function display_spswifter_twitch_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['spswifter_twitch_enabled']) && $cart_item['spswifter_twitch_enabled']) {
            $item_data[] = array(
                'key' => __('Twitch Channel', 'speedyswifter-stream-integrator-for-twitch'),
                'value' => $cart_item['spswifter_twitch_channel'],
            );
        }
        
        return $item_data;
    }
    
    /**
     * Display Twitch order item meta
     */
    public function display_spswifter_twitch_order_item_meta($item_id, $item, $order) {
        $spswifter_twitch_channel = $item->get_meta('_spswifter_twitch_channel');
        
        if ($spswifter_twitch_channel) {
            echo '<p><strong>' . __('Twitch Channel', 'speedyswifter-stream-integrator-for-twitch') . ':</strong> ' . esc_html($spswifter_twitch_channel) . '</p>';
        }
    }
    
    /**
     * Add Twitch product info
     */
    public function add_spswifter_twitch_product_info() {
        global $product;
        
        $spswifter_twitch_enabled = get_post_meta($product->get_id(), '_spswifter_twitch_enabled', true) === 'yes';
        
        if ($spswifter_twitch_enabled) {
            $spswifter_twitch_channel = get_post_meta($product->get_id(), '_spswifter_twitch_channel', true);
            $spswifter_twitch_stream_url = get_post_meta($product->get_id(), '_spswifter_twitch_stream_url', true);
            
            echo '<div class="twitch-product-info">';
            echo '<div class="twitch-product-header">';
            echo '<span class="twitch-product-badge">🎮 Twitch Product</span>';
            
            if ($spswifter_twitch_channel) {
                echo '<span class="twitch-product-channel">Channel: ' . esc_html($spswifter_twitch_channel) . '</span>';
            }
            
            echo '</div>';
            
            if ($spswifter_twitch_stream_url) {
                echo '<div class="twitch-product-stream">';
                echo '<iframe src="' . esc_url($spswifter_twitch_stream_url) . '" frameborder="0" allowfullscreen></iframe>';
                echo '</div>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Add Twitch stream button
     */
    public function add_spswifter_twitch_stream_button() {
        global $product;
        
        $spswifter_twitch_enabled = get_post_meta($product->get_id(), '_spswifter_twitch_enabled', true) === 'yes';
        $spswifter_twitch_channel = get_post_meta($product->get_id(), '_spswifter_twitch_channel', true);
        
        if ($spswifter_twitch_enabled && $spswifter_twitch_channel) {
            echo '<div class="twitch-stream-button-container">';
            echo '<a href="https://www.twitch.tv/' . esc_attr($spswifter_twitch_channel) . '" class="twitch-stream-button" target="_blank">';
            echo '<span class="twitch-button-icon">🎮</span>';
            echo '<span class="twitch-button-text">Watch Stream</span>';
            echo '</a>';
            echo '</div>';
        }
    }
    
    /**
     * Add Twitch cart notice
     */
    public function add_spswifter_twitch_cart_notice() {
        if ($this->has_spswifter_twitch_products_in_cart()) {
            echo '<div class="twitch-cart-notice">';
            echo '<div class="twitch-notice-content">';
            echo '<span class="twitch-notice-icon">🎮</span>';
            echo '<span class="twitch-notice-text">You have Twitch-related products in your cart!</span>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Add Twitch checkout notice
     */
    public function add_spswifter_twitch_checkout_notice() {
        if ($this->has_spswifter_twitch_products_in_cart()) {
            echo '<div class="twitch-checkout-notice">';
            echo '<div class="twitch-notice-content">';
            echo '<span class="twitch-notice-icon">🎮</span>';
            echo '<span class="twitch-notice-text">Complete your purchase while watching the stream!</span>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Add Twitch order notice
     */
    public function add_spswifter_twitch_order_notice($order_id) {
        $order = wc_get_order($order_id);
        
        if ($this->order_has_spswifter_twitch_products($order)) {
            echo '<div class="twitch-order-notice">';
            echo '<div class="twitch-notice-content">';
            echo '<span class="twitch-notice-icon">🎮</span>';
            echo '<span class="twitch-notice-text">Thank you for your purchase! Check out the stream for more content.</span>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Check if cart has Twitch products
     */
    private function has_spswifter_twitch_products_in_cart() {
        if (WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if (isset($cart_item['spswifter_twitch_enabled']) && $cart_item['spswifter_twitch_enabled']) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Check if order has Twitch products
     */
    private function order_has_spswifter_twitch_products($order) {
        foreach ($order->get_items() as $item) {
            if ($item->get_meta('_spswifter_twitch_enabled') === 'yes') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Customize add to cart text
     */
    public function customize_add_to_cart_text($text) {
        global $product;
        
        $spswifter_twitch_enabled = get_post_meta($product->get_id(), '_spswifter_twitch_enabled', true) === 'yes';
        
        if ($spswifter_twitch_enabled) {
            return __('Buy & Watch Stream', 'speedyswifter-stream-integrator-for-twitch');
        }
        
        return $text;
    }
    
    /**
     * Customize loop add to cart text
     */
    public function customize_loop_add_to_cart_text($text) {
        global $product;
        
        $spswifter_twitch_enabled = get_post_meta($product->get_id(), '_spswifter_twitch_enabled', true) === 'yes';
        
        if ($spswifter_twitch_enabled) {
            return __('Buy & Watch', 'speedyswifter-stream-integrator-for-twitch');
        }
        
        return $text;
    }
    
    /**
     * Customize loop add to cart link
     */
    public function customize_loop_add_to_cart_link($link, $product) {
        $spswifter_twitch_enabled = get_post_meta($product->get_id(), '_spswifter_twitch_enabled', true) === 'yes';
        
        if ($spswifter_twitch_enabled) {
            $link = str_replace('add_to_cart_button', 'add_to_cart_button twitch-product', $link);
        }
        
        return $link;
    }
    
    /**
     * Handle WooCommerce AJAX
     */
    public function handle_woo_product_ajax() {
        check_ajax_referer('spswifter_twitch_woo_nonce', 'nonce');
        
        $action = wp_unslash($_POST['woo_action']) ?? '';
        
        switch ($action) {
            case 'get_product_stream':
                $this->get_product_stream_ajax();
                break;
            case 'check_stream_status':
                $this->check_stream_status_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get product stream AJAX
     */
    private function get_product_stream_ajax() {
        $product_id = intval(wp_unslash($_POST['product_id']) ?? 0);
        
        if (!$product_id) {
            wp_send_json_error('Product ID is required');
        }
        
        $product = wc_get_product($product_id);
        
        if (!$product) {
            wp_send_json_error('Product not found');
        }
        
        $spswifter_twitch_enabled = get_post_meta($product_id, '_spswifter_twitch_enabled', true) === 'yes';
        $spswifter_twitch_channel = get_post_meta($product_id, '_spswifter_twitch_channel', true);
        $spswifter_twitch_stream_url = get_post_meta($product_id, '_spswifter_twitch_stream_url', true);
        
        wp_send_json_success(array(
            'enabled' => $spswifter_twitch_enabled,
            'channel' => $spswifter_twitch_channel,
            'stream_url' => $spswifter_twitch_stream_url,
        ));
    }
    
    /**
     * Check stream status AJAX
     */
    private function check_stream_status_ajax() {
        $channel = sanitize_text_field(wp_unslash($_POST['channel']) ?? '');
        
        if (empty($channel)) {
            wp_send_json_error('Channel is required');
        }
        
        $stream_data = $this->spswifter_twitch_api->get_stream_data($channel);
        
        wp_send_json_success(array(
            'is_live' => !empty($stream_data),
            'viewers' => $stream_data['viewer_count'] ?? 0,
            'game' => $stream_data['game_name'] ?? '',
        ));
    }
    
    /**
     * Add WooCommerce settings menu
     */
    public function add_woo_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'WooCommerce Integration',
            'WooCommerce',
            'manage_options',
            'twitch-woocommerce-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch WooCommerce Integration Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_woocommerce_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_woocommerce_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable WooCommerce Integration</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_woocommerce_settings[enabled]" 
                                   <?php checked($this->woo_settings['enabled'], true); ?> />
                            <label>Enable Twitch integration with WooCommerce products</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Stream on Product Pages</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_woocommerce_settings[show_stream_product]" 
                                   <?php checked($this->woo_settings['show_stream_product'], true); ?> />
                            <label>Show embedded stream on product pages</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Chat on Product Pages</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_woocommerce_settings[show_chat_product]" 
                                   <?php checked($this->woo_settings['show_chat_product'], false); ?> />
                            <label>Show embedded chat on product pages</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Cart Notices</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_woocommerce_settings[show_cart_notices]" 
                                   <?php checked($this->woo_settings['show_cart_notices'], true); ?> />
                            <label>Show notices when Twitch products are in cart</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Checkout Notices</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_woocommerce_settings[show_checkout_notices]" 
                                   <?php checked($this->woo_settings['show_checkout_notices'], true); ?> />
                            <label>Show notices during checkout for Twitch products</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Custom Add to Cart Text</th>
                        <td>
                            <input type="text" name="spswifter_twitch_woocommerce_settings[add_to_cart_text]" 
                                   value="<?php echo esc_attr($this->woo_settings['add_to_cart_text'] ?? 'Buy & Watch Stream'); ?>" 
                                   class="regular-text" />
                            <p class="description">Custom text for add to cart button on Twitch products</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Stream Embed Width</th>
                        <td>
                            <input type="number" name="spswifter_twitch_woocommerce_settings[stream_width]" 
                                   value="<?php echo esc_attr($this->woo_settings['stream_width'] ?? 640); ?>" 
                                   min="300" max="1920" step="10" class="small-text" />
                            <label>px</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Stream Embed Height</th>
                        <td>
                            <input type="number" name="spswifter_twitch_woocommerce_settings[stream_height]" 
                                   value="<?php echo esc_attr($this->woo_settings['stream_height'] ?? 360); ?>" 
                                   min="200" max="1080" step="10" class="small-text" />
                            <label>px</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get WooCommerce settings
     */
    private function get_woo_settings() {
        return get_option('spswifter_twitch_woocommerce_settings', array(
            'enabled' => true,
            'show_stream_product' => true,
            'show_chat_product' => false,
            'show_cart_notices' => true,
            'show_checkout_notices' => true,
            'add_to_cart_text' => 'Buy & Watch Stream',
            'stream_width' => 640,
            'stream_height' => 360,
        ));
    }
    
    /**
     * Enqueue WooCommerce scripts
     */
    public function enqueue_woocommerce_scripts() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        wp_enqueue_style(
            'twitch-woocommerce',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/woocommerce-integration.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-woocommerce',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/woocommerce-integration.js',
            array('jquery'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-woocommerce', 'twitchWooCommerce', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_woo_nonce'),
            'enabled' => $this->woo_settings['enabled'] ?? true,
            'showStreamProduct' => $this->woo_settings['show_stream_product'] ?? true,
            'showChatProduct' => $this->woo_settings['show_chat_product'] ?? false,
        ));
    }
}

// Initialize WooCommerce integration
new SPSWIFTER_Twitch_WooCommerce_Integration();
