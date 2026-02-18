/**
 * Membership Plugin Integration JavaScript
 */

(function($) {
    'use strict';

    // Global membership integration instance
    window.twitchMembership = {
        enabled: true,
        userLevel: 'free',
        activePlugins: [],
        membershipLevels: {},
        restrictedContent: []
    };

    // Initialize membership integration
    $(document).ready(function() {
        initMembershipIntegration();
        initEventHandlers();
        initContentRestrictions();
        initMembershipBadges();
        initUpgradePrompts();
    });

    /**
     * Initialize membership integration
     */
    function initMembershipIntegration() {
        // Set global variables
        window.twitchMembership.enabled = twitchMembership.enabled !== false;
        window.twitchMembership.userLevel = twitchMembership.userLevel || 'free';
        window.twitchMembership.activePlugins = twitchMembership.activePlugins || [];
        window.twitchMembership.membershipLevels = twitchMembership.membershipLevels || {};
        
        // Check if membership integration is enabled
        if (!window.twitchMembership.enabled) {
            console.log('Twitch membership integration is disabled');
            return;
        }
        
        // Check for active membership plugins
        if (window.twitchMembership.activePlugins.length === 0) {
            console.log('No active membership plugins detected');
            return;
        }
        
        // Initialize membership features
        initMembershipFeatures();
        
        // Add membership data to page
        addMembershipData();
        
        // Initialize content monitoring
        initContentMonitoring();
    }

    /**
     * Initialize event handlers
     */
    function initEventHandlers() {
        // Upgrade button clicks
        $(document).on('click', '.twitch-upgrade-btn', function(e) {
            e.preventDefault();
            var upgradeUrl = $(this).attr('href');
            handleUpgradeClick(upgradeUrl);
        });

        // Membership level clicks
        $(document).on('click', '.twitch-membership-level', function() {
            var $level = $(this);
            var levelKey = $level.data('level');
            
            if (!$level.hasClass('has-access')) {
                showUpgradeModal(levelKey);
            }
        });

        // Content access checks
        $(document).on('click', '[data-twitch-access]', function(e) {
            var accessLevel = $(this).data('twitch-access');
            
            if (!hasAccessLevel(accessLevel)) {
                e.preventDefault();
                showAccessDeniedModal(accessLevel);
            }
        });

        // Membership badge clicks
        $(document).on('click', '.twitch-membership-badge', function(e) {
            e.preventDefault();
            showMembershipInfo();
        });

        // Dynamic content loading
        $(document).on('twitch:contentLoaded', function() {
            checkContentRestrictions();
        });

        // AJAX form submissions
        $(document).on('submit', 'form[data-twitch-membership]', function(e) {
            var requiredLevel = $(this).data('twitch-membership');
            
            if (!hasAccessLevel(requiredLevel)) {
                e.preventDefault();
                showAccessDeniedModal(requiredLevel);
            }
        });
    }

    /**
     * Initialize content restrictions
     */
    function initContentRestrictions() {
        // Check all restricted content
        checkContentRestrictions();
        
        // Monitor dynamic content
        observeContentChanges();
        
        // Handle URL-based restrictions
        handleURLRestrictions();
    }

    /**
     * Initialize membership badges
     */
    function initMembershipBadges() {
        // Add badges to user elements
        addUserBadges();
        
        // Add badges to chat elements
        addChatBadges();
        
        // Add badges to profile elements
        addProfileBadges();
        
        // Update badges on user level changes
        updateBadgesOnLevelChange();
    }

    /**
     * Initialize upgrade prompts
     */
    function initUpgradePrompts() {
        // Show upgrade prompts for restricted content
        showUpgradePromptsForRestrictedContent();
        
        // Handle upgrade success
        handleUpgradeSuccess();
        
        // Show upgrade suggestions
        showUpgradeSuggestions();
    }

    /**
     * Initialize membership features
     */
    function initMembershipFeatures() {
        // Add membership-specific features
        addMembershipFeatures();
        
        // Initialize analytics tracking
        initMembershipAnalytics();
        
        // Add membership shortcuts
        addMembershipShortcuts();
    }

    /**
     * Add membership data to page
     */
    function addMembershipData() {
        // Add meta tags
        $('head').append('<meta name="twitch-membership-enabled" content="' + window.twitchMembership.enabled + '">');
        $('head').append('<meta name="twitch-membership-level" content="' + window.twitchMembership.userLevel + '">');
        
        // Add body classes
        $('body').addClass('twitch-membership-enabled');
        $('body').addClass('twitch-level-' + window.twitchMembership.userLevel);
        
        // Add data attributes to main elements
        $('.twitch-stream-container').attr('data-user-level', window.twitchMembership.userLevel);
        $('.twitch-chat-container').attr('data-user-level', window.twitchMembership.userLevel);
    }

    /**
     * Initialize content monitoring
     */
    function initContentMonitoring() {
        // Monitor stream content
        monitorStreamContent();
        
        // Monitor VOD content
        monitorVODContent();
        
        // Monitor chat content
        monitorChatContent();
        
        // Monitor analytics content
        monitorAnalyticsContent();
    }

    /**
     * Check content restrictions
     */
    function checkContentRestrictions() {
        // Check restricted content elements
        $('[data-twitch-restrict]').each(function() {
            var $element = $(this);
            var requiredLevel = $element.data('twitch-restrict');
            
            if (!hasAccessLevel(requiredLevel)) {
                restrictContent($element, requiredLevel);
            }
        });
        
        // Check membership required content
        $('.twitch-membership-required').each(function() {
            var $element = $(this);
            var requiredLevel = $element.data('level') || 'basic';
            
            if (!hasAccessLevel(requiredLevel)) {
                showMembershipRequired($element, requiredLevel);
            }
        });
        
        // Check access-controlled links
        $('[data-twitch-access]').each(function() {
            var $element = $(this);
            var accessLevel = $element.data('twitch-access');
            
            if (!hasAccessLevel(accessLevel)) {
                $element.addClass('twitch-access-restricted');
                $element.attr('title', 'Requires ' + accessLevel + ' membership');
            }
        });
    }

    /**
     * Observe content changes
     */
    function observeContentChanges() {
        // Create mutation observer
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Check new content for restrictions
                    $(mutation.addedNodes).find('[data-twitch-restrict]').each(function() {
                        var $element = $(this);
                        var requiredLevel = $element.data('twitch-restrict');
                        
                        if (!hasAccessLevel(requiredLevel)) {
                            restrictContent($element, requiredLevel);
                        }
                    });
                }
            });
        });
        
        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Handle URL restrictions
     */
    function handleURLRestrictions() {
        var urlParams = new URLSearchParams(window.location.search);
        var requiredLevel = urlParams.get('membership_level');
        
        if (requiredLevel && !hasAccessLevel(requiredLevel)) {
            showAccessDeniedModal(requiredLevel);
        }
    }

    /**
     * Add user badges
     */
    function addUserBadges() {
        $('.twitch-user-info').each(function() {
            var $userInfo = $(this);
            var userId = $userInfo.data('user-id');
            
            if (userId) {
                addUserBadge($userInfo, userId);
            }
        });
    }

    /**
     * Add chat badges
     */
    function addChatBadges() {
        $('.twitch-chat-message').each(function() {
            var $message = $(this);
            var userId = $message.data('user-id');
            
            if (userId) {
                addChatBadge($message, userId);
            }
        });
    }

    /**
     * Add profile badges
     */
    function addProfileBadges() {
        $('.twitch-user-profile').each(function() {
            var $profile = $(this);
            var userId = $profile.data('user-id');
            
            if (userId) {
                addProfileBadge($profile, userId);
            }
        });
    }

    /**
     * Update badges on level change
     */
    function updateBadgesOnLevelChange() {
        // Listen for membership level changes
        $(document).on('twitch:membershipLevelChanged', function(e, data) {
            updateAllBadges(data.newLevel);
        });
    }

    /**
     * Show upgrade prompts for restricted content
     */
    function showUpgradePromptsForRestrictedContent() {
        $('.twitch-restricted-content').each(function() {
            var $content = $(this);
            var requiredLevel = $content.data('required-level');
            
            if (!hasAccessLevel(requiredLevel)) {
                showUpgradePrompt($content, requiredLevel);
            }
        });
    }

    /**
     * Handle upgrade success
     */
    function handleUpgradeSuccess() {
        // Check URL for upgrade success parameter
        var urlParams = new URLSearchParams(window.location.search);
        var upgradeSuccess = urlParams.get('upgrade_success');
        
        if (upgradeSuccess === 'true') {
            showUpgradeSuccessMessage();
        }
    }

    /**
     * Show upgrade suggestions
     */
    function showUpgradeSuggestions() {
        // Show suggestions based on user behavior
        if (window.twitchMembership.userLevel === 'free') {
            showBasicUpgradeSuggestion();
        } else if (window.twitchMembership.userLevel === 'basic') {
            showPremiumUpgradeSuggestion();
        } else if (window.twitchMembership.userLevel === 'premium') {
            showVIPUpgradeSuggestion();
        }
    }

    /**
     * Add membership features
     */
    function addMembershipFeatures() {
        // Add features based on membership level
        var features = window.twitchMembership.membershipLevels[window.twitchMembership.userLevel]?.features || [];
        
        features.forEach(function(feature) {
            addMembershipFeature(feature);
        });
    }

    /**
     * Initialize membership analytics
     */
    function initMembershipAnalytics() {
        // Track membership-related events
        trackMembershipEvents();
        
        // Track upgrade clicks
        trackUpgradeClicks();
        
        // Track content access
        trackContentAccess();
    }

    /**
     * Add membership shortcuts
     */
    function addMembershipShortcuts() {
        // Add keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + U: Show upgrade modal
            if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
                e.preventDefault();
                showUpgradeModal();
            }
            
            // Ctrl/Cmd + M: Show membership info
            if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
                e.preventDefault();
                showMembershipInfo();
            }
        });
    }

    /**
     * Monitor stream content
     */
    function monitorStreamContent() {
        $('.twitch-stream-container').each(function() {
            var $stream = $(this);
            var accessLevel = $stream.data('access-level') || 'basic';
            
            if (!hasAccessLevel(accessLevel)) {
                restrictStreamContent($stream, accessLevel);
            }
        });
    }

    /**
     * Monitor VOD content
     */
    function monitorVODContent() {
        $('.twitch-vod-container').each(function() {
            var $vod = $(this);
            var accessLevel = $vod.data('access-level') || 'basic';
            
            if (!hasAccessLevel(accessLevel)) {
                restrictVODContent($vod, accessLevel);
            }
        });
    }

    /**
     * Monitor chat content
     */
    function monitorChatContent() {
        $('.twitch-chat-container').each(function() {
            var $chat = $(this);
            var accessLevel = $chat.data('access-level') || 'free';
            
            if (!hasAccessLevel(accessLevel)) {
                restrictChatContent($chat, accessLevel);
            }
        });
    }

    /**
     * Monitor analytics content
     */
    function monitorAnalyticsContent() {
        $('.twitch-analytics-container').each(function() {
            var $analytics = $(this);
            var accessLevel = $analytics.data('access-level') || 'vip';
            
            if (!hasAccessLevel(accessLevel)) {
                restrictAnalyticsContent($analytics, accessLevel);
            }
        });
    }

    /**
     * Handle upgrade click
     */
    function handleUpgradeClick(upgradeUrl) {
        // Track upgrade click
        trackEvent('upgrade_click', {
            url: upgradeUrl,
            currentLevel: window.twitchMembership.userLevel
        });
        
        // Show confirmation modal
        showUpgradeConfirmation(upgradeUrl);
    }

    /**
     * Show upgrade modal
     */
    function showUpgradeModal(targetLevel) {
        // Get upgrade info
        getUpgradeInfo(targetLevel).then(function(upgradeInfo) {
            renderUpgradeModal(upgradeInfo);
        });
    }

    /**
     * Show access denied modal
     */
    function showAccessDeniedModal(requiredLevel) {
        var modalHtml = '<div class="twitch-access-denied-modal">' +
            '<div class="twitch-modal-content">' +
            '<div class="twitch-modal-header">' +
            '<h3>Access Denied</h3>' +
            '<button class="twitch-modal-close">&times;</button>' +
            '</div>' +
            '<div class="twitch-modal-body">' +
            '<p>This content requires a ' + requiredLevel + ' membership to access.</p>' +
            '<button class="twitch-upgrade-btn">Upgrade Now</button>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modalHtml);
        $('.twitch-access-denied-modal').addClass('twitch-modal-open');
    }

    /**
     * Show membership info
     */
    function showMembershipInfo() {
        var levelInfo = window.twitchMembership.membershipLevels[window.twitchMembership.userLevel];
        
        if (!levelInfo) {
            return;
        }
        
        var modalHtml = '<div class="twitch-membership-info-modal">' +
            '<div class="twitch-modal-content">' +
            '<div class="twitch-modal-header">' +
            '<h3>Your Membership</h3>' +
            '<button class="twitch-modal-close">&times;</button>' +
            '</div>' +
            '<div class="twitch-modal-body">' +
            '<div class="twitch-current-level">' +
            '<h4>Current Level: ' + levelInfo.name + '</h4>' +
            '<ul>' +
            levelInfo.features.map(function(feature) {
                return '<li>✓ ' + feature + '</li>';
            }).join('') +
            '</ul>' +
            '</div>' +
            '<button class="twitch-upgrade-btn">Upgrade Membership</button>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modalHtml);
        $('.twitch-membership-info-modal').addClass('twitch-modal-open');
    }

    /**
     * Restrict content
     */
    function restrictContent($element, requiredLevel) {
        $element.addClass('twitch-content-restricted');
        $element.attr('data-required-level', requiredLevel);
        
        // Add restriction overlay
        var overlayHtml = '<div class="twitch-restriction-overlay">' +
            '<div class="twitch-restriction-content">' +
            '<span class="twitch-restriction-icon">🔒</span>' +
            '<span class="twitch-restriction-text">Requires ' + requiredLevel + ' membership</span>' +
            '<button class="twitch-upgrade-btn">Upgrade Now</button>' +
            '</div>' +
            '</div>';
        
        $element.append(overlayHtml);
    }

    /**
     * Show membership required
     */
    function showMembershipRequired($element, requiredLevel) {
        // Element already has the required HTML structure
        $element.show();
    }

    /**
     * Add user badge
     */
    function addUserBadge($userInfo, userId) {
        getUserMembershipLevel(userId).then(function(level) {
            if (level && level !== 'free') {
                var badgeHtml = createBadgeHtml(level);
                $userInfo.append(badgeHtml);
            }
        });
    }

    /**
     * Add chat badge
     */
    function addChatBadge($message, userId) {
        getUserMembershipLevel(userId).then(function(level) {
            if (level && level !== 'free') {
                var badgeHtml = createBadgeHtml(level, 'compact');
                $message.find('.twitch-message-user').append(badgeHtml);
            }
        });
    }

    /**
     * Add profile badge
     */
    function addProfileBadge($profile, userId) {
        getUserMembershipLevel(userId).then(function(level) {
            if (level && level !== 'free') {
                var badgeHtml = createBadgeHtml(level, 'large');
                $profile.find('.twitch-profile-header').append(badgeHtml);
            }
        });
    }

    /**
     * Create badge HTML
     */
    function createBadgeHtml(level, style = 'default') {
        var levelInfo = window.twitchMembership.membershipLevels[level];
        
        if (!levelInfo) {
            return '';
        }
        
        return '<span class="twitch-membership-badge twitch-style-' + style + ' twitch-level-' + level + '">' +
            '<span class="twitch-badge-icon">👑</span>' +
            '<span class="twitch-badge-name">' + levelInfo.name + '</span>' +
            '</span>';
    }

    /**
     * Show upgrade prompt
     */
    function showUpgradePrompt($content, requiredLevel) {
        var promptHtml = '<div class="twitch-upgrade-prompt">' +
            '<p>Upgrade to ' + requiredLevel + ' to access this content</p>' +
            '<button class="twitch-upgrade-btn">Upgrade Now</button>' +
            '</div>';
        
        $content.append(promptHtml);
    }

    /**
     * Show upgrade success message
     */
    function showUpgradeSuccessMessage() {
        var messageHtml = '<div class="twitch-upgrade-success">' +
            '<span class="twitch-success-icon">✅</span>' +
            '<span class="twitch-success-text">Membership upgraded successfully!</span>' +
            '</div>';
        
        $('body').prepend(messageHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.twitch-upgrade-success').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Show basic upgrade suggestion
     */
    function showBasicUpgradeSuggestion() {
        // Show suggestion after user has been on page for 30 seconds
        setTimeout(function() {
            if (Math.random() < 0.3) { // 30% chance
                showUpgradeSuggestion('basic');
            }
        }, 30000);
    }

    /**
     * Show premium upgrade suggestion
     */
    function showPremiumUpgradeSuggestion() {
        // Show suggestion when user tries to access premium content
        $(document).on('click', '[data-twitch-access="premium"]', function() {
            if (Math.random() < 0.5) { // 50% chance
                showUpgradeSuggestion('premium');
            }
        });
    }

    /**
     * Show VIP upgrade suggestion
     */
    function showVIPUpgradeSuggestion() {
        // Show suggestion when user uses analytics features
        $(document).on('click', '.twitch-analytics-feature', function() {
            if (Math.random() < 0.7) { // 70% chance
                showUpgradeSuggestion('vip');
            }
        });
    }

    /**
     * Show upgrade suggestion
     */
    function showUpgradeSuggestion(targetLevel) {
        var suggestionHtml = '<div class="twitch-upgrade-suggestion">' +
            '<div class="twitch-suggestion-content">' +
            '<h4>Upgrade to ' + targetLevel + '</h4>' +
            '<p>Get access to exclusive features and content!</p>' +
            '<button class="twitch-upgrade-btn">Learn More</button>' +
            '<button class="twitch-suggestion-dismiss">Maybe Later</button>' +
            '</div>' +
            '</div>';
        
        $('body').append(suggestionHtml);
        
        // Handle dismiss
        $(document).on('click', '.twitch-suggestion-dismiss', function() {
            $('.twitch-upgrade-suggestion').fadeOut(function() {
                $(this).remove();
            });
        });
    }

    /**
     * Add membership feature
     */
    function addMembershipFeature(feature) {
        switch (feature) {
            case 'watch_stream':
                enableStreamAccess();
                break;
            case 'read_chat':
                enableChatReadAccess();
                break;
            case 'chat_participate':
                enableChatParticipateAccess();
                break;
            case 'basic_vods':
                enableBasicVODAccess();
                break;
            case 'full_vods':
                enableFullVODAccess();
                break;
            case 'clips':
                enableClipsAccess();
                break;
            case 'analytics':
                enableAnalyticsAccess();
                break;
            case 'special_badges':
                enableSpecialBadges();
                break;
        }
    }

    /**
     * Track membership events
     */
    function trackMembershipEvents() {
        // Track level changes
        $(document).on('twitch:membershipLevelChanged', function(e, data) {
            trackEvent('membership_level_changed', data);
        });
        
        // Track feature usage
        $(document).on('twitch:featureUsed', function(e, data) {
            trackEvent('membership_feature_used', data);
        });
    }

    /**
     * Track upgrade clicks
     */
    function trackUpgradeClicks() {
        $(document).on('click', '.twitch-upgrade-btn', function() {
            trackEvent('upgrade_button_clicked', {
                currentLevel: window.twitchMembership.userLevel,
                buttonLocation: $(this).data('location') || 'unknown'
            });
        });
    }

    /**
     * Track content access
     */
    function trackContentAccess() {
        $(document).on('click', '[data-twitch-access]', function() {
            var accessLevel = $(this).data('twitch-access');
            
            if (hasAccessLevel(accessLevel)) {
                trackEvent('content_accessed', {
                    accessLevel: accessLevel,
                    contentType: $(this).data('content-type') || 'unknown'
                });
            }
        });
    }

    /**
     * Restrict stream content
     */
    function restrictStreamContent($stream, accessLevel) {
        $stream.addClass('twitch-stream-restricted');
        $stream.find('.twitch-video-overlay').addClass('twitch-restricted-overlay');
    }

    /**
     * Restrict VOD content
     */
    function restrictVODContent($vod, accessLevel) {
        $vod.addClass('twitch-vod-restricted');
        $vod.find('.twitch-vod-player').addClass('twitch-restricted-player');
    }

    /**
     * Restrict chat content
     */
    function restrictChatContent($chat, accessLevel) {
        $chat.addClass('twitch-chat-restricted');
        $chat.find('.twitch-chat-input').prop('disabled', true);
    }

    /**
     * Restrict analytics content
     */
    function restrictAnalyticsContent($analytics, accessLevel) {
        $analytics.addClass('twitch-analytics-restricted');
        $analytics.find('.twitch-analytics-data').addClass('twitch-restricted-data');
    }

    /**
     * Show upgrade confirmation
     */
    function showUpgradeConfirmation(upgradeUrl) {
        var confirmationHtml = '<div class="twitch-upgrade-confirmation">' +
            '<div class="twitch-confirmation-content">' +
            '<h3>Upgrade Your Membership</h3>' +
            '<p>Are you ready to unlock premium features?</p>' +
            '<div class="twitch-confirmation-actions">' +
            '<a href="' + upgradeUrl + '" class="twitch-upgrade-btn">Yes, Upgrade</a>' +
            '<button class="twitch-confirmation-cancel">Cancel</button>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(confirmationHtml);
        
        // Handle cancel
        $(document).on('click', '.twitch-confirmation-cancel', function() {
            $('.twitch-upgrade-confirmation').fadeOut(function() {
                $(this).remove();
            });
        });
    }

    /**
     * Render upgrade modal
     */
    function renderUpgradeModal(upgradeInfo) {
        var modalHtml = '<div class="twitch-upgrade-modal">' +
            '<div class="twitch-modal-content">' +
            '<div class="twitch-modal-header">' +
            '<h3>Upgrade to ' + upgradeInfo.targetInfo.name + '</h3>' +
            '<button class="twitch-modal-close">&times;</button>' +
            '</div>' +
            '<div class="twitch-modal-body">' +
            '<div class="twitch-upgrade-features">' +
            '<h4>Features you\'ll get:</h4>' +
            '<ul>' +
            upgradeInfo.targetInfo.features.map(function(feature) {
                return '<li>✓ ' + feature + '</li>';
            }).join('') +
            '</ul>' +
            '</div>' +
            '<div class="twitch-upgrade-actions">' +
            '<a href="' + upgradeInfo.upgradeUrl + '" class="twitch-upgrade-btn">Upgrade Now</a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modalHtml);
        $('.twitch-upgrade-modal').addClass('twitch-modal-open');
    }

    /**
     * Update all badges
     */
    function updateAllBadges(newLevel) {
        $('.twitch-membership-badge').remove();
        
        // Re-add badges with new level
        addUserBadges();
        addChatBadges();
        addProfileBadges();
    }

    /**
     * Enable stream access
     */
    function enableStreamAccess() {
        $('.twitch-stream-container').removeClass('twitch-stream-restricted');
    }

    /**
     * Enable chat read access
     */
    function enableChatReadAccess() {
        $('.twitch-chat-container').removeClass('twitch-chat-restricted');
    }

    /**
     * Enable chat participate access
     */
    function enableChatParticipateAccess() {
        $('.twitch-chat-input').prop('disabled', false);
    }

    /**
     * Enable basic VOD access
     */
    function enableBasicVODAccess() {
        $('.twitch-vod-container[data-access-level="basic"]').removeClass('twitch-vod-restricted');
    }

    /**
     * Enable full VOD access
     */
    function enableFullVODAccess() {
        $('.twitch-vod-container').removeClass('twitch-vod-restricted');
    }

    /**
     * Enable clips access
     */
    function enableClipsAccess() {
        $('.twitch-clips-container').removeClass('twitch-clips-restricted');
    }

    /**
     * Enable analytics access
     */
    function enableAnalyticsAccess() {
        $('.twitch-analytics-container').removeClass('twitch-analytics-restricted');
    }

    /**
     * Enable special badges
     */
    function enableSpecialBadges() {
        $('.twitch-membership-badge').addClass('twitch-special-badges');
    }

    /**
     * Helper functions
     */
    function hasAccessLevel(requiredLevel) {
        var levelHierarchy = {
            'free': 0,
            'basic': 1,
            'premium': 2,
            'vip': 3
        };
        
        var userLevelRank = levelHierarchy[window.twitchMembership.userLevel] || 0;
        var requiredLevelRank = levelHierarchy[requiredLevel] || 0;
        
        return userLevelRank >= requiredLevelRank;
    }

    function getUserMembershipLevel(userId) {
        return new Promise(function(resolve) {
            if (!twitchMembership.ajaxUrl || !twitchMembership.nonce) {
                resolve('free');
                return;
            }
            
            $.ajax({
                url: twitchMembership.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_membership',
                    membership_action: 'get_level',
                    user_id: userId,
                    nonce: twitchMembership.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        resolve(response.data.level);
                    } else {
                        resolve('free');
                    }
                },
                error: function() {
                    resolve('free');
                }
            });
        });
    }

    function getUpgradeInfo(targetLevel) {
        return new Promise(function(resolve) {
            if (!twitchMembership.ajaxUrl || !twitchMembership.nonce) {
                resolve(null);
                return;
            }
            
            $.ajax({
                url: twitchMembership.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_membership',
                    membership_action: 'upgrade_info',
                    target_level: targetLevel,
                    nonce: twitchMembership.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        resolve(response.data);
                    } else {
                        resolve(null);
                    }
                },
                error: function() {
                    resolve(null);
                }
            });
        });
    }

    function trackEvent(eventName, data) {
        // Track event using analytics or custom tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, data);
        } else if (typeof fbq !== 'undefined') {
            fbq('trackCustom', eventName, data);
        }
        
        // Custom event dispatch
        $(document).trigger('twitch:' + eventName, data);
    }

    /**
     * Modal handlers
     */
    $(document).on('click', '.twitch-modal-close', function() {
        $(this).closest('.twitch-modal').removeClass('twitch-modal-open');
        setTimeout(function() {
            $('.twitch-modal').remove();
        }, 300);
    });

    $(document).on('click', '.twitch-modal', function(e) {
        if (e.target === this) {
            $(this).removeClass('twitch-modal-open');
            setTimeout(function() {
                $('.twitch-modal').remove();
            }, 300);
        }
    });

    /**
     * Expose functions globally
     */
    window.TwitchMembership = {
        hasAccessLevel: hasAccessLevel,
        getUserLevel: function() { return window.twitchMembership.userLevel; },
        showUpgradeModal: showUpgradeModal,
        showMembershipInfo: showMembershipInfo,
        checkContentRestrictions: checkContentRestrictions
    };

})(jQuery);
