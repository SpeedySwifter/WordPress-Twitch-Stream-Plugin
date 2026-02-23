<?php
/**
 * Multi-Language Support for Twitch Stream Plugin
 * Supports: EN/DE/FR/ES/RU/PT/JA
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPSWIFTER_Twitch_Multi_Language_Support {
    
    private $supported_languages = array(
        'en' => 'English',
        'de' => 'Deutsch',
        'fr' => 'Français',
        'es' => 'Español',
        'ru' => 'Русский',
        'pt' => 'Português',
        'ja' => '日本語'
    );
    
    private $current_language;
    private $language_settings;
    private $translations = array();
    
    public function __construct() {
        // Delay initialization until WordPress is loaded
        add_action('init', array($this, 'init'));
    }

    public function init() {
        $this->language_settings = $this->get_language_settings();
        $this->current_language = $this->get_current_language();
        
        add_action('plugins_loaded', array($this, 'init_language_support'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_language_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_language_scripts'));
        add_action('wp_ajax_spswifter_twitch_language_switch', array($this, 'handle_language_switch'));
        add_action('wp_ajax_nopriv_spswifter_twitch_language_switch', array($this, 'handle_language_switch'));
        add_action('admin_menu', array($this, 'add_language_settings_menu'));
        add_filter('plugin_locale', array($this, 'set_plugin_locale'), 10, 2);
        add_filter('widget_text', array($this, 'translate_widget_text'), 10, 3);
        add_filter('the_content', array($this, 'translate_content'), 10, 1);
        add_filter('the_title', array($this, 'translate_title'), 10, 2);
        
        // Register shortcodes
        add_action('init', array($this, 'register_language_shortcodes'));
        
        // Load translations
        $this->load_translations();
    }
    
    /**
     * Initialize language support
     */
    public function init_language_support() {
        // Set locale
        $locale = $this->get_locale();
        setlocale(LC_ALL, $locale);
        
        // Add language attributes to HTML
        add_filter('language_attributes', array($this, 'add_language_attributes'));
        
        // Add language class to body
        add_filter('body_class', array($this, 'add_language_body_class'));
        
        // Handle URL language switching
        $this->handle_url_language_switching();
    }
    
    /**
     * Load textdomain
     */
    public function load_textdomain() {
        $domain = 'speedyswifter-twitch';
        $locale = $this->get_locale();
        
        // Traditional WordPress language files
        load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Load our custom translations
        $mofile = SPSWIFTER_TWITCH_PLUGIN_DIR . 'languages/spswifter-twitch-' . $locale . '.mo';
        if (file_exists($mofile)) {
            load_textdomain($domain, $mofile);
        }
    }
    
    /**
     * Get current language
     */
    private function get_current_language() {
        // Priority order: URL parameter > User preference > Browser > Default
        
        // 1. URL parameter
        if (isset($_GET['lang']) && isset($this->supported_languages[$_GET['lang']])) {
            return sanitize_text_field($_GET['lang']);
        }
        
        // 2. User preference (if logged in)
        if (is_user_logged_in()) {
            $user_lang = get_user_meta(get_current_user_id(), 'spswifter_twitch_language', true);
            if ($user_lang && isset($this->supported_languages[$user_lang])) {
                return $user_lang;
            }
        }
        
        // 3. Browser language
        $browser_lang = $this->get_browser_language();
        if ($browser_lang && isset($this->supported_languages[$browser_lang])) {
            return $browser_lang;
        }
        
        // 4. Default from settings
        return $this->language_settings['default_language'] ?? 'en';
    }
    
    /**
     * Get browser language
     */
    private function get_browser_language() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($langs as $lang) {
                $lang = substr(trim($lang), 0, 2);
                if (isset($this->supported_languages[$lang])) {
                    return $lang;
                }
            }
        }
        return null;
    }
    
    /**
     * Get locale
     */
    private function get_locale() {
        $locale_map = array(
            'en' => 'en_US',
            'de' => 'de_DE',
            'fr' => 'fr_FR',
            'es' => 'es_ES',
            'ru' => 'ru_RU',
            'pt' => 'pt_PT',
            'ja' => 'ja_JP'
        );
        
        return $locale_map[$this->current_language] ?? 'en_US';
    }
    
    /**
     * Load translations
     */
    private function load_translations() {
        $this->translations = array(
            'en' => array(
                'stream_live' => 'Stream is Live',
                'stream_offline' => 'Stream is Offline',
                'viewers' => 'Viewers',
                'duration' => 'Duration',
                'followers' => 'Followers',
                'subscribers' => 'Subscribers',
                'donate' => 'Donate',
                'subscribe' => 'Subscribe',
                'chat' => 'Chat',
                'analytics' => 'Analytics',
                'recordings' => 'Recordings',
                'download' => 'Download',
                'watch' => 'Watch',
                'play' => 'Play',
                'pause' => 'Pause',
                'mute' => 'Mute',
                'unmute' => 'Unmute',
                'fullscreen' => 'Fullscreen',
                'loading' => 'Loading...',
                'error' => 'Error',
                'success' => 'Success',
                'no_streams' => 'No streams available',
                'no_recordings' => 'No recordings available',
                'select_channel' => 'Select Channel',
                'last_7_days' => 'Last 7 Days',
                'last_30_days' => 'Last 30 Days',
                'last_90_days' => 'Last 90 Days',
                'last_year' => 'Last Year',
                'total_viewers' => 'Total Viewers',
                'total_duration' => 'Total Duration',
                'total_revenue' => 'Total Revenue',
                'top_streams' => 'Top Streams',
                'top_games' => 'Top Games',
                'overview' => 'Overview',
                'engagement' => 'Engagement',
                'monetization' => 'Monetization',
                'audience' => 'Audience',
                'export_csv' => 'Export CSV',
                'export_json' => 'Export JSON',
                'refresh' => 'Refresh',
                'settings' => 'Settings',
                'language' => 'Language',
                'theme' => 'Theme',
                'dark_mode' => 'Dark Mode',
                'light_mode' => 'Light Mode'
            ),
            'de' => array(
                'stream_live' => 'Stream ist Live',
                'stream_offline' => 'Stream ist Offline',
                'viewers' => 'Zuschauer',
                'duration' => 'Dauer',
                'followers' => 'Follower',
                'subscribers' => 'Abonnenten',
                'donate' => 'Spenden',
                'subscribe' => 'Abonnieren',
                'chat' => 'Chat',
                'analytics' => 'Analytics',
                'recordings' => 'Aufnahmen',
                'download' => 'Download',
                'watch' => 'Ansehen',
                'play' => 'Abspielen',
                'pause' => 'Pause',
                'mute' => 'Stumm',
                'unmute' => 'Ton ein',
                'fullscreen' => 'Vollbild',
                'loading' => 'Laden...',
                'error' => 'Fehler',
                'success' => 'Erfolg',
                'no_streams' => 'Keine Streams verfügbar',
                'no_recordings' => 'Keine Aufnahmen verfügbar',
                'select_channel' => 'Kanal auswählen',
                'last_7_days' => 'Letzte 7 Tage',
                'last_30_days' => 'Letzte 30 Tage',
                'last_90_days' => 'Letzte 90 Tage',
                'last_year' => 'Letztes Jahr',
                'total_viewers' => 'Gesamt-Zuschauer',
                'total_duration' => 'Gesamt-Dauer',
                'total_revenue' => 'Gesamt-Einnahmen',
                'top_streams' => 'Top Streams',
                'top_games' => 'Top Spiele',
                'overview' => 'Übersicht',
                'engagement' => 'Engagement',
                'monetization' => 'Monetarisierung',
                'audience' => 'Zielgruppe',
                'export_csv' => 'CSV Export',
                'export_json' => 'JSON Export',
                'refresh' => 'Aktualisieren',
                'settings' => 'Einstellungen',
                'language' => 'Sprache',
                'theme' => 'Theme',
                'dark_mode' => 'Dark Mode',
                'light_mode' => 'Light Mode'
            ),
            'fr' => array(
                'stream_live' => 'Le Stream est en Direct',
                'stream_offline' => 'Le Stream est Hors Ligne',
                'viewers' => 'Spectateurs',
                'duration' => 'Durée',
                'followers' => 'Abonnés',
                'subscribers' => 'Abonnés',
                'donate' => 'Faire un Don',
                'subscribe' => 'S\'abonner',
                'chat' => 'Chat',
                'analytics' => 'Analytiques',
                'recordings' => 'Enregistrements',
                'download' => 'Télécharger',
                'watch' => 'Regarder',
                'play' => 'Lire',
                'pause' => 'Pause',
                'mute' => 'Couper le Son',
                'unmute' => 'Activer le Son',
                'fullscreen' => 'Plein Écran',
                'loading' => 'Chargement...',
                'error' => 'Erreur',
                'success' => 'Succès',
                'no_streams' => 'Aucun stream disponible',
                'no_recordings' => 'Aucun enregistrement disponible',
                'select_channel' => 'Sélectionner une Chaîne',
                'last_7_days' => 'Derniers 7 Jours',
                'last_30_days' => 'Derniers 30 Jours',
                'last_90_days' => 'Derniers 90 Jours',
                'last_year' => 'Année Dernière',
                'total_viewers' => 'Total Spectateurs',
                'total_duration' => 'Durée Totale',
                'total_revenue' => 'Revenus Totaux',
                'top_streams' => 'Top Streams',
                'top_games' => 'Top Jeux',
                'overview' => 'Aperçu',
                'engagement' => 'Engagement',
                'monetization' => 'Monétisation',
                'audience' => 'Audience',
                'export_csv' => 'Exporter CSV',
                'export_json' => 'Exporter JSON',
                'refresh' => 'Actualiser',
                'settings' => 'Paramètres',
                'language' => 'Langue',
                'theme' => 'Thème',
                'dark_mode' => 'Mode Sombre',
                'light_mode' => 'Mode Clair'
            ),
            'es' => array(
                'stream_live' => 'El Stream está en Vivo',
                'stream_offline' => 'El Stream está Desconectado',
                'viewers' => 'Espectadores',
                'duration' => 'Duración',
                'followers' => 'Seguidores',
                'subscribers' => 'Suscriptores',
                'donate' => 'Donar',
                'subscribe' => 'Suscribirse',
                'chat' => 'Chat',
                'analytics' => 'Analíticas',
                'recordings' => 'Grabaciones',
                'download' => 'Descargar',
                'watch' => 'Ver',
                'play' => 'Reproducir',
                'pause' => 'Pausar',
                'mute' => 'Silenciar',
                'unmute' => 'Activar Sonido',
                'fullscreen' => 'Pantalla Completa',
                'loading' => 'Cargando...',
                'error' => 'Error',
                'success' => 'Éxito',
                'no_streams' => 'No hay streams disponibles',
                'no_recordings' => 'No hay grabaciones disponibles',
                'select_channel' => 'Seleccionar Canal',
                'last_7_days' => 'Últimos 7 Días',
                'last_30_days' => 'Últimos 30 Días',
                'last_90_days' => 'Últimos 90 Días',
                'last_year' => 'Último Año',
                'total_viewers' => 'Total Espectadores',
                'total_duration' => 'Duración Total',
                'total_revenue' => 'Ingresos Totales',
                'top_streams' => 'Top Streams',
                'top_games' => 'Top Juegos',
                'overview' => 'Resumen',
                'engagement' => 'Compromiso',
                'monetization' => 'Monetización',
                'audience' => 'Audiencia',
                'export_csv' => 'Exportar CSV',
                'export_json' => 'Exportar JSON',
                'refresh' => 'Actualizar',
                'settings' => 'Configuración',
                'language' => 'Idioma',
                'theme' => 'Tema',
                'dark_mode' => 'Modo Oscuro',
                'light_mode' => 'Modo Claro'
            ),
            'ru' => array(
                'stream_live' => 'Стрим в прямом эфире',
                'stream_offline' => 'Стрим не в сети',
                'viewers' => 'Зрители',
                'duration' => 'Длительность',
                'followers' => 'Подписчики',
                'subscribers' => 'Подписчики',
                'donate' => 'Пожертвовать',
                'subscribe' => 'Подписаться',
                'chat' => 'Чат',
                'analytics' => 'Аналитика',
                'recordings' => 'Записи',
                'download' => 'Скачать',
                'watch' => 'Смотреть',
                'play' => 'Воспроизвести',
                'pause' => 'Пауза',
                'mute' => 'Без звука',
                'unmute' => 'Включить звук',
                'fullscreen' => 'Полноэкранный режим',
                'loading' => 'Загрузка...',
                'error' => 'Ошибка',
                'success' => 'Успех',
                'no_streams' => 'Нет доступных стримов',
                'no_recordings' => 'Нет доступных записей',
                'select_channel' => 'Выбрать канал',
                'last_7_days' => 'Последние 7 дней',
                'last_30_days' => 'Последние 30 дней',
                'last_90_days' => 'Последние 90 дней',
                'last_year' => 'Последний год',
                'total_viewers' => 'Всего зрителей',
                'total_duration' => 'Общая длительность',
                'total_revenue' => 'Общий доход',
                'top_streams' => 'Топ стримы',
                'top_games' => 'Топ игры',
                'overview' => 'Обзор',
                'engagement' => 'Вовлеченность',
                'monetization' => 'Монетизация',
                'audience' => 'Аудитория',
                'export_csv' => 'Экспорт CSV',
                'export_json' => 'Экспорт JSON',
                'refresh' => 'Обновить',
                'settings' => 'Настройки',
                'language' => 'Язык',
                'theme' => 'Тема',
                'dark_mode' => 'Темный режим',
                'light_mode' => 'Светлый режим'
            ),
            'pt' => array(
                'stream_live' => 'Stream está ao Vivo',
                'stream_offline' => 'Stream está Offline',
                'viewers' => 'Espectadores',
                'duration' => 'Duração',
                'followers' => 'Seguidores',
                'subscribers' => 'Assinantes',
                'donate' => 'Doar',
                'subscribe' => 'Assinar',
                'chat' => 'Chat',
                'analytics' => 'Análises',
                'recordings' => 'Gravações',
                'download' => 'Baixar',
                'watch' => 'Assistir',
                'play' => 'Reproduzir',
                'pause' => 'Pausar',
                'mute' => 'Sem Som',
                'unmute' => 'Com Som',
                'fullscreen' => 'Tela Cheia',
                'loading' => 'Carregando...',
                'error' => 'Erro',
                'success' => 'Sucesso',
                'no_streams' => 'Nenhum stream disponível',
                'no_recordings' => 'Nenhuma gravação disponível',
                'select_channel' => 'Selecionar Canal',
                'last_7_days' => 'Últimos 7 Dias',
                'last_30_days' => 'Últimos 30 Dias',
                'last_90_days' => 'Últimos 90 Dias',
                'last_year' => 'Último Ano',
                'total_viewers' => 'Total Espectadores',
                'total_duration' => 'Duração Total',
                'total_revenue' => 'Receita Total',
                'top_streams' => 'Top Streams',
                'top_games' => 'Top Jogos',
                'overview' => 'Visão Geral',
                'engagement' => 'Engajamento',
                'monetization' => 'Monetização',
                'audience' => 'Audiência',
                'export_csv' => 'Exportar CSV',
                'export_json' => 'Exportar JSON',
                'refresh' => 'Atualizar',
                'settings' => 'Configurações',
                'language' => 'Idioma',
                'theme' => 'Tema',
                'dark_mode' => 'Modo Escuro',
                'light_mode' => 'Modo Claro'
            ),
            'ja' => array(
                'stream_live' => 'ストレームがライブ中',
                'stream_offline' => 'ストリームがオフライン',
                'viewers' => '視聴者',
                'duration' => '時間',
                'followers' => 'フォロワー',
                'subscribers' => '購読者',
                'donate' => '寄付',
                'subscribe' => '購読',
                'chat' => 'チャット',
                'analytics' => '分析',
                'recordings' => '録画',
                'download' => 'ダウンロード',
                'watch' => '視聴',
                'play' => '再生',
                'pause' => '一時停止',
                'mute' => 'ミュート',
                'unmute' => 'ミュート解除',
                'fullscreen' => 'フルスクリーン',
                'loading' => '読み込み中...',
                'error' => 'エラー',
                'success' => '成功',
                'no_streams' => '利用可能なストリームがありません',
                'no_recordings' => '利用可能な録画がありません',
                'select_channel' => 'チャンネルを選択',
                'last_7_days' => '過去7日間',
                'last_30_days' => '過去30日間',
                'last_90_days' => '過去90日間',
                'last_year' => '過去1年間',
                'total_viewers' => '総視聴者数',
                'total_duration' => '総時間',
                'total_revenue' => '総収益',
                'top_streams' => 'トップストリーム',
                'top_games' => 'トップゲーム',
                'overview' => '概要',
                'engagement' => 'エンゲージメント',
                'monetization' => '収益化',
                'audience' => 'オーディエンス',
                'export_csv' => 'CSVエクスポート',
                'export_json' => 'JSONエクスポート',
                'refresh' => '更新',
                'settings' => '設定',
                'language' => '言語',
                'theme' => 'テーマ',
                'dark_mode' => 'ダークモード',
                'light_mode' => 'ライトモード'
            )
        );
    }
    
    /**
     * Translate text
     */
    public function translate($key, $language = null) {
        $language = $language ?: $this->current_language;
        
        if (isset($this->translations[$language][$key])) {
            return $this->translations[$language][$key];
        }
        
        // Fallback to English
        if (isset($this->translations['en'][$key])) {
            return $this->translations['en'][$key];
        }
        
        // Return key if no translation found
        return $key;
    }
    
    /**
     * Register language shortcodes
     */
    public function register_language_shortcodes() {
        add_shortcode('spswifter_twitch_language_switcher', array($this, 'render_language_switcher'));
        add_shortcode('spswifter_twitch_current_language', array($this, 'render_current_language'));
        add_shortcode('spswifter_twitch_translate', array($this, 'render_translate_shortcode'));
        add_shortcode('spswifter_twitch_language_flag', array($this, 'render_language_flag'));
    }
    
    /**
     * Render language switcher
     */
    public function render_language_switcher($atts) {
        $atts = shortcode_atts(array(
            'style' => 'dropdown',
            'show_flags' => 'true',
            'show_names' => 'true',
            'class' => '',
        ), $atts);
        
        ob_start();
        ?>
        <div class="twitch-language-switcher twitch-switcher-<?php echo esc_attr($atts['style']); ?> <?php echo esc_attr($atts['class']); ?>">
            <?php if ($atts['style'] === 'dropdown'): ?>
                <select class="twitch-language-select" onchange="switchLanguage(this.value)">
                    <?php foreach ($this->supported_languages as $code => $name): ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($code, $this->current_language); ?>>
                            <?php if ($atts['show_flags'] === 'true'): ?>
                                <?php echo $this->get_language_flag($code); ?>
                            <?php endif; ?>
                            <?php if ($atts['show_names'] === 'true'): ?>
                                <?php echo esc_html($name); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <div class="twitch-language-buttons">
                    <?php foreach ($this->supported_languages as $code => $name): ?>
                        <button class="twitch-language-btn <?php echo $code === $this->current_language ? 'active' : ''; ?>" 
                                onclick="switchLanguage('<?php echo esc_attr($code); ?>')"
                                data-language="<?php echo esc_attr($code); ?>">
                            <?php if ($atts['show_flags'] === 'true'): ?>
                                <span class="twitch-language-flag"><?php echo $this->get_language_flag($code); ?></span>
                            <?php endif; ?>
                            <?php if ($atts['show_names'] === 'true'): ?>
                                <span class="twitch-language-name"><?php echo esc_html($name); ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render current language
     */
    public function render_current_language($atts) {
        $atts = shortcode_atts(array(
            'format' => 'name',
            'show_flag' => 'false',
        ), $atts);
        
        $output = '';
        
        if ($atts['show_flag'] === 'true') {
            $output .= $this->get_language_flag($this->current_language) . ' ';
        }
        
        if ($atts['format'] === 'code') {
            $output .= $this->current_language;
        } else {
            $output .= $this->supported_languages[$this->current_language];
        }
        
        return $output;
    }
    
    /**
     * Render translate shortcode
     */
    public function render_translate_shortcode($atts) {
        $atts = shortcode_atts(array(
            'key' => '',
            'lang' => '',
        ), $atts);
        
        if (empty($atts['key'])) {
            return '';
        }
        
        return $this->translate($atts['key'], $atts['lang'] ?: null);
    }
    
    /**
     * Render language flag
     */
    public function render_language_flag($atts) {
        $atts = shortcode_atts(array(
            'code' => '',
            'size' => '24',
        ), $atts);
        
        if (empty($atts['code'])) {
            $atts['code'] = $this->current_language;
        }
        
        return $this->get_language_flag($atts['code'], $atts['size']);
    }
    
    /**
     * Get language flag
     */
    private function get_language_flag($code, $size = 24) {
        $flags = array(
            'en' => '🇺🇸',
            'de' => '🇩🇪',
            'fr' => '🇫🇷',
            'es' => '🇪🇸',
            'ru' => '🇷🇺',
            'pt' => '🇵🇹',
            'ja' => '🇯🇵'
        );
        
        $flag = $flags[$code] ?? '🌍';
        return '<span class="twitch-flag" style="font-size: ' . intval($size) . 'px;">' . $flag . '</span>';
    }
    
    /**
     * Handle language switch
     */
    public function handle_language_switch() {
        check_ajax_referer('spswifter_twitch_language_nonce', 'nonce');
        
        $language = sanitize_text_field($_POST['language'] ?? '');
        
        if (!isset($this->supported_languages[$language])) {
            wp_send_json_error('Invalid language');
        }
        
        // Save user preference if logged in
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'spswifter_twitch_language', $language);
        }
        
        // Save to session
        $_SESSION['spswifter_twitch_language'] = $language;
        
        // Set cookie
        setcookie('spswifter_twitch_language', $language, time() + (86400 * 30), '/');
        
        wp_send_json_success(array('language' => $language));
    }
    
    /**
     * Add language settings menu
     */
    public function add_language_settings_menu() {
        add_submenu_page(
            'twitch-dashboard',
            'Language Settings',
            'Language',
            'manage_options',
            'twitch-language-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Twitch Language Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('spswifter_twitch_language_settings'); ?>
                <?php do_settings_sections('spswifter_twitch_language_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Default Language</th>
                        <td>
                            <select name="spswifter_twitch_language_settings[default_language]">
                                <?php foreach ($this->supported_languages as $code => $name): ?>
                                    <option value="<?php echo esc_attr($code); ?>" <?php selected($this->language_settings['default_language'], $code); ?>>
                                        <?php echo $this->get_language_flag($code); ?> <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Language Switching</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_language_settings[enable_switching]" 
                                   <?php checked($this->language_settings['enable_switching'], true); ?> />
                            <label>Allow users to switch languages</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Show Language Switcher</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_language_settings[show_switcher]" 
                                   <?php checked($this->language_settings['show_switcher'], true); ?> />
                            <label>Show language switcher in widgets</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Auto-detect Language</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_language_settings[auto_detect]" 
                                   <?php checked($this->language_settings['auto_detect'], true); ?> />
                            <label>Detect language from browser settings</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">URL Language Parameter</th>
                        <td>
                            <input type="checkbox" name="spswifter_twitch_language_settings[url_parameter]" 
                                   <?php checked($this->language_settings['url_parameter'], true); ?> />
                            <label>Allow language switching via URL parameter (?lang=de)</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enabled Languages</th>
                        <td>
                            <div class="twitch-language-checkboxes">
                                <?php foreach ($this->supported_languages as $code => $name): ?>
                                    <label>
                                        <input type="checkbox" name="spswifter_twitch_language_settings[enabled_languages][]" 
                                               value="<?php echo esc_attr($code); ?>"
                                               <?php checked(in_array($code, $this->language_settings['enabled_languages'] ?? array_keys($this->supported_languages))); ?> />
                                        <?php echo $this->get_language_flag($code); ?> <?php echo esc_html($name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="description">Select which languages to make available to users</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get language settings
     */
    private function get_language_settings() {
        return get_option('spswifter_twitch_language_settings', array(
            'default_language' => 'en',
            'enable_switching' => true,
            'show_switcher' => true,
            'auto_detect' => true,
            'url_parameter' => true,
            'enabled_languages' => array_keys($this->supported_languages),
        ));
    }
    
    /**
     * Enqueue language scripts
     */
    public function enqueue_language_scripts() {
        wp_enqueue_style(
            'twitch-language-support',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/language-support.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
        
        wp_enqueue_script(
            'twitch-language-support',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/js/language-support.js',
            array('jquery'),
            SPSWIFTER_TWITCH_VERSION,
            true
        );
        
        wp_localize_script('twitch-language-support', 'twitchLanguageSupport', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spswifter_twitch_language_nonce'),
            'currentLanguage' => $this->current_language,
            'supportedLanguages' => $this->supported_languages,
            'enableSwitching' => $this->language_settings['enable_switching'] ?? true,
        ));
    }
    
    /**
     * Enqueue admin language scripts
     */
    public function enqueue_admin_language_scripts() {
        wp_enqueue_style(
            'twitch-language-admin',
            SPSWIFTER_TWITCH_PLUGIN_URL . 'assets/css/language-admin.css',
            array(),
            SPSWIFTER_TWITCH_VERSION
        );
    }
    
    /**
     * Add language attributes
     */
    public function add_language_attributes($output) {
        $output .= ' lang="' . esc_attr($this->current_language) . '"';
        return $output;
    }
    
    /**
     * Add language body class
     */
    public function add_language_body_class($classes) {
        $classes[] = 'twitch-lang-' . $this->current_language;
        return $classes;
    }
    
    /**
     * Handle URL language switching
     */
    private function handle_url_language_switching() {
        if (isset($_GET['lang']) && isset($this->supported_languages[$_GET['lang']])) {
            $language = sanitize_text_field($_GET['lang']);
            
            // Save to session
            $_SESSION['spswifter_twitch_language'] = $language;
            
            // Set cookie
            setcookie('spswifter_twitch_language', $language, time() + (86400 * 30), '/');
            
            // Update current language
            $this->current_language = $language;
        }
    }
    
    /**
     * Set plugin locale
     */
    public function set_plugin_locale($locale, $domain) {
        if ($domain === 'speedyswifter-twitch') {
            return $this->get_locale();
        }
        return $locale;
    }
    
    /**
     * Translate widget text
     */
    public function translate_widget_text($text, $instance, $widget) {
        // Check if text is a translation key
        if (strpos($text, 'twitch:') === 0) {
            $key = substr($text, 7);
            return $this->translate($key);
        }
        
        return $text;
    }
    
    /**
     * Translate content
     */
    public function translate_content($content) {
        // Replace translation keys in content
        return preg_replace_callback('/\{twitch:([^}]+)\}/', function($matches) {
            return $this->translate($matches[1]);
        }, $content);
    }
    
    /**
     * Translate title
     */
    public function translate_title($title, $id) {
        // Replace translation keys in title
        return preg_replace_callback('/\{twitch:([^}]+)\}/', function($matches) {
            return $this->translate($matches[1]);
        }, $title);
    }
    
    /**
     * Get supported languages
     */
    public function get_supported_languages() {
        return $this->supported_languages;
    }
    
    /**
     * Check if language is supported
     */
    public function is_language_supported($language) {
        return isset($this->supported_languages[$language]);
    }
}

// Initialize multi-language support
new SPSWIFTER_Twitch_Multi_Language_Support();
