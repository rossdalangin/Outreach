<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Activity_Log;

$logs = Activity_Log::query(array('number' => 100));
?>
<div class="wrap cc-outreach-wrap">
    <h1>System Activity Log</h1>
    <p>Comprehensive audit trail of outreach actions, drafts, emails, and Google Sheets sync events.</p>

    <!-- Bulk Actions Controls -->
    <div class="alignleft actions bulkactions" style="margin-bottom:10px;">
        <select id="cc-bulk-logs-action">
            <option value="">-- Bulk Actions --</option>
            <option value="delete">Delete Selected Logs</option>
        </select>
        <button id="cc-btn-apply-bulk-logs" class="button action">Apply to Selected</button>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column"><input type="checkbox" id="cc-select-all-logs"></td>
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
                        <th scope="row" class="check-column"><input type="checkbox" class="cc-log-checkbox" value="<?php echo esc_attr($log['id']); ?>"></th>
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
