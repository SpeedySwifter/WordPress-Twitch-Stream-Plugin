<?php
/**
 * Custom CSS Builder for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_CSS_Builder {
    
    private $css_presets;
    private $custom_css;
    
    public function __construct() {
        $this->css_presets = $this->get_default_presets();
        $this->custom_css = get_option('spswifter_twitch_custom_css', array());
        
        add_action('admin_menu', array($this, 'add_css_builder_menu'));
        add_action('wp_ajax_spswifter_twitch_css_builder', array($this, 'handle_css_builder_ajax'));
        add_action('wp_ajax_nopriv_spswifter_twitch_css_builder', array($this, 'handle_css_builder_ajax'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_css'));
        add_action('wp_head', array($this, 'output_custom_css'));
    }
    
    /**
     * Add CSS builder menu
     */
    public function add_css_builder_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'CSS Builder',
            'CSS Builder',
            'manage_options',
            'twitch-css-builder',
            array($this, 'render_css_builder_page')
        );
    }
    
    /**
     * Render CSS builder page
     */
    public function render_css_builder_page() {
        $presets = $this->get_available_presets();
        $current_preset = $this->custom_css['preset'] ?? 'default';
        $custom_styles = $this->custom_css['styles'] ?? array();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch CSS Builder</h1>
            
            <div class="twitch-css-builder-container">
                <!-- Preset Selection -->
                <div class="twitch-css-section">
                    <h2>Choose Preset</h2>
                    <div class="twitch-preset-selector">
                        <select id="css-preset-select">
                            <option value="default" <?php selected($current_preset, 'default'); ?>>Default</option>
                            <?php foreach ($presets as $preset_id => $preset): ?>
                                <option value="<?php echo esc_attr($preset_id); ?>" <?php selected($current_preset, $preset_id); ?>>
                                    <?php echo esc_html($preset['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button id="apply-preset" class="button button-primary">Apply Preset</button>
                        <button id="create-custom-preset" class="button">Create Custom Preset</button>
                    </div>
                </div>
                
                <!-- CSS Controls -->
                <div class="twitch-css-section">
                    <h2>Customize Styles</h2>
                    <div class="twitch-css-controls">
                        <!-- Container Styles -->
                        <div class="twitch-control-group">
                            <h3>Container</h3>
                            
                            <div class="twitch-control">
                                <label>Background Color</label>
                                <input type="color" id="container-bg-color" value="<?php echo esc_attr($custom_styles['container']['background_color'] ?? '#f8f9fa'); ?>" />
                            </div>
                            
                            <div class="twitch-control">
                                <label>Border Radius</label>
                                <input type="range" id="container-border-radius" min="0" max="50" value="<?php echo esc_attr($custom_styles['container']['border_radius'] ?? '8'); ?>" />
                                <span class="twitch-value-display">8px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Padding</label>
                                <input type="range" id="container-padding" min="0" max="50" value="<?php echo esc_attr($custom_styles['container']['padding'] ?? '20'); ?>" />
                                <span class="twitch-value-display">20px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Margin</label>
                                <input type="range" id="container-margin" min="0" max="50" value="<?php echo esc_attr($custom_styles['container']['margin'] ?? '20'); ?>" />
                                <span class="twitch-value-display">20px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Box Shadow</label>
                                <select id="container-shadow">
                                    <option value="none" <?php selected($custom_styles['container']['box_shadow'] ?? 'none', 'none'); ?>>None</option>
                                    <option value="small" <?php selected($custom_styles['container']['box_shadow'] ?? 'none', 'small'); ?>>Small</option>
                                    <option value="medium" <?php selected($custom_styles['container']['box_shadow'] ?? 'none', 'medium'); ?>>Medium</option>
                                    <option value="large" <?php selected($custom_styles['container']['box_shadow'] ?? 'none', 'large'); ?>>Large</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Player Styles -->
                        <div class="twitch-control-group">
                            <h3>Player</h3>
                            
                            <div class="twitch-control">
                                <label>Border</label>
                                <select id="player-border">
                                    <option value="none" <?php selected($custom_styles['player']['border'] ?? 'none', 'none'); ?>>None</option>
                                    <option value="solid" <?php selected($custom_styles['player']['border'] ?? 'none', 'solid'); ?>>Solid</option>
                                    <option value="dashed" <?php selected($custom_styles['player']['border'] ?? 'none', 'dashed'); ?>>Dashed</option>
                                    <option value="dotted" <?php selected($custom_styles['player']['border'] ?? 'none', 'dotted'); ?>>Dotted</option>
                                </select>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Border Color</label>
                                <input type="color" id="player-border-color" value="<?php echo esc_attr($custom_styles['player']['border_color'] ?? '#9146FF'); ?>" />
                            </div>
                            
                            <div class="twitch-control">
                                <label>Border Width</label>
                                <input type="range" id="player-border-width" min="0" max="10" value="<?php echo esc_attr($custom_styles['player']['border_width'] ?? '0'); ?>" />
                                <span class="twitch-value-display">0px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Border Radius</label>
                                <input type="range" id="player-border-radius" min="0" max="25" value="<?php echo esc_attr($custom_styles['player']['border_radius'] ?? '8'); ?>" />
                                <span class="twitch-value-display">8px</span>
                            </div>
                        </div>
                        
                        <!-- Info Panel Styles -->
                        <div class="twitch-control-group">
                            <h3>Info Panel</h3>
                            
                            <div class="twitch-control">
                                <label>Background Color</label>
                                <input type="color" id="info-bg-color" value="<?php echo esc_attr($custom_styles['info']['background_color'] ?? '#667eea'); ?>" />
                            </div>
                            
                            <div class="twitch-control">
                                <label>Text Color</label>
                                <input type="color" id="info-text-color" value="<?php echo esc_attr($custom_styles['info']['text_color'] ?? '#ffffff'); ?>" />
                            </div>
                            
                            <div class="twitch-control">
                                <label>Font Size</label>
                                <input type="range" id="info-font-size" min="12" max="24" value="<?php echo esc_attr($custom_styles['info']['font_size'] ?? '16'); ?>" />
                                <span class="twitch-value-display">16px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Padding</label>
                                <input type="range" id="info-padding" min="10" max="30" value="<?php echo esc_attr($custom_styles['info']['padding'] ?? '15'); ?>" />
                                <span class="twitch-value-display">15px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Position</label>
                                <select id="info-position">
                                    <option value="top" <?php selected($custom_styles['info']['position'] ?? 'top', 'top'); ?>>Top</option>
                                    <option value="bottom" <?php selected($custom_styles['info']['position'] ?? 'top', 'bottom'); ?>>Bottom</option>
                                    <option value="left" <?php selected($custom_styles['info']['position'] ?? 'top', 'left'); ?>>Left</option>
                                    <option value="right" <?php selected($custom_styles['info']['position'] ?? 'top', 'right'); ?>>Right</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Button Styles -->
                        <div class="twitch-control-group">
                            <h3>Buttons</h3>
                            
                            <div class="twitch-control">
                                <label>Background Color</label>
                                <input type="color" id="button-bg-color" value="<?php echo esc_attr($custom_styles['button']['background_color'] ?? '#9146FF'); ?>" />
                            </div>
                            
                            <div class="twitch-control">
                                <label>Text Color</label>
                                <input type="color" id="button-text-color" value="<?php echo esc_attr($custom_styles['button']['text_color'] ?? '#ffffff'); ?>" />
                            </div>
                            
                            <div class="twitch-control">
                                <label>Border Radius</label>
                                <input type="range" id="button-border-radius" min="0" max="25" value="<?php echo esc_attr($custom_styles['button']['border_radius'] ?? '6'); ?>" />
                                <span class="twitch-value-display">6px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Padding</label>
                                <input type="range" id="button-padding" min="5" max="20" value="<?php echo esc_attr($custom_styles['button']['padding'] ?? '12'); ?>" />
                                <span class="twitch-value-display">12px</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Font Size</label>
                                <input type="range" id="button-font-size" min="12" max="20" value="<?php echo esc_attr($custom_styles['button']['font_size'] ?? '14'); ?>" />
                                <span class="twitch-value-display">14px</span>
                            </div>
                        </div>
                        
                        <!-- Responsive Settings -->
                        <div class="twitch-control-group">
                            <h3>Responsive</h3>
                            
                            <div class="twitch-control">
                                <label>Mobile Breakpoint</label>
                                <select id="mobile-breakpoint">
                                    <option value="768" <?php selected($custom_styles['responsive']['mobile_breakpoint'] ?? '768', '768'); ?>>768px</option>
                                    <option value="640" <?php selected($custom_styles['responsive']['mobile_breakpoint'] ?? '768', '640'); ?>>640px</option>
                                    <option value="480" <?php selected($custom_styles['responsive']['mobile_breakpoint'] ?? '768', '480'); ?>>480px</option>
                                </select>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Mobile Container Width</label>
                                <input type="range" id="mobile-container-width" min="80" max="100" value="<?php echo esc_attr($custom_styles['responsive']['mobile_container_width'] ?? '100'); ?>" />
                                <span class="twitch-value-display">100%</span>
                            </div>
                            
                            <div class="twitch-control">
                                <label>Mobile Font Size</label>
                                <input type="range" id="mobile-font-size" min="10" max="18" value="<?php echo esc_attr($custom_styles['responsive']['mobile_font_size'] ?? '14'); ?>" />
                                <span class="twitch-value-display">14px</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Live Preview -->
                <div class="twitch-css-section">
                    <h2>Live Preview</h2>
                    <div class="twitch-preview-container">
                        <div id="twitch-preview">
                            <!-- Preview will be rendered here -->
                        </div>
                    </div>
                </div>
                
                <!-- Generated CSS -->
                <div class="twitch-css-section">
                    <h2>Generated CSS</h2>
                    <div class="twitch-css-output">
                        <textarea id="generated-css" readonly rows="20" class="large-text">
                            <!-- Generated CSS will appear here -->
                        </textarea>
                        
                        <div class="twitch-css-actions">
                            <button id="copy-css" class="button">Copy CSS</button>
                            <button id="save-css" class="button button-primary">Save Changes</button>
                            <button id="reset-css" class="button">Reset to Default</button>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Presets -->
                <div class="twitch-css-section">
                    <h2>Custom Presets</h2>
                    <div class="twitch-presets-manager">
                        <div id="custom-presets-list">
                            <!-- Custom presets will be listed here -->
                        </div>
                        
                        <div class="twitch-preset-actions">
                            <input type="text" id="new-preset-name" placeholder="Enter preset name" />
                            <button id="save-current-preset" class="button">Save Current as Preset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        // CSS Builder JavaScript will be loaded here
        </script>
        <?php
    }
    
    /**
     * Handle CSS builder AJAX
     */
    public function handle_css_builder_ajax() {
        check_ajax_referer('spswifter_twitch_css_builder_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $action = wp_unslash($_POST['css_action']) ?? '';
        
        switch ($action) {
            case 'get_preset':
                $this->get_preset_ajax();
                break;
            case 'save_css':
                $this->save_css_ajax();
                break;
            case 'save_preset':
                $this->save_preset_ajax();
                break;
            case 'delete_preset':
                $this->delete_preset_ajax();
                break;
            case 'reset_css':
                $this->reset_css_ajax();
                break;
            default:
                wp_send_json_error('Unknown action');
        }
    }
    
    /**
     * Get preset AJAX
     */
    private function get_preset_ajax() {
        $preset_id = sanitize_text_field(wp_unslash($_POST['preset_id']) ?? 'default');
        $preset = $this->get_preset($preset_id);
        
        wp_send_json_success(array('preset' => $preset));
    }
    
    /**
     * Save CSS AJAX
     */
    private function save_css_ajax() {
        $styles = wp_unslash($_POST['styles']) ?? array();
        $preset = sanitize_text_field(wp_unslash($_POST['preset']) ?? 'default');
        
        $this->custom_css = array(
            'preset' => $preset,
            'styles' => $this->sanitize_css_styles($styles),
            'updated_at' => current_time('mysql'),
        );
        
        update_option('spswifter_twitch_custom_css', $this->custom_css);
        
        wp_send_json_success(array('message' => 'CSS saved successfully'));
    }
    
    /**
     * Save preset AJAX
     */
    private function save_preset_ajax() {
        $preset_name = sanitize_text_field(wp_unslash($_POST['preset_name']) ?? '');
        $styles = wp_unslash($_POST['styles']) ?? array();
        
        if (empty($preset_name)) {
            wp_send_json_error('Preset name is required');
        }
        
        $preset_id = sanitize_title($preset_name);
        $custom_presets = get_option('spswifter_twitch_custom_presets', array());
        
        $custom_presets[$preset_id] = array(
            'name' => $preset_name,
            'styles' => $this->sanitize_css_styles($styles),
            'created_at' => current_time('mysql'),
        );
        
        update_option('spswifter_twitch_custom_presets', $custom_presets);
        
        wp_send_json_success(array('message' => 'Preset saved successfully'));
    }
    
    /**
     * Delete preset AJAX
     */
    private function delete_preset_ajax() {
        $preset_id = sanitize_text_field(wp_unslash($_POST['preset_id']) ?? '');
        
        if (empty($preset_id)) {
            wp_send_json_error('Preset ID is required');
        }
        
        $custom_presets = get_option('spswifter_twitch_custom_presets', array());
        
        if (isset($custom_presets[$preset_id])) {
            unset($custom_presets[$preset_id]);
            update_option('spswifter_twitch_custom_presets', $custom_presets);
            
            wp_send_json_success(array('message' => 'Preset deleted successfully'));
        } else {
            wp_send_json_error('Preset not found');
        }
    }
    
    /**
     * Reset CSS AJAX
     */
    private function reset_css_ajax() {
        $this->custom_css = array(
            'preset' => 'default',
            'styles' => array(),
            'updated_at' => current_time('mysql'),
        );
        
        update_option('spswifter_twitch_custom_css', $this->custom_css);
        
        wp_send_json_success(array('message' => 'CSS reset to default'));
    }
    
    /**
     * Get default presets
     */
    private function get_default_presets() {
        return array(
            'default' => array(
                'name' => 'Default',
                'styles' => array(
                    'container' => array(
                        'background_color' => '#f8f9fa',
                        'border_radius' => '8',
                        'padding' => '20',
                        'margin' => '20',
                        'box_shadow' => 'none',
                    ),
                    'player' => array(
                        'border' => 'none',
                        'border_color' => '#9146FF',
                        'border_width' => '0',
                        'border_radius' => '8',
                    ),
                    'info' => array(
                        'background_color' => '#667eea',
                        'text_color' => '#ffffff',
                        'font_size' => '16',
                        'padding' => '15',
                        'position' => 'top',
                    ),
                    'button' => array(
                        'background_color' => '#9146FF',
                        'text_color' => '#ffffff',
                        'border_radius' => '6',
                        'padding' => '12',
                        'font_size' => '14',
                    ),
                    'responsive' => array(
                        'mobile_breakpoint' => '768',
                        'mobile_container_width' => '100',
                        'mobile_font_size' => '14',
                    ),
                ),
            ),
            'dark' => array(
                'name' => 'Dark Mode',
                'styles' => array(
                    'container' => array(
                        'background_color' => '#1a1a1a',
                        'border_radius' => '8',
                        'padding' => '20',
                        'margin' => '20',
                        'box_shadow' => 'medium',
                    ),
                    'player' => array(
                        'border' => 'solid',
                        'border_color' => '#9146FF',
                        'border_width' => '2',
                        'border_radius' => '8',
                    ),
                    'info' => array(
                        'background_color' => '#2d2d2d',
                        'text_color' => '#ffffff',
                        'font_size' => '16',
                        'padding' => '15',
                        'position' => 'top',
                    ),
                    'button' => array(
                        'background_color' => '#9146FF',
                        'text_color' => '#ffffff',
                        'border_radius' => '6',
                        'padding' => '12',
                        'font_size' => '14',
                    ),
                    'responsive' => array(
                        'mobile_breakpoint' => '768',
                        'mobile_container_width' => '100',
                        'mobile_font_size' => '14',
                    ),
                ),
            ),
            'minimal' => array(
                'name' => 'Minimal',
                'styles' => array(
                    'container' => array(
                        'background_color' => '#ffffff',
                        'border_radius' => '0',
                        'padding' => '10',
                        'margin' => '10',
                        'box_shadow' => 'none',
                    ),
                    'player' => array(
                        'border' => 'none',
                        'border_color' => '#000000',
                        'border_width' => '0',
                        'border_radius' => '0',
                    ),
                    'info' => array(
                        'background_color' => '#f0f0f0',
                        'text_color' => '#333333',
                        'font_size' => '14',
                        'padding' => '10',
                        'position' => 'top',
                    ),
                    'button' => array(
                        'background_color' => '#333333',
                        'text_color' => '#ffffff',
                        'border_radius' => '0',
                        'padding' => '8',
                        'font_size' => '12',
                    ),
                    'responsive' => array(
                        'mobile_breakpoint' => '768',
                        'mobile_container_width' => '100',
                        'mobile_font_size' => '12',
                    ),
                ),
            ),
            'colorful' => array(
                'name' => 'Colorful',
                'styles' => array(
                    'container' => array(
                        'background_color' => '#ff6b6b',
                        'border_radius' => '15',
                        'padding' => '25',
                        'margin' => '25',
                        'box_shadow' => 'large',
                    ),
                    'player' => array(
                        'border' => 'solid',
                        'border_color' => '#4ecdc4',
                        'border_width' => '3',
                        'border_radius' => '15',
                    ),
                    'info' => array(
                        'background_color' => '#45b7d1',
                        'text_color' => '#ffffff',
                        'font_size' => '18',
                        'padding' => '20',
                        'position' => 'top',
                    ),
                    'button' => array(
                        'background_color' => '#f9ca24',
                        'text_color' => '#333333',
                        'border_radius' => '10',
                        'padding' => '15',
                        'font_size' => '16',
                    ),
                    'responsive' => array(
                        'mobile_breakpoint' => '768',
                        'mobile_container_width' => '100',
                        'mobile_font_size' => '16',
                    ),
                ),
            ),
        );
    }
    
    /**
     * Get available presets
     */
    private function get_available_presets() {
        $default_presets = $this->get_default_presets();
        $custom_presets = get_option('spswifter_twitch_custom_presets', array());
        
        return array_merge($default_presets, $custom_presets);
    }
    
    /**
     * Get preset
     */
    private function get_preset($preset_id) {
        $presets = $this->get_available_presets();
        return $presets[$preset_id] ?? $presets['default'];
    }
    
    /**
     * Sanitize CSS styles
     */
    private function sanitize_css_styles($styles) {
        $sanitized = array();
        
        foreach ($styles as $group => $group_styles) {
            $sanitized[$group] = array();
            
            foreach ($group_styles as $property => $value) {
                switch ($property) {
                    case 'background_color':
                    case 'border_color':
                    case 'text_color':
                        $sanitized[$group][$property] = sanitize_hex_color($value);
                        break;
                    case 'border_radius':
                    case 'padding':
                    case 'margin':
                    case 'border_width':
                    case 'font_size':
                    case 'mobile_breakpoint':
                    case 'mobile_container_width':
                    case 'mobile_font_size':
                        $sanitized[$group][$property] = absint($value);
                        break;
                    case 'border':
                    case 'box_shadow':
                    case 'position':
                        $sanitized[$group][$property] = sanitize_text_field($value);
                        break;
                    default:
                        $sanitized[$group][$property] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Generate CSS from styles
     */
    public function generate_css($styles = null) {
        if (!$styles) {
            $styles = $this->custom_css['styles'] ?? array();
        }
        
        $css = '';
        
        // Container styles
        if (isset($styles['container'])) {
            $css .= $this->generate_container_css($styles['container']);
        }
        
        // Player styles
        if (isset($styles['player'])) {
            $css .= $this->generate_player_css($styles['player']);
        }
        
        // Info panel styles
        if (isset($styles['info'])) {
            $css .= $this->generate_info_css($styles['info']);
        }
        
        // Button styles
        if (isset($styles['button'])) {
            $css .= $this->generate_button_css($styles['button']);
        }
        
        // Responsive styles
        if (isset($styles['responsive'])) {
            $css .= $this->generate_responsive_css($styles['responsive']);
        }
        
        return $css;
    }
    
    /**
     * Generate container CSS
     */
    private function generate_container_css($styles) {
        $css = ".twitch-stream-container {\n";
        
        if (isset($styles['background_color'])) {
            $css .= "    background-color: {$styles['background_color']};\n";
        }
        
        if (isset($styles['border_radius'])) {
            $css .= "    border-radius: {$styles['border_radius']}px;\n";
        }
        
        if (isset($styles['padding'])) {
            $css .= "    padding: {$styles['padding']}px;\n";
        }
        
        if (isset($styles['margin'])) {
            $css .= "    margin: {$styles['margin']}px 0;\n";
        }
        
        if (isset($styles['box_shadow']) && $styles['box_shadow'] !== 'none') {
            $css .= "    box-shadow: " . $this->get_box_shadow_value($styles['box_shadow']) . ";\n";
        }
        
        $css .= "}\n\n";
        
        return $css;
    }
    
    /**
     * Generate player CSS
     */
    private function generate_player_css($styles) {
        $css = ".twitch-stream-container iframe {\n";
        
        if (isset($styles['border']) && $styles['border'] !== 'none') {
            $css .= "    border-style: {$styles['border']};\n";
            
            if (isset($styles['border_color'])) {
                $css .= "    border-color: {$styles['border_color']};\n";
            }
            
            if (isset($styles['border_width'])) {
                $css .= "    border-width: {$styles['border_width']}px;\n";
            }
        }
        
        if (isset($styles['border_radius'])) {
            $css .= "    border-radius: {$styles['border_radius']}px;\n";
        }
        
        $css .= "}\n\n";
        
        return $css;
    }
    
    /**
     * Generate info CSS
     */
    private function generate_info_css($styles) {
        $css = ".twitch-stream-info {\n";
        
        if (isset($styles['background_color'])) {
            $css .= "    background-color: {$styles['background_color']};\n";
        }
        
        if (isset($styles['text_color'])) {
            $css .= "    color: {$styles['text_color']};\n";
        }
        
        if (isset($styles['font_size'])) {
            $css .= "    font-size: {$styles['font_size']}px;\n";
        }
        
        if (isset($styles['padding'])) {
            $css .= "    padding: {$styles['padding']}px;\n";
        }
        
        $css .= "}\n\n";
        
        // Position-specific styles
        if (isset($styles['position'])) {
            $css .= ".twitch-stream-container {\n";
            $css .= "    display: flex;\n";
            $css .= "    flex-direction: column;\n";
            
            switch ($styles['position']) {
                case 'bottom':
                    $css .= "    flex-direction: column-reverse;\n";
                    break;
                case 'left':
                    $css .= "    flex-direction: row;\n";
                    break;
                case 'right':
                    $css .= "    flex-direction: row-reverse;\n";
                    break;
            }
            
            $css .= "}\n\n";
        }
        
        return $css;
    }
    
    /**
     * Generate button CSS
     */
    private function generate_button_css($styles) {
        $css = ".twitch-stream-button {\n";
        
        if (isset($styles['background_color'])) {
            $css .= "    background-color: {$styles['background_color']};\n";
        }
        
        if (isset($styles['text_color'])) {
            $css .= "    color: {$styles['text_color']};\n";
        }
        
        if (isset($styles['border_radius'])) {
            $css .= "    border-radius: {$styles['border_radius']}px;\n";
        }
        
        if (isset($styles['padding'])) {
            $css .= "    padding: {$styles['padding']}px;\n";
        }
        
        if (isset($styles['font_size'])) {
            $css .= "    font-size: {$styles['font_size']}px;\n";
        }
        
        $css .= "}\n\n";
        
        return $css;
    }
    
    /**
     * Generate responsive CSS
     */
    private function generate_responsive_css($styles) {
        $breakpoint = $styles['mobile_breakpoint'] ?? '768';
        
        $css = "@media (max-width: {$breakpoint}px) {\n";
        
        if (isset($styles['mobile_container_width'])) {
            $css .= "    .twitch-stream-container {\n";
            $css .= "        width: {$styles['mobile_container_width']}%;\n";
            $css .= "    }\n";
        }
        
        if (isset($styles['mobile_font_size'])) {
            $css .= "    .twitch-stream-info {\n";
            $css .= "        font-size: {$styles['mobile_font_size']}px;\n";
            $css .= "    }\n";
        }
        
        $css .= "}\n\n";
        
        return $css;
    }
    
    /**
     * Get box shadow value
     */
    private function get_box_shadow_value($size) {
        switch ($size) {
            case 'small':
                return '0 2px 4px rgba(0, 0, 0, 0.1)';
            case 'medium':
                return '0 4px 8px rgba(0, 0, 0, 0.15)';
            case 'large':
                return '0 8px 16px rgba(0, 0, 0, 0.2)';
            default:
                return 'none';
        }
    }
    
    /**
     * Enqueue custom CSS
     */
    public function enqueue_custom_css() {
        if (!empty($this->custom_css['styles'])) {
            wp_add_inline_style('spswifter-twitch-frontend', $this->generate_css());
        }
    }
    
    /**
     * Output custom CSS
     */
    public function output_custom_css() {
        if (!empty($this->custom_css['styles'])) {
            echo "<style>\n";
            echo $this->generate_css();
            echo "</style>\n";
        }
    }
    
    /**
     * Get custom CSS for preview
     */
    public function get_preview_css($styles) {
        return $this->generate_css($styles);
    }
    
    /**
     * Export CSS
     */
    public function export_css($preset_id = null) {
        if ($preset_id) {
            $preset = $this->get_preset($preset_id);
            $styles = $preset['styles'];
        } else {
            $styles = $this->custom_css['styles'];
        }
        
        return $this->generate_css($styles);
    }
    
    /**
     * Import CSS
     */
    public function import_css($css_string) {
        // Parse CSS string and extract styles
        // This is a simplified implementation
        $styles = $this->parse_css_string($css_string);
        
        $this->custom_css = array(
            'preset' => 'custom',
            'styles' => $styles,
            'updated_at' => current_time('mysql'),
        );
        
        update_option('spswifter_twitch_custom_css', $this->custom_css);
        
        return true;
    }
    
    /**
     * Parse CSS string
     */
    private function parse_css_string($css_string) {
        // This is a simplified CSS parser
        // In a real implementation, you'd use a more robust CSS parser
        $styles = array();
        
        // Parse container styles
        if (preg_match('/\.twitch-stream-container\s*\{([^}]+)\}/', $css_string, $matches)) {
            $container_css = $matches[1];
            $styles['container'] = $this->parse_css_properties($container_css);
        }
        
        // Parse player styles
        if (preg_match('/\.twitch-stream-container\s*iframe\s*\{([^}]+)\}/', $css_string, $matches)) {
            $player_css = $matches[1];
            $styles['player'] = $this->parse_css_properties($player_css);
        }
        
        // Parse info styles
        if (preg_match('/\.twitch-stream-info\s*\{([^}]+)\}/', $css_string, $matches)) {
            $info_css = $matches[1];
            $styles['info'] = $this->parse_css_properties($info_css);
        }
        
        // Parse button styles
        if (preg_match('/\.twitch-stream-button\s*\{([^}]+)\}/', $css_string, $matches)) {
            $button_css = $matches[1];
            $styles['button'] = $this->parse_css_properties($button_css);
        }
        
        return $styles;
    }
    
    /**
     * Parse CSS properties
     */
    private function parse_css_properties($css_string) {
        $properties = array();
        $lines = explode("\n", $css_string);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (preg_match('/([^:]+):\s*([^;]+)/', $line, $matches)) {
                $property = trim($matches[1]);
                $value = trim($matches[2]);
                
                // Convert CSS property names to our internal format
                $internal_property = $this->css_property_to_internal($property);
                
                if ($internal_property) {
                    $properties[$internal_property] = $this->css_value_to_internal($value);
                }
            }
        }
        
        return $properties;
    }
    
    /**
     * Convert CSS property to internal format
     */
    private function css_property_to_internal($property) {
        $mapping = array(
            'background-color' => 'background_color',
            'border-radius' => 'border_radius',
            'padding' => 'padding',
            'margin' => 'margin',
            'box-shadow' => 'box_shadow',
            'border-style' => 'border',
            'border-color' => 'border_color',
            'border-width' => 'border_width',
            'color' => 'text_color',
            'font-size' => 'font_size',
        );
        
        return $mapping[$property] ?? null;
    }
    
    /**
     * Convert CSS value to internal format
     */
    private function css_value_to_internal($value) {
        // Remove units from numeric values
        if (preg_match('/(\d+)px/', $value, $matches)) {
            return $matches[1];
        }
        
        // Handle color values
        if (preg_match('/#[0-9a-fA-F]{6}/', $value)) {
            return $value;
        }
        
        // Handle other values
        return $value;
    }
}

// Initialize CSS builder
new SPSWIFTER_Twitch_CSS_Builder();
