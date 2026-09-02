<?php
namespace CloseClient\Outreach\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Security\Security_Helper;
use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Includes\Models\Queue;
use CloseClient\Outreach\Includes\Models\Campaign;
use CloseClient\Outreach\Includes\Models\Rule;
use CloseClient\Outreach\Includes\Models\Activity_Log;
use CloseClient\Outreach\Integrations\GoogleSheets\Google_Sheets_Service;
use CloseClient\Outreach\Integrations\Prospecting\Lead_Finder_Service;
use CloseClient\Outreach\Integrations\AI\AI_Service;
use CloseClient\Outreach\Integrations\Email\Email_Service;
use CloseClient\Outreach\Automation\Automation_Engine;

class Admin_Controller {

    public static function register_menu() {
        add_menu_page(
            __('CloseClient Outreach', 'closeclient-outreach'),
            __('CloseClient Outreach', 'closeclient-outreach'),
            'manage_options',
            'closeclient-outreach',
            array(__CLASS__, 'render_dashboard_page'),
            'dashicons-paperplane',
            30
        );

        $submenus = array(
            'closeclient-outreach'           => array(__('Dashboard', 'closeclient-outreach'), array(__CLASS__, 'render_dashboard_page')),
            'closeclient-outreach-leads'     => array(__('Leads', 'closeclient-outreach'), array(__CLASS__, 'render_leads_page')),
            'closeclient-outreach-prospecting' => array(__('Find Leads', 'closeclient-outreach'), array(__CLASS__, 'render_prospecting_page')),
            'closeclient-outreach-queue'     => array(__('Outreach Queue', 'closeclient-outreach'), array(__CLASS__, 'render_queue_page')),
            'closeclient-outreach-conversations' => array(__('Conversations', 'closeclient-outreach'), array(__CLASS__, 'render_conversations_page')),
            'closeclient-outreach-campaigns' => array(__('Campaigns', 'closeclient-outreach'), array(__CLASS__, 'render_campaigns_page')),
            'closeclient-outreach-rules'     => array(__('Automation Rules', 'closeclient-outreach'), array(__CLASS__, 'render_rules_page')),
            'closeclient-outreach-templates' => array(__('Templates', 'closeclient-outreach'), array(__CLASS__, 'render_templates_page')),
            'closeclient-outreach-logs'      => array(__('Activity Log', 'closeclient-outreach'), array(__CLASS__, 'render_logs_page')),
            'closeclient-outreach-analytics' => array(__('Analytics', 'closeclient-outreach'), array(__CLASS__, 'render_analytics_page')),
            'closeclient-outreach-docs'      => array(__('Docs & Growth', 'closeclient-outreach'), array(__CLASS__, 'render_docs_page')),
            'closeclient-outreach-marketing' => array(__('Sales Playbook', 'closeclient-outreach'), array(__CLASS__, 'render_marketing_page')),
            'closeclient-outreach-settings'  => array(__('Settings', 'closeclient-outreach'), array(__CLASS__, 'render_settings_page')),
        );

        foreach ($submenus as $slug => $data) {
            add_submenu_page(
                'closeclient-outreach',
                $data[0],
                $data[0],
                'manage_options',
                $slug,
                $data[1]
            );
        }
    }

