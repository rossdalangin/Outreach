<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap cc-outreach-wrap">
    <h1>Automation Rules Engine</h1>
    <p>Configure automated triggers and IF-THEN workflow rules.</p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Rule Name</th>
                <th>Trigger IF</th>
                <th>Action THEN</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
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
            <tr>
                <td><strong>Prospect Reply Handler</strong></td>
                <td>Incoming Reply Detected</td>
                <td>Analyze Sentiment -> Update Lead Summary -> Set Status <code>Replied / Interested</code></td>
                <td><span class="cc-badge" style="background:#28a745; color:#fff;">Active</span></td>
            </tr>
        </tbody>
    </table>
</div>
