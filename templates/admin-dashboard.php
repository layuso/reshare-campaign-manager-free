<?php
if (!defined('ABSPATH')) exit;

echo '<div class="wrap">';
echo '<h1>' . esc_html__('ReShare Campaigns', 'reshare-campaign-manager') . '</h1>';

$campaigns = get_posts([
    'post_type' => \RCM\Campaign_Manager::POST_TYPE,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);

if (empty($campaigns)) {
    echo '<p>' . esc_html__('No campaigns found.', 'reshare-campaign-manager') . '</p>';
    echo '<a href="' . admin_url('admin.php?page=rcm-campaigns-add') . '" class="button button-primary">' . esc_html__('Create New Campaign', 'reshare-campaign-manager') . '</a>';
} else {
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>' . esc_html__('Title', 'reshare-campaign-manager') . '</th><th>' . esc_html__('Status', 'reshare-campaign-manager') . '</th><th>' . esc_html__('Next Share', 'reshare-campaign-manager') . '</th><th>' . esc_html__('Actions', 'reshare-campaign-manager') . '</th></tr></thead>';
    echo '<tbody>';

    foreach ($campaigns as $campaign) {
        $status = get_post_meta($campaign->ID, '_rcm_status', true);
        $next_share = get_post_meta($campaign->ID, '_rcm_next_share_time', true);
        $next_share = $next_share ? date('Y-m-d H:i', $next_share) : '-';

        echo '<tr>';
        echo '<td>' . esc_html($campaign->post_title) . '</td>';
        echo '<td>' . esc_html(ucfirst($status)) . '</td>';
        echo '<td>' . esc_html($next_share) . '</td>';
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

