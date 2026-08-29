<?php
namespace CloseClient\Outreach\Integrations\AI;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Security\Security_Helper;

class Anthropic_Provider {

    private $api_key;
    private $model;
    private $temperature;
    private $max_tokens;

    public function __construct($config = array()) {
        $encrypted_key = isset($config['anthropic_api_key']) ? $config['anthropic_api_key'] : '';
        $this->api_key = Security_Helper::decrypt($encrypted_key);
        $this->model = !empty($config['anthropic_model']) ? $config['anthropic_model'] : 'claude-3-5-sonnet-20241022';
        $this->temperature = isset($config['ai_temperature']) ? floatval($config['ai_temperature']) : 0.7;
        $this->max_tokens = isset($config['ai_max_tokens']) ? intval($config['ai_max_tokens']) : 500;
    }

    /**
     * Send completion request to Anthropic Messages API
     */
    public function generate($prompt, $system_instruction = '') {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', __('Anthropic API Key is not configured in settings.', 'closeclient-outreach'));
        }

        $endpoint = 'https://api.anthropic.com/v1/messages';

        $body_data = array(
            'model'       => $this->model,
            'max_tokens'  => $this->max_tokens,
            'temperature' => $this->temperature,
            'messages'    => array(
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                )
            ),
        );

        if (!empty($system_instruction)) {
            $body_data['system'] = $system_instruction;
        }

        $args = array(
            'headers' => array(
                'Content-Type'      => 'application/json',
                'x-api-key'         => $this->api_key,
                'anthropic-version' => '2023-06-01',
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

        if ($code !== 200 || !isset($data['content'][0]['text'])) {
            $err_msg = isset($data['error']['message']) ? $data['error']['message'] : __('Anthropic API request failed.', 'closeclient-outreach');
            return new \WP_Error('api_error', $err_msg);
        }

        return trim($data['content'][0]['text']);
    }
}
