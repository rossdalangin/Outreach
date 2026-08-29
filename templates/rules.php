<?php
if (!defined('ABSPATH')) exit;
?>
<?php
use CloseClient\Outreach\Includes\Models\Rule;
$rules = Rule::query();
?>
<div class="wrap cc-outreach-wrap">
    <h1 class="wp-heading-inline">Automation Rules Engine</h1>
    <button class="page-title-action" id="cc-btn-open-add-rule-modal">Create Rule</button>
    <hr class="wp-header-end">

    <p>Configure automated triggers and IF-THEN workflow rules.</p>

    <table class="wp-list-table widefat fixed striped" style="margin-top:15px;">
        <thead>
            <tr>
                <th>Rule Name</th>
                <th>Trigger Condition (IF)</th>
                <th>Action Execution (THEN)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rules)): ?>
                <?php foreach ($rules as $r): ?>
                    <tr>
                        <td><strong><?php echo esc_html($r['name']); ?></strong></td>
                        <td>Status = <code><?php echo esc_html($r['condition_status']); ?></code></td>
                        <td><code><?php echo esc_html($r['action_type']); ?></code></td>
                        <td><span class="cc-badge" style="background:#28a745; color:#fff;"><?php echo esc_html($r['is_active'] ? 'Active' : 'Disabled'); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td><strong>New Lead Initial Contact</strong></td>
                    <td>Status = <code>Ready for First Contact</code></td>
                    <td>Generate AI Draft -> Queue -> Set status <code>Awaiting Approval</code></td>
                    <td><span class="cc-badge" style="background:#28a745; color:#fff;">Active</span></td>
                </tr>
                <tr>
                    <td><strong>Follow-Up 1 Generator</strong></td>
                    <td>Status = <code>Follow-Up 1 Due</code></td>
                    <td>Check Reply -> Generate Contextual Follow-Up Draft -> Queue</td>
                    <td><span class="cc-badge" style="background:#28a745; color:#fff;">Active</span></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal Add Rule -->
    <div id="cc-add-rule-modal" class="cc-modal" style="display:none;">
        <div class="cc-modal-content">
            <span class="cc-modal-close">&times;</span>
            <h2>Create Automation Rule</h2>
            <form id="cc-form-add-rule">
                <p><label>Rule Name:</label><input type="text" name="name" required class="widefat"></p>
                <p><label>Condition Status (IF):</label><input type="text" name="condition_status" placeholder="e.g. Ready for First Contact" required class="widefat"></p>
                <p><label>Action Type (THEN):</label><input type="text" name="action_type" placeholder="e.g. generate_ai_draft" required class="widefat"></p>
                <p><button type="submit" class="button button-primary">Save Rule</button></p>
            </form>
        </div>
    </div>
</div>
