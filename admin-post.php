<?php
if (!defined('ABSPATH')) {
    wp_die('Direct access not allowed.');
}

// Debug log helper (optional, still useful)
function rcm_log($message) {
    if (WP_DEBUG === true) {
        error_log('[RCM] ' . $message);
    }
}

// Handle saving campaign data (DEBUG VERSION)
add_action('admin_post_rcm_save_campaign', function() {
    // DUMP $_REQUEST FOR DEBUGGING
    wp_die('<pre>' . print_r($_REQUEST, true) . '</pre>');

    try {
        rcm_log('Entered rcm_save_campaign.');

        if (!current_user_can('manage_options')) {
            throw new Exception(__('Permission denied.', 'reshare-campaign-manager'));
        }

        if (!isset($_POST['rcm_nonce']) || !wp_verify_nonce($_POST['rcm_nonce'], 'rcm_save_campaign')) {
            throw new Exception(__('Invalid request.', 'reshare-campaign-manager'));
        }

        $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
        $title = isset($_POST['rcm_title']) ? sanitize_text_field($_POST['rcm_title']) : '';
        $post_ids = isset($_POST['rcm_post_ids']) ? array_map('absint', $_POST['rcm_post_ids']) : [];
        $global_prepend = isset($_POST['rcm_global_prepend']) ? substr(sanitize_text_field($_POST['rcm_global_prepend']), 0, 280) : '';
        $frequency = isset($_POST['rcm_frequency']) ? sanitize_text_field($_POST['rcm_frequency']) : '+1 day';

        if (!$title) {
            throw new Exception(__('Campaign title is required.', 'reshare-campaign-manager'));
        }

        $active_campaigns = get_posts([
            'post_type' => \RCM\Campaign_Manager::POST_TYPE,
            'post_status' => 'publish',
            'meta_query' => [[
                'key' => '_rcm_status',
                'value' => 'active',
                'compare' => '='
            ]],
            'fields' => 'ids'
        ]);

        if ($campaign_id === 0 && count($active_campaigns) >= 1) {
            throw new Exception(__('Free version limit reached: Only 1 active campaign allowed. Upgrade to Pro for unlimited campaigns.', 'reshare-campaign-manager'));
        }

        if ($campaign_id === 0) {
            $campaign_id = \RCM\Campaign_Manager::create_campaign($title, $post_ids, ['frequency' => $frequency]);
            if (is_wp_error($campaign_id)) {
                throw new Exception(__('Failed to create campaign. Please try again.', 'reshare-campaign-manager'));
            }
        } else {
            wp_update_post(['ID' => $campaign_id, 'post_title' => $title]);
            update_post_meta($campaign_id, '_rcm_post_ids', $post_ids);
            update_post_meta($campaign_id, '_rcm_global_prepend_text', $global_prepend);
            update_post_meta($campaign_id, '_rcm_frequency', $frequency);
        }

        rcm_log('Campaign saved successfully. ID: ' . $campaign_id);
        wp_safe_redirect(admin_url('admin.php?page=rcm-campaigns'));
        exit;

    } catch (Exception $e) {
        rcm_log('Error in rcm_save_campaign: ' . $e->getMessage());
        wp_die($e->getMessage());
    }
});

// Other handlers (pause, resume, cancel) remain unchanged
// You can keep them as is unless you want the same dump logic added for testing.

