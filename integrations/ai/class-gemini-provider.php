<?php
namespace CloseClient\Outreach\Integrations\AI;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Security\Security_Helper;

class Gemini_Provider {

    private $api_key;
    private $model;

    public function __construct($config = array()) {
        $encrypted_key = isset($config['gemini_api_key']) ? $config['gemini_api_key'] : '';
        $this->api_key = Security_Helper::decrypt($encrypted_key);
        $this->model = !empty($config['gemini_model']) ? $config['gemini_model'] : 'gemini-3-flash';
    }

    /**
     * Send generation request to Google Gemini API
     */
    public function generate($prompt, $system_instruction = '') {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', __('Google Gemini API Key is not configured in settings.', 'closeclient-outreach'));
        }

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            urlencode($this->model),
            urlencode($this->api_key)
        );

        $contents = array();
        if (!empty($system_instruction)) {
            $contents[] = array(
                'role' => 'user',
                'parts' => array(array('text' => 'System Instruction: ' . $system_instruction))
            );
        }

        $contents[] = array(
            'role' => 'user',
            'parts' => array(array('text' => $prompt))
        );

        $body_data = array(
            'contents' => $contents
        );

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body'    => json_encode($body_data),
            'timeout' => 30,
        );

        $response = wp_remote_post($endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($code !== 200 || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $err_msg = isset($data['error']['message']) ? $data['error']['message'] : __('Google Gemini API request failed.', 'closeclient-outreach');
            return new \WP_Error('api_error', $err_msg);
        }

        return trim($data['candidates'][0]['content']['parts'][0]['text']);
    }
}
