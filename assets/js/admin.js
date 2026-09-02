jQuery(document).ready(function($) {

    // Settings, Docs & Mindmap Tab Switching
    $(document).on('click', '.nav-tab-wrapper a.nav-tab', function(e) {
        var targetTab = $(this).attr('href');
        if (!targetTab || targetTab.indexOf('#') !== 0) return;

        e.preventDefault();
        var $wrap = $(this).closest('.cc-outreach-wrap');

        $wrap.find('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $wrap.find('.cc-tab-content').hide().css('display', 'none');
        $wrap.find(targetTab).css('display', 'block').show();
    });

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

    // Edit & Preview Queue Modal
    $(document).on('click', '.cc-btn-edit-queue', function(e) {
        e.preventDefault();
        var item = $(this).data('item');
        $('#edit_queue_id').val(item.id);
        $('#edit_queue_recipient').val(item.recipient_email);
        $('#edit_queue_subject').val(item.subject);
        $('#edit_queue_body').val(item.body_content);
        $('#edit_queue_rationale').text(item.ai_rationale);
        $('#cc-edit-queue-modal').show();
    });

    $('.cc-modal-close-btn').on('click', function() {
        $('.cc-modal').hide();
    });

    $('#cc-form-edit-queue').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postData = {
            action: 'cc_outreach_ajax_action',
            sub_action: 'update_queue_item',
            nonce: ccOutreachVars.nonce
        };
        $.each(formData, function(i, field) {
            postData[field.name] = field.value;
        });

        $.post(ccOutreachVars.ajax_url, postData, function(response) {
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

    // Lead Editing & Deleting
    $(document).on('click', '.cc-btn-edit-lead', function(e) {
        e.preventDefault();
        var lead = $(this).data('lead');
        $('#edit_lead_id').val(lead.id);
        $('#edit_first_name').val(lead.first_name);
        $('#edit_last_name').val(lead.last_name);
        $('#edit_company_name').val(lead.company_name);
        $('#edit_email').val(lead.email);
        $('#edit_website').val(lead.website);
        $('#edit_niche').val(lead.niche);
        $('#edit_status').val(lead.status);
        $('#edit_notes').val(lead.notes);
        $('#cc-edit-lead-modal').show();
    });

    $('#cc-form-edit-lead').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postData = {
            action: 'cc_outreach_ajax_action',
            sub_action: 'update_lead_details',
            nonce: ccOutreachVars.nonce
        };
        $.each(formData, function(i, field) {
            postData[field.name] = field.value;
        });

        $.post(ccOutreachVars.ajax_url, postData, function(response) {
            if (response.success) {
                alert(response.data);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    $(document).on('click', '.cc-btn-delete-lead', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this lead?')) return;
        var leadId = $(this).data('lead-id');

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'delete_lead',
            lead_id: leadId,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Internet Lead Discovery / Prospecting
    $('#cc-form-find-leads').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#cc-btn-run-prospecting');
        $btn.prop('disabled', true).text('Searching Internet & Importing...');

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'discover_leads',
            industry: $('#prospect_industry').val(),
            location: $('#prospect_location').val(),
            quantity: $('#prospect_quantity').val(),
            nonce: ccOutreachVars.nonce
        }, function(response) {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> Search Internet & Import Leads');
            if (response.success) {
                $('#cc-prospecting-results').show();
                var html = '<p style="color:green; font-weight:bold; font-size:15px;"><span class="dashicons dashicons-yes-alt"></span> Discovered ' + response.data.total_found + ' prospects in ' + response.data.industry + '. Added ' + response.data.new_added + ' new leads to database and updated Google Sheets webhook!</p>';
                if (response.data.prospects && response.data.prospects.length > 0) {
                    html += '<table class="wp-list-table widefat fixed striped" style="margin-top:15px;">';
                    html += '<thead><tr><th>Name</th><th>Company</th><th>Email</th><th>Website</th><th>Location</th></tr></thead><tbody>';
                    $.each(response.data.prospects, function(i, item) {
                        html += '<tr><td><strong>' + (item.first_name || '') + ' ' + (item.last_name || '') + '</strong></td>';
                        html += '<td>' + (item.company_name || '') + '</td>';
                        html += '<td><code>' + (item.email || '') + '</code></td>';
                        html += '<td><a href="' + (item.website || '#') + '" target="_blank">' + (item.website || '') + '</a></td>';
                        html += '<td>' + (item.location || '') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                }
                $('#cc-prospecting-output').html(html);
            } else {
                alert('Search Error: ' + response.data);
            }
        });
    });

    // Modal Add Lead
    $('#cc-btn-open-add-lead-modal').on('click', function(e) {
        e.preventDefault();
        $('#cc-add-lead-modal').show();
    });

    $('.cc-modal-close').on('click', function() {
        $('.cc-modal').hide();
    });

    // Campaign modal
    $('#cc-btn-open-add-campaign-modal').on('click', function(e) {
        e.preventDefault();
        $('#cc-add-campaign-modal').show();
    });

    $('#cc-form-add-campaign').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postData = {
            action: 'cc_outreach_ajax_action',
            sub_action: 'create_campaign',
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

    // Rule modal
    $('#cc-btn-open-add-rule-modal').on('click', function(e) {
        e.preventDefault();
        $('#cc-add-rule-modal').show();
    });

    $('#cc-form-add-rule').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postData = {
            action: 'cc_outreach_ajax_action',
            sub_action: 'create_rule',
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

    // CSV Export
    $('#cc-btn-export-leads-csv').on('click', function(e) {
        e.preventDefault();
        window.location.href = ccOutreachVars.ajax_url + '?action=cc_outreach_ajax_action&sub_action=export_leads_csv&nonce=' + ccOutreachVars.nonce;
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
