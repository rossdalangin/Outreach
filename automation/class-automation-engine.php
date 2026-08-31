<?php
namespace CloseClient\Outreach\Automation;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Includes\Models\Queue;
use CloseClient\Outreach\Includes\Models\Activity_Log;
use CloseClient\Outreach\Integrations\AI\AI_Service;
use CloseClient\Outreach\Integrations\Email\Email_Service;

class Automation_Engine {

    /**
     * Trigger action based on lead status change or event
     */
    public static function process_lead_status($lead_id, $status) {
        $lead = Lead::get($lead_id);
        if (!$lead) {
            return false;
        }

        $settings = get_option('cc_outreach_settings', array());
        $sending_mode = !empty($settings['sending_mode']) ? $settings['sending_mode'] : 'draft';

        switch ($status) {
            case 'Ready for First Contact':
            case 'New Lead':
                // Generate AI draft
                $draft = AI_Service::generate_email_draft($lead, 'first_contact');

                // If draft mode is active or automated mode not explicitly enabled, require manual approval
                $queue_status = ($sending_mode === 'automated') ? 'approved' : 'awaiting_approval';

                $queue_id = Queue::insert(array(
                    'lead_id'          => $lead['id'],
                    'campaign_id'      => $lead['campaign_id'],
                    'type'             => 'first_contact',
                    'recipient_email'  => $lead['email'],
                    'subject'          => $draft['subject'],
                    'body_content'     => $draft['body'],
                    'ai_rationale'     => $draft['rationale'],
                    'status'           => $queue_status,
                    'scheduled_at'     => current_time('mysql'),
                ));

                $new_lead_status = ($queue_status === 'approved') ? 'Ready to Send' : 'First Email Draft Created';
                Lead::update($lead['id'], array(
                    'status'      => $new_lead_status,
                    'last_action' => 'Generated AI Outreach Draft (Awaiting Review)',
                ));

                // Update Google Sheet with draft summary if configured
                if (!empty($settings['google_sheets_url'])) {
                    Activity_Log::log('sheet_update_queued', $lead['id'], 'Updated lead draft status for Google Sheet');
                }

                Activity_Log::log('draft_created', $lead['id'], array(
                    'queue_id' => $queue_id,
                    'subject'  => $draft['subject'],
                ));

                // If sending mode is automated, attempt send immediately
                if ($sending_mode === 'automated') {
                    Email_Service::send_email($queue_id);
                }
                break;

            case 'Follow-Up 1 Due':
            case 'Follow-Up 2 Due':
                // Generate Follow-up draft
                $draft = AI_Service::generate_email_draft($lead, 'follow_up');

                $queue_status = ($sending_mode === 'automated') ? 'approved' : 'awaiting_approval';

                $queue_id = Queue::insert(array(
                    'lead_id'          => $lead['id'],
                    'campaign_id'      => $lead['campaign_id'],
                    'type'             => 'follow_up',
                    'recipient_email'  => $lead['email'],
                    'subject'          => $draft['subject'],
                    'body_content'     => $draft['body'],
                    'ai_rationale'     => $draft['rationale'],
                    'status'           => $queue_status,
                    'scheduled_at'     => current_time('mysql'),
                ));

                Lead::update($lead['id'], array(
                    'status'      => 'Awaiting Approval',
                    'last_action' => 'Generated AI Follow-Up Draft',
                ));

                Activity_Log::log('followup_draft_created', $lead['id'], array(
                    'queue_id' => $queue_id,
                ));

                if ($sending_mode === 'automated') {
                    Email_Service::send_email($queue_id);
                }
                break;

            default:
                break;
        }

        return true;
    }

    /**
     * Process incoming reply
     */
    public static function process_incoming_reply($lead_id, $reply_content) {
        $lead = Lead::get($lead_id);
        if (!$lead) {
            return false;
        }

        // Analyze reply with AI
        $analysis = AI_Service::analyze_reply($reply_content, $lead);

        $status_map = array(
            'interested'        => 'Interested',
            'not_interested'    => 'Not Interested',
            'unsubscribed'      => 'Unsubscribed',
            'meeting_requested' => 'Meeting Requested',
            'neutral'           => 'Replied',
        );

        $new_status = isset($status_map[$analysis['sentiment']]) ? $status_map[$analysis['sentiment']] : 'Replied';

        Lead::update($lead['id'], array(
            'status'               => $new_status,
            'conversation_summary' => $analysis['summary'],
            'last_action'          => 'Reply Analyzed: ' . $analysis['recommended_action'],
        ));

        // Write reply status and summary back to Google Sheets
        \CloseClient\Outreach\Integrations\GoogleSheets\Google_Sheets_Service::update_sheet_lead($lead['id'], $new_status, $analysis['summary']);

        Activity_Log::log('reply_received', $lead['id'], array(
            'sentiment' => $analysis['sentiment'],
            'summary'   => $analysis['summary'],
        ));

        return $analysis;
    }
}
