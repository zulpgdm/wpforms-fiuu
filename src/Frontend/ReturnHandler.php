<?php
namespace WPFormsFiuu\Frontend;

defined('ABSPATH') || exit;

class ReturnHandler {
    public static function init() {
        add_action('init', [__CLASS__, 'maybe_handle_return']);
    }

    public static function maybe_handle_return() {
        if (!isset($_GET['fiuu_return'], $_GET['ref'])) return;

        $ref = sanitize_text_field($_GET['ref']);
        $data = get_transient($ref);
        if (!$data) return;

        delete_transient($ref);

        // Here you'd normally verify the Fiuu payment success
        // Example: send curl request to Fiuu to verify transaction

        // Save entry manually
        if (!empty($data['fields']) && !empty($data['form_data'])) {
            wpforms()->process->entry_save($data['fields'], $data['form_data']);
        }

        wp_redirect(home_url('/thank-you'));
        exit;
    }
}
