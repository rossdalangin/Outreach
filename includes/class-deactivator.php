<?php
namespace CloseClient\Outreach\Includes;

if (!defined('ABSPATH')) {
    exit;
}

class Deactivator {

    /**
     * Run deactivation procedures.
     */
    public static function deactivate() {
        // Clear scheduled cron jobs
        $timestamp = wp_next_scheduled('cc_outreach_cron_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'cc_outreach_cron_event');
        }

        flush_rewrite_rules();
    }
}
