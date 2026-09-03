<?php
if (!defined('ABSPATH')) exit;
?>
<?php
use CloseClient\Outreach\Includes\Models\Campaign;
$campaigns = Campaign::query();
?>
<div class="wrap cc-outreach-wrap">
    <h1 class="wp-heading-inline">Campaigns</h1>
    <button class="page-title-action" id="cc-btn-open-add-campaign-modal">Create New Campaign</button>
    <hr class="wp-header-end">

    <p>Organize lead segments and target specific niches with tailored outreach settings.</p>

    <!-- Bulk Actions Controls -->
    <div class="alignleft actions bulkactions" style="margin-bottom:10px;">
        <select id="cc-bulk-campaigns-action">
            <option value="">-- Bulk Actions --</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button id="cc-btn-apply-bulk-campaigns" class="button action">Apply to Selected</button>
    </div>

    <table class="wp-list-table widefat fixed striped" style="margin-top:15px;">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column"><input type="checkbox" id="cc-select-all-campaigns"></td>
                <th>ID</th>
                <th>Campaign Name</th>
                <th>Target Niche</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($campaigns)): ?>
                <?php foreach ($campaigns as $camp): ?>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" class="cc-campaign-checkbox" value="<?php echo esc_attr($camp['id']); ?>"></th>
                        <td>#<?php echo esc_html($camp['id']); ?></td>
                        <td><strong><?php echo esc_html($camp['name']); ?></strong></td>
                        <td><span class="cc-badge"><?php echo esc_html($camp['target_niche']); ?></span></td>
                        <td><?php echo esc_html($camp['description']); ?></td>
                        <td><span class="cc-badge" style="background:#28a745; color:#fff;"><?php echo esc_html(ucfirst($camp['status'])); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td>#1</td>
                    <td><strong>Coach & Consultant Acquisition</strong></td>
                    <td><span class="cc-badge">Coaches & Consultants</span></td>
                    <td>Custom High-Converting WordPress Websites & Client Lead Systems</td>
                    <td><span class="cc-badge" style="background:#28a745; color:#fff;">Active</span></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal Add Campaign -->
    <div id="cc-add-campaign-modal" class="cc-modal" style="display:none;">
        <div class="cc-modal-content">
            <span class="cc-modal-close">&times;</span>
            <h2>Create New Outreach Campaign</h2>
            <form id="cc-form-add-campaign">
                <p><label>Campaign Name:</label><input type="text" name="name" required class="widefat"></p>
                <p><label>Target Niche:</label><input type="text" name="target_niche" placeholder="Business Coaches, Life Coaches, etc." required class="widefat"></p>
                <p><label>Description:</label><textarea name="description" class="widefat" rows="3"></textarea></p>
                <p><button type="submit" class="button button-primary">Save Campaign</button></p>
            </form>
        </div>
    </div>
</div>
