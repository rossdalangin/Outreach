<?php
namespace CloseClient\Outreach\Integrations\Email;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Includes\Models\Queue;
use CloseClient\Outreach\Includes\Models\Activity_Log;

class Email_Service {

    /**
     * Check if a lead is suppressed or ineligible for email outreach
     */
    public static function is_suppressed($lead) {
        if (is_numeric($lead)) {
            $lead = Lead::get($lead);
        }

        if (!$lead) {
            return 'Lead not found';
        }

        $suppressed_statuses = array(
            'Unsubscribed',
            'Do Not Contact',
            'Client Won',
            'Not Interested'
        );

        if (in_array($lead['status'], $suppressed_statuses, true)) {
            return sprintf('Lead is in suppressed status: %s', $lead['status']);
        }

        $settings = get_option('cc_outreach_settings', array());
        if (!empty($settings['kill_switch'])) {
            return 'Global emergency kill-switch is active';
        }

        return false;
    }

    /**
     * Check rate limits (hourly & daily)
     */
    public static function is_rate_limited() {
        global $wpdb;
        $settings = get_option('cc_outreach_settings', array());

        $daily_limit  = !empty($settings['daily_limit']) ? intval($settings['daily_limit']) : 50;
        $hourly_limit = !empty($settings['hourly_limit']) ? intval($settings['hourly_limit']) : 10;

        $table_queue = Queue::get_table_name();

        // Count sent today
        $today_start = date('Y-m-d 00:00:00');
        $sent_today = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_queue WHERE status = 'sent' AND sent_at >= %s",
            $today_start
        ));

        if ($sent_today >= $daily_limit) {
            return sprintf('Daily sending limit reached (%d/%d)', $sent_today, $daily_limit);
        }

        // Count sent in the last hour
        $hour_ago = date('Y-m-d H:i:s', time() - 3600);
        $sent_hour = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_queue WHERE status = 'sent' AND sent_at >= %s",
            $hour_ago
        ));

        if ($sent_hour >= $hourly_limit) {
            return sprintf('Hourly sending limit reached (%d/%d)', $sent_hour, $hourly_limit);
        }

        return false;
    }

    /**
     * Check if current time is within configured sending window
     */
    public static function is_within_sending_window() {
        $settings = get_option('cc_outreach_settings', array());

        $start_time = !empty($settings['sending_window_start']) ? $settings['sending_window_start'] : '09:00';
        $end_time   = !empty($settings['sending_window_end']) ? $settings['sending_window_end'] : '17:00';

        $current_time = current_time('H:i');
        $current_day  = current_time('N'); // 1 (Mon) to 7 (Sun)

        // Weekend check if weekend sending disabled (default disabled on weekends 6 & 7)
        $allow_weekends = !empty($settings['allow_weekend_sending']);
        if (!$allow_weekends && ($current_day == 6 || $current_day == 7)) {
            return false;
        }

        if ($current_time >= $start_time && $current_time <= $end_time) {
            return true;
        }

        return false;
    }

    /**
     * Replace dynamic merge tags in text
     */
    public static function replace_merge_tags($text, $lead) {
        if (empty($text) || !is_array($lead)) {
            return $text;
        }

        $tags = array(
            '{first_name}'   => !empty($lead['first_name']) ? $lead['first_name'] : '',
            '{last_name}'    => !empty($lead['last_name']) ? $lead['last_name'] : '',
            '{company_name}' => !empty($lead['company_name']) ? $lead['company_name'] : 'your company',
            '{website}'      => !empty($lead['website']) ? $lead['website'] : '',
            '{niche}'        => !empty($lead['niche']) ? $lead['niche'] : 'coaching/consulting',
            '{location}'     => !empty($lead['location']) ? $lead['location'] : '',
        );

        return str_replace(array_keys($tags), array_values($tags), $text);
    }

    /**
     * Send email via wp_mail
     */
    public static function send_email($queue_id) {
        $queue_item = Queue::get($queue_id);
        if (!$queue_item) {
            return new \WP_Error('queue_not_found', __('Queue item not found.', 'closeclient-outreach'));
        }

        $lead = Lead::get($queue_item['lead_id']);
        if (!$lead) {
            Queue::update($queue_id, array('status' => 'failed', 'error_message' => 'Associated lead not found'));
            return new \WP_Error('lead_not_found', __('Lead not found.', 'closeclient-outreach'));
        }

        // Check suppression
        $suppression_reason = self::is_suppressed($lead);
        if ($suppression_reason) {
            Queue::update($queue_id, array('status' => 'rejected', 'error_message' => $suppression_reason));
            Activity_Log::log('email_suppressed', $lead['id'], $suppression_reason);
            return new \WP_Error('suppressed', $suppression_reason);
        }

        // Check rate limit
        $rate_limit_reason = self::is_rate_limited();
        if ($rate_limit_reason) {
            return new \WP_Error('rate_limited', $rate_limit_reason);
        }

        $settings = get_option('cc_outreach_settings', array());
        $sender_name  = !empty($settings['sender_name']) ? $settings['sender_name'] : get_bloginfo('name');
        $sender_email = !empty($settings['sender_email']) ? $settings['sender_email'] : get_option('admin_email');
        $reply_to     = !empty($settings['reply_to_email']) ? $settings['reply_to_email'] : $sender_email;

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <%s>', $sender_name, $sender_email),
            sprintf('Reply-To: %s', $reply_to),
        );

        // Add custom tracking message ID / thread ID if present
        $thread_id = !empty($lead['email_thread_id']) ? $lead['email_thread_id'] : 'cc_thread_' . $lead['id'] . '_' . time();
        $headers[] = 'X-CC-Outreach-Thread: ' . $thread_id;

        $sent = wp_mail(
            $queue_item['recipient_email'],
            $queue_item['subject'],
            nl2br(esc_html($queue_item['body_content'])),
            $headers
        );

        if ($sent) {
            $now = current_time('mysql');
            Queue::update($queue_id, array(
                'status' => 'sent',
                'sent_at' => $now,
                'error_message' => ''
            ));

            // Update lead status & contact date
            $next_status = ($queue_item['type'] === 'follow_up') ? 'Follow-Up Sent' : 'Email Sent';
            Lead::update($lead['id'], array(
                'status'            => $next_status,
                'last_contact_date' => $now,
                'email_thread_id'   => $thread_id,
                'last_action'       => 'Email sent: ' . $queue_item['subject'],
            ));

            Activity_Log::log('email_sent', $lead['id'], array(
                'queue_id' => $queue_id,
                'subject'  => $queue_item['subject'],
                'to'       => $queue_item['recipient_email'],
            ));

            return true;
        } else {
            Queue::update($queue_id, array(
                'status' => 'failed',
                'error_message' => 'wp_mail returned false'
            ));
            Activity_Log::log('email_failed', $lead['id'], 'wp_mail returned false');
            return new \WP_Error('send_failed', __('Failed to send email via wp_mail.', 'closeclient-outreach'));
        }
    }
}