    public static function render_dashboard_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/dashboard.php';
    }

    public static function render_leads_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/leads.php';
    }

    public static function render_prospecting_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/prospecting.php';
    }

    public static function render_queue_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/queue.php';
    }

    public static function render_conversations_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/conversations.php';
    }

    public static function render_campaigns_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/campaigns.php';
    }

    public static function render_rules_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/rules.php';
    }

    public static function render_templates_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/templates.php';
    }

    public static function render_logs_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/logs.php';
    }

    public static function render_analytics_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/analytics.php';
    }

    public static function render_docs_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/docs.php';
    }

    public static function render_marketing_page() {
        Security_Helper::verify_capability();
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/marketing.php';
    }

    public static function render_settings_page() {
        Security_Helper::verify_capability();
        if (isset($_POST['cc_outreach_save_settings']) && check_admin_referer('cc_outreach_settings_nonce')) {
            $settings = get_option('cc_outreach_settings', array());
            $input = isset($_POST['settings']) ? Security_Helper::sanitize_array($_POST['settings']) : array();

            if (!empty($_POST['settings']['ai_api_key'])) {
                $input['ai_api_key'] = Security_Helper::encrypt(sanitize_text_field($_POST['settings']['ai_api_key']));
            } else {
                $input['ai_api_key'] = isset($settings['ai_api_key']) ? $settings['ai_api_key'] : '';
            }

            if (!empty($_POST['settings']['anthropic_api_key'])) {
                $input['anthropic_api_key'] = Security_Helper::encrypt(sanitize_text_field($_POST['settings']['anthropic_api_key']));
            } else {
                $input['anthropic_api_key'] = isset($settings['anthropic_api_key']) ? $settings['anthropic_api_key'] : '';
            }

            if (!empty($_POST['settings']['gemini_api_key'])) {
                $input['gemini_api_key'] = Security_Helper::encrypt(sanitize_text_field($_POST['settings']['gemini_api_key']));
            } else {
                $input['gemini_api_key'] = isset($settings['gemini_api_key']) ? $settings['gemini_api_key'] : '';
            }

            $input['kill_switch'] = !empty($_POST['settings']['kill_switch']) ? true : false;
            $input['allow_weekend_sending'] = !empty($_POST['settings']['allow_weekend_sending']) ? true : false;

            update_option('cc_outreach_settings', array_merge($settings, $input));
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings updated successfully!', 'closeclient-outreach') . '</p></div>';
        }
        require_once CC_OUTREACH_PLUGIN_DIR . 'templates/settings.php';
    }

    /**
     * Handle AJAX requests
     */
    public static function handle_ajax_request() {
        check_ajax_referer('cc_outreach_ajax_nonce', 'nonce');
        if (!Security_Helper::check_capability()) {
            wp_send_json_error(__('Unauthorized capability.', 'closeclient-outreach'));
        }

        $sub_action = isset($_REQUEST['sub_action']) ? sanitize_text_field($_REQUEST['sub_action']) : '';

        switch ($sub_action) {
            case 'sync_sheets':
                $settings = get_option('cc_outreach_settings', array());
                $url = !empty($settings['google_sheets_url']) ? $settings['google_sheets_url'] : '';
                $tab = !empty($settings['google_sheets_tab']) ? $settings['google_sheets_tab'] : 'Leads';
                $mapping = !empty($settings['column_mapping']) ? $settings['column_mapping'] : array();

                if (empty($url)) {
                    wp_send_json_error(__('Google Sheets URL is not configured in Settings.', 'closeclient-outreach'));
                }

                $res = Google_Sheets_Service::sync_leads($url, $tab, $mapping);
                if (is_wp_error($res)) {
                    wp_send_json_error($res->get_error_message());
                } else {
                    wp_send_json_success($res);
                }
                break;

            case 'generate_draft':
                $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;
                $res = Automation_Engine::process_lead_status($lead_id, 'Ready for First Contact');
                if ($res) {
                    wp_send_json_success(__('AI Draft generated and added to queue.', 'closeclient-outreach'));
                } else {
                    wp_send_json_error(__('Lead not found.', 'closeclient-outreach'));
                }
                break;

            case 'approve_queue_item':
                $queue_id = isset($_POST['queue_id']) ? intval($_POST['queue_id']) : 0;
                Queue::update($queue_id, array('status' => 'approved'));
                wp_send_json_success(__('Queue item approved.', 'closeclient-outreach'));
                break;

            case 'send_queue_item':
                $queue_id = isset($_POST['queue_id']) ? intval($_POST['queue_id']) : 0;
                $res = Email_Service::send_email($queue_id);
                if (is_wp_error($res)) {
                    wp_send_json_error($res->get_error_message());
                } else {
                    wp_send_json_success(__('Email sent successfully!', 'closeclient-outreach'));
                }
                break;

            case 'add_lead':
                $lead_data = array(
                    'first_name'   => sanitize_text_field($_POST['first_name']),
                    'last_name'    => sanitize_text_field($_POST['last_name']),
                    'company_name' => sanitize_text_field($_POST['company_name']),
                    'email'        => sanitize_email($_POST['email']),
                    'website'      => sanitize_text_field($_POST['website']),
                    'niche'        => sanitize_text_field($_POST['niche']),
                    'status'       => 'New Lead',
                );
                $id = Lead::insert($lead_data);
                Activity_Log::log('lead_created', $id, 'Manual lead entry');
                wp_send_json_success(array('id' => $id, 'message' => __('Lead added successfully!', 'closeclient-outreach')));
                break;

            case 'update_lead_status':
                $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;
                $status  = sanitize_text_field($_POST['status']);
                Lead::update_status($lead_id, $status, 'Status updated via dashboard');
                Activity_Log::log('status_changed', $lead_id, 'Status updated to ' . $status);
                wp_send_json_success(__('Status updated.', 'closeclient-outreach'));
                break;

            case 'update_lead_details':
                $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;
                $data = array(
                    'first_name'   => sanitize_text_field($_POST['first_name']),
                    'last_name'    => sanitize_text_field($_POST['last_name']),
                    'company_name' => sanitize_text_field($_POST['company_name']),
                    'email'        => sanitize_email($_POST['email']),
                    'website'      => sanitize_text_field($_POST['website']),
                    'niche'        => sanitize_text_field($_POST['niche']),
                    'status'       => sanitize_text_field($_POST['status']),
                    'notes'        => sanitize_textarea_field($_POST['notes']),
                );
                Lead::update($lead_id, $data);
                Activity_Log::log('lead_updated', $lead_id, 'Updated lead parameters via dashboard');
                wp_send_json_success(__('Lead updated successfully.', 'closeclient-outreach'));
                break;

            case 'delete_lead':
                $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;
                global $wpdb;
                $table = Lead::get_table_name();
                $wpdb->delete($table, array('id' => $lead_id));
                Activity_Log::log('lead_deleted', $lead_id, 'Lead removed from database');
                wp_send_json_success(__('Lead deleted.', 'closeclient-outreach'));
                break;

            case 'create_campaign':
                $name   = sanitize_text_field($_POST['name']);
                $niche  = sanitize_text_field($_POST['target_niche']);
                $desc   = sanitize_text_field($_POST['description']);
                $id = Campaign::insert(array(
                    'name'         => $name,
                    'target_niche' => $niche,
                    'description'  => $desc,
                    'status'       => 'active',
                ));
                Activity_Log::log('campaign_created', 0, 'Campaign: ' . $name);
                wp_send_json_success(array('id' => $id, 'message' => __('Campaign created!', 'closeclient-outreach')));
                break;

            case 'discover_leads':
                $industry = sanitize_text_field($_POST['industry']);
                $location = sanitize_text_field($_POST['location']);
                $quantity = intval($_POST['quantity']);
                $res = Lead_Finder_Service::discover_leads($industry, $location, $quantity);
                wp_send_json_success($res);
                break;

            case 'create_rule':
                $name   = sanitize_text_field($_POST['name']);
                $cond   = sanitize_text_field($_POST['condition_status']);
                $action = sanitize_text_field($_POST['action_type']);
                $id = Rule::insert(array(
                    'name'             => $name,
                    'condition_status' => $cond,
                    'action_type'      => $action,
                    'is_active'        => 1,
                ));
                Activity_Log::log('rule_created', 0, 'Rule: ' . $name);
                wp_send_json_success(array('id' => $id, 'message' => __('Automation Rule created!', 'closeclient-outreach')));
                break;

            case 'export_leads_csv':
                $leads = Lead::query(array('number' => 10000));
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=closeclient_leads_' . date('Y-m-d') . '.csv');
                $output = fopen('php://output', 'w');
                fputcsv($output, array('ID', 'Lead ID', 'First Name', 'Last Name', 'Company', 'Email', 'Website', 'Niche', 'Status', 'Last Contact'));
                foreach ($leads as $l) {
                    fputcsv($output, array($l['id'], $l['lead_id'], $l['first_name'], $l['last_name'], $l['company_name'], $l['email'], $l['website'], $l['niche'], $l['status'], $l['last_contact_date']));
                }
                fclose($output);
                exit;

            default:
                wp_send_json_error(__('Invalid action.', 'closeclient-outreach'));
                break;
        }
    }
}
