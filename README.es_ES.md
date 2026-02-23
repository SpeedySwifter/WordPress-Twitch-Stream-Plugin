# 🎮 SpeedySwifter Stream Integrator para Twitch v1.7.2

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-6.8-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Twitch](https://img.shields.io/badge/Twitch_API-9146FF?style=for-the-badge&logo=twitch&logoColor=white)
![License](https://img.shields.io/badge/License-GPL_v2+-green?style=for-the-badge)

[![GitHub Stars](https://img.shields.io/github/stars/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/forks)
[![GitHub Issues](https://img.shields.io/github/issues/SpeedySwifter/WordPress-Twitch-Stream-Plugin)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)

**Un plugin de WordPress para la integración de streams de Twitch con soporte de aplicación móvil, programación, análisis y soporte multiidioma.**

[🚀 Características](#-características) • [📦 Instalación](#-instalación) • [🧩 Uso](#-uso) • [📋 Shortcodes](#-shortcodes) • [⚙️ Admin](#-configuración-admin) • [🌍 Idiomas](#-idiomas)

</div>

---

## 📌 ¿Qué es esto?

El **SpeedySwifter Stream Integrator para Twitch v1.7.2** proporciona una solución para integrar streams de Twitch en sitios web de WordPress. Ofrece características como integración de aplicación móvil, programación de streams, análisis y soporte multiidioma.

### ✨ Características principales

- ✅ **Shortcodes simples** – `[twitch_stream channel="tucanal"]`
- 🔴 **Detección de estado en vivo** – Verificación automática si el stream está en línea
- 📺 **Reproductor responsive** – La integración de Twitch se adapta a todos los tamaños de pantalla
- ⚙️ **Panel de administración** – Página de configuración cómoda para credenciales de API
- 🔐 **Integración API segura** – Utiliza la API oficial de Twitch Helix
- 💾 **Cache de tokens** – Reduce las llamadas a la API gracias a un cache inteligente
- 🎨 **Personalizable** – Clases CSS para estilo individual
- 🧩 **Compatible con WordPress 6.9.1** – Probado con la versión actual de WP
- 🎯 **Información de streams** – Título, juego, espectadores, avatar, insignia en vivo
- 📱 **Cuadrícula de streams múltiples** – Múltiples streams en layout de cuadrícula
- 🧩 **Bloques Gutenberg** – Integración nativa del editor de bloques de WordPress
- 🔧 **Soporte para constructores de páginas** – Elementor, Oxygen, Divi, Beaver Builder & más
- 🍪 **Integración de banners de cookies** – Conforme con RGPD con 6 sistemas de cookies

---

## 🚀 Características avanzadas (v1.7.0)

### 📱 **Integración de aplicación móvil**
- **Progressive Web App (PWA)** con manifiesto completo
- **Service Worker** para funcionalidad offline y cache
- **Notificaciones push** con soporte de claves VAPID
- **Interfaz optimizada para móvil** con gestos táctiles
- **Solicitudes de instalación de app** y banners inteligentes
- **Detección offline** y sincronización

### 📅 **Programador visual de streams**
- **Calendario interactivo** con integración FullCalendar.js
- **Programación por arrastrar y soltar** y reprogramación
- **Múltiples modos de vista** (Calendario, Lista, Timeline)
- **Seguimiento de estado en tiempo real** (Programado/En vivo/Completado)
- **Patrones de streams recurrentes** (Diario/Semanal/Mensual)
- **Filtrado avanzado** por fecha, estado, categoría

### 🛠️ **Constructor avanzado de shortcodes**
- **GUI interactiva** para crear shortcodes de Twitch
- **Vista previa en vivo** con auto-refresh
- **Soporte para todos los 13+ shortcodes del plugin**
- **Organización por categorías**
- **Plantillas predefinidas** para inicio rápido
- **Funcionalidad copiar-pegar**

### 🔒 **Integración de plugins de membresía**
- **Soporte para 6 plugins de membresía principales**
- **MemberPress, RCP, PMPro, WooCommerce Memberships**
- **Ultimate Member, integración s2Member**
- **Sistema de membresía de 4 niveles** (Gratis/Básico/Premium/VIP)
- **Restricciones de contenido** basadas en nivel de membresía
- **Insignias de membresía** e indicadores visuales

### 🌍 **Soporte multiidioma (7 idiomas)**
- **🇺🇸 English (en_US)**
- **🇩🇪 Deutsch (de_DE)**
- **🇫🇷 Français (fr_FR)**
- **🇪🇸 Español (es_ES)**
- **🇷🇺 Русский (ru_RU)**
- **🇵🇹 Português (pt_PT)**
- **🇯🇵 日本語 (ja_JP)**

### 💰 **Integración de donaciones**
- **Botones Buy Me a Coffee** y PayPal
- **Formularios de donaciones personalizables**
- **Objetivos de donaciones y seguimiento de progreso**
- **Diseño responsive** con modo oscuro
- **Estadísticas de donaciones** y análisis

### 💬 **Integración del chat de Twitch**
- **Integración de chat avanzada** con selector de emojis
- **Moderación de mensajes** y procesamiento de comandos
- **Temas de chat** y opciones de personalización
- **Sondeo de mensajes en tiempo real**
- **Visualización de insignias y roles de usuario**

### 📥 **Descarga de grabación de streams**
- **Funcionalidad de descarga VOD**
- **Gestión de grabaciones de streams**
- **Seguimiento del progreso de descarga**
- **Controles del reproductor de video**
- **Permisos de descarga** y control de acceso

### 📊 **Dashboard avanzado de análisis**
- **Análisis de streams** y métricas de rendimiento
- **Estadísticas de espectadores** y seguimiento de engagement
- **Visualización de datos en tiempo real**
- **Gráficos personalizables** e informes
- **Funcionalidad de exportación** para análisis de datos

### 🛒 **Integración WooCommerce**
- **Productos vinculados a streams**
- **Acceso a streams activado por compra**
- **Integración de e-commerce** para membresías
- **Sincronización del estado de pedidos**
- **Seguimiento de ingresos** y análisis

---

## 🎯 Casos de uso

### 📡 Perfecto para

- 🎮 **Sitios de juegos** – Mostrar tu propio stream de Twitch en el sitio web
- 🏆 **Equipos eSports** – Insertar partidos en vivo directamente
- 🎥 **Creadores de contenido** – Integración de streams en blog de WordPress
- 📰 **Portales de noticias** – Transmitir streams de eventos en vivo
- 🎪 **Sitios de eventos** – Transmitir conferencias & torneos
- 📱 **Aplicaciones móviles** – PWA con capacidades offline
- 🔒 **Sitios de membresía** – Restricciones de contenido y control de acceso
- 📅 **Redes de streams** – Programar y gestionar múltiples streams

### 🔧 Lo que hace

```text
✓ Verificar automáticamente si el stream está en vivo
✓ Mostrar reproductor de Twitch solo para streams en vivo
✓ Mostrar mensaje offline cuando el stream no está activo
✓ Totalmente responsive para todos los dispositivos
✓ Aplicación móvil con notificaciones push
✓ Programación visual con interfaz de calendario
✓ Restricciones de contenido basadas en membresía
✓ Soporte multiidioma (7 idiomas)
✓ Análisis avanzados y reporting
✓ PWA con funcionalidad offline
```

---

## 📦 Instalación

### Opción 1: Manual (Subida ZIP)

1. **Descargar plugin** como ZIP
2. En WordPress: **Plugins → Instalar → Subir plugin**
3. Seleccionar archivo ZIP e instalar
4. **Activar** plugin

### Opción 2: FTP/SFTP

```bash
# Clonar repositorio
git clone https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin.git

# Mover carpeta a wp-content/plugins/
mv WordPress-Twitch-Stream-Plugin /ruta/a/wordpress/wp-content/plugins/
```

Luego activar en el admin de WordPress bajo **Plugins**.

---

## 🔑 Configuración de API de Twitch

### 1️⃣ Crear app de Twitch

Necesitas una **Aplicación de desarrollador de Twitch** para acceder a la API:

1. Ir a: [https://dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Hacer clic en **"Register Your Application"**
3. Llenar el formulario:

```
Name:                 Tu sitio WordPress
OAuth Redirect URLs:  https://tu-dominio.com
Category:             Website Integration
```

4. **Guardar** y anotar:
   - ✅ **Client ID**
   - ✅ **Client Secret** (se muestra solo una vez!)

### 2️⃣ Ingresar credenciales en WordPress

1. En admin de WordPress: **Ajustes → API de Twitch**
2. Ingresar **Client ID**
3. Ingresar **Client Secret**
4. **Guardar cambios**

✅ ¡Listo! El plugin está ahora listo para usar.

---

## 🧩 Uso

### Shortcode básico

```text
[twitch_stream channel="shroud"]
```

### Con opciones

```text
[twitch_stream channel="shroud" width="100%" height="480"]
```

### Aplicación móvil avanzada

```text
[twitch_mobile_app theme="dark" show_notifications="true"]
```

### Programador de streams

```text
[twitch_stream_scheduler channel="tucanal" view="calendar"]
```

### Contenido de membresía

```text
[twitch_membership_content level="premium"]
Tu contenido premium aquí
[/twitch_membership_content]
```

---

## 📋 Referencia de shortcodes

### Shortcodes principales

| Shortcode | Descripción | Ejemplo |
|-----------|-------------|---------|
| `[twitch_stream]` | Integración básica de stream | `[twitch_stream channel="shroud"]` |
| `[twitch_chat]` | Chat independiente | `[twitch_chat channel="shroud"]` |
| `[twitch_follow_button]` | Botón de seguir | `[twitch_follow_button channel="shroud"]` |
| `[twitch_subscribe_button]` | Botón de suscribir | `[twitch_subscribe_button channel="shroud"]` |
| `[twitch_clips]` | Clips del canal | `[twitch_clips channel="shroud" limit="10"]` |
| `[twitch_vod]` | Transmisiones pasadas | `[twitch_vod channel="shroud" type="archive"]` |

### Shortcodes avanzados

| Shortcode | Descripción | Ejemplo |
|-----------|-------------|---------|
| `[twitch_mobile_app]` | Interfaz de aplicación móvil | `[twitch_mobile_app theme="dark"]` |
| `[twitch_stream_scheduler]` | Programador visual | `[twitch_stream_scheduler view="calendar"]` |
| `[twitch_shortcode_builder]` | GUI del constructor de shortcodes | `[twitch_shortcode_builder show_preview="true"]` |
| `[twitch_membership_content]` | Contenido restringido | `[twitch_membership_content level="vip"]` |
| `[twitch_donations]` | Integración de donaciones | `[twitch_donations type="both"]` |
| `[twitch_chat_integration]` | Chat avanzado | `[twitch_chat_integration theme="dark"]` |
| `[twitch_recording_download]` | Descargas VOD | `[twitch_recording_download limit="10"]` |
| `[twitch_analytics]` | Dashboard de análisis | `[twitch_analytics time_range="7d"]` |

### Shortcodes de utilidad

| Shortcode | Descripción | Ejemplo |
|-----------|-------------|---------|
| `[twitch_pwa_install]` | Botón de instalación PWA | `[twitch_pwa_install text="Instalar app"]` |
| `[twitch_mobile_menu]` | Navegación móvil | `[twitch_mobile_menu position="left"]` |
| `[twitch_mobile_streams]` | Cuadrícula de streams móvil | `[twitch_mobile_streams limit="10"]` |
| `[twitch_push_notifications]` | Configuración de notificaciones | `[twitch_push_notifications show_settings="true"]` |
| `[twitch_upcoming_streams]` | Streams próximos | `[twitch_upcoming_streams limit="5"]` |
| `[twitch_stream_schedule]` | Horario semanal | `[twitch_stream_schedule days="7"]` |

---

## ⚙️ Configuración admin

### Página de configuración principal
**Admin WordPress → Ajustes → API de Twitch**

- **Client ID & Secret** – Credenciales API de Twitch
- **Opciones de cache** – Configuración de cache de tokens y datos
- **Opciones de visualización** – Dimensiones de reproductor predeterminadas y temas

### Configuración de aplicación móvil
**Admin WordPress → Dashboard de Twitch → Aplicación móvil**

- **Configuración PWA** – Configuración del manifiesto de app y service worker
- **Notificaciones push** – Claves VAPID y preferencias de notificaciones
- **Configuración de tema** – Personalización de la apariencia de la app móvil

### Programador de streams
**Admin WordPress → Dashboard de Twitch → Programador de streams**

- **Configuración de calendario** – Vista predeterminada y zona horaria
- **Configuración de notificaciones** – Preferencias de email y notificaciones push
- **Patrones recurrentes** – Programación automatizada de streams

### Integración de membresía
**Admin WordPress → Dashboard de Twitch → Membresía**

- **Detección de plugins** – Detección automática de plugins de membresía
- **Mapeo de niveles** – Mapear niveles de membresía a niveles de acceso
- **Restricciones de contenido** – Configurar reglas de control de acceso

---

## 📂 Estructura del plugin

```
WordPress-Twitch-Stream-Plugin/
│
├── 📄 wp-twitch-stream.php                    # Archivo plugin principal
├── 📄 README.md                               # Documentación (7 idiomas)
├── 📄 LICENSE                                 # Licencia MIT
│
├── 📁 admin/
│   ├── 📄 settings-page.php                   # Página configuración admin
│   └── 📄 admin-styles.css                    # Estilos admin
│
├── 📁 includes/
│   ├── 📄 twitch-api.php                      # Gestor API
│   ├── 📄 shortcode.php                       # Lógica shortcodes
│   ├── 📄 token-manager.php                   # Cache de tokens
│   ├── 📄 gutenberg-block.php                 # Bloques Gutenberg
│   ├── 📄 page-builder-compatibility.php      # Integración constructores de páginas
│   ├── 📄 cookie-integration.php              # Integración banners de cookies
│   ├── 📄 sidebar-widgets.php                 # Widgets VOD & clips
│   ├── 📄 donation-integration.php            # Sistema de donaciones
│   ├── 📄 twitch-chat-integration.php         # Chat avanzado
│   ├── 📄 stream-recording-download.php       # Descargas VOD
│   ├── 📄 advanced-analytics-dashboard.php    # Sistema de análisis
│   ├── 📄 multi-language-support.php          # Soporte i18n
│   ├── 📄 woocommerce-integration.php         # Integración eCommerce
│   ├── 📄 membership-plugin-integration.php   # Sistema de membresía
│   ├── 📄 advanced-shortcode-builder.php      # Constructor de shortcodes
│   ├── 📄 visual-stream-scheduler.php         # Programador calendario
│   └── 📄 mobile-app-integration.php          # PWA & aplicación móvil
│
├── 📁 assets/
│   ├── 📁 css/
│   │   ├── 📄 frontend.css                     # Estilos frontend
│   │   ├── 📄 block.css                        # Estilos bloques Gutenberg
│   │   ├── 📄 page-builder-compatibility.css   # Estilos constructores de páginas
│   │   ├── 📄 cookie-integration.css           # Estilos integración cookies
│   │   ├── 📄 vod-clips.css                    # Estilos VOD & clips
│   │   ├── 📄 donations.css                    # Estilos sistema de donaciones
│   │   ├── 📄 twitch-chat.css                  # Estilos integración chat
│   │   ├── 📄 recording-download.css           # Estilos sistema de descarga
│   │   ├── 📄 analytics-dashboard.css          # Estilos análisis
│   │   ├── 📄 language-support.css             # Estilos multiidioma
│   │   ├── 📄 woocommerce-integration.css      # Estilos eCommerce
│   │   ├── 📄 membership-integration.css       # Estilos membresía
│   │   ├── 📄 shortcode-builder.css            # Estilos interfaz constructor
│   │   ├── 📄 stream-scheduler.css             # Estilos calendario
│   │   └── 📄 mobile-app.css                   # Estilos aplicación móvil
│   └── 📁 js/
│       ├── 📄 player.js                        # Funciones reproductor
│       ├── 📄 block.js                         # JavaScript bloques Gutenberg
│       ├── 📄 oxygen-builder.js                # JS constructor Oxygen
│       ├── 📄 divi-builder.js                  # JS constructor Divi
│       ├── 📄 donations.js                     # JS sistema de donaciones
│       ├── 📄 twitch-chat.js                   # JS integración chat
│       ├── 📄 recording-download.js            # JS sistema de descarga
│       ├── 📄 analytics-dashboard.js           # JS análisis
│       ├── 📄 language-support.js              # JS multiidioma
│       ├── 📄 woocommerce-integration.js       # JS eCommerce
│       ├── 📄 membership-integration.js        # JS membresía
│       ├── 📄 shortcode-builder.js             # JS interfaz constructor
│       ├── 📄 stream-scheduler.js              # JS calendario
│       └── 📄 mobile-app.js                    # JS aplicación móvil
│
├── 📁 docs/
│   ├── 📄 cookie-banner-integration.md        # Tutorial integración cookies
│   ├── 📄 membership-plugin-integration.md    # Guía configuración membresía
│   ├── 📄 mobile-app-setup.md                 # Configuración PWA
│   └── 📄 api-reference.md                    # Referencia API completa
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
    ├── 📄 offline-page.html                   # Página offline PWA
    └── 📄 mobile-app-manifest.json            # Plantilla manifiesto PWA
```

---

## 🌍 Idiomas / Languages

El plugin soporta **7 idiomas** con traducciones completas:

### 🇺🇸 English (en_US) - Predeterminado
- Documentación e interfaz de usuario completas en inglés

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

## 📊 Historial de versiones

### v1.7.0 - Integración de aplicación móvil 🚀
- 📱 Progressive Web App (PWA) con soporte offline
- 🔔 Notificaciones push con configuración de claves VAPID
- 👆 Gestos táctiles e interfaz optimizada para móvil
- 📅 Programador de streams visual con interfaz de calendario
- 🛠️ Constructor de shortcodes avanzado GUI
- 🔒 Integración de plugins de membresía (6 plugins soportados)
- 🌍 Soporte multiidioma (7 idiomas)

### v1.6.0 - Programador visual de streams 📅
- 📅 Calendario interactivo con FullCalendar.js
- 🖱️ Programación por arrastrar y soltar
- 📋 Múltiples modos de vista (Calendario/Lista/Timeline)
- 🔄 Seguimiento de estado en tiempo real y actualizaciones
- 🔁 Soporte de patrones de streams recurrentes
- 🎯 Características avanzadas de filtrado y búsqueda

### v1.5.0 - Constructor avanzado de shortcodes 🛠️
- 🎨 GUI interactiva para crear shortcodes
- 👀 Vista previa en vivo con auto-refresh
- 📋 Soporte para todos los shortcodes del plugin (13+)
- 📂 Organización por categorías
- 💾 Plantillas predefinidas y opciones de inicio rápido
- 📋 Funcionalidad copiar-pegar

### v1.4.0 - Integración de plugins de membresía 🔒
- 👥 Soporte para 6 plugins de membresía principales
- 🏆 Sistema de membresía de 4 niveles (Gratis/Básico/Premium/VIP)
- 🚫 Restricciones de contenido basadas en nivel de membresía
- 🏷️ Insignias de membresía e indicadores visuales
- 🔐 Gestión de controles de acceso y permisos

### v1.3.0 - Suite de características avanzadas 💎
- 💰 Integración de donaciones (Buy Me a Coffee + PayPal)
- 💬 Integración del chat de Twitch con soporte de emojis
- 📥 Funcionalidad de descarga de grabación de streams
- 📊 Dashboard avanzado de análisis con gráficos
- 🌍 Soporte multiidioma (EN/DE/FR/ES/RU/PT/JA)

### v1.2.0 - Integración WooCommerce 🛒
- 🛒 Integración de e-commerce para productos vinculados a streams
- 💳 Acceso a streams activado por compra
- 📈 Seguimiento de ingresos y sincronización de pedidos
- 🏪 Soporte de membresías y suscripciones WooCommerce

### v1.1.0 - Soporte de contenido extendido 🎬
- 🎬 Soporte VOD (Video on Demand) con archivos
- 🎞️ Integración e integración de clips de Twitch
- 📱 Widgets de sidebar para VODs y clips
- 🧩 Compatibilidad extendida de constructores de páginas

### v1.0.0 - Versión principal 🎯
- ✅ Integración básica de stream de Twitch
- 🔴 Detección de estado en vivo
- 📺 Integración de reproductor responsive
- ⚙️ Panel de parámetros admin
- 🔐 Integración API segura

---

## 🗺️ Roadmap

### ✅ Versión 1.7.0 (Completada - Versión actual)
- [x] **Integración de aplicación móvil** - Progressive Web App (PWA) con soporte offline
- [x] **Notificaciones push** - Configuración de claves VAPID y notificaciones de navegador
- [x] **Gestos táctiles** - Interfaz optimizada para móvil con control por deslizamiento
- [x] **Programador visual de streams** - Interfaz de calendario para planificación de streams
- [x] **Constructor avanzado de shortcodes** - GUI para crear shortcodes personalizados
- [x] **Integración de plugins de membresía** - Soporte para 6 plugins de membresía principales
- [x] **Soporte multiidioma** - Traducciones completas en 7 idiomas
- [x] **Integración de donativos** - Botones Buy Me a Coffee y PayPal
- [x] **Integración del chat Twitch** - Chat avanzado con soporte de emojis
- [x] **Descarga de grabación de streams** - Funcionalidad de descarga VOD
- [x] **Dashboard avanzado de análisis** - Métricas en tiempo real y gráficos
- [x] **Integración WooCommerce** - Integración e-commerce para membresías
- [x] **Integración de banners de cookie** - Conforme RGPD con 6 sistemas de cookies
- [x] **Soporte VOD** - Video on Demand con archivos y momentos destacados
- [x] **Integración de clips** - Integración y gestión de clips de Twitch
- [x] **Widgets de sidebar** - Widgets VOD y clips para sidebars
- [x] **Soporte para constructores de páginas** - Elementor, Oxygen, Divi, Beaver Builder & más
- [x] **Bloques Gutenberg** - Integración nativa del editor de bloques de WordPress
- [x] **Puntos finales REST API** - Acceso programático a las funcionalidades del plugin
- [x] **Soporte webhook** - Integración EventSub para actualizaciones en tiempo real
- [x] **Dashboard multi-canal** - Gestionar múltiples canales de Twitch
- [x] **Constructor CSS personalizado** - Interfaz de ajuste CSS visual
- [x] **Cache avanzado** - Optimización de rendimiento y opciones de cache
- [x] **Soporte de modo oscuro** - Implementación completa del tema oscuro
- [x] **Diseño responsive** - Layouts responsive mobile-first
- [x] **Integración de stream básica** - Funcionalidad de integración Twitch básica
- [x] **Detección de estado en vivo** - Verificación automática del estado del stream
- [x] **Panel de parámetros admin** - Interfaz de configuración completa
- [x] **Sistema de cache de tokens** - Gestión inteligente de tokens API

### 🚀 Versión 1.8.0 (Planificada - Próxima versión)
- [ ] **Funcionalidades impulsadas por IA** - Recomendaciones de streams inteligentes y análisis
- [ ] **Monetización avanzada** - Modelos de suscripción y funcionalidades premium
- [ ] **Integración multi-plataforma** - Soporte YouTube, Facebook Gaming
- [ ] **Funcionalidades empresariales** - Soluciones white-label y seguridad avanzada
- [ ] **Mejoras de rendimiento** - Cache y optimización avanzados
- [ ] **Herramientas de desarrollo** - API y capacidades webhook mejoradas

### 🔮 Versión 2.0.0 (Planificada a largo plazo)
- [ ] **Asistente IA de stream** - Gestión y optimización de streams impulsados por IA
- [ ] **Suite de análisis avanzada** - Informes e insights de nivel empresarial
- [ ] **Desarrollo de aplicación móvil** - Aplicaciones móviles dedicadas
- [ ] **Integración cloud** - Soporte avanzado de almacenamiento cloud y CDN
- [ ] **Limitación de tasa API** - Gestión avanzada de cuotas y escalado
- [ ] **Soluciones white-label** - Branding personalizado y opciones de licencia

¡Aceptamos contribuciones! Ver nuestra [Guía de contribución](CONTRIBUTING.md) para detalles.

1. Hacer fork del repositorio
2. Crear rama de característica (`git checkout -b feature/amazing-feature`)
3. Commitear tus cambios (`git commit -m 'Add amazing feature'`)
4. Hacer push a la rama (`git push origin feature/amazing-feature`)
5. Abrir Pull Request

---

## 📄 Licencia

Este proyecto está licenciado bajo la **MIT License** - ver el archivo [LICENSE](LICENSE) para detalles.

---

## 🙏 Agradecimientos

- **Twitch** por la increíble plataforma de streaming y API
- **WordPress** por el increíble fundamento CMS
- **FullCalendar.js** por la funcionalidad de calendario
- **Todos los contribuidores** que ayudan a mejorar este plugin

---

## 📞 Soporte

- 📧 **Email**: support@speedyswifter.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)
- 📖 **Documentación**: [Wiki](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/wiki)
- 💬 **Discusiones**: [GitHub Discussions](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/discussions)

---

<div align="center">

**Creado con ❤️ por [SpeedySwifter](https://github.com/SpeedySwifter)**

⭐ ¡Si encuentras útil este plugin, por favor dale una estrella!

</div>
