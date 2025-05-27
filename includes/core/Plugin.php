<?php
namespace ReShare\Core;

/**
 * Main plugin class
 */
class Plugin {
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            
            // AJAX handlers for admin
            add_action('wp_ajax_reshare_get_posts', [$this, 'ajax_get_posts']);
            add_action('wp_ajax_reshare_get_social_accounts', [$this, 'ajax_get_social_accounts']);
            add_action('wp_ajax_reshare_save_campaign', [$this, 'ajax_save_campaign']);
            add_action('wp_ajax_reshare_update_campaign_status', [$this, 'ajax_update_campaign_status']);
        }

        // Initialize Action Scheduler if not already done
        if (!class_exists('ActionScheduler_Versions')) {
            require_once RESHARE_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
        }

        // Schedule hooks
        add_action('reshare_process_campaign_posts', [$this, 'process_campaign_posts']);
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('ReShare Campaign Manager', 'reshare-campaign-manager'),
            __('ReShare', 'reshare-campaign-manager'),
            'manage_options',
            'reshare-campaign-manager',
            [$this, 'render_admin_page'],
            'dashicons-share',
            30
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_reshare-campaign-manager' !== $hook) {
            return;
        }

        // Enqueue WordPress core assets
        wp_enqueue_style('wp-components');
        wp_enqueue_script('wp-element');
        wp_enqueue_script('wp-components');
        wp_enqueue_script('wp-api-fetch');

        // Enqueue plugin assets
        wp_enqueue_style(
            'reshare-admin-style',
            RESHARE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            RESHARE_VERSION
        );

        wp_enqueue_script(
            'reshare-admin-script',
            RESHARE_PLUGIN_URL . 'assets/js/admin.js',
            ['wp-element', 'wp-components', 'wp-api-fetch'],
            RESHARE_VERSION,
            true
        );

        wp_localize_script('reshare-admin-script', 'reshareAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reshare-admin'),
            'isPro' => $this->is_pro_version(),
        ]);
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        require_once RESHARE_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * Check if pro version is active
     */
    private function is_pro_version() {
        return apply_filters('reshare_is_pro_version', false);
    }

    /**
     * AJAX handler for getting posts
     */
    public function ajax_get_posts() {
        check_ajax_referer('reshare-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'paged' => $page,
            's' => $search,
        ];

        $query = new \WP_Query($args);
        $posts = array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'excerpt' => get_the_excerpt($post),
                'permalink' => get_permalink($post),
            ];
        }, $query->posts);

        wp_send_json_success([
            'posts' => $posts,
            'total' => $query->found_posts,
            'totalPages' => $query->max_num_pages,
        ]);
    }

    /**
     * AJAX handler for getting social accounts
     */
    public function ajax_get_social_accounts() {
        check_ajax_referer('reshare-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $accounts = apply_filters('reshare_get_social_accounts', []);
        wp_send_json_success($accounts);
    }

    /**
     * Process scheduled campaign posts
     */
    public function process_campaign_posts($campaign_id) {
        // Implementation will be added in Campaign class
    }
} 