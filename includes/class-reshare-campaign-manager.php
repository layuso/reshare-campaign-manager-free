<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reshare_Campaign_Manager {
    private static $instance = null;
    private $version;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->version = '1.0.1';
        add_action('init', array($this, 'register_post_type'));
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_reshare_campaign_post', array($this, 'process_campaign_post'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => _x('Campaigns', 'post type general name', 'reshare-campaign-manager'),
            'singular_name'      => _x('Campaign', 'post type singular name', 'reshare-campaign-manager'),
            'menu_name'          => _x('ReShare', 'admin menu', 'reshare-campaign-manager'),
            'name_admin_bar'     => _x('Campaign', 'add new on admin bar', 'reshare-campaign-manager'),
            'add_new'           => _x('Add New', 'campaign', 'reshare-campaign-manager'),
            'add_new_item'      => __('Add New Campaign', 'reshare-campaign-manager'),
            'new_item'          => __('New Campaign', 'reshare-campaign-manager'),
            'edit_item'         => __('Edit Campaign', 'reshare-campaign-manager'),
            'view_item'         => __('View Campaign', 'reshare-campaign-manager'),
            'all_items'         => __('All Campaigns', 'reshare-campaign-manager'),
            'search_items'      => __('Search Campaigns', 'reshare-campaign-manager'),
            'not_found'         => __('No campaigns found.', 'reshare-campaign-manager'),
            'not_found_in_trash'=> __('No campaigns found in Trash.', 'reshare-campaign-manager')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'query_var'         => false,
            'capability_type'   => 'post',
            'has_archive'       => false,
            'hierarchical'      => false,
            'menu_position'     => null,
            'supports'          => array('title')
        );

        register_post_type('reshare_campaign', $args);
    }

    public function add_menu_page() {
        add_menu_page(
            __('ReShare Campaign Manager', 'reshare-campaign-manager'),
            __('ReShare', 'reshare-campaign-manager'),
            'manage_options',
            'reshare-campaign-manager',
            array($this, 'render_admin_page'),
            'dashicons-share',
            30
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include plugin_dir_path(dirname(__FILE__)) . 'templates/admin-page.php';
    }

    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_reshare-campaign-manager' !== $hook) {
            return;
        }

        // Debug information
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ReShare: Enqueuing admin scripts');
            error_log('ReShare: Hook - ' . $hook);
        }

        // Enqueue WordPress's built-in jQuery UI and dependencies
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        
        // Enqueue WordPress's jQuery UI CSS
        wp_enqueue_style('wp-jquery-ui-dialog');

        // Enqueue our styles
        wp_enqueue_style(
            'reshare-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css',
            array('wp-jquery-ui-dialog'),
            $this->version . '.' . time()
        );

        // Create and localize the nonce
        $nonce = wp_create_nonce('reshare_ajax_nonce');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ReShare: Generated nonce - ' . $nonce);
        }

        // First, register our script with its dependencies
        wp_register_script(
            'reshare-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-sortable'),
            $this->version . '.' . time(),
            false
        );

        // Then localize before enqueuing
        wp_localize_script(
            'reshare-admin',
            'reshareAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => $nonce,
                'debug' => defined('WP_DEBUG') && WP_DEBUG,
                'version' => $this->version,
                'i18n' => array(
                    'error' => __('Error', 'reshare-campaign-manager'),
                    'success' => __('Success', 'reshare-campaign-manager'),
                    'loading' => __('Loading...', 'reshare-campaign-manager'),
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'reshare-campaign-manager'),
                    'loadingPosts' => __('Loading posts...', 'reshare-campaign-manager'),
                    'errorLoading' => __('Error loading posts. Please try again.', 'reshare-campaign-manager')
                )
            )
        );

        // Finally, enqueue the script
        wp_enqueue_script('reshare-admin');
    }

    public function process_campaign_post($campaign_id) {
        // Get campaign data
        $posts = get_post_meta($campaign_id, '_reshare_posts', true);
        $social_accounts = get_post_meta($campaign_id, '_reshare_social_accounts', true);
        $frequency_value = get_post_meta($campaign_id, '_reshare_frequency_value', true);
        $frequency_unit = get_post_meta($campaign_id, '_reshare_frequency_unit', true);

        if (empty($posts) || empty($social_accounts)) {
            return;
        }

        // Get the current post to share
        $current_index = get_post_meta($campaign_id, '_reshare_current_post_index', true);
        if ($current_index === '') {
            $current_index = 0;
        }

        $post = $posts[$current_index];
        
        // TODO: Implement actual social media sharing here
        // For now, just log the attempt
        error_log(sprintf(
            'Would share post ID %d to social accounts: %s',
            $post['id'],
            implode(', ', $social_accounts)
        ));

        // Update the index for next time
        $next_index = ($current_index + 1) % count($posts);
        update_post_meta($campaign_id, '_reshare_current_post_index', $next_index);

        // Schedule next post
        $interval = 0;
        switch ($frequency_unit) {
            case 'minutes':
                $interval = MINUTE_IN_SECONDS * $frequency_value;
                break;
            case 'hours':
                $interval = HOUR_IN_SECONDS * $frequency_value;
                break;
            case 'days':
                $interval = DAY_IN_SECONDS * $frequency_value;
                break;
            case 'weeks':
                $interval = WEEK_IN_SECONDS * $frequency_value;
                break;
            case 'months':
                $interval = MONTH_IN_SECONDS * $frequency_value;
                break;
        }

        if ($interval > 0) {
            wp_schedule_single_event(time() + $interval, 'reshare_campaign_post', array($campaign_id));
        }
    }
}

// Initialize the plugin
Reshare_Campaign_Manager::get_instance(); 