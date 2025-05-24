<?php
namespace RCM;

if (!defined('ABSPATH')) exit;

class Campaign_Scheduler {

    public static function init() {
        add_action('rcm_share_next_post', [__CLASS__, 'handle_share'], 10, 1);
    }

    public static function schedule_campaign($campaign_id, $start_time = null, $frequency = '+1 day') {
        if (!$start_time) $start_time = time();
        update_post_meta($campaign_id, '_rcm_next_share_time', $start_time);
        update_post_meta($campaign_id, '_rcm_frequency', $frequency);
        as_schedule_single_action($start_time, 'rcm_share_next_post', [$campaign_id], 'rcm');
    }

    public static function handle_share($campaign_id) {
        $campaign = Campaign_Manager::get_campaign($campaign_id);
        if (!$campaign || $campaign['status'] !== 'active') return;

        $post_ids = $campaign['post_ids'];
        $index = (int) get_post_meta($campaign_id, '_rcm_current_index', true);
        $frequency = get_post_meta($campaign_id, '_rcm_frequency', true);

        if (!isset($post_ids[$index])) {
            Campaign_Manager::update_status($campaign_id, 'completed');
            return;
        }

        Campaign_Sharer::share_post($post_ids[$index], $campaign_id);
        update_post_meta($campaign_id, '_rcm_current_index', $index + 1);

        $next_time = strtotime($frequency, time());
        update_post_meta($campaign_id, '_rcm_next_share_time', $next_time);
        as_schedule_single_action($next_time, 'rcm_share_next_post', [$campaign_id], 'rcm');
    }

    public static function pause_campaign($campaign_id) {
        as_unschedule_all_actions('rcm_share_next_post', [$campaign_id], 'rcm');
        Campaign_Manager::update_status($campaign_id, 'paused');
    }

    public static function resume_campaign($campaign_id) {
        $frequency = get_post_meta($campaign_id, '_rcm_frequency', true);
        self::schedule_campaign($campaign_id, time(), $frequency);
        Campaign_Manager::update_status($campaign_id, 'active');
    }

    public static function cancel_campaign($campaign_id) {
        self::pause_campaign($campaign_id);
        Campaign_Manager::update_status($campaign_id, 'cancelled');
    }
}

