/**
 * Donation Integration JavaScript
 */

(function($) {
    'use strict';

    // Initialize donation functionality
    $(document).ready(function() {
        initDonationHandlers();
        initDonationGoal();
        initDonationStats();
    });

    /**
     * Initialize donation handlers
     */
    function initDonationHandlers() {
        // Handle donation button clicks
        $('.twitch-bmc-link, .twitch-paypal-button').on('click', function(e) {
            var $this = $(this);
            
            // Add loading state
            $this.addClass('loading');
            
            // Track donation click
            trackDonationClick($this);
            
            // Remove loading state after a delay
            setTimeout(function() {
                $this.removeClass('loading');
            }, 1000);
        });

        // Handle donation form submissions
        $('.twitch-paypal-form').on('submit', function(e) {
            var $form = $(this);
            
            // Add loading state
            $form.find('.twitch-paypal-button').addClass('loading');
            
            // Track PayPal donation attempt
            trackDonationClick($form.find('.twitch-paypal-button'), 'paypal');
        });
    }

    /**
     * Initialize donation goal
     */
    function initDonationGoal() {
        var $goalElements = $('.twitch-donation-goal-fill');
        
        if ($goalElements.length === 0) {
            return;
        }

        // Animate goal progress on page load
        $goalElements.each(function() {
            var $this = $(this);
            var targetWidth = $this.data('target-width') || $this.css('width');
            
            // Set initial width to 0 for animation
            $this.css('width', '0%');
            
            // Animate to target width
            setTimeout(function() {
                $this.css('width', targetWidth);
            }, 100);
        });

        // Update goal progress periodically
        setInterval(updateDonationGoal, 30000); // Update every 30 seconds
    }

    /**
     * Initialize donation stats
     */
    function initDonationStats() {
        var $statsElements = $('.twitch-donation-stats');
        
        if ($statsElements.length === 0) {
            return;
        }

        // Load initial stats
        loadDonationStats();

        // Update stats periodically
        setInterval(loadDonationStats, 60000); // Update every minute
    }

    /**
     * Update donation goal progress
     */
    function updateDonationGoal() {
        $.ajax({
            url: twitchDonations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_donation_settings',
                donation_action: 'get_stats',
                nonce: twitchDonations.nonce
            },
            success: function(response) {
                if (response.success && response.data.stats) {
                    updateGoalDisplay(response.data.stats);
                }
            },
            error: function() {
                console.log('Failed to update donation goal');
            }
        });
    }

    /**
     * Load donation stats
     */
    function loadDonationStats() {
        $.ajax({
            url: twitchDonations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_donation_settings',
                donation_action: 'get_stats',
                nonce: twitchDonations.nonce
            },
            success: function(response) {
                if (response.success && response.data.stats) {
                    updateStatsDisplay(response.data.stats);
                }
            },
            error: function() {
                console.log('Failed to load donation stats');
            }
        });
    }

    /**
     * Update goal display
     */
    function updateGoalDisplay(stats) {
        var $goalElements = $('.twitch-donation-goal-fill');
        var $currentAmountElements = $('.twitch-donation-goal-current .twitch-donation-amount');
        var $percentageElements = $('.twitch-donation-goal-percentage');

        if ($goalElements.length === 0) {
            return;
        }

        // Calculate percentage (this would come from server in real implementation)
        var goalAmount = parseFloat($('.twitch-donation-goal-target .twitch-donation-amount').first().text().replace(/[^0-9.]/g, ''));
        var currentAmount = stats.total_amount || 0;
        var percentage = goalAmount > 0 ? (currentAmount / goalAmount) * 100 : 0;

        // Update progress bar
        $goalElements.css('width', Math.min(percentage, 100) + '%');

        // Update current amount
        $currentAmountElements.text(formatCurrency(currentAmount));

        // Update percentage
        $percentageElements.text(Math.round(percentage, 1) + '%');
    }

    /**
     * Update stats display
     */
    function updateStatsDisplay(stats) {
        var $totalDonationsElements = $('.twitch-donation-stat').eq(0).find('.twitch-donation-stat-number');
        var $totalAmountElements = $('.twitch-donation-stat').eq(1).find('.twitch-donation-stat-number');
        var $averageAmountElements = $('.twitch-donation-stat').eq(2).find('.twitch-donation-stat-number');

        if ($totalDonationsElements.length > 0) {
            $totalDonationsElements.text(stats.total_donations || 0);
        }

        if ($totalAmountElements.length > 0) {
            $totalAmountElements.text(formatCurrency(stats.total_amount || 0));
        }

        if ($averageAmountElements.length > 0) {
            $averageAmountElements.text(formatCurrency(stats.average_amount || 0));
        }
    }

    /**
     * Track donation click
     */
    function trackDonationClick($element, type) {
        var donationType = type || ($element.hasClass('twitch-bmc-link') ? 'bmc' : 'paypal');
        
        // Send tracking data to server
        $.ajax({
            url: twitchDonations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_donation_settings',
                donation_action: 'track_click',
                type: donationType,
                nonce: twitchDonations.nonce
            },
            success: function(response) {
                // Tracking successful
                console.log('Donation click tracked');
            },
            error: function() {
                console.log('Failed to track donation click');
            }
        });

        // Trigger custom event
        $(document).trigger('twitch_donation_click', [donationType, $element]);
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        var currency = twitchDonations.currency || 'EUR';
        var symbols = {
            'EUR': '€',
            'USD': '$',
            'GBP': '£',
            'CHF': 'Fr'
        };
        
        var symbol = symbols[currency] || currency;
        return symbol + parseFloat(amount).toFixed(2).replace('.', ',');
    }

    /**
     * Show donation success message
     */
    function showDonationSuccess(message) {
        var message = message || twitchDonations.thankYouMessage;
        
        var $successMessage = $('<div class="twitch-donation-success">' + message + '</div>');
        
        $('.twitch-donations-container').prepend($successMessage);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $successMessage.fadeOut(function() {
                $successMessage.remove();
            });
        }, 5000);
    }

    /**
     * Show donation error message
     */
    function showDonationError(message) {
        var $errorMessage = $('<div class="twitch-donation-error">' + message + '</div>');
        
        $('.twitch-donations-container').prepend($errorMessage);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $errorMessage.fadeOut(function() {
                $errorMessage.remove();
            });
        }, 5000);
    }

    /**
     * Handle URL parameters for donation success
     */
    function handleDonationSuccess() {
        var urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('donation') === 'success') {
            showDonationSuccess();
            
            // Remove the parameter from URL
            var newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        if (urlParams.get('donation') === 'cancelled') {
            showDonationError('Spende wurde abgebrochen. Sie können es jederzeit erneut versuchen.');
            
            // Remove the parameter from URL
            var newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    }

    /**
     * Initialize donation widgets
     */
    window.initTwitchDonations = function() {
        initDonationHandlers();
        initDonationGoal();
        initDonationStats();
        handleDonationSuccess();
    };

    /**
     * Refresh donation data
     */
    window.refreshTwitchDonations = function() {
        loadDonationStats();
        updateDonationGoal();
    };

    /**
     * Show donation modal
     */
    window.showDonationModal = function(type, amount) {
        // This could be implemented to show a custom donation modal
        console.log('Show donation modal:', type, amount);
    };

    /**
     * Handle custom donation events
     */
    $(document).on('twitch_donation_click', function(event, type, $element) {
        // Custom handling for donation clicks
        console.log('Donation clicked:', type, $element);
    });

    // Handle URL parameters on page load
    $(document).ready(function() {
        handleDonationSuccess();
    });

    // Expose functions globally
    window.TwitchDonations = {
        refresh: refreshTwitchDonations,
        showSuccess: showDonationSuccess,
        showError: showDonationError,
        formatCurrency: formatCurrency
    };

})(jQuery);
