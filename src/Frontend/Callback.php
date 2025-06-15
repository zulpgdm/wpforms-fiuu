<?php
namespace WPFormsFiuu\Frontend;

defined('ABSPATH') || exit;

use WPFormsFiuu\Helpers\SessionManager;
use WPForms\Models\Entry;
use WPForms\Models\EntryMeta;

/**
 * Handles the callback from Fiuu payment gateway after a transaction.
 */
class Callback {

    /**
     * Initialize callback hooks.
     */
    public static function init() {
        // Handle the initial redirect back from Fiuu for user-facing confirmation.
        add_action('template_redirect', [__CLASS__, 'maybe_handle_user_redirect']);
        // Register REST API endpoint for server-to-server Fiuu callbacks.
        add_action('rest_api_init', [__CLASS__, 'register_rest_route']);
    }

    /**
     * Handles the user's browser redirect back from Fiuu.
     * This is for displaying a success/failure message to the user.
     */
    public static function maybe_handle_user_redirect() {
        if (!isset($_GET['fiuu_callback']) || !isset($_GET['entry_id'])) {
            return;
        }

        $entry_id = sanitize_text_field($_GET['entry_id']);
        $data     = SessionManager::get_fiuu_data($entry_id);

        if ( ! $data) {
            // Data not found, perhaps transient expired or invalid entry_id.
            wp_redirect(home_url('/payment-failed-or-expired')); // Redirect to a generic error page.
            exit;
        }

        // Ideally, you'd have Fiuu's transaction status parameter here (e.g., $_GET['status']).
        // For demonstration, assuming success if data is found.
        // In a real scenario, you'd cross-check this with the Fiuu API.

        // After successful processing, clear the transient.
        SessionManager::delete_fiuu_data($entry_id);

        // Redirect to a thank you page.
        wp_redirect(home_url('/thank-you-for-payment'));
        exit;
    }

    /**
     * Registers a REST API route for Fiuu's server-to-server callback.
     * This is crucial for reliable payment confirmation.
     */
    public static function register_rest_route() {
        register_rest_route('wpforms-fiuu/v1', '/callback', [
            'methods'             => 'POST', // Fiuu usually sends POST requests for callbacks.
            'callback'            => [__CLASS__, 'handle_fiuu_webhook_callback'],
            'permission_callback' => '__return_true', // Implement proper security for production.
                                                     // Fiuu's IP whitelist or signature verification should be here.
        ]);
    }

    /**
     * Handles the incoming Fiuu webhook callback.
     * This is where the actual payment verification and entry update should happen.
     *
     * @param \WP_REST_Request $request The REST API request object.
     * @return \WP_REST_Response REST response.
     */
    public static function handle_fiuu_webhook_callback(\WP_REST_Request $request) {
        $body    = $request->get_json_params(); // Get JSON payload from Fiuu.
        $headers = $request->get_headers();     // Get headers, often includes signature.

        // Log the incoming callback for debugging.
        error_log('WPForms Fiuu Webhook Callback Received: ' . print_r($body, true));
        error_log('WPForms Fiuu Webhook Headers: ' . print_r($headers, true));

        // 1. **Verify the authenticity of the callback.**
        //    This is crucial. Fiuu will provide documentation on how to verify
        //    the callback (e.g., checking an HMAC signature using your API secret).
        //    If verification fails, return an error.
        // if ( ! self::verify_fiuu_signature($body, $headers, $form_data['settings']['fiuu_api_secret'])) {
        //     return new \WP_REST_Response(['message' => 'Invalid signature'], 401);
        // }

        // 2. **Extract relevant information from the Fiuu payload.**
        //    This will depend on Fiuu's specific callback structure.
        $fiuu_transaction_id = isset($body['transaction_id']) ? sanitize_text_field($body['transaction_id']) : '';
        $fiuu_status         = isset($body['status']) ? sanitize_text_field($body['status']) : '';
        $fiuu_order_id       = isset($body['order_id']) ? sanitize_text_field($body['order_id']) : ''; // This should map to your entry_id or transient key.
        $fiuu_amount         = isset($body['amount']) ? floatval($body['amount']) : 0.0;

        // Assuming $fiuu_order_id contains the entry ID.
        $entry_id = intval($fiuu_order_id); // Or extract from a more complex order_id.

        if (empty($entry_id)) {
            return new \WP_REST_Response(['message' => 'Missing entry ID in callback'], 400);
        }

        // Retrieve the stored form data using the entry ID.
        $stored_data = SessionManager::get_fiuu_data($entry_id);

        if ( ! $stored_data) {
            error_log('WPForms Fiuu Webhook: Stored data not found for entry ' . $entry_id);
            return new \WP_REST_Response(['message' => 'Entry data not found or expired'], 404);
        }

        $form_data = $stored_data['form_data'];
        $fields    = $stored_data['fields'];

        // 3. **Process based on Fiuu transaction status.**
        if ($fiuu_status === 'completed' || $fiuu_status === 'success') {
            // Payment was successful.
            // Mark the WPForms entry as paid or complete the payment process.
            self::mark_entry_as_paid($entry_id, $fiuu_transaction_id, $fiuu_amount);

            // You might also want to send email notifications, update other plugin data, etc.
            do_action('wpforms_fiuu_payment_completed', $entry_id, $fiuu_transaction_id, $fiuu_amount, $form_data, $fields);

            // Delete the transient as the payment is confirmed.
            SessionManager::delete_fiuu_data($entry_id);

            return new \WP_REST_Response(['message' => 'Payment successfully processed'], 200);

        } elseif ($fiuu_status === 'pending') {
            // Payment is pending. Update entry status accordingly.
            self::update_entry_status($entry_id, 'pending', $fiuu_transaction_id);

            do_action('wpforms_fiuu_payment_pending', $entry_id, $fiuu_transaction_id, $fiuu_amount, $form_data, $fields);

            return new \WP_REST_Response(['message' => 'Payment pending'], 200);

        } else {
            // Payment failed or was cancelled.
            self::update_entry_status($entry_id, 'failed', $fiuu_transaction_id);

            do_action('wpforms_fiuu_payment_failed', $entry_id, $fiuu_transaction_id, $fiuu_amount, $form_data, $fields);

            // Optionally, delete the transient if no further action is expected for this entry.
            SessionManager::delete_fiuu_data($entry_id);

            return new \WP_REST_Response(['message' => 'Payment failed or cancelled'], 200);
        }
    }

