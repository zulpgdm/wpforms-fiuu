<?php
namespace WPFormsFiuu\Admin;

defined('ABSPATH') || exit;

class Settings {
    public static function init() {
        add_filter('wpforms_form_settings_panel_content', [__CLASS__, 'add_settings'], 10, 2);
    }

    public static function add_settings($content, $form_data) {
        $enabled = !empty($form_data['settings']['fiuu_enable']) ? '1' : '0';
        $api = !empty($form_data['settings']['fiuu_api']) ? esc_attr($form_data['settings']['fiuu_api']) : '';
        $merchant = !empty($form_data['settings']['fiuu_merchant']) ? esc_attr($form_data['settings']['fiuu_merchant']) : '';

        ob_start();
        ?>
        <div class="wpforms-panel-field">
            <label for="fiuu_enable">Enable Fiuu Payment</label>
            <input type="checkbox" name="settings[fiuu_enable]" value="1" <?php checked('1', $enabled); ?>>
        </div>
        <div class="wpforms-panel-field">
            <label>Fiuu API Key</label>
            <input type="text" name="settings[fiuu_api]" value="<?php echo $api; ?>" />
        </div>
        <div class="wpforms-panel-field">
            <label>Fiuu Merchant ID</label>
            <input type="text" name="settings[fiuu_merchant]" value="<?php echo $merchant; ?>" />
        </div>
        <?php
        $content .= ob_get_clean();
        return $content;
    }
}
