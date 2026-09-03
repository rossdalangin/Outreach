<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap cc-outreach-wrap">
    <h1><span class="dashicons dashicons-book-alt"></span> Master Step-by-Step Outreach Process & Troubleshooting Guide</h1>
    <p>Comprehensive technical and strategic guide to configuring CloseClient Outreach, securing email deliverability, setting up two-way Google Sheets write-back webhooks, and executing the Agency Growth Playbook.</p>

    <!-- Visual Process Flowchart Banner -->
    <div class="cc-card" style="background:#1d2327; color:#fff; padding:20px; border-radius:8px; margin-bottom:25px;">
        <h2 style="color:#003c58; border-bottom:1px solid #3c434a; padding-bottom:10px; margin-top:0;">SYSTEM OUTREACH PROCESS WORKFLOW</h2>
        <div style="display:flex; justify-content:space-between; align-items:center; text-align:center; flex-wrap:wrap; margin-top:15px; font-weight:600;">
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #007cba;">STEP 1: API & Deliverability</div>
            <div style="font-size:20px; color:#007cba;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #fd7e14;">STEP 2: Lead Sourcing & Sheets</div>
            <div style="font-size:20px; color:#fd7e14;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #6f42c1;">STEP 3: AI Personalization & Review</div>
            <div style="font-size:20px; color:#6f42c1;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #28a745;">STEP 4: Sending & Safety</div>
            <div style="font-size:20px; color:#28a745;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #ffc107;">STEP 5: Reply Analysis & Conversion</div>
        </div>
    </div>

    <!-- Step 1 Card -->
    <div class="cc-card" style="border-left: 4px solid #007cba;">
        <h2><span class="dashicons dashicons-admin-settings"></span> Step 1: System Configuration & Email Deliverability Setup</h2>
        <p>Configure provider credentials and domain deliverability settings in <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-settings'); ?>">Settings</a>:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 1.1 - Choose AI Provider:</strong> Select from <code>OpenAI API</code> (GPT-4o), <code>Anthropic Claude API</code> (Claude 3.5 Sonnet), or <code>Google Gemini API</code> (gemini-3-flash BETA). Credentials are stored encrypted at rest via AES-256-CBC.</li>
            <li><strong>Action 1.2 - Set Outreach Mode:</strong> Keep mode set to <code>Draft Mode (Manual Review)</code> during initial launch to review all generated drafts before sending.</li>
            <li><strong>Action 1.3 - Domain Deliverability Setup (SPF, DKIM, DMARC):</strong>
                <ul>
                    <li><code>SPF Record:</code> Ensure your domain DNS contains <code>v=spf1 include:_spf.google.com ~all</code> (or your SMTP provider's SPF value).</li>
                    <li><code>DKIM Signing:</code> Enable DKIM signing in your email host (Google Workspace or Microsoft 365) to prevent emails landing in spam folders.</li>
                    <li><code>DMARC Policy:</code> Add DNS TXT record for <code>_dmarc.yourdomain.com</code> with <code>v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com</code>.</li>
                </ul>
            </li>
            <li><strong>Action 1.4 - Secret Webhook Token:</strong> Set a secret token (e.g. <code>cc_sec_token_98765</code>) to secure remote reply webhooks.</li>
        </ul>
    </div>

    <!-- Step 2 Card -->
    <div class="cc-card" style="border-left: 4px solid #fd7e14;">
        <h2><span class="dashicons dashicons-search"></span> Step 2: Multi-Source Lead Acquisition & Google Sheets Integration</h2>
        <p>Populate your database using CloseClient's multi-source prospecting engine or Google Sheets synchronization:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 2.1 - Multi-Source Web & LinkedIn Prospecting:</strong> Open <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-prospecting'); ?>">Find Leads</a>. The system queries multiple high-quality web indexes:
                <ul>
                    <li><code>LinkedIn Directory Scraper:</code> Queries <code>site:linkedin.com/in/</code> for verified coach/consultant profile titles and locations.</li>
                    <li><code>Direct Company Site HTML Scraper:</code> Scrapes target company homepages to extract <code>mailto:</code> links and direct domain contact emails.</li>
                    <li><code>Niche Search Directories:</code> Scrapes public coaching directories matching the requested location.</li>
                </ul>
            </li>
            <li><strong>Action 2.2 - Upload Excel Template:</strong> Upload <code>CloseClient_Outreach_Leads_Template.xlsx</code> or <code>.csv</code> to Google Drive and open in Google Sheets.</li>
            <li><strong>Action 2.3 - Apps Script Webhook Code:</strong> Paste this Webhook script into Google Sheets (<em>Extensions > Apps Script</em>), deploy as Web App (Access: <code>Anyone</code>), and copy the URL into Settings:</li>
        </ul>

        <textarea class="widefat" rows="12" readonly style="font-family:monospace; background:#f6f8fa; padding:10px;">
