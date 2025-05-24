<?php
namespace RCM;

if (!defined('ABSPATH')) exit;

class Campaign_Manager {

    const POST_TYPE = 'reshare_campaign';

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
    }

    public static function register_post_type() {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name' => __('ReShare Campaigns', 'reshare-campaign-manager'),
                'singular_name' => __('ReShare Campaign', 'reshare-campaign-manager'),
            ],
            'public' => false,
            'show_ui' => false,
            'supports' => ['title', 'custom-fields'],
        ]);
    }

    public static function create_campaign($title, $post_ids = [], $schedule = []) {
        $campaign_id = wp_insert_post([
            'post_type' => self::POST_TYPE,
            'post_title' => sanitize_text_field($title),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($campaign_id)) return $campaign_id;

        update_post_meta($campaign_id, '_rcm_post_ids', $post_ids);
        update_post_meta($campaign_id, '_rcm_schedule', $schedule);
        update_post_meta($campaign_id, '_rcm_status', 'active');

        return $campaign_id;
    }

    public static function get_campaign($campaign_id) {
        $post = get_post($campaign_id);
        if (!$post || $post->post_type !== self::POST_TYPE) return false;

        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'post_ids' => get_post_meta($post->ID, '_rcm_post_ids', true),
            'schedule' => get_post_meta($post->ID, '_rcm_schedule', true),
            'status' => get_post_meta($post->ID, '_rcm_status', true),
        ];
    }

    public static function update_status($campaign_id, $status) {
        return update_post_meta($campaign_id, '_rcm_status', $status);
    }

    public static function delete_campaign($campaign_id) {
        return wp_delete_post($campaign_id, true);
    }
}

