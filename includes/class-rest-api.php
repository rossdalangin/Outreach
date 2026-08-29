<?php
namespace CloseClient\Outreach\Includes;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Automation\Automation_Engine;
use CloseClient\Outreach\Integrations\GoogleSheets\Google_Sheets_Service;

class REST_API {

    const NAMESPACE = 'closeclient-outreach/v1';

    public static function register_routes() {
        register_rest_route(self::NAMESPACE, '/webhook', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_inbound_webhook'),
            'permission_callback'=> array(__CLASS__, 'verify_webhook_token'),
        ));

        register_rest_route(self::NAMESPACE, '/sync', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_remote_sync'),
            'permission_callback'=> array(__CLASS__, 'verify_webhook_token'),
        ));
    }

    public static function verify_webhook_token(\WP_REST_Request $request) {
        $settings = get_option('cc_outreach_settings', array());
        $expected_token = !empty($settings['webhook_secret']) ? $settings['webhook_secret'] : '';

        // If secret token is set, check X-CC-Token header or query param
        if (!empty($expected_token)) {
            $header_token = $request->get_header('X-CC-Token');
            $param_token  = $request->get_param('secret_token');

            if ($header_token === $expected_token || $param_token === $expected_token) {
                return true;
            }
            return new \WP_Error('rest_forbidden', __('Invalid secret webhook token.', 'closeclient-outreach'), array('status' => 403));
        }

        // Default: requires manage_options
        return current_user_can('manage_options');
    }

    public static function handle_inbound_webhook(\WP_REST_Request $request) {
        $email = sanitize_email($request->get_param('email'));
        $reply_content = sanitize_textarea_field($request->get_param('reply_content'));

        if (empty($email) || empty($reply_content)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Email and reply_content parameters are required.'
            ), 400);
        }

        $lead = Lead::get_by_email($email);
        if (!$lead) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Lead not found for email: ' . $email
            ), 404);
        }

        $analysis = Automation_Engine::process_incoming_reply($lead['id'], $reply_content);

        return new \WP_REST_Response(array(
            'success'  => true,
            'lead_id'  => $lead['id'],
            'analysis' => $analysis,
        ), 200);
    }

    public static function handle_remote_sync(\WP_REST_Request $request) {
        $settings = get_option('cc_outreach_settings', array());
        $url = !empty($settings['google_sheets_url']) ? $settings['google_sheets_url'] : '';
        $tab = !empty($settings['google_sheets_tab']) ? $settings['google_sheets_tab'] : 'Leads';
        $mapping = !empty($settings['column_mapping']) ? $settings['column_mapping'] : array();

        if (empty($url)) {
            return new \WP_REST_Response(array('success' => false, 'message' => 'Google Sheets URL not configured.'), 400);
        }

        $res = Google_Sheets_Service::sync_leads($url, $tab, $mapping);
        if (is_wp_error($res)) {
            return new \WP_REST_Response(array('success' => false, 'message' => $res->get_error_message()), 500);
        }

        return new \WP_REST_Response(array('success' => true, 'sync_results' => $res), 200);
    }
}
