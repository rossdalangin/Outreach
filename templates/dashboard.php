<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Includes\Models\Queue;

$total_leads     = Lead::count();
$new_leads       = Lead::count(array('status' => 'New Lead'));
$awaiting_appr   = Queue::count_by_status('awaiting_approval');
$sent_count      = Queue::count_by_status('sent');
$replied_count   = Lead::count(array('status' => 'Replied'));
$interested_cnt  = Lead::count(array('status' => 'Interested'));
$won_count       = Lead::count(array('status' => 'Client Won'));
?>
<div class="wrap cc-outreach-wrap">
    <h1><span class="dashicons dashicons-paperplane"></span> CloseClient Outreach Dashboard</h1>
    <div class="cc-header-notice">
        <p>Targeting Business & Life Coaches, Consultants, and Agencies with Personalized AI Outreach.</p>
    </div>

    <!-- Stat Grid -->
    <div class="cc-stats-grid">
        <div class="cc-stat-card">
            <span class="cc-stat-number"><?php echo intval($total_leads); ?></span>
            <span class="cc-stat-label">Total Leads</span>
        </div>
        <div class="cc-stat-card">
            <span class="cc-stat-number"><?php echo intval($new_leads); ?></span>
            <span class="cc-stat-label">New Leads</span>
        </div>
        <div class="cc-stat-card highlight-orange">
            <span class="cc-stat-number"><?php echo intval($awaiting_appr); ?></span>
            <span class="cc-stat-label">Awaiting Approval</span>
        </div>
        <div class="cc-stat-card highlight-blue">
            <span class="cc-stat-number"><?php echo intval($sent_count); ?></span>
            <span class="cc-stat-label">Emails Sent</span>
        </div>
        <div class="cc-stat-card highlight-purple">
            <span class="cc-stat-number"><?php echo intval($replied_count); ?></span>
            <span class="cc-stat-label">Replies Received</span>
        </div>
        <div class="cc-stat-card highlight-green">
            <span class="cc-stat-number"><?php echo intval($interested_cnt); ?></span>
            <span class="cc-stat-label">Interested Leads</span>
        </div>
        <div class="cc-stat-card highlight-gold">
            <span class="cc-stat-number"><?php echo intval($won_count); ?></span>
            <span class="cc-stat-label">Clients Won</span>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="cc-action-bar">
        <button id="cc-btn-sync-sheets" class="button button-primary button-large"><span class="dashicons dashicons-update"></span> Sync Google Sheets Now</button>
        <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-queue'); ?>" class="button button-secondary button-large"><span class="dashicons dashicons-email-alt"></span> Review Queue (<?php echo intval($awaiting_appr); ?>)</a>
    </div>

    <!-- Today's Action Items Section -->
    <div class="cc-card">
        <h2>Today's Attention Items</h2>
        <p>Leads ready for initial outreach research and email generation:</p>
        <?php
        $ready_leads = Lead::query(array('status' => 'New Lead', 'number' => 5));
        if (!empty($ready_leads)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Lead Name</th>
                        <th>Company</th>
                        <th>Niche</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ready_leads as $l): ?>
                        <tr>
                            <td><strong><?php echo esc_html($l['first_name'] . ' ' . $l['last_name']); ?></strong></td>
                            <td><?php echo esc_html($l['company_name']); ?></td>
                            <td><span class="cc-badge"><?php echo esc_html($l['niche']); ?></span></td>
                            <td><?php echo esc_html($l['email']); ?></td>
                            <td>
                                <button class="button button-small cc-btn-gen-draft" data-lead-id="<?php echo esc_attr($l['id']); ?>">Generate AI Draft</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><em>No pending new leads require immediate action right now. All caught up!</em></p>
        <?php endif; ?>
    </div>
</div>
