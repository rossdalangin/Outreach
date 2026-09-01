<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Security\Security_Helper;

$settings = get_option('cc_outreach_settings', array());
$api_key = !empty($settings['ai_api_key']) ? Security_Helper::decrypt($settings['ai_api_key']) : '';
$masked_key = !empty($api_key) ? substr($api_key, 0, 7) . '...' . substr($api_key, -4) : '';
?>
<div class="wrap cc-outreach-wrap">
    <h1>CloseClient Outreach Settings</h1>

    <form method="post" action="">
        <?php wp_nonce_field('cc_outreach_settings_nonce'); ?>

        <!-- Emergency Safety Kill-Switch -->
        <div class="cc-card" style="border-left: 4px solid #d9534f; background: #fff5f5;">
            <h2 style="color:#d9534f;"><span class="dashicons dashicons-warning"></span> Safety Kill-Switch</h2>
            <p>Check this box to instantly freeze and pause all automated email sending and scheduled background jobs.</p>
            <label>
                <input type="checkbox" name="settings[kill_switch]" value="1" <?php checked(!empty($settings['kill_switch'])); ?> />
                <strong>EMERGENCY PAUSE ALL AUTOMATION</strong>
            </label>
        </div>

        <!-- Webhook & API Key Settings -->
        <div class="cc-card">
            <h2><span class="dashicons dashicons-rest-api"></span> Webhook & Secret Token Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="webhook_secret">Inbound Webhook Secret</label></th>
                    <td>
                        <input type="text" id="webhook_secret" name="settings[webhook_secret]" value="<?php echo esc_attr(!empty($settings['webhook_secret']) ? $settings['webhook_secret'] : ''); ?>" class="regular-text" placeholder="e.g. cc_sec_token_12345" />
                        <p class="description">Set a secret token used in <code>X-CC-Token</code> header or <code>secret_token</code> parameter for remote reply webhooks.</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Google Sheets Integration -->
        <div class="cc-card">
            <h2><span class="dashicons dashicons-google"></span> Google Sheets Integration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="gs_url">Spreadsheet URL / ID</label></th>
                    <td>
                        <input type="text" id="gs_url" name="settings[google_sheets_url]" value="<?php echo esc_attr(!empty($settings['google_sheets_url']) ? $settings['google_sheets_url'] : ''); ?>" class="regular-text" placeholder="https://docs.google.com/spreadsheets/d/..." />
                        <p class="description">Enter the public URL or ID of your lead database Google Sheet.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gs_tab">Worksheet / Tab Name</label></th>
                    <td>
                        <input type="text" id="gs_tab" name="settings[google_sheets_tab]" value="<?php echo esc_attr(!empty($settings['google_sheets_tab']) ? $settings['google_sheets_tab'] : 'Leads'); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gs_webhook">Google Apps Script Webhook (Write-Back)</label></th>
                    <td>
                        <input type="url" id="gs_webhook" name="settings[google_sheets_webhook_url]" value="<?php echo esc_attr(!empty($settings['google_sheets_webhook_url']) ? $settings['google_sheets_webhook_url'] : ''); ?>" class="regular-text" placeholder="https://script.google.com/macros/s/.../exec" />
                        <p class="description">Optional Google Apps Script Webhook URL to automatically update rows in Google Sheets when outreach status changes.</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- AI Provider Settings -->
        <div class="cc-card">
            <h2><span class="dashicons dashicons-lightbulb"></span> AI Provider Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ai_provider">AI Provider</label></th>
                    <td>
                        <select id="ai_provider" name="settings[ai_provider]">
                            <option value="openai" <?php selected(!empty($settings['ai_provider']) && $settings['ai_provider'] === 'openai'); ?>>OpenAI API</option>
                            <option value="anthropic" <?php selected(!empty($settings['ai_provider']) && $settings['ai_provider'] === 'anthropic'); ?>>Anthropic Claude API</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="anthropic_api_key">Anthropic API Key</label></th>
                    <td>
                        <input type="password" id="anthropic_api_key" name="settings[anthropic_api_key]" value="<?php echo esc_attr(!empty($settings['anthropic_api_key']) ? \CloseClient\Outreach\Security\Security_Helper::decrypt($settings['anthropic_api_key']) : ''); ?>" class="regular-text" placeholder="sk-ant-..." />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_api_key">API Key</label></th>
                    <td>
                        <input type="password" id="ai_api_key" name="settings[ai_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="sk-..." />
                        <?php if ($masked_key): ?>
                            <p class="description">Currently saved key: <code><?php echo esc_html($masked_key); ?></code></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_model">Model</label></th>
                    <td>
                        <input type="text" id="ai_model" name="settings[ai_model]" value="<?php echo esc_attr(!empty($settings['ai_model']) ? $settings['ai_model'] : 'gpt-4o'); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_temp">Temperature</label></th>
                    <td>
                        <input type="number" step="0.1" min="0" max="1" id="ai_temp" name="settings[ai_temperature]" value="<?php echo esc_attr(isset($settings['ai_temperature']) ? $settings['ai_temperature'] : '0.7'); ?>" class="small-text" />
                    </td>
                </tr>
            </table>
        </div>

        <!-- Email & Sending Controls -->
        <div class="cc-card">
            <h2><span class="dashicons dashicons-email"></span> Email & Sending Limits</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="sending_mode">Outreach Mode</label></th>
                    <td>
                        <select id="sending_mode" name="settings[sending_mode]">
                            <option value="draft" <?php selected(!empty($settings['sending_mode']) && $settings['sending_mode'] === 'draft'); ?>>Draft Mode (Manual Review)</option>
                            <option value="approval" <?php selected(!empty($settings['sending_mode']) && $settings['sending_mode'] === 'approval'); ?>>Approval Queue Mode</option>
                            <option value="automated" <?php selected(!empty($settings['sending_mode']) && $settings['sending_mode'] === 'automated'); ?>>Automated Sending Mode</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="followup_days">Follow-Up Interval (Days)</label></th>
                    <td>
                        <input type="number" id="followup_days" name="settings[followup_days]" value="<?php echo esc_attr(!empty($settings['followup_days']) ? $settings['followup_days'] : 3); ?>" class="small-text" /> days
                        <p class="description">Number of days to wait after sending an initial email before marking Follow-Up 1 as due (e.g. 3 days).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="daily_limit">Daily Sending Limit</label></th>
                    <td>
                        <input type="number" id="daily_limit" name="settings[daily_limit]" value="<?php echo esc_attr(!empty($settings['daily_limit']) ? $settings['daily_limit'] : 50); ?>" class="small-text" /> emails/day
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="hourly_limit">Hourly Sending Limit</label></th>
                    <td>
                        <input type="number" id="hourly_limit" name="settings[hourly_limit]" value="<?php echo esc_attr(!empty($settings['hourly_limit']) ? $settings['hourly_limit'] : 10); ?>" class="small-text" /> emails/hour
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sender_name">Sender Name</label></th>
                    <td>
                        <input type="text" id="sender_name" name="settings[sender_name]" value="<?php echo esc_attr(!empty($settings['sender_name']) ? $settings['sender_name'] : get_bloginfo('name')); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sender_email">Sender Email</label></th>
                    <td>
                        <input type="email" id="sender_email" name="settings[sender_email]" value="<?php echo esc_attr(!empty($settings['sender_email']) ? $settings['sender_email'] : get_option('admin_email')); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <input type="submit" name="cc_outreach_save_settings" class="button button-primary button-large" value="Save All Settings" />
        </p>
    </form>
</div>
