<?php
/*
Plugin Name: ReShare Campaign Manager
Description: Create and manage social media resharing campaigns using previously published blog posts.
Version: 1.0.0
Author: Luis Ayuso
Author URI: https://lotuslinktech.com
Text Domain: reshare-campaign-manager
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

define('RCM_PLUGIN_VERSION', '1.0.0');
define('RCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RCM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once RCM_PLUGIN_DIR . 'includes/class-campaign-manager.php';
require_once RCM_PLUGIN_DIR . 'includes/class-campaign-scheduler.php';
require_once RCM_PLUGIN_DIR . 'includes/class-campaign-sharer.php';
require_once RCM_PLUGIN_DIR . 'includes/class-post-filter.php';
require_once RCM_PLUGIN_DIR . 'includes/functions-helpers.php';
require_once RCM_PLUGIN_DIR . 'admin/class-admin-menu.php';
require_once RCM_PLUGIN_DIR . 'admin/class-admin-assets.php';

function rcm_init_plugin() {
    \RCM\Campaign_Manager::init();
    \RCM\Campaign_Scheduler::init();
    if (is_admin()) {
        \RCM\Admin_Menu::init();
        \RCM\Admin_Assets::init();
    }
}
add_action('plugins_loaded', 'rcm_init_plugin');

// 🧩 Activation Hook to Create Table
register_activation_hook(__FILE__, 'rcm_activate_plugin');

function rcm_activate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rcm_campaigns';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        status varchar(20) DEFAULT 'Pending' NOT NULL,
        date_scheduled datetime DEFAULT NULL,
        expected_finish_date datetime DEFAULT NULL,
        created_by bigint(20) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

