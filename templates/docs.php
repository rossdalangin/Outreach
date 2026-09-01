<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap cc-outreach-wrap">
    <h1><span class="dashicons dashicons-book-alt"></span> Master Step-by-Step Outreach Process Guide</h1>
    <p>Follow this complete 5-stage step-by-step process guide to configure CloseClient Outreach, discover high-ticket coaching leads, automate personalized AI outreach, and synchronize responses back to Google Sheets.</p>

    <!-- Visual Process Flowchart Banner -->
    <div class="cc-card" style="background:#1d2327; color:#fff; padding:20px; border-radius:8px; margin-bottom:25px;">
        <h2 style="color:#003c58; border-bottom:1px solid #3c434a; padding-bottom:10px; margin-top:0;">SYSTEM OUTREACH PROCESS WORKFLOW</h2>
        <div style="display:flex; justify-content:space-between; align-items:center; text-align:center; flex-wrap:wrap; margin-top:15px; font-weight:600;">
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #007cba;">STEP 1: Setup & API Keys</div>
            <div style="font-size:20px; color:#007cba;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #fd7e14;">STEP 2: Prospecting & Sheets</div>
            <div style="font-size:20px; color:#fd7e14;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #6f42c1;">STEP 3: AI Draft & Review</div>
            <div style="font-size:20px; color:#6f42c1;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #28a745;">STEP 4: Sending & Safety</div>
            <div style="font-size:20px; color:#28a745;">➔</div>
            <div style="flex:1; background:#2c3338; padding:12px; margin:5px; border-radius:6px; border-top:3px solid #ffc107;">STEP 5: Reply Analysis & Conversion</div>
        </div>
    </div>

    <!-- Step 1 Card -->
    <div class="cc-card" style="border-left: 4px solid #007cba;">
        <h2><span class="dashicons dashicons-admin-settings"></span> Step 1: Initial System Configuration & API Setup</h2>
        <p>Before launching outreach, set up your system credentials and sending safeguards in <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-settings'); ?>">Settings</a>:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 1.1 - API Key Encryption:</strong> Enter your <strong>OpenAI API Key</strong> (<code>sk-...</code>) or <strong>Anthropic Claude API Key</strong> (<code>sk-ant-...</code>). Your key is automatically encrypted at rest using AES-256-CBC.</li>
            <li><strong>Action 1.2 - Select Outreach Mode:</strong> Choose <code>Draft Mode (Manual Review)</code>. This ensures every single AI draft is held in the queue for human review before any email leaves your server.</li>
            <li><strong>Action 1.3 - Sender Identity:</strong> Set your <strong>Sender Name</strong> (e.g., <code>Alex from CloseClient</code>) and <strong>Sender Email</strong> (e.g., <code>alex@closeclient.com</code>).</li>
            <li><strong>Action 1.4 - Inbound Webhook Secret:</strong> Enter a secret string (e.g., <code>cc_sec_token_98765</code>) into the <strong>Inbound Webhook Secret</strong> field to authenticate incoming reply notifications.</li>
        </ul>
    </div>

    <!-- Step 2 Card -->
    <div class="cc-card" style="border-left: 4px solid #fd7e14;">
        <h2><span class="dashicons dashicons-search"></span> Step 2: Lead Sourcing & Google Sheets Integration</h2>
        <p>Populate your outreach database using built-in internet prospecting or Google Sheets import:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 2.1 - Automated Internet Prospecting:</strong> Go to <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-prospecting'); ?>">Find Leads</a>. Select your target niche (e.g. <code>Executive Coach</code>, <code>Business Consultant</code>) and target location (e.g. <code>Austin, TX</code>). Click <strong>Search Internet & Import Leads</strong>.</li>
            <li><strong>Action 2.2 - Upload Excel/CSV Template to Google Sheets:</strong> Upload the provided <code>CloseClient_Outreach_Leads_Template.xlsx</code> or <code>.csv</code> file to your Google Drive and open it in Google Sheets.</li>
            <li><strong>Action 2.3 - Two-Way Google Apps Script Webhook Setup:</strong> In your Google Sheet, open <em>Extensions > Apps Script</em>, paste the script below, deploy as a Web App (set access to <code>Anyone</code>), and copy the Web App URL into Settings:</li>
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
    return ContentService.createTextOutput(JSON.stringify({result: "not_found"})).setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({result: "error", error: err.toString()})).setMimeType(ContentService.MimeType.JSON);
  }
}
</textarea>
    </div>

    <!-- Step 3 Card -->
    <div class="cc-card" style="border-left: 4px solid #6f42c1;">
        <h2><span class="dashicons dashicons-email-alt"></span> Step 3: AI Draft Generation & Human Review Queue</h2>
        <p>Execute personalized draft generation while maintaining complete human review control:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 3.1 - Generate Personalized Draft:</strong> On the <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-leads'); ?>">Leads</a> screen, click <strong>Draft</strong> next to any new lead. The AI analyzes the lead's company, website, and niche to craft a non-pushy outreach email.</li>
            <li><strong>Action 3.2 - Inspect Queue Item:</strong> Open the <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-queue'); ?>">Outreach Queue</a>. Review the recipient, subject line, email body, and AI rationale.</li>
            <li><strong>Action 3.3 - Approve or Edit:</strong> Click <strong>Approve</strong> to mark the draft ready for sending, or click <strong>Send Now</strong> to dispatch immediately.</li>
        </ul>
    </div>

    <!-- Step 4 Card -->
    <div class="cc-card" style="border-left: 4px solid #28a745;">
        <h2><span class="dashicons dashicons-shield"></span> Step 4: Sending Execution & Safety Compliance</h2>
        <p>Ensure long-term domain health and outreach compliance:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 4.1 - Rate Limiting:</strong> Enforce sending limits (e.g. <code>50 emails/day</code> and <code>10 emails/hour</code>) in Settings.</li>
            <li><strong>Action 4.2 - Sending Window Restrictions:</strong> Set sending hours (e.g. <code>09:00</code> to <code>17:00</code>) and disable weekend sending to ensure emails land during business hours.</li>
            <li><strong>Action 4.3 - Automatic Suppression:</strong> Leads marked <code>Unsubscribed</code>, <code>Do Not Contact</code>, <code>Not Interested</code>, or <code>Client Won</code> are automatically blocked from all future automated sends.</li>
            <li><strong>Action 4.4 - Emergency Kill-Switch:</strong> In case of emergency, check the <strong>EMERGENCY PAUSE ALL AUTOMATION</strong> checkbox in Settings to freeze all sending instantly.</li>
        </ul>
    </div>

    <!-- Step 5 Card -->
    <div class="cc-card" style="border-left: 4px solid #ffc107;">
        <h2><span class="dashicons dashicons-chart-line"></span> Step 5: Prospect Reply Analysis & Client Conversion</h2>
        <p>Convert interested replies into high-ticket web development clients:</p>
        <ul style="line-height:1.8; font-size:14px;">
            <li><strong>Action 5.1 - Inbound Reply Capture:</strong> Incoming replies are sent via webhook to <code>/wp-json/closeclient-outreach/v1/webhook</code>.</li>
            <li><strong>Action 5.2 - AI Sentiment & Summary:</strong> The AI analyzes prospect sentiment (Interested, Neutral, Meeting Requested, Unsubscribed) and generates a 2-sentence summary.</li>
            <li><strong>Action 5.3 - Review Conversations:</strong> View summaries and thread history on the <a href="<?php echo admin_url('admin.php?page=closeclient-outreach-conversations'); ?>">Conversations</a> screen.</li>
            <li><strong>Action 5.4 - Deliver Audit Video & Close Retainer:</strong> Send a 3-minute Loom video website audit, schedule a discovery call, and present a $3,500 - $7,500 WordPress Redesign offer paired with a $400/mo retainer.</li>
        </ul>
    </div>
</div>
