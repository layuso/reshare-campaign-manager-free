<?php
// admin-dashboard.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function rcm_render_dashboard_page() {
    ?>
    <div class="wrap">
        <h1>ReShare Campaign Manager</h1>

        <div style="margin-top: 20px;">
            <a href="<?php echo admin_url('admin.php?page=rcm_add_campaign'); ?>" class="button button-primary">Add Campaign</a>
        </div>

        <div style="margin-top: 20px;">
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'rcm_campaigns';

            $campaigns = $wpdb->get_results("
                SELECT * FROM $table_name
                WHERE status IN ('active', 'paused', 'pending')
                ORDER BY date_scheduled ASC
            ");

            if ($campaigns) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Campaign Name</th>
                            <th>Status</th>
                            <th>Date Scheduled</th>
                            <th>Expected Finish Date</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $campaign) : ?>
                            <tr>
                                <td><?php echo esc_html($campaign->campaign_name); ?></td>
                                <td><?php echo esc_html(ucfirst($campaign->status)); ?></td>
                                <td><?php echo esc_html($campaign->date_scheduled); ?></td>
                                <td><?php echo esc_html($campaign->expected_finish_date); ?></td>
                                <td><?php echo esc_html($campaign->created_by); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="notice notice-info">
                    <p>No active or paused campaigns found. Click <strong>Add Campaign</strong> to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

