<?php
namespace WPFormsFiuu;

defined('ABSPATH') || exit;

use WPFormsFiuu\Admin\Settings;
use WPFormsFiuu\Admin\FormBuilder;
use WPFormsFiuu\Frontend\Process;
use WPFormsFiuu\Frontend\Callback;
use WPFormsFiuu\Helpers\SessionManager; // If SessionManager will be used

/**
 * Main plugin loader class.
 */
class Loader {

    /**
     * Initialize all necessary classes.
     */
    public static function init() {
        self::load_dependencies();
        self::register_hooks();
    }

    /**
     * Load core dependencies.
     * While autoloading handles `require_once`, explicitly calling
     * the init methods ensures the hooks are registered.
     */
    private static function load_dependencies() {
        // Classes are autoloaded, so we just need to ensure their static init methods are called.
    }

    /**
     * Register all WordPress hooks.
     */
    private static function register_hooks() {
        Settings::init();
        FormBuilder::init(); // Even if empty, keeps the structure consistent.
        Process::init();
        Callback::init();
        SessionManager::init(); // Call init if SessionManager has static methods to initialize.
    }
}
