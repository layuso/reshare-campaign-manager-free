<?php
namespace ReShare\DB;

/**
 * Handles plugin activation tasks
 */
class Activator {
    /**
     * Activate the plugin
     */
    public static function activate() {
        self::create_tables();
        self::maybe_set_version();
    }

    /**
     * Create plugin tables
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Campaigns table
        $table_campaigns = $wpdb->prefix . 'reshare_campaigns';
        $sql_campaigns = "CREATE TABLE IF NOT EXISTS $table_campaigns (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            frequency int(11) NOT NULL DEFAULT 24,
            frequency_unit varchar(10) NOT NULL DEFAULT 'hours',
            social_accounts text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Campaign posts table
        $table_campaign_posts = $wpdb->prefix . 'reshare_campaign_posts';
        $sql_campaign_posts = "CREATE TABLE IF NOT EXISTS $table_campaign_posts (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            custom_text text,
            post_order int(11) NOT NULL DEFAULT 0,
            scheduled_time datetime DEFAULT NULL,
            shared_time datetime DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            PRIMARY KEY  (id),
            KEY campaign_id (campaign_id),
            KEY post_id (post_id),
            KEY status (status),
            KEY scheduled_time (scheduled_time)
        ) $charset_collate;";

        // Campaign logs table
        $table_logs = $wpdb->prefix . 'reshare_logs';
        $sql_logs = "CREATE TABLE IF NOT EXISTS $table_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) unsigned NOT NULL,
            campaign_post_id bigint(20) unsigned DEFAULT NULL,
            type varchar(50) NOT NULL,
            message text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY campaign_id (campaign_id),
            KEY type (type),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Load dbDelta function
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create tables
        dbDelta($sql_campaigns);
        dbDelta($sql_campaign_posts);
        dbDelta($sql_logs);
    }

    /**
     * Set or update plugin version in options
     */
    private static function maybe_set_version() {
        $installed_version = get_option('reshare_version');
        
        if (!$installed_version) {
            add_option('reshare_version', RESHARE_VERSION);
        } elseif ($installed_version !== RESHARE_VERSION) {
            update_option('reshare_version', RESHARE_VERSION);
        }
    }
} 