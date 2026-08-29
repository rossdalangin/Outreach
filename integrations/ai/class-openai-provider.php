<?php
namespace CloseClient\Outreach\Integrations\AI;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Security\Security_Helper;

class OpenAI_Provider {

    private $api_key;
    private $base_url;
    private $model;
    private $temperature;
    private $max_tokens;

    public function __construct($config = array()) {
        $encrypted_key = isset($config['api_key']) ? $config['api_key'] : '';
        $this->api_key = Security_Helper::decrypt($encrypted_key);
        $this->base_url = !empty($config['base_url']) ? rtrim($config['base_url'], '/') : 'https://api.openai.com/v1';
        $this->model = !empty($config['ai_model']) ? $config['ai_model'] : 'gpt-4o';
        $this->temperature = isset($config['ai_temperature']) ? floatval($config['ai_temperature']) : 0.7;
        $this->max_tokens = isset($config['ai_max_tokens']) ? intval($config['ai_max_tokens']) : 500;
    }

    /**
     * Send completion request to OpenAI compatible API
     */
    public function generate($prompt, $system_instruction = '') {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', __('OpenAI API Key is not configured in settings.', 'closeclient-outreach'));
        }

        $endpoint = $this->base_url . '/chat/completions';

        $messages = array();
        if (!empty($system_instruction)) {
            $messages[] = array(
                'role'    => 'system',
                'content' => $system_instruction,
            );
        }
        $messages[] = array(
            'role'    => 'user',
            'content' => $prompt,
        );

        $body = json_encode(array(
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => $this->temperature,
            'max_tokens'  => $this->max_tokens,
        ));

        $args = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'body'    => $body,
            'timeout' => 30,
        );

        $response = wp_remote_post($endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($code !== 200 || !isset($data['choices'][0]['message']['content'])) {
            $err_msg = isset($data['error']['message']) ? $data['error']['message'] : __('OpenAI API request failed.', 'closeclient-outreach');
            return new \WP_Error('api_error', $err_msg);
        }

        return trim($data['choices'][0]['message']['content']);
    }
}
