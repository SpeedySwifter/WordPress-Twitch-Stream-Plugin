# 🍪 Cookie Banner Integration Tutorial

Dieses Tutorial zeigt, wie du das WordPress Twitch Stream Plugin mit verschiedenen Cookie-Consent-Management-Systemen integrierst, um die DSGVO-Konformität sicherzustellen.

---

## 📋 Inhaltsverzeichnis

- [Warum Cookie-Integration?](#warum-cookie-integration)
- [Borlabs Cookie (Premium)](#borlabs-cookie-premium)
- [Real Cookie Banner (Pro/Kostenlos)](#real-cookie-banner-prokostenlos)
- [Complianz (Kostenlos/Premium)](#complianz-kostenlospremium)
- [Cookiebot (SaaS)](#cookiebot-saas)
- [OMR](#omr)
- [Allgemeine Integration](#allgemeine-integration)
- [Troubleshooting](#troubleshooting)

---

## 🤔 Warum Cookie-Integration?

Twitch-Player laden externe Skripte von `player.twitch.tv`, die Cookies setzen können. Für DSGVO-Konformität müssen diese Skripte nur mit Einwilligung geladen werden.

### 🎯 Was wird blockiert?
- Twitch Player JavaScript
- Twitch Analytics
- Externe Tracking-Skripte
- Cookie-basierte Funktionen

---

## 🔧 Borlabs Cookie (Premium)

### Schritt 1: Borlabs Cookie einrichten

1. **Borlabs Cookie Plugin installieren und aktivieren**
2. **Neue Cookie-Gruppe erstellen**: `Externe Medien`
3. **Neuen Cookie-Dienst erstellen**: `Twitch`

### Schritt 2: Twitch-Dienst konfigurieren

```javascript
// Borlabs Cookie - Twitch Dienst Einstellungen
Dienstname: Twitch
Beschreibung: Twitch Stream Player und Analytics
Cookie-Name: twitch_* oder leer lassen
Laufzeit: Session
Anbieter: Twitch Interactive, Inc.
Zweck: Stream-Wiedergabe und Analytics
Datenschutz: https://www.twitch.tv/p/legal/privacy-policy
```

### Schritt 3: Skript-Blocker einrichten

1. **Globalen Skript-Blocker erstellen**:
   - Name: `Twitch Player`
   - Typ: `JavaScript`
   - Inhalt: `player.twitch.tv`

2. **Initialisierungs-Skript hinzufügen**:
   ```javascript
   // Borlabs Cookie - Twitch Initialisierung
   window.borlabsCookieExecuted = true;
   if (typeof window.loadTwitchStreams === 'function') {
       window.loadTwitchStreams();
   }
   ```

### Schritt 4: Plugin anpassen

Füge folgenden Code in deine `functions.php` oder ein Custom-Plugin ein:

```php
<?php
/**
 * Borlabs Cookie Integration für Twitch Stream Plugin
 */

// Twitch Stream nur mit Cookie-Einwilligung laden
function wp_twitch_borlabs_cookie_integration() {
    ?>
    <script>
    // Borlabs Cookie - Twitch Stream Integration
    document.addEventListener('borlabsCookieInit', function() {
        // Prüfen ob Twitch-Cookies akzeptiert wurden
        if (window.BorlabsCookie && window.BorlabsCookie.checkCookieGroup('external-media')) {
            // Twitch Streams laden
            loadTwitchStreams();
        } else {
            // Placeholder anzeigen
            showTwitchCookiePlaceholder();
        }
    });

    // Twitch Streams laden
    function loadTwitchStreams() {
        // Alle Twitch Stream Container finden
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            // Original-Content speichern
            var originalContent = container.innerHTML;
            container.setAttribute('data-original-content', originalContent);
            
            // Twitch Player laden
            if (container.classList.contains('twitch-stream-container')) {
                loadTwitchPlayer(container);
            } else if (container.classList.contains('twitch-streams-grid')) {
                loadTwitchGrid(container);
            }
        });
    }

    // Twitch Player laden
    function loadTwitchPlayer(container) {
        var channel = container.getAttribute('data-channel');
        if (!channel) return;
        
        var iframe = document.createElement('iframe');
        iframe.src = 'https://player.twitch.tv/?channel=' + channel + '&parent=' + window.location.hostname + '&autoplay=false&muted=true';
        iframe.width = container.getAttribute('data-width') || '100%';
        iframe.height = container.getAttribute('data-height') || '480';
        iframe.frameBorder = '0';
        iframe.scrolling = 'no';
        iframe.allowFullscreen = true;
        
        container.innerHTML = '';
        container.appendChild(iframe);
    }

    // Twitch Grid laden
    function loadTwitchGrid(container) {
        // Grid-Items laden
        var gridItems = container.querySelectorAll('.twitch-grid-item');
        gridItems.forEach(function(item) {
            var channel = item.getAttribute('data-channel');
            if (channel) {
                loadTwitchPlayer(item);
            }
        });
    }

    // Cookie-Placeholder anzeigen
    function showTwitchCookiePlaceholder() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            if (!container.querySelector('.twitch-cookie-placeholder')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'twitch-cookie-placeholder';
                placeholder.innerHTML = `
                    <div class="twitch-cookie-message">
                        <h3>🎮 Twitch Stream</h3>
                        <p>Dieser Inhalt erfordert Ihre Zustimmung zu externen Medien.</p>
                        <button class="twitch-cookie-accept" onclick="window.BorlabsCookie.acceptGroup('external-media')">
                            Externe Medien akzeptieren
                        </button>
                        <button class="twitch-cookie-settings" onclick="window.BorlabsCookie.showCookieBox()">
                            Cookie-Einstellungen
                        </button>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(placeholder);
            }
        });
    }
    </script>
    
    <style>
    .twitch-cookie-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 20px;
        text-align: center;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .twitch-cookie-message h3 {
        margin: 0 0 15px 0;
        font-size: 20px;
    }
    
    .twitch-cookie-message p {
        margin: 0 0 20px 0;
        opacity: 0.9;
    }
    
    .twitch-cookie-accept,
    .twitch-cookie-settings {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 10px 20px;
        margin: 5px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .twitch-cookie-accept:hover,
    .twitch-cookie-settings:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    </style>
    <?php
}
add_action('wp_footer', 'wp_twitch_borlabs_cookie_integration');
?>
```

---

## 🛡️ Real Cookie Banner (Pro/Kostenlos)

### Schritt 1: Real Cookie Banner einrichten

1. **Real Cookie Banner Plugin installieren**
2. **Neuen Dienst erstellen**: `Twitch`
3. **Dienst-Typ**: `Externe Medien`

### Schritt 2: Twitch-Dienst konfigurieren

```javascript
// Real Cookie Banner - Twitch Dienst
{
    "name": "Twitch",
    "description": "Twitch Stream Player und Analytics",
    "purpose": "Stream-Wiedergabe und Analyse",
    "provider": "Twitch Interactive, Inc.",
    "privacyPolicy": "https://www.twitch.tv/p/legal/privacy-policy",
    "cookies": [
        {
            "name": "twitch_*",
            "description": "Twitch Session Cookies",
            "retention": "session"
        }
    ],
    "scripts": [
        {
            "name": "Twitch Player",
            "url": "player.twitch.tv",
            "type": "iframe"
        }
    ]
}
```

### Schritt 3: Integration Code

```php
<?php
/**
 * Real Cookie Banner Integration für Twitch Stream Plugin
 */

function wp_twitch_real_cookie_banner_integration() {
    ?>
    <script>
    // Real Cookie Banner - Twitch Stream Integration
    document.addEventListener('rcb-ready', function() {
        // Twitch Streams nur mit Einwilligung laden
        if (window.rcb && window.rcb.consent && window.rcb.consent['twitch']) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
        
        // Event Listener für Cookie-Änderungen
        window.addEventListener('rcb:consent', function(e) {
            if (e.detail.consent['twitch']) {
                loadTwitchStreams();
            } else {
                showTwitchCookiePlaceholder();
            }
        });
    });

    // Twitch Streams laden (gleiche Funktion wie oben)
    function loadTwitchStreams() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            // Implementierung wie bei Borlabs Cookie
            var channel = container.getAttribute('data-channel');
            if (channel) {
                loadTwitchPlayer(container);
            }
        });
    }

    // Twitch Player laden (gleiche Funktion wie oben)
    function loadTwitchPlayer(container) {
        // Implementierung wie bei Borlabs Cookie
    }

    // Cookie-Placeholder anzeigen (angepasst für Real Cookie Banner)
    function showTwitchCookiePlaceholder() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            if (!container.querySelector('.twitch-cookie-placeholder')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'twitch-cookie-placeholder';
                placeholder.innerHTML = `
                    <div class="twitch-cookie-message">
                        <h3>🎮 Twitch Stream</h3>
                        <p>Dieser Inhalt erfordert Ihre Zustimmung zu externen Medien.</p>
                        <button class="twitch-cookie-accept" onclick="window.rcb.acceptService('twitch')">
                            Twitch akzeptieren
                        </button>
                        <button class="twitch-cookie-settings" onclick="window.rcb.showSettings()">
                            Cookie-Einstellungen
                        </button>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(placeholder);
            }
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'wp_twitch_real_cookie_banner_integration');
?>
```

---

## ⚖️ Complianz (Kostenlos/Premium)

### Schritt 1: Complianz einrichten

1. **Complianz Plugin installieren**
2. **Cookie-Kategorie erstellen**: `Externe Medien`
3. **Dienst hinzufügen**: `Twitch`

### Schritt 2: Integration Code

```php
<?php
/**
 * Complianz Integration für Twitch Stream Plugin
 */

function wp_twitch_complianz_integration() {
    ?>
    <script>
    // Complianz - Twitch Stream Integration
    document.addEventListener('cmplz_consent_changed', function(consent) {
        if (consent.details.categories['marketing'] || consent.details.categories['statistics']) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    });

    // Initialisierung
    if (typeof cmplz !== 'undefined' && cmplz.consent) {
        if (cmplz.consent.marketing || cmplz.consent.statistics) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    }

    // Twitch Streams laden (gleiche Funktion wie oben)
    function loadTwitchStreams() {
        // Implementierung wie bei Borlabs Cookie
    }

    // Cookie-Placeholder anzeigen (angepasst für Complianz)
    function showTwitchCookiePlaceholder() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            if (!container.querySelector('.twitch-cookie-placeholder')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'twitch-cookie-placeholder';
                placeholder.innerHTML = `
                    <div class="twitch-cookie-message">
                        <h3>🎮 Twitch Stream</h3>
                        <p>Dieser Inhalt erfordert Ihre Zustimmung zu Marketing-Cookies.</p>
                        <button class="twitch-cookie-accept" onclick="cmplz_accept_category('marketing')">
                            Marketing-Cookies akzeptieren
                        </button>
                        <button class="twitch-cookie-settings" onclick="cmplz_show_banner()">
                            Cookie-Einstellungen
                        </button>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(placeholder);
            }
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'wp_twitch_complianz_integration');
?>
```

---

## 🤖 Cookiebot (SaaS)

### Schritt 1: Cookiebot einrichten

1. **Cookiebot Account erstellen**
2. **Domain hinzufügen**
3. **Dienst deklarieren**: `Twitch`

### Schritt 2: Integration Code

```php
<?php
/**
 * Cookiebot Integration für Twitch Stream Plugin
 */

function wp_twitch_cookiebot_integration() {
    ?>
    <script>
    // Cookiebot - Twitch Stream Integration
    window.addEventListener('CookiebotConsentApplied', function(e) {
        if (e.consent.marketing) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    });

    // Initialisierung
    if (typeof Cookiebot !== 'undefined' && Cookiebot.consent) {
        if (Cookiebot.consent.marketing) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    }

    // Twitch Streams laden (gleiche Funktion wie oben)
    function loadTwitchStreams() {
        // Implementierung wie bei Borlabs Cookie
    }

    // Cookie-Placeholder anzeigen (angepasst für Cookiebot)
    function showTwitchCookiePlaceholder() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            if (!container.querySelector('.twitch-cookie-placeholder')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'twitch-cookie-placeholder';
                placeholder.innerHTML = `
                    <div class="twitch-cookie-message">
                        <h3>🎮 Twitch Stream</h3>
                        <p>Dieser Inhalt erfordert Ihre Zustimmung zu Marketing-Cookies.</p>
                        <button class="twitch-cookie-accept" onclick="Cookiebot.renew()">
                            Cookie-Einstellungen ändern
                        </button>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(placeholder);
            }
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'wp_twitch_cookiebot_integration');
?>
```

---

## 📊 OMR (Online-Marketing-Regional)

### Schritt 1: OMR einrichten

1. **OMR Plugin installieren**
2. **Cookie-Kategorie erstellen**: `Externe Medien`
3. **Dienst hinzufügen**: `Twitch`

### Schritt 2: Integration Code

```php
<?php
/**
 * OMR Integration für Twitch Stream Plugin
 */

function wp_twitch_omr_integration() {
    ?>
    <script>
    // OMR - Twitch Stream Integration
    document.addEventListener('omr_consent_update', function(e) {
        if (e.detail.consent['external-media']) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    });

    // Initialisierung
    if (typeof omr !== 'undefined' && omr.consent) {
        if (omr.consent['external-media']) {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    }

    // Twitch Streams laden (gleiche Funktion wie oben)
    function loadTwitchStreams() {
        // Implementierung wie bei Borlabs Cookie
    }

    // Cookie-Placeholder anzeigen (angepasst für OMR)
    function showTwitchCookiePlaceholder() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            if (!container.querySelector('.twitch-cookie-placeholder')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'twitch-cookie-placeholder';
                placeholder.innerHTML = `
                    <div class="twitch-cookie-message">
                        <h3>🎮 Twitch Stream</h3>
                        <p>Dieser Inhalt erfordert Ihre Zustimmung zu externen Medien.</p>
                        <button class="twitch-cookie-accept" onclick="omr.updateConsent({'external-media': true})">
                            Externe Medien akzeptieren
                        </button>
                        <button class="twitch-cookie-settings" onclick="omr.showSettings()">
                            Cookie-Einstellungen
                        </button>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(placeholder);
            }
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'wp_twitch_omr_integration');
?>
```

---

## 🔗 Allgemeine Integration

### Universelle Cookie-Integration

Falls du kein spezifisches Cookie-Plugin verwendest, kannst du diese universelle Lösung verwenden:

```php
<?php
/**
 * Universelle Cookie-Integration für Twitch Stream Plugin
 */

function wp_twitch_universal_cookie_integration() {
    ?>
    <script>
    // Universelle Cookie-Integration
    document.addEventListener('DOMContentLoaded', function() {
        // Prüfen ob Cookies akzeptiert wurden (localStorage)
        var cookieConsent = localStorage.getItem('cookie_consent');
        
        if (cookieConsent === 'accepted') {
            loadTwitchStreams();
        } else {
            showTwitchCookiePlaceholder();
        }
    });

    // Cookie-Zustimmung speichern
    function acceptTwitchCookies() {
        localStorage.setItem('cookie_consent', 'accepted');
        loadTwitchStreams();
    }

    // Twitch Streams laden (gleiche Funktion wie oben)
    function loadTwitchStreams() {
        // Implementierung wie bei Borlabs Cookie
    }

    // Cookie-Placeholder anzeigen
    function showTwitchCookiePlaceholder() {
        var twitchContainers = document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid');
        
        twitchContainers.forEach(function(container) {
            if (!container.querySelector('.twitch-cookie-placeholder')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'twitch-cookie-placeholder';
                placeholder.innerHTML = `
                    <div class="twitch-cookie-message">
                        <h3>🎮 Twitch Stream</h3>
                        <p>Dieser Inhalt verwendet externe Dienste von Twitch.</p>
                        <button class="twitch-cookie-accept" onclick="acceptTwitchCookies()">
                            Akzeptieren und laden
                        </button>
                        <div class="twitch-cookie-info">
                            <small>Durch Klick akzeptieren Sie die <a href="https://www.twitch.tv/p/legal/privacy-policy" target="_blank">Twitch Datenschutzrichtlinie</a></small>
                        </div>
                    </div>
                `;
                container.innerHTML = '';
                container.appendChild(placeholder);
            }
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'wp_twitch_universal_cookie_integration');
?>
```

---

## 🔧 Troubleshooting

### Häufige Probleme

#### 1. Twitch Player wird nicht geladen
- **Lösung**: Prüfe ob die Cookie-Einstellungen korrekt konfiguriert sind
- **Debug**: Öffne Browser-Konsole und prüfe auf JavaScript-Fehler

#### 2. Placeholder wird nicht angezeigt
- **Lösung**: Stelle sicher, dass das Cookie-Plugin korrekt initialisiert wird
- **Debug**: Prüfe ob die Event-Listener ausgelöst werden

#### 3. Cookies werden nicht gesetzt
- **Lösung**: Überprüfe die Cookie-Domain und -Laufzeit
- **Debug**: Verwende Browser-Entwicklertools zum Cookie-Inspektor

#### 4. Performance-Probleme
- **Lösung**: Lazy Loading für Twitch-Streams implementieren
- **Debug**: Prüfe die Ladezeiten mit PageSpeed Insights

### Debug-Modus

Füge diesen Code hinzu, um Probleme zu diagnostizieren:

```php
<?php
/**
 * Debug-Modus für Cookie-Integration
 */

function wp_twitch_cookie_debug() {
    if (current_user_can('administrator')) {
        ?>
        <script>
        console.log('Twitch Cookie Debug Info:', {
            'cookiePlugin': detectCookiePlugin(),
            'consentStatus': getCookieConsent(),
            'twitchContainers': document.querySelectorAll('.twitch-stream-container, .twitch-streams-grid').length
        });
        
        function detectCookiePlugin() {
            if (typeof window.BorlabsCookie !== 'undefined') return 'Borlabs Cookie';
            if (typeof window.rcb !== 'undefined') return 'Real Cookie Banner';
            if (typeof window.cmplz !== 'undefined') return 'Complianz';
            if (typeof window.Cookiebot !== 'undefined') return 'Cookiebot';
            if (typeof window.omr !== 'undefined') return 'OMR';
            return 'Kein Cookie-Plugin erkannt';
        }
        
        function getCookieConsent() {
            if (typeof window.BorlabsCookie !== 'undefined') {
                return window.BorlabsCookie.checkCookieGroup('external-media');
            }
            // Weitere Prüfungen für andere Plugins...
            return 'unknown';
        }
        </script>
        <?php
    }
}
add_action('wp_footer', 'wp_twitch_cookie_debug');
?>
```

---

## 📞 Support

Bei Problemen mit der Cookie-Integration:

1. **Plugin-Dokumentation** prüfen
2. **Browser-Konsole** auf Fehler untersuchen
3. **Cookie-Plugin-Einstellungen** überprüfen
4. **Debug-Modus** aktivieren

---

## 📄 Lizenz

Dieses Tutorial ist Teil des WordPress Twitch Stream Plugins und unterliegt der MIT-Lizenz.

---

<div align="center">

**🍪 Cookie-konforme Twitch-Integration für deine WordPress-Seite!**

</div>
