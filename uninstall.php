<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all custom campaigns
$campaigns = get_posts([
    'post_type' => 'reshare_campaign',
    'posts_per_page' => -1,
    'fields' => 'ids',
]);

if (!empty($campaigns)) {
    foreach ($campaigns as $campaign_id) {
        wp_delete_post($campaign_id, true); // true = force delete
    }
}

// Clean up any orphaned postmeta (optional, be cautious)
global $wpdb;
$wpdb->query("
    DELETE pm FROM {$wpdb->postmeta} pm
    LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE p.ID IS NULL
");

// Drop the custom campaigns table
$table_name = $wpdb->prefix . 'rcm_campaigns';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// (Optional) Remove plugin options or transients here if added in future
// delete_option('reshare_campaign_manager_settings'); // Example placeholder

