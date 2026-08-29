<?php
namespace CloseClient\Outreach\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Security_Helper
 * Provides security utilities including nonces, capability checks, encryption, and sanitization.
 */
class Security_Helper {

    private static $encryption_key = 'cc_outreach_sec_key_hash';

    /**
     * Check current user capability
     */
    public static function check_capability($capability = 'manage_options') {
        return current_user_can($capability);
    }

    /**
     * Enforce capability or die/exit
     */
    public static function verify_capability($capability = 'manage_options') {
        if (!self::check_capability($capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'closeclient-outreach'));
        }
    }

    /**
     * Create nonce for action
     */
    public static function create_nonce($action = 'cc_outreach_action') {
        return wp_create_nonce($action);
    }

    /**
     * Verify nonce
     */
    public static function verify_nonce($nonce, $action = 'cc_outreach_action') {
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Encrypt sensitive data (e.g., API keys)
     */
    public static function encrypt($value) {
        if (empty($value)) {
            return '';
        }
        $key = defined('AUTH_KEY') ? AUTH_KEY : 'cc_fallback_salt_key_2025';
        $cipher = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($value, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        return base64_encode($iv . $hmac . $ciphertext_raw);
    }

    /**
     * Decrypt sensitive data
     */
    public static function decrypt($ciphertext) {
        if (empty($ciphertext)) {
            return '';
        }
        $c = base64_decode($ciphertext);
        $key = defined('AUTH_KEY') ? AUTH_KEY : 'cc_fallback_salt_key_2025';
        $cipher = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        if (strlen($c) < $ivlen + 32) {
            return $ciphertext; // Return raw if not valid encrypted string
        }
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) {
            return openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        }
        return $ciphertext;
    }

    /**
     * Sanitize array recursively
     */
    public static function sanitize_array($array) {
        $sanitized = array();
        foreach ($array as $key => $value) {
            $key = sanitize_key($key);
            if (is_array($value)) {
                $sanitized[$key] = self::sanitize_array($value);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }
}
