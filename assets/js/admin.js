jQuery(document).ready(function($) {

    // Settings, Docs & Mindmap Tab Switching
    $(document).on('click', '.nav-tab-wrapper a.nav-tab', function(e) {
        var targetTab = $(this).attr('href');
        if (!targetTab || targetTab.indexOf('#') !== 0) return;

        e.preventDefault();
        var $wrap = $(this).closest('.cc-outreach-wrap');

        $wrap.find('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $wrap.find('.cc-tab-content').removeClass('active').hide().attr('style', 'display: none !important;');
        $wrap.find(targetTab).addClass('active').show().attr('style', 'display: block !important;');
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

    // Preset Template Variations dictionary
    var queuePresets = {
        preset_soft_pitch: {
            subject: "Quick observation regarding your mobile booking page",
            body: "Hi,\n\nI came across your website while reviewing top coaching practices in your area. Your client testimonials are impressive!\n\nI noticed your mobile site takes over 3 seconds to load and doesn't feature a direct 1-click consultation booking calendar. For high-ticket coaching, this often leads to lost consultation requests.\n\nWe built a high-converting WordPress framework specifically for coaches that fixes this in under 7 days.\n\nWould you be open to a quick 3-minute video showing 2 specific tweaks to increase your weekly client bookings?\n\nBest regards,\nCloseClient Engineering Team"
        },
        preset_speed_benchmark: {
            subject: "Re: Quick question regarding your mobile booking page",
            body: "Hi,\n\nFollowing up on my last note. Here is a quick performance benchmark comparison between your current site and top-converting coaching platforms:\n\n- Average Coaching Industry Mobile Speed: 1.6s\n- Your Site's Current Mobile Speed: 4.1s\n\nStudies show a 1-second improvement in site load speed boosts consultation booking conversions by up to 21%.\n\nShould I record a 3-minute video showing how we optimize this?\n\nBest regards,\nCloseClient Engineering Team"
        },
        preset_case_study: {
            subject: "Re: Quick question regarding your mobile booking page",
            body: "Hi,\n\nWe recently redesigned a WordPress coaching platform for an executive coach in your space. By streamlining mobile performance and implementing a 1-click booking funnel, they doubled discovery call requests within 30 days.\n\nAre you free for a brief 10-minute chat this Thursday to see if we can get similar results for your practice?\n\nBest regards,\nCloseClient Engineering Team"
        },
        preset_breakup: {
            subject: "Permission to close your file?",
            body: "Hi,\n\nI haven't heard back, so I assume optimizing your website for client bookings isn't a priority right now.\n\nI won't follow up again, but if you ever want to upgrade your WordPress site into an automated client acquisition engine, feel free to reply anytime.\n\nBest of luck with your practice!\n\nBest regards,\nCloseClient Engineering Team"
        }
    };

    // Edit & Preview Queue Modal
    $(document).on('click', '.cc-btn-edit-queue', function(e) {
        e.preventDefault();
        var item = $(this).data('item');
        $('#edit_queue_id').val(item.id);
        $('#edit_queue_recipient').val(item.recipient_email);
        $('#edit_queue_subject').val(item.subject);
        $('#edit_queue_body').val(item.body_content);
        $('#edit_queue_rationale').text(item.ai_rationale);
        $('#cc-select-queue-template').val('');
        $('#cc-edit-queue-modal').show();
    });

    // Handle template preset dropdown selection in Queue Modal
    $('#cc-select-queue-template').on('change', function() {
        var presetKey = $(this).val();
        if (presetKey && queuePresets[presetKey]) {
            $('#edit_queue_subject').val(queuePresets[presetKey].subject);
            $('#edit_queue_body').val(queuePresets[presetKey].body);
        }
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
        $('#edit_lead_source').val(lead.lead_source);
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
                    html += '<thead><tr><th>Name</th><th>Company</th><th>Email</th><th>Website</th><th>Lead Source</th><th>Location</th></tr></thead><tbody>';
                    $.each(response.data.prospects, function(i, item) {
                        var sourceTag = item.lead_source || 'Internet Prospecting Search';
                        html += '<tr><td><strong>' + (item.first_name || '') + ' ' + (item.last_name || '') + '</strong></td>';
                        html += '<td>' + (item.company_name || '') + '</td>';
                        html += '<td><code>' + (item.email || '') + '</code></td>';
                        html += '<td><a href="' + (item.website || '#') + '" target="_blank">' + (item.website || '') + '</a></td>';
                        html += '<td><span class="cc-badge">' + sourceTag + '</span></td>';
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

    // Checkbox "Select All" Toggles
    $('#cc-select-all-leads').on('change', function() {
        $('.cc-lead-checkbox').prop('checked', $(this).is(':checked'));
    });
    $('#cc-select-all-queue').on('change', function() {
        $('.cc-queue-checkbox').prop('checked', $(this).is(':checked'));
    });
    $('#cc-select-all-campaigns').on('change', function() {
        $('.cc-campaign-checkbox').prop('checked', $(this).is(':checked'));
    });
    $('#cc-select-all-logs').on('change', function() {
        $('.cc-log-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Apply Bulk Lead Actions
    $('#cc-btn-apply-bulk-leads').on('click', function(e) {
        e.preventDefault();
        var action = $('#cc-bulk-leads-action').val();
        var selectedIds = $('.cc-lead-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (!action) {
            alert('Please select a bulk action.');
            return;
        }
        if (selectedIds.length === 0) {
            alert('Please select at least one lead record.');
            return;
        }

        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete ' + selectedIds.length + ' selected lead records?')) return;
            $.post(ccOutreachVars.ajax_url, {
                action: 'cc_outreach_ajax_action',
                sub_action: 'bulk_delete_leads',
                ids: selectedIds,
                nonce: ccOutreachVars.nonce
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        } else if (action === 'status_ready' || action === 'status_dnc') {
            var newStatus = (action === 'status_ready') ? 'Ready for First Contact' : 'Do Not Contact';
            $.post(ccOutreachVars.ajax_url, {
                action: 'cc_outreach_ajax_action',
                sub_action: 'bulk_status_leads',
                ids: selectedIds,
                status: newStatus,
                nonce: ccOutreachVars.nonce
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        } else if (action === 'draft') {
            alert('Generating AI drafts for ' + selectedIds.length + ' leads...');
            var completed = 0;
            $.each(selectedIds, function(i, leadId) {
                $.post(ccOutreachVars.ajax_url, {
                    action: 'cc_outreach_ajax_action',
                    sub_action: 'generate_draft',
                    lead_id: leadId,
                    nonce: ccOutreachVars.nonce
                }, function() {
                    completed++;
                    if (completed === selectedIds.length) {
                        alert('All AI drafts generated successfully!');
                        location.reload();
                    }
                });
            });
        }
    });

    // Apply Bulk Queue Actions
    $('#cc-btn-apply-bulk-queue').on('click', function(e) {
        e.preventDefault();
        var action = $('#cc-bulk-queue-action').val();
        var selectedIds = $('.cc-queue-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (!action) {
            alert('Please select a bulk queue action.');
            return;
        }
        if (selectedIds.length === 0) {
            alert('Please select at least one queue item.');
            return;
        }

        if (!confirm('Are you sure you want to execute bulk action "' + action + '" on ' + selectedIds.length + ' items?')) return;

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'bulk_queue_action',
            queue_action: action,
            ids: selectedIds,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Apply Bulk Campaign Actions
    $('#cc-btn-apply-bulk-campaigns').on('click', function(e) {
        e.preventDefault();
        var action = $('#cc-bulk-campaigns-action').val();
        var selectedIds = $('.cc-campaign-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (!action || selectedIds.length === 0) {
            alert('Please select campaign records and a bulk action.');
            return;
        }

        if (!confirm('Delete ' + selectedIds.length + ' selected campaigns?')) return;

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'bulk_delete_campaigns',
            ids: selectedIds,
            nonce: ccOutreachVars.nonce
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Apply Bulk Log Actions
    $('#cc-btn-apply-bulk-logs').on('click', function(e) {
        e.preventDefault();
        var action = $('#cc-bulk-logs-action').val();
        var selectedIds = $('.cc-log-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (!action || selectedIds.length === 0) {
            alert('Please select log records to delete.');
            return;
        }

        $.post(ccOutreachVars.ajax_url, {
            action: 'cc_outreach_ajax_action',
            sub_action: 'bulk_delete_logs',
            ids: selectedIds,
            nonce: ccOutreachVars.nonce
        }, function(response) {
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
