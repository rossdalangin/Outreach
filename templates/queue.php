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
                            <button class="button button-small cc-btn-edit-queue" data-item="<?php echo esc_attr(json_encode($item)); ?>">Preview & Edit</button>
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
    <!-- Edit / Preview Queue Modal -->
    <div id="cc-edit-queue-modal" class="cc-modal" style="display:none;">
        <div class="cc-modal-content" style="width:600px;">
            <span class="cc-modal-close">&times;</span>
            <h2>Inspect & Edit Outreach Draft</h2>
            <form id="cc-form-edit-queue">
                <input type="hidden" name="queue_id" id="edit_queue_id">
                <p><label>Recipient Email:</label><input type="email" id="edit_queue_recipient" readonly class="widefat" style="background:#f0f0f1;"></p>
                <p><label>Subject Line:</label><input type="text" name="subject" id="edit_queue_subject" required class="widefat"></p>
                <p><label>Email Body Content:</label><textarea name="body_content" id="edit_queue_body" class="widefat" rows="10" required></textarea></p>
                <p><strong>AI Rationale:</strong> <small id="edit_queue_rationale" style="color:#555;"></small></p>
                <p>
                    <button type="submit" class="button button-primary">Save Changes</button>
                    <button type="button" class="button button-secondary cc-modal-close-btn">Cancel</button>
                </p>
            </form>
        </div>
    </div>
</div>
