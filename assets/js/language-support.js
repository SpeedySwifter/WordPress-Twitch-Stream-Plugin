/**
 * Multi-Language Support JavaScript
 */

(function($) {
    'use strict';

    // Global language support instance
    window.twitchLanguageSupport = {
        currentLanguage: '',
        supportedLanguages: {},
        enableSwitching: true,
        translations: {},
        isRTL: false
    };

    // Initialize language support
    $(document).ready(function() {
        initLanguageSupport();
        initEventHandlers();
        initAutoTranslation();
        initLanguageDetection();
    });

    /**
     * Initialize language support
     */
    function initLanguageSupport() {
        // Set global variables
        window.twitchLanguageSupport.currentLanguage = twitchLanguageSupport.currentLanguage || 'en';
        window.twitchLanguageSupport.supportedLanguages = twitchLanguageSupport.supportedLanguages || {};
        window.twitchLanguageSupport.enableSwitching = twitchLanguageSupport.enableSwitching !== false;
        
        // Check RTL
        window.twitchLanguageSupport.isRTL = $('html').attr('dir') === 'rtl';
        
        // Update language indicators
        updateLanguageIndicators();
        
        // Initialize language switchers
        initLanguageSwitchers();
        
        // Load translations
        loadTranslations();
        
        // Set HTML lang attribute
        $('html').attr('lang', window.twitchLanguageSupport.currentLanguage);
        
        // Add body class
        $('body').addClass('twitch-lang-' + window.twitchLanguageSupport.currentLanguage);
    }

    /**
     * Initialize event handlers
     */
    function initEventHandlers() {
        // Language switcher dropdown
        $(document).on('change', '.twitch-language-select', function() {
            var language = $(this).val();
            switchLanguage(language);
        });

        // Language switcher buttons
        $(document).on('click', '.twitch-language-btn', function(e) {
            e.preventDefault();
            var language = $(this).data('language');
            if (language) {
                switchLanguage(language);
            }
        });

        // Language menu items
        $(document).on('click', '.twitch-language-menu-item', function() {
            var language = $(this).data('language');
            if (language) {
                switchLanguage(language);
                closeLanguageMenu();
            }
        });

        // Close language menu on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.twitch-language-switcher').length) {
                closeLanguageMenu();
            }
        });

        // Handle URL language parameter
        handleURLLanguageParameter();

        // Handle browser language detection
        handleBrowserLanguageDetection();

        // Handle storage events for cross-tab synchronization
        window.addEventListener('storage', function(e) {
            if (e.key === 'twitch_language') {
                var language = e.newValue;
                if (language && language !== window.twitchLanguageSupport.currentLanguage) {
                    switchLanguage(language, false);
                }
            }
        });
    }

    /**
     * Initialize auto translation
     */
    function initAutoTranslation() {
        // Auto-translate elements with data-translate attribute
        $('[data-translate]').each(function() {
            var $element = $(this);
            var key = $element.data('translate');
            var translation = translate(key);
            
            if (translation && translation !== key) {
                if ($element.is('input, textarea')) {
                    $element.attr('placeholder', translation);
                } else if ($element.is('img')) {
                    $element.attr('alt', translation);
                } else {
                    $element.text(translation);
                }
            }
        });

        // Auto-translate elements with twitch: translation keys
        translateContent();
    }

    /**
     * Initialize language detection
     */
    function initLanguageDetection() {
        // Show language detection notice if needed
        if (shouldShowLanguageNotice()) {
            showLanguageNotice();
        }
    }

    /**
     * Initialize language switchers
     */
    function initLanguageSwitchers() {
        // Initialize dropdowns
        $('.twitch-language-select').each(function() {
            var $select = $(this);
            $select.val(window.twitchLanguageSupport.currentLanguage);
        });

        // Initialize buttons
        $('.twitch-language-btn').each(function() {
            var $btn = $(this);
            var language = $btn.data('language');
            if (language === window.twitchLanguageSupport.currentLanguage) {
                $btn.addClass('active');
            } else {
                $btn.removeClass('active');
            }
        });

        // Initialize custom dropdowns
        initCustomDropdowns();
    }

    /**
     * Initialize custom dropdowns
     */
    function initCustomDropdowns() {
        $('.twitch-language-dropdown').each(function() {
            var $dropdown = $(this);
            var $toggle = $dropdown.find('.twitch-language-toggle');
            var $menu = $dropdown.find('.twitch-language-menu');

            if ($toggle.length && $menu.length) {
                $toggle.on('click', function(e) {
                    e.preventDefault();
                    toggleLanguageMenu($dropdown);
                });
            }
        });
    }

    /**
     * Switch language
     */
    function switchLanguage(language, savePreference = true) {
        if (!window.twitchLanguageSupport.supportedLanguages[language]) {
            console.warn('Unsupported language:', language);
            return;
        }

        if (language === window.twitchLanguageSupport.currentLanguage) {
            return;
        }

        // Show loading state
        showLoadingState();

        // Save preference
        if (savePreference) {
            saveLanguagePreference(language);
        }

        // Update current language
        var previousLanguage = window.twitchLanguageSupport.currentLanguage;
        window.twitchLanguageSupport.currentLanguage = language;

        // Update HTML attributes
        $('html').attr('lang', language);

        // Update body classes
        $('body').removeClass('twitch-lang-' + previousLanguage);
        $('body').addClass('twitch-lang-' + language);

        // Update language switchers
        updateLanguageSwitchers(language);

        // Update language indicators
        updateLanguageIndicators();

        // Translate content
        translateContent();

        // Update RTL if needed
        updateRTL(language);

        // Trigger custom event
        $(document).trigger('twitch:languageChanged', {
            previousLanguage: previousLanguage,
            currentLanguage: language
        });

        // Hide loading state
        hideLoadingState();

        // Update URL if needed
        updateURLLanguage(language);

        // Show success message
        showSuccessMessage(language);
    }

    /**
     * Save language preference
     */
    function saveLanguagePreference(language) {
        // Save to localStorage
        localStorage.setItem('twitch_language', language);

        // Save via AJAX for logged-in users
        if (twitchLanguageSupport.ajaxUrl && twitchLanguageSupport.nonce) {
            $.ajax({
                url: twitchLanguageSupport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_language_switch',
                    language: language,
                    nonce: twitchLanguageSupport.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Language preference saved:', language);
                    }
                },
                error: function() {
                    console.warn('Failed to save language preference');
                }
            });
        }
    }

    /**
     * Update language switchers
     */
    function updateLanguageSwitchers(language) {
        // Update dropdowns
        $('.twitch-language-select').val(language);

        // Update buttons
        $('.twitch-language-btn').each(function() {
            var $btn = $(this);
            var btnLanguage = $btn.data('language');
            if (btnLanguage === language) {
                $btn.addClass('active');
            } else {
                $btn.removeClass('active');
            }
        });

        // Update menu items
        $('.twitch-language-menu-item').each(function() {
            var $item = $(this);
            var itemLanguage = $item.data('language');
            if (itemLanguage === language) {
                $item.addClass('active');
            } else {
                $item.removeClass('active');
            }
        });
    }

    /**
     * Update language indicators
     */
    function updateLanguageIndicators() {
        $('.twitch-current-language').each(function() {
            var $indicator = $(this);
            var format = $indicator.data('format') || 'name';
            var showFlag = $indicator.data('show-flag') === 'true';
            
            var output = '';
            
            if (showFlag) {
                output += getLanguageFlag(window.twitchLanguageSupport.currentLanguage) + ' ';
            }
            
            if (format === 'code') {
                output += window.twitchLanguageSupport.currentLanguage;
            } else {
                output += window.twitchLanguageSupport.supportedLanguages[window.twitchLanguageSupport.currentLanguage] || '';
            }
            
            $indicator.text(output);
        });
    }

    /**
     * Translate content
     */
    function translateContent() {
        // Translate elements with data-translate attribute
        $('[data-translate]').each(function() {
            var $element = $(this);
            var key = $element.data('translate');
            var translation = translate(key);
            
            if (translation && translation !== key) {
                if ($element.is('input, textarea')) {
                    $element.attr('placeholder', translation);
                } else if ($element.is('img')) {
                    $element.attr('alt', translation);
                } else if ($element.is('option')) {
                    $element.text(translation);
                } else {
                    $element.text(translation);
                }
            }
        });

        // Translate elements with twitch: translation keys
        $('[data-twitch-translate]').each(function() {
            var $element = $(this);
            var key = $element.data('twitch-translate');
            var translation = translate(key);
            
            if (translation && translation !== key) {
                $element.text(translation);
            }
        });

        // Translate title attributes
        $('[title][data-translate]').each(function() {
            var $element = $(this);
            var key = $element.data('translate');
            var translation = translate(key);
            
            if (translation && translation !== key) {
                $element.attr('title', translation);
            }
        });

        // Translate alt attributes
        $('[alt][data-translate]').each(function() {
            var $element = $(this);
            var key = $element.data('translate');
            var translation = translate(key);
            
            if (translation && translation !== key) {
                $element.attr('alt', translation);
            }
        });
    }

    /**
     * Translate text
     */
    function translate(key, language = null) {
        language = language || window.twitchLanguageSupport.currentLanguage;
        
        // Check if we have translations loaded
        if (window.twitchLanguageSupport.translations[language] && window.twitchLanguageSupport.translations[language][key]) {
            return window.twitchLanguageSupport.translations[language][key];
        }
        
        // Fallback to English
        if (window.twitchLanguageSupport.translations.en && window.twitchLanguageSupport.translations.en[key]) {
            return window.twitchLanguageSupport.translations.en[key];
        }
        
        // Return key if no translation found
        return key;
    }

    /**
     * Load translations
     */
    function loadTranslations() {
        // Load translations from server if needed
        if (Object.keys(window.twitchLanguageSupport.translations).length === 0) {
            // Basic translations are embedded in the PHP file
            // Additional translations can be loaded via AJAX if needed
            console.log('Using embedded translations');
        }
    }

    /**
     * Get language flag
     */
    function getLanguageFlag(code, size = 24) {
        const flags = {
            'en': '🇺🇸',
            'de': '🇩🇪',
            'fr': '🇫🇷',
            'es': '🇪🇸',
            'ru': '🇷🇺',
            'pt': '🇵🇹',
            'ja': '🇯🇵'
        };
        
        const flag = flags[code] || '🌍';
        return `<span class="twitch-flag" style="font-size: ${size}px;">${flag}</span>`;
    }

    /**
     * Toggle language menu
     */
    function toggleLanguageMenu($dropdown) {
        var $menu = $dropdown.find('.twitch-language-menu');
        
        if ($menu.is(':visible')) {
            closeLanguageMenu();
        } else {
            closeAllLanguageMenus();
            $menu.show();
            $dropdown.addClass('open');
        }
    }

    /**
     * Close language menu
     */
    function closeLanguageMenu() {
        $('.twitch-language-menu').hide();
        $('.twitch-language-dropdown').removeClass('open');
    }

    /**
     * Close all language menus
     */
    function closeAllLanguageMenus() {
        $('.twitch-language-menu').hide();
        $('.twitch-language-dropdown').removeClass('open');
    }

    /**
     * Handle URL language parameter
     */
    function handleURLLanguageParameter() {
        var urlParams = new URLSearchParams(window.location.search);
        var langParam = urlParams.get('lang');
        
        if (langParam && window.twitchLanguageSupport.supportedLanguages[langParam]) {
            switchLanguage(langParam);
        }
    }

    /**
     * Handle browser language detection
     */
    function handleBrowserLanguageDetection() {
        // Check if user has a saved preference
        var savedLanguage = localStorage.getItem('twitch_language') || 
                           getCookie('twitch_language');
        
        if (savedLanguage && window.twitchLanguageSupport.supportedLanguages[savedLanguage]) {
            if (savedLanguage !== window.twitchLanguageSupport.currentLanguage) {
                switchLanguage(savedLanguage, false);
            }
            return;
        }
        
        // Detect browser language
        var browserLanguage = detectBrowserLanguage();
        
        if (browserLanguage && window.twitchLanguageSupport.supportedLanguages[browserLanguage]) {
            showLanguageDetectionNotice(browserLanguage);
        }
    }

    /**
     * Detect browser language
     */
    function detectBrowserLanguage() {
        var languages = navigator.languages || [navigator.language];
        
        for (var i = 0; i < languages.length; i++) {
            var lang = languages[i].substring(0, 2);
            if (window.twitchLanguageSupport.supportedLanguages[lang]) {
                return lang;
            }
        }
        
        return null;
    }

    /**
     * Show language detection notice
     */
    function showLanguageDetectionNotice(detectedLanguage) {
        var noticeHtml = `
            <div class="twitch-language-notice">
                ${getLanguageFlag(detectedLanguage)} 
                We detected your browser language is ${window.twitchLanguageSupport.supportedLanguages[detectedLanguage]}. 
                <a href="#" class="twitch-language-switch-link" data-language="${detectedLanguage}">Switch to ${window.twitchLanguageSupport.supportedLanguages[detectedLanguage]}</a>
                <a href="#" class="twitch-language-dismiss">×</a>
            </div>
        `;
        
        $('body').prepend(noticeHtml);
        
        // Handle dismiss
        $(document).on('click', '.twitch-language-dismiss', function(e) {
            e.preventDefault();
            $('.twitch-language-notice').fadeOut(function() {
                $(this).remove();
            });
            localStorage.setItem('twitch_language_notice_dismissed', 'true');
        });
        
        // Handle switch
        $(document).on('click', '.twitch-language-switch-link', function(e) {
            e.preventDefault();
            var language = $(this).data('language');
            switchLanguage(language);
            $('.twitch-language-notice').fadeOut(function() {
                $(this).remove();
            });
        });
    }

    /**
     * Should show language notice
     */
    function shouldShowLanguageNotice() {
        // Don't show if dismissed
        if (localStorage.getItem('twitch_language_notice_dismissed') === 'true') {
            return false;
        }
        
        // Don't show if user already has a preference
        if (localStorage.getItem('twitch_language') || getCookie('twitch_language')) {
            return false;
        }
        
        // Don't show if current language is already detected
        var detectedLanguage = detectBrowserLanguage();
        if (detectedLanguage === window.twitchLanguageSupport.currentLanguage) {
            return false;
        }
        
        return true;
    }

    /**
     * Update RTL
     */
    function updateRTL(language) {
        var rtlLanguages = ['ar', 'he', 'fa', 'ur'];
        var isRTL = rtlLanguages.includes(language);
        
        if (isRTL !== window.twitchLanguageSupport.isRTL) {
            window.twitchLanguageSupport.isRTL = isRTL;
            
            if (isRTL) {
                $('html').attr('dir', 'rtl');
                $('body').addClass('rtl');
            } else {
                $('html').attr('dir', 'ltr');
                $('body').removeClass('rtl');
            }
        }
    }

    /**
     * Update URL language
     */
    function updateURLLanguage(language) {
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location);
            
            if (language === 'en') {
                url.searchParams.delete('lang');
            } else {
                url.searchParams.set('lang', language);
            }
            
            window.history.replaceState({}, '', url);
        }
    }

    /**
     * Show loading state
     */
    function showLoadingState() {
        $('.twitch-language-switcher').addClass('twitch-language-loading');
    }

    /**
     * Hide loading state
     */
    function hideLoadingState() {
        $('.twitch-language-switcher').removeClass('twitch-language-loading');
    }

    /**
     * Show success message
     */
    function showSuccessMessage(language) {
        var message = `Language switched to ${window.twitchLanguageSupport.supportedLanguages[language]}`;
        
        // Create toast notification
        var $toast = $(`
            <div class="twitch-language-toast">
                ${getLanguageFlag(language)} ${message}
            </div>
        `);
        
        $('body').append($toast);
        
        // Show toast
        setTimeout(function() {
            $toast.addClass('show');
        }, 100);
        
        // Hide toast after 3 seconds
        setTimeout(function() {
            $toast.removeClass('show');
            setTimeout(function() {
                $toast.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Get cookie value
     */
    function getCookie(name) {
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        
        return null;
    }

    /**
     * Show language notice
     */
    function showLanguageNotice() {
        // This would show a notice about language detection
        console.log('Language notice would be shown here');
    }

    /**
     * Expose functions globally
     */
    window.TwitchLanguage = {
        switch: switchLanguage,
        translate: translate,
        getCurrentLanguage: function() {
            return window.twitchLanguageSupport.currentLanguage;
        },
        getSupportedLanguages: function() {
            return window.twitchLanguageSupport.supportedLanguages;
        },
        isRTL: function() {
            return window.twitchLanguageSupport.isRTL;
        }
    };

    // Global function for inline onclick handlers
    window.switchLanguage = switchLanguage;

})(jQuery);
