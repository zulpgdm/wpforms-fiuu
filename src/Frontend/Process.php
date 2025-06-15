<?php
namespace WPFormsFiuu\Frontend;

defined('ABSPATH') || exit;

use WPFormsFiuu\Helpers\SessionManager; // Using SessionManager for transient key storage

/**
 * Handles the processing of WPForms submissions for Fiuu payments.
 */
class Process {

    /**
     * Initialize process hooks.
     */
    public static function init() {
        add_action('wpforms_process_complete', [__CLASS__, 'maybe_redirect_to_fiuu'], 10, 4);
    }

    /**
     * Redirects to Fiuu payment gateway if Fiuu is enabled for the form.
     *
     * @param array $fields    Processed form fields.
     * @param array $entry     Entry data.
     * @param array $form_data Form data.
     * @param int   $entry_id  Entry ID.
     */
    public static function maybe_redirect_to_fiuu($fields, $entry, $form_data, $entry_id) {
        // Check if Fiuu payment is enabled for this specific form.
        if (empty($form_data['settings']['fiuu_enable'])) {
            return;
        }

        // Retrieve Fiuu specific settings.
        $fiuu_api_key      = ! empty($form_data['settings']['fiuu_api']) ? $form_data['settings']['fiuu_api'] : '';
        $fiuu_merchant_id  = ! empty($form_data['settings']['fiuu_merchant']) ? $form_data['settings']['fiuu_merchant'] : '';

        // Basic validation for Fiuu credentials.
        if (empty($fiuu_api_key) || empty($fiuu_merchant_id)) {
            error_log('WPForms Fiuu: Missing API Key or Merchant ID for form ' . $form_data['id']);
            // Optionally, handle this by showing an error to the user or redirecting to a failure page.
            return;
        }

        // Store necessary data in a transient for the callback.
        // Using SessionManager to handle transient keys to avoid direct string concatenation.
        $transient_key = SessionManager::set_fiuu_data($entry_id, [
            'fields'    => $fields,
            'form_data' => $form_data,
            'entry_id'  => $entry_id,
            'amount'    => self::get_payment_amount($fields, $form_data), // Extract payment amount
            'currency'  => 'MYR', // Or retrieve dynamically if supported
            'description' => sprintf(__('Payment for form entry %d', 'wpforms-fiuu'), $entry_id),
            // Add other Fiuu specific parameters as needed, e.g., customer name, email.
            'customer_email' => self::get_customer_email($fields, $form_data),
            'customer_name'  => self::get_customer_name($fields, $form_data),
        ]);

        if ( ! $transient_key) {
            error_log('WPForms Fiuu: Failed to store data in transient for entry ' . $entry_id);
            // Handle error, e.g., redirect to an error page.
            return;
        }

        // Construct Fiuu payment URL with necessary parameters.
        // This is a placeholder; you'll need to replace with actual Fiuu API endpoint and parameters.
        $fiuu_payment_url = self::build_fiuu_payment_url(
            $fiuu_merchant_id,
            self::get_payment_amount($fields, $form_data),
            $transient_key, // Use the transient key as a reference
            self::get_customer_email($fields, $form_data),
            self::get_customer_name($fields, $form_data)
        );

        // Redirect to Fiuu payment gateway.
        wp_redirect(esc_url_raw($fiuu_payment_url));
        exit;
    }

    /**
     * Extracts the payment amount from the form fields.
     * You'll need to customize this based on how your WPForms are set up for payments.
     *
     * @param array $fields    Processed form fields.
     * @param array $form_data Form data.
     * @return float The payment amount.
     */
    private static function get_payment_amount($fields, $form_data) {
        $amount = 0.0;

        // Example: If you have a "Total" field with ID 5.
        foreach ($fields as $field_id => $field) {
            if (isset($field['type']) && ($field['type'] === 'payment-total' || $field['type'] === 'total')) {
                // WPForms stores total as string, convert to float.
                $amount = (float) str_replace(',', '', $field['value']);
                break;
            }
            // You might need to iterate through individual product fields and sum them up.
            if (isset($field['type']) && in_array($field['type'], ['payment-single', 'payment-multiple', 'payment-checkbox', 'payment-select'], true)) {
                // For individual payment fields, you'd need to sum their values.
                // This would be more complex and depend on how you configure your forms.
                // For simplicity, assuming a 'payment-total' field for now.
            }
        }

        return round($amount, 2); // Round to 2 decimal places for currency.
    }

