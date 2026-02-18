<?php
/**
 * Advanced Analytics for Twitch Stream Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Twitch_Analytics {
    
    private $api;
    private $cache;
    
    public function __construct() {
        $this->api = new WP_Twitch_API();
        $this->cache = new WP_Twitch_Cache();
    }
    
    /**
     * Get channel analytics
     */
    public function get_channel_analytics($channel, $period = 'week') {
        $cache_key = "twitch_analytics_{$channel}_{$period}";
        $cached_data = $this->cache->get($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $analytics_data = array(
            'channel' => $channel,
            'period' => $period,
            'generated_at' => current_time('mysql'),
            'stream_stats' => $this->get_stream_analytics($channel, $period),
            'viewer_stats' => $this->get_viewer_analytics($channel, $period),
            'engagement_stats' => $this->get_engagement_analytics($channel, $period),
            'growth_stats' => $this->get_growth_analytics($channel, $period),
            'content_stats' => $this->get_content_analytics($channel, $period),
        );
        
        $this->cache->set($cache_key, $analytics_data, 300); // 5 minutes cache
        
        return $analytics_data;
    }
    
    /**
     * Get stream analytics
     */
    private function get_stream_analytics($channel, $period) {
        $date_range = $this->get_date_range($period);
        $streams = $this->get_streams_in_period($channel, $date_range);
        
        if (empty($streams)) {
            return array(
                'total_streams' => 0,
                'total_hours' => 0,
                'average_duration' => 0,
                'peak_viewers' => 0,
                'average_viewers' => 0,
                'unique_days' => 0,
            );
        }
        
        $total_hours = 0;
        $total_viewers = 0;
        $peak_viewers = 0;
        $stream_days = array();
        
        foreach ($streams as $stream) {
            $duration = $this->calculate_stream_duration($stream['started_at'], $stream['ended_at'] ?? current_time('mysql'));
            $total_hours += $duration;
            $total_viewers += $stream['viewer_count'];
            $peak_viewers = max($peak_viewers, $stream['viewer_count']);
            $stream_days[] = date('Y-m-d', strtotime($stream['started_at']));
        }
        
        $unique_days = count(array_unique($stream_days));
        $average_duration = count($streams) > 0 ? $total_hours / count($streams) : 0;
        $average_viewers = count($streams) > 0 ? $total_viewers / count($streams) : 0;
        
        return array(
            'total_streams' => count($streams),
            'total_hours' => round($total_hours, 2),
            'average_duration' => round($average_duration, 2),
            'peak_viewers' => $peak_viewers,
            'average_viewers' => round($average_viewers),
            'unique_days' => $unique_days,
        );
    }
    
    /**
     * Get viewer analytics
     */
    private function get_viewer_analytics($channel, $period) {
        $date_range = $this->get_date_range($period);
        $streams = $this->get_streams_in_period($channel, $date_range);
        
        if (empty($streams)) {
            return array(
                'total_unique_viewers' => 0,
                'average_concurrent_viewers' => 0,
                'viewer_retention_rate' => 0,
                'peak_concurrent_viewers' => 0,
                'viewer_growth_rate' => 0,
            );
        }
        
        $viewer_data = array();
        $total_concurrent = 0;
        $peak_concurrent = 0;
        
        foreach ($streams as $stream) {
            $viewer_data[] = $stream['viewer_count'];
            $total_concurrent += $stream['viewer_count'];
            $peak_concurrent = max($peak_concurrent, $stream['viewer_count']);
        }
        
        $average_concurrent = count($viewer_data) > 0 ? $total_concurrent / count($viewer_data) : 0;
        $total_unique_viewers = $this->estimate_unique_viewers($streams);
        $viewer_retention_rate = $this->calculate_viewer_retention($streams);
        $viewer_growth_rate = $this->calculate_viewer_growth($channel, $period);
        
        return array(
            'total_unique_viewers' => $total_unique_viewers,
            'average_concurrent_viewers' => round($average_concurrent),
            'viewer_retention_rate' => round($viewer_retention_rate, 2),
            'peak_concurrent_viewers' => $peak_concurrent,
            'viewer_growth_rate' => round($viewer_growth_rate, 2),
        );
    }
    
    /**
     * Get engagement analytics
     */
    private function get_engagement_analytics($channel, $period) {
        $date_range = $this->get_date_range($period);
        
        $follow_events = $this->get_events_in_period('twitch_follow_events', $channel, $date_range);
        $subscribe_events = $this->get_events_in_period('twitch_subscribe_events', $channel, $date_range);
        $cheer_events = $this->get_events_in_period('twitch_cheer_events', $channel, $date_range);
        $raid_events = $this->get_events_in_period('twitch_raid_events', $channel, $date_range);
        
        $total_bits = array_sum(array_column($cheer_events, 'bits'));
        $total_raiders = array_sum(array_column($raid_events, 'viewers'));
        
        return array(
            'new_followers' => count($follow_events),
            'new_subscribers' => count($subscribe_events),
            'total_bits' => $total_bits,
            'total_cheers' => count($cheer_events),
            'total_raids_sent' => count($raid_events),
            'total_raiders_received' => $total_raiders,
            'engagement_score' => $this->calculate_engagement_score($follow_events, $subscribe_events, $cheer_events, $raid_events),
        );
    }
    
    /**
     * Get growth analytics
     */
    private function get_growth_analytics($channel, $period) {
        $current_followers = $this->get_current_followers($channel);
        $previous_followers = $this->get_previous_followers($channel, $period);
        
        $follower_growth = $current_followers - $previous_followers;
        $follower_growth_rate = $previous_followers > 0 ? ($follower_growth / $previous_followers) * 100 : 0;
        
        $viewer_growth = $this->calculate_viewer_growth($channel, $period);
        $stream_frequency = $this->calculate_stream_frequency($channel, $period);
        
        return array(
            'current_followers' => $current_followers,
            'previous_followers' => $previous_followers,
            'follower_growth' => $follower_growth,
            'follower_growth_rate' => round($follower_growth_rate, 2),
            'viewer_growth_rate' => round($viewer_growth, 2),
            'stream_frequency' => round($stream_frequency, 2),
            'growth_trend' => $this->determine_growth_trend($follower_growth, $viewer_growth),
        );
    }
    
    /**
     * Get content analytics
     */
    private function get_content_analytics($channel, $period) {
        $date_range = $this->get_date_range($period);
        
        $videos = $this->get_videos_in_period($channel, $date_range);
        $clips = $this->get_clips_in_period($channel, $date_range);
        
        $total_video_views = array_sum(array_column($videos, 'view_count'));
        $total_clip_views = array_sum(array_column($clips, 'view_count'));
        
        $top_games = $this->get_top_games_in_period($channel, $date_range);
        $content_categories = $this->analyze_content_categories($videos, $clips);
        
        return array(
            'total_videos' => count($videos),
            'total_clips' => count($clips),
            'total_video_views' => $total_video_views,
            'total_clip_views' => $total_clip_views,
            'average_video_views' => count($videos) > 0 ? round($total_video_views / count($videos)) : 0,
            'average_clip_views' => count($clips) > 0 ? round($total_clip_views / count($clips)) : 0,
            'top_games' => $top_games,
            'content_categories' => $content_categories,
            'content_performance' => $this->analyze_content_performance($videos, $clips),
        );
    }
    
    /**
     * Get multi-channel analytics
     */
    public function get_multi_channel_analytics($channels, $period = 'week') {
        $analytics_data = array();
        
        foreach ($channels as $channel) {
            $analytics_data[$channel] = $this->get_channel_analytics($channel, $period);
        }
        
        // Calculate combined metrics
        $combined_analytics = $this->combine_channel_analytics($analytics_data);
        
        return array(
            'channels' => $analytics_data,
            'combined' => $combined_analytics,
            'period' => $period,
            'generated_at' => current_time('mysql'),
        );
    }
    
    /**
     * Get real-time analytics
     */
    public function get_real_time_analytics($channel) {
        $stream_data = $this->api->get_complete_stream_info($channel);
        
        if (!$stream_data || !$stream_data['is_live']) {
            return array(
                'is_live' => false,
                'current_viewers' => 0,
                'live_duration' => 0,
                'current_game' => '',
                'current_title' => '',
            );
        }
        
        $started_at = $stream_data['stream']['started_at'];
        $live_duration = $this->calculate_stream_duration($started_at, current_time('mysql'));
        
        return array(
            'is_live' => true,
            'current_viewers' => $stream_data['stream']['viewer_count'],
            'live_duration' => round($live_duration, 2),
            'current_game' => $stream_data['game']['name'] ?? '',
            'current_title' => $stream_data['stream']['title'],
            'started_at' => $started_at,
            'thumbnail_url' => $stream_data['stream']['thumbnail_url'] ?? '',
        );
    }
    
    /**
     * Get analytics summary
     */
    public function get_analytics_summary($channel, $period = 'week') {
        $analytics = $this->get_channel_analytics($channel, $period);
        $real_time = $this->get_real_time_analytics($channel);
        
        return array(
            'channel' => $channel,
            'period' => $period,
            'real_time' => $real_time,
            'summary' => array(
                'total_streams' => $analytics['stream_stats']['total_streams'],
                'total_hours' => $analytics['stream_stats']['total_hours'],
                'average_viewers' => $analytics['viewer_stats']['average_concurrent_viewers'],
                'new_followers' => $analytics['engagement_stats']['new_followers'],
                'total_bits' => $analytics['engagement_stats']['total_bits'],
                'follower_growth_rate' => $analytics['growth_stats']['follower_growth_rate'],
                'engagement_score' => $analytics['engagement_stats']['engagement_score'],
            ),
            'trends' => array(
                'viewer_trend' => $this->calculate_viewer_trend($channel, $period),
                'follower_trend' => $this->calculate_follower_trend($channel, $period),
                'engagement_trend' => $this->calculate_engagement_trend($channel, $period),
            ),
        );
    }
    
    /**
     * Export analytics data
     */
    public function export_analytics($channel, $period = 'week', $format = 'json') {
        $analytics = $this->get_channel_analytics($channel, $period);
        
        switch ($format) {
            case 'csv':
                return $this->export_to_csv($analytics);
            case 'xml':
                return $this->export_to_xml($analytics);
            default:
                return json_encode($analytics, JSON_PRETTY_PRINT);
        }
    }
    
    /**
     * Get date range for period
     */
    private function get_date_range($period) {
        $now = current_time('timestamp');
        
        switch ($period) {
            case 'day':
                $start = strtotime('-1 day', $now);
                break;
            case 'week':
                $start = strtotime('-7 days', $now);
                break;
            case 'month':
                $start = strtotime('-30 days', $now);
                break;
            case 'year':
                $start = strtotime('-365 days', $now);
                break;
            default:
                $start = strtotime('-7 days', $now);
        }
        
        return array(
            'start' => date('Y-m-d H:i:s', $start),
            'end' => date('Y-m-d H:i:s', $now),
        );
    }
    
    /**
     * Get streams in period
     */
    private function get_streams_in_period($channel, $date_range) {
        // This would typically query a database of stored stream data
        // For now, we'll simulate with current stream data
        $stream_data = $this->api->get_complete_stream_info($channel);
        
        if (!$stream_data || !$stream_data['is_live']) {
            return array();
        }
        
        return array(
            array(
                'started_at' => $stream_data['stream']['started_at'],
                'ended_at' => current_time('mysql'),
                'viewer_count' => $stream_data['stream']['viewer_count'],
                'game_name' => $stream_data['game']['name'] ?? '',
                'title' => $stream_data['stream']['title'],
            )
        );
    }
    
    /**
     * Calculate stream duration
     */
    private function calculate_stream_duration($started_at, $ended_at) {
        $start = strtotime($started_at);
        $end = strtotime($ended_at);
        
        return ($end - $start) / 3600; // Convert to hours
    }
    
    /**
     * Estimate unique viewers
     */
    private function estimate_unique_viewers($streams) {
        // This is a simplified estimation
        // In a real implementation, you'd track unique viewers over time
        $total_viewers = array_sum(array_column($streams, 'viewer_count'));
        return round($total_viewers * 0.7); // Assume 70% are unique
    }
    
    /**
     * Calculate viewer retention
     */
    private function calculate_viewer_retention($streams) {
        if (count($streams) < 2) {
            return 100;
        }
        
        $first_stream = $streams[0];
        $last_stream = end($streams);
        
        $first_viewers = $first_stream['viewer_count'];
        $last_viewers = $last_stream['viewer_count'];
        
        if ($first_viewers == 0) {
            return 100;
        }
        
        return ($last_viewers / $first_viewers) * 100;
    }
    
    /**
     * Calculate viewer growth
     */
    private function calculate_viewer_growth($channel, $period) {
        $current_analytics = $this->get_channel_analytics($channel, $period);
        $previous_analytics = $this->get_previous_period_analytics($channel, $period);
        
        $current_avg = $current_analytics['viewer_stats']['average_concurrent_viewers'];
        $previous_avg = $previous_analytics['viewer_stats']['average_concurrent_viewers'];
        
        if ($previous_avg == 0) {
            return 0;
        }
        
        return (($current_avg - $previous_avg) / $previous_avg) * 100;
    }
    
    /**
     * Get events in period
     */
    private function get_events_in_period($event_type, $channel, $date_range) {
        $events = get_option($event_type, array());
        $filtered_events = array();
        
        foreach ($events as $event) {
            $event_time = strtotime($event['created_at']);
            $start_time = strtotime($date_range['start']);
            $end_time = strtotime($date_range['end']);
            
            if ($event_time >= $start_time && $event_time <= $end_time) {
                if ($event_type === 'twitch_follow_events' || $event_type === 'twitch_subscribe_events') {
                    if ($event['channel'] === $channel) {
                        $filtered_events[] = $event;
                    }
                } elseif ($event_type === 'twitch_raid_events') {
                    if ($event['from_channel'] === $channel || $event['to_channel'] === $channel) {
                        $filtered_events[] = $event;
                    }
                } elseif ($event_type === 'twitch_cheer_events') {
                    if ($event['channel'] === $channel) {
                        $filtered_events[] = $event;
                    }
                }
            }
        }
        
        return $filtered_events;
    }
    
    /**
     * Calculate engagement score
     */
    private function calculate_engagement_score($follow_events, $subscribe_events, $cheer_events, $raid_events) {
        $follow_weight = 1;
        $subscribe_weight = 5;
        $bits_weight = 0.01;
        $raid_weight = 2;
        
        $score = (count($follow_events) * $follow_weight) +
                 (count($subscribe_events) * $subscribe_weight) +
                 (array_sum(array_column($cheer_events, 'bits')) * $bits_weight) +
                 (count($raid_events) * $raid_weight);
        
        return round($score, 2);
    }
    
    /**
     * Get current followers
     */
    private function get_current_followers($channel) {
        $user_info = $this->api->get_user_info($channel);
        return $user_info['followers'] ?? 0;
    }
    
    /**
     * Get previous followers
     */
    private function get_previous_followers($channel, $period) {
        // This would typically query historical data
        // For now, we'll estimate based on current followers
        $current = $this->get_current_followers($channel);
        $growth_factor = $period === 'week' ? 0.05 : ($period === 'month' ? 0.2 : 0.5);
        return round($current / (1 + $growth_factor));
    }
    
    /**
     * Calculate stream frequency
     */
    private function calculate_stream_frequency($channel, $period) {
        $date_range = $this->get_date_range($period);
        $streams = $this->get_streams_in_period($channel, $date_range);
        
        $days_in_period = $this->get_days_in_period($period);
        
        return count($streams) / $days_in_period;
    }
    
    /**
     * Get days in period
     */
    private function get_days_in_period($period) {
        switch ($period) {
            case 'day':
                return 1;
            case 'week':
                return 7;
            case 'month':
                return 30;
            case 'year':
                return 365;
            default:
                return 7;
        }
    }
    
    /**
     * Determine growth trend
     */
    private function determine_growth_trend($follower_growth, $viewer_growth) {
        if ($follower_growth > 0 && $viewer_growth > 0) {
            return 'positive';
        } elseif ($follower_growth < 0 && $viewer_growth < 0) {
            return 'negative';
        } else {
            return 'mixed';
        }
    }
    
    /**
     * Get videos in period
     */
    private function get_videos_in_period($channel, $date_range) {
        $videos = $this->api->get_channel_videos($channel, 100, 'archive');
        $filtered_videos = array();
        
        foreach ($videos as $video) {
            $video_time = strtotime($video['created_at']);
            $start_time = strtotime($date_range['start']);
            $end_time = strtotime($date_range['end']);
            
            if ($video_time >= $start_time && $video_time <= $end_time) {
                $filtered_videos[] = $video;
            }
        }
        
        return $filtered_videos;
    }
    
    /**
     * Get clips in period
     */
    private function get_clips_in_period($channel, $date_range) {
        $clips = $this->api->get_channel_clips($channel, 100);
        $filtered_clips = array();
        
        foreach ($clips as $clip) {
            $clip_time = strtotime($clip['created_at']);
            $start_time = strtotime($date_range['start']);
            $end_time = strtotime($date_range['end']);
            
            if ($clip_time >= $start_time && $clip_time <= $end_time) {
                $filtered_clips[] = $clip;
            }
        }
        
        return $filtered_clips;
    }
    
    /**
     * Get top games in period
     */
    private function get_top_games_in_period($channel, $date_range) {
        $streams = $this->get_streams_in_period($channel, $date_range);
        $games = array();
        
        foreach ($streams as $stream) {
            $game = $stream['game_name'];
            if (!isset($games[$game])) {
                $games[$game] = 0;
            }
            $games[$game]++;
        }
        
        arsort($games);
        
        return array_slice($games, 0, 5, true);
    }
    
    /**
     * Analyze content categories
     */
    private function analyze_content_categories($videos, $clips) {
        $categories = array(
            'gaming' => 0,
            'just_chatting' => 0,
            'creative' => 0,
            'other' => 0,
        );
        
        // This is a simplified analysis
        // In a real implementation, you'd analyze titles, tags, etc.
        
        return $categories;
    }
    
    /**
     * Analyze content performance
     */
    private function analyze_content_performance($videos, $clips) {
        $video_performance = array();
        $clip_performance = array();
        
        foreach ($videos as $video) {
            $video_performance[] = array(
                'title' => $video['title'],
                'views' => $video['view_count'],
                'duration' => $video['duration'],
                'performance_score' => $this->calculate_video_performance_score($video),
            );
        }
        
        foreach ($clips as $clip) {
            $clip_performance[] = array(
                'title' => $clip['title'],
                'views' => $clip['view_count'],
                'duration' => $clip['duration'],
                'performance_score' => $this->calculate_clip_performance_score($clip),
            );
        }
        
        return array(
            'videos' => $video_performance,
            'clips' => $clip_performance,
        );
    }
    
    /**
     * Calculate video performance score
     */
    private function calculate_video_performance_score($video) {
        $views = $video['view_count'];
        $duration = $this->parse_duration($video['duration']);
        
        if ($duration == 0) {
            return 0;
        }
        
        $views_per_hour = $views / $duration;
        return round($views_per_hour, 2);
    }
    
    /**
     * Calculate clip performance score
     */
    private function calculate_clip_performance_score($clip) {
        $views = $clip['view_count'];
        $duration = $this->parse_duration($clip['duration']);
        
        if ($duration == 0) {
            return 0;
        }
        
        $views_per_second = $views / $duration;
        return round($views_per_second, 2);
    }
    
    /**
     * Parse duration string
     */
    private function parse_duration($duration) {
        // Parse duration format like "1h30m15s"
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        
        if (preg_match('/(\d+)h/', $duration, $matches)) {
            $hours = intval($matches[1]);
        }
        
        if (preg_match('/(\d+)m/', $duration, $matches)) {
            $minutes = intval($matches[1]);
        }
        
        if (preg_match('/(\d+)s/', $duration, $matches)) {
            $seconds = intval($matches[1]);
        }
        
        return $hours * 3600 + $minutes * 60 + $seconds;
    }
    
    /**
     * Combine channel analytics
     */
    private function combine_channel_analytics($analytics_data) {
        $combined = array(
            'total_streams' => 0,
            'total_hours' => 0,
            'total_viewers' => 0,
            'total_followers' => 0,
            'total_bits' => 0,
            'total_videos' => 0,
            'total_clips' => 0,
        );
        
        foreach ($analytics_data as $channel_analytics) {
            $combined['total_streams'] += $channel_analytics['stream_stats']['total_streams'];
            $combined['total_hours'] += $channel_analytics['stream_stats']['total_hours'];
            $combined['total_viewers'] += $channel_analytics['viewer_stats']['average_concurrent_viewers'];
            $combined['total_followers'] += $channel_analytics['growth_stats']['current_followers'];
            $combined['total_bits'] += $channel_analytics['engagement_stats']['total_bits'];
            $combined['total_videos'] += $channel_analytics['content_stats']['total_videos'];
            $combined['total_clips'] += $channel_analytics['content_stats']['total_clips'];
        }
        
        $channel_count = count($analytics_data);
        $combined['average_hours_per_channel'] = $channel_count > 0 ? $combined['total_hours'] / $channel_count : 0;
        $combined['average_viewers_per_channel'] = $channel_count > 0 ? $combined['total_viewers'] / $channel_count : 0;
        
        return $combined;
    }
    
    /**
     * Calculate viewer trend
     */
    private function calculate_viewer_trend($channel, $period) {
        // This would analyze historical data to determine trend
        // For now, return a placeholder
        return 'stable';
    }
    
    /**
     * Calculate follower trend
     */
    private function calculate_follower_trend($channel, $period) {
        // This would analyze historical data to determine trend
        // For now, return a placeholder
        return 'growing';
    }
    
    /**
     * Calculate engagement trend
     */
    private function calculate_engagement_trend($channel, $period) {
        // This would analyze historical data to determine trend
        // For now, return a placeholder
        return 'increasing';
    }
    
    /**
     * Get previous period analytics
     */
    private function get_previous_period_analytics($channel, $period) {
        // This would get analytics from the previous period
        // For now, return a placeholder
        return array(
            'viewer_stats' => array(
                'average_concurrent_viewers' => 0,
            ),
        );
    }
    
    /**
     * Export to CSV
     */
    private function export_to_csv($analytics) {
        $csv = "Channel,Period,Total Streams,Total Hours,Average Viewers,New Followers,Total Bits\n";
        
        $csv .= "{$analytics['channel']},{$analytics['period']},";
        $csv .= "{$analytics['stream_stats']['total_streams']},";
        $csv .= "{$analytics['stream_stats']['total_hours']},";
        $csv .= "{$analytics['viewer_stats']['average_concurrent_viewers']},";
        $csv .= "{$analytics['engagement_stats']['new_followers']},";
        $csv .= "{$analytics['engagement_stats']['total_bits']}\n";
        
        return $csv;
    }
    
    /**
     * Export to XML
     */
    private function export_to_xml($analytics) {
        $xml = new SimpleXMLElement('<analytics/>');
        
        $xml->addChild('channel', $analytics['channel']);
        $xml->addChild('period', $analytics['period']);
        $xml->addChild('generated_at', $analytics['generated_at']);
        
        $stream_stats = $xml->addChild('stream_stats');
        foreach ($analytics['stream_stats'] as $key => $value) {
            $stream_stats->addChild($key, $value);
        }
        
        return $xml->asXML();
    }
}

// Initialize analytics
new WP_Twitch_Analytics();
