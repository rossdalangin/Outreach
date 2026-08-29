<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Queue;
use CloseClient\Outreach\Includes\Models\Lead;

$queue_items = Queue::query(array('number' => 50));
?>
<div class="wrap cc-outreach-wrap">
    <h1>Outreach Queue & Review</h1>
    <p>Review AI-generated personalized outreach drafts before sending to prospects.</p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Recipient</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Status</th>
                <th>AI Rationale</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($queue_items)): ?>
                <?php foreach ($queue_items as $item): ?>
                    <?php $lead = Lead::get($item['lead_id']); ?>
                    <tr>
                        <td>#<?php echo esc_html($item['id']); ?></td>
                        <td>
                            <strong><?php echo esc_html($lead ? $lead['first_name'] . ' ' . $lead['last_name'] : 'Unknown'); ?></strong><br>
                            <code><?php echo esc_html($item['recipient_email']); ?></code>
                        </td>
                        <td><span class="cc-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $item['type']))); ?></span></td>
                        <td><strong><?php echo esc_html($item['subject']); ?></strong></td>
                        <td><span class="cc-status-badge status-<?php echo esc_attr($item['status']); ?>"><?php echo esc_html(ucwords(str_replace('_', ' ', $item['status']))); ?></span></td>
                        <td><small><?php echo esc_html($item['ai_rationale']); ?></small></td>
                        <td>
                            <?php if ($item['status'] === 'awaiting_approval'): ?>
                                <button class="button button-small button-primary cc-btn-approve-queue" data-queue-id="<?php echo esc_attr($item['id']); ?>">Approve</button>
                            <?php endif; ?>
                            <button class="button button-small cc-btn-send-queue" data-queue-id="<?php echo esc_attr($item['id']); ?>">Send Now</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No email items currently in queue.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
