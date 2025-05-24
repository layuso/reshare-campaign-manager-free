<?php
// admin-post.php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_reshare_save_campaign', 'reshare_handle_save_campaign');
add_action('admin_post_reshare_resume_campaign', 'reshare_handle_resume_campaign');

function reshare_handle_save_campaign() {
    if (!isset($_POST['reshare_save_campaign_nonce']) || !wp_verify_nonce($_POST['reshare_save_campaign_nonce'], 'reshare_save_campaign_action')) {
        wp_die('Security check failed.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'reshare_campaigns';

    $name = sanitize_text_field($_POST['campaign_name']);
    $date_scheduled = sanitize_text_field($_POST['date_scheduled']);
    $expected_finish_date = sanitize_text_field($_POST['expected_finish_date']);
    $created_by = sanitize_text_field($_POST['created_by']);

    // Determine status based on accounts
    $accounts_available = get_option('reshare_connected_accounts', []);
    $status = (!empty($accounts_available)) ? 'Active' : 'Pending Accounts';

    // Check if this is a new campaign or an update
    $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;

    if ($campaign_id > 0) {
        // Update existing campaign
        $wpdb->update(
            $table_name,
            [
                'name' => $name,
                'date_scheduled' => $date_scheduled,
                'expected_finish_date' => $expected_finish_date,
                'status' => $status,
            ],
            ['id' => $campaign_id]
        );
    } else {
        // Insert new campaign
        $wpdb->insert(
            $table_name,
            [
                'name' => $name,
                'date_scheduled' => $date_scheduled,
                'expected_finish_date' => $expected_finish_date,
                'created_by' => $created_by,
                'status' => $status,
            ]
        );
    }

    error_log('ReShare Debug: Save Campaign - ' . print_r($_REQUEST, true));

    wp_redirect(admin_url('admin.php?page=reshare_campaigns'));
    exit;
}

function reshare_handle_resume_campaign() {
    if (!isset($_POST['reshare_resume_campaign_nonce']) || !wp_verify_nonce($_POST['reshare_resume_campaign_nonce'], 'reshare_resume_campaign_action')) {
        wp_die('Security check failed.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'reshare_campaigns';
    $campaign_id = intval($_POST['campaign_id']);

    // Check if accounts are available
    $accounts_available = get_option('reshare_connected_accounts', []);

    if (!empty($accounts_available)) {
        $wpdb->update(
            $table_name,
            ['status' => 'Active'],
            ['id' => $campaign_id]
        );
    } else {
        wp_die('No accounts available to resume the campaign. Please add accounts first.');
    }

    error_log('ReShare Debug: Resume Campaign - ' . print_r($_REQUEST, true));

    wp_redirect(admin_url('admin.php?page=reshare_campaigns'));
    exit;
}

