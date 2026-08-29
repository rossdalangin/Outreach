<?php
namespace CloseClient\Outreach\Database;

if (!defined('ABSPATH')) {
    exit;
}

class DB_Schema {

    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // 1. Leads Table
        $table_leads = $wpdb->prefix . 'cc_outreach_leads';
        $sql_leads = "CREATE TABLE $table_leads (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id VARCHAR(100) NOT NULL DEFAULT '',
            first_name VARCHAR(100) NOT NULL DEFAULT '',
            last_name VARCHAR(100) NOT NULL DEFAULT '',
            company_name VARCHAR(255) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            website VARCHAR(255) NOT NULL DEFAULT '',
            linkedin_url VARCHAR(255) NOT NULL DEFAULT '',
            niche VARCHAR(100) NOT NULL DEFAULT '',
            location VARCHAR(100) NOT NULL DEFAULT '',
            lead_source VARCHAR(100) NOT NULL DEFAULT '',
            status VARCHAR(50) NOT NULL DEFAULT 'New Lead',
            campaign_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            last_contact_date DATETIME DEFAULT NULL,
            next_followup_date DATETIME DEFAULT NULL,
            followup_count INT(11) NOT NULL DEFAULT 0,
            notes TEXT DEFAULT NULL,
            conversation_summary TEXT DEFAULT NULL,
            email_thread_id VARCHAR(255) NOT NULL DEFAULT '',
            last_action VARCHAR(255) NOT NULL DEFAULT '',
            assigned_to VARCHAR(100) NOT NULL DEFAULT '',
            custom_data LONGTEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id),
            KEY email (email),
            KEY status (status),
            KEY campaign_id (campaign_id)
        ) $charset_collate;";
        dbDelta($sql_leads);

        // 2. Queue Table
        $table_queue = $wpdb->prefix . 'cc_outreach_queue';
        $sql_queue = "CREATE TABLE $table_queue (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id BIGINT(20) UNSIGNED NOT NULL,
            campaign_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            type VARCHAR(50) NOT NULL DEFAULT 'first_contact',
            recipient_email VARCHAR(255) NOT NULL DEFAULT '',
            subject VARCHAR(255) NOT NULL DEFAULT '',
            body_content LONGTEXT DEFAULT NULL,
            ai_rationale TEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'awaiting_approval',
            scheduled_at DATETIME DEFAULT NULL,
            sent_at DATETIME DEFAULT NULL,
            error_message TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_queue);

        // 3. Conversations Table
        $table_conversations = $wpdb->prefix . 'cc_outreach_conversations';
        $sql_conversations = "CREATE TABLE $table_conversations (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id BIGINT(20) UNSIGNED NOT NULL,
            thread_id VARCHAR(255) NOT NULL DEFAULT '',
            message_id VARCHAR(255) NOT NULL DEFAULT '',
            direction VARCHAR(20) NOT NULL DEFAULT 'outbound',
            sender VARCHAR(255) NOT NULL DEFAULT '',
            recipient VARCHAR(255) NOT NULL DEFAULT '',
            subject VARCHAR(255) NOT NULL DEFAULT '',
            body LONGTEXT DEFAULT NULL,
            sentiment VARCHAR(50) NOT NULL DEFAULT 'neutral',
            ai_analysis TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id),
            KEY thread_id (thread_id)
        ) $charset_collate;";
        dbDelta($sql_conversations);

        // 4. Campaigns Table
        $table_campaigns = $wpdb->prefix . 'cc_outreach_campaigns';
        $sql_campaigns = "CREATE TABLE $table_campaigns (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL DEFAULT '',
            description TEXT DEFAULT NULL,
            target_niche VARCHAR(100) NOT NULL DEFAULT '',
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            settings LONGTEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_campaigns);

        // 5. Automation Rules Table
        $table_rules = $wpdb->prefix . 'cc_outreach_automation_rules';
        $sql_rules = "CREATE TABLE $table_rules (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL DEFAULT '',
            trigger_event VARCHAR(100) NOT NULL DEFAULT '',
            condition_status VARCHAR(50) NOT NULL DEFAULT '',
            action_type VARCHAR(100) NOT NULL DEFAULT '',
            action_config LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_rules);

        // 6. Templates Table
        $table_templates = $wpdb->prefix . 'cc_outreach_templates';
        $sql_templates = "CREATE TABLE $table_templates (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL DEFAULT '',
            type VARCHAR(50) NOT NULL DEFAULT 'outreach',
            subject VARCHAR(255) NOT NULL DEFAULT '',
            body LONGTEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_templates);

        // 7. Activity Logs Table
        $table_logs = $wpdb->prefix . 'cc_outreach_activity_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            action VARCHAR(100) NOT NULL DEFAULT '',
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            details TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id)
        ) $charset_collate;";
        dbDelta($sql_logs);
    }
}
