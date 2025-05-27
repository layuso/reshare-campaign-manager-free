<?php
namespace ReShare\Core;

/**
 * Handles campaign operations
 */
class Campaign {
    /**
     * Get campaign by ID
     */
    public static function get_campaign($campaign_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaigns';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $campaign_id)
        );
    }

    /**
     * Get campaign posts
     */
    public static function get_campaign_posts($campaign_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaign_posts';
        
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE campaign_id = %d ORDER BY post_order ASC", $campaign_id)
        );
    }

    /**
     * Create or update campaign
     */
    public static function save_campaign($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaigns';
        
        $defaults = [
            'name' => '',
            'status' => 'draft',
            'frequency' => 24,
            'frequency_unit' => 'hours',
            'social_accounts' => '[]'
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        // Sanitize data
        $data['name'] = sanitize_text_field($data['name']);
        $data['status'] = sanitize_text_field($data['status']);
        $data['frequency'] = absint($data['frequency']);
        $data['frequency_unit'] = sanitize_text_field($data['frequency_unit']);
        $data['social_accounts'] = wp_json_encode(array_map('sanitize_text_field', json_decode($data['social_accounts'], true)));

        if (isset($data['id'])) {
            // Update
            $wpdb->update(
                $table,
                $data,
                ['id' => $data['id']],
                ['%s', '%s', '%d', '%s', '%s'],
                ['%d']
            );
            return $data['id'];
        } else {
            // Insert
            $wpdb->insert(
                $table,
                $data,
                ['%s', '%s', '%d', '%s', '%s']
            );
            return $wpdb->insert_id;
        }
    }

    /**
     * Save campaign posts
     */
    public static function save_campaign_posts($campaign_id, $posts) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaign_posts';
        
        // First, remove existing posts
        $wpdb->delete(
            $table,
            ['campaign_id' => $campaign_id],
            ['%d']
        );
        
        // Insert new posts
        foreach ($posts as $order => $post) {
            $wpdb->insert(
                $table,
                [
                    'campaign_id' => $campaign_id,
                    'post_id' => $post['id'],
                    'custom_text' => isset($post['custom_text']) ? $post['custom_text'] : '',
                    'post_order' => $order,
                    'status' => 'pending'
                ],
                ['%d', '%d', '%s', '%d', '%s']
            );
        }
    }

    /**
     * Update campaign status
     */
    public static function update_status($campaign_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaigns';
        
        return $wpdb->update(
            $table,
            ['status' => $status],
            ['id' => $campaign_id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Schedule campaign posts
     */
    public static function schedule_posts($campaign_id) {
        $campaign = self::get_campaign($campaign_id);
        if (!$campaign || $campaign->status !== 'active') {
            return false;
        }

        $posts = self::get_campaign_posts($campaign_id);
        if (empty($posts)) {
            return false;
        }

        $interval = $campaign->frequency * ($campaign->frequency_unit === 'hours' ? HOUR_IN_SECONDS : DAY_IN_SECONDS);
        $start_time = current_time('timestamp');

        foreach ($posts as $post) {
            if ($post->status === 'pending') {
                as_schedule_single_action(
                    $start_time,
                    'reshare_process_campaign_posts',
                    ['campaign_id' => $campaign_id, 'post_id' => $post->id],
                    'reshare'
                );
                $start_time += $interval;
            }
        }

        return true;
    }

    /**
     * Process campaign post
     */
    public static function process_post($campaign_id, $post_id) {
        $campaign = self::get_campaign($campaign_id);
        if (!$campaign || $campaign->status !== 'active') {
            return false;
        }

        // Get post data
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        // Get social accounts
        $social_accounts = json_decode($campaign->social_accounts, true);
        if (empty($social_accounts)) {
            return false;
        }

        // Process each social account
        foreach ($social_accounts as $account) {
            // This will be handled by social media plugins via filters
            $result = apply_filters('reshare_process_social_post', false, $post, $account);
            
            if (!$result) {
                // Log error
                self::log_error($campaign_id, $post_id, "Failed to share post {$post->ID} to account {$account}");
            }
        }

        // Update post status
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_campaign_posts';
        
        $wpdb->update(
            $table,
            [
                'status' => 'completed',
                'shared_time' => current_time('mysql')
            ],
            [
                'campaign_id' => $campaign_id,
                'post_id' => $post_id
            ],
            ['%s', '%s'],
            ['%d', '%d']
        );

        return true;
    }

    /**
     * Log error
     */
    private static function log_error($campaign_id, $post_id, $message) {
        global $wpdb;
        $table = $wpdb->prefix . 'reshare_logs';
        
        $wpdb->insert(
            $table,
            [
                'campaign_id' => $campaign_id,
                'campaign_post_id' => $post_id,
                'type' => 'error',
                'message' => $message
            ],
            ['%d', '%d', '%s', '%s']
        );
    }
} 