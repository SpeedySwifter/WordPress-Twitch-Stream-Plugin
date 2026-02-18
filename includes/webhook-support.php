<?php
/**
 * Twitch EventSub Webhook Support
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Webhook_Support {
    
    private $api;
    private $webhook_secret;
    private $webhook_url;
    
    public function __construct() {
        $this->api = new WP_Twitch_API();
        $this->webhook_secret = get_option('twitch_webhook_secret');
        $this->webhook_url = home_url('/wp-json/wp-twitch-stream/v1/webhook');
        
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
        add_action('init', array($this, 'schedule_webhook_cleanup'));
        add_action('wp_twitch_cleanup_webhooks', array($this, 'cleanup_expired_subscriptions'));
    }
    
    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        register_rest_route('wp-twitch-stream/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Handle incoming webhook
     */
    public function handle_webhook($request) {
        $headers = $request->get_headers();
        $body = $request->get_body();
        $signature = $headers['twitch_eventsub_message_signature'][0] ?? '';
        
        // Verify webhook signature
        if (!$this->verify_webhook_signature($body, $signature)) {
            return new WP_Error(
                'invalid_signature',
                'Invalid webhook signature',
                array('status' => 403)
            );
        }
        
        $data = json_decode($body, true);
        
        if (!$data) {
            return new WP_Error(
                'invalid_json',
                'Invalid JSON data',
                array('status' => 400)
            );
        }
        
        $message_type = $data['subscription']['type'] ?? '';
        
        switch ($message_type) {
            case 'webhook_callback_verification':
                return $this->handle_verification($data);
            case 'notification':
                return $this->handle_notification($data);
            case 'revocation':
                return $this->handle_revocation($data);
            default:
                return new WP_Error(
                    'unknown_message_type',
                    'Unknown message type',
                    array('status' => 400)
                );
        }
    }
    
    /**
     * Handle webhook verification
     */
    private function handle_verification($data) {
        $challenge = $data['challenge'] ?? '';
        
        if (empty($challenge)) {
            return new WP_Error(
                'missing_challenge',
                'Missing challenge in verification',
                array('status' => 400)
            );
        }
        
        // Store subscription info
        $subscription_id = $data['subscription']['id'];
        $subscription_type = $data['subscription']['type'];
        $condition = $data['subscription']['condition'];
        
        $this->store_subscription($subscription_id, $subscription_type, $condition);
        
        return new WP_REST_Response(array(
            'challenge' => $challenge,
        ), 200);
    }
    
    /**
     * Handle webhook notification
     */
    private function handle_notification($data) {
        $event_type = $data['subscription']['type'];
        $event_data = $data['event'];
        
        switch ($event_type) {
            case 'stream.online':
                $this->handle_stream_online($event_data);
                break;
            case 'stream.offline':
                $this->handle_stream_offline($event_data);
                break;
            case 'channel.update':
                $this->handle_channel_update($event_data);
                break;
            case 'user.follow':
                $this->handle_user_follow($event_data);
                break;
            case 'channel.subscribe':
                $this->handle_channel_subscribe($event_data);
                break;
            case 'channel.cheer':
                $this->handle_channel_cheer($event_data);
                break;
            case 'channel.raid':
                $this->handle_channel_raid($event_data);
                break;
            case 'channel.subscription.gift':
                $this->handle_subscription_gift($event_data);
                break;
        }
        
        return new WP_REST_Response(array(
            'status' => 'ok',
        ), 200);
    }
    
    /**
     * Handle webhook revocation
     */
    private function handle_revocation($data) {
        $subscription_id = $data['subscription']['id'];
        $reason = $data['subscription']['status'];
        
        $this->remove_subscription($subscription_id);
        
        error_log("Twitch webhook subscription revoked: {$subscription_id} - Reason: {$reason}");
        
        return new WP_REST_Response(array(
            'status' => 'ok',
        ), 200);
    }
    
    /**
     * Handle stream online event
     */
    private function handle_stream_online($event_data) {
        $user_id = $event_data['broadcaster_user_id'];
        $user_login = $event_data['broadcaster_user_login'];
        $game_id = $event_data['game_id'];
        $started_at = $event_data['started_at'];
        
        // Update stream status
        $this->update_stream_status($user_login, 'online', array(
            'user_id' => $user_id,
            'game_id' => $game_id,
            'started_at' => $started_at,
        ));
        
        // Trigger custom actions
        do_action('wp_twitch_stream_online', $user_login, $event_data);
        
        // Send notifications
        $this->send_stream_notification($user_login, 'online');
    }
    
    /**
     * Handle stream offline event
     */
    private function handle_stream_offline($event_data) {
        $user_id = $event_data['broadcaster_user_id'];
        $user_login = $event_data['broadcaster_user_login'];
        
        // Update stream status
        $this->update_stream_status($user_login, 'offline', array(
            'user_id' => $user_id,
        ));
        
        // Trigger custom actions
        do_action('wp_twitch_stream_offline', $user_login, $event_data);
        
        // Send notifications
        $this->send_stream_notification($user_login, 'offline');
    }
    
    /**
     * Handle channel update event
     */
    private function handle_channel_update($event_data) {
        $user_id = $event_data['broadcaster_user_id'];
        $user_login = $event_data['broadcaster_user_login'];
        $title = $event_data['title'];
        $language = $event_data['language'];
        $category_id = $event_data['category_id'];
        $category_name = $event_data['category_name'];
        
        // Update channel info
        $this->update_channel_info($user_login, array(
            'title' => $title,
            'language' => $language,
            'category_id' => $category_id,
            'category_name' => $category_name,
        ));
        
        // Trigger custom actions
        do_action('wp_twitch_channel_update', $user_login, $event_data);
    }
    
    /**
     * Handle user follow event
     */
    private function handle_user_follow($event_data) {
        $broadcaster_user_id = $event_data['broadcaster_user_id'];
        $broadcaster_user_login = $event_data['broadcaster_user_login'];
        $user_id = $event_data['user_id'];
        $user_login = $event_data['user_login'];
        $followed_at = $event_data['followed_at'];
        
        // Store follow event
        $this->store_follow_event($broadcaster_user_login, $user_login, $followed_at);
        
        // Trigger custom actions
        do_action('wp_twitch_user_follow', $broadcaster_user_login, $user_login, $event_data);
        
        // Send notifications
        $this->send_follow_notification($broadcaster_user_login, $user_login);
    }
    
    /**
     * Handle channel subscribe event
     */
    private function handle_channel_subscribe($event_data) {
        $broadcaster_user_id = $event_data['broadcaster_user_id'];
        $broadcaster_user_login = $event_data['broadcaster_user_login'];
        $user_id = $event_data['user_id'];
        $user_login = $event_data['user_login'];
        $tier = $event_data['tier'];
        
        // Store subscribe event
        $this->store_subscribe_event($broadcaster_user_login, $user_login, $tier);
        
        // Trigger custom actions
        do_action('wp_twitch_channel_subscribe', $broadcaster_user_login, $user_login, $tier, $event_data);
        
        // Send notifications
        $this->send_subscribe_notification($broadcaster_user_login, $user_login, $tier);
    }
    
    /**
     * Handle channel cheer event
     */
    private function handle_channel_cheer($event_data) {
        $broadcaster_user_id = $event_data['broadcaster_user_id'];
        $broadcaster_user_login = $event_data['broadcaster_user_login'];
        $user_id = $event_data['user_id'];
        $user_login = $event_data['user_login'];
        $message = $event_data['message'];
        $bits = $event_data['bits'];
        
        // Store cheer event
        $this->store_cheer_event($broadcaster_user_login, $user_login, $bits, $message);
        
        // Trigger custom actions
        do_action('wp_twitch_channel_cheer', $broadcaster_user_login, $user_login, $bits, $message, $event_data);
        
        // Send notifications
        $this->send_cheer_notification($broadcaster_user_login, $user_login, $bits);
    }
    
    /**
     * Handle channel raid event
     */
    private function handle_channel_raid($event_data) {
        $from_broadcaster_user_id = $event_data['from_broadcaster_user_id'];
        $from_broadcaster_user_login = $event_data['from_broadcaster_user_login'];
        $to_broadcaster_user_id = $event_data['to_broadcaster_user_id'];
        $to_broadcaster_user_login = $event_data['to_broadcaster_user_login'];
        $viewers = $event_data['viewers'];
        
        // Store raid event
        $this->store_raid_event($from_broadcaster_user_login, $to_broadcaster_user_login, $viewers);
        
        // Trigger custom actions
        do_action('wp_twitch_channel_raid', $from_broadcaster_user_login, $to_broadcaster_user_login, $viewers, $event_data);
        
        // Send notifications
        $this->send_raid_notification($from_broadcaster_user_login, $to_broadcaster_user_login, $viewers);
    }
    
    /**
     * Handle subscription gift event
     */
    private function handle_subscription_gift($event_data) {
        $broadcaster_user_id = $event_data['broadcaster_user_id'];
        $broadcaster_user_login = $event_data['broadcaster_user_login'];
        $user_id = $event_data['user_id'];
        $user_login = $event_data['user_login'];
        $total = $event_data['total'];
        $tier = $event_data['tier'];
        
        // Store subscription gift event
        $this->store_subscription_gift_event($broadcaster_user_login, $user_login, $total, $tier);
        
        // Trigger custom actions
        do_action('wp_twitch_subscription_gift', $broadcaster_user_login, $user_login, $total, $tier, $event_data);
        
        // Send notifications
        $this->send_subscription_gift_notification($broadcaster_user_login, $user_login, $total, $tier);
    }
    
    /**
     * Create webhook subscription
     */
    public function create_subscription($type, $condition, $version = '1') {
        $subscription_data = array(
            'type' => $type,
            'version' => $version,
            'condition' => $condition,
            'transport' => array(
                'method' => 'webhook',
                'callback' => $this->webhook_url,
                'secret' => $this->webhook_secret,
            ),
        );
        
        $response = wp_remote_post(
            'https://api.twitch.tv/helix/eventsub/subscriptions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api->access_token,
                    'Client-Id' => $this->api->client_id,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($subscription_data),
            )
        );
        
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!empty($data['data'][0]['id'])) {
                $subscription_id = $data['data'][0]['id'];
                $status = $data['data'][0]['status'];
                $cost = $data['data'][0]['cost'];
                
                $this->store_subscription($subscription_id, $type, $condition, $status, $cost);
                
                return array(
                    'success' => true,
                    'subscription_id' => $subscription_id,
                    'status' => $status,
                    'cost' => $cost,
                );
            }
        }
        
        return array(
            'success' => false,
            'error' => 'Failed to create subscription',
        );
    }
    
    /**
     * Delete webhook subscription
     */
    public function delete_subscription($subscription_id) {
        $response = wp_remote_request(
            "https://api.twitch.tv/helix/eventsub/subscriptions?id={$subscription_id}",
            array(
                'method' => 'DELETE',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api->access_token,
                    'Client-Id' => $this->api->client_id,
                ),
            )
        );
        
        if (!is_wp_error($response)) {
            $status_code = wp_remote_retrieve_response_code($response);
            
            if ($status_code === 204) {
                $this->remove_subscription($subscription_id);
                return array('success' => true);
            }
        }
        
        return array(
            'success' => false,
            'error' => 'Failed to delete subscription',
        );
    }
    
    /**
     * Get all subscriptions
     */
    public function get_subscriptions() {
        $response = wp_remote_get(
            'https://api.twitch.tv/helix/eventsub/subscriptions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api->access_token,
                    'Client-Id' => $this->api->client_id,
                ),
            )
        );
        
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data['data'] ?? array();
        }
        
        return array();
    }
    
    /**
     * Verify webhook signature
     */
    private function verify_webhook_signature($body, $signature) {
        if (empty($this->webhook_secret)) {
            return false;
        }
        
        $hmac_message = $this->webhook_secret . $body;
        $expected_signature = 'sha256=' . hash_hmac('sha256', $hmac_message, $this->webhook_secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Store subscription
     */
    private function store_subscription($subscription_id, $type, $condition, $status = 'pending', $cost = 0) {
        $subscriptions = get_option('twitch_webhook_subscriptions', array());
        
        $subscriptions[$subscription_id] = array(
            'id' => $subscription_id,
            'type' => $type,
            'condition' => $condition,
            'status' => $status,
            'cost' => $cost,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );
        
        update_option('twitch_webhook_subscriptions', $subscriptions);
    }
    
    /**
     * Remove subscription
     */
    private function remove_subscription($subscription_id) {
        $subscriptions = get_option('twitch_webhook_subscriptions', array());
        
        if (isset($subscriptions[$subscription_id])) {
            unset($subscriptions[$subscription_id]);
            update_option('twitch_webhook_subscriptions', $subscriptions);
        }
    }
    
    /**
     * Update stream status
     */
    private function update_stream_status($channel, $status, $data = array()) {
        $streams = get_option('twitch_stream_status', array());
        
        $streams[$channel] = array_merge(array(
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ), $data);
        
        update_option('twitch_stream_status', $streams);
    }
    
    /**
     * Update channel info
     */
    private function update_channel_info($channel, $data) {
        $channels = get_option('twitch_channel_info', array());
        
        $channels[$channel] = array_merge($channels[$channel] ?? array(), $data);
        $channels[$channel]['updated_at'] = current_time('mysql');
        
        update_option('twitch_channel_info', $channels);
    }
    
    /**
     * Store follow event
     */
    private function store_follow_event($channel, $follower, $followed_at) {
        $events = get_option('twitch_follow_events', array());
        
        $events[] = array(
            'channel' => $channel,
            'follower' => $follower,
            'followed_at' => $followed_at,
            'created_at' => current_time('mysql'),
        );
        
        // Keep only last 100 events
        $events = array_slice($events, -100);
        
        update_option('twitch_follow_events', $events);
    }
    
    /**
     * Store subscribe event
     */
    private function store_subscribe_event($channel, $subscriber, $tier) {
        $events = get_option('twitch_subscribe_events', array());
        
        $events[] = array(
            'channel' => $channel,
            'subscriber' => $subscriber,
            'tier' => $tier,
            'created_at' => current_time('mysql'),
        );
        
        // Keep only last 100 events
        $events = array_slice($events, -100);
        
        update_option('twitch_subscribe_events', $events);
    }
    
    /**
     * Store cheer event
     */
    private function store_cheer_event($channel, $user, $bits, $message) {
        $events = get_option('twitch_cheer_events', array());
        
        $events[] = array(
            'channel' => $channel,
            'user' => $user,
            'bits' => $bits,
            'message' => $message,
            'created_at' => current_time('mysql'),
        );
        
        // Keep only last 100 events
        $events = array_slice($events, -100);
        
        update_option('twitch_cheer_events', $events);
    }
    
    /**
     * Store raid event
     */
    private function store_raid_event($from_channel, $to_channel, $viewers) {
        $events = get_option('twitch_raid_events', array());
        
        $events[] = array(
            'from_channel' => $from_channel,
            'to_channel' => $to_channel,
            'viewers' => $viewers,
            'created_at' => current_time('mysql'),
        );
        
        // Keep only last 100 events
        $events = array_slice($events, -100);
        
        update_option('twitch_raid_events', $events);
    }
    
    /**
     * Store subscription gift event
     */
    private function store_subscription_gift_event($channel, $user, $total, $tier) {
        $events = get_option('twitch_subscription_gift_events', array());
        
        $events[] = array(
            'channel' => $channel,
            'user' => $user,
            'total' => $total,
            'tier' => $tier,
            'created_at' => current_time('mysql'),
        );
        
        // Keep only last 100 events
        $events = array_slice($events, -100);
        
        update_option('twitch_subscription_gift_events', $events);
    }
    
    /**
     * Send stream notification
     */
    private function send_stream_notification($channel, $status) {
        if (!get_option('twitch_webhook_notifications', false)) {
            return;
        }
        
        $webhook_url = get_option('twitch_discord_webhook_url');
        if ($webhook_url) {
            $this->send_discord_notification($channel, $status, $webhook_url);
        }
    }
    
    /**
     * Send follow notification
     */
    private function send_follow_notification($channel, $follower) {
        if (!get_option('twitch_webhook_notifications', false)) {
            return;
        }
        
        $webhook_url = get_option('twitch_discord_webhook_url');
        if ($webhook_url) {
            $this->send_discord_follow_notification($channel, $follower, $webhook_url);
        }
    }
    
    /**
     * Send subscribe notification
     */
    private function send_subscribe_notification($channel, $subscriber, $tier) {
        if (!get_option('twitch_webhook_notifications', false)) {
            return;
        }
        
        $webhook_url = get_option('twitch_discord_webhook_url');
        if ($webhook_url) {
            $this->send_discord_subscribe_notification($channel, $subscriber, $tier, $webhook_url);
        }
    }
    
    /**
     * Send cheer notification
     */
    private function send_cheer_notification($channel, $user, $bits) {
        if (!get_option('twitch_webhook_notifications', false)) {
            return;
        }
        
        $webhook_url = get_option('twitch_discord_webhook_url');
        if ($webhook_url) {
            $this->send_discord_cheer_notification($channel, $user, $bits, $webhook_url);
        }
    }
    
    /**
     * Send raid notification
     */
    private function send_raid_notification($from_channel, $to_channel, $viewers) {
        if (!get_option('twitch_webhook_notifications', false)) {
            return;
        }
        
        $webhook_url = get_option('twitch_discord_webhook_url');
        if ($webhook_url) {
            $this->send_discord_raid_notification($from_channel, $to_channel, $viewers, $webhook_url);
        }
    }
    
    /**
     * Send subscription gift notification
     */
    private function send_subscription_gift_notification($channel, $user, $total, $tier) {
        if (!get_option('twitch_webhook_notifications', false)) {
            return;
        }
        
        $webhook_url = get_option('twitch_discord_webhook_url');
        if ($webhook_url) {
            $this->send_discord_subscription_gift_notification($channel, $user, $total, $tier, $webhook_url);
        }
    }
    
    /**
     * Send Discord notification
     */
    private function send_discord_notification($channel, $status, $webhook_url) {
        $color = $status === 'online' ? 0x00FF00 : 0xFF0000;
        $title = $status === 'online' ? "🔴 {$channel} is now LIVE!" : "⚫ {$channel} is now OFFLINE";
        
        $payload = array(
            'embeds' => array(
                array(
                    'title' => $title,
                    'color' => $color,
                    'timestamp' => date('c'),
                ),
            ),
        );
        
        wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
        ));
    }
    
    /**
     * Send Discord follow notification
     */
    private function send_discord_follow_notification($channel, $follower, $webhook_url) {
        $payload = array(
            'embeds' => array(
                array(
                    'title' => "👥 New Follower!",
                    'description' => "**{$follower}** is now following **{$channel}**!",
                    'color' => 0x9146FF,
                    'timestamp' => date('c'),
                ),
            ),
        );
        
        wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
        ));
    }
    
    /**
     * Send Discord subscribe notification
     */
    private function send_discord_subscribe_notification($channel, $subscriber, $tier, $webhook_url) {
        $tier_emoji = array(
            '1000' => '🔷',
            '2000' => '🔶',
            '3000' => '🔴',
        );
        
        $emoji = $tier_emoji[$tier] ?? '⭐';
        
        $payload = array(
            'embeds' => array(
                array(
                    'title' => "⭐ New Subscriber!",
                    'description' => "**{$subscriber}** subscribed to **{$channel}**! {$emoji} Tier {$tier}",
                    'color' => 0x9146FF,
                    'timestamp' => date('c'),
                ),
            ),
        );
        
        wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
        ));
    }
    
    /**
     * Send Discord cheer notification
     */
    private function send_discord_cheer_notification($channel, $user, $bits, $webhook_url) {
        $payload = array(
            'embeds' => array(
                array(
                    'title' => "💎 New Cheer!",
                    'description' => "**{$user}** cheered **{$bits} bits** to **{$channel}**!",
                    'color' => 0x9146FF,
                    'timestamp' => date('c'),
                ),
            ),
        );
        
        wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
        ));
    }
    
    /**
     * Send Discord raid notification
     */
    private function send_discord_raid_notification($from_channel, $to_channel, $viewers, $webhook_url) {
        $payload = array(
            'embeds' => array(
                array(
                    'title' => "🎯 Raid!",
                    'description' => "**{$from_channel}** raided **{$to_channel}** with **{$viewers}** viewers!",
                    'color' => 0x9146FF,
                    'timestamp' => date('c'),
                ),
            ),
        );
        
        wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
        ));
    }
    
    /**
     * Send Discord subscription gift notification
     */
    private function send_discord_subscription_gift_notification($channel, $user, $total, $tier, $webhook_url) {
        $tier_emoji = array(
            '1000' => '🔷',
            '2000' => '🔶',
            '3000' => '🔴',
        );
        
        $emoji = $tier_emoji[$tier] ?? '⭐';
        
        $payload = array(
            'embeds' => array(
                array(
                    'title' => "🎁 Subscription Gift!",
                    'description' => "**{$user}** gifted **{$total}** Tier {$tier} subscriptions to **{$channel}**! {$emoji}",
                    'color' => 0x9146FF,
                    'timestamp' => date('c'),
                ),
            ),
        );
        
        wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
        ));
    }
    
    /**
     * Schedule webhook cleanup
     */
    public function schedule_webhook_cleanup() {
        if (!wp_next_scheduled('wp_twitch_cleanup_webhooks')) {
            wp_schedule_event(time(), 'daily', 'wp_twitch_cleanup_webhooks');
        }
    }
    
    /**
     * Cleanup expired subscriptions
     */
    public function cleanup_expired_subscriptions() {
        $subscriptions = $this->get_subscriptions();
        $stored_subscriptions = get_option('twitch_webhook_subscriptions', array());
        
        $active_subscription_ids = array();
        foreach ($subscriptions as $subscription) {
            $active_subscription_ids[] = $subscription['id'];
        }
        
        foreach ($stored_subscriptions as $subscription_id => $subscription) {
            if (!in_array($subscription_id, $active_subscription_ids)) {
                $this->remove_subscription($subscription_id);
            }
        }
    }
}

// Initialize webhook support
new WP_Twitch_Webhook_Support();
