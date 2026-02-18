=== Live Stream Integration ===
Contributors: speedyswifter
Donate link: https://www.paypal.com/donate/?hosted_button_id=YOUR_BUTTON_ID
Tags: twitch, streaming, live stream, twitch.tv, embed, video, gaming, esports, widget, shortcode, chat, clips, vod, analytics, mobile, pwa
Requires at least: 5.8
Tested up to: 6.9.1
Stable tag: 1.7.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.4

The ultimate WordPress plugin for Twitch stream integration with mobile app, scheduling, analytics, and multi-language support.

== Description ==

**WordPress Twitch Stream Plugin v1.7.0** is a comprehensive solution for integrating Twitch streams into WordPress websites. It provides everything from basic stream embedding to advanced features like mobile apps, scheduling, analytics, and more.

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

### 🚀 Advanced Features (v1.7.0)

#### 📱 Mobile App Integration
- Progressive Web App (PWA) with offline support
- Push notifications with VAPID key configuration
- Touch gestures and mobile-optimized interface
- App install prompts and smart banners

#### 📅 Visual Stream Scheduler
- Interactive calendar with FullCalendar.js integration
- Drag-and-drop scheduling and rescheduling
- Multiple view modes (Calendar, List, Timeline)
- Real-time status tracking (Scheduled/Live/Completed)

#### 🛠️ Advanced Shortcode Builder
- Interactive GUI for building Twitch shortcodes
- Live preview with auto-refresh
- Support for all 13+ plugin shortcodes
- Category-based organization and preset templates

#### 🔒 Membership Plugin Integration
- Support for 6 major membership plugins
- 4-tier membership system (Free/Basic/Premium/VIP)
- Content restrictions based on membership level
- Membership badges and visual indicators

#### 🌍 Multi-Language Support
- Complete translations in 7 languages
- English, German, French, Spanish, Russian, Portuguese, Japanese

#### 💰 Additional Features
- Donation Integration (Buy Me a Coffee + PayPal)
- Twitch Chat Integration with emoji support
- Stream Recording Download functionality
- Advanced Analytics Dashboard with charts
- WooCommerce Integration for e-commerce features

### 🎯 Perfect For

- **Gaming Websites** – Display your own Twitch stream on website
- **eSports Teams** – Embed live matches directly
- **Content Creators** – Stream integration in WordPress blog
- **News Portals** – Broadcast event streams live
- **Event Sites** – Stream conferences & tournaments
- **Mobile Apps** – PWA with offline capabilities
- **Membership Sites** – Content restrictions and access control

== Installation ==

### Option 1: Automatic Installation

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "WordPress Twitch Stream Plugin"
3. Click **Install Now** and then **Activate**

### Option 2: Manual Installation

1. Download the plugin as a ZIP file
2. In WordPress: **Plugins → Add New → Upload Plugin**
3. Select the ZIP file and install
4. **Activate** the plugin

### Option 3: FTP/SFTP

```bash
# Clone repository
git clone https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin.git

# Move folder to wp-content/plugins/
mv WordPress-Twitch-Stream-Plugin /path/to/wordpress/wp-content/plugins/
```

Activate in WordPress admin under **Plugins**.

### 🔑 Twitch API Setup

**You need a Twitch Developer Application for API access:**

