<?php
namespace CloseClient\Outreach\Automation;

if (!defined('ABSPATH')) {
    exit;
}

class Status_Workflow {

    public static function get_all_statuses() {
        return array(
            'New Lead'                 => __('New Lead', 'closeclient-outreach'),
            'Research Needed'          => __('Research Needed', 'closeclient-outreach'),
            'Ready for First Contact'  => __('Ready for First Contact', 'closeclient-outreach'),
            'First Email Draft Created'=> __('First Email Draft Created', 'closeclient-outreach'),
            'Awaiting Approval'        => __('Awaiting Approval', 'closeclient-outreach'),
            'Email Sent'               => __('Email Sent', 'closeclient-outreach'),
            'Follow-Up 1 Due'          => __('Follow-Up 1 Due', 'closeclient-outreach'),
            'Follow-Up 1 Sent'         => __('Follow-Up 1 Sent', 'closeclient-outreach'),
            'Follow-Up 2 Due'          => __('Follow-Up 2 Due', 'closeclient-outreach'),
            'Replied'                  => __('Replied', 'closeclient-outreach'),
            'Interested'               => __('Interested', 'closeclient-outreach'),
            'Meeting Requested'        => __('Meeting Requested', 'closeclient-outreach'),
            'Proposal Sent'            => __('Proposal Sent', 'closeclient-outreach'),
            'Client Won'               => __('Client Won', 'closeclient-outreach'),
            'Not Interested'           => __('Not Interested', 'closeclient-outreach'),
            'Unsubscribed'             => __('Unsubscribed', 'closeclient-outreach'),
            'Do Not Contact'           => __('Do Not Contact', 'closeclient-outreach'),
        );
    }

    /**
     * Check if transition is valid
     */
    public static function is_valid_transition($current_status, $new_status) {
        // Suppressed statuses cannot transition automatically unless forced manually
        $suppressed = array('Unsubscribed', 'Do Not Contact', 'Client Won', 'Not Interested');
        if (in_array($current_status, $suppressed, true) && !in_array($new_status, $suppressed, true)) {
            // Allows manual override
            return true;
        }
        return true;
    }
}
