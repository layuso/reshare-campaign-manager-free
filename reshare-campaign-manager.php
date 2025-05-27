<?php
/**
 * Plugin Name: ReShare Campaign Manager
 * Plugin URI: https://wordpress.org/plugins/reshare-campaign-manager
 * Description: Automate and manage social media resharing campaigns for your WordPress content
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: reshare-campaign-manager
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RESHARE_VERSION', '1.0.0');
define('RESHARE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESHARE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RESHARE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    // Plugin namespace prefix
    $prefix = 'ReShare\\';
    $base_dir = RESHARE_PLUGIN_DIR . 'includes/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function reshare_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('reshare-campaign-manager', false, dirname(RESHARE_PLUGIN_BASENAME) . '/languages');
    
    // Initialize core plugin class
    \ReShare\Core\Plugin::get_instance();
}
add_action('plugins_loaded', 'reshare_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    require_once RESHARE_PLUGIN_DIR . 'includes/db/Activator.php';
    \ReShare\DB\Activator::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    require_once RESHARE_PLUGIN_DIR . 'includes/db/Deactivator.php';
    \ReShare\DB\Deactivator::deactivate();
});

// Register uninstall hook
register_uninstall_hook(__FILE__, function() {
    require_once RESHARE_PLUGIN_DIR . 'includes/db/Uninstaller.php';
    \ReShare\DB\Uninstaller::uninstall();
}); 