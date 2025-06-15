<?php
namespace WPFormsFiuu\Helpers;

defined('ABSPATH') || exit;

/**
 * Manages session-like data persistence using WordPress Transients.
 * Useful for storing temporary data between page loads (e.g., before redirecting to Fiuu and back).
 */
class SessionManager {

    const TRANSIENT_PREFIX = 'wpforms_fiuu_entry_';
    const TRANSIENT_EXPIRATION = HOUR_IN_SECONDS; // 1 hour

    /**
     * Initialize SessionManager (if any specific setup is needed).
     */
    public static function init() {
        // No specific init logic required for this transient-based manager currently.
    }

    /**
     * Stores Fiuu payment data in a transient.
     *
     * @param int   $entry_id The WPForms entry ID.
     * @param array $data     The data to store (e.g., form fields, form_data, entry_id).
     * @return string|false The transient key on success, false on failure.
     */
    public static function set_fiuu_data($entry_id, $data) {
        $transient_key = self::TRANSIENT_PREFIX . $entry_id;
        $success = set_transient($transient_key, $data, self::TRANSIENT_EXPIRATION);

        return $success ? $transient_key : false;
    }

    /**
     * Retrieves Fiuu payment data from a transient.
     *
     * @param int $entry_id The WPForms entry ID.
     * @return array|false The stored data on success, false if not found or expired.
     */
    public static function get_fiuu_data($entry_id) {
        $transient_key = self::TRANSIENT_PREFIX . $entry_id;
        return get_transient($transient_key);
    }

    /**
     * Deletes Fiuu payment data from a transient.
     *
     * @param int $entry_id The WPForms entry ID.
     * @return bool True if the transient was deleted, false otherwise.
     */
    public static function delete_fiuu_data($entry_id) {
        $transient_key = self::TRANSIENT_PREFIX . $entry_id;
        return delete_transient($transient_key);
    }
}
