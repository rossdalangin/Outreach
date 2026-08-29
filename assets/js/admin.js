jQuery(document).ready(function($) {

    // Sync Google Sheets
    $('#cc-btn-sync-sheets').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        $btn.prop('disabled', true).text('Syncing...');

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'sync_sheets',
            nonce: ccOutreachVars.nonce
        }, function(response) {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Sync Google Sheets Now');
            if (response.success) {
                alert('Sync complete! Created: ' + response.data.created + ', Updated: ' + response.data.updated);
                location.reload();
            } else {
                alert('Sync Error: ' + response.data);
            }
        });
    });

    // Generate Draft
    $(document).on('click', '.cc-btn-gen-draft', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var leadId = $btn.data('lead-id');
        $btn.prop('disabled', true).text('Generating...');

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'generate_draft',
            lead_id: leadId,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            $btn.prop('disabled', false).text('Gen Draft');
            if (response.success) {
                alert(response.data);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Approve Queue Item
    $(document).on('click', '.cc-btn-approve-queue', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var queueId = $btn.data('queue-id');

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'approve_queue_item',
            queue_id: queueId,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Send Queue Item
    $(document).on('click', '.cc-btn-send-queue', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var queueId = $btn.data('queue-id');
        $btn.prop('disabled', true).text('Sending...');

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'send_queue_item',
            queue_id: queueId,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            if (response.success) {
                alert(response.data);
                location.reload();
            } else {
                $btn.prop('disabled', false).text('Send Now');
                alert('Send Error: ' + response.data);
            }
        });
    });

    // Modal Add Lead
    $('#cc-btn-open-add-lead-modal').on('click', function(e) {
        e.preventDefault();
        $('#cc-add-lead-modal').show();
    });

    $('.cc-modal-close').on('click', function() {
        $('#cc-add-lead-modal').hide();
    });

    $('#cc-form-add-lead').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postData = {
            action: 'cc_outreach_ajax_action',
            sub_action: 'add_lead',
            nonce: ccOutreachVars.nonce
        };
        $.each(formData, function(i, field) {
            postData[field.name] = field.value;
        });

        $.post(ccOutreachVars.ajax_url, postData, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Quick Status Dropdown
    $(document).on('change', '.cc-select-quick-status', function() {
        var leadId = $(this).data('lead-id');
        var newStatus = $(this).val();
        if (!newStatus) return;

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'update_lead_status',
            lead_id: leadId,
            status: newStatus,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

});
