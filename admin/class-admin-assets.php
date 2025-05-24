<?php
namespace RCM;

if (!defined('ABSPATH')) exit;

class Admin_Assets {

    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets($hook) {
        if (!isset($_GET['page']) || !in_array($_GET['page'], ['rcm-campaigns', 'rcm-campaigns-add'])) return;

        wp_enqueue_style(
            'rcm-admin-style',
            RCM_PLUGIN_URL . 'assets/admin-style.css',
            [],
            RCM_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'rcm-admin-scripts',
            RCM_PLUGIN_URL . 'assets/admin-scripts.js',
            ['jquery'],
            RCM_PLUGIN_VERSION,
            true
        );

        wp_localize_script('rcm-admin-scripts', 'RCM_Admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rcm_admin_nonce'),
        ]);
    }
}

