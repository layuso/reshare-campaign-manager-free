<?php
if (!defined('ABSPATH')) exit;

use RCM\Campaign_Manager;

echo '<div class="wrap">';
echo '<h1>' . esc_html__('ReShare Campaigns', 'reshare-campaign-manager') . '</h1>';

$campaigns = get_posts([
    'post_type' => Campaign_Manager::POST_TYPE,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);

// Filter active and paused campaigns
$filtered_campaigns = array_filter($campaigns, function($c) {
    $status = get_post_meta($c->ID, '_rcm_status', true);
    return in_array($status, ['active', 'paused']);
});

if (empty($filtered_campaigns)) {
    echo '<div style="border: 2px dashed #ccc; padding: 40px; text-align: center; margin-top: 20px;">
        <h2>' . esc_html__('No Active Campaigns', 'reshare-campaign-manager') . '</h2>
        <p>' . esc_html__('Click "Add New" to create your first campaign!', 'reshare-campaign-manager') . '</p>
        <a href="' . admin_url('admin.php?page=rcm-campaigns-add') . '" class="button button-primary">' . esc_html__('Add New Campaign', 'reshare-campaign-manager') . '</a>
    </div>';
} else {
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>
        <th>' . esc_html__('Name', 'reshare-campaign-manager') . '</th>
        <th>' . esc_html__('Status', 'reshare-campaign-manager') . '</th>
        <th>' . esc_html__('Date Scheduled', 'reshare-campaign-manager') . '</th>
        <th>' . esc_html__('Expected Finish Date', 'reshare-campaign-manager') . '</th>
        <th>' . esc_html__('Created By', 'reshare-campaign-manager') . '</th>
        <th>' . esc_html__('Actions', 'reshare-campaign-manager') . '</th>
    </tr></thead>';
    echo '<tbody>';

    foreach ($filtered_campaigns as $campaign) {
        $status = get_post_meta($campaign->ID, '_rcm_status', true);
        $scheduled = get_the_date('Y-m-d H:i', $campaign->ID);
        $created_by = get_the_author_meta('display_name', $campaign->post_author);

        $post_ids = get_post_meta($campaign->ID, '_rcm_post_ids', true);
        $post_count = is_array($post_ids) ? count($post_ids) : 0;
        $frequency = get_post_meta($campaign->ID, '_rcm_frequency', true);
        $start_timestamp = strtotime($scheduled);
        $expected_finish = $start_timestamp + ($post_count * strtotime($frequency, 0));
        $expected_finish_formatted = $post_count > 0 ? date('Y-m-d H:i', $expected_finish) : '-';

        echo '<tr>';
        echo '<td>' . esc_html($campaign->post_title) . '</td>';
        echo '<td>' . esc_html(ucfirst($status)) . '</td>';
        echo '<td>' . esc_html($scheduled) . '</td>';
        echo '<td>' . esc_html($expected_finish_formatted) . '</td>';
        echo '<td>' . esc_html($created_by) . '</td>';
        echo '<td>';
        echo '<a href="' . admin_url('admin.php?page=rcm-campaigns-add&campaign_id=' . $campaign->ID) . '" class="button button-small">' . esc_html__('Edit', 'reshare-campaign-manager') . '</a> ';
        echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=rcm_pause_campaign&campaign_id=' . $campaign->ID), 'rcm_action', 'rcm_nonce') . '" class="button button-small">' . esc_html__('Pause', 'reshare-campaign-manager') . '</a> ';
        echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=rcm_resume_campaign&campaign_id=' . $campaign->ID), 'rcm_action', 'rcm_nonce') . '" class="button button-small">' . esc_html__('Resume', 'reshare-campaign-manager') . '</a> ';
        echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=rcm_cancel_campaign&campaign_id=' . $campaign->ID), 'rcm_action', 'rcm_nonce') . '" class="button button-small">' . esc_html__('Cancel', 'reshare-campaign-manager') . '</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

echo '</div>';