    /**
     * Extracts the customer email from the form fields.
     *
     * @param array $fields    Processed form fields.
     * @param array $form_data Form data.
     * @return string Customer email.
     */
    private static function get_customer_email($fields, $form_data) {
        foreach ($fields as $field) {
            if (isset($field['type']) && $field['type'] === 'email' && !empty($field['value'])) {
                return sanitize_email($field['value']);
            }
        }
        return '';
    }

    /**
     * Extracts the customer name from the form fields.
     *
     * @param array $fields    Processed form fields.
     * @param array $form_data Form data.
     * @return string Customer name.
     */
    private static function get_customer_name($fields, $form_data) {
        $first_name = '';
        $last_name  = '';

        foreach ($fields as $field) {
            if (isset($field['type'])) {
                if ($field['type'] === 'name' && isset($field['value_first']) && isset($field['value_last'])) {
                    $first_name = sanitize_text_field($field['value_first']);
                    $last_name  = sanitize_text_field($field['value_last']);
                    break;
                } elseif ($field['type'] === 'text' || $field['type'] === 'name-simple') {
                    // Fallback for simple text fields used as name.
                    if (strpos(strtolower($field['name']), 'name') !== false && !empty($field['value'])) {
                        return sanitize_text_field($field['value']);
                    }
                }
            }
        }
        return trim($first_name . ' ' . $last_name);
    }

    /**
     * Builds the Fiuu payment URL.
     * This method needs to be implemented according to Fiuu's API documentation.
     *
     * @param string $merchant_id  Your Fiuu Merchant ID.
     * @param float  $amount       Payment amount.
     * @param string $order_id     Unique order ID (can be entry ID or a generated one).
     * @param string $customer_email Customer email.
     * @param string $customer_name Customer name.
     * @return string The Fiuu payment gateway URL.
     */
    private static function build_fiuu_payment_url($merchant_id, $amount, $order_id, $customer_email = '', $customer_name = '') {
        // IMPORTANT: Replace this with the actual Fiuu payment endpoint and parameters.
        // This is a simplified example. You'll likely need to generate a signature/hash.

        $fiuu_base_url = 'https://api.fiuu.com/v1/payment'; // Example endpoint, confirm with Fiuu docs.

        $params = [
            'merchant_id'    => $merchant_id,
            'amount'         => number_format($amount, 2, '.', ''), // Fiuu might expect specific format
            'order_id'       => $order_id, // This should be unique per transaction. Using transient key for now.
            'currency'       => 'MYR',
            'return_url'     => add_query_arg([
                'fiuu_callback' => 1,
                'entry_id'      => $order_id, // Pass original entry_id back
            ], home_url('/')),
            'cancel_url'     => home_url('/payment-cancelled'), // Create a dedicated cancel page
            'callback_url'   => home_url('/wp-json/wpforms-fiuu/v1/callback'), // Dedicated webhook endpoint for server-to-server callback
            'customer_email' => $customer_email,
            'customer_name'  => $customer_name,
            // ... other required Fiuu parameters (e.g., product description, customer details, signature)
        ];

        // You'll likely need to generate a `hash` or `signature` based on Fiuu's documentation.
        // This usually involves concatenating parameters and hashing with your API secret.
        // $params['signature'] = self::generate_fiuu_signature($params, $api_secret);

        return add_query_arg($params, $fiuu_base_url);
    }

    /**
     * Placeholder for Fiuu signature generation.
     * Consult Fiuu API documentation for the correct method.
     *
     * @param array  $params The parameters to be sent to Fiuu.
     * @param string $api_secret Your Fiuu API secret.
     * @return string The generated signature.
     */
    // private static function generate_fiuu_signature($params, $api_secret) {
    //     // Example (NOT Fiuu's actual method, check their docs!):
    //     // $string_to_hash = $params['merchant_id'] . $params['order_id'] . $params['amount'] . $api_secret;
    //     // return md5($string_to_hash);
    //     return '';
    // }
}
