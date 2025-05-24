<?php
// In your main plugin file or wherever you hook admin-post actions:
add_action('admin_post_save_campaign', 'reshare_handle_save_campaign');

function reshare_handle_save_campaign() {
    // Start debugging logs
    error_log("Reshare: save_campaign action triggered");

    // Debug raw input data
    error_log("Reshare: _POST data -> " . print_r($_POST, true));
    error_log("Reshare: _REQUEST data -> " . print_r($_REQUEST, true));

    // Check for required fields
    $required_fields = ['campaign_name', 'campaign_status', 'date_scheduled', 'expected_finish_date'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        error_log("Reshare: Missing required fields -> " . implode(", ", $missing_fields));
        wp_die("Missing required fields: " . implode(", ", $missing_fields));
    }

    // Sanitize and assign fields
    $campaign_name = sanitize_text_field($_POST['campaign_name']);
    $campaign_status = sanitize_text_field($_POST['campaign_status']);
    $date_scheduled = sanitize_text_field($_POST['date_scheduled']);
    $expected_finish_date = sanitize_text_field($_POST['expected_finish_date']);

    // Fallback: Get current user
    $created_by = get_current_user_id();
    if (!$created_by) {
        error_log("Reshare: Failed to get current user ID");
        wp_die('Failed to get current user ID');
    }

    // Insert the campaign
    global $wpdb;
    $table_name = $wpdb->prefix . 'reshare_campaigns';

    $inserted = $wpdb->insert(
        $table_name,
        [
            'campaign_name' => $campaign_name,
            'campaign_status' => $campaign_status,
            'date_scheduled' => $date_scheduled,
            'expected_finish_date' => $expected_finish_date,
            'created_by' => $created_by,
            'created_at' => current_time('mysql')
        ]
    );

    if ($inserted === false) {
        error_log("Reshare: DB Insert failed. Last error: " . $wpdb->last_error);
        wp_die('Failed to insert campaign. DB Error: ' . $wpdb->last_error);
    }

    error_log("Reshare: Campaign inserted successfully. ID: " . $wpdb->insert_id);

    // Redirect after success
    wp_redirect(admin_url('admin.php?page=reshare-campaigns&message=success'));
    exit;
}

