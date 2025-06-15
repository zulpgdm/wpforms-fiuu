<?php
namespace WPFormsFiuu\Frontend;

defined('ABSPATH') || exit;

class Callback {
    public static function init() {
        add_action('init', [__CLASS__, 'maybe_handle_callback']);
    }

    public static function maybe_handle_callback() {
        if (!isset($_GET['fiuu_callback'])) return;

        $entry_id = intval($_GET['entry_id']);
        $data = get_transient('fiuu_entry_' . $entry_id);
        if ($data) {
            // Here you would validate the callback with Fiuu and confirm payment.
            delete_transient('fiuu_entry_' . $entry_id);
        }

        wp_redirect(home_url('/thank-you'));
        exit;
    }
}
