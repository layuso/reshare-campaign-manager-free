<?php
namespace RCM;

if (!defined('ABSPATH')) exit;

class Admin_Menu {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu_pages']);
    }

    public static function add_menu_pages() {
        add_menu_page(
            __('ReShare Campaigns', 'reshare-campaign-manager'),
            __('ReShare Campaigns', 'reshare-campaign-manager'),
            'manage_options',
            'rcm-campaigns',
            [__CLASS__, 'render_dashboard'],
            'dashicons-share',
            26
        );

        add_submenu_page(
            'rcm-campaigns',
            __('All Campaigns', 'reshare-campaign-manager'),
            __('All Campaigns', 'reshare-campaign-manager'),
            'manage_options',
            'rcm-campaigns',
            [__CLASS__, 'render_dashboard']
        );

        add_submenu_page(
            'rcm-campaigns',
            __('Add New Campaign', 'reshare-campaign-manager'),
            __('Add New', 'reshare-campaign-manager'),
            'manage_options',
            'rcm-campaigns-add',
            [__CLASS__, 'render_add_campaign']
        );
    }

    public static function render_dashboard() {
        include RCM_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    public static function render_add_campaign() {
        include RCM_PLUGIN_DIR . 'templates/campaign-form.php';
    }
}

