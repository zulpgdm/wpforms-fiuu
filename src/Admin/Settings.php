<?php
namespace WPFormsFiuu\Admin;

defined('ABSPATH') || exit;

/**
 * Handles Fiuu payment gateway settings within WPForms form builder.
 */
class Settings {

    /**
     * Initialize settings hooks.
     */
    public static function init() {
        add_filter('wpforms_form_settings_panel_content', [__CLASS__, 'add_settings'], 10, 2);
        // If you need to save settings beyond what WPForms does automatically:
        // add_filter('wpforms_save_form_settings', [__CLASS__, 'save_settings'], 10, 3);
    }

    /**
     * Adds the Fiuu payment gateway settings to the WPForms form settings panel.
     *
     * @param string $content   Current panel content.
     * @param array  $form_data Form data.
     * @return string Modified panel content.
     */
    public static function add_settings($content, $form_data) {
        $enabled  = ! empty($form_data['settings']['fiuu_enable']) ? '1' : '0';
        $api      = ! empty($form_data['settings']['fiuu_api']) ? esc_attr($form_data['settings']['fiuu_api']) : '';
        $merchant = ! empty($form_data['settings']['fiuu_merchant']) ? esc_attr($form_data['settings']['fiuu_merchant']) : '';

        ob_start();
        ?>
        <div class="wpforms-panel-content-section wpforms-panel-content-section-fiuu" id="wpforms-panel-section-fiuu">
            <div class="wpforms-panel-field">
                <label for="wpforms-field-fiuu_enable" class="wpforms-field-label">
                    <?php esc_html_e('Enable Fiuu Payment', 'wpforms-fiuu'); ?>
                </label>
                <input type="checkbox" id="wpforms-field-fiuu_enable" name="settings[fiuu_enable]" value="1" <?php checked('1', $enabled); ?>>
                <p class="description"><?php esc_html_e('Check this box to enable Fiuu payment for this form.', 'wpforms-fiuu'); ?></p>
            </div>

            <div class="wpforms-panel-field">
                <label for="wpforms-field-fiuu_api" class="wpforms-field-label">
                    <?php esc_html_e('Fiuu API Key', 'wpforms-fiuu'); ?>
                </label>
                <input type="text" id="wpforms-field-fiuu_api" name="settings[fiuu_api]" value="<?php echo $api; ?>" class="regular-text" placeholder="<?php esc_attr_e('Enter your Fiuu API Key', 'wpforms-fiuu'); ?>" />
                <p class="description"><?php esc_html_e('Enter your Fiuu API Key here. You can find this in your Fiuu merchant dashboard.', 'wpforms-fiuu'); ?></p>
            </div>

            <div class="wpforms-panel-field">
                <label for="wpforms-field-fiuu_merchant" class="wpforms-field-label">
                    <?php esc_html_e('Fiuu Merchant ID', 'wpforms-fiuu'); ?>
                </label>
                <input type="text" id="wpforms-field-fiuu_merchant" name="settings[fiuu_merchant]" value="<?php echo $merchant; ?>" class="regular-text" placeholder="<?php esc_attr_e('Enter your Fiuu Merchant ID', 'wpforms-fiuu'); ?>" />
                <p class="description"><?php esc_html_e('Enter your Fiuu Merchant ID here. You can find this in your Fiuu merchant dashboard.', 'wpforms-fiuu'); ?></p>
            </div>
        </div>
        <?php
        $content .= ob_get_clean();
        return $content;
    }

    /**
     * Placeholder for saving custom settings if WPForms' default saving isn't sufficient.
     *
     * @param array $settings Form settings.
     * @param array $form_data Form data.
     * @param int   $form_id Form ID.
     * @return array Modified form settings.
     */
    // public static function save_settings($settings, $form_data, $form_id) {
    //     // Example: Manual saving if needed
    //     // if (isset($_POST['settings']['fiuu_enable'])) {
    //     //     $settings['fiuu_enable'] = sanitize_text_field($_POST['settings']['fiuu_enable']);
    //     // }
    //     return $settings;
    // }
}