1. Go to: [https://dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Click **"Register Your Application"**
3. Fill the form:
   ```
   Name: Your WordPress Site
   OAuth Redirect URLs: https://your-domain.com
   Category: Website Integration
   ```
4. **Save** and note your **Client ID** and **Client Secret**

5. In WordPress Admin: **Settings → Twitch API**
6. Enter your **Client ID** and **Client Secret**
7. **Save Changes**

✅ Done! The plugin is now ready to use.

== Frequently Asked Questions ==

= How do I embed a Twitch stream? =

Use the shortcode: `[twitch_stream channel="yourchannel"]`

You can add options like: `[twitch_stream channel="yourchannel" width="100%" height="480"]`

= What are all the available shortcodes? =

**Core Shortcodes:**
- `[twitch_stream]` - Basic stream embed
- `[twitch_chat]` - Standalone chat
- `[twitch_follow_button]` - Follow button
- `[twitch_subscribe_button]` - Subscribe button
- `[twitch_clips]` - Channel clips
- `[twitch_vod]` - Past broadcasts

**Advanced Shortcodes (v1.7.0):**
- `[twitch_mobile_app]` - Mobile app interface
- `[twitch_stream_scheduler]` - Visual scheduler
- `[twitch_shortcode_builder]` - Shortcode builder GUI
- `[twitch_membership_content]` - Restricted content
- `[twitch_donations]` - Donation integration
- `[twitch_chat_integration]` - Advanced chat
- `[twitch_analytics]` - Analytics dashboard

= How do I set up push notifications? =

1. Go to **Twitch Dashboard → Mobile App** in WordPress admin
2. Configure VAPID keys for push notifications
3. Set notification preferences
4. Users will be prompted to enable notifications when using the mobile app

= How do I schedule streams? =

Use the visual scheduler: `[twitch_stream_scheduler view="calendar"]`

Or access it via **Twitch Dashboard → Stream Scheduler** in WordPress admin.

= What membership plugins are supported? =

The plugin supports:
- MemberPress
- Restrict Content Pro (RCP)
- Paid Memberships Pro (PMPro)
- WooCommerce Memberships
- Ultimate Member
- s2Member

= How do I enable the mobile app? =

Use the shortcode: `[twitch_mobile_app theme="dark" show_notifications="true"]`

This creates a full mobile app interface with PWA capabilities.

= Can I use this on mobile devices? =

Yes! The plugin includes full PWA support and mobile-optimized interfaces. Users can install it as a mobile app.

= How do I restrict content based on membership? =

Use: `[twitch_membership_content level="premium"]Your content here[/twitch_membership_content]`

= What languages are supported? =

The plugin includes complete translations for:
- English (en_US)
- German (de_DE)
- French (fr_FR)
- Spanish (es_ES)
- Russian (ru_RU)
- Portuguese (pt_PT)
- Japanese (ja_JP)

= How do I enable analytics? =

Use: `[twitch_analytics time_range="7d" chart_type="line"]`

Or access the full dashboard via the admin panel.

= Is this plugin GDPR compliant? =

Yes, the plugin includes cookie banner integration and is fully GDPR compliant. It works with 6 major cookie consent systems.

= How do I get support? =

- GitHub Issues: [Report bugs](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)
- Documentation: [Wiki](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/wiki)
- Email: support@speedyswifter.com

== Screenshots ==

1. **Main Stream Interface** - Clean, responsive stream embedding
2. **Mobile App Experience** - Full PWA with touch gestures
3. **Visual Stream Scheduler** - Drag-and-drop calendar interface
4. **Advanced Shortcode Builder** - GUI for creating shortcodes
5. **Analytics Dashboard** - Real-time stream metrics
6. **Membership Integration** - Content restriction management
7. **Admin Settings Panel** - Comprehensive configuration options

== Changelog ==

= 1.7.0 - Mobile App Integration =
* 🚀 **Progressive Web App (PWA)** with offline support
* 🔔 **Push notifications** with VAPID key configuration
* 👆 **Touch gestures** and mobile-optimized interface
* 📅 **Visual stream scheduler** with calendar interface
* 🛠️ **Advanced shortcode builder** GUI
* 🔒 **Membership plugin integration** (6 plugins supported)
* 🌍 **Multi-language support** (7 languages complete)
* 📱 **Mobile app interface** with native-like experience

= 1.6.0 - Visual Stream Scheduler =
* 📅 Interactive calendar with FullCalendar.js
* 🖱️ Drag-and-drop stream scheduling
* 📋 Multiple view modes (Calendar/List/Timeline)
* 🔄 Real-time status tracking and updates
* 🔁 Recurring stream patterns support
* 🎯 Advanced filtering and search capabilities

= 1.5.0 - Advanced Shortcode Builder =
* 🎨 Interactive GUI for building shortcodes
* 👀 Live preview with auto-refresh
* 📋 Support for all plugin shortcodes (13+)
* 📂 Category-based organization
* 💾 Preset templates and quick-start options
* 📋 Copy-to-clipboard functionality

= 1.4.0 - Membership Plugin Integration =
* 👥 Support for 6 major membership plugins
* 🏆 4-tier membership system (Free/Basic/Premium/VIP)
* 🚫 Content restrictions based on membership level
* 🏷️ Membership badges and visual indicators
* 🔐 Access control and permission management

= 1.3.0 - Advanced Features Suite =
* 💰 Donation Integration (Buy Me a Coffee + PayPal)
* 💬 Twitch Chat Integration with emoji support
* 📥 Stream Recording Download functionality
* 📊 Advanced Analytics Dashboard with charts
* 🌍 Multi-Language Support (EN/DE/FR/ES/RU/PT/JA)

= 1.2.0 - WooCommerce Integration =
* 🛒 eCommerce integration for stream-linked products
* 💳 Purchase-triggered stream access
* 📈 Revenue tracking and order synchronization
* 🏪 WooCommerce membership and subscription support

= 1.1.0 - Extended Content Support =
* 🎬 VOD (Video on Demand) support with archives
* 🎞️ Twitch Clips integration and embedding
* 📱 Sidebar widgets for VODs and clips
* 🧩 Extended page builder compatibility

= 1.0.0 - Core Release =
* ✅ Basic Twitch stream embedding
* 🔴 Live status detection
* 📺 Responsive player integration
* ⚙️ Admin settings panel
* 🔐 Secure API integration

== Upgrade Notice ==

= 1.7.0 =
Major update with mobile app integration, visual scheduler, and advanced features. Requires WordPress 5.8+ and PHP 7.4+. Backup your database before upgrading.

= 1.6.0 =
Adds visual stream scheduler with calendar interface. New database tables will be created automatically.

= 1.5.0 =
Introduces advanced shortcode builder GUI. No breaking changes, fully backward compatible.

= 1.4.0 =
Membership plugin integration added. Configure level mappings in the admin panel.

= 1.3.0 =
Multiple new features added. Check settings for new configuration options.

= 1.2.0 =
WooCommerce integration. Requires WooCommerce plugin if using e-commerce features.

= 1.1.0 =
Extended content support. No breaking changes.

= 1.0.0 =
Initial release. Basic functionality for Twitch stream integration.

== Credits ==

This plugin is developed by **SpeedySwifter**.

Special thanks to:
- **Twitch** for the amazing streaming platform and API
- **WordPress** for the incredible CMS foundation
- **FullCalendar.js** for calendar functionality
- **All contributors** who help make this plugin better

== License ==

This plugin is licensed under the GPL v2 or later.

    WordPress Twitch Stream Plugin
    Copyright (C) 2024 SpeedySwifter

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
