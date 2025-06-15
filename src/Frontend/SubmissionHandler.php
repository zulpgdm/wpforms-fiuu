<?php
namespace WPFormsFiuu\Frontend;

defined('ABSPATH') || exit;

class SubmissionHandler {
    public static function init() {
        add_action('wpforms_process_before_save', [__CLASS__, 'maybe_redirect_to_fiuu'], 10, 4);
    }

    public static function maybe_redirect_to_fiuu($fields, $entry, $form_data, $entry_id) {
        if (empty($form_data['settings']['fiuu_enable'])) return;

        $amount = $form_data['settings']['fiuu_amount'] ?? '';
        $api = $form_data['settings']['fiuu_api'] ?? '';
        $merchant = $form_data['settings']['fiuu_merchant'] ?? '';

        $name = '';
        $email = '';
        foreach ($fields as $field) {
            if (stripos($field['name'], 'name') !== false) $name = $field['value'];
            if (stripos($field['name'], 'email') !== false) $email = $field['value'];
        }

        // Generate a reference and store temporary entry
        $ref = uniqid('fiuu_', true);
        set_transient($ref, compact('fields', 'form_data'), 10 * MINUTE_IN_SECONDS);

        // Redirect to Fiuu payment page
        $redirect_url = add_query_arg([
            'fiuu_return' => '1',
            'ref' => $ref
        ], home_url('/'));

        $fiuu_url = 'https://sandbox.fiuu.my/payment';
        $params = http_build_query([
            'merchant_id' => $merchant,
            'amount' => $amount,
            'customer_name' => $name,
            'customer_email' => $email,
            'redirect_url' => $redirect_url
        ]);

        wp_redirect($fiuu_url . '?' . $params);
        exit;
    }
}
