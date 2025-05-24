<?php
if (!defined('ABSPATH')) exit;

// Handle saving campaign data
add_action('admin_post_rcm_save_campaign', function() {
    if (!current_user_can('manage_options') || !isset($_POST['rcm_nonce']) || !wp_verify_nonce($_POST['rcm_nonce'], 'rcm_save_campaign')) {
        wp_die(__('Permission denied or invalid nonce.', 'reshare-campaign-manager'));
    }

    $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
    $title = isset($_POST['rcm_title']) ? sanitize_text_field($_POST['rcm_title']) : '';
    $post_ids = isset($_POST['rcm_post_ids']) ? array_map('absint', $_POST['rcm_post_ids']) : [];
    $global_prepend = isset($_POST['rcm_global_prepend']) ? substr(sanitize_text_field($_POST['rcm_global_prepend']), 0, 280) : '';
    $frequency = isset($_POST['rcm_frequency']) ? sanitize_text_field($_POST['rcm_frequency']) : '+1 day';

    if (!$title) {
        wp_die(__('Campaign title is required.', 'reshare-campaign-manager'));
    }

    // Check active campaign limit (free version)
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
        wp_die(__('Free version limit reached: Only 1 active campaign allowed. Upgrade to Pro for unlimited campaigns.', 'reshare-campaign-manager'));
    }

    // Create or update campaign
    if ($campaign_id === 0) {
        $campaign_id = \RCM\Campaign_Manager::create_campaign($title, $post_ids, ['frequency' => $frequency]);
        if (is_wp_error($campaign_id)) {
            wp_die(__('Failed to create campaign. Please try again.', 'reshare-campaign-manager'));
        }
    } else {
        wp_update_post(['ID' => $campaign_id, 'post_title' => $title]);
        update_post_meta($campaign_id, '_rcm_post_ids', $post_ids);
        update_post_meta($campaign_id, '_rcm_global_prepend_text', $global_prepend);
        update_post_meta($campaign_id, '_rcm_frequency', $frequency);
    }

    wp_safe_redirect(admin_url('admin.php?page=rcm-campaigns'));
    exit;
});

// Pause, Resume, Cancel
add_action('admin_post_rcm_pause_campaign', function() {
    if (!current_user_can('manage_options') || !isset($_GET['rcm_nonce']) || !wp_verify_nonce($_GET['rcm_nonce'], 'rcm_action')) {
        wp_die(__('Permission denied or invalid nonce.', 'reshare-campaign-manager'));
    }

    $campaign_id = absint($_GET['campaign_id']);
    \RCM\Campaign_Scheduler::pause_campaign($campaign_id);
    wp_safe_redirect(admin_url('admin.php?page=rcm-campaigns'));
    exit;
});

add_action('admin_post_rcm_resume_campaign', function() {
    if (!current_user_can('manage_options') || !isset($_GET['rcm_nonce']) || !wp_verify_nonce($_GET['rcm_nonce'], 'rcm_action')) {
        wp_die(__('Permission denied or invalid nonce.', 'reshare-campaign-manager'));
    }

    $campaign_id = absint($_GET['campaign_id']);
    \RCM\Campaign_Scheduler::resume_campaign($campaign_id);
    wp_safe_redirect(admin_url('admin.php?page=rcm-campaigns'));
    exit;
});

add_action('admin_post_rcm_cancel_campaign', function() {
    if (!current_user_can('manage_options') || !isset($_GET['rcm_nonce']) || !wp_verify_nonce($_GET['rcm_nonce'], 'rcm_action')) {
        wp_die(__('Permission denied or invalid nonce.', 'reshare-campaign-manager'));
    }

    $campaign_id = absint($_GET['campaign_id']);
    \RCM\Campaign_Scheduler::cancel_campaign($campaign_id);
    wp_safe_redirect(admin_url('admin.php?page=rcm-campaigns'));
    exit;
});

