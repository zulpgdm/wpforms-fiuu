<?php
namespace WPFormsFiuu\Admin;

defined('ABSPATH') || exit;

class Settings {
    public static function init() {
        add_filter('wpforms_builder_settings_sections', [__CLASS__, 'add_settings_section'], 20, 1); // <--- adds new section tab
        add_filter('wpforms_form_settings_panel_content', [__CLASS__, 'render_fiuu_settings'], 20, 1); // <--- FIXED: render content for custom section
        add_filter('wpforms_form_settings_defaults', [__CLASS__, 'register_fiuu_defaults'], 20, 1); // <--- ADDED: register defaults so settings are saved
    }

    public static function add_settings_section($sections) {
        $sections['fiuu'] = __('FIUU Integration', 'wpforms-fiuu');
        return $sections;
    }

    public static function render_fiuu_settings($instance) {
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-fiuu">';
        echo '<div class="wpforms-panel-content-section-title">' . __('Fiuu Payment Settings', 'wpforms-fiuu') . '</div>';

        \wpforms_panel_field(
            'checkbox',              // type
            'settings',              // settings section
            'fiuu_enable',           // setting key
            $instance->form_data,    // form data
            __('Enable Fiuu Payment', 'wpforms-fiuu')
        );

        \wpforms_panel_field(
            'text',
            'settings',
            'fiuu_api',
            $instance->form_data,
            __('Fiuu API Key', 'wpforms-fiuu')
        );

        \wpforms_panel_field(
            'text',
            'settings',
            'fiuu_merchant',
            $instance->form_data,
            __('Fiuu Merchant ID', 'wpforms-fiuu')
        );

        \wpforms_panel_field(
            'text', // <--- changed from 'number' to 'text' so it renders properly
            'settings',
            'fiuu_amount',
            $instance->form_data,
            __('Amount (USD)', 'wpforms-fiuu')
        );

        echo '</div>';
    }

    public static function register_fiuu_defaults($defaults) { // <--- ADDED METHOD
        $defaults['fiuu_enable'] = false;
        $defaults['fiuu_api'] = '';
        $defaults['fiuu_merchant'] = '';
        $defaults['fiuu_amount'] = '';
        return $defaults;
    }
}
