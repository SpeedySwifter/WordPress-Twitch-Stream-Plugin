/**
 * Twitch Chat Integration JavaScript
 */

(function($) {
    'use strict';

    // Global chat instances
    window.twitchChatInstances = {};

    // Initialize chat functionality
    $(document).ready(function() {
        initChatContainers();
        initChatEventListeners();
        initWebSocketConnections();
    });

    /**
     * Initialize chat containers
     */
    function initChatContainers() {
        $('.twitch-chat-container').each(function() {
            var $container = $(this);
            var channel = $container.data('channel');
            
            if (channel) {
                initChatInstance($container, channel);
            }
        });
    }

    /**
     * Initialize chat instance
     */
    function initChatInstance($container, channel) {
        var chatId = 'twitch-chat-' + channel;
        
        window.twitchChatInstances[chatId] = {
            container: $container,
            channel: channel,
            connected: false,
            messages: [],
            maxMessages: parseInt($container.data('max-messages') || 100),
            websocket: null,
            reconnectAttempts: 0,
            maxReconnectAttempts: 5,
            reconnectDelay: 1000
        };
        
        // Load initial messages
        loadRecentMessages(channel);
        
        // Connect to chat
        connectToChat(channel);
    }

    /**
     * Connect to chat
     */
    function connectToChat(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (!instance) return;
        
        // Show loading state
        showLoadingState(channel);
        
        // Simulate connection (in real implementation, connect to Twitch IRC)
        setTimeout(function() {
            instance.connected = true;
            hideLoadingState(channel);
            updateChatStatus(channel, 'connected');
            
            // Start message polling
            startMessagePolling(channel);
            
            // Trigger connection event
            $(document).trigger('twitch_chat_connected', [channel]);
        }, 1000);
    }

    /**
     * Start message polling
     */
    function startMessagePolling(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (!instance || !instance.connected) return;
        
        // Poll for new messages every 5 seconds
        instance.pollInterval = setInterval(function() {
            pollMessages(channel);
        }, 5000);
    }

    /**
     * Poll for messages
     */
    function pollMessages(channel) {
        $.ajax({
            url: twitchChat.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_chat_messages',
                channel: channel,
                limit: 10,
                nonce: twitchChat.nonce
            },
            success: function(response) {
                if (response.success && response.data.messages) {
                    processNewMessages(channel, response.data.messages);
                }
            },
            error: function() {
                console.log('Failed to poll messages for channel:', channel);
            }
        });
    }

    /**
     * Process new messages
     */
    function processNewMessages(channel, newMessages) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (!instance) return;
        
        var $messagesContainer = instance.container.find('.twitch-chat-messages');
        var currentMessageCount = $messagesContainer.children().length;
        
        // Remove old messages if we exceed the limit
        if (currentMessageCount >= instance.maxMessages) {
            var removeCount = currentMessageCount - instance.maxMessages + newMessages.length;
            $messagesContainer.children().slice(0, removeCount).remove();
        }
        
        // Add new messages
        newMessages.forEach(function(message) {
            addMessageToChat(channel, message);
        });
        
        // Scroll to bottom
        scrollToBottom(channel);
    }

    /**
     * Add message to chat
     */
    function addMessageToChat(channel, message) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (!instance) return;
        
        var $messagesContainer = instance.container.find('.twitch-chat-messages');
        var messageHtml = createMessageHtml(message);
        
        $messagesContainer.append(messageHtml);
        
        // Store message
        instance.messages.push(message);
        
        // Limit stored messages
        if (instance.messages.length > instance.maxMessages) {
            instance.messages.shift();
        }
    }

    /**
     * Create message HTML
     */
    function createMessageHtml(message) {
        var html = '<div class="twitch-chat-message" data-user="' + escapeHtml(message.user) + '">';
        
        // Timestamp
        if (message.timestamp) {
            html += '<span class="twitch-chat-timestamp">' + escapeHtml(message.timestamp) + '</span>';
        }
        
        // Badges
        if (message.badges && message.badges.length > 0) {
            html += '<span class="twitch-chat-badges">';
            message.badges.forEach(function(badge) {
                html += '<span class="twitch-chat-badge twitch-badge-' + escapeHtml(badge) + '"></span>';
            });
            html += '</span>';
        }
        
        // Username
        var usernameColor = message.color || getUserColor(message.user);
        html += '<span class="twitch-chat-username" style="color: ' + usernameColor + '">' + escapeHtml(message.user) + ':</span>';
        
        // Message text
        html += '<span class="twitch-chat-message-text">' + parseEmotes(message.message) + '</span>';
        
        html += '</div>';
        
        return html;
    }

    /**
     * Parse emotes in message
     */
    function parseEmotes(message) {
        // This would parse Twitch emotes and replace them with images
        // For now, return the message as-is
        return escapeHtml(message);
    }

    /**
     * Get user color
     */
    function getUserColor(username) {
        // Generate consistent color based on username
        var hash = 0;
        for (var i = 0; i < username.length; i++) {
            hash = username.charCodeAt(i) + ((hash << 5) - hash);
        }
        var hue = hash % 360;
        return 'hsl(' + hue + ', 70%, 60%)';
    }

    /**
     * Load recent messages
     */
    function loadRecentMessages(channel) {
        $.ajax({
            url: twitchChat.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_chat_messages',
                channel: channel,
                limit: 20,
                nonce: twitchChat.nonce
            },
            success: function(response) {
                if (response.success && response.data.messages) {
                    var chatId = 'twitch-chat-' + channel;
                    var instance = window.twitchChatInstances[chatId];
                    
                    if (instance) {
                        response.data.messages.forEach(function(message) {
                            addMessageToChat(channel, message);
                        });
                        
                        scrollToBottom(channel);
                    }
                }
            },
            error: function() {
                console.log('Failed to load recent messages for channel:', channel);
            }
        });
    }

    /**
     * Scroll to bottom of chat
     */
    function scrollToBottom(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            var $messagesContainer = instance.container.find('.twitch-chat-messages');
            $messagesContainer.scrollTop($messagesContainer[0].scrollHeight);
        }
    }

    /**
     * Show loading state
     */
    function showLoadingState(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            instance.container.find('.twitch-chat-loading').show();
        }
    }

    /**
     * Hide loading state
     */
    function hideLoadingState(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            instance.container.find('.twitch-chat-loading').hide();
        }
    }

    /**
     * Update chat status
     */
    function updateChatStatus(channel, status) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            var $statusElement = instance.container.find('.twitch-chat-status');
            
            if (status === 'connected') {
                $statusElement.find('.twitch-chat-live-indicator').show();
            } else {
                $statusElement.find('.twitch-chat-live-indicator').hide();
            }
        }
    }

    /**
     * Initialize chat event listeners
     */
    function initChatEventListeners() {
        // Emoji picker toggle
        $(document).on('click', '.twitch-chat-toggle-emoji', function(e) {
            e.preventDefault();
            var $container = $(this).closest('.twitch-chat-container');
            $container.find('.twitch-chat-emoji-picker').toggle();
        });
        
        // Settings panel toggle
        $(document).on('click', '.twitch-chat-toggle-settings', function(e) {
            e.preventDefault();
            var $container = $(this).closest('.twitch-chat-container');
            $container.find('.twitch-chat-settings-panel').toggle();
        });
        
        // Clear chat
        $(document).on('click', '.twitch-chat-clear', function(e) {
            e.preventDefault();
            var $container = $(this).closest('.twitch-chat-container');
            var channel = $container.data('channel');
            clearChat(channel);
        });
        
        // Emoji selection
        $(document).on('click', '.twitch-emoji-btn', function(e) {
            e.preventDefault();
            var emoji = $(this).data('emoji');
            var $container = $(this).closest('.twitch-chat-container');
            var $input = $container.find('.twitch-chat-input');
            
            $input.val($input.val() + emoji);
            $container.find('.twitch-chat-emoji-picker').hide();
            $input.focus();
        });
        
        // Send message
        $(document).on('click', '.twitch-chat-send', function(e) {
            e.preventDefault();
            var $container = $(this).closest('.twitch-chat-container');
            var channel = $container.data('channel');
            var $input = $container.find('.twitch-chat-input');
            var message = $input.val().trim();
            
            if (message) {
                sendMessage(channel, message);
                $input.val('');
            }
        });
        
        // Enter key to send message
        $(document).on('keypress', '.twitch-chat-input', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                var $container = $(this).closest('.twitch-chat-container');
                var channel = $container.data('channel');
                var message = $(this).val().trim();
                
                if (message) {
                    sendMessage(channel, message);
                    $(this).val('');
                }
            }
        });
        
        // Settings changes
        $(document).on('change', '.twitch-chat-theme-select', function() {
            var theme = $(this).val();
            var $container = $(this).closest('.twitch-chat-container');
            
            $container.removeClass('twitch-chat-dark twitch-chat-light twitch-chat-blue')
                     .addClass('twitch-chat-' + theme);
        });
        
        $(document).on('change', '.twitch-chat-font-size', function() {
            var size = $(this).val();
            var $container = $(this).closest('.twitch-chat-container');
            
            $container.removeClass('twitch-chat-small twitch-chat-medium twitch-chat-large')
                     .addClass('twitch-chat-' + size);
        });
        
        $(document).on('change', '.twitch-chat-show-timestamps', function() {
            var show = $(this).is(':checked');
            var $container = $(this).closest('.twitch-chat-container');
            
            if (show) {
                $container.find('.twitch-chat-timestamp').show();
            } else {
                $container.find('.twitch-chat-timestamp').hide();
            }
        });
        
        $(document).on('change', '.twitch-chat-show-badges', function() {
            var show = $(this).is(':checked');
            var $container = $(this).closest('.twitch-chat-container');
            
            if (show) {
                $container.find('.twitch-chat-badges').show();
            } else {
                $container.find('.twitch-chat-badges').hide();
            }
        });
        
        // Close panels when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.twitch-chat-emoji-picker, .twitch-chat-toggle-emoji').length) {
                $('.twitch-chat-emoji-picker').hide();
            }
            
            if (!$(e.target).closest('.twitch-chat-settings-panel, .twitch-chat-toggle-settings').length) {
                $('.twitch-chat-settings-panel').hide();
            }
        });
        
        // Custom events
        $(document).on('twitch_chat_connected', function(e, channel) {
            console.log('Chat connected for channel:', channel);
        });
        
        $(document).on('twitch_chat_disconnected', function(e, channel) {
            console.log('Chat disconnected for channel:', channel);
        });
        
        $(document).on('twitch_chat_message_received', function(e, channel, message) {
            console.log('Message received:', channel, message);
        });
        
        $(document).on('twitch_chat_message_sent', function(e, channel, message) {
            console.log('Message sent:', channel, message);
        });
    }

    /**
     * Send message
     */
    function sendMessage(channel, message) {
        // Check if message is a command
        if (message.startsWith('!')) {
            processCommand(channel, message);
            return;
        }
        
        // Validate message
        if (!validateMessage(message)) {
            showError(channel, 'Message contains inappropriate content');
            return;
        }
        
        $.ajax({
            url: twitchChat.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_chat',
                chat_action: 'send_message',
                channel: channel,
                message: message,
                nonce: twitchChat.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Add message to chat immediately for better UX
                    var userMessage = {
                        user: 'You',
                        message: message,
                        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        color: '#9146ff',
                        badges: [],
                        emotes: []
                    };
                    
                    addMessageToChat(channel, userMessage);
                    scrollToBottom(channel);
                    
                    // Trigger event
                    $(document).trigger('twitch_chat_message_sent', [channel, message]);
                } else {
                    showError(channel, 'Failed to send message');
                }
            },
            error: function() {
                showError(channel, 'Network error. Please try again.');
            }
        });
    }

    /**
     * Process command
     */
    function processCommand(channel, command) {
        $.ajax({
            url: twitchChat.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_chat',
                chat_action: 'process_command',
                channel: channel,
                command: command,
                nonce: twitchChat.nonce
            },
            success: function(response) {
                if (response.success && response.data.response) {
                    var responseMessage = {
                        user: 'System',
                        message: response.data.response,
                        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        color: '#ffc107',
                        badges: ['system'],
                        emotes: []
                    };
                    
                    addMessageToChat(channel, responseMessage);
                    scrollToBottom(channel);
                }
            },
            error: function() {
                console.log('Failed to process command:', command);
            }
        });
    }

    /**
     * Validate message
     */
    function validateMessage(message) {
        // Basic validation
        if (message.length === 0 || message.length > 500) {
            return false;
        }
        
        // Check for blocked words (this would be server-side in real implementation)
        var blockedWords = ['spam', 'abuse', 'inappropriate'];
        var lowerMessage = message.toLowerCase();
        
        for (var i = 0; i < blockedWords.length; i++) {
            if (lowerMessage.includes(blockedWords[i])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Clear chat
     */
    function clearChat(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            var $messagesContainer = instance.container.find('.twitch-chat-messages');
            $messagesContainer.empty();
            instance.messages = [];
            
            // Add system message
            var clearMessage = {
                user: 'System',
                message: 'Chat has been cleared',
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                color: '#ffc107',
                badges: ['system'],
                emotes: []
            };
            
            addMessageToChat(channel, clearMessage);
        }
    }

    /**
     * Show error message
     */
    function showError(channel, message) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            var errorMessage = {
                user: 'System',
                message: message,
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                color: '#dc3545',
                badges: ['system'],
                emotes: []
            };
            
            addMessageToChat(channel, errorMessage);
            scrollToBottom(channel);
        }
    }

    /**
     * Show success message
     */
    function showSuccess(channel, message) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance) {
            var successMessage = {
                user: 'System',
                message: message,
                timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                color: '#28a745',
                badges: ['system'],
                emotes: []
            };
            
            addMessageToChat(channel, successMessage);
            scrollToBottom(channel);
        }
    }

    /**
     * Initialize WebSocket connections
     */
    function initWebSocketConnections() {
        // This would initialize WebSocket connections to Twitch IRC
        // For now, we'll use polling as demonstrated above
        console.log('WebSocket connections initialized');
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Disconnect from chat
     */
    function disconnectFromChat(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance && instance.pollInterval) {
            clearInterval(instance.pollInterval);
            instance.connected = false;
            updateChatStatus(channel, 'disconnected');
            
            // Trigger event
            $(document).trigger('twitch_chat_disconnected', [channel]);
        }
    }

    /**
     * Reconnect to chat
     */
    function reconnectToChat(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (instance && instance.reconnectAttempts < instance.maxReconnectAttempts) {
            instance.reconnectAttempts++;
            
            setTimeout(function() {
                connectToChat(channel);
            }, instance.reconnectDelay * instance.reconnectAttempts);
        }
    }

    /**
     * Get chat statistics
     */
    function getChatStatistics(channel) {
        var chatId = 'twitch-chat-' + channel;
        var instance = window.twitchChatInstances[chatId];
        
        if (!instance) {
            return null;
        }
        
        return {
            channel: channel,
            connected: instance.connected,
            messageCount: instance.messages.length,
            maxMessages: instance.maxMessages,
            reconnectAttempts: instance.reconnectAttempts
        };
    }

    /**
     * Expose functions globally
     */
    window.TwitchChat = {
        connect: connectToChat,
        disconnect: disconnectFromChat,
        sendMessage: sendMessage,
        clear: clearChat,
        getStatistics: getChatStatistics,
        reconnect: reconnectToChat,
        refresh: loadRecentMessages
    };

})(jQuery);
