<?php
namespace ReShare\Core;

/**
 * Handles social media account integrations
 */
class SocialAccounts {
    /**
     * Get all connected social accounts from various plugins
     */
    public static function get_all_accounts() {
        $accounts = [];
        
        // Check for NextScripts SNAP
        if (class_exists('SNAP_Plugin')) {
            $accounts = array_merge($accounts, self::get_snap_accounts());
        }
        
        // Check for Social Networks Auto-Poster
        if (defined('NS_SOCIAL_POSTER_PLUGIN_DIR')) {
            $accounts = array_merge($accounts, self::get_nsp_accounts());
        }
        
        // Allow other plugins to add their accounts
        return apply_filters('reshare_social_accounts', $accounts);
    }

    /**
     * Get accounts from NextScripts SNAP
     */
    private static function get_snap_accounts() {
        $accounts = [];
        
        if (!class_exists('SNAP_Plugin')) {
            return $accounts;
        }

        $nxs_options = get_option('NS_SNAutoPoster');
        if (!empty($nxs_options)) {
            foreach ($nxs_options as $network => $settings) {
                if (is_array($settings)) {
                    foreach ($settings as $account_id => $account) {
                        if (!empty($account['enabled'])) {
                            $accounts[] = [
                                'id' => $network . '_' . $account_id,
                                'name' => !empty($account['name']) ? $account['name'] : $network,
                                'type' => $network,
                                'plugin' => 'snap',
                                'settings' => [
                                    'network' => $network,
                                    'account_id' => $account_id
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $accounts;
    }

    /**
     * Get accounts from Social Networks Auto-Poster
     */
    private static function get_nsp_accounts() {
        $accounts = [];
        
        if (!defined('NS_SOCIAL_POSTER_PLUGIN_DIR')) {
            return $accounts;
        }

        $nsp_options = get_option('nsp_networks');
        if (!empty($nsp_options)) {
            foreach ($nsp_options as $network => $settings) {
                if (!empty($settings['enabled'])) {
                    $accounts[] = [
                        'id' => 'nsp_' . $network,
                        'name' => !empty($settings['name']) ? $settings['name'] : $network,
                        'type' => $network,
                        'plugin' => 'nsp',
                        'settings' => [
                            'network' => $network
                        ]
                    ];
                }
            }
        }

        return $accounts;
    }

    /**
     * Share post to social account
     */
    public static function share_post($post, $account) {
        switch ($account['plugin']) {
            case 'snap':
                return self::share_via_snap($post, $account);
            case 'nsp':
                return self::share_via_nsp($post, $account);
            default:
                return apply_filters('reshare_share_post_' . $account['plugin'], false, $post, $account);
        }
    }

    /**
     * Share post via NextScripts SNAP
     */
    private static function share_via_snap($post, $account) {
        if (!class_exists('SNAP_Plugin')) {
            return false;
        }

        $settings = $account['settings'];
        $network = $settings['network'];
        $account_id = $settings['account_id'];

        // Get post content
        $content = [
            'title' => $post->post_title,
            'text' => wp_strip_all_tags($post->post_content),
            'url' => get_permalink($post->ID),
            'imageURL' => get_the_post_thumbnail_url($post->ID, 'full')
        ];

        // Call SNAP's posting function
        if (function_exists('nxs_doSMAS')) {
            return nxs_doSMAS($network, $account_id, $content);
        }

        return false;
    }

    /**
     * Share post via Social Networks Auto-Poster
     */
    private static function share_via_nsp($post, $account) {
        if (!defined('NS_SOCIAL_POSTER_PLUGIN_DIR')) {
            return false;
        }

        $settings = $account['settings'];
        $network = $settings['network'];

        // Get post content
        $content = [
            'title' => $post->post_title,
            'message' => wp_strip_all_tags($post->post_content),
            'link' => get_permalink($post->ID),
            'image' => get_the_post_thumbnail_url($post->ID, 'full')
        ];

        // Call NSP's posting function
        if (function_exists('nsp_post_to_network')) {
            return nsp_post_to_network($network, $content);
        }

        return false;
    }
} 