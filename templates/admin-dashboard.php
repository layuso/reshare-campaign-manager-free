<?php
// admin-dashboard.php

if (!defined('ABSPATH')) {
    exit;
}

// Retrieve campaigns from the database
global $wpdb;
$table_name = $wpdb->prefix . 'reshare_campaigns';

// Fetch campaigns with statuses Active, Paused, or Pending Accounts
$campaigns = $wpdb->get_results("
    SELECT * FROM $table_name
    WHERE status IN ('Active', 'Paused', 'Pending Accounts')
    ORDER BY date_scheduled DESC
");

?>
<div class="wrap">
    <h1>ReShare Campaigns</h1>

    <style>
        .reshare-pending {
            color: orange;
            font-weight: bold;
        }
        .reshare-resume-button {
            margin-left: 10px;
        }
    </style>

    <?php if (empty($campaigns)) : ?>
        <div class="reshare-empty-state" style="background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin-top: 20px; text-align: center;">
            <h2>No Campaigns Found</h2>
            <p>You haven't created any campaigns yet. Start by creating a new campaign!</p>
            <a href="<?php echo admin_url('admin.php?page=reshare_create_campaign'); ?>" class="button button-primary">Create New Campaign</a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col">Campaign Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date Scheduled</th>
                    <th scope="col">Expected Finish Date</th>
                    <th scope="col">Created By</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign) : ?>
                    <tr>
                        <td><?php echo esc_html($campaign->name); ?></td>
                        <td>
                            <?php 
                                if ($campaign->status === 'Pending Accounts') {
                                    echo '<span class="reshare-pending">Pending Accounts</span>';
                                } else {
                                    echo esc_html($campaign->status);
                                }
                            ?>
                        </td>
                        <td><?php echo esc_html($campaign->date_scheduled); ?></td>
                        <td><?php echo esc_html($campaign->expected_finish_date); ?></td>
                        <td><?php echo esc_html($campaign->created_by); ?></td>
                        <td>
                            <?php if ($campaign->status === 'Pending Accounts') : ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="reshare_resume_campaign">
                                    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign->id); ?>">
                                    <?php wp_nonce_field('reshare_resume_campaign_action', 'reshare_resume_campaign_nonce'); ?>
                                    <button type="submit" class="button button-primary reshare-resume-button">Resume</button>
                                </form>
                            <?php else : ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

