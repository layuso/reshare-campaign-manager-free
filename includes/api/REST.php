<?php
namespace ReShare\API;

use ReShare\Core\Campaign;
use ReShare\Core\SocialAccounts;

/**
 * Handles REST API endpoints
 */
class REST {
    /**
     * Register REST routes
     */
    public static function register_routes() {
        register_rest_route('wp/v2/reshare', '/campaigns', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'get_campaigns'],
                'permission_callback' => [self::class, 'check_admin_permissions'],
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'create_campaign'],
                'permission_callback' => [self::class, 'check_admin_permissions'],
            ],
        ]);

        register_rest_route('wp/v2/reshare', '/campaigns/(?P<id>\d+)/status', [
            'methods' => 'POST',
            'callback' => [self::class, 'update_campaign_status'],
            'permission_callback' => [self::class, 'check_admin_permissions'],
            'args' => [
                'status' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['active', 'paused', 'cancelled'],
                ],
            ],
        ]);

        register_rest_route('wp/v2/reshare', '/social-accounts', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_social_accounts'],
            'permission_callback' => [self::class, 'check_admin_permissions'],
        ]);
    }

    /**
     * Check if user has admin permissions
     */
    public static function check_admin_permissions() {
        return current_user_can('manage_options');
    }

    /**
     * Get all campaigns
     */
    public static function get_campaigns(\WP_REST_Request $request) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaigns';
        
        $campaigns = $wpdb->get_results("
            SELECT c.*, COUNT(cp.id) as post_count 
            FROM {$table} c 
            LEFT JOIN {$wpdb->prefix}reshare_campaign_posts cp ON c.id = cp.campaign_id 
            GROUP BY c.id 
            ORDER BY c.created_at DESC
        ");

        return rest_ensure_response($campaigns);
    }

    /**
     * Create a new campaign
     */
    public static function create_campaign(\WP_REST_Request $request) {
        $params = $request->get_params();
        
        // Validate required fields
        if (empty($params['name']) || empty($params['posts'])) {
            return new \WP_Error(
                'missing_fields',
                'Required fields are missing',
                ['status' => 400]
            );
        }

        // Create campaign
        $campaign_id = Campaign::save_campaign([
            'name' => sanitize_text_field($params['name']),
            'frequency' => absint($params['frequency']),
            'frequency_unit' => sanitize_text_field($params['frequency_unit']),
            'social_accounts' => wp_json_encode(array_map('sanitize_text_field', $params['social_accounts'])),
            'status' => empty($params['social_accounts']) ? 'pending' : 'active'
        ]);

        if (!$campaign_id) {
            return new \WP_Error(
                'campaign_creation_failed',
                'Failed to create campaign',
                ['status' => 500]
            );
        }

        // Save campaign posts
        Campaign::save_campaign_posts($campaign_id, $params['posts']);

        // Schedule posts if campaign is active
        if (!empty($params['social_accounts'])) {
            Campaign::schedule_posts($campaign_id);
        }

        return rest_ensure_response([
            'id' => $campaign_id,
            'message' => 'Campaign created successfully'
        ]);
    }

    /**
     * Update campaign status
     */
    public static function update_campaign_status(\WP_REST_Request $request) {
        $campaign_id = $request['id'];
        $new_status = $request['status'];

        $result = Campaign::update_status($campaign_id, $new_status);
        
        if (false === $result) {
            return new \WP_Error(
                'status_update_failed',
                'Failed to update campaign status',
                ['status' => 500]
            );
        }

        // Handle status-specific actions
        switch ($new_status) {
            case 'active':
                Campaign::schedule_posts($campaign_id);
                break;
            case 'paused':
            case 'cancelled':
                // Unschedule future posts
                if (class_exists('ActionScheduler')) {
                    as_unschedule_action('reshare_process_campaign_posts', ['campaign_id' => $campaign_id], 'reshare');
                }
                break;
        }

        return rest_ensure_response([
            'message' => 'Campaign status updated successfully'
        ]);
    }

    /**
     * Get all social accounts
     */
    public static function get_social_accounts() {
        return rest_ensure_response(SocialAccounts::get_all_accounts());
    }
} 