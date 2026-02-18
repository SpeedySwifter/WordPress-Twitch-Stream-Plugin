# 🎮 WordPress Twitch Stream Plugin

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-6.9.1-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Twitch](https://img.shields.io/badge/Twitch_API-9146FF?style=for-the-badge&logo=twitch&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

[![GitHub Stars](https://img.shields.io/github/stars/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/forks)
[![GitHub Issues](https://img.shields.io/github/issues/SpeedySwifter/WordPress-Twitch-Stream-Plugin)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)

**Ein leichtgewichtiges WordPress-Plugin zur Einbindung von Twitch-Streams per Shortcode**

[Features](#-features) • [Installation](#-installation) • [Verwendung](#-verwendung) • [FAQ](#-häufige-fragen-faq)

</div>

---

## 📌 Was ist das?

Dieses **WordPress-Plugin** ermöglicht die nahtlose Integration von **Twitch-Streams** in deine Website. Es zeigt automatisch den Live-Status an und bindet den Twitch-Player nur ein, wenn der Stream tatsächlich live ist.

### ✨ Features

- ✅ **Einfacher Shortcode** – `[twitch_stream channel="deinkanal"]`
- 🔴 **Live-Status Erkennung** – Automatische Prüfung ob Stream online ist
- 📺 **Responsive Player** – Twitch-Embed passt sich an alle Bildschirmgrößen an
- ⚙️ **Admin-Panel** – Komfortable Einstellungsseite für API-Credentials
- 🔐 **Sichere API-Integration** – Nutzt offizielle Twitch Helix API
- 💾 **Token-Caching** – Reduziert API-Calls durch intelligentes Caching
- 🎨 **Anpassbar** – CSS-Klassen für individuelles Styling
- 🧩 **WordPress 6.9.1 kompatibel** – Getestet mit aktueller WP-Version
- 🎯 **Stream-Infos** – Titel, Spiel, Zuschauer, Avatar, Live Badge
- 📱 **Multiple Streams Grid** – Mehrere Streams im Grid-Layout
- 🧩 **Gutenberg Blocks** – Native WordPress Block Editor Integration
- 🔧 **Page Builder Support** – Elementor, Oxygen, Divi, Beaver Builder & mehr
- 🍪 **Cookie Banner Integration** – DSGVO-konform mit 6 Cookie-Systemen
- 📹 **VOD Support** – Video on Demand mit Archiven, Uploads, Highlights
- 🎬 **Clips Integration** – Twitch Clips mit Embed-Funktionalität
- 📱 **Sidebar Widgets** – VOD & Clips Widgets für WordPress Sidebars

---

## 🎯 Wofür brauche ich das?

### 📡 Use Cases

- 🎮 **Gaming-Websites** – Eigenen Twitch-Stream auf der Webseite zeigen
- 🏆 **eSports-Teams** – Live-Matches direkt einbetten
- 🎥 **Content Creator** – Stream-Integration in WordPress-Blog
- 📰 **News-Portalen** – Event-Streams live übertragen
- 🎪 **Event-Seiten** – Konferenzen & Tournaments streamen

### 🔧 Was macht es?

```text
✓ Prüft automatisch ob Stream live ist
✓ Zeigt Twitch-Player nur bei Live-Streams
✓ Zeigt Offline-Nachricht wenn Stream nicht aktiv
✓ Vollständig responsive für alle Geräte
```

---

## 📦 Installation

### Option 1: Manuell (ZIP-Upload)

1. **Plugin herunterladen** als ZIP
2. In WordPress: **Plugins → Installieren → Plugin hochladen**
3. ZIP-Datei auswählen und installieren
4. Plugin **aktivieren**

### Option 2: FTP/SFTP

```bash
# Repository klonen
git clone https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin.git

# Ordner nach wp-content/plugins/ verschieben
mv WordPress-Twitch-Stream-Plugin /pfad/zu/wordpress/wp-content/plugins/
```

Dann im WordPress-Backend unter **Plugins** aktivieren.

---

## 🔑 Twitch API einrichten

### 1️⃣ Twitch App erstellen

Du benötigst eine **Twitch Developer Application**, um API-Zugriff zu erhalten:

1. Gehe zu: [https://dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Klicke auf **"Register Your Application"**
3. Fülle das Formular aus:

```
Name:                 Meine WordPress Site
OAuth Redirect URLs:  https://deine-domain.de
Category:             Website Integration
```

4. **Speichern** und notiere dir:
   - ✅ **Client ID**
   - ✅ **Client Secret** (wird nur einmal angezeigt!)

### 2️⃣ Credentials in WordPress eintragen

1. In WordPress-Admin: **Einstellungen → Twitch API**
2. **Client ID** eintragen
3. **Client Secret** eintragen
4. **Änderungen speichern**

✅ Fertig! Das Plugin ist jetzt einsatzbereit.

---

## 🧩 Verwendung

### Basic Shortcode

```text
[twitch_stream channel="shroud"]
```

### Mit Optionen

```text
[twitch_stream channel="shroud" width="100%" height="480"]
```

### Parameter-Übersicht

| Parameter | Beschreibung | Standard | Erforderlich |
|-----------|--------------|----------|--------------|
| `channel` | Twitch-Benutzername | - | ✅ Ja |
| `width` | Breite des Players | `100%` | ❌ Nein |
| `height` | Höhe des Players | `480` | ❌ Nein |
| `autoplay` | Automatisch starten | `true` | ❌ Nein |
| `muted` | Stummgeschaltet | `false` | ❌ Nein |

### 📝 Praktische Beispiele

```text
[twitch_stream channel="esl_csgo"]
[twitch_stream channel="ninja" height="720"]
[twitch_stream channel="pokimane" autoplay="false"]
[twitch_stream channel="summit1g" width="800px" height="600"]
```

### Stream-Verhalten

| Stream-Status | Was passiert? |
|---------------|---------------|
| 🟢 **Live** | Twitch-Player wird eingebettet |
| ⚫ **Offline** | "Stream ist derzeit offline" Nachricht |
| ⚠️ **Fehler** | Fehlermeldung mit Hinweis |

---

## 📂 Plugin-Struktur

```
WordPress-Twitch-Stream-Plugin/
│
├── 📄 wp-twitch-stream.php        # Haupt-Plugin-Datei
├── 📄 README.md                   # Diese Datei
├── 📄 LICENSE                     # MIT Lizenz
│
├── 📁 admin/
│   ├── 📄 settings-page.php       # Admin-Einstellungsseite
│   └── 📄 admin-styles.css        # Admin-Styling
│
├── 📁 includes/
│   ├── 📄 twitch-api.php          # API-Handler
│   ├── 📄 shortcode.php           # Shortcode-Logic
│   ├── 📄 token-manager.php       # Token-Caching
│   ├── 📄 gutenberg-block.php     # Gutenberg Blocks
│   ├── 📄 page-builder-compatibility.php # Page Builder Integration
│   ├── 📄 cookie-integration.php  # Cookie Banner Integration
│   └── � sidebar-widgets.php    # VOD & Clips Widgets
│
├── �📁 assets/
│   ├── 📁 css/
│   │   ├── 📄 frontend.css        # Frontend-Styles
│   │   ├── 📄 block.css           # Gutenberg Block Styles
│   │   ├── 📄 page-builder-compatibility.css # Page Builder Styles
│   │   ├── 📄 cookie-integration.css # Cookie Integration Styles
│   │   └── � vod-clips.css       # VOD & Clips Styles
│   └── �📁 js/
│       ├── 📄 player.js            # Player-Funktionen
│       ├── 📄 block.js            # Gutenberg Block JavaScript
│       ├── 📄 oxygen-builder.js    # Oxygen Builder JS
│       └── 📄 divi-builder.js      # Divi Builder JS
│
├── 📁 docs/
│   └── 📄 cookie-banner-integration.md # Cookie Integration Tutorial
│
└── 📁 languages/
    ├── 📄 wp-twitch-stream-de_DE.po
    └── 📄 wp-twitch-stream-de_DE.mo
```

---

## 💻 Code-Dokumentation

### Plugin-Haupt-Datei

```php
<?php
/**
 * Plugin Name: WordPress Twitch Stream
 * Plugin URI: https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin
 * Description: Bindet Twitch-Streams per Shortcode ein mit Live-Status-Erkennung
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: SpeedySwifter
 * Author URI: https://github.com/SpeedySwifter
 * License: MIT
 * Text Domain: wp-twitch-stream
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('WP_TWITCH_VERSION', '1.0.0');
define('WP_TWITCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_TWITCH_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WP_TWITCH_PLUGIN_DIR . 'includes/twitch-api.php';
require_once WP_TWITCH_PLUGIN_DIR . 'includes/shortcode.php';
require_once WP_TWITCH_PLUGIN_DIR . 'admin/settings-page.php';
?>
```

### Twitch API Klasse

```php
<?php
class WP_Twitch_API {
    private $client_id;
    private $client_secret;
    private $access_token;

    public function __construct() {
        $this->client_id = get_option('twitch_client_id');
        $this->client_secret = get_option('twitch_client_secret');
        $this->access_token = $this->get_access_token();
    }

    private function get_access_token() {
        $token = get_transient('twitch_access_token');

        if (!$token) {
            $response = wp_remote_post('https://id.twitch.tv/oauth2/token', [
                'body' => [
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type' => 'client_credentials'
                ]
            ]);

            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $token = $data['access_token'];
                set_transient('twitch_access_token', $token, 50 * DAY_IN_SECONDS);
            }
        }

        return $token;
    }

    public function is_stream_live($channel) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/streams?user_login={$channel}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return !empty($data['data']);
        }

        return false;
    }

    public function get_stream_data($channel) {
        $response = wp_remote_get(
            "https://api.twitch.tv/helix/streams?user_login={$channel}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'Client-Id' => $this->client_id
                ]
            ]
        );

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'][0] ?? null;
        }

        return null;
    }
}
?>
```

### Shortcode Handler

```php
<?php
function wp_twitch_stream_shortcode($atts) {
    $atts = shortcode_atts([
        'channel' => '',
        'width' => '100%',
        'height' => '480',
        'autoplay' => 'true',
        'muted' => 'false'
    ], $atts);

    if (empty($atts['channel'])) {
        return '<p class="twitch-error">❌ Bitte gib einen Twitch-Kanal an.</p>';
    }

    $api = new WP_Twitch_API();
    $is_live = $api->is_stream_live($atts['channel']);

    if (!$is_live) {
        return sprintf(
            '<div class="twitch-offline">
                <p>🔴 Stream ist derzeit offline</p>
                <p>Folge <a href="https://twitch.tv/%s" target="_blank">@%s</a> um benachrichtigt zu werden!</p>
            </div>',
            esc_attr($atts['channel']),
            esc_html($atts['channel'])
        );
    }

    $domain = $_SERVER['HTTP_HOST'];
    $embed_url = sprintf(
        'https://player.twitch.tv/?channel=%s&parent=%s&autoplay=%s&muted=%s',
        urlencode($atts['channel']),
        urlencode($domain),
        $atts['autoplay'],
        $atts['muted']
    );

    return sprintf(
        '<div class="twitch-stream-container">
            <iframe
                src="%s"
                width="%s"
                height="%s"
                frameborder="0"
                scrolling="no"
                allowfullscreen="true">
            </iframe>
        </div>',
        esc_url($embed_url),
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );
}

add_shortcode('twitch_stream', 'wp_twitch_stream_shortcode');
?>
```

---

## 🎮 Erweiterte Shortcodes (v1.1.0+)

### Stream-Infos Shortcode
Zeigt detaillierte Informationen über einen Twitch-Stream an:

```text
[twitch_stream_info channel="username" layout="horizontal" show_avatar="true"]
```

**Parameter:**
- `channel` - Twitch-Benutzername
- `layout` - horizontal, vertical, compact
- `show_avatar` - Avatar anzeigen (true/false)
- `show_thumbnail` - Thumbnail anzeigen (true/false)
- `show_title` - Titel anzeigen (true/false)
- `show_game` - Spiel anzeigen (true/false)
- `show_viewers` - Zuschauer anzeigen (true/false)
- `show_language` - Sprache anzeigen (true/false)
- `show_start_time` - Startzeit anzeigen (true/false)

### Multiple Streams Grid
Zeigt mehrere Twitch-Streams in einem Grid an:

```text
[twitch_streams_grid channels="user1,user2,user3" columns="3" layout="grid"]
```

**Parameter:**
- `channels` - Kommagetrennte Liste von Kanälen
- `columns` - Anzahl der Spalten (1-6)
- `layout` - grid, list, masonry
- `gap` - Abstand zwischen Items
- `show_player` - Player anzeigen (true/false)
- `show_info` - Informationen anzeigen (true/false)
- `responsive` - Responsive Breakpoints (true/false)

---

## 📹 VOD & Clips Shortcodes (v1.2.0+)

### VOD (Video on Demand) Shortcode
Zeigt Twitch-Videos oder VODs an:

```text
[twitch_vod channel="username" limit="5" type="archive" layout="grid"]
[twitch_vod video_id="123456" width="100%" height="480" autoplay="false"]
```

**Parameter:**
- `channel` - Twitch-Benutzername (für Liste)
- `video_id` - Spezifische Video-ID
- `limit` - Anzahl der Videos (1-20)
- `type` - archive, upload, highlight
- `width` - Breite des Players
- `height` - Höhe des Players
- `autoplay` - Autoplay (true/false)
- `muted` - Stummgeschaltet (true/false)
- `show_info` - Informationen anzeigen (true/false)
- `show_thumbnail` - Thumbnail anzeigen (true/false)
- `layout` - grid, list, single

### Clips Shortcode
Zeigt Twitch-Clips an:

```text
[twitch_clips channel="username" limit="10" layout="grid"]
[twitch_clips clip_id="FunnyClip123" autoplay="true"]
```

**Parameter:**
- `channel` - Twitch-Benutzername (für Liste)
- `clip_id` - Spezifische Clip-ID
- `limit` - Anzahl der Clips (1-20)
- `width` - Breite des Players
- `height` - Höhe des Players
- `autoplay` - Autoplay (true/false)
- `show_info` - Informationen anzeigen (true/false)
- `layout` - grid, list, single

---

## 🧩 Gutenberg Blocks (v1.1.0+)

### Twitch Stream Block
- **Name**: Twitch Stream
- **Kategorie**: Twitch Stream
- **Funktion**: Einzelnen Stream mit allen Optionen
- **Einstellungen**: Kanal, Größe, Autoplay, Stream-Infos

### Twitch Grid Block
- **Name**: Twitch Stream Grid
- **Kategorie**: Twitch Stream
- **Funktion**: Multiple Streams im Grid
- **Einstellungen**: Kanäle, Spalten, Layout, Player/Info

---

## 🔧 Page Builder Integration (v1.1.0+)

### Unterstützte Page Builder:
- ✅ **Gutenberg** (Native WordPress Blocks)
- ✅ **Elementor** (Widgets mit Inspector Controls)
- ✅ **Oxygen Builder** (Components mit Visual Builder)
- ✅ **Divi Builder** (Modules mit Visual Builder)
- ✅ **Beaver Builder** (Module Support)
- ✅ **Visual Composer/WPBakery** (Shortcode Integration)
- ✅ **Fusion Builder** (Module Support)
- ✅ **SiteOrigin** (Widget Support)
- ✅ **Thrive Architect** (Component Support)

---

## 🍪 Cookie Banner Integration (v1.1.0+)

### Unterstützte Cookie-Systeme:
- ✅ **Borlabs Cookie** (Premium)
- ✅ **Real Cookie Banner** (Pro/Kostenlos)
- ✅ **Complianz** (Kostenlos/Premium)
- ✅ **Cookiebot** (SaaS)
- ✅ **OMR** (Online-Marketing-Regional)
- ✅ **Universal Solution** (Fallback)

### Features:
- **Auto-Detection** aktiver Cookie-Plugins
- **DSGVO-konforme** Platzhalter
- **Zustimmungs-Buttons** für alle Cookie-Typen
- **Responsive Design** für alle Geräte
- **Builder-Kompatibilität**

---

## 📱 Sidebar Widgets (v1.2.0+)

### Twitch VOD Widget
- **Funktion**: Einzelnes Video oder Video-Liste
- **Einstellungen**: Kanal, Video-ID, Anzahl, Typ, Layout
- **Display**: Optimiert für Sidebar-Anzeige

### Twitch Clips Widget
- **Funktion**: Einzelner Clip oder Clip-Liste
- **Einstellungen**: Kanal, Clip-ID, Anzahl, Layout
- **Display**: Optimiert für Sidebar-Anzeige

---

### Admin Settings Page

```php
<?php
function wp_twitch_add_admin_menu() {
    add_options_page(
        'Twitch API Einstellungen',
        'Twitch API',
        'manage_options',
        'twitch-api-settings',
        'wp_twitch_settings_page'
    );
}
add_action('admin_menu', 'wp_twitch_add_admin_menu');

function wp_twitch_settings_init() {
    register_setting('twitch_api', 'twitch_client_id');
    register_setting('twitch_api', 'twitch_client_secret');
}
add_action('admin_init', 'wp_twitch_settings_init');

function wp_twitch_settings_page() {
    ?>
    <div class="wrap">
        <h1>🎮 Twitch API Einstellungen</h1>
        
        <div class="notice notice-info">
            <p><strong>📌 Hinweis:</strong> Du benötigst eine Twitch-App. Erstelle sie hier: 
            <a href="https://dev.twitch.tv/console/apps" target="_blank">Twitch Developer Console</a></p>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('twitch_api'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="twitch_client_id">Client ID</label></th>
                    <td>
                        <input 
                            type="text" 
                            id="twitch_client_id" 
                            name="twitch_client_id" 
                            value="<?php echo esc_attr(get_option('twitch_client_id')); ?>" 
                            class="regular-text"
                        />
                        <p class="description">Deine Twitch Client ID</p>
                    </td>
                </tr>
                
                <tr>
                    <th><label for="twitch_client_secret">Client Secret</label></th>
                    <td>
                        <input 
                            type="password" 
                            id="twitch_client_secret" 
                            name="twitch_client_secret" 
                            value="<?php echo esc_attr(get_option('twitch_client_secret')); ?>" 
                            class="regular-text"
                        />
                        <p class="description">Dein Twitch Client Secret</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Einstellungen speichern'); ?>
        </form>

        <hr>

        <h2>📖 Shortcode Verwendung</h2>
        <code>[twitch_stream channel="deinkanal"]</code>
        
        <h3>Parameter:</h3>
        <ul>
            <li><code>channel</code> - Twitch-Benutzername (erforderlich)</li>
            <li><code>width</code> - Breite (Standard: 100%)</li>
            <li><code>height</code> - Höhe (Standard: 480)</li>
            <li><code>autoplay</code> - Automatisch starten (Standard: true)</li>
            <li><code>muted</code> - Stumm (Standard: false)</li>
        </ul>
    </div>
    <?php
}
?>
```

### Frontend CSS

```css
/* assets/css/frontend.css */

.twitch-stream-container {
    position: relative;
    width: 100%;
    max-width: 100%;
    margin: 20px 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.twitch-stream-container iframe {
    display: block;
    width: 100%;
    border: none;
}

.twitch-offline {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 20px;
    text-align: center;
    border-radius: 8px;
    margin: 20px 0;
}

.twitch-offline p {
    margin: 10px 0;
    font-size: 18px;
}

.twitch-offline a {
    color: #ffffff;
    text-decoration: underline;
    font-weight: bold;
}

.twitch-offline a:hover {
    color: #9146FF;
}

.twitch-error {
    background-color: #fee;
    border-left: 4px solid #f44;
    padding: 15px;
    margin: 20px 0;
    color: #d00;
}

@media (max-width: 768px) {
    .twitch-stream-container {
        margin: 15px 0;
    }
    
    .twitch-offline {
        padding: 30px 15px;
    }
    
    .twitch-offline p {
        font-size: 16px;
    }
}
```

---

## ⚠️ Wichtige Hinweise

### Twitch Parent Parameter

Twitch verlangt beim Einbetten die Angabe der Domain (`parent`-Parameter). Das Plugin erkennt diese automatisch.

**Für lokale Entwicklung:**
```php
$domain = ($_SERVER['HTTP_HOST'] === 'localhost') ? 'localhost' : $_SERVER['HTTP_HOST'];
```

### Token-Sicherheit

- ✅ Client Secret wird sicher in WordPress-Options gespeichert
- ✅ Access Token wird gecacht (50 Tage via Transient API)
- ✅ Keine sensiblen Daten im Frontend

### Performance-Tipps

- ✓ Token-Caching reduziert API-Calls
- ✓ Empfohlen: Object-Caching (Redis/Memcached)
- ✓ Stream-Status kann zusätzlich gecacht werden

---

## 🔗 Verwandte Projekte

### Token-Generator für erweiterte Features

Für **OAuth-Authentication** und erweiterte Token-Verwaltung:

**→ [WP-Twitch-Access-Token](https://github.com/SpeedySwifter/WP-Twitch-Access-Token)**

Bietet:
- OAuth-Authentication für Benutzer-Login
- User-specific Access Tokens
- Erweiterte Token-Verwaltung
- Integration mit diesem Plugin

---

## 📝 Häufige Fragen (FAQ)

### ❓ Wie lange ist der Access Token gültig?

Der Token ist standardmäßig **~60 Tage** gültig und wird automatisch gecacht.

### ❓ Funktioniert das Plugin mit Elementor/Gutenberg?

✅ **Ja!** Shortcodes funktionieren in allen Page Buildern:
- ✓ Gutenberg (Shortcode-Block)
- ✓ Elementor (Shortcode-Widget)
- ✓ WPBakery
- ✓ Divi

### ❓ Kann ich mehrere Streams gleichzeitig einbinden?

✅ **Ja!** Einfach mehrere Shortcodes verwenden:

```text
[twitch_stream channel="shroud"]
[twitch_stream channel="ninja"]
[twitch_stream channel="pokimane"]
```

### ❓ Warum funktioniert der Player nicht?

**Mögliche Ursachen:**
1. ⚠️ **Client ID/Secret falsch** – Prüfe die Einstellungen
2. ⚠️ **Domain nicht registriert** – Füge Domain in Twitch-App hinzu
3. ⚠️ **HTTPS fehlt** – Twitch empfiehlt HTTPS
4. ⚠️ **Ad-Blocker** – Kann Twitch-Embed blockieren

### ❓ Kann ich das Design anpassen?

✅ **Ja!** Nutze eigenes CSS:

```css
/* In deinem Theme CSS */
.twitch-stream-container {
    border: 2px solid #9146FF;
    border-radius: 12px;
}

.twitch-offline {
    background: linear-gradient(135deg, #FF0080 0%, #7928CA 100%);
}
```

### ❓ Zeigt das Plugin Zuschauerzahlen an?

📌 **Ja!** Ab Version 1.1.0 werden Zuschauerzahlen in den Stream-Infos angezeigt.

```text
[twitch_stream_info channel="username" show_viewers="true"]
```

Siehe [Roadmap](#-roadmap) für alle verfügbaren Features.

---

## 🗺️ Roadmap

### ✅ Version 1.2.0 (Abgeschlossen)
- [x] Basic Shortcode
- [x] Live-Status Erkennung
- [x] Admin Settings Page
- [x] Token-Caching
- [x] Stream-Infos (Titel, Spiel, Zuschauer, Avatar, Live Badge)
- [x] Multiple Streams Grid-Layout
- [x] Gutenberg Block (Stream & Grid)
- [x] Elementor Widgets (Stream & Grid)
- [x] Oxygen Builder Components
- [x] Divi Builder Modules
- [x] Universal Page Builder Compatibility
- [x] WordPress Widgets (Stream & Grid)
- [x] Cookie Banner Integration (DSGVO)
- [x] Multi-Language (DE/EN)
- [x] Dark Mode Support
- [x] Responsive Design
- [x] VOD (Video on Demand) Support
- [x] Clips einbinden
- [x] Sidebar Widgets (VOD & Clips)

### 🚧 Version 1.3.0 (In Entwicklung)
- [ ] REST API Endpoint
- [ ] Webhook-Support (EventSub)
- [ ] Advanced Analytics
- [ ] Stream-Recording Integration
- [ ] Multi-Channel Dashboard
- [ ] Custom CSS Builder
- [ ] Advanced Caching Options

### 🔮 Version 2.0.0 (Geplant)
- [ ] Twitch Chat Integration
- [ ] Donation/Subscription Buttons
- [ ] Stream-Recording Download
- [ ] Advanced Analytics Dashboard
- [ ] Multi-Language Support (EN/DE/FR/ES)
- [ ] WooCommerce Integration
- [ ] Membership Plugin Integration
- [ ] Advanced Shortcode Builder
- [ ] Visual Stream Scheduler
- [ ] Mobile App Integration

---

## 📊 Feature-Übersicht

### 🎮 Core Features (v1.0+)
| Feature | Status | Version |
|---------|--------|--------|
| Basic Shortcode | ✅ Fertig | 1.0.0 |
| Live-Status Erkennung | ✅ Fertig | 1.0.0 |
| Admin Settings Page | ✅ Fertig | 1.0.0 |
| Token-Caching | ✅ Fertig | 1.0.0 |

### 🎯 Enhanced Features (v1.1+)
| Feature | Status | Version |
|---------|--------|--------|
| Stream-Infos (Titel, Spiel, Zuschauer) | ✅ Fertig | 1.1.0 |
| Multiple Streams Grid-Layout | ✅ Fertig | 1.1.0 |
| Gutenberg Block | ✅ Fertig | 1.1.0 |
| Page Builder Integration | ✅ Fertig | 1.1.0 |
| Cookie Banner Integration | ✅ Fertig | 1.1.0 |
| Dark Mode Support | ✅ Fertig | 1.1.0 |

### � VOD & Clips Features (v1.2+)
| Feature | Status | Version |
|---------|--------|--------|
| VOD (Video on Demand) Support | ✅ Fertig | 1.2.0 |
| Clips einbinden | ✅ Fertig | 1.2.0 |
| Sidebar Widgets (VOD & Clips) | ✅ Fertig | 1.2.0 |
| Enhanced API Integration | ✅ Fertig | 1.2.0 |
| Responsive VOD/Clips Design | ✅ Fertig | 1.2.0 |
| Video Metadata Display | ✅ Fertig | 1.2.0 |

### �🔧 Builder Integration (v1.1+)
| Builder | Status | Version |
|--------|--------|--------|
| Gutenberg | ✅ Fertig | 1.1.0 |
| Elementor | ✅ Fertig | 1.1.0 |
| Oxygen Builder | ✅ Fertig | 1.1.0 |
| Divi Builder | ✅ Fertig | 1.1.0 |
| Beaver Builder | ✅ Fertig | 1.1.0 |
| Visual Composer | ✅ Fertig | 1.1.0 |
| Fusion Builder | ✅ Fertig | 1.1.0 |
| SiteOrigin | ✅ Fertig | 1.1.0 |
| Thrive Architect | ✅ Fertig | 1.1.0 |

### 🍪 Cookie Integration (v1.1+)
| Cookie-System | Status | Version |
|--------------|--------|--------|
| Borlabs Cookie | ✅ Fertig | 1.1.0 |
| Real Cookie Banner | ✅ Fertig | 1.1.0 |
| Complianz | ✅ Fertig | 1.1.0 |
| Cookiebot | ✅ Fertig | 1.1.0 |
| OMR | ✅ Fertig | 1.1.0 |
| Universal Solution | ✅ Fertig | 1.1.0 |

### 🚀 Advanced Features (v1.3+)
| Feature | Status | Version |
|---------|--------|--------|
| REST API Endpoint | 🚧 In Entwicklung | 1.3.0 |
| Webhook-Support (EventSub) | 🚧 In Entwicklung | 1.3.0 |
| Advanced Analytics | 🚧 In Entwicklung | 1.3.0 |
| Stream-Recording Integration | 🚧 In Entwicklung | 1.3.0 |
| Multi-Channel Dashboard | 🚧 In Entwicklung | 1.3.0 |
| Custom CSS Builder | 🚧 In Entwicklung | 1.3.0 |
| Advanced Caching Options | 🚧 In Entwicklung | 1.3.0 |

### 🔮 Future Features (v2.0+)
| Feature | Status | Version |
|---------|--------|--------|
| Twitch Chat Integration | 📋 Geplant | 2.0.0 |
| Donation/Subscription Buttons | 📋 Geplant | 2.0.0 |
| Stream-Recording Integration | 📋 Geplant | 2.0.0 |
| WooCommerce Integration | 📋 Geplant | 2.0.0 |
| Mobile App Integration | 📋 Geplant | 2.0.0 |

---

## 🐛 Bekannte Issues

| Issue | Status | Workaround |
|-------|--------|----------|
| Parent-Parameter bei Localhost | 🟡 Open | Manuell anpassen |
| HTTPS-Warnung | 🟢 Known | HTTPS verwenden |
| Ad-Blocker blockiert Embed | 🟡 External | Whitelist hinzufügen |

---

## 🤝 Contributing

Contributions sind herzlich willkommen! 🎉

1. **Fork** das Repository
2. **Branch** erstellen (`git checkout -b feature/AmazingFeature`)
3. **Commit** (`git commit -m 'Add: Amazing Feature'`)
4. **Push** (`git push origin feature/AmazingFeature`)
5. **Pull Request** öffnen

---

## 📄 Lizenz

```
MIT License

Copyright (c) 2024 SpeedySwifter

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 👤 Autor

<div align="center">

**SpeedySwifter**

[![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/SpeedySwifter)
[![Twitter](https://img.shields.io/badge/Twitter-1DA1F2?style=for-the-badge&logo=twitter&logoColor=white)](https://twitter.com/SpeedySwifter)
[![Twitch](https://img.shields.io/badge/Twitch-9146FF?style=for-the-badge&logo=twitch&logoColor=white)](https://twitch.tv/SpeedySwifter)

</div>

---

## ⭐ Support

<div align="center">

**Hat dir dieses Plugin geholfen?**

Gib dem Repository einen ⭐ Stern auf GitHub!

[![GitHub Stars](https://img.shields.io/github/stars/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/stargazers)

### 💖 Sponsor werden

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-FFDD00?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/speedyswifter)
[![PayPal](https://img.shields.io/badge/PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://paypal.me/svenhajer)

</div>

---

## 📊 Statistiken

<div align="center">

![GitHub repo size](https://img.shields.io/github/repo-size/SpeedySwifter/WordPress-Twitch-Stream-Plugin)
![GitHub commit activity](https://img.shields.io/github/commit-activity/m/SpeedySwifter/WordPress-Twitch-Stream-Plugin)
![GitHub last commit](https://img.shields.io/github/last-commit/SpeedySwifter/WordPress-Twitch-Stream-Plugin)

</div>

---

## 🔗 Weitere Ressourcen

- [Twitch Developer Portal](https://dev.twitch.tv/)
- [Twitch API Documentation](https://dev.twitch.tv/docs/api/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [OAuth 2.0 Specification](https://oauth.net/2/)

---

<div align="center">

**Made with 💜 for the WordPress & Twitch Community**

---

⭐ **Star dieses Repo wenn es dir geholfen hat!** ⭐

</div>
