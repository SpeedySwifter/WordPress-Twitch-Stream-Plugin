<?php
/**
 * Admin-Einstellungsseite
 */
function spswifter_twitch_add_admin_menu() {
    add_options_page(
        'SpeedySwifter Twitch API Einstellungen',
        'SpeedySwifter Twitch API',
        'manage_options',
        'spswifter-twitch-api-settings',
        'spswifter_twitch_settings_page'
    );
}
add_action('admin_menu', 'spswifter_twitch_add_admin_menu');

/**
 * Einstellungen registrieren
 */
function spswifter_twitch_settings_init() {
    register_setting('spswifter_twitch_api', 'spswifter_twitch_client_id', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
        'show_in_rest'      => false,
    ));
    register_setting('spswifter_twitch_api', 'spswifter_twitch_client_secret', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
        'show_in_rest'      => false,
    ));
}
add_action('admin_init', 'spswifter_twitch_settings_init');

/**
 * Settings-Page HTML
 */
function spswifter_twitch_settings_page() {
    ?>
    <div class="wrap">
        <h1>🎮 SpeedySwifter Twitch API Einstellungen</h1>
        
        <div class="notice notice-info">
            <p><strong>Hinweis:</strong> Du benötigst eine Twitch-App. Erstelle sie hier: 
            <a href="https://dev.twitch.tv/console/apps" target="_blank">Twitch Developer Console</a></p>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('spswifter_twitch_api');
            wp_nonce_field('spswifter_twitch_save_settings', 'spswifter_nonce');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="spswifter_twitch_client_id">Client ID</label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            id="spswifter_twitch_client_id" 
                            name="spswifter_twitch_client_id" 
                            value="<?php echo esc_attr(get_option('spswifter_twitch_client_id')); ?>" 
                            class="regular-text"
                        />
                        <p class="description">Deine Twitch Client ID</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="spswifter_twitch_client_secret">Client Secret</label>
                    </th>
                    <td>
                        <input 
                            type="password" 
                            id="spswifter_twitch_client_secret" 
                            name="spswifter_twitch_client_secret" 
                            value="<?php echo esc_attr(get_option('spswifter_twitch_client_secret')); ?>" 
                            class="regular-text"
                        />
                        <p class="description">Dein Twitch Client Secret (wird nur einmal angezeigt!)</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Einstellungen speichern'); ?>
        </form>

        <hr>

        <h2>📖 Shortcode Verwendung</h2>
        <code>[spswifter_twitch_stream channel="deinkanal"]</code>
        
        <h3>Parameter:</h3>
        <ul>
            <li><code>channel</code> - Twitch-Benutzername (erforderlich)</li>
            <li><code>width</code> - Breite (Standard: 100%)</li>
            <li><code>height</code> - Höhe (Standard: 480)</li>
            <li><code>autoplay</code> - Automatisch starten (Standard: true)</li>
            <li><code>muted</code> - Stumm (Standard: false)</li>
        </ul>

        <h3>Beispiele:</h3>
        <ul>
            <li><code>[spswifter_twitch_stream channel="shroud"]</code></li>
            <li><code>[spswifter_twitch_stream channel="ninja" height="720"]</code></li>
            <li><code>[spswifter_twitch_stream channel="pokimane" autoplay="false"]</code></li>
            <li><code>[spswifter_twitch_stream channel="summit1g" width="800px" height="600"]</code></li>
        </ul>

        <hr>

        <h2>🔗 Erweiterte Integration</h2>
        <p>Für OAuth-Authentication und erweiterte Token-Funktionalität:</p>
        <p><strong><a href="https://github.com/SpeedySwifter/WP-Twitch-Access-Token" target="_blank">WP-Twitch-Access-Token Plugin</a></strong></p>
        <ul>
            <li>Benutzer-Login mit Twitch-Konto</li>
            <li>User-specific Access Tokens</li>
            <li>Erweiterte API-Rechte</li>
            <li>Integration mit diesem Plugin</li>
        </ul>
    </div>
    <?php
}
?>
