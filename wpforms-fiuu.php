<?php
/**
 * Plugin Name: WPForms FIUU Integration
 * Description: Adds FIUU payment integration to WPForms.
 * Version: 1.0
 * Author: ZulPGDM
 */

defined('ABSPATH') || exit;

// Autoload plugin classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'WPFormsFiuu\\') !== 0) return;

    $class_path = str_replace(['WPFormsFiuu\\', '\\'], ['', '/'], $class);
    $file = plugin_dir_path(__FILE__) . 'src/' . $class_path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize plugin components
add_action('plugins_loaded', function () {
    if (!function_exists('wpforms')) return;

    \WPFormsFiuu\Admin\Settings::init();
    \WPFormsFiuu\Frontend\SubmissionHandler::init();
    \WPFormsFiuu\Frontend\ReturnHandler::init();
});
