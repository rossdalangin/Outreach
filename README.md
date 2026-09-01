# CloseClient Outreach WordPress Plugin & Architecture Manual

**CloseClient Outreach** is a lightweight, AI-powered outreach CRM and automated client acquisition system built specifically for web development agencies targeting coaches, consultants, and professional service providers.

---

## 1. System Architecture & Component Mapping

```
/closeclient-outreach/
├── closeclient-outreach.php                  # Main Plugin Entry Point & Hooks
├── includes/
│   ├── class-plugin.php                       # Singleton Orchestrator
│   ├── class-activator.php                    # Table Creation & Default Options
│   ├── class-deactivator.php                  # Cron Unscheduling & Cleanup
│   ├── class-rest-api.php                     # Webhook & Sync REST Routes
│   └── models/                                # Data Models (Lead, Queue, Campaign, Rule, Log)
├── admin/
│   └── class-admin-controller.php             # Admin Menu & AJAX Action Handlers
├── database/
│   └── class-db-schema.php                    # dbDelta Custom Tables Setup
├── integrations/
│   ├── ai/                                    # AI Services (OpenAI, Anthropic, Gemini, Scoring)
│   ├── email/                                 # Email Sending, Windows & Merge Tags
│   ├── googlesheets/                          # CSV Parsing & Webhook Write-Back
│   └── prospecting/                           # AI Lead Finder Engine
├── security/
│   └── class-security-helper.php              # Nonces, Capabilities & AES-256 Key Encryption
├── templates/                                 # Dashboard Views & Playbooks
└── assets/                                    # Admin CSS & JS Scripts
```

---

## 2. Master 5-Stage Outreach Process Flowchart

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

## 3. Configuration & REST API Reference

### Environment Options (`wp_options`)
| Option Key | Type | Description |
|---|---|---|
| `ai_provider` | String | `openai`, `anthropic`, or `gemini` |
| `ai_api_key` | String (Encrypted) | OpenAI API Key (`sk-...`) |
| `anthropic_api_key` | String (Encrypted) | Anthropic API Key (`sk-ant-...`) |
| `gemini_api_key` | String (Encrypted) | Google Gemini API Key (`AIzaSy...`) |
| `sending_mode` | String | `draft`, `approval`, or `automated` |
| `daily_limit` | Integer | Max outbound emails per day (Default: `50`) |
| `hourly_limit` | Integer | Max outbound emails per hour (Default: `10`) |
| `webhook_secret` | String | Secret token for REST API Webhook verification |

### REST API Webhook Endpoints
- **POST `/wp-json/closeclient-outreach/v1/webhook`**: Inbound email reply processor. Accepts `email`, `reply_content`, and `X-CC-Token` header.
- **POST `/wp-json/closeclient-outreach/v1/sync`**: Trigger Google Sheets synchronization remotely.

---

## 4. Google Sheets Two-Way Apps Script Webhook

Paste this Web App script into Google Sheets (*Extensions > Apps Script*) to enable automated write-back when leads reply or change status:

```javascript
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
```

---

## 5. License & Credits

GPL-2.0+ License. Developed by CloseClient Engineering.
