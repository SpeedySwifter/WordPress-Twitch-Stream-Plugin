# 🎮 WordPress Twitch Stream Plugin v1.7.0

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-6.9.1-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Twitch](https://img.shields.io/badge/Twitch_API-9146FF?style=for-the-badge&logo=twitch&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

[![GitHub Stars](https://img.shields.io/github/stars/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/forks)
[![GitHub Issues](https://img.shields.io/github/issues/SpeedySwifter/WordPress-Twitch-Stream-Plugin)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)

**The Ultimate WordPress Plugin for Twitch Stream Integration**

[🚀 Features](#-features) • [📦 Installation](#-installation) • [🧩 Usage](#-usage) • [📋 Shortcodes](#-shortcodes) • [⚙️ Admin](#-admin-settings) • [🌍 Languages](#-languages)

</div>

---

## 📌 What is this?

The **WordPress Twitch Stream Plugin v1.7.0** is a comprehensive solution for integrating Twitch streams into WordPress websites. It provides everything from basic stream embedding to advanced features like mobile apps, scheduling, analytics, and more.

### ✨ Core Features

- ✅ **Simple Shortcodes** – `[twitch_stream channel="yourchannel"]`
- 🔴 **Live Status Detection** – Automatic checking if stream is online
- 📺 **Responsive Player** – Twitch embed adapts to all screen sizes
- ⚙️ **Admin Panel** – Comfortable settings page for API credentials
- 🔐 **Secure API Integration** – Uses official Twitch Helix API
- 💾 **Token Caching** – Reduces API calls through intelligent caching
- 🎨 **Customizable** – CSS classes for individual styling
- 🧩 **WordPress 6.9.1 Compatible** – Tested with current WP version
- 🎯 **Stream Info** – Title, game, viewers, avatar, live badge
- 📱 **Multiple Streams Grid** – Multiple streams in grid layout
- 🧩 **Gutenberg Blocks** – Native WordPress Block Editor integration
- 🔧 **Page Builder Support** – Elementor, Oxygen, Divi, Beaver Builder & more
- 🍪 **Cookie Banner Integration** – GDPR compliant with 6 cookie systems

---

## 🚀 Advanced Features (v1.7.0)

### 📱 **Mobile App Integration**
- **Progressive Web App (PWA)** with complete manifest
- **Service Worker** for offline functionality and caching
- **Push Notifications** with VAPID key support
- **Mobile-optimized interface** with touch gestures
- **App install prompts** and smart banners
- **Offline detection** and synchronization

### 📅 **Visual Stream Scheduler**
- **Interactive calendar** with FullCalendar.js integration
- **Drag-and-drop scheduling** and rescheduling
- **Multiple view modes** (Calendar, List, Timeline)
- **Real-time status tracking** (Scheduled/Live/Completed)
- **Recurring stream patterns** (Daily/Weekly/Monthly)
- **Advanced filtering** by date, status, category

### 🛠️ **Advanced Shortcode Builder**
- **Interactive GUI** for building Twitch shortcodes
- **Live preview** with auto-refresh
- **Support for all 13+ plugin shortcodes**
- **Category-based organization**
- **Preset templates** for quick start
- **Copy-to-clipboard functionality**

### 🔒 **Membership Plugin Integration**
- **Support for 6 major membership plugins**
- **MemberPress, RCP, PMPro, WooCommerce Memberships**
- **Ultimate Member, s2Member integration**
- **4-tier membership system** (Free/Basic/Premium/VIP)
- **Content restrictions** based on membership level
- **Membership badges** and visual indicators

### 🌍 **Multi-Language Support (7 Languages)**
- **🇺🇸 English (en_US)**
- **🇩🇪 Deutsch (de_DE)**
- **🇫🇷 Français (fr_FR)**
- **🇪🇸 Español (es_ES)**
- **🇷🇺 Русский (ru_RU)**
- **🇵🇹 Português (pt_PT)**
- **🇯🇵 日本語 (ja_JP)**

### 💰 **Donation Integration**
- **Buy Me a Coffee** and PayPal buttons
- **Customizable donation forms**
- **Donation goals and progress tracking**
- **Responsive design** with dark mode
- **Donation statistics** and analytics

### 💬 **Twitch Chat Integration**
- **Advanced chat integration** with emoji picker
- **Message moderation** and command processing
- **Chat themes** and customization options
- **Real-time message polling**
- **User badges and roles display**

### 📥 **Stream Recording Download**
- **VOD download functionality**
- **Stream recording management**
- **Download progress tracking**
- **Video player controls**
- **Download permissions** and access control

### 📊 **Advanced Analytics Dashboard**
- **Stream analytics** and performance metrics
- **Viewer statistics** and engagement tracking
- **Real-time data visualization**
- **Customizable charts** and reports
- **Export functionality** for data analysis

### 🛒 **WooCommerce Integration**
- **Stream-linked products**
- **Purchase-triggered stream access**
- **E-commerce integration** for memberships
- **Order status synchronization**
- **Revenue tracking** and analytics

---

## 🎯 Use Cases

### 📡 Perfect For

- 🎮 **Gaming Websites** – Display your own Twitch stream on website
- 🏆 **eSports Teams** – Embed live matches directly
- 🎥 **Content Creators** – Stream integration in WordPress blog
- 📰 **News Portals** – Broadcast event streams live
- 🎪 **Event Sites** – Stream conferences & tournaments
- 📱 **Mobile Apps** – PWA with offline capabilities
- 🔒 **Membership Sites** – Content restrictions and access control
- 📅 **Stream Networks** – Schedule and manage multiple streams

### 🔧 What it does

```text
✓ Automatically checks if stream is live
✓ Shows Twitch player only for live streams
✓ Shows offline message when stream not active
✓ Fully responsive for all devices
✓ Mobile app with push notifications
✓ Visual scheduling with calendar interface
✓ Membership-based content restrictions
✓ Multi-language support (7 languages)
✓ Advanced analytics and reporting
✓ PWA with offline functionality
```

---

## 📦 Installation

### Option 1: Manual (ZIP Upload)

1. **Download plugin** as ZIP
2. In WordPress: **Plugins → Install → Upload Plugin**
3. Select ZIP file and install
4. **Activate** plugin

### Option 2: FTP/SFTP

```bash
# Clone repository
git clone https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin.git

# Move folder to wp-content/plugins/
mv WordPress-Twitch-Stream-Plugin /path/to/wordpress/wp-content/plugins/
```

Then activate in WordPress admin under **Plugins**.

---

## 🔑 Twitch API Setup

### 1️⃣ Create Twitch App

You need a **Twitch Developer Application** to get API access:

1. Go to: [https://dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Click **"Register Your Application"**
3. Fill the form:

```
Name:                 Your WordPress Site
OAuth Redirect URLs:  https://your-domain.com
Category:             Website Integration
```

4. **Save** and note:
   - ✅ **Client ID**
   - ✅ **Client Secret** (shown only once!)

### 2️⃣ Enter Credentials in WordPress

1. In WordPress Admin: **Settings → Twitch API**
2. Enter **Client ID**
3. Enter **Client Secret**
4. **Save Changes**

✅ Done! The plugin is now ready to use.

---

## 🧩 Usage

### Basic Shortcode

```text
[twitch_stream channel="shroud"]
```

### With Options

```text
[twitch_stream channel="shroud" width="100%" height="480"]
```

### Advanced Mobile App

```text
[twitch_mobile_app theme="dark" show_notifications="true"]
```

### Stream Scheduler

```text
[twitch_stream_scheduler channel="yourchannel" view="calendar"]
```

### Membership Content

```text
[twitch_membership_content level="premium"]
Your premium content here
[/twitch_membership_content]
```

---

## 📋 Shortcodes Reference

### Core Shortcodes

| Shortcode | Description | Example |
|-----------|-------------|---------|
| `[twitch_stream]` | Basic stream embed | `[twitch_stream channel="shroud"]` |
| `[twitch_chat]` | Standalone chat | `[twitch_chat channel="shroud"]` |
| `[twitch_follow_button]` | Follow button | `[twitch_follow_button channel="shroud"]` |
| `[twitch_subscribe_button]` | Subscribe button | `[twitch_subscribe_button channel="shroud"]` |
| `[twitch_clips]` | Channel clips | `[twitch_clips channel="shroud" limit="10"]` |
| `[twitch_vod]` | Past broadcasts | `[twitch_vod channel="shroud" type="archive"]` |

### Advanced Shortcodes

| Shortcode | Description | Example |
|-----------|-------------|---------|
| `[twitch_mobile_app]` | Mobile app interface | `[twitch_mobile_app theme="dark"]` |
| `[twitch_stream_scheduler]` | Visual scheduler | `[twitch_stream_scheduler view="calendar"]` |
| `[twitch_shortcode_builder]` | Shortcode builder GUI | `[twitch_shortcode_builder show_preview="true"]` |
| `[twitch_membership_content]` | Restricted content | `[twitch_membership_content level="vip"]` |
| `[twitch_donations]` | Donation integration | `[twitch_donations type="both"]` |
| `[twitch_chat_integration]` | Advanced chat | `[twitch_chat_integration theme="dark"]` |
| `[twitch_recording_download]` | VOD downloads | `[twitch_recording_download limit="10"]` |
| `[twitch_analytics]` | Analytics dashboard | `[twitch_analytics time_range="7d"]` |

### Utility Shortcodes

| Shortcode | Description | Example |
|-----------|-------------|---------|
| `[twitch_pwa_install]` | PWA install button | `[twitch_pwa_install text="Install App"]` |
| `[twitch_mobile_menu]` | Mobile navigation | `[twitch_mobile_menu position="left"]` |
| `[twitch_mobile_streams]` | Mobile stream grid | `[twitch_mobile_streams limit="10"]` |
| `[twitch_push_notifications]` | Notification settings | `[twitch_push_notifications show_settings="true"]` |
| `[twitch_upcoming_streams]` | Upcoming streams | `[twitch_upcoming_streams limit="5"]` |
| `[twitch_stream_schedule]` | Weekly schedule | `[twitch_stream_schedule days="7"]` |

---

## ⚙️ Admin Settings

### Main Settings Page
**WordPress Admin → Settings → Twitch API**

- **Client ID & Secret** – Twitch API credentials
- **Caching Options** – Token and data caching settings
- **Display Options** – Default player dimensions and themes

### Mobile App Settings
**WordPress Admin → Twitch Dashboard → Mobile App**

- **PWA Configuration** – App manifest and service worker settings
- **Push Notifications** – VAPID keys and notification preferences
- **Theme Settings** – Mobile app appearance customization

### Stream Scheduler
**WordPress Admin → Twitch Dashboard → Stream Scheduler**

- **Calendar Settings** – Default view and time zone
- **Notification Settings** – Email and push notification preferences
- **Recurring Patterns** – Automated stream scheduling

### Membership Integration
**WordPress Admin → Twitch Dashboard → Membership**

- **Plugin Detection** – Auto-detection of membership plugins
- **Level Mapping** – Map membership levels to access tiers
- **Content Restrictions** – Configure access control rules

---

## 📂 Plugin Structure

```
WordPress-Twitch-Stream-Plugin/
│
├── 📄 wp-twitch-stream.php                    # Main plugin file
├── 📄 README.md                               # Documentation (7 languages)
├── 📄 LICENSE                                 # MIT License
│
├── 📁 admin/
│   ├── 📄 settings-page.php                   # Admin settings page
│   └── 📄 admin-styles.css                    # Admin styling
│
├── 📁 includes/
│   ├── 📄 twitch-api.php                      # API handler
│   ├── 📄 shortcode.php                       # Shortcode logic
│   ├── 📄 token-manager.php                   # Token caching
│   ├── 📄 gutenberg-block.php                 # Gutenberg blocks
│   ├── 📄 page-builder-compatibility.php      # Page builder integration
│   ├── 📄 cookie-integration.php              # Cookie banner integration
│   ├── 📄 sidebar-widgets.php                 # VOD & clips widgets
│   ├── 📄 donation-integration.php            # Donation system
│   ├── 📄 twitch-chat-integration.php         # Advanced chat
│   ├── 📄 stream-recording-download.php       # VOD downloads
│   ├── 📄 advanced-analytics-dashboard.php    # Analytics system
│   ├── 📄 multi-language-support.php          # i18n support
│   ├── 📄 woocommerce-integration.php         # eCommerce integration
│   ├── 📄 membership-plugin-integration.php   # Membership system
│   ├── 📄 advanced-shortcode-builder.php      # Shortcode builder
│   ├── 📄 visual-stream-scheduler.php         # Calendar scheduler
│   └── 📄 mobile-app-integration.php          # PWA & mobile app
│
├── 📁 assets/
│   ├── 📁 css/
│   │   ├── 📄 frontend.css                     # Frontend styles
│   │   ├── 📄 block.css                        # Gutenberg block styles
│   │   ├── 📄 page-builder-compatibility.css   # Page builder styles
│   │   ├── 📄 cookie-integration.css           # Cookie integration styles
│   │   ├── 📄 vod-clips.css                    # VOD & clips styles
│   │   ├── 📄 donations.css                    # Donation system styles
│   │   ├── 📄 twitch-chat.css                  # Chat integration styles
│   │   ├── 📄 recording-download.css           # Download system styles
│   │   ├── 📄 analytics-dashboard.css          # Analytics styles
│   │   ├── 📄 language-support.css             # Multi-language styles
│   │   ├── 📄 woocommerce-integration.css      # eCommerce styles
│   │   ├── 📄 membership-integration.css       # Membership styles
│   │   ├── 📄 shortcode-builder.css            # Builder interface styles
│   │   ├── 📄 stream-scheduler.css             # Calendar styles
│   │   └── 📄 mobile-app.css                   # Mobile app styles
│   └── 📁 js/
│       ├── 📄 player.js                        # Player functions
│       ├── 📄 block.js                         # Gutenberg block JavaScript
│       ├── 📄 oxygen-builder.js                # Oxygen builder JS
│       ├── 📄 divi-builder.js                  # Divi builder JS
│       ├── 📄 donations.js                     # Donation system JS
│       ├── 📄 twitch-chat.js                   # Chat integration JS
│       ├── 📄 recording-download.js            # Download system JS
│       ├── 📄 analytics-dashboard.js           # Analytics JS
│       ├── 📄 language-support.js              # Multi-language JS
│       ├── 📄 woocommerce-integration.js       # eCommerce JS
│       ├── 📄 membership-integration.js        # Membership JS
│       ├── 📄 shortcode-builder.js             # Builder interface JS
│       ├── 📄 stream-scheduler.js              # Calendar JS
│       └── 📄 mobile-app.js                    # Mobile app JS
│
├── 📁 docs/
│   ├── 📄 cookie-banner-integration.md        # Cookie integration tutorial
│   ├── 📄 membership-plugin-integration.md    # Membership setup guide
│   ├── 📄 mobile-app-setup.md                 # PWA configuration
│   └── 📄 api-reference.md                    # Complete API reference
│
├── 📁 languages/
│   ├── 📄 wp-twitch-stream-en_US.po
│   ├── 📄 wp-twitch-stream-en_US.mo
│   ├── 📄 wp-twitch-stream-de_DE.po
│   ├── 📄 wp-twitch-stream-de_DE.mo
│   ├── 📄 wp-twitch-stream-fr_FR.po
│   ├── 📄 wp-twitch-stream-fr_FR.mo
│   ├── 📄 wp-twitch-stream-es_ES.po
│   ├── 📄 wp-twitch-stream-es_ES.mo
│   ├── 📄 wp-twitch-stream-ru_RU.po
│   ├── 📄 wp-twitch-stream-ru_RU.mo
│   ├── 📄 wp-twitch-stream-pt_PT.po
│   ├── 📄 wp-twitch-stream-pt_PT.mo
│   ├── 📄 wp-twitch-stream-ja_JP.po
│   └── 📄 wp-twitch-stream-ja_JP.mo
│
└── 📁 templates/
    ├── 📄 offline-page.html                   # PWA offline page
    └── 📄 mobile-app-manifest.json            # PWA manifest template
```

---

## 🌍 Languages / Sprachen

The plugin supports **7 languages** with complete translations:

### 🇺🇸 English (en_US) - Default
- Complete English documentation and interface

### 🇩🇪 Deutsch (de_DE)
- Vollständige deutsche Dokumentation und Benutzeroberfläche

### 🇫🇷 Français (fr_FR)
- Documentation et interface utilisateur complètes en français

### 🇪🇸 Español (es_ES)
- Documentación e interfaz de usuario completas en español

### 🇷🇺 Русский (ru_RU)
- Полная документация и пользовательский интерфейс на русском

### 🇵🇹 Português (pt_PT)
- Documentação e interface do usuário completas em português

### 🇯🇵 日本語 (ja_JP)
- 完全な日本語のドキュメントとユーザーインターフェース

---

## 📊 Version History

### v1.7.0 - Mobile App Integration 🚀
- 📱 Progressive Web App (PWA) with offline support
- 🔔 Push notifications with VAPID key configuration
- 👆 Touch gestures and mobile-optimized interface
- 📅 Visual stream scheduler with calendar interface
- 🛠️ Advanced shortcode builder GUI
- 🔒 Membership plugin integration (6 plugins supported)
- 🌍 Multi-language support (7 languages)

### v1.6.0 - Visual Stream Scheduler 📅
- 📅 Interactive calendar with FullCalendar.js
- 🖱️ Drag-and-drop stream scheduling
- 📋 Multiple view modes (Calendar/List/Timeline)
- 🔄 Real-time status tracking and updates
- 🔁 Recurring stream patterns support
- 🎯 Advanced filtering and search capabilities

### v1.5.0 - Advanced Shortcode Builder 🛠️
- 🎨 Interactive GUI for building shortcodes
- 👀 Live preview with auto-refresh
- 📋 Support for all plugin shortcodes (13+)
- 📂 Category-based organization
- 💾 Preset templates and quick-start options
- 📋 Copy-to-clipboard functionality

### v1.4.0 - Membership Plugin Integration 🔒
- 👥 Support for 6 major membership plugins
- 🏆 4-tier membership system (Free/Basic/Premium/VIP)
- 🚫 Content restrictions based on membership level
- 🏷️ Membership badges and visual indicators
- 🔐 Access control and permission management

### v1.3.0 - Advanced Features Suite 💎
- 💰 Donation Integration (Buy Me a Coffee + PayPal)
- 💬 Twitch Chat Integration with emoji support
- 📥 Stream Recording Download functionality
- 📊 Advanced Analytics Dashboard with charts
- 🌍 Multi-Language Support (EN/DE/FR/ES/RU/PT/JA)

### v1.2.0 - WooCommerce Integration 🛒
- 🛒 eCommerce integration for stream-linked products
- 💳 Purchase-triggered stream access
- 📈 Revenue tracking and order synchronization
- 🏪 WooCommerce membership and subscription support

### v1.1.0 - Extended Content Support 🎬
- 🎬 VOD (Video on Demand) support with archives
- 🎞️ Twitch Clips integration and embedding
- 📱 Sidebar widgets for VODs and clips
- 🧩 Extended page builder compatibility

### v1.0.0 - Core Release 🎯
- ✅ Basic Twitch stream embedding
- 🔴 Live status detection
- 📺 Responsive player integration
- ⚙️ Admin settings panel
- 🔐 Secure API integration

---

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **Twitch** for providing the amazing streaming platform and API
- **WordPress** for the incredible CMS foundation
- **FullCalendar.js** for the calendar functionality
- **All contributors** who help make this plugin better

---

## 📞 Support

- 📧 **Email**: support@speedyswifter.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)
- 📖 **Documentation**: [Wiki](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/wiki)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/discussions)

---

<div align="center">

**Made with ❤️ by [SpeedySwifter](https://github.com/SpeedySwifter)**

⭐ If you find this plugin helpful, please give it a star!

</div>

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

### ✅ Version 1.7.0 (Abgeschlossen - Current Release)
- [x] **Mobile App Integration** - Progressive Web App (PWA) with offline support
- [x] **Push Notifications** - VAPID key configuration and browser notifications
- [x] **Touch Gestures** - Mobile-optimized interface with swipe controls
- [x] **Visual Stream Scheduler** - Calendar interface for stream planning
- [x] **Advanced Shortcode Builder** - GUI for creating custom shortcodes
- [x] **Membership Plugin Integration** - Support for 6 major membership plugins
- [x] **Multi-Language Support** - Complete translations in 7 languages
- [x] **Donation Integration** - Buy Me a Coffee and PayPal buttons
- [x] **Twitch Chat Integration** - Advanced chat with emoji support
- [x] **Stream Recording Download** - VOD download functionality
- [x] **Advanced Analytics Dashboard** - Real-time metrics and charts
- [x] **WooCommerce Integration** - eCommerce integration for memberships
- [x] **Cookie Banner Integration** - GDPR compliant with 6 cookie systems
- [x] **VOD Support** - Video on Demand with archives and highlights
- [x] **Clips Integration** - Twitch clips embedding and management
- [x] **Sidebar Widgets** - VOD and clips widgets for sidebars
- [x] **Page Builder Support** - Elementor, Oxygen, Divi, Beaver Builder & more
- [x] **Gutenberg Blocks** - Native WordPress block editor integration
- [x] **REST API Endpoints** - Programmatic access to plugin features
- [x] **Webhook Support** - EventSub integration for real-time updates
- [x] **Multi-Channel Dashboard** - Manage multiple Twitch channels
- [x] **Custom CSS Builder** - Visual CSS customization interface
- [x] **Advanced Caching** - Performance optimization and caching options
- [x] **Dark Mode Support** - Complete dark theme implementation
- [x] **Responsive Design** - Mobile-first responsive layouts
- [x] **Basic Stream Embedding** - Core Twitch integration functionality
- [x] **Live Status Detection** - Automatic stream status checking
- [x] **Admin Settings Panel** - Comprehensive configuration interface
- [x] **Token Caching System** - Intelligent API token management

### � Version 1.8.0 (Geplant - Next Release)
- [ ] **AI-Powered Features** - Smart stream recommendations and analytics
- [ ] **Advanced Monetization** - Subscription models and premium features
- [ ] **Cross-Platform Integration** - YouTube, Facebook Gaming support
- [ ] **Enterprise Features** - White-label solutions and advanced security
- [ ] **Performance Enhancements** - Advanced caching and optimization
- [ ] **Developer Tools** - Enhanced API and webhook capabilities

### 🔮 Version 2.0.0 (Langfristig geplant)
- [ ] **AI Stream Assistant** - AI-powered stream management and optimization
- [ ] **Advanced Analytics Suite** - Enterprise-level reporting and insights
- [ ] **Mobile App Development** - Dedicated mobile applications
- [ ] **Cloud Integration** - Advanced cloud storage and CDN support
- [ ] **API Rate Limiting** - Advanced quota management and scaling
- [ ] **White-Label Solutions** - Custom branding and licensing options

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
| REST API Endpoint | ✅ Fertig | 1.3.0 |
| Webhook-Support (EventSub) | ✅ Fertig | 1.3.0 |
| Advanced Analytics | ✅ Fertig | 1.3.0 |
| Stream-Recording Integration | ✅ Fertig | 1.3.0 |
| Multi-Channel Dashboard | ✅ Fertig | 1.3.0 |
| Custom CSS Builder | ✅ Fertig | 1.3.0 |
| Advanced Caching Options | ✅ Fertig | 1.3.0 |
| Donation Integration | ✅ Fertig | 1.3.1 |

### 🔮 Future Features (v2.0+)
| Feature | Status | Version |
|---------|--------|--------|
| Twitch Chat Integration | 📋 Geplant | 1.4.0 |
| Donation/Subscription Buttons (erweitert) | 📋 Geplant | 1.4.0 |
| Stream-Recording Download | 📋 Geplant | 1.4.0 |
| Advanced Analytics Dashboard | 📋 Geplant | 1.4.0 |
| Multi-Language Support (EN/DE/FR/ES) | 📋 Geplant | 1.4.0 |
| WooCommerce Integration | 📋 Geplant | 1.4.0 |
| Membership Plugin Integration | 📋 Geplant | 1.4.0 |
| Advanced Shortcode Builder | 📋 Geplant | 1.4.0 |
| Visual Stream Scheduler | 📋 Geplant | 1.4.0 |
| Mobile App Integration | 📋 Geplant | 1.4.0 |
| AI-Powered Stream Recommendations | 📋 Geplant | 2.0.0 |
| Advanced Monetization Features | 📋 Geplant | 2.0.0 |
| Cross-Platform Integration | 📋 Geplant | 2.0.0 |
| Advanced User Management | 📋 Geplant | 2.0.0 |
| White-Label Solutions | 📋 Geplant | 2.0.0 |
| Enterprise Analytics Suite | 📋 Geplant | 2.0.0 |
| API Rate Limiting & Quotas | 📋 Geplant | 2.0.0 |
| Advanced Security Features | 📋 Geplant | 2.0.0 |
| Cloud Storage Integration | 📋 Geplant | 2.0.0 |
| Mobile App Development | 📋 Geplant | 2.0.0 |

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

**SpeedySwifter - Sven Hajer**

<a href="https://hajer.dev">
  <img src="https://hajer.dev/logo-hajerdev.svg" alt="hajer.dev" width="120" height="120">
</a>

<table>
  <tr>
    <td align="center" width="50%">
      <a href="https://hajer.dev">
        <img src="https://img.shields.io/badge/Website-hajer.dev-blue?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwYXRoIGQ9Ik0xMCAxMXYxMk0xMCAxMGMwIDYuNjI3LTUuMzczIDEyLTEyIDEyUzAgMTguMzczIDAgMTIgNS4zNzMgMTIgMTJ6Ii8+PC9zdmc+" alt="Website">
      </a>
    </td>
    <td align="center" width="50%">
      <a href="https://github.com/SpeedySwifter">
        <img src="https://img.shields.io/badge/GitHub-SpeedySwifter-181717?style=for-the-badge&logo=github" alt="GitHub">
      </a>
    </td>
  </tr>
</table>

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
