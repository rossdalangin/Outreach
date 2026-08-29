<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Includes\Models\Queue;

$total_leads   = Lead::count();
$sent_count    = Queue::count_by_status('sent');
$replied_count = Lead::count(array('status' => 'Replied'));
$interested    = Lead::count(array('status' => 'Interested'));
$won           = Lead::count(array('status' => 'Client Won'));

$response_rate = ($sent_count > 0) ? round(($replied_count / $sent_count) * 100, 1) : 0;
$conversion_rate = ($total_leads > 0) ? round(($won / $total_leads) * 100, 1) : 0;
?>
<div class="wrap cc-outreach-wrap">
    <h1>Outreach Analytics & Performance</h1>
    <p>Real-time campaign performance metrics and conversion metrics.</p>

    <div class="cc-stats-grid">
        <div class="cc-stat-card">
            <span class="cc-stat-number"><?php echo esc_html($response_rate); ?>%</span>
            <span class="cc-stat-label">Response Rate</span>
        </div>
        <div class="cc-stat-card highlight-green">
            <span class="cc-stat-number"><?php echo esc_html($conversion_rate); ?>%</span>
            <span class="cc-stat-label">Lead-to-Client Rate</span>
        </div>
        <div class="cc-stat-card highlight-blue">
            <span class="cc-stat-number"><?php echo intval($sent_count); ?></span>
            <span class="cc-stat-label">Total Outbound Sent</span>
        </div>
        <div class="cc-stat-card highlight-gold">
            <span class="cc-stat-number"><?php echo intval($won); ?></span>
            <span class="cc-stat-label">New Web Dev Clients</span>
        </div>
    </div>
</div>
