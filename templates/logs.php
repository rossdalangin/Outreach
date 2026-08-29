<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Activity_Log;

$logs = Activity_Log::query(array('number' => 100));
?>
<div class="wrap cc-outreach-wrap">
    <h1>System Activity Log</h1>
    <p>Comprehensive audit trail of outreach actions, drafts, emails, and Google Sheets sync events.</p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Action</th>
                <th>Lead ID</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><code><?php echo esc_html($log['created_at']); ?></code></td>
                        <td><strong><?php echo esc_html($log['action']); ?></strong></td>
                        <td><?php echo esc_html($log['lead_id'] ? '#' . $log['lead_id'] : 'System'); ?></td>
                        <td><code><?php echo esc_html($log['details']); ?></code></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No activity log entries found yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
