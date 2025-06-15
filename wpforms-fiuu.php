<?php
/**
 * Plugin Name: WPForms Fiuu Gateway
 * Description: Adds Fiuu payment gateway support to WPForms.
 * Version: 1.0.0
 * Author: Zulhelmi
 * Text Domain: wpforms-fiuu
 */

defined('ABSPATH') || exit;

// Define plugin constants.
if ( ! defined('WPFORMS_FIUU_VERSION')) {
    define('WPFORMS_FIUU_VERSION', '1.0.0');
}
if ( ! defined('WPFORMS_FIUU_PLUGIN_DIR')) {
    define('WPFORMS_FIUU_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if ( ! defined('WPFORMS_FIUU_PLUGIN_URL')) {
    define('WPFORMS_FIUU_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Autoload classes (optional but recommended for larger plugins).
spl_autoload_register(function ($class) {
    $prefix = 'WPFormsFiuu\\';
    $base_dir = WPFORMS_FIUU_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($class, $prefix, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize the plugin.
add_action('plugins_loaded', ['WPFormsFiuu\\Loader', 'init']);

// Activation and Deactivation hooks (optional, for future enhancements).
register_activation_hook(__FILE__, function() {
    // Perform activation tasks if needed.
});
register_deactivation_hook(__FILE__, function() {
    // Perform deactivation tasks if needed.
});
