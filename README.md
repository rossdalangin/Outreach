# CloseClient Outreach WordPress Plugin

**CloseClient Outreach** is a lightweight, AI-powered outreach CRM and automated client acquisition plugin built specifically for web development companies targeting coaches, consultants, and service providers.

It operates directly inside the WordPress admin dashboard to manage leads, generate highly personalized AI email drafts, execute follow-ups, summarize prospect replies, enforce sending compliance limits, and synchronize two-way data with Google Sheets.

---

## Key Features

- **WordPress Admin CRM Dashboard**: Manage leads, outreach queues, campaigns, automation rules, activity logs, analytics, and settings.
- **AI-Powered Personalization**: Integrated OpenAI (`gpt-4o`) and Anthropic Claude (`claude-3-5-sonnet`) providers with AES-256-CBC encrypted API key storage.
- **AI Lead Prospecting**: Built-in internet lead finder to discover coaches and consultants by industry/niche and location.
- **Draft Review Mode**: Human-in-the-loop review queue to inspect, edit, and approve drafts before sending.
- **Two-Way Google Sheets Sync**: Import leads from Google Sheets and automatically push lead status and AI conversation summaries back using Google Apps Script Webhooks.
- **Outreach Compliance & Safety**: Daily/hourly sending rate limits, schedule sending window restrictions, suppression lists (`Unsubscribed`, `Do Not Contact`, `Client Won`), and an Emergency Safety Kill-Switch.
- **Inbound Webhook API**: Custom REST API endpoints (`/wp-json/closeclient-outreach/v1/webhook` and `/wp-json/closeclient-outreach/v1/sync`) secured via secret tokens.

---

## Installation & Setup

1. **Upload Plugin**: Copy the `closeclient-outreach` folder into your WordPress site's `wp-content/plugins/` directory.
2. **Activate Plugin**: Navigate to **Plugins > Installed Plugins** in WordPress and click **Activate** under **CloseClient Outreach**.
3. **Configure Settings**:
   - Go to **CloseClient Outreach > Settings**.
   - Select your preferred AI Provider (OpenAI or Anthropic Claude) and enter your API key.
   - Configure your Google Sheets URL and optional Google Apps Script Webhook URL.
   - Set daily/hourly sending limits and sending time windows.
4. **Discover Leads**: Go to **CloseClient Outreach > Find Leads** to start prospecting coaches and consultants in your target niche.

---

## System Requirements

- WordPress 5.8+
- PHP 7.4+ or PHP 8.0+
- OpenSSL extension enabled for credential encryption
- Active OpenAI or Anthropic API Key for AI features

---

## License

GPL-2.0+ License. Built by CloseClient Engineering.
