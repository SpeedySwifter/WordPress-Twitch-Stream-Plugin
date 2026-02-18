/**
 * Advanced Analytics Dashboard JavaScript
 */

(function($) {
    'use strict';

    // Global dashboard instance
    window.twitchAnalyticsDashboard = {
        charts: {},
        data: {},
        settings: {},
        refreshTimer: null
    };

    // Initialize dashboard
    $(document).ready(function() {
        initDashboard();
        initEventHandlers();
        startAutoRefresh();
    });

    /**
     * Initialize dashboard
     */
    function initDashboard() {
        // Load initial data
        loadDashboardData();
        
        // Initialize charts
        initCharts();
        
        // Load overview data
        loadOverviewData();
        
        // Load chart data
        loadChartData('viewers');
        loadChartData('revenue');
        
        // Load tables
        loadTopStreams();
        loadTopGames();
    }

    /**
     * Initialize event handlers
     */
    function initEventHandlers() {
        // Channel filter
        $(document).on('change', '#twitch-channel-filter, .twitch-channel-select', function() {
            var channel = $(this).val();
            var period = $('#twitch-date-range').val() || $('.twitch-period-select').val();
            
            updateDashboard(channel, period);
        });

        // Date range filter
        $(document).on('change', '#twitch-date-range, .twitch-period-select', function() {
            var channel = $('#twitch-channel-filter').val() || $('.twitch-channel-select').val();
            var period = $(this).val();
            
            updateDashboard(channel, period);
        });

        // Refresh button
        $(document).on('click', '#twitch-refresh-dashboard, .twitch-refresh-btn', function() {
            var $btn = $(this);
            var $icon = $btn.find('.dashicons, .twitch-refresh-icon');
            
            // Add spinning animation
            $icon.addClass('spinning');
            
            // Refresh data
            var channel = $('#twitch-channel-filter').val() || $('.twitch-channel-select').val();
            var period = $('#twitch-date-range').val() || $('.twitch-period-select').val();
            
            updateDashboard(channel, period, function() {
                $icon.removeClass('spinning');
            });
        });

        // Chart type buttons
        $(document).on('click', '.twitch-chart-type', function() {
            var $btn = $(this);
            var chart = $btn.data('chart');
            var type = $btn.data('type');
            
            // Update active state
            $btn.siblings().removeClass('active');
            $btn.addClass('active');
            
            // Update chart
            updateChartType(chart, type);
        });

        // Export buttons
        $(document).on('click', '.twitch-export-btn', function() {
            var format = $(this).data('format');
            exportData(format);
        });

        // Analytics tabs
        $(document).on('click', '.twitch-tab', function() {
            var $tab = $(this);
            var tabId = $tab.data('tab');
            
            // Update active tab
            $tab.siblings().removeClass('active');
            $tab.addClass('active');
            
            // Update tab content
            $('.twitch-tab-pane').removeClass('active');
            $('#' + tabId + '-tab').addClass('active');
            
            // Load tab-specific data
            loadTabData(tabId);
        });

        // Shortcode dashboard events
        $(document).on('change', '.twitch-channel-select, .twitch-period-select', function() {
            var $dashboard = $(this).closest('.twitch-analytics-dashboard');
            var channel = $dashboard.find('.twitch-channel-select').val();
            var period = $dashboard.find('.twitch-period-select').val();
            
            updateShortcodeDashboard($dashboard, channel, period);
        });
    }

    /**
     * Load dashboard data
     */
    function loadDashboardData() {
        $.ajax({
            url: twitchAnalyticsDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_analytics_dashboard',
                analytics_action: 'get_overview',
                channel: '',
                period: 30,
                nonce: twitchAnalyticsDashboard.nonce
            },
            success: function(response) {
                if (response.success && response.data.overview) {
                    window.twitchAnalyticsDashboard.data.overview = response.data.overview;
                    updateOverviewCards(response.data.overview);
                }
            },
            error: function() {
                console.log('Failed to load dashboard data');
            }
        });
    }

    /**
     * Load overview data
     */
    function loadOverviewData(channel, period) {
        channel = channel || '';
        period = period || 30;
        
        $.ajax({
            url: twitchAnalyticsDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_analytics_dashboard',
                analytics_action: 'get_overview',
                channel: channel,
                period: period,
                nonce: twitchAnalyticsDashboard.nonce
            },
            success: function(response) {
                if (response.success && response.data.overview) {
                    updateOverviewCards(response.data.overview);
                    updateDetailedStats(response.data.overview);
                }
            },
            error: function() {
                showError('Fehler beim Laden der Übersichtsdaten');
            }
        });
    }

    /**
     * Load chart data
     */
    function loadChartData(chartType, channel, period) {
        channel = channel || '';
        period = period || 30;
        
        $.ajax({
            url: twitchAnalyticsDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_analytics_dashboard',
                analytics_action: 'get_chart_data',
                channel: channel,
                period: period,
                chart_type: chartType,
                nonce: twitchAnalyticsDashboard.nonce
            },
            success: function(response) {
                if (response.success && response.data.chart_data) {
                    updateChart(chartType, response.data.chart_data);
                }
            },
            error: function() {
                showError('Fehler beim Laden der Chart-Daten');
            }
        });
    }

    /**
     * Load top streams
     */
    function loadTopStreams(channel, period) {
        channel = channel || '';
        period = period || 30;
        
        $.ajax({
            url: twitchAnalyticsDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_analytics_dashboard',
                analytics_action: 'get_top_streams',
                channel: channel,
                period: period,
                limit: 10,
                nonce: twitchAnalyticsDashboard.nonce
            },
            success: function(response) {
                if (response.success && response.data.top_streams) {
                    updateTopStreamsTable(response.data.top_streams);
                }
            },
            error: function() {
                showError('Fehler beim Laden der Top Streams');
            }
        });
    }

    /**
     * Load top games
     */
    function loadTopGames(channel, period) {
        channel = channel || '';
        period = period || 30;
        
        $.ajax({
            url: twitchAnalyticsDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_analytics_dashboard',
                analytics_action: 'get_top_games',
                channel: channel,
                period: period,
                limit: 10,
                nonce: twitchAnalyticsDashboard.nonce
            },
            success: function(response) {
                if (response.success && response.data.top_games) {
                    updateTopGamesTable(response.data.top_games);
                }
            },
            error: function() {
                showError('Fehler beim Laden der Top Spiele');
            }
        });
    }

    /**
     * Initialize charts
     */
    function initCharts() {
        // Initialize viewers chart
        var viewersCtx = document.getElementById('viewers-chart');
        if (viewersCtx) {
            window.twitchAnalyticsDashboard.charts.viewers = new Chart(viewersCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Zuschauer',
                        data: [],
                        borderColor: twitchAnalyticsDashboard.chartColors.primary,
                        backgroundColor: 'rgba(145, 70, 255, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Initialize revenue chart
        var revenueCtx = document.getElementById('revenue-chart');
        if (revenueCtx) {
            window.twitchAnalyticsDashboard.charts.revenue = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Einnahmen',
                        data: [],
                        borderColor: twitchAnalyticsDashboard.chartColors.success,
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Initialize shortcode charts
        initShortcodeCharts();
    }

    /**
     * Initialize shortcode charts
     */
    function initShortcodeCharts() {
        // Shortcode viewers chart
        var shortcodeViewersCtx = document.getElementById('shortcode-viewers-chart');
        if (shortcodeViewersCtx) {
            new Chart(shortcodeViewersCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Zuschauer',
                        data: [],
                        borderColor: twitchAnalyticsDashboard.chartColors.primary,
                        backgroundColor: 'rgba(145, 70, 255, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Shortcode revenue chart
        var shortcodeRevenueCtx = document.getElementById('shortcode-revenue-chart');
        if (shortcodeRevenueCtx) {
            new Chart(shortcodeRevenueCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Einnahmen',
                        data: [],
                        borderColor: twitchAnalyticsDashboard.chartColors.success,
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Widget charts
        $('.twitch-widget-chart canvas').each(function() {
            new Chart(this, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Zuschauer',
                        data: [],
                        borderColor: twitchAnalyticsDashboard.chartColors.primary,
                        backgroundColor: 'rgba(145, 70, 255, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }

    /**
     * Update overview cards
     */
    function updateOverviewCards(overview) {
        // Update total viewers
        $('#total-viewers').text(formatNumber(overview.total_viewers || 0));
        
        // Update total duration
        $('#total-duration').text(formatDuration(overview.total_duration || 0));
        
        // Update total revenue
        $('#total-revenue').text(formatCurrency(overview.total_revenue || 0));
        
        // Update change indicators
        updateChangeIndicator('viewers-change', overview.viewers_change || 0);
        updateChangeIndicator('duration-change', overview.duration_change || 0);
        updateChangeIndicator('revenue-change', overview.revenue_change || 0);
    }

    /**
     * Update detailed stats
     */
    function updateDetailedStats(overview) {
        // Overview tab
        $('#avg-viewers').text(formatNumber(overview.avg_viewers || 0));
        $('#peak-viewers').text(formatNumber(overview.peak_viewers || 0));
        $('#total-streams').text(formatNumber(overview.total_streams || 0));
        $('#avg-duration').text(formatDuration(overview.avg_duration || 0));
        
        // Engagement tab
        $('#chat-messages').text(formatNumber(overview.chat_messages || 0));
        $('#unique-chatters').text(formatNumber(overview.unique_chatters || 0));
        $('#clips-created').text(formatNumber(overview.clips_created || 0));
        $('#follows').text(formatNumber(overview.follows || 0));
        
        // Monetization tab
        $('#donations').text(formatCurrency(overview.donations || 0));
        $('#subscriptions').text(formatNumber(overview.subscriptions || 0));
        $('#bits').text(formatNumber(overview.bits || 0));
        $('#ad-revenue').text(formatCurrency(overview.ad_revenue || 0));
        
        // Audience tab
        $('#top-country').text(overview.top_country || '-');
        $('#languages').text(overview.languages || '-');
        $('#age-group').text(overview.age_group || '-');
        $('#gender').text(overview.gender || '-');
    }

    /**
     * Update chart
     */
    function updateChart(chartType, chartData) {
        var chart = window.twitchAnalyticsDashboard.charts[chartType];
        
        if (chart && chartData) {
            chart.data.labels = chartData.labels || [];
            chart.data.datasets[0].data = chartData.data || [];
            chart.update();
        }
    }

    /**
     * Update chart type
     */
    function updateChartType(chart, type) {
        var chartInstance = window.twitchAnalyticsDashboard.charts[chart];
        
        if (chartInstance) {
            chartInstance.config.type = type;
            chartInstance.update();
        }
    }

    /**
     * Update top streams table
     */
    function updateTopStreamsTable(topStreams) {
        var $tbody = $('#top-streams');
        
        if (!$tbody.length) return;
        
        $tbody.empty();
        
        topStreams.forEach(function(stream) {
            var $row = $('<tr>')
                .append($('<td>').text(stream.title))
                .append($('<td>').text(formatDate(stream.date)))
                .append($('<td>').text(formatNumber(stream.viewers)))
                .append($('<td>').text(formatDuration(stream.duration)));
            
            $tbody.append($row);
        });
    }

    /**
     * Update top games table
     */
    function updateTopGamesTable(topGames) {
        var $tbody = $('#top-games');
        
        if (!$tbody.length) return;
        
        $tbody.empty();
        
        topGames.forEach(function(game) {
            var $row = $('<tr>')
                .append($('<td>').text(game.name))
                .append($('<td>').text(formatNumber(game.streams)))
                .append($('<td>').text(formatNumber(game.viewers)))
                .append($('<td>').text(formatDuration(game.duration)));
            
            $tbody.append($row);
        });
    }

    /**
     * Update dashboard
     */
    function updateDashboard(channel, period, callback) {
        // Load all data
        loadOverviewData(channel, period);
        loadChartData('viewers', channel, period);
        loadChartData('revenue', channel, period);
        loadTopStreams(channel, period);
        loadTopGames(channel, period);
        
        // Execute callback if provided
        if (typeof callback === 'function') {
            setTimeout(callback, 1000);
        }
    }

    /**
     * Update shortcode dashboard
     */
    function updateShortcodeDashboard($dashboard, channel, period) {
        // Update shortcode charts
        var viewersChart = $dashboard.find('#shortcode-viewers-chart').data('chart');
        var revenueChart = $dashboard.find('#shortcode-revenue-chart').data('chart');
        
        if (viewersChart) {
            loadChartData('viewers', channel, period);
        }
        
        if (revenueChart) {
            loadChartData('revenue', channel, period);
        }
        
        // Update shortcode tables
        loadTopStreams(channel, period);
    }

    /**
     * Load tab data
     */
    function loadTabData(tabId) {
        // This would load tab-specific data
        console.log('Loading data for tab:', tabId);
    }

    /**
     * Export data
     */
    function exportData(format) {
        var channel = $('#twitch-channel-filter').val() || '';
        var period = $('#twitch-date-range').val() || 30;
        
        // Create form for download
        var $form = $('<form>', {
            method: 'POST',
            action: twitchAnalyticsDashboard.ajaxUrl
        });
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: 'twitch_analytics_dashboard'
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'analytics_action',
            value: 'export_data'
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'channel',
            value: channel
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'period',
            value: period
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'format',
            value: format
        }));
        
        $form.append($('<input>', {
            type: 'hidden',
            name: 'nonce',
            value: twitchAnalyticsDashboard.nonce
        }));
        
        // Submit form
        $form.appendTo('body').submit().remove();
    }

    /**
     * Update change indicator
     */
    function updateChangeIndicator(elementId, change) {
        var $element = $('#' + elementId);
        
        if (!$element.length) return;
        
        var changeText = change >= 0 ? '+' + change + '%' : change + '%';
        var changeClass = change > 0 ? 'positive' : (change < 0 ? 'negative' : 'neutral');
        
        $element.text(changeText).removeClass('positive negative neutral').addClass(changeClass);
    }

    /**
     * Start auto refresh
     */
    function startAutoRefresh() {
        if (twitchAnalyticsDashboard.refreshInterval > 0) {
            window.twitchAnalyticsDashboard.refreshTimer = setInterval(function() {
                var channel = $('#twitch-channel-filter').val() || '';
                var period = $('#twitch-date-range').val() || 30;
                
                loadOverviewData(channel, period);
            }, twitchAnalyticsDashboard.refreshInterval);
        }
    }

    /**
     * Stop auto refresh
     */
    function stopAutoRefresh() {
        if (window.twitchAnalyticsDashboard.refreshTimer) {
            clearInterval(window.twitchAnalyticsDashboard.refreshTimer);
            window.twitchAnalyticsDashboard.refreshTimer = null;
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        // Remove existing errors
        $('.twitch-error').remove();
        
        // Create error element
        var $error = $('<div class="twitch-error">')
            .text(message)
            .prependTo('.twitch-analytics-dashboard');
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $error.fadeOut(function() {
                $error.remove();
            });
        }, 5000);
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        // Remove existing success messages
        $('.twitch-success').remove();
        
        // Create success element
        var $success = $('<div class="twitch-success">')
            .text(message)
            .prependTo('.twitch-analytics-dashboard');
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
            $success.fadeOut(function() {
                $success.remove();
            });
        }, 3000);
    }

    /**
     * Format number
     */
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        } else {
            return num.toString();
        }
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return '€' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Format duration
     */
    function formatDuration(minutes) {
        if (minutes >= 60) {
            var hours = Math.floor(minutes / 60);
            var mins = minutes % 60;
            return hours + 'h ' + mins + 'm';
        } else {
            return minutes + 'm';
        }
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('de-DE');
    }

    /**
     * Add spinning animation
     */
    $.fn.extend({
        addSpinning: function() {
            return this.addClass('spinning');
        },
        removeSpinning: function() {
            return this.removeClass('spinning');
        }
    });

    // Add spinning CSS
    $('<style>')
        .text('.spinning { animation: spin 1s linear infinite; } @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }')
        .appendTo('head');

    /**
     * Expose functions globally
     */
    window.TwitchAnalyticsDashboard = {
        refresh: updateDashboard,
        export: exportData,
        stopAutoRefresh: stopAutoRefresh,
        startAutoRefresh: startAutoRefresh
    };

})(jQuery);
