<?php
namespace CloseClient\Outreach\Automation;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Includes\Models\Queue;
use CloseClient\Outreach\Integrations\Email\Email_Service;
use CloseClient\Outreach\Integrations\GoogleSheets\Google_Sheets_Service;

class Cron_Handler {

    /**
     * Scheduled hourly cron tasks
     */
    public static function execute() {
        $settings = get_option('cc_outreach_settings', array());

        // 1. Google Sheets Auto Sync if URL provided
        if (!empty($settings['google_sheets_url'])) {
            $mapping = !empty($settings['column_mapping']) ? $settings['column_mapping'] : array();
            $tab_name = !empty($settings['google_sheets_tab']) ? $settings['google_sheets_tab'] : 'Leads';
            Google_Sheets_Service::sync_leads($settings['google_sheets_url'], $tab_name, $mapping);
        }

        // 2. Process pending approved emails if automated mode enabled
        if (!empty($settings['sending_mode']) && $settings['sending_mode'] === 'automated') {
            $approved_items = Queue::query(array('status' => 'approved'));
            foreach ($approved_items as $item) {
                // Check rate limits
                if (Email_Service::is_rate_limited()) {
                    break;
                }
                Email_Service::send_email($item['id']);
            }
        }
    }
}
