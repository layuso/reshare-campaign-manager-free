<?php
// admin-post.php - Handles form submissions and campaign saving for ReShare Campaign Manager

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Debugging helper
function rcm_log($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

// Handle campaign form submission
add_action('admin_post_rcm_save_campaign', 'rcm_handle_save_campaign');

function rcm_handle_save_campaign() {
    if (!isset($_POST['rcm_nonce_field']) || !wp_verify_nonce($_POST['rcm_nonce_field'], 'rcm_save_campaign')) {
        wp_die('Invalid nonce. Please try again.');
    }

    // Debug POST data
    rcm_log('Received POST data:');
    rcm_log($_POST);

    $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
    $campaign_name = sanitize_text_field($_POST['campaign_name']);
    $scheduled_date = sanitize_text_field($_POST['scheduled_date']);
    $finish_date = sanitize_text_field($_POST['finish_date']);
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'Active';

    // Check for connected accounts
    $connected_accounts = get_option('rcm_connected_accounts', []);
    if (empty($connected_accounts)) {
        $status = 'Paused';
        $pending_flag = true;
        rcm_log('No connected accounts found. Campaign will be marked as "Paused" with "Pending Accounts" status.');
    } else {
        $pending_flag = false;
    }

    // Prepare campaign data
    $campaign_data = [
        'campaign_name' => $campaign_name,
        'scheduled_date' => $scheduled_date,
        'finish_date' => $finish_date,
        'status' => $status,
        'created_by' => get_current_user_id(),
        'pending_accounts' => $pending_flag ? true : false,
    ];

    if ($campaign_id > 0) {
        // Update existing campaign
        update_post_meta($campaign_id, '_rcm_campaign_data', $campaign_data);
        rcm_log("Updated campaign ID {$campaign_id}.");
    } else {
        // Create new campaign as a custom post type
        $post_id = wp_insert_post([
            'post_title' => $campaign_name,
            'post_status' => 'publish',
            'post_type' => 'rcm_campaign',
        ]);

        if (is_wp_error($post_id)) {
            rcm_log('Error creating new campaign post: ' . $post_id->get_error_message());
            wp_die('An error occurred while saving the campaign.');
        }

        update_post_meta($post_id, '_rcm_campaign_data', $campaign_data);
        rcm_log("Created new campaign with ID {$post_id}.");
    }

    // Redirect back to the dashboard
    wp_redirect(admin_url('admin.php?page=rcm-dashboard&message=campaign_saved'));
    exit;
}

