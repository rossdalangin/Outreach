<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Lead;

$replied_leads = Lead::query(array('status' => 'Replied'));
$interested_leads = Lead::query(array('status' => 'Interested'));
$conversations = array_merge($replied_leads, $interested_leads);
?>
<div class="wrap cc-outreach-wrap">
    <h1>Conversations & AI Analysis</h1>
    <p>View prospect reply histories, AI summaries, and sentiment analysis.</p>

    <div class="cc-card">
        <?php if (!empty($conversations)): ?>
            <?php foreach ($conversations as $c): ?>
                <div class="cc-conversation-card" style="border-bottom:1px solid #ccc; padding:15px 0;">
                    <h3><?php echo esc_html($c['first_name'] . ' ' . $c['last_name'] . ' - ' . $c['company_name']); ?> <span class="cc-badge"><?php echo esc_html($c['status']); ?></span></h3>
                    <p><strong>Email:</strong> <?php echo esc_html($c['email']); ?> | <strong>Thread ID:</strong> <code><?php echo esc_html($c['email_thread_id']); ?></code></p>
                    <div style="background:#f9f9f9; padding:10px; border-left:4px solid #007cba; margin:10px 0;">
                        <strong>AI Conversation Summary:</strong>
                        <p><?php echo esc_html($c['conversation_summary'] ? $c['conversation_summary'] : 'No summary generated yet.'); ?></p>
                    </div>
                    <p><strong>Last System Action:</strong> <?php echo esc_html($c['last_action']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><em>No active reply conversations detected yet. Responses from email outreach will be automatically processed and summarized here.</em></p>
        <?php endif; ?>
    </div>
</div>
