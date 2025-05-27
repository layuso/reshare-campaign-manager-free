# ReShare Campaign Manager

A WordPress plugin for automating and managing social media resharing campaigns for your blog content.

## Description

ReShare Campaign Manager allows you to create and manage campaigns to automatically reshare your WordPress blog posts across your connected social media accounts. The plugin integrates with your existing social media connections in WordPress and doesn't require any additional authentication.

### Features

- Create automated campaigns to reshare your blog posts
- Select posts by searching, filtering by tags, categories, or authors
- Set custom sharing intervals (hourly, daily, or custom days)
- Drag-and-drop interface for reordering posts
- Campaign management dashboard with status tracking
- Integration with existing WordPress social media connections
- No additional social media authentication required

### Free vs Pro Version

#### Free Version
- Single concurrent campaign
- Basic post sharing functionality
- Standard post text sharing

#### Pro Version
- Unlimited concurrent campaigns
- Custom text per post
- Global campaign text customization
- Real-time character count validation

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- At least one social media integration plugin (e.g., NextScripts SNAP, Social Networks Auto-Poster)

## Installation

1. Upload the plugin files to `/wp-content/plugins/reshare-campaign-manager`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin via the 'ReShare' menu item in your WordPress admin

## Usage

1. **Creating a Campaign**
   - Click "Add New Campaign" from the dashboard
   - Select posts to include in your campaign
   - Choose which social accounts to use
   - Set your sharing frequency
   - Review and launch

2. **Managing Campaigns**
   - View all campaigns from the dashboard
   - Pause/resume campaigns as needed
   - Edit campaign settings
   - Monitor campaign progress

3. **Social Media Integration**
   - The plugin automatically detects connected social accounts
   - No additional setup required if you have social media plugins installed

## Supported Social Media Plugins

- NextScripts SNAP
- Social Networks Auto-Poster
- Additional plugins via WordPress filters

## Developer Documentation

### Filters

```php
// Add custom social media accounts
add_filter('reshare_social_accounts', function($accounts) {
    $accounts[] = [
        'id' => 'custom_account_1',
        'name' => 'Custom Account',
        'type' => 'custom',
        'plugin' => 'custom_plugin'
    ];
    return $accounts;
});

// Process custom social media posts
add_filter('reshare_share_post_custom_plugin', function($result, $post, $account) {
    // Custom sharing logic
    return true;
}, 10, 3);
```

### Actions

```php
// Hook into campaign status changes
add_action('reshare_campaign_status_changed', function($campaign_id, $new_status, $old_status) {
    // Custom logic
}, 10, 3);

// Hook into post sharing events
add_action('reshare_before_share_post', function($post, $account) {
    // Pre-share logic
}, 10, 2);

add_action('reshare_after_share_post', function($post, $account, $result) {
    // Post-share logic
}, 10, 3);
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please use the WordPress.org plugin support forums. 