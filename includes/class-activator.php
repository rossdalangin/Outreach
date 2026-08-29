<?php
namespace CloseClient\Outreach\Includes;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Database\DB_Schema;

class Activator {

    /**
     * Run activation procedures.
     */
    public static function activate() {
        require_once CC_OUTREACH_PLUGIN_DIR . 'database/class-db-schema.php';
        DB_Schema::create_tables();

        // Initialize default options if not exists
        if (!get_option('cc_outreach_settings')) {
            $default_settings = array(
                'sending_mode' => 'draft', // draft, approval, automated
                'daily_limit' => 50,
                'hourly_limit' => 10,
                'max_followups' => 2,
                'ai_provider' => 'openai',
                'ai_model' => 'gpt-4o',
                'ai_temperature' => 0.7,
                'ai_max_tokens' => 500,
                'kill_switch' => false,
                'google_sheets_url' => '',
                'google_sheets_tab' => 'Leads',
                'column_mapping' => array(),
                'sending_window_start' => '09:00',
                'sending_window_end' => '17:00',
                'sender_name' => get_bloginfo('name'),
                'sender_email' => get_option('admin_email'),
                'reply_to_email' => get_option('admin_email'),
            );
            update_option('cc_outreach_settings', $default_settings);
        }

        // Schedule cron event
        if (!wp_next_scheduled('cc_outreach_cron_event')) {
            wp_schedule_event(time(), 'hourly', 'cc_outreach_cron_event');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
