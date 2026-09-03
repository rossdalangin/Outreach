<?php
if (!defined('ABSPATH')) exit;

use CloseClient\Outreach\Includes\Models\Lead;
use CloseClient\Outreach\Automation\Status_Workflow;

$status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : '';
$search        = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$leads = Lead::query(array(
    'status' => $status_filter,
    'search' => $search,
    'number' => 50,
));
$all_statuses = Status_Workflow::get_all_statuses();
?>
<div class="wrap cc-outreach-wrap">
    <h1 class="wp-heading-inline">Lead Management</h1>
    <button class="page-title-action" id="cc-btn-open-add-lead-modal">Add New Lead</button>
    <button class="page-title-action" id="cc-btn-export-leads-csv">Export CSV</button>
    <hr class="wp-header-end">

    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="closeclient-outreach-leads" />
            <div class="alignleft actions">
                <select name="status_filter">
                    <option value="">-- All Statuses --</option>
                    <?php foreach ($all_statuses as $st => $label): ?>
                        <option value="<?php echo esc_attr($st); ?>" <?php selected($status_filter, $st); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="Filter" />
            </div>
            <p class="search-box">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search leads..." />
                <input type="submit" class="button" value="Search Leads" />
            </p>
        </form>
    </div>

    <!-- Bulk Actions Controls -->
    <div class="alignleft actions bulkactions" style="margin-bottom:10px;">
        <select id="cc-bulk-leads-action">
            <option value="">-- Bulk Actions --</option>
            <option value="delete">Delete Selected</option>
            <option value="draft">Generate AI Drafts</option>
            <option value="status_ready">Set Status: Ready for First Contact</option>
            <option value="status_dnc">Set Status: Do Not Contact</option>
        </select>
        <button id="cc-btn-apply-bulk-leads" class="button action">Apply to Selected</button>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column"><input type="checkbox" id="cc-select-all-leads"></td>
                <th>Lead ID</th>
                <th>Name</th>
                <th>Company</th>
                <th>Email / Website</th>
                <th>Niche</th>
                <th>Lead Source</th>
                <th>Status</th>
                <th>Last Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($leads)): ?>
                <?php foreach ($leads as $l): ?>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" class="cc-lead-checkbox" value="<?php echo esc_attr($l['id']); ?>"></th>
                        <td><code><?php echo esc_html($l['lead_id'] ? $l['lead_id'] : '#' . $l['id']); ?></code></td>
                        <td><strong><?php echo esc_html($l['first_name'] . ' ' . $l['last_name']); ?></strong></td>
                        <td><?php echo esc_html($l['company_name']); ?></td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($l['email']); ?>"><?php echo esc_html($l['email']); ?></a><br>
                            <small><a href="<?php echo esc_url($l['website']); ?>" target="_blank"><?php echo esc_html($l['website']); ?></a></small>
                        </td>
                        <td><span class="cc-badge"><?php echo esc_html($l['niche']); ?></span></td>
                        <td><span class="cc-badge" style="background:#e8f4f8; color:#007cba;"><?php echo esc_html($l['lead_source'] ? $l['lead_source'] : 'Manual Entry'); ?></span></td>
                        <td><span class="cc-status-badge status-<?php echo esc_attr(sanitize_html_class(strtolower(str_replace(' ', '-', $l['status'])))); ?>"><?php echo esc_html($l['status']); ?></span></td>
                        <td><?php echo esc_html($l['last_contact_date'] ? $l['last_contact_date'] : 'Never'); ?></td>
                        <td>
                            <button class="button button-small cc-btn-gen-draft" data-lead-id="<?php echo esc_attr($l['id']); ?>">Draft</button>
                            <button class="button button-small cc-btn-edit-lead" data-lead="<?php echo esc_attr(json_encode($l)); ?>">Edit</button>
                            <button class="button button-small button-link-delete cc-btn-delete-lead" data-lead-id="<?php echo esc_attr($l['id']); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No leads found matching criteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Edit Lead Modal -->
    <div id="cc-edit-lead-modal" class="cc-modal" style="display:none;">
        <div class="cc-modal-content">
            <span class="cc-modal-close">&times;</span>
            <h2>Edit Outreach Lead</h2>
            <form id="cc-form-edit-lead">
                <input type="hidden" name="lead_id" id="edit_lead_id">
                <p><label>First Name:</label><input type="text" name="first_name" id="edit_first_name" required class="widefat"></p>
                <p><label>Last Name:</label><input type="text" name="last_name" id="edit_last_name" class="widefat"></p>
                <p><label>Company Name:</label><input type="text" name="company_name" id="edit_company_name" class="widefat"></p>
                <p><label>Email Address:</label><input type="email" name="email" id="edit_email" required class="widefat"></p>
                <p><label>Website URL:</label><input type="url" name="website" id="edit_website" class="widefat"></p>
                <p><label>Niche:</label><input type="text" name="niche" id="edit_niche" class="widefat"></p>
                <p><label>Lead Source:</label><input type="text" name="lead_source" id="edit_lead_source" class="widefat"></p>
                <p><label>Status:</label>
                    <select name="status" id="edit_status" class="widefat">
                        <?php foreach ($all_statuses as $st => $lbl): ?>
                            <option value="<?php echo esc_attr($st); ?>"><?php echo esc_html($lbl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p><label>Notes:</label><textarea name="notes" id="edit_notes" class="widefat" rows="3"></textarea></p>
                <p><button type="submit" class="button button-primary">Update Lead</button></p>
            </form>
        </div>
    </div>

    <!-- Add Lead Modal -->
    <div id="cc-add-lead-modal" class="cc-modal" style="display:none;">
        <div class="cc-modal-content">
            <span class="cc-modal-close">&times;</span>
            <h2>Add New Outreach Lead</h2>
            <form id="cc-form-add-lead">
                <p><label>First Name:</label><input type="text" name="first_name" required class="widefat"></p>
                <p><label>Last Name:</label><input type="text" name="last_name" class="widefat"></p>
                <p><label>Company Name:</label><input type="text" name="company_name" class="widefat"></p>
                <p><label>Email Address:</label><input type="email" name="email" required class="widefat"></p>
                <p><label>Website URL:</label><input type="url" name="website" class="widefat"></p>
                <p><label>Niche:</label><input type="text" name="niche" placeholder="Business Coach, Consultant, etc." class="widefat"></p>
                <p><button type="submit" class="button button-primary">Save Lead</button></p>
            </form>
        </div>
    </div>
</div>
