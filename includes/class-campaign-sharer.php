<?php
namespace RCM;

if (!defined('ABSPATH')) exit;

class Campaign_Sharer {

    public static function share_post($post_id, $campaign_id) {
        if (!get_post($post_id)) return false;

        $prepend = get_post_meta($campaign_id, '_rcm_global_prepend_text', true);
        $prepend = is_string($prepend) ? $prepend . ' ' : '';

        $post_url = get_permalink($post_id);
        $post_title = get_the_title($post_id);
        $message = $prepend . $post_title . ' ' . $post_url;

        do_action('rcm_pre_share_post', $post_id, $campaign_id, $message);
        self::log_share($campaign_id, $post_id, $message);

        return true;
    }

    private static function log_share($campaign_id, $post_id, $message) {
        $log = get_post_meta($campaign_id, '_rcm_share_log', true);
        if (!is_array($log)) $log = [];

        $log[] = [
            'timestamp' => current_time('mysql'),
            'post_id' => $post_id,
            'message' => $message,
        ];

        update_post_meta($campaign_id, '_rcm_share_log', $log);
    }
}

