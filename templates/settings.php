<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Security\Security_Helper;

$settings = get_option('cc_outreach_settings', array());

$openai_key    = !empty($settings['ai_api_key']) ? Security_Helper::decrypt($settings['ai_api_key']) : '';
$anthropic_key = !empty($settings['anthropic_api_key']) ? Security_Helper::decrypt($settings['anthropic_api_key']) : '';
$gemini_key    = !empty($settings['gemini_api_key']) ? Security_Helper::decrypt($settings['gemini_api_key']) : '';

$has_openai    = !empty($openai_key);
$has_anthropic = !empty($anthropic_key);
$has_gemini    = !empty($gemini_key);
?>
<div class="wrap cc-outreach-wrap">
    <h1><span class="dashicons dashicons-admin-settings"></span> CloseClient Outreach Settings</h1>
    <p>Configure system parameters, AI provider credentials, email rate limits, sending windows, and Google Sheets integrations.</p>

    <!-- Emergency Safety Kill-Switch Card -->
    <div class="cc-card cc-card-emergency" style="border-left: 5px solid #dc3545; background: #fff5f5; padding: 15px 20px; margin-bottom:20px;">
        <h3 style="color:#dc3545; margin-top:0;"><span class="dashicons dashicons-warning"></span> Emergency Safety Kill-Switch</h3>
        <label style="font-size:14px; font-weight:600; color:#dc3545;">
            <input type="checkbox" name="settings[kill_switch]" value="1" <?php checked(!empty($settings['kill_switch'])); ?> form="cc-settings-form" />
            FREEZE ALL AUTOMATION & PAUSE OUTBOUND EMAILS IMMEDIATELY
        </label>
        <p class="description" style="margin-bottom:0; margin-top:5px;">Check this box to instantly suspend all background cron tasks, scheduled follow-ups, and queue dispatches.</p>
    </div>

    <!-- Tabbed Settings Layout -->
    <h2 class="nav-tab-wrapper cc-settings-tabs">
        <a href="#tab-general" class="nav-tab nav-tab-active"><span class="dashicons dashicons-shield"></span> General & Safety</a>
        <a href="#tab-ai" class="nav-tab"><span class="dashicons dashicons-lightbulb"></span> AI Providers</a>
        <a href="#tab-email" class="nav-tab"><span class="dashicons dashicons-email"></span> Email & Sending Limits</a>
        <a href="#tab-sheets" class="nav-tab"><span class="dashicons dashicons-google"></span> Google Sheets Integration</a>
    </h2>

    <form method="post" action="" id="cc-settings-form">
        <?php wp_nonce_field('cc_outreach_settings_nonce'); ?>

        <!-- SECTION 1: General & Safety -->
        <div id="tab-general" class="cc-tab-content" style="display:block;">
            <div class="cc-card">
                <h2>General System & Webhook Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="sending_mode">Outreach Operating Mode</label></th>
                        <td>
                            <select id="sending_mode" name="settings[sending_mode]">
                                <option value="draft" <?php selected(!empty($settings['sending_mode']) && $settings['sending_mode'] === 'draft'); ?>>Draft Mode (Manual Inspection & Review)</option>
                                <option value="approval" <?php selected(!empty($settings['sending_mode']) && $settings['sending_mode'] === 'approval'); ?>>Approval Queue Mode (Human Click-to-Send)</option>
                                <option value="automated" <?php selected(!empty($settings['sending_mode']) && $settings['sending_mode'] === 'automated'); ?>>Automated Sending Mode (Hands-Free Background Queue)</option>
                            </select>
                            <p class="description">We recommend starting with <strong>Draft Mode</strong> to manually inspect all AI-generated outreach drafts before sending.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="webhook_secret">Inbound Webhook Secret Token</label></th>
                        <td>
                            <input type="text" id="webhook_secret" name="settings[webhook_secret]" value="<?php echo esc_attr(!empty($settings['webhook_secret']) ? $settings['webhook_secret'] : ''); ?>" class="regular-text" placeholder="e.g. cc_sec_token_98765" />
                            <p class="description">Secret token required in <code>X-CC-Token</code> header or <code>secret_token</code> URL parameter for inbound reply REST API webhooks.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- SECTION 2: AI Providers -->
        <div id="tab-ai" class="cc-tab-content" style="display:none;">
            <div class="cc-card">
                <h2>AI Engine Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ai_provider">Primary AI Provider</label></th>
                        <td>
                            <select id="ai_provider" name="settings[ai_provider]">
                                <option value="openai" <?php selected(!empty($settings['ai_provider']) && $settings['ai_provider'] === 'openai'); ?>>OpenAI API (GPT-4o)</option>
                                <option value="anthropic" <?php selected(!empty($settings['ai_provider']) && $settings['ai_provider'] === 'anthropic'); ?>>Anthropic Claude API (Claude 3.5 Sonnet)</option>
                                <option value="gemini" <?php selected(!empty($settings['ai_provider']) && $settings['ai_provider'] === 'gemini'); ?>>Google Gemini API (gemini-3-flash BETA)</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <!-- OpenAI Group -->
                <div class="cc-settings-subcard" style="background:#f9f9f9; padding:15px; border-radius:6px; margin-bottom:15px; border-left:4px solid #10a37f;">
                    <h3>OpenAI Configuration <?php echo $has_openai ? '<span class="cc-badge" style="background:#28a745; color:#fff;">Connected</span>' : '<span class="cc-badge" style="background:#dc3545; color:#fff;">Not Set</span>'; ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ai_api_key">OpenAI API Key</label></th>
                            <td>
                                <input type="password" id="ai_api_key" name="settings[ai_api_key]" value="<?php echo esc_attr($openai_key); ?>" class="regular-text" placeholder="sk-..." />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ai_model">OpenAI Model</label></th>
                            <td>
                                <input type="text" id="ai_model" name="settings[ai_model]" value="<?php echo esc_attr(!empty($settings['ai_model']) ? $settings['ai_model'] : 'gpt-4o'); ?>" class="regular-text" placeholder="gpt-4o" />
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Anthropic Group -->
                <div class="cc-settings-subcard" style="background:#f9f9f9; padding:15px; border-radius:6px; margin-bottom:15px; border-left:4px solid #d97706;">
                    <h3>Anthropic Claude Configuration <?php echo $has_anthropic ? '<span class="cc-badge" style="background:#28a745; color:#fff;">Connected</span>' : '<span class="cc-badge" style="background:#6c757d; color:#fff;">Optional</span>'; ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="anthropic_api_key">Anthropic API Key</label></th>
                            <td>
                                <input type="password" id="anthropic_api_key" name="settings[anthropic_api_key]" value="<?php echo esc_attr($anthropic_key); ?>" class="regular-text" placeholder="sk-ant-..." />
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Gemini Group -->
                <div class="cc-settings-subcard" style="background:#f9f9f9; padding:15px; border-radius:6px; border-left:4px solid #4285f4;">
                    <h3>Google Gemini Configuration <?php echo $has_gemini ? '<span class="cc-badge" style="background:#28a745; color:#fff;">Connected</span>' : '<span class="cc-badge" style="background:#6c757d; color:#fff;">Optional</span>'; ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="gemini_api_key">Gemini API Key</label></th>
                            <td>
                                <input type="password" id="gemini_api_key" name="settings[gemini_api_key]" value="<?php echo esc_attr($gemini_key); ?>" class="regular-text" placeholder="AIzaSy..." />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gemini_model">Gemini Model</label></th>
                            <td>
                                <input type="text" id="gemini_model" name="settings[gemini_model]" value="<?php echo esc_attr(!empty($settings['gemini_model']) ? $settings['gemini_model'] : 'gemini-3-flash'); ?>" class="regular-text" placeholder="gemini-3-flash" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Email & Sending Limits -->
        <div id="tab-email" class="cc-tab-content" style="display:none;">
            <div class="cc-card">
                <h2>Email Headers & Sender Identity</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="sender_name">Sender Name</label></th>
                        <td>
                            <input type="text" id="sender_name" name="settings[sender_name]" value="<?php echo esc_attr(!empty($settings['sender_name']) ? $settings['sender_name'] : get_bloginfo('name')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sender_email">Sender Email Address</label></th>
                        <td>
                            <input type="email" id="sender_email" name="settings[sender_email]" value="<?php echo esc_attr(!empty($settings['sender_email']) ? $settings['sender_email'] : get_option('admin_email')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="reply_to_email">Reply-To Email Address</label></th>
                        <td>
                            <input type="email" id="reply_to_email" name="settings[reply_to_email]" value="<?php echo esc_attr(!empty($settings['reply_to_email']) ? $settings['reply_to_email'] : get_option('admin_email')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="cc-card">
                <h2>Sending Schedules & Compliance Limits</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="daily_limit">Daily Sending Limit</label></th>
                        <td>
                            <input type="number" id="daily_limit" name="settings[daily_limit]" value="<?php echo esc_attr(!empty($settings['daily_limit']) ? $settings['daily_limit'] : 50); ?>" class="small-text" min="1" max="500" /> emails/day
                            <p class="description">Recommended: <code>30–50</code> emails per day to protect domain reputation.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hourly_limit">Hourly Sending Limit</label></th>
                        <td>
                            <input type="number" id="hourly_limit" name="settings[hourly_limit]" value="<?php echo esc_attr(!empty($settings['hourly_limit']) ? $settings['hourly_limit'] : 10); ?>" class="small-text" min="1" max="50" /> emails/hour
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sending_window_start">Sending Window Hours</label></th>
                        <td>
                            <input type="time" id="sending_window_start" name="settings[sending_window_start]" value="<?php echo esc_attr(!empty($settings['sending_window_start']) ? $settings['sending_window_start'] : '09:00'); ?>" />
                            to
                            <input type="time" id="sending_window_end" name="settings[sending_window_end]" value="<?php echo esc_attr(!empty($settings['sending_window_end']) ? $settings['sending_window_end'] : '17:00'); ?>" />
                            <p class="description">Outreach emails will only dispatch within these hours.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="allow_weekend_sending">Weekend Sending</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="allow_weekend_sending" name="settings[allow_weekend_sending]" value="1" <?php checked(!empty($settings['allow_weekend_sending'])); ?> />
                                Allow email outreach on Saturdays & Sundays
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- SECTION 4: Google Sheets Integration -->
        <div id="tab-sheets" class="cc-tab-content" style="display:none;">
            <div class="cc-card">
                <h2>Google Sheets Integration Parameters</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="google_sheets_url">Google Spreadsheet URL or ID</label></th>
                        <td>
                            <input type="text" id="google_sheets_url" name="settings[google_sheets_url]" value="<?php echo esc_attr(!empty($settings['google_sheets_url']) ? $settings['google_sheets_url'] : ''); ?>" class="regular-text" placeholder="https://docs.google.com/spreadsheets/d/..." />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="google_sheets_tab">Worksheet / Tab Name</label></th>
                        <td>
                            <input type="text" id="google_sheets_tab" name="settings[google_sheets_tab]" value="<?php echo esc_attr(!empty($settings['google_sheets_tab']) ? $settings['google_sheets_tab'] : 'Leads'); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="google_sheets_webhook_url">Google Apps Script Webhook (Write-Back)</label></th>
                        <td>
                            <input type="url" id="google_sheets_webhook_url" name="settings[google_sheets_webhook_url]" value="<?php echo esc_attr(!empty($settings['google_sheets_webhook_url']) ? $settings['google_sheets_webhook_url'] : ''); ?>" class="regular-text" placeholder="https://script.google.com/macros/s/.../exec" />
                            <p class="description">Paste your Web App URL to automatically push lead status and conversation summaries back to Google Sheets.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <p class="submit">
            <input type="submit" name="cc_outreach_save_settings" class="button button-primary button-large" value="Save All Settings" />
        </p>
    </form>
</div>
