<?php
namespace ReShare\DB;

/**
 * Handles plugin uninstallation tasks
 */
class Uninstaller {
    /**
     * Uninstall the plugin
     */
    public static function uninstall() {
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            exit;
        }

        // Clear all scheduled actions
        if (class_exists('ActionScheduler')) {
            as_unschedule_all_actions('reshare_process_campaign_posts', [], 'reshare');
        }

        // Remove database tables
        global $wpdb;
        $tables = [
            $wpdb->prefix . 'reshare_campaigns',
            $wpdb->prefix . 'reshare_campaign_posts',
            $wpdb->prefix . 'reshare_logs'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        // Remove plugin options
        delete_option('reshare_version');
        
        // Remove any other plugin-specific options
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'reshare_%'");
        
        // Clear any transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_reshare_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_reshare_%'");
    }
} 