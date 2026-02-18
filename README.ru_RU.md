# 🎮 Плагин WordPress Twitch Stream v1.7.0

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-6.9.1-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Twitch](https://img.shields.io/badge/Twitch_API-9146FF?style=for-the-badge&logo=twitch&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

[![GitHub Stars](https://img.shields.io/github/stars/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/forks)
[![GitHub Issues](https://img.shields.io/github/issues/SpeedySwifter/WordPress-Twitch-Stream-Plugin)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)

**Окончательный плагин WordPress для интеграции стримов Twitch**

[🚀 Возможности](#-возможности) • [📦 Установка](#-установка) • [🧩 Использование](#-использование) • [📋 Шорткоды](#-шорткоды) • [⚙️ Админ](#-настройки-админ) • [🌍 Языки](#-языки)

</div>

---

## 📌 Что это такое?

**Плагин WordPress Twitch Stream v1.7.0** - это комплексное решение для интеграции стримов Twitch в сайты WordPress. Он предоставляет всё: от базовой интеграции стримов до продвинутых функций вроде мобильных приложений, планирования, аналитики и многого другого.

### ✨ Основные возможности

- ✅ **Простые шорткоды** – `[twitch_stream channel="вашкнал"]`
- 🔴 **Определение статуса в эфире** – Автоматическая проверка, онлайн ли стрим
- 📺 **Адаптивный плеер** – Интеграция Twitch подстраивается под все размеры экрана
- ⚙️ **Панель администратора** – Удобная страница настроек для учетных данных API
- 🔐 **Безопасная интеграция API** – Использует официальный API Twitch Helix
- 💾 **Кеширование токенов** – Снижает вызовы API благодаря умному кешированию
- 🎨 **Настраиваемый** – CSS классы для индивидуального стиля
- 🧩 **Совместим с WordPress 6.9.1** – Протестирован с текущей версией WP
- 🎯 **Информация о стримах** – Название, игра, зрители, аватар, значок в эфире
- 📱 **Сетка множественных стримов** – Множественные стримы в виде сетки
- 🧩 **Блоки Gutenberg** – Нативная интеграция редактора блоков WordPress
- 🔧 **Поддержка конструкторов страниц** – Elementor, Oxygen, Divi, Beaver Builder & более
- 🍪 **Интеграция баннеров cookie** – Соответствует GDPR с 6 системами cookie

---

## 🚀 Продвинутые возможности (v1.7.0)

### 📱 **Интеграция мобильного приложения**
- **Прогрессивное веб-приложение (PWA)** с полным манифестом
- **Service Worker** для оффлайн-функциональности и кеширования
- **Push-уведомления** с поддержкой ключей VAPID
- **Оптимизированный интерфейс для мобильных** с сенсорными жестами
- **Запросы установки приложения** и умные баннеры
- **Определение оффлайн** и синхронизация

### 📅 **Визуальный планировщик стримов**
- **Интерактивный календарь** с интеграцией FullCalendar.js
- **Планирование перетаскиванием** и перепланирование
- **Множественные режимы просмотра** (Календарь, Список, Timeline)
- **Отслеживание статуса в реальном времени** (Запланировано/В эфире/Завершено)
- **Паттерны повторяющихся стримов** (Ежедневно/Еженедельно/Ежемесячно)
- **Продвинутую фильтрацию** по дате, статусу, категории

### 🛠️ **Продвинутый конструктор шорткодов**
- **Интерактивный GUI** для создания шорткодов Twitch
- **Живая превью** с автообновлением
- **Поддержка всех 13+ шорткодов плагина**
- **Организация по категориям**
- **Предустановленные шаблоны** для быстрого старта
- **Функциональность копирования**

### 🔒 **Интеграция плагинов членства**
- **Поддержка 6 основных плагинов членства**
- **MemberPress, RCP, PMPro, WooCommerce Memberships**
- **Ultimate Member, интеграция s2Member**
- **Система членства 4 уровней** (Бесплатно/Базовый/Премиум/VIP)
- **Ограничения контента** на основе уровня членства
- **Значки членства** и визуальные индикаторы

### 🌍 **Многоязычная поддержка (7 языков)**
- **🇺🇸 English (en_US)**
- **🇩🇪 Deutsch (de_DE)**
- **🇫🇷 Français (fr_FR)**
- **🇪🇸 Español (es_ES)**
- **🇷🇺 Русский (ru_RU)**
- **🇵🇹 Português (pt_PT)**
- **🇯🇵 日本語 (ja_JP)**

### 💰 **Интеграция пожертвований**
- **Кнопки Buy Me a Coffee** и PayPal
- **Настраиваемые формы пожертвований**
- **Цели пожертвований и отслеживание прогресса**
- **Адаптивный дизайн** с темным режимом
- **Статистика пожертвований** и аналитика

### 💬 **Интеграция чата Twitch**
- **Продвинутая интеграция чата** с селектором эмодзи
- **Модерация сообщений** и обработка команд
- **Темы чата** и опции кастомизации
- **Реал-тайм опрос сообщений**
- **Отображение значков и ролей пользователей**

### 📥 **Загрузка записи стримов**
- **Функциональность загрузки VOD**
- **Управление записями стримов**
- **Отслеживание прогресса загрузки**
- **Управление плеером видео**
- **Разрешения загрузки** и контроль доступа

### 📊 **Продвинутая панель аналитики**
- **Аналитика стримов** и метрики производительности
- **Статистика зрителей** и отслеживание вовлеченности
- **Визуализация данных в реальном времени**
- **Кастомизируемые графики** и отчеты
- **Функциональность экспорта** для анализа данных

### 🛒 **Интеграция WooCommerce**
- **Продукты, связанные со стримами**
- **Доступ к стриму, активированный покупкой**
- **Интеграция e-commerce** для членств
- **Синхронизация статуса заказов**
- **Отслеживание доходов** и аналитика

---

## 🎯 Случаи использования

### 📡 Идеально для

- 🎮 **Сайтов игр** – Отображение собственного стрима Twitch на сайте
- 🏆 **Команд eSports** – Встраивание живых матчей напрямую
- 🎥 **Создателей контента** – Интеграция стримов в блог WordPress
- 📰 **Новостных порталов** – Трансляция стримов событий вживую
- 🎪 **Сайтов событий** – Стриминг конференций & турниров
- 📱 **Мобильных приложений** – PWA с оффлайн-возможностями
- 🔒 **Сайтов членства** – Ограничения контента и контроль доступа
- 📅 **Сетей стримов** – Планирование и управление множественными стримами

### 🔧 Что он делает

```text
✓ Автоматическая проверка, онлайн ли стрим
✓ Отображение плеера Twitch только для живых стримов
✓ Отображение оффлайн-сообщения, когда стрим не активен
✓ Полностью адаптивен для всех устройств
✓ Мобильное приложение с push-уведомлениями
✓ Визуальное планирование с интерфейсом календаря
✓ Ограничения контента на основе членства
✓ Многоязычная поддержка (7 языков)
✓ Продвинутая аналитика и отчетность
✓ PWA с оффлайн-функциональностью
```

---

## 📦 Установка

### Вариант 1: Ручная (Загрузка ZIP)

1. **Скачать плагин** как ZIP
2. В WordPress: **Плагины → Установить → Загрузить плагин**
3. Выбрать файл ZIP и установить
4. **Активировать** плагин

### Вариант 2: FTP/SFTP

```bash
# Клонировать репозиторий
git clone https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin.git

# Переместить папку в wp-content/plugins/
mv WordPress-Twitch-Stream-Plugin /путь/к/wordpress/wp-content/plugins/
```

Затем активировать в админке WordPress под **Плагины**.

---

## 🔑 Настройка API Twitch

### 1️⃣ Создать приложение Twitch

Вам нужна **Приложение разработчика Twitch** для доступа к API:

1. Перейти на: [https://dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Нажать **"Register Your Application"**
3. Заполнить форму:

```
Name:                 Ваш сайт WordPress
OAuth Redirect URLs:  https://ваш-домен.com
Category:             Website Integration
```

4. **Сохранить** и записать:
   - ✅ **Client ID**
   - ✅ **Client Secret** (показывается только один раз!)

### 2️⃣ Ввести учетные данные в WordPress

1. В админке WordPress: **Настройки → API Twitch**
2. Ввести **Client ID**
3. Ввести **Client Secret**
4. **Сохранить изменения**

✅ Готово! Плагин теперь готов к использованию.

---

## 🧩 Использование

### Базовый шорткод

```text
[twitch_stream channel="shroud"]
```

### С опциями

```text
[twitch_stream channel="shroud" width="100%" height="480"]
```

### Продвинутое мобильное приложение

```text
[twitch_mobile_app theme="dark" show_notifications="true"]
```

### Планировщик стримов

```text
[twitch_stream_scheduler channel="вашкнал" view="calendar"]
```

### Контент членства

```text
[twitch_membership_content level="premium"]
Ваш премиум-контент здесь
[/twitch_membership_content]
```

---

## 📋 Справочник шорткодов

### Основные шорткоды

| Шорткод | Описание | Пример |
|-----------|-------------|---------|
| `[twitch_stream]` | Базовая интеграция стрима | `[twitch_stream channel="shroud"]` |
| `[twitch_chat]` | Независимый чат | `[twitch_chat channel="shroud"]` |
| `[twitch_follow_button]` | Кнопка подписки | `[twitch_follow_button channel="shroud"]` |
| `[twitch_subscribe_button]` | Кнопка подписки | `[twitch_subscribe_button channel="shroud"]` |
| `[twitch_clips]` | Клипы канала | `[twitch_clips channel="shroud" limit="10"]` |
| `[twitch_vod]` | Прошлые трансляции | `[twitch_vod channel="shroud" type="archive"]` |

### Продвинутые шорткоды

| Шорткод | Описание | Пример |
|-----------|-------------|---------|
| `[twitch_mobile_app]` | Интерфейс мобильного приложения | `[twitch_mobile_app theme="dark"]` |
| `[twitch_stream_scheduler]` | Визуальный планировщик | `[twitch_stream_scheduler view="calendar"]` |
| `[twitch_shortcode_builder]` | GUI конструктора шорткодов | `[twitch_shortcode_builder show_preview="true"]` |
| `[twitch_membership_content]` | Ограниченный контент | `[twitch_membership_content level="vip"]` |
| `[twitch_donations]` | Интеграция пожертвований | `[twitch_donations type="both"]` |
| `[twitch_chat_integration]` | Продвинутый чат | `[twitch_chat_integration theme="dark"]` |
| `[twitch_recording_download]` | Загрузки VOD | `[twitch_recording_download limit="10"]` |
| `[twitch_analytics]` | Панель аналитики | `[twitch_analytics time_range="7d"]` |

### Утилитарные шорткоды

| Шорткод | Описание | Пример |
|-----------|-------------|---------|
| `[twitch_pwa_install]` | Кнопка установки PWA | `[twitch_pwa_install text="Установить приложение"]` |
| `[twitch_mobile_menu]` | Мобильная навигация | `[twitch_mobile_menu position="left"]` |
| `[twitch_mobile_streams]` | Мобильная сетка стримов | `[twitch_mobile_streams limit="10"]` |
| `[twitch_push_notifications]` | Настройки уведомлений | `[twitch_push_notifications show_settings="true"]` |
| `[twitch_upcoming_streams]` | Предстоящие стримы | `[twitch_upcoming_streams limit="5"]` |
| `[twitch_stream_schedule]` | Еженедельное расписание | `[twitch_stream_schedule days="7"]` |

---

## ⚙️ Настройки админ

### Главная страница настроек
**Админка WordPress → Настройки → API Twitch**

- **Client ID & Secret** – Учетные данные API Twitch
- **Опции кеширования** – Настройки кеширования токенов и данных
- **Опции отображения** – Размеры плеера по умолчанию и темы

### Настройки мобильного приложения
**Админка WordPress → Дашборд Twitch → Мобильное приложение**

- **Конфигурация PWA** – Настройки манифеста приложения и service worker
- **Push-уведомления** – Ключи VAPID и предпочтения уведомлений
- **Настройки темы** – Кастомизация внешнего вида мобильного приложения

### Планировщик стримов
**Админка WordPress → Дашборд Twitch → Планировщик стримов**

- **Настройки календаря** – Вид по умолчанию и часовой пояс
- **Настройки уведомлений** – Предпочтения email и push-уведомлений
- **Повторяющиеся паттерны** – Автоматизированное планирование стримов

### Интеграция членства
**Админка WordPress → Дашборд Twitch → Членство**

- **Обнаружение плагинов** – Автообнаружение плагинов членства
- **Маппинг уровней** – Маппинг уровней членства к уровням доступа
- **Ограничения контента** – Настройка правил контроля доступа

---

## 📂 Структура плагина

```
WordPress-Twitch-Stream-Plugin/
│
├── 📄 wp-twitch-stream.php                    # Основной файл плагина
├── 📄 README.md                               # Документация (7 языков)
├── 📄 LICENSE                                 # Лицензия MIT
│
├── 📁 admin/
│   ├── 📄 settings-page.php                   # Страница настроек админ
│   └── 📄 admin-styles.css                    # Стили админ
│
├── 📁 includes/
│   ├── 📄 twitch-api.php                      # Менеджер API
│   ├── 📄 shortcode.php                       # Логика шорткодов
│   ├── 📄 token-manager.php                   # Кеширование токенов
│   ├── 📄 gutenberg-block.php                 # Блоки Gutenberg
│   ├── 📄 page-builder-compatibility.php      # Интеграция конструкторов страниц
│   ├── 📄 cookie-integration.php              # Интеграция баннеров cookie
│   ├── 📄 sidebar-widgets.php                 # Виджеты VOD & клипов
│   ├── 📄 donation-integration.php            # Система пожертвований
│   ├── 📄 twitch-chat-integration.php         # Продвинутый чат
│   ├── 📄 stream-recording-download.php       # Загрузки VOD
│   ├── 📄 advanced-analytics-dashboard.php    # Система аналитики
│   ├── 📄 multi-language-support.php          # Поддержка i18n
│   ├── 📄 woocommerce-integration.php         # Интеграция eCommerce
│   ├── 📄 membership-plugin-integration.php   # Система членства
│   ├── 📄 advanced-shortcode-builder.php      # Конструктор шорткодов
│   ├── 📄 visual-stream-scheduler.php         # Планировщик календаря
│   └── 📄 mobile-app-integration.php          # PWA & мобильное приложение
│
├── 📁 assets/
│   ├── 📁 css/
│   │   ├── 📄 frontend.css                     # Стили frontend
│   │   ├── 📄 block.css                        # Стили блоков Gutenberg
│   │   ├── 📄 page-builder-compatibility.css   # Стили конструкторов страниц
│   │   ├── 📄 cookie-integration.css           # Стили интеграции cookie
│   │   ├── 📄 vod-clips.css                    # Стили VOD & клипов
│   │   ├── 📄 donations.css                    # Стили системы пожертвований
│   │   ├── 📄 twitch-chat.css                  # Стили интеграции чата
│   │   ├── 📄 recording-download.css           # Стили системы загрузки
│   │   ├── 📄 analytics-dashboard.css          # Стили аналитики
│   │   ├── 📄 language-support.css             # Стили многоязычности
│   │   ├── 📄 woocommerce-integration.css      # Стили eCommerce
│   │   ├── 📄 membership-integration.css       # Стили членства
│   │   ├── 📄 shortcode-builder.css            # Стили интерфейса конструктора
│   │   ├── 📄 stream-scheduler.css             # Стили календаря
│   │   └── 📄 mobile-app.css                   # Стили мобильного приложения
│   └── 📁 js/
│       ├── 📄 player.js                        # Функции плеера
│       ├── 📄 block.js                         # JavaScript блоков Gutenberg
│       ├── 📄 oxygen-builder.js                # JS конструктора Oxygen
│       ├── 📄 divi-builder.js                  # JS конструктора Divi
│       ├── 📄 donations.js                     # JS системы пожертвований
│       ├── 📄 twitch-chat.js                   # JS интеграции чата
│       ├── 📄 recording-download.js            # JS системы загрузки
│       ├── 📄 analytics-dashboard.js           # JS аналитики
│       ├── 📄 language-support.js              # JS многоязычности
│       ├── 📄 woocommerce-integration.js       # JS eCommerce
│       ├── 📄 membership-integration.js        # JS членства
│       ├── 📄 shortcode-builder.js             # JS интерфейса конструктора
│       ├── 📄 stream-scheduler.js              # JS календаря
│       └── 📄 mobile-app.js                    # JS мобильного приложения
│
├── 📁 docs/
│   ├── 📄 cookie-banner-integration.md        # Туториал интеграции cookie
│   ├── 📄 membership-plugin-integration.md    # Руководство настройки членства
│   ├── 📄 mobile-app-setup.md                 # Конфигурация PWA
│   └── 📄 api-reference.md                    # Полная справка API
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
    ├── 📄 offline-page.html                   # Оффлайн-страница PWA
    └── 📄 mobile-app-manifest.json            # Шаблон манифеста PWA
```

---

## 🌍 Языки / Languages

Плагин поддерживает **7 языков** с полными переводами:

### 🇺🇸 English (en_US) - По умолчанию
- Полная документация и пользовательский интерфейс на английском

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

## 📊 История версий

### v1.7.0 - Интеграция мобильного приложения 🚀
- 📱 Progressive Web App (PWA) с поддержкой оффлайн
- 🔔 Push-уведомления с конфигурацией ключей VAPID
- 👆 Сенсорные жесты и оптимизированный интерфейс для мобильных
- 📅 Визуальный планировщик стримов с интерфейсом календаря
- 🛠️ Продвинутый конструктор шорткодов GUI
- 🔒 Интеграция плагинов членства (6 поддерживаемых плагинов)
- 🌍 Многоязычная поддержка (7 языков)

### v1.6.0 - Визуальный планировщик стримов 📅
- 📅 Интерактивный календарь с FullCalendar.js
- 🖱️ Планирование перетаскиванием
- 📋 Множественные режимы просмотра (Календарь/Список/Timeline)
- 🔄 Отслеживание статуса в реальном времени и обновления
- 🔁 Поддержка паттернов повторяющихся стримов
- 🎯 Продвинутые возможности фильтрации и поиска

### v1.5.0 - Продвинутый конструктор шорткодов 🛠️
- 🎨 Интерактивный GUI для создания шорткодов
- 👀 Живая превью с автообновлением
- 📋 Поддержка всех шорткодов плагина (13+)
- 📂 Организация по категориям
- 💾 Предустановленные шаблоны и опции быстрого старта
- 📋 Функциональность копирования-вставки

### v1.4.0 - Интеграция плагинов членства 🔒
- 👥 Поддержка 6 основных плагинов членства
- 🏆 Система членства 4 уровней (Бесплатно/Базовый/Премиум/VIP)
- 🚫 Ограничения контента на основе уровня членства
- 🏷️ Значки членства и визуальные индикаторы
- 🔐 Управление контролями доступа и разрешениями

### v1.3.0 - Набор продвинутых возможностей 💎
- 💰 Интеграция пожертвований (Buy Me a Coffee + PayPal)
- 💬 Интеграция чата Twitch с поддержкой эмодзи
- 📥 Функциональность загрузки записи стримов
- 📊 Продвинутая панель аналитики с графиками
- 🌍 Многоязычная поддержка (EN/DE/FR/ES/RU/PT/JA)

### v1.2.0 - Интеграция WooCommerce 🛒
- 🛒 Интеграция e-commerce для продуктов, связанных со стримами
- 💳 Доступ к стриму, активированный покупкой
- 📈 Отслеживание доходов и синхронизация заказов
- 🏪 Поддержка членств и подписок WooCommerce

### v1.1.0 - Расширенная поддержка контента 🎬
- 🎬 Поддержка VOD (Video on Demand) с архивами
- 🎞️ Интеграция и встраивание клипов Twitch
- 📱 Виджеты сайдбара для VOD и клипов
- 🧩 Расширенная совместимость конструкторов страниц

### v1.0.0 - Основная версия 🎯
- ✅ Базовая интеграция стрима Twitch
- 🔴 Определение статуса в эфире
- 📺 Интеграция адаптивного плеера
- ⚙️ Панель параметров админ
- 🔐 Безопасная интеграция API

---

## 🤝 Сотрудничество

Мы приветствуем вклад! Смотрите наше [Руководство по сотрудничеству](CONTRIBUTING.md) для деталей.

1. Форкнуть репозиторий
2. Создать ветку возможности (`git checkout -b feature/amazing-feature`)
3. Зафиксировать изменения (`git commit -m 'Add amazing feature'`)
4. Отправить в ветку (`git push origin feature/amazing-feature`)
5. Открыть Pull Request

---

## 📄 Лицензия

Этот проект лицензирован под **MIT License** - смотрите файл [LICENSE](LICENSE) для деталей.

---

## 🙏 Благодарности

- **Twitch** за невероятную платформу стриминга и API
- **WordPress** за невероятное основание CMS
- **FullCalendar.js** за функциональность календаря
- **Всех контрибьюторов** помогающих улучшить этот плагин

---

## 📞 Поддержка

- 📧 **Email**: support@speedyswifter.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)
- 📖 **Документация**: [Wiki](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/wiki)
- 💬 **Обсуждения**: [GitHub Discussions](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/discussions)

---

<div align="center">

**Создано с ❤️ [SpeedySwifter](https://github.com/SpeedySwifter)**

⭐ Если вы нашли этот плагин полезным, пожалуйста, поставьте звезду!

</div>