    /**
     * Marks a WPForms entry as paid.
     * This involves updating entry meta or a custom field.
     *
     * @param int    $entry_id          The WPForms entry ID.
     * @param string $transaction_id    The Fiuu transaction ID.
     * @param float  $amount            The paid amount.
     */
    private static function mark_entry_as_paid($entry_id, $transaction_id, $amount) {
        if ( ! class_exists('WPForms\Models\EntryMeta') || ! class_exists('WPForms\Models\Entry')) {
            return;
        }

        // Add transaction details to entry meta.
        EntryMeta::add(
            [
                'entry_id' => $entry_id,
                'form_id'  => Entry::get_form_id($entry_id), // Get form ID from entry
                'field_id' => 0, // General meta, not tied to a specific field.
                'key'      => 'fiuu_transaction_id',
                'value'    => $transaction_id,
            ],
            'payment'
        );
        EntryMeta::add(
            [
                'entry_id' => $entry_id,
                'form_id'  => Entry::get_form_id($entry_id),
                'field_id' => 0,
                'key'      => 'fiuu_payment_status',
                'value'    => 'completed',
            ],
            'payment'
        );
        EntryMeta::add(
            [
                'entry_id' => $entry_id,
                'form_id'  => Entry::get_form_id($entry_id),
                'field_id' => 0,
                'key'      => 'fiuu_payment_amount',
                'value'    => $amount,
            ],
            'payment'
        );

        // You might want to update the entry status within WPForms if it supports it.
        // For WPForms Lite, direct entry status modification might be limited.
        // For WPForms Pro with Payments Addon, there are specific hooks.
        // As a fallback, setting meta is generally robust.
    }

    /**
     * Updates the status of a WPForms entry.
     *
     * @param int    $entry_id          The WPForms entry ID.
     * @param string $status            The new status (e.g., 'pending', 'failed').
     * @param string $transaction_id    The Fiuu transaction ID.
     */
    private static function update_entry_status($entry_id, $status, $transaction_id = '') {
        if ( ! class_exists('WPForms\Models\EntryMeta') || ! class_exists('WPForms\Models\Entry')) {
            return;
        }

        // Get form ID from entry.
        $form_id = Entry::get_form_id($entry_id);

        EntryMeta::update(
            [
                'entry_id' => $entry_id,
                'form_id'  => $form_id,
                'field_id' => 0,
                'key'      => 'fiuu_payment_status',
                'value'    => $status,
            ],
            'payment'
        );

        if ( ! empty($transaction_id)) {
            EntryMeta::update(
                [
                    'entry_id' => $entry_id,
                    'form_id'  => $form_id,
                    'field_id' => 0,
                    'key'      => 'fiuu_transaction_id',
                    'value'    => $transaction_id,
                ],
                'payment'
            );
        }
    }

    /**
     * Placeholder for Fiuu signature verification.
     * This is crucial for security. Consult Fiuu API documentation.
     *
     * @param array $payload The raw JSON payload from Fiuu.
     * @param array $headers The HTTP headers from Fiuu.
     * @param string $api_secret Your Fiuu API secret.
     * @return bool True if signature is valid, false otherwise.
     */
    // private static function verify_fiuu_signature($payload, $headers, $api_secret) {
    //     // Example: Fiuu might send a signature in a header like 'X-Fiuu-Signature'.
    //     // $fiuu_signature = isset($headers['x-fiuu-signature'][0]) ? $headers['x-fiuu-signature'][0] : '';
    //     // $calculated_signature = hash_hmac('sha256', json_encode($payload), $api_secret);
    //     // return hash_equals($fiuu_signature, $calculated_signature);
    //     return true; // DANGER! Replace with actual verification in production.
    // }
}
