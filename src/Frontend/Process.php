<?php
namespace WPFormsFiuu\Frontend;

defined('ABSPATH') || exit;

class Process {
    public static function init() {
        add_action('wpforms_process_complete', [__CLASS__, 'maybe_redirect'], 10, 4);
    }

    public static function maybe_redirect($fields, $entry, $form_data, $entry_id) {
        if (empty($form_data['settings']['fiuu_enable'])) return;

        set_transient('fiuu_entry_' . $entry_id, compact('fields', 'form_data', 'entry_id'), HOUR_IN_SECONDS);

        wp_redirect(add_query_arg([
            'fiuu_redirect' => 1,
            'entry_id' => $entry_id
        ], home_url()));
        exit;
    }
}
