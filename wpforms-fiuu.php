<?php
/**
 * Plugin Name: WPForms Fiuu Gateway
 * Description: Adds Fiuu payment gateway support to WPForms.
 * Version: 1.0.0
 * Author: Zulhelmi
 */

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'src/Loader.php';

add_action('plugins_loaded', ['WPFormsFiuu\\Loader', 'init']);

// src/Loader.php
<?php
namespace WPFormsFiuu;

defined('ABSPATH') || exit;

class Loader {
    public static function init() {
        require_once __DIR__ . '/Admin/Settings.php';
        require_once __DIR__ . '/Admin/FormBuilder.php';
        require_once __DIR__ . '/Frontend/Process.php';
        require_once __DIR__ . '/Frontend/Callback.php';
        require_once __DIR__ . '/Helpers/SessionManager.php';

        Admin\Settings::init();
        Admin\FormBuilder::init();
        Frontend\Process::init();
        Frontend\Callback::init();
    }
}
