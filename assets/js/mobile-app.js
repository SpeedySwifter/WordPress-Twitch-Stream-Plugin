/**
 * Mobile App Integration JavaScript
 */

(function($) {
    'use strict';

    // Main Mobile App class
    var TwitchMobileApp = {
        isInstalled: false,
        isOnline: navigator.onLine,
        deferredPrompt: null,
        pushSubscription: null,
        notifications: [],
        currentSection: 'streams',
        theme: 'light',
        settings: {},

        init: function() {
            this.bindEvents();
            this.initPWA();
            this.initPushNotifications();
            this.initOfflineDetection();
            this.initTheme();
            this.loadSettings();
            this.updateUI();
            this.showInstallPrompt();
        },

        bindEvents: function() {
            var self = this;

            // Navigation
            $(document).on('click', '.twitch-mobile-menu-toggle', function(e) {
                e.preventDefault();
                self.toggleSidebar();
            });

            $(document).on('click', '.twitch-menu-close', function(e) {
                e.preventDefault();
                self.closeSidebar();
            });

            $(document).on('click', '.twitch-nav-item', function(e) {
                e.preventDefault();
                var section = $(this).attr('href').substring(1);
                self.switchSection(section);
                self.closeSidebar();
            });

            $(document).on('click', '.twitch-tab-btn', function(e) {
                e.preventDefault();
                var section = $(this).data('section');
                self.switchSection(section);
            });

            // PWA Install
            $(document).on('click', '.twitch-pwa-install-btn, .twitch-install-accept', function(e) {
                e.preventDefault();
                self.installPWA();
            });

            $(document).on('click', '.twitch-install-dismiss', function(e) {
                e.preventDefault();
                self.dismissInstallPrompt();
            });

            // Notifications
            $(document).on('click', '.twitch-notification-toggle', function(e) {
                e.preventDefault();
                self.toggleNotificationPanel();
            });

            $(document).on('click', '.twitch-notification-close', function(e) {
                e.preventDefault();
                self.closeNotificationPanel();
            });

            $(document).on('click', '.twitch-notification-item', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');
                self.markNotificationRead(notificationId);
            });

            // Settings
            $(document).on('change', '.twitch-push-enabled', function() {
                self.togglePushNotifications($(this).is(':checked'));
            });

            $(document).on('change', '.twitch-stream-notifications', function() {
                self.updateNotificationSetting('stream_start', $(this).is(':checked'));
            });

            $(document).on('change', '.twitch-follower-notifications', function() {
                self.updateNotificationSetting('followers', $(this).is(':checked'));
            });

            $(document).on('change', '.twitch-theme-light, .twitch-theme-dark, .twitch-theme-auto', function() {
                var theme = $(this).val();
                self.setTheme(theme);
            });

            $(document).on('change', '.twitch-quality-auto, .twitch-quality-high, .twitch-quality-low', function() {
                var quality = $(this).val();
                self.setQuality(quality);
            });

            // Test notification
            $(document).on('click', '.twitch-test-notification', function(e) {
                e.preventDefault();
                self.sendTestNotification();
            });

            // Stream interactions
            $(document).on('click', '.twitch-watch-btn', function(e) {
                e.preventDefault();
                var streamId = $(this).closest('.twitch-mobile-stream-card').data('stream-id');
                self.watchStream(streamId);
            });

            $(document).on('click', '.twitch-follow-btn', function(e) {
                e.preventDefault();
                var channel = $(this).data('channel');
                self.toggleFollow(channel, $(this));
            });

            $(document).on('click', '.twitch-notify-btn', function(e) {
                e.preventDefault();
                var channel = $(this).data('channel');
                self.toggleChannelNotifications(channel, $(this));
            });

            // App interactions
            $(document).on('click', '.twitch-remind-btn', function(e) {
                e.preventDefault();
                var streamId = $(this).data('stream-id');
                self.setStreamReminder(streamId);
            });

            $(document).on('click', '.twitch-calendar-btn', function(e) {
                e.preventDefault();
                var streamId = $(this).data('stream-id');
                self.addToCalendar(streamId);
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Escape closes panels
                if (e.key === 'Escape') {
                    self.closeSidebar();
                    self.closeNotificationPanel();
                }
            });

            // Window events
            $(window).on('beforeinstallprompt', function(e) {
                e.preventDefault();
                self.deferredPrompt = e.originalEvent;
                self.showInstallButton();
            });

            $(window).on('appinstalled', function() {
                self.isInstalled = true;
                self.hideInstallPrompt();
                self.trackEvent('pwa_installed');
            });

            $(window).on('online offline', function(e) {
                self.isOnline = e.type === 'online';
                self.updateOnlineStatus();
            });

            // Touch events for swipe gestures
            this.initTouchGestures();
        },

        initPWA: function() {
            var self = this;

            // Check if already installed
            if (window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true) {
                this.isInstalled = true;
            }

            // Register service worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register(twitchMobileApp.serviceWorkerUrl || '/twitch-service-worker.js')
                    .then(function(registration) {
                        console.log('Service Worker registered:', registration.scope);

                        // Handle updates
                        registration.addEventListener('updatefound', function() {
                            var newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    self.showUpdateNotification();
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            }

            // Handle PWA install prompt
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                self.deferredPrompt = e;
                self.showInstallButton();
            });

            // Handle app installed
            window.addEventListener('appinstalled', function() {
                self.isInstalled = true;
                self.hideInstallPrompt();
                self.trackEvent('pwa_installed');
            });
        },

        initPushNotifications: function() {
            var self = this;

            // Check for notification support
            if (!('Notification' in window)) {
                console.log('This browser does not support notifications');
                return;
            }

            // Request permission if not already granted
            if (Notification.permission === 'default') {
                this.requestNotificationPermission();
            }

            // Subscribe to push notifications if enabled
            if (this.settings.pushEnabled && Notification.permission === 'granted') {
                this.subscribeToPush();
            }
        },

        initOfflineDetection: function() {
            var self = this;

            this.isOnline = navigator.onLine;

            window.addEventListener('online', function() {
                self.isOnline = true;
                self.updateOnlineStatus();
                self.syncOfflineData();
            });

            window.addEventListener('offline', function() {
                self.isOnline = false;
                self.updateOnlineStatus();
            });

            // Show offline indicator if offline
            if (!this.isOnline) {
                this.showOfflineIndicator();
            }
        },

        initTheme: function() {
            var savedTheme = localStorage.getItem('twitch_mobile_theme') || 'auto';
            this.setTheme(savedTheme);
        },

        initTouchGestures: function() {
            var self = this;
            var startX, startY, endX, endY;

            // Touch start
            $(document).on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
                startY = e.originalEvent.touches[0].clientY;
            });

            // Touch end
            $(document).on('touchend', function(e) {
                endX = e.originalEvent.changedTouches[0].clientX;
                endY = e.originalEvent.changedTouches[0].clientY;

                var deltaX = endX - startX;
                var deltaY = endY - startY;

                // Horizontal swipe (more than 50px and more horizontal than vertical)
                if (Math.abs(deltaX) > 50 && Math.abs(deltaX) > Math.abs(deltaY)) {
                    if (deltaX > 0) {
                        // Swipe right - open sidebar
                        self.openSidebar();
                    } else {
                        // Swipe left - close sidebar
                        self.closeSidebar();
                    }
                }
            });
        },

        loadSettings: function() {
            this.settings = {
                pushEnabled: localStorage.getItem('twitch_push_enabled') === 'true',
                streamNotifications: localStorage.getItem('twitch_stream_notifications') !== 'false',
                followerNotifications: localStorage.getItem('twitch_follower_notifications') === 'true',
                theme: localStorage.getItem('twitch_mobile_theme') || 'auto',
                quality: localStorage.getItem('twitch_stream_quality') || 'auto'
            };
        },

        updateUI: function() {
            // Update theme
            $('[name="theme"][value="' + this.theme + '"]').prop('checked', true);

            // Update settings checkboxes
            $('.twitch-push-enabled').prop('checked', this.settings.pushEnabled);
            $('.twitch-stream-notifications').prop('checked', this.settings.streamNotifications);
            $('.twitch-follower-notifications').prop('checked', this.settings.followerNotifications);
            $('[name="quality"][value="' + this.settings.quality + '"]').prop('checked', true);

            // Update notification count
            this.updateNotificationCount();
        },

        switchSection: function(section) {
            // Update navigation
            $('.twitch-nav-item, .twitch-tab-btn').removeClass('active');
            $('.twitch-nav-item[href="#' + section + '"], .twitch-tab-btn[data-section="' + section + '"]').addClass('active');

            // Hide all sections
            $('.twitch-mobile-section').removeClass('active');

            // Show selected section
            $('#' + section + '-section').addClass('active');

            this.currentSection = section;

            // Load section content
            this.loadSectionContent(section);
        },

        loadSectionContent: function(section) {
            var self = this;
            var $section = $('#' + section + '-section');

            // Show loading
            $section.html('<div class="twitch-mobile-loading"><div class="twitch-mobile-spinner"></div></div>');

            // Load content based on section
            switch (section) {
                case 'streams':
                    this.loadStreams();
                    break;
                case 'schedule':
                    $section.html('<div class="twitch-mobile-schedule">' +
                        do_shortcode('[twitch_stream_scheduler view="list" theme="dark"]') +
                        '</div>');
                    break;
                case 'chat':
                    $section.html('<div class="twitch-mobile-chat">' +
                        do_shortcode('[twitch_chat channel="yourchannel" theme="dark" height="400"]') +
                        '</div>');
                    break;
                case 'profile':
                    this.loadProfile();
                    break;
                case 'settings':
                    // Settings is already rendered
                    break;
            }
        },

        loadStreams: function() {
            var self = this;

            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'get_streams',
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.renderStreams(response.data.streams);
                    } else {
                        $('#streams-section').html('<div class="twitch-mobile-error">' +
                            '<span class="twitch-mobile-error-icon">⚠️</span>' +
                            '<h3>Failed to load streams</h3>' +
                            '<p>Please check your connection and try again.</p>' +
                            '<button class="twitch-error-retry" onclick="TwitchMobileApp.loadStreams()">Retry</button>' +
                            '</div>');
                    }
                },
                error: function() {
                    $('#streams-section').html('<div class="twitch-mobile-error">' +
                        '<span class="twitch-mobile-error-icon">📶</span>' +
                        '<h3>Connection Error</h3>' +
                        '<p>Unable to connect to the server.</p>' +
                        '<button class="twitch-error-retry" onclick="TwitchMobileApp.loadStreams()">Retry</button>' +
                        '</div>');
                }
            });
        },

        renderStreams: function(streams) {
            var html = '';

            if (!streams || streams.length === 0) {
                html = '<div class="twitch-no-streams">' +
                    '<span class="twitch-no-streams-icon">📺</span>' +
                    '<h3>No Live Streams</h3>' +
                    '<p>Check back later for live content.</p>' +
                    '</div>';
            } else {
                html = '<div class="twitch-mobile-streams">';
                streams.forEach(function(stream) {
                    html += '<div class="twitch-mobile-stream-card" data-stream-id="' + stream.id + '">' +
                        '<div class="twitch-stream-thumbnail">' +
                        '<img src="' + stream.thumbnail_url + '" alt="' + stream.title + '" loading="lazy">' +
                        '<div class="twitch-stream-overlay">' +
                        '<div class="twitch-stream-status">' +
                        '<span class="twitch-live-badge">LIVE</span>' +
                        '<span class="twitch-viewer-count">👁️ ' + stream.viewer_count + '</span>' +
                        '</div>' +
                        '<div class="twitch-stream-actions">' +
                        '<button class="twitch-watch-btn">Watch</button>' +
                        '<button class="twitch-follow-btn" data-channel="' + stream.channel + '">❤️</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="twitch-stream-info">' +
                        '<h4 class="twitch-stream-title">' + stream.title + '</h4>' +
                        '<p class="twitch-stream-channel">' + stream.channel + '</p>' +
                        '<div class="twitch-stream-meta">' +
                        '<span class="twitch-game-name">' + stream.game_name + '</span>' +
                        '<span class="twitch-stream-duration">' + stream.duration + '</span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                });
                html += '</div>';
            }

            $('#streams-section').html(html);
        },

        loadProfile: function() {
            var self = this;

            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'get_user_stats',
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    var html = '<div class="twitch-mobile-profile">';

                    if (response.success) {
                        var stats = response.data;
                        html += '<div class="twitch-profile-header">' +
                            '<div class="twitch-profile-avatar">' + (stats.avatar || '<span>👤</span>') + '</div>' +
                            '<div class="twitch-profile-info">' +
                            '<h3>' + (stats.display_name || 'User') + '</h3>' +
                            '<p>Level: <span class="twitch-user-level">' + (stats.level || 'Viewer') + '</span></p>' +
                            '</div>' +
                            '</div>' +
                            '<div class="twitch-profile-stats">' +
                            '<div class="twitch-stat-item">' +
                            '<span class="twitch-stat-number">' + (stats.streams_watched || 0) + '</span>' +
                            '<span class="twitch-stat-label">Streams Watched</span>' +
                            '</div>' +
                            '<div class="twitch-stat-item">' +
                            '<span class="twitch-stat-number">' + (stats.hours_watched || 0) + '</span>' +
                            '<span class="twitch-stat-label">Hours Watched</span>' +
                            '</div>' +
                            '<div class="twitch-stat-item">' +
                            '<span class="twitch-stat-number">' + (stats.following || 0) + '</span>' +
                            '<span class="twitch-stat-label">Following</span>' +
                            '</div>' +
                            '</div>';
                    } else {
                        html += '<div class="twitch-mobile-error">' +
                            '<span class="twitch-mobile-error-icon">⚠️</span>' +
                            '<h3>Profile Error</h3>' +
                            '<p>Unable to load profile information.</p>' +
                            '</div>';
                    }

                    html += '</div>';
                    $('#profile-section').html(html);
                },
                error: function() {
                    $('#profile-section').html('<div class="twitch-mobile-error">' +
                        '<span class="twitch-mobile-error-icon">📶</span>' +
                        '<h3>Connection Error</h3>' +
                        '<p>Unable to load profile.</p>' +
                        '<button class="twitch-error-retry" onclick="TwitchMobileApp.loadProfile()">Retry</button>' +
                        '</div>');
                }
            });
        },

        toggleSidebar: function() {
            $('.twitch-mobile-sidebar').toggleClass('open');
        },

        closeSidebar: function() {
            $('.twitch-mobile-sidebar').removeClass('open');
        },

        toggleNotificationPanel: function() {
            $('.twitch-notification-panel').toggleClass('open');
            if ($('.twitch-notification-panel').hasClass('open')) {
                this.loadNotifications();
            }
        },

        closeNotificationPanel: function() {
            $('.twitch-notification-panel').removeClass('open');
        },

        showInstallPrompt: function() {
            if (this.isInstalled || !this.deferredPrompt) return;

            // Show install prompt after user has been on page for 30 seconds
            setTimeout(function() {
                if (!TwitchMobileApp.isInstalled) {
                    $('.twitch-pwa-install-prompt').addClass('show');
                }
            }, 30000);
        },

        showInstallButton: function() {
            $('.twitch-pwa-install-btn').show();
        },

        hideInstallPrompt: function() {
            $('.twitch-pwa-install-prompt').removeClass('show');
        },

        dismissInstallPrompt: function() {
            this.hideInstallPrompt();
            localStorage.setItem('twitch_install_dismissed', Date.now().toString());
        },

        installPWA: function() {
            if (!this.deferredPrompt) return;

            this.deferredPrompt.prompt();

            this.deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    TwitchMobileApp.trackEvent('pwa_install_accepted');
                } else {
                    TwitchMobileApp.trackEvent('pwa_install_dismissed');
                }
                TwitchMobileApp.deferredPrompt = null;
            });
        },

        requestNotificationPermission: function() {
            var self = this;

            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    self.subscribeToPush();
                }
            });
        },

        subscribeToPush: function() {
            var self = this;

            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.log('Push notifications not supported');
                return;
            }

            navigator.serviceWorker.ready.then(function(registration) {
                registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: self.urlBase64ToUint8Array(twitchMobileApp.vapidKey)
                }).then(function(subscription) {
                    self.pushSubscription = subscription;
                    self.sendSubscriptionToServer(subscription);
                }).catch(function(error) {
                    console.error('Failed to subscribe to push:', error);
                });
            });
        },

        sendSubscriptionToServer: function(subscription) {
            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_pwa_subscribe',
                    subscription: JSON.stringify(subscription),
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Push subscription saved');
                    }
                }
            });
        },

        togglePushNotifications: function(enabled) {
            this.settings.pushEnabled = enabled;
            localStorage.setItem('twitch_push_enabled', enabled);

            if (enabled) {
                if (Notification.permission === 'default') {
                    this.requestNotificationPermission();
                } else if (Notification.permission === 'granted') {
                    this.subscribeToPush();
                }
            } else {
                this.unsubscribeFromPush();
            }

            this.updateNotificationSetting('push_enabled', enabled);
        },

        unsubscribeFromPush: function() {
            if (this.pushSubscription) {
                this.pushSubscription.unsubscribe().then(function() {
                    console.log('Unsubscribed from push notifications');
                });
            }
        },

        updateNotificationSetting: function(setting, value) {
            var settingsMap = {
                'push_enabled': 'pushEnabled',
                'stream_start': 'streamNotifications',
                'followers': 'followerNotifications'
            };

            if (settingsMap[setting]) {
                this.settings[settingsMap[setting]] = value;
                localStorage.setItem('twitch_' + setting.replace('_', '_'), value);
            }

            // Send to server
            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'update_settings',
                    setting: setting,
                    value: value,
                    nonce: twitchMobileApp.nonce
                }
            });
        },

        sendTestNotification: function() {
            if (!this.pushSubscription) {
                alert('Push notifications not enabled');
                return;
            }

            // Send test notification via AJAX
            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'send_test_notification',
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Test notification sent!');
                    } else {
                        alert('Failed to send test notification');
                    }
                }
            });
        },

        loadNotifications: function() {
            var self = this;

            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'get_notifications',
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.renderNotifications(response.data.notifications);
                    }
                }
            });
        },

        renderNotifications: function(notifications) {
            var html = '';

            if (!notifications || notifications.length === 0) {
                html = '<div class="twitch-no-notifications">No notifications</div>';
            } else {
                notifications.forEach(function(notification) {
                    html += '<div class="twitch-notification-item ' + (notification.unread ? 'unread' : '') + '" data-id="' + notification.id + '">' +
                        '<div class="twitch-notification-content">' +
                        '<div class="twitch-notification-icon">' + (notification.icon || '🔔') + '</div>' +
                        '<div class="twitch-notification-text">' +
                        '<div class="twitch-notification-title">' + notification.title + '</div>' +
                        '<div class="twitch-notification-message">' + notification.body + '</div>' +
                        '<div class="twitch-notification-time">' + self.formatTimeAgo(notification.created_at) + '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                });
            }

            $('.twitch-notification-list').html(html);
            this.updateNotificationCount(notifications.filter(n => n.unread).length);
        },

        updateNotificationCount: function(count) {
            if (count > 0) {
                $('.twitch-notification-count').text(count).addClass('show');
            } else {
                $('.twitch-notification-count').removeClass('show');
            }
        },

        markNotificationRead: function(notificationId) {
            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'mark_notification_read',
                    notification_id: notificationId,
                    nonce: twitchMobileApp.nonce
                },
                success: function() {
                    $('[data-id="' + notificationId + '"]').removeClass('unread');
                    TwitchMobileApp.updateNotificationCount(
                        $('.twitch-notification-item.unread').length
                    );
                }
            });
        },

        setTheme: function(theme) {
            this.theme = theme;
            $('html').attr('data-theme', theme);
            localStorage.setItem('twitch_mobile_theme', theme);
        },

        setQuality: function(quality) {
            localStorage.setItem('twitch_stream_quality', quality);
            // Apply quality setting to video players
            this.updateStreamQuality(quality);
        },

        updateStreamQuality: function(quality) {
            // Update quality for any active streams
            $('.twitch-stream-player').attr('data-quality', quality);
        },

        updateOnlineStatus: function() {
            if (this.isOnline) {
                $('.twitch-offline-indicator').removeClass('show');
                $('body').removeClass('twitch-offline');
            } else {
                this.showOfflineIndicator();
                $('body').addClass('twitch-offline');
            }
        },

        showOfflineIndicator: function() {
            if (!$('.twitch-offline-indicator').length) {
                $('body').append('<div class="twitch-offline-indicator">' +
                    '<span class="twitch-offline-icon">📶</span> ' + twitchMobileApp.strings.offlineMessage +
                    '</div>');
            }
            $('.twitch-offline-indicator').addClass('show');
        },

        syncOfflineData: function() {
            // Sync any offline data when coming back online
            if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
                navigator.serviceWorker.ready.then(function(registration) {
                    registration.sync.register('sync-offline-data');
                });
            }
        },

        watchStream: function(streamId) {
            // Open stream in fullscreen or new window
            window.open('/stream/' + streamId, '_blank');
        },

        toggleFollow: function(channel, $button) {
            // Toggle follow status
            var isFollowing = $button.hasClass('following');

            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_toggle_follow',
                    channel: channel,
                    follow: !isFollowing,
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.toggleClass('following');
                        $button.html(isFollowing ? '❤️' : '🤍');
                    }
                }
            });
        },

        toggleChannelNotifications: function(channel, $button) {
            // Toggle channel notifications
            var hasNotifications = $button.hasClass('active');

            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_toggle_channel_notifications',
                    channel: channel,
                    enabled: !hasNotifications,
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.toggleClass('active');
                        $button.find('.twitch-notify-icon').html(hasNotifications ? '🔔' : '🔕');
                    }
                }
            });
        },

        setStreamReminder: function(streamId) {
            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'set_reminder',
                    stream_id: streamId,
                    nonce: twitchMobileApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Reminder set!');
                    }
                }
            });
        },

        addToCalendar: function(streamId) {
            // Add to device calendar
            if ('webkit.messageHandlers' in window) {
                // iOS
                window.webkit.messageHandlers.addToCalendar.postMessage({ streamId: streamId });
            } else if (navigator.share) {
                // Web Share API
                navigator.share({
                    title: 'Twitch Stream',
                    text: 'Check out this stream!',
                    url: '/stream/' + streamId
                });
            } else {
                // Fallback - copy link
                this.copyToClipboard('/stream/' + streamId);
                alert('Stream link copied to clipboard!');
            }
        },

        showUpdateNotification: function() {
            var html = '<div class="twitch-update-notification">' +
                '<div class="twitch-update-content">' +
                '<span class="twitch-update-icon">⬆️</span>' +
                '<div class="twitch-update-text">' +
                '<h4>Update Available</h4>' +
                '<p>A new version is available. Refresh to update.</p>' +
                '</div>' +
                '<button class="twitch-update-refresh">Refresh</button>' +
                '<button class="twitch-update-dismiss">&times;</button>' +
                '</div>' +
                '</div>';

            $('body').append(html);

            $('.twitch-update-refresh').on('click', function() {
                window.location.reload();
            });

            $('.twitch-update-dismiss').on('click', function() {
                $('.twitch-update-notification').remove();
            });
        },

        trackEvent: function(eventName, data) {
            // Track mobile app events
            if (typeof gtag !== 'undefined') {
                gtag('event', 'mobile_' + eventName, data || {});
            }

            // Send to server for analytics
            $.ajax({
                url: twitchMobileApp.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_mobile_app',
                    mobile_action: 'track_event',
                    event: eventName,
                    data: JSON.stringify(data || {}),
                    nonce: twitchMobileApp.nonce
                }
            });
        },

        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
            } else {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
        },

        formatTimeAgo: function(dateString) {
            var date = new Date(dateString);
            var now = new Date();
            var diff = now - date;
            var minutes = Math.floor(diff / 60000);
            var hours = Math.floor(diff / 3600000);
            var days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return minutes + 'm ago';
            if (hours < 24) return hours + 'h ago';
            return days + 'd ago';
        },

        urlBase64ToUint8Array: function(base64String) {
            var padding = '='.repeat((4 - base64String.length % 4) % 4);
            var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            var rawData = window.atob(base64);
            var outputArray = new Uint8Array(rawData.length);
            for (var i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if (typeof twitchMobileApp !== 'undefined') {
            TwitchMobileApp.init();
        }
    });

    // Expose globally
    window.TwitchMobileApp = TwitchMobileApp;

})(jQuery);
