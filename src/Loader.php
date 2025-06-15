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