function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Leads") || SpreadsheetApp.getActiveSpreadsheet().getSheets()[0];
    var email = data.email;
    var status = data.status;
    var summary = data.conversation_summary || "";
    var values = sheet.getDataRange().getValues();

    for (var i = 1; i < values.length; i++) {
      if (values[i][4] == email) { // Column E (Email)
        sheet.getRange(i + 1, 11).setValue(status);  // Column K (Status)
        if (summary) sheet.getRange(i + 1, 16).setValue(summary); // Column P (Summary)
        return ContentService.createTextOutput(JSON.stringify({result: "success"})).setMimeType(ContentService.MimeType.JSON);
      }
    }
    // Append row if lead is newly discovered and not found in sheet
    sheet.appendRow([data.lead_id || "", data.first_name || "", data.last_name || "", data.company_name || "", email, "", "", "", "", "Web Prospecting", status, "", summary, "Alex"]);
    return ContentService.createTextOutput(JSON.stringify({result: "appended"})).setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({result: "error", error: err.toString()})).setMimeType(ContentService.MimeType.JSON);
  }
}
</textarea>
    </div>

    <!-- Step 3 Card -->
    <div class="cc-card" style="border-left: 4px solid #6f42c1;">
        <h2><span class="dashicons dashicons-email-alt"></span> Step 3: AI Unique Rephrasing & Anti-Spam Queue Review</h2>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 3.1 - Generate Draft:</strong> Click <strong>Draft</strong> next to any lead on the <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-leads'); ?>">Leads</a> screen.</li>
            <li><strong>Action 3.2 - Dynamic Unique Rephrasing:</strong> The AI automatically incorporates a unique variation seed for every single draft, ensuring sentence structures, vocabulary, and openings differ across all prospects to prevent spam filter grouping.</li>
            <li><strong>Action 3.3 - Inspect Rationale:</strong> Review subject lines, email body, and AI rationale in the <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-queue'); ?>">Outreach Queue</a>.</li>
            <li><strong>Action 3.4 - Human Approval:</strong> Click <strong>Approve</strong> or <strong>Send Now</strong> to dispatch the email.</li>
        </ul>
    </div>

    <!-- Step 4 Card -->
    <div class="cc-card" style="border-left: 4px solid #28a745;">
        <h2><span class="dashicons dashicons-shield"></span> Step 4: Sending Execution & Safety Compliance</h2>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 4.1 - Daily/Hourly Rate Limits:</strong> Keep limits set to <code>30-50 emails/day</code> and <code>10 emails/hour</code> to protect domain reputation.</li>
            <li><strong>Action 4.2 - Time Window Restrictions:</strong> Schedule sending between <code>09:00</code> and <code>17:00</code> during weekdays only.</li>
            <li><strong>Action 4.3 - Automatic Suppression:</strong> Unsubscribed or Do Not Contact leads are automatically blocked from future sending.</li>
        </ul>
    </div>

    <!-- Step 5 Card -->
    <div class="cc-card" style="border-left: 4px solid #ffc107;">
        <h2><span class="dashicons dashicons-chart-line"></span> Step 5: Reply Sentiment Analysis & Client Conversion</h2>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 5.1 - Webhook Capture:</strong> Replies received at <code>/wp-json/closeclient-outreach/v1/webhook</code> are parsed automatically.</li>
            <li><strong>Action 5.2 - Sentiment & Summary:</strong> AI classifies intent (Interested, Meeting Requested, Not Interested) and updates the lead record.</li>
            <li><strong>Action 5.3 - Audit Video & Proposal:</strong> Record a 3-minute Loom video audit and present a $3,500 - $7,500 WordPress Redesign offer.</li>
        </ul>
    </div>

    <!-- FAQ & Troubleshooting Card -->
    <div class="cc-card">
        <h2><span class="dashicons dashicons-sos"></span> Frequently Asked Questions & Troubleshooting</h2>
        <dl style="line-height:1.8; font-size:14px;">
            <dt><strong>Q: Why are my emails landing in spam folders?</strong></dt>
            <dd>A: Ensure SPF, DKIM, and DMARC DNS records are active for your sender domain. Avoid aggressive sales phrases and keep initial outreach under 50 emails per day.</dd>
            <dt><strong>Q: How do I test the Google Sheets webhook?</strong></dt>
            <dd>A: Click <strong>Sync Google Sheets Now</strong> on the Dashboard. Check the <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-logs'); ?>">Activity Log</a> for sync response logs.</dd>
            <dt><strong>Q: What if I need to stop all sending immediately?</strong></dt>
            <dd>A: Go to Settings and check <strong>EMERGENCY PAUSE ALL AUTOMATION</strong>. All cron jobs and queue dispatchers freeze instantly.</dd>
        </dl>
    </div>
</div>
