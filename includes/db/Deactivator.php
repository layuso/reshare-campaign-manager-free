<?php
namespace ReShare\DB;

/**
 * Handles plugin deactivation tasks
 */
class Deactivator {
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear all scheduled actions
        if (class_exists('ActionScheduler')) {
            $actions = as_get_scheduled_actions([
                'hook' => 'reshare_process_campaign_posts',
                'status' => \ActionScheduler_Store::STATUS_PENDING,
            ]);

            foreach ($actions as $action_id) {
                as_unschedule_action('reshare_process_campaign_posts', [], 'reshare');
            }
        }

        // Update all active campaigns to paused
        global $wpdb;
        $table_campaigns = $wpdb->prefix . 'reshare_campaigns';
        
        $wpdb->update(
            $table_campaigns,
            ['status' => 'paused'],
            ['status' => 'active'],
            ['%s'],
            ['%s']
        );
    }
} 