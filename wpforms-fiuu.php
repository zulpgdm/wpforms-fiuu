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
