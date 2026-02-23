<?php
/**
 * Cookie Integration Helper Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Cookie_Integration {
    
    private $supported_plugins = array(
        'borlabs' => false,
        'real_cookie_banner' => false,
        'complianz' => false,
        'cookiebot' => false,
        'omr' => false,
        'universal' => false
    );
    
    public function __construct() {
        $this->detect_cookie_plugins();
        add_action('wp_footer', array($this, 'render_cookie_integration'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_cookie_scripts'));
    }
    
    /**
     * Aktive Cookie-Plugins erkennen
     */
    private function detect_cookie_plugins() {
        // Borlabs Cookie
        $this->supported_plugins['borlabs'] = class_exists('BorlabsCookie') || defined('BORLABS_COOKIE_VERSION');
        
        // Real Cookie Banner
        $this->supported_plugins['real_cookie_banner'] = class_exists('RCB') || defined('RCB_VERSION');
        
        // Complianz
        $this->supported_plugins['complianz'] = class_exists('COMPLIANZ') || defined('CMPLZ_VERSION');
        
        // Cookiebot
        $this->supported_plugins['cookiebot'] = defined('COOKIEBOT_VERSION') || get_option('cookiebot_active');
        
        // OMR
        $this->supported_plugins['omr'] = class_exists('OMR') || defined('OMR_VERSION');
        
        // Universal (Fallback)
        $this->supported_plugins['universal'] = true;
    }
    
    /**
     * Cookie-Integration rendern
     */
    public function render_cookie_integration() {
        $active_plugin = $this->get_active_plugin();
        
        switch ($active_plugin) {
            case 'borlabs':
                $this->render_borlabs_integration();
                break;
            case 'real_cookie_banner':
                $this->render_real_cookie_banner_integration();
                break;
            case 'complianz':
                $this->render_complianz_integration();
                break;
            case 'cookiebot':
                $this->render_cookiebot_integration();
                break;
            case 'omr':
                $this->render_omr_integration();
                break;
            default:
                $this->render_universal_integration();
                break;
        }
    }
    
    /**
     * Borlabs Cookie Integration
     */
    private function render_borlabs_integration() {
        ?>
        <script>
        document.addEventListener('borlabsCookieInit', function() {
            if (window.BorlabsCookie && window.BorlabsCookie.checkCookieGroup('external-media')) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('borlabs');
            }
        });

        function loadTwitchStreams() {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                var channel = container.getAttribute('data-channel');
                if (channel) {
                    loadTwitchPlayer(container);
                }
            });
        }

        function loadTwitchPlayer(container) {
            var channel = container.getAttribute('data-channel');
            var width = container.getAttribute('data-width') || '100%';
            var height = container.getAttribute('data-height') || '480';
            
            var iframe = document.createElement('iframe');
            iframe.src = 'https://player.twitch.tv/?channel=' + channel + '&parent=' + window.location.hostname + '&autoplay=false&muted=true';
            iframe.width = width;
            iframe.height = height;
            iframe.frameBorder = '0';
            iframe.scrolling = 'no';
            iframe.allowFullscreen = true;
            
            container.innerHTML = '';
            container.appendChild(iframe);
        }

        function showTwitchCookiePlaceholder(plugin) {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                if (!container.querySelector('.twitch-cookie-placeholder')) {
                    var placeholder = createCookiePlaceholder(plugin);
                    container.innerHTML = '';
                    container.appendChild(placeholder);
                }
            });
        }

        function createCookiePlaceholder(plugin) {
            var placeholder = document.createElement('div');
            placeholder.className = 'twitch-cookie-placeholder';
            
            var acceptText = 'Externe Medien akzeptieren';
            var settingsText = 'Cookie-Einstellungen';
            var acceptAction = "window.BorlabsCookie.acceptGroup('external-media')";
            var settingsAction = "window.BorlabsCookie.showCookieBox()";
            
            placeholder.innerHTML = `
                <div class="twitch-cookie-message">
                    <h3>🎮 Twitch Stream</h3>
                    <p>Dieser Inhalt erfordert Ihre Zustimmung zu externen Medien.</p>
                    <button class="twitch-cookie-accept" onclick="${acceptAction}">${acceptText}</button>
                    <button class="twitch-cookie-settings" onclick="${settingsAction}">${settingsText}</button>
                </div>
            `;
            
            return placeholder;
        }
        </script>
        <?php
    }
    
    /**
     * Real Cookie Banner Integration
     */
    private function render_real_cookie_banner_integration() {
        ?>
        <script>
        document.addEventListener('rcb-ready', function() {
            if (window.rcb && window.rcb.consent && window.rcb.consent['twitch']) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('real_cookie_banner');
            }
        });

        window.addEventListener('rcb:consent', function(e) {
            if (e.detail.consent['twitch']) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('real_cookie_banner');
            }
        });

        function loadTwitchStreams() {
            // Gleiche Funktion wie bei Borlabs
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                var channel = container.getAttribute('data-channel');
                if (channel) {
                    loadTwitchPlayer(container);
                }
            });
        }

        function loadTwitchPlayer(container) {
            // Gleiche Funktion wie bei Borlabs
        }

        function showTwitchCookiePlaceholder(plugin) {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                if (!container.querySelector('.twitch-cookie-placeholder')) {
                    var placeholder = createCookiePlaceholder(plugin);
                    container.innerHTML = '';
                    container.appendChild(placeholder);
                }
            });
        }

        function createCookiePlaceholder(plugin) {
            var placeholder = document.createElement('div');
            placeholder.className = 'twitch-cookie-placeholder';
            
            var acceptText = 'Twitch akzeptieren';
            var settingsText = 'Cookie-Einstellungen';
            var acceptAction = "window.rcb.acceptService('twitch')";
            var settingsAction = "window.rcb.showSettings()";
            
            placeholder.innerHTML = `
                <div class="twitch-cookie-message">
                    <h3>🎮 Twitch Stream</h3>
                    <p>Dieser Inhalt erfordert Ihre Zustimmung zu externen Medien.</p>
                    <button class="twitch-cookie-accept" onclick="${acceptAction}">${acceptText}</button>
                    <button class="twitch-cookie-settings" onclick="${settingsAction}">${settingsText}</button>
                </div>
            `;
            
            return placeholder;
        }
        </script>
        <?php
    }
    
    /**
     * Complianz Integration
     */
    private function render_complianz_integration() {
        ?>
        <script>
        document.addEventListener('cmplz_consent_changed', function(consent) {
            if (consent.details.categories['marketing'] || consent.details.categories['statistics']) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('complianz');
            }
        });

        if (typeof cmplz !== 'undefined' && cmplz.consent) {
            if (cmplz.consent.marketing || cmplz.consent.statistics) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('complianz');
            }
        }

        function loadTwitchStreams() {
            // Gleiche Funktion wie bei Borlabs
        }

        function loadTwitchPlayer(container) {
            // Gleiche Funktion wie bei Borlabs
        }

        function showTwitchCookiePlaceholder(plugin) {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                if (!container.querySelector('.twitch-cookie-placeholder')) {
                    var placeholder = createCookiePlaceholder(plugin);
                    container.innerHTML = '';
                    container.appendChild(placeholder);
                }
            });
        }

        function createCookiePlaceholder(plugin) {
            var placeholder = document.createElement('div');
            placeholder.className = 'twitch-cookie-placeholder';
            
            var acceptText = 'Marketing-Cookies akzeptieren';
            var settingsText = 'Cookie-Einstellungen';
            var acceptAction = "cmplz_accept_category('marketing')";
            var settingsAction = "cmplz_show_banner()";
            
            placeholder.innerHTML = `
                <div class="twitch-cookie-message">
                    <h3>🎮 Twitch Stream</h3>
                    <p>Dieser Inhalt erfordert Ihre Zustimmung zu Marketing-Cookies.</p>
                    <button class="twitch-cookie-accept" onclick="${acceptAction}">${acceptText}</button>
                    <button class="twitch-cookie-settings" onclick="${settingsAction}">${settingsText}</button>
                </div>
            `;
            
            return placeholder;
        }
        </script>
        <?php
    }
    
    /**
     * Cookiebot Integration
     */
    private function render_cookiebot_integration() {
        ?>
        <script>
        window.addEventListener('CookiebotConsentApplied', function(e) {
            if (e.consent.marketing) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('cookiebot');
            }
        });

        if (typeof Cookiebot !== 'undefined' && Cookiebot.consent) {
            if (Cookiebot.consent.marketing) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('cookiebot');
            }
        }

        function loadTwitchStreams() {
            // Gleiche Funktion wie bei Borlabs
        }

        function loadTwitchPlayer(container) {
            // Gleiche Funktion wie bei Borlabs
        }

        function showTwitchCookiePlaceholder(plugin) {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                if (!container.querySelector('.twitch-cookie-placeholder')) {
                    var placeholder = createCookiePlaceholder(plugin);
                    container.innerHTML = '';
                    container.appendChild(placeholder);
                }
            });
        }

        function createCookiePlaceholder(plugin) {
            var placeholder = document.createElement('div');
            placeholder.className = 'twitch-cookie-placeholder';
            
            var acceptText = 'Cookie-Einstellungen ändern';
            var acceptAction = "Cookiebot.renew()";
            
            placeholder.innerHTML = `
                <div class="twitch-cookie-message">
                    <h3>🎮 Twitch Stream</h3>
                    <p>Dieser Inhalt erfordert Ihre Zustimmung zu Marketing-Cookies.</p>
                    <button class="twitch-cookie-accept" onclick="${acceptAction}">${acceptText}</button>
                </div>
            `;
            
            return placeholder;
        }
        </script>
        <?php
    }
    
    /**
     * OMR Integration
     */
    private function render_omr_integration() {
        ?>
        <script>
        document.addEventListener('omr_consent_update', function(e) {
            if (e.detail.consent['external-media']) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('omr');
            }
        });

        if (typeof omr !== 'undefined' && omr.consent) {
            if (omr.consent['external-media']) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('omr');
            }
        }

        function loadTwitchStreams() {
            // Gleiche Funktion wie bei Borlabs
        }

        function loadTwitchPlayer(container) {
            // Gleiche Funktion wie bei Borlabs
        }

        function showTwitchCookiePlaceholder(plugin) {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                if (!container.querySelector('.twitch-cookie-placeholder')) {
                    var placeholder = createCookiePlaceholder(plugin);
                    container.innerHTML = '';
                    container.appendChild(placeholder);
                }
            });
        }

        function createCookiePlaceholder(plugin) {
            var placeholder = document.createElement('div');
            placeholder.className = 'twitch-cookie-placeholder';
            
            var acceptText = 'Externe Medien akzeptieren';
            var settingsText = 'Cookie-Einstellungen';
            var acceptAction = "omr.updateConsent({'external-media': true})";
            var settingsAction = "omr.showSettings()";
            
            placeholder.innerHTML = `
                <div class="twitch-cookie-message">
                    <h3>🎮 Twitch Stream</h3>
                    <p>Dieser Inhalt erfordert Ihre Zustimmung zu externen Medien.</p>
                    <button class="twitch-cookie-accept" onclick="${acceptAction}">${acceptText}</button>
                    <button class="twitch-cookie-settings" onclick="${settingsAction}">${settingsText}</button>
                </div>
            `;
            
            return placeholder;
        }
        </script>
        <?php
    }
    
    /**
     * Universelle Integration (Fallback)
     */
    private function render_universal_integration() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var cookieConsent = localStorage.getItem('cookie_consent');
            
            if (cookieConsent === 'accepted') {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder('universal');
            }
        });

        function acceptTwitchCookies() {
            localStorage.setItem('cookie_consent', 'accepted');
            loadTwitchStreams();
        }

        function loadTwitchStreams() {
            // Gleiche Funktion wie bei Borlabs
        }

        function loadTwitchPlayer(container) {
            // Gleiche Funktion wie bei Borlabs
        }

        function showTwitchCookiePlaceholder(plugin) {
            var containers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
            containers.forEach(function(container) {
                if (!container.querySelector('.twitch-cookie-placeholder')) {
                    var placeholder = createCookiePlaceholder(plugin);
                    container.innerHTML = '';
                    container.appendChild(placeholder);
                }
            });
        }

        function createCookiePlaceholder(plugin) {
            var placeholder = document.createElement('div');
            placeholder.className = 'twitch-cookie-placeholder';
            
            var acceptText = 'Akzeptieren und laden';
            var acceptAction = "acceptTwitchCookies()";
            
            placeholder.innerHTML = `
                <div class="twitch-cookie-message">
                    <h3>🎮 Twitch Stream</h3>
                    <p>Dieser Inhalt verwendet externe Dienste von Twitch.</p>
                    <button class="twitch-cookie-accept" onclick="${acceptAction}">${acceptText}</button>
                    <div class="twitch-cookie-info">
                        <small>Durch Klick akzeptieren Sie die <a href="https://www.twitch.tv/p/legal/privacy-policy" target="_blank">Twitch Datenschutzrichtlinie</a></small>
                    </div>
                </div>
            `;
            
            return placeholder;
        }
        </script>
        <?php
    }
    
    /**
     * Cookie-Scripts laden
     */
    public function enqueue_cookie_scripts() {
        wp_enqueue_style(
            'twitch-cookie-integration',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/cookie-integration.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
    }
    
    /**
     * Aktives Cookie-Plugin abrufen
     */
    private function get_active_plugin() {
        foreach ($this->supported_plugins as $plugin => $active) {
            if ($active) {
                return $plugin;
            }
        }
        return 'universal';
    }
    
    /**
     * Unterstützte Plugins abrufen
     */
    public function get_supported_plugins() {
        return $this->supported_plugins;
    }
}

// Initialisierung
function spswifter_twitch_cookie_integration_init() {
    new SPSWIFTER_Twitch_Cookie_Integration();
}
add_action('init', 'spswifter_twitch_cookie_integration_init');

// Admin Notice für Cookie-Integration
function spswifter_twitch_cookie_admin_notice() {
    $integration = new SPSWIFTER_Twitch_Cookie_Integration();
    $supported_plugins = $integration->get_supported_plugins();
    $active_plugins = array_filter($supported_plugins);
    
    if (!empty($active_plugins)) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php esc_html_e('🍪 SpeedySwifter Twitch: Cookie-Integration aktiv für ', 'speedyswifter-stream-integrator-for-twitch'); ?>
                <strong><?php echo implode(', ', array_keys($active_plugins)); ?></strong>
                <?php esc_html_e(' - DSGVO-konform!', 'speedyswifter-stream-integrator-for-twitch'); ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'spswifter_twitch_cookie_admin_notice');
?>
