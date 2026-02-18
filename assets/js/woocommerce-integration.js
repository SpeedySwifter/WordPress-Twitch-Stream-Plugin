/**
 * WooCommerce Integration JavaScript
 */

(function($) {
    'use strict';

    // Global WooCommerce integration instance
    window.twitchWooCommerce = {
        enabled: true,
        showStreamProduct: true,
        showChatProduct: false,
        streamStatus: {},
        productStreams: {}
    };

    // Initialize WooCommerce integration
    $(document).ready(function() {
        initWooCommerceIntegration();
        initEventHandlers();
        initStreamMonitoring();
        initProductIntegration();
    });

    /**
     * Initialize WooCommerce integration
     */
    function initWooCommerceIntegration() {
        // Set global variables
        window.twitchWooCommerce.enabled = twitchWooCommerce.enabled !== false;
        window.twitchWooCommerce.showStreamProduct = twitchWooCommerce.showStreamProduct !== false;
        window.twitchWooCommerce.showChatProduct = twitchWooCommerce.showChatProduct === true;
        
        // Check if WooCommerce is active
        if (!window.twitchWooCommerce.enabled) {
            console.log('Twitch WooCommerce integration is disabled');
            return;
        }
        
        // Initialize product streams
        initProductStreams();
        
        // Add Twitch badges to products
        addTwitchBadges();
        
        // Initialize stream embeds
        initStreamEmbeds();
        
        // Initialize cart monitoring
        initCartMonitoring();
    }

    /**
     * Initialize event handlers
     */
    function initEventHandlers() {
        // Product page events
        $(document).on('click', '.twitch-stream-button', function(e) {
            e.preventDefault();
            var streamUrl = $(this).attr('href');
            openStreamModal(streamUrl);
        });

        // Watch stream buttons
        $(document).on('click', '.twitch-watch-stream-btn', function(e) {
            e.preventDefault();
            var channel = $(this).data('channel') || 'default';
            openStreamModal('https://www.twitch.tv/' + channel);
        });

        // Product grid hover effects
        $(document).on('mouseenter', '.twitch-woo-product.twitch-product', function() {
            $(this).addClass('twitch-product-hover');
        });

        $(document).on('mouseleave', '.twitch-woo-product.twitch-product', function() {
            $(this).removeClass('twitch-product-hover');
        });

        // Cart update events
        $(document.body).on('updated_cart_totals', function() {
            updateCartNotices();
        });

        // Checkout events
        $(document.body).on('checkout_updated', function() {
            updateCheckoutNotices();
        });

        // Product variation changes
        $(document).on('found_variation', function(event, variation) {
            updateProductStreamInfo(variation);
        });

        // Add to cart events
        $(document).on('added_to_cart', function(event, fragments, cart_hash, button) {
            handleAddToCart(button);
        });

        // Remove from cart events
        $(document).on('removed_from_cart', function(event, fragments, cart_hash, button) {
            handleRemoveFromCart(button);
        });
    }

    /**
     * Initialize stream monitoring
     */
    function initStreamMonitoring() {
        // Monitor stream status for Twitch products
        $('.twitch-woo-product.twitch-product').each(function() {
            var $product = $(this);
            var productId = $product.data('product-id');
            var channel = $product.data('twitch-channel');
            
            if (channel) {
                monitorStreamStatus(channel, productId);
            }
        });

        // Auto-refresh stream status
        setInterval(function() {
            refreshStreamStatus();
        }, 60000); // Check every minute
    }

    /**
     * Initialize product integration
     */
    function initProductIntegration() {
        // Add Twitch data to product elements
        $('.twitch-woo-product').each(function() {
            var $product = $(this);
            var productId = $product.data('product-id');
            
            if (productId) {
                loadProductStreamData(productId);
            }
        });

        // Initialize single product page
        if ($('.single-product').length) {
            initSingleProductPage();
        }

        // Initialize cart page
        if ($('.woocommerce-cart').length) {
            initCartPage();
        }

        // Initialize checkout page
        if ($('.woocommerce-checkout').length) {
            initCheckoutPage();
        }
    }

    /**
     * Initialize product streams
     */
    function initProductStreams() {
        $('.twitch-woo-product.twitch-product').each(function() {
            var $product = $(this);
            var productId = $product.data('product-id');
            var channel = $product.data('twitch-channel');
            var streamUrl = $product.data('twitch-stream-url');
            
            if (productId && channel) {
                window.twitchWooCommerce.productStreams[productId] = {
                    channel: channel,
                    streamUrl: streamUrl,
                    isLive: false,
                    viewers: 0,
                    game: ''
                };
            }
        });
    }

    /**
     * Add Twitch badges to products
     */
    function addTwitchBadges() {
        $('.twitch-woo-product.twitch-product').each(function() {
            var $product = $(this);
            var $image = $product.find('.twitch-woo-product-image');
            
            if ($image.length && !$image.find('.twitch-woo-stream-badge').length) {
                var $badge = $('<div class="twitch-woo-stream-badge">' +
                    '<span class="twitch-badge-icon">🎮</span>' +
                    '<span class="twitch-badge-text">Twitch Product</span>' +
                    '</div>');
                
                $image.append($badge);
            }
        });
    }

    /**
     * Initialize stream embeds
     */
    function initStreamEmbeds() {
        if (window.twitchWooCommerce.showStreamProduct) {
            $('.twitch-product-stream iframe').each(function() {
                var $iframe = $(this);
                var src = $iframe.attr('src');
                
                if (src && src.includes('twitch.tv')) {
                    // Ensure proper embed parameters
                    if (!src.includes('parent=')) {
                        var parent = window.location.hostname;
                        src += (src.includes('?') ? '&' : '?') + 'parent=' + parent;
                        $iframe.attr('src', src);
                    }
                }
            });
        }
    }

    /**
     * Initialize cart monitoring
     */
    function initCartMonitoring() {
        // Check cart for Twitch products
        updateCartNotices();
        
        // Monitor cart changes
        $(document.body).on('wc_cart_button_updated', function() {
            setTimeout(updateCartNotices, 500);
        });
    }

    /**
     * Initialize single product page
     */
    function initSingleProductPage() {
        var $productInfo = $('.twitch-product-info');
        
        if ($productInfo.length) {
            // Add stream monitoring
            var channel = $productInfo.data('twitch-channel');
            if (channel) {
                monitorStreamStatus(channel, 'single-product');
            }
            
            // Initialize stream embed
            if (window.twitchWooCommerce.showStreamProduct) {
                initSingleProductStream();
            }
            
            // Initialize chat embed
            if (window.twitchWooCommerce.showChatProduct) {
                initSingleProductChat();
            }
        }
    }

    /**
     * Initialize single product stream
     */
    function initSingleProductStream() {
        var $streamEmbed = $('.twitch-product-stream iframe');
        
        if ($streamEmbed.length) {
            var src = $streamEmbed.attr('src');
            var parent = window.location.hostname;
            
            // Ensure proper embed parameters
            if (!src.includes('parent=')) {
                src += (src.includes('?') ? '&' : '?') + 'parent=' + parent;
                $streamEmbed.attr('src', src);
            }
            
            // Add resize handler
            $(window).on('resize', function() {
                resizeStreamEmbed($streamEmbed);
            });
            
            // Initial resize
            resizeStreamEmbed($streamEmbed);
        }
    }

    /**
     * Initialize single product chat
     */
    function initSingleProductChat() {
        var $chatEmbed = $('.twitch-chat-embed iframe');
        
        if ($chatEmbed.length) {
            var src = $chatEmbed.attr('src');
            var parent = window.location.hostname;
            
            // Ensure proper embed parameters
            if (!src.includes('parent=')) {
                src += (src.includes('?') ? '&' : '?') + 'parent=' + parent;
                $chatEmbed.attr('src', src);
            }
            
            // Add resize handler
            $(window).on('resize', function() {
                resizeChatEmbed($chatEmbed);
            });
            
            // Initial resize
            resizeChatEmbed($chatEmbed);
        }
    }

    /**
     * Initialize cart page
     */
    function initCartPage() {
        updateCartNotices();
        
        // Add stream links for Twitch products
        $('.woocommerce-cart-form .cart_item').each(function() {
            var $item = $(this);
            var productName = $item.find('.product-name a').text();
            
            if (window.twitchWooCommerce.productStreams[productName]) {
                addCartStreamLink($item, window.twitchWooCommerce.productStreams[productName]);
            }
        });
    }

    /**
     * Initialize checkout page
     */
    function initCheckoutPage() {
        updateCheckoutNotices();
        
        // Add stream encouragement
        if (hasTwitchProductsInCart()) {
            addCheckoutStreamEncouragement();
        }
    }

    /**
     * Monitor stream status
     */
    function monitorStreamStatus(channel, productId) {
        if (!twitchWooCommerce.ajaxUrl || !twitchWooCommerce.nonce) {
            return;
        }
        
        $.ajax({
            url: twitchWooCommerce.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_woo_product',
                woo_action: 'check_stream_status',
                channel: channel,
                nonce: twitchWooCommerce.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    updateStreamStatus(channel, response.data);
                    
                    if (productId) {
                        updateProductStreamBadge(productId, response.data);
                    }
                }
            },
            error: function() {
                console.warn('Failed to check stream status for:', channel);
            }
        });
    }

    /**
     * Update stream status
     */
    function updateStreamStatus(channel, data) {
        window.twitchWooCommerce.streamStatus[channel] = {
            isLive: data.is_live,
            viewers: data.viewers,
            game: data.game,
            lastChecked: new Date()
        };
        
        // Update UI elements
        updateStreamUI(channel, data);
    }

    /**
     * Update stream UI
     */
    function updateStreamUI(channel, data) {
        // Update badges
        $('.twitch-woo-stream-badge').each(function() {
            var $badge = $(this);
            var $product = $badge.closest('.twitch-woo-product');
            var productChannel = $product.data('twitch-channel');
            
            if (productChannel === channel) {
                if (data.is_live) {
                    $badge.addClass('twitch-live');
                    $badge.find('.twitch-badge-text').text('LIVE');
                    $badge.find('.twitch-badge-icon').text('🔴');
                } else {
                    $badge.removeClass('twitch-live');
                    $badge.find('.twitch-badge-text').text('Twitch Product');
                    $badge.find('.twitch-badge-icon').text('🎮');
                }
            }
        });
        
        // Update stream info
        $('.twitch-stream-info').each(function() {
            var $info = $(this);
            var $product = $info.closest('.twitch-woo-product');
            var productChannel = $product.data('twitch-channel');
            
            if (productChannel === channel && data.is_live) {
                var viewersText = data.viewers > 0 ? ' • ' + formatNumber(data.viewers) + ' viewers' : '';
                var gameText = data.game ? ' • ' + data.game : '';
                $info.html('🎮 ' + channel + viewersText + gameText);
            }
        });
    }

    /**
     * Update product stream badge
     */
    function updateProductStreamBadge(productId, data) {
        var $product = $('.twitch-woo-product[data-product-id="' + productId + '"]');
        
        if ($product.length) {
            var $badge = $product.find('.twitch-woo-stream-badge');
            
            if (data.is_live) {
                $badge.addClass('twitch-live');
                $badge.find('.twitch-badge-text').text('LIVE');
                $badge.find('.twitch-badge-icon').text('🔴');
            } else {
                $badge.removeClass('twitch-live');
                $badge.find('.twitch-badge-text').text('Twitch Product');
                $badge.find('.twitch-badge-icon').text('🎮');
            }
        }
    }

    /**
     * Refresh stream status
     */
    function refreshStreamStatus() {
        Object.keys(window.twitchWooCommerce.streamStatus).forEach(function(channel) {
            monitorStreamStatus(channel);
        });
        
        // Also check product streams
        Object.keys(window.twitchWooCommerce.productStreams).forEach(function(productId) {
            var streamData = window.twitchWooCommerce.productStreams[productId];
            if (streamData.channel) {
                monitorStreamStatus(streamData.channel, productId);
            }
        });
    }

    /**
     * Load product stream data
     */
    function loadProductStreamData(productId) {
        if (!twitchWooCommerce.ajaxUrl || !twitchWooCommerce.nonce) {
            return;
        }
        
        $.ajax({
            url: twitchWooCommerce.ajaxUrl,
            type: 'POST',
            data: {
                action: 'twitch_woo_product',
                woo_action: 'get_product_stream',
                product_id: productId,
                nonce: twitchWooCommerce.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    window.twitchWooCommerce.productStreams[productId] = response.data;
                    
                    if (response.data.enabled && response.data.channel) {
                        monitorStreamStatus(response.data.channel, productId);
                    }
                }
            },
            error: function() {
                console.warn('Failed to load product stream data for:', productId);
            }
        });
    }

    /**
     * Update cart notices
     */
    function updateCartNotices() {
        var $cartNotice = $('.twitch-cart-notice');
        
        if (hasTwitchProductsInCart()) {
            if ($cartNotice.length === 0) {
                var noticeHtml = '<div class="twitch-cart-notice">' +
                    '<div class="twitch-notice-content">' +
                    '<span class="twitch-notice-icon">🎮</span>' +
                    '<span class="twitch-notice-text">You have Twitch-related products in your cart!</span>' +
                    '</div>' +
                    '</div>';
                
                $('.woocommerce-cart-form').before(noticeHtml);
            }
        } else {
            $cartNotice.remove();
        }
    }

    /**
     * Update checkout notices
     */
    function updateCheckoutNotices() {
        var $checkoutNotice = $('.twitch-checkout-notice');
        
        if (hasTwitchProductsInCart()) {
            if ($checkoutNotice.length === 0) {
                var noticeHtml = '<div class="twitch-checkout-notice">' +
                    '<div class="twitch-notice-content">' +
                    '<span class="twitch-notice-icon">🎮</span>' +
                    '<span class="twitch-notice-text">Complete your purchase while watching the stream!</span>' +
                    '</div>' +
                    '</div>';
                
                $('.woocommerce-checkout').prepend(noticeHtml);
            }
        } else {
            $checkoutNotice.remove();
        }
    }

    /**
     * Check if cart has Twitch products
     */
    function hasTwitchProductsInCart() {
        // This would check the cart for Twitch products
        // For now, we'll use a simple check
        return $('.twitch-woo-product.twitch-product').length > 0;
    }

    /**
     * Add cart stream link
     */
    function addCartStreamLink($item, streamData) {
        var $productLink = $item.find('.product-name');
        
        if ($productLink.length && streamData.channel) {
            var $streamLink = $('<a href="https://www.twitch.tv/' + streamData.channel + '" class="twitch-cart-stream-link" target="_blank">🎮 Watch Stream</a>');
            $productLink.append(' ').append($streamLink);
        }
    }

    /**
     * Add checkout stream encouragement
     */
    function addCheckoutStreamEncouragement() {
        var $checkoutForm = $('.woocommerce-checkout');
        
        if ($checkoutForm.length && !$checkoutForm.find('.twitch-checkout-encouragement').length) {
            var encouragementHtml = '<div class="twitch-checkout-encouragement">' +
                '<h3>🎮 Watch While You Shop!</h3>' +
                '<p>Complete your purchase while enjoying the stream. Your support helps keep the content coming!</p>' +
                '<button class="twitch-checkout-stream-btn">Open Stream</button>' +
                '</div>';
            
            $checkoutForm.prepend(encouragementHtml);
        }
    }

    /**
     * Handle add to cart
     */
    function handleAddToCart($button) {
        var $product = $button.closest('.twitch-woo-product');
        
        if ($product.hasClass('twitch-product')) {
            // Show success message for Twitch product
            showTwitchCartMessage('Twitch product added to cart! 🎮');
            
            // Update cart notices
            setTimeout(updateCartNotices, 500);
        }
    }

    /**
     * Handle remove from cart
     */
    function handleRemoveFromCart($button) {
        // Update cart notices after removal
        setTimeout(updateCartNotices, 500);
    }

    /**
     * Update product stream info
     */
    function updateProductStreamInfo(variation) {
        // This would update stream info based on product variation
        console.log('Product variation changed:', variation);
    }

    /**
     * Open stream modal
     */
    function openStreamModal(streamUrl) {
        // Create modal if it doesn't exist
        if ($('#twitch-stream-modal').length === 0) {
            var modalHtml = '<div id="twitch-stream-modal" class="twitch-stream-modal">' +
                '<div class="twitch-modal-content">' +
                '<div class="twitch-modal-header">' +
                '<h3>Twitch Stream</h3>' +
                '<button class="twitch-modal-close">&times;</button>' +
                '</div>' +
                '<div class="twitch-modal-body">' +
                '<iframe src="' + streamUrl + '" frameborder="0" allowfullscreen></iframe>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            $('body').append(modalHtml);
            
            // Add modal close handlers
            $('.twitch-modal-close, .twitch-stream-modal').on('click', function(e) {
                if (e.target === this) {
                    closeStreamModal();
                }
            });
        }
        
        // Show modal
        $('#twitch-stream-modal').addClass('twitch-modal-open');
        $('body').addClass('twitch-modal-open');
    }

    /**
     * Close stream modal
     */
    function closeStreamModal() {
        $('#twitch-stream-modal').removeClass('twitch-modal-open');
        $('body').removeClass('twitch-modal-open');
    }

    /**
     * Resize stream embed
     */
    function resizeStreamEmbed($iframe) {
        var containerWidth = $iframe.parent().width();
        var aspectRatio = 16 / 9;
        var height = containerWidth / aspectRatio;
        
        $iframe.css({
            width: containerWidth,
            height: height
        });
    }

    /**
     * Resize chat embed
     */
    function resizeChatEmbed($iframe) {
        var containerWidth = $iframe.parent().width();
        var height = 400; // Fixed height for chat
        
        $iframe.css({
            width: containerWidth,
            height: height
        });
    }

    /**
     * Show Twitch cart message
     */
    function showTwitchCartMessage(message) {
        // Remove existing messages
        $('.twitch-cart-message').remove();
        
        // Create message
        var $message = $('<div class="twitch-cart-message">' +
            '<span class="twitch-message-icon">🎮</span>' +
            '<span class="twitch-message-text">' + message + '</span>' +
            '</div>');
        
        // Add to page
        $('body').append($message);
        
        // Show message
        setTimeout(function() {
            $message.addClass('twitch-message-show');
        }, 100);
        
        // Hide message after 3 seconds
        setTimeout(function() {
            $message.removeClass('twitch-message-show');
            setTimeout(function() {
                $message.remove();
            }, 300);
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
     * Expose functions globally
     */
    window.TwitchWooCommerce = {
        openStreamModal: openStreamModal,
        closeStreamModal: closeStreamModal,
        refreshStreamStatus: refreshStreamStatus,
        hasTwitchProductsInCart: hasTwitchProductsInCart
    };

})(jQuery);
