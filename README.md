# CloseClient Outreach WordPress Plugin & System Process Guide

**CloseClient Outreach** is a lightweight, AI-powered outreach CRM and automated client acquisition system built specifically for web development agencies targeting coaches, consultants, and professional service providers.

---

## Master 5-Stage Outreach Process Flowchart

```
[ STEP 1: Configuration & API Setup ]
               │
               ▼
[ STEP 2: Lead Prospecting & Google Sheets ]
               │
               ▼
[ STEP 3: AI Personalization & Human Review ]
               │
               ▼
[ STEP 4: Sending Execution & Safety Compliance ]
               │
               ▼
[ STEP 5: Reply Analysis & Client Conversion ]
```

---

## Step-by-Step Implementation Guide

### Step 1: Initial Setup & API Configuration
1. Activate **CloseClient Outreach** in WordPress Admin (**Plugins > Installed Plugins**).
2. Go to **CloseClient Outreach > Settings**.
3. Select your AI Provider (**OpenAI API** or **Anthropic Claude API**) and enter your API key (`sk-...` or `sk-ant-...`). API keys are encrypted at rest using AES-256-CBC.
4. Select `Draft Mode (Manual Review)` as your Outreach Mode to review all generated drafts before sending.
5. Set your **Sender Name** (e.g. `Alex from CloseClient`) and **Sender Email**.
6. Set an **Inbound Webhook Secret** token (e.g. `cc_sec_token_12345`) for external reply webhook security.

### Step 2: Lead Sourcing & Google Sheets Integration
1. **Find Leads**: Go to **CloseClient Outreach > Find Leads**. Select your target industry (e.g. `Executive Coach`) and location (e.g. `Austin, TX`), then click **Search Internet & Import Leads**.
2. **Google Sheets Import**: Upload `CloseClient_Outreach_Leads_Template.xlsx` or `.csv` to Google Drive and open in Google Sheets.
3. **Google Apps Script Write-Back**: In Google Sheets, open *Extensions > Apps Script*, paste the webhook handler provided in **CloseClient Outreach > Docs & Growth**, deploy as Web App, and paste the Web App URL into Settings.

### Step 3: AI Personalization & Human Review Queue
1. **Generate Draft**: On the **Leads** screen, click **Draft** next to any new lead. The AI analyzes the lead's business, website, and niche to craft a personalized outreach draft.
2. **Review Queue**: Go to **Outreach Queue** to inspect the draft body, subject line, recipient, and AI rationale.
3. **Approve / Send**: Click **Approve** to authorize the draft or **Send Now** to dispatch immediately.

### Step 4: Sending Controls & Outreach Safety
1. **Sending Rate Limits**: Set daily limits (e.g. `50 emails/day`) and hourly limits (e.g. `10 emails/hour`) in Settings.
2. **Sending Window Restrictions**: Configure sending start and end times (e.g. `09:00` to `17:00`) and disable weekend sending.
3. **Automatic Suppression**: Contacts marked `Unsubscribed`, `Do Not Contact`, `Not Interested`, or `Client Won` are automatically blocked from automated sends.
4. **Kill-Switch**: Enable the **EMERGENCY PAUSE ALL AUTOMATION** kill-switch in Settings to instantly freeze all background tasks if needed.

### Step 5: Prospect Reply Analysis & Client Conversion
1. **Inbound Reply Processing**: Webhook endpoint `/wp-json/closeclient-outreach/v1/webhook` receives prospect responses.
2. **AI Sentiment Analysis**: The AI extracts interest level, key questions, and updates status (`Interested`, `Meeting Requested`, `Not Interested`).
3. **Google Sheets Write-Back**: Lead status and conversation summary are automatically pushed back to your connected Google Sheet.
4. **Close Web Dev Retainers**: Deliver a 3-minute Loom video website audit, schedule a discovery call, and present a $3,500–$7,500 WordPress Redesign offer.

---

## System Requirements
- WordPress 5.8+
- PHP 7.4+ or PHP 8.0+
- OpenSSL extension enabled for credential encryption
- Active OpenAI or Anthropic API Key

---

## License
GPL-2.0+ License. Built by CloseClient Engineering.
