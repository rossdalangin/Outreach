<?php
namespace CloseClient\Outreach\Integrations\GoogleSheets;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Includes\Models\Activity_Log;

class Google_Sheets_Service {

    /**
     * Extract Spreadsheet ID from full URL or return ID if raw ID is passed
     */
    public static function extract_spreadsheet_id($url_or_id) {
        $url_or_id = trim($url_or_id);
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url_or_id, $matches)) {
            return $matches[1];
        }
        return $url_or_id;
    }

    /**
     * Get default column mapping
     */
    public static function get_default_mapping() {
        return array(
            'lead_id'              => 'Lead ID',
            'first_name'           => 'First Name',
            'last_name'            => 'Last Name',
            'company_name'         => 'Company Name',
            'email'                => 'Email',
            'website'              => 'Website',
            'linkedin_url'         => 'LinkedIn URL',
            'niche'                => 'Niche',
            'location'             => 'Location',
            'lead_source'          => 'Lead Source',
            'status'               => 'Status',
            'notes'                => 'Notes',
            'conversation_summary' => 'Conversation Summary',
            'assigned_to'          => 'Assigned To',
        );
    }

    /**
     * Fetch public CSV data or via published Google Sheet link / API format
     */
    public static function fetch_sheet_rows($spreadsheet_url, $tab_name = 'Leads') {
        $sheet_id = self::extract_spreadsheet_id($spreadsheet_url);
        if (empty($sheet_id)) {
            return new \WP_Error('invalid_url', __('Invalid Google Sheets URL or ID.', 'closeclient-outreach'));
        }

        // CSV export URL format for public Google Sheets
        $export_url = sprintf(
            'https://docs.google.com/spreadsheets/d/%s/gviz/tq?tqx=out:csv&sheet=%s',
            urlencode($sheet_id),
            urlencode($tab_name)
        );

        $response = wp_remote_get($export_url, array('timeout' => 15));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return new \WP_Error('empty_response', __('Google Sheets returned empty response.', 'closeclient-outreach'));
        }

        // Parse CSV string into array
        $rows = array_map('str_getcsv', explode("\n", str_replace("\r", "", $body)));
        if (empty($rows) || count($rows) < 2) {
            return new \WP_Error('no_data', __('No lead rows found in the sheet.', 'closeclient-outreach'));
        }

        $headers = array_shift($rows);
        $headers = array_map(function($h) {
            return trim(str_replace('"', '', $h));
        }, $headers);

        $parsed_rows = array();
        foreach ($rows as $row) {
            if (count($row) === count($headers)) {
                $item = array();
                foreach ($headers as $index => $header) {
                    $item[$header] = trim(str_replace('"', '', $row[$index]));
                }
                $parsed_rows[] = $item;
            }
        }

        return array(
            'headers' => $headers,
            'rows'    => $parsed_rows
        );
    }

    /**
     * Sync Google Sheet rows to DB
     */
    public static function sync_leads($spreadsheet_url, $tab_name = 'Leads', $column_mapping = array()) {
        if (empty($column_mapping)) {
            $column_mapping = self::get_default_mapping();
        }

        $data = self::fetch_sheet_rows($spreadsheet_url, $tab_name);
        if (is_wp_error($data)) {
            Activity_Log::log('google_sheets_sync_error', 0, $data->get_error_message());
            return $data;
        }

        $rows = $data['rows'];
        $created_count = 0;
        $updated_count = 0;
        $failed_count = 0;

        foreach ($rows as $row) {
            $lead_id_val = isset($column_mapping['lead_id']) && isset($row[$column_mapping['lead_id']]) ? sanitize_text_field($row[$column_mapping['lead_id']]) : '';
            $email_val   = isset($column_mapping['email']) && isset($row[$column_mapping['email']]) ? sanitize_email($row[$column_mapping['email']]) : '';

            if (empty($email_val) && empty($lead_id_val)) {
                $failed_count++;
                continue;
            }

            // Check if existing
            $existing = null;
            if (!empty($lead_id_val)) {
                $existing = Lead::get_by_lead_id($lead_id_val);
            }
            if (!$existing && !empty($email_val)) {
                $existing = Lead::get_by_email($email_val);
            }

            $lead_data = array();
            foreach ($column_mapping as $db_field => $sheet_col) {
                if (isset($row[$sheet_col])) {
                    $lead_data[$db_field] = sanitize_text_field($row[$sheet_col]);
                }
            }

            if (empty($lead_data['status'])) {
                $lead_data['status'] = 'New Lead';
            }

            if ($existing) {
                Lead::update($existing['id'], $lead_data);
                $updated_count++;
            } else {
                Lead::insert($lead_data);
                $created_count++;
            }
        }

        $summary = array(
            'total_processed' => count($rows),
            'created'         => $created_count,
            'updated'         => $updated_count,
            'failed'          => $failed_count,
            'sync_time'       => current_time('mysql')
        );

        // Update settings with last sync status
        $settings = get_option('cc_outreach_settings', array());
        $settings['last_sync_time'] = $summary['sync_time'];
        $settings['last_sync_result'] = $summary;
        update_option('cc_outreach_settings', $settings);

        Activity_Log::log('google_sheets_sync', 0, sprintf('Processed: %d, Created: %d, Updated: %d, Failed: %d', count($rows), $created_count, $updated_count, $failed_count));

        return $summary;
    }

    /**
     * Send lead status / conversation update back to Google Sheets via Webhook
     */
    public static function update_sheet_lead($lead_id, $status, $summary = '') {
        $settings = get_option('cc_outreach_settings', array());
        $webhook_url = !empty($settings['google_sheets_webhook_url']) ? $settings['google_sheets_webhook_url'] : '';

        if (empty($webhook_url)) {
            return false;
        }

        $lead = Lead::get($lead_id);
        if (!$lead) return false;

        $payload = array(
            'lead_id'              => $lead['lead_id'],
            'email'                => $lead['email'],
            'first_name'           => $lead['first_name'],
            'last_name'            => $lead['last_name'],
            'company_name'         => $lead['company_name'],
            'status'               => $status,
            'conversation_summary' => $summary ? $summary : $lead['conversation_summary'],
            'updated_at'           => current_time('mysql'),
        );

        $response = wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => json_encode($payload),
            'timeout' => 15,
        ));

        if (!is_wp_error($response)) {
            Activity_Log::log('google_sheets_writeback', $lead_id, 'Pushed status update to Google Sheet webhook');
            return true;
        }

        return false;
    }
}
