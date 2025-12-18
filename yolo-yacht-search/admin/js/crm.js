/**
 * CRM Admin JavaScript
 *
 * @package YOLO_Yacht_Search
 * @since 71.0
 */

(function($) {
    'use strict';

    // CRM Module
    var CRM = {
        currentPage: 0,
        pageSize: 50,
        totalCustomers: 0,
        filters: {},

        init: function() {
            this.bindEvents();
            this.loadCustomers();
        },

        bindEvents: function() {
            var self = this;

            // Pagination
            $('#crm-prev-page').on('click', function() {
                if (self.currentPage > 0) {
                    self.currentPage--;
                    self.loadCustomers();
                }
            });

            $('#crm-next-page').on('click', function() {
                if ((self.currentPage + 1) * self.pageSize < self.totalCustomers) {
                    self.currentPage++;
                    self.loadCustomers();
                }
            });

            // Modal handlers
            $('.crm-modal-close, .crm-modal-cancel').on('click', function() {
                $(this).closest('.crm-modal').hide();
            });

            // Add Customer
            $('#crm-add-customer-btn').on('click', function(e) {
                e.preventDefault();
                $('#crm-add-customer-modal').show();
            });

            $('#crm-save-customer-btn').on('click', function() {
                self.saveCustomer();
            });

            // Export
            $('#crm-export-btn').on('click', function(e) {
                e.preventDefault();
                self.exportCustomers();
            });

            // Run Migration
            $('#crm-run-migration-btn').on('click', function(e) {
                e.preventDefault();
                self.runMigration();
            });

            // Status change
            $('#crm-status-select').on('change', function() {
                self.updateStatus($(this).data('customer-id'), $(this).val());
            });

            // Assignment change
            $('#crm-assign-select').on('change', function() {
                self.assignCustomer($(this).data('customer-id'), $(this).val());
            });

            // Log Call
            $('#crm-log-call-btn').on('click', function() {
                $('#crm-log-call-modal').show();
            });

            $('#crm-save-call-btn').on('click', function() {
                self.logCall();
            });

            // Add Note
            $('#crm-add-note-btn').on('click', function() {
                $('#crm-add-note-modal').show();
            });

            $('#crm-save-note-btn').on('click', function() {
                self.addNote();
            });

            // Add Reminder
            $('#crm-add-reminder-btn').on('click', function() {
                // Set default date to tomorrow
                var tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                $('#crm-add-reminder-form input[name="due_date"]').val(tomorrow.toISOString().split('T')[0]);
                $('#crm-add-reminder-modal').show();
            });

            $('#crm-save-reminder-btn').on('click', function() {
                self.addReminder();
            });

            // Complete/Delete Reminder
            $(document).on('change', '.crm-reminder-complete-checkbox', function() {
                var reminderId = $(this).closest('.crm-reminder-item').data('reminder-id');
                self.completeReminder(reminderId, $(this).is(':checked'));
            });

            $(document).on('click', '.crm-reminder-delete', function() {
                var reminderId = $(this).closest('.crm-reminder-item').data('reminder-id');
                if (confirm('Delete this reminder?')) {
                    self.deleteReminder(reminderId);
                }
            });

            // Send Offer
            $('#crm-send-offer-btn').on('click', function() {
                $('#crm-send-offer-modal').show();
            });

            $('#crm-send-offer-submit-btn').on('click', function() {
                self.sendOffer();
            });

            // Manual Booking
            $('#crm-manual-booking-btn').on('click', function() {
                $('#crm-manual-booking-modal').show();
            });

            $('#crm-create-booking-btn').on('click', function() {
                self.createManualBooking();
            });

            // Send Welcome Email
            $(document).on('click', '.crm-send-welcome-email', function() {
                var bookingId = $(this).data('booking-id');
                self.sendWelcomeEmail(bookingId, $(this));
            });

            // Tags
            $('#crm-add-tag-select').on('change', function() {
                var tagId = $(this).val();
                if (tagId) {
                    self.addTag(tagId);
                    $(this).val('');
                }
            });

            $(document).on('click', '.crm-tag-remove', function() {
                var tagId = $(this).data('tag-id');
                self.removeTag(tagId);
            });

            // Close modal on outside click
            $('.crm-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });

            // Quick status change from list view
            $(document).on('change', '.crm-quick-status', function() {
                var $select = $(this);
                var customerId = $select.data('customer-id');
                var newStatus = $select.val();
                var oldStatus = $select.data('current');
                
                if (newStatus === oldStatus) return;
                
                $.ajax({
                    url: yoloCRM.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'yolo_crm_update_status',
                        nonce: yoloCRM.nonce,
                        customer_id: customerId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            $select.data('current', newStatus);
                            // Update background color
                            var statusColors = {
                                'new': '#3b82f6',
                                'contacted': '#8b5cf6',
                                'qualified': '#f59e0b',
                                'proposal_sent': '#ec4899',
                                'negotiating': '#14b8a6',
                                'booked': '#22c55e',
                                'lost': '#ef4444'
                            };
                            $select.css('background-color', statusColors[newStatus] || '#6c757d');
                        } else {
                            alert(response.data.message || 'Error updating status');
                            $select.val(oldStatus);
                        }
                    },
                    error: function() {
                        alert('Error updating status');
                        $select.val(oldStatus);
                    }
                });
            });

            // Floating quick note button
            $(document).on('click', '#crm-floating-note-btn', function() {
                $('#crm-quick-note-modal').show();
                $('#crm-quick-note-text').focus();
            });

            $(document).on('click', '#crm-save-quick-note-btn', function() {
                self.saveQuickNote();
            });

            // Activity type filter
            $(document).on('change', '#crm-activity-filter', function() {
                var filterType = $(this).val();
                if (filterType === '') {
                    $('.crm-timeline-item').show();
                } else {
                    $('.crm-timeline-item').hide();
                    $('.crm-timeline-item[data-type="' + filterType + '"]').show();
                }
            });

            // Duplicate detection on email/phone change
            var duplicateCheckTimer;
            $(document).on('blur', '#crm-new-customer-email, #crm-new-customer-phone', function() {
                clearTimeout(duplicateCheckTimer);
                duplicateCheckTimer = setTimeout(function() {
                    self.checkDuplicate();
                }, 300);
            });
            
            // Ignore duplicate button
            $(document).on('click', '#crm-ignore-duplicate-btn', function() {
                $('#crm-duplicate-warning').hide();
            });
            
            // View duplicate button
            $(document).on('click', '#crm-view-duplicate-btn', function() {
                var duplicateId = $(this).data('duplicate-id');
                if (duplicateId) {
                    window.location.href = yoloCRM.adminUrl + 'admin.php?page=yolo-ys-crm&view=detail&customer=' + duplicateId;
                }
            });
            
            // Export PDF button
            $(document).on('click', '#crm-export-pdf-btn', function() {
                self.exportCustomerPDF($(this).data('customer-id'));
            });
            
            // Merge customer button
            $(document).on('click', '#crm-merge-customer-btn', function() {
                $('#crm-merge-modal').show();
                $('#crm-merge-search').val('').focus();
                $('#crm-merge-results').empty();
                $('#crm-merge-selection').hide();
                $('#crm-merge-warning').hide();
                $('#crm-merge-confirm-btn').prop('disabled', true);
            });
            
            // Merge search
            var mergeSearchTimer;
            $(document).on('input', '#crm-merge-search', function() {
                var query = $(this).val();
                clearTimeout(mergeSearchTimer);
                if (query.length >= 2) {
                    mergeSearchTimer = setTimeout(function() {
                        self.searchForMerge(query);
                    }, 300);
                } else {
                    $('#crm-merge-results').empty();
                }
            });
            
            // Select merge customer
            $(document).on('click', '.crm-merge-result-item', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var email = $(this).data('email');
                
                $('#crm-merge-selected-id').val(id);
                $('#crm-merge-selected-info').html('<strong>' + name + '</strong><br>' + email);
                $('#crm-merge-selection').show();
                $('#crm-merge-warning').show();
                $('#crm-merge-confirm-btn').prop('disabled', false);
                $('#crm-merge-results').empty();
                $('#crm-merge-search').val('');
            });
            
            // Confirm merge
            $(document).on('click', '#crm-merge-confirm-btn', function() {
                self.mergeCustomers();
            });

            // Reminder snooze
            $(document).on('change', '.crm-reminder-snooze', function() {
                var $select = $(this);
                var reminderId = $select.data('reminder-id');
                var duration = $select.val();
                
                if (!duration) return;
                
                $.ajax({
                    url: yoloCRM.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'yolo_crm_snooze_reminder',
                        nonce: yoloCRM.nonce,
                        reminder_id: reminderId,
                        duration: duration
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || 'Error snoozing reminder');
                        }
                    },
                    error: function() {
                        alert('Error snoozing reminder');
                    },
                    complete: function() {
                        $select.val('');
                    }
                });
            });
        },

        loadCustomers: function() {
            var self = this;
            var $tbody = $('#crm-customers-tbody');

            // Get filters from URL
            var urlParams = new URLSearchParams(window.location.search);
            this.filters = {
                search: urlParams.get('search') || '',
                status: urlParams.get('status') || '',
                source: urlParams.get('source') || '',
                assigned_to: urlParams.get('assigned') || ''
            };

            $tbody.html('<tr class="crm-loading-row"><td colspan="8" style="text-align: center; padding: 40px;"><span class="spinner is-active" style="float: none;"></span> Loading customers...</td></tr>');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_get_customers',
                    nonce: yoloCRM.nonce,
                    search: this.filters.search,
                    status: this.filters.status,
                    source: this.filters.source,
                    assigned_to: this.filters.assigned_to,
                    limit: this.pageSize,
                    offset: this.currentPage * this.pageSize
                },
                success: function(response) {
                    if (response.success) {
                        self.totalCustomers = response.data.total;
                        self.renderCustomers(response.data.customers);
                        self.updatePagination();
                    } else {
                        $tbody.html('<tr><td colspan="8" style="text-align: center; padding: 40px;">Error loading customers</td></tr>');
                    }
                },
                error: function() {
                    $tbody.html('<tr><td colspan="8" style="text-align: center; padding: 40px;">Error loading customers</td></tr>');
                }
            });
        },

        renderCustomers: function(customers) {
            var self = this; // Fix: add self reference for this method
            var $tbody = $('#crm-customers-tbody');
            var statuses = yoloCRM.statuses;
            var sources = yoloCRM.sources;
            var statusColors = {
                'new': '#3b82f6',
                'contacted': '#8b5cf6',
                'qualified': '#f59e0b',
                'proposal_sent': '#ec4899',
                'negotiating': '#14b8a6',
                'booked': '#22c55e',
                'lost': '#ef4444'
            };

            if (customers.length === 0) {
                $tbody.html('<tr><td colspan="8" style="text-align: center; padding: 40px;">No customers found</td></tr>');
                return;
            }

            var html = '';
            customers.forEach(function(customer) {
                var name = (customer.first_name + ' ' + customer.last_name).trim() || '<em>No name</em>';
                // Fix: statuses are strings, not objects with .label property
                var statusLabel = statuses[customer.status] || customer.status;
                var statusColor = statusColors[customer.status] || '#6c757d';
                var sourceLabel = sources[customer.source] || customer.source;
                var lastActivity = customer.last_activity_at ? self.formatDate(customer.last_activity_at) : '-';
                var assignee = customer.assigned_to_name || '<em>Unassigned</em>';
                var value = '€' + self.formatNumber(customer.total_revenue || 0);

                html += '<tr data-customer-id="' + customer.id + '">';
                html += '<td class="column-name"><div class="crm-customer-name">' + name + '</div></td>';
                html += '<td class="column-contact"><div class="crm-customer-email">' + customer.email + '</div>';
                if (customer.phone) {
                    html += '<div class="crm-customer-phone" style="font-size: 12px; color: #50575e;">' + customer.phone + '</div>';
                }
                html += '</td>';
                html += '<td class="column-source"><span class="crm-source-badge">' + sourceLabel + '</span></td>';
                html += '<td class="column-status">';
                html += '<select class="crm-quick-status" data-customer-id="' + customer.id + '" data-current="' + customer.status + '" style="background-color: ' + statusColor + '; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;">';
                for (var statusKey in statuses) {
                    var selected = statusKey === customer.status ? ' selected' : '';
                    html += '<option value="' + statusKey + '"' + selected + '>' + statuses[statusKey] + '</option>';
                }
                html += '</select>';
                html += '</td>';
                html += '<td class="column-assigned">' + assignee + '</td>';
                html += '<td class="column-value">' + value + '</td>';
                html += '<td class="column-activity">' + lastActivity + '</td>';
                html += '<td class="column-actions"><div class="crm-row-actions">';
                html += '<a href="' + self.getCustomerUrl(customer.id) + '" class="button button-small">View</a>';
                html += '</div></td>';
                html += '</tr>';
            });

            $tbody.html(html);
        },

        updatePagination: function() {
            var start = this.currentPage * this.pageSize + 1;
            var end = Math.min((this.currentPage + 1) * this.pageSize, this.totalCustomers);

            $('#crm-showing-count').text(this.totalCustomers > 0 ? start + '-' + end : '0');
            $('#crm-total-count').text(this.totalCustomers);

            $('#crm-prev-page').prop('disabled', this.currentPage === 0);
            $('#crm-next-page').prop('disabled', (this.currentPage + 1) * this.pageSize >= this.totalCustomers);
        },

        getCustomerUrl: function(customerId) {
            return 'admin.php?page=yolo-ys-crm&view=detail&customer=' + customerId;
        },

        formatDate: function(dateStr) {
            var date = new Date(dateStr);
            var options = { month: 'short', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        },

        formatNumber: function(num) {
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        },

        saveCustomer: function() {
            var self = this;
            var $form = $('#crm-add-customer-form');
            var $btn = $('#crm-save-customer-btn');

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_create_customer',
                    nonce: yoloCRM.nonce,
                    first_name: $form.find('input[name="first_name"]').val(),
                    last_name: $form.find('input[name="last_name"]').val(),
                    email: $form.find('input[name="email"]').val(),
                    phone: $form.find('input[name="phone"]').val(),
                    company: $form.find('input[name="company"]').val(),
                    notes: $form.find('textarea[name="notes"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#crm-add-customer-modal').hide();
                        $form[0].reset();
                        window.location.href = self.getCustomerUrl(response.data.customer_id);
                    } else {
                        alert(response.data.message || 'Error creating customer');
                    }
                },
                error: function() {
                    alert('Error creating customer');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Add Customer');
                }
            });
        },

        exportCustomers: function() {
            var self = this;
            var $btn = $('#crm-export-btn');

            $btn.text('Exporting...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_export_customers',
                    nonce: yoloCRM.nonce,
                    status: this.filters.status,
                    source: this.filters.source,
                    assigned_to: this.filters.assigned_to
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.url;
                    } else {
                        alert(response.data.message || 'Error exporting customers');
                    }
                },
                error: function() {
                    alert('Error exporting customers');
                },
                complete: function() {
                    $btn.text('Export CSV');
                }
            });
        },

        runMigration: function() {
            var $btn = $('#crm-run-migration-btn');

            if (!confirm('This will import all existing quote requests, contact messages, and bookings into the CRM. Continue?')) {
                return;
            }

            $btn.text('Migrating...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_run_migration',
                    nonce: yoloCRM.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error running migration');
                    }
                },
                error: function() {
                    alert('Error running migration');
                },
                complete: function() {
                    $btn.text('Run Migration');
                }
            });
        },

        updateStatus: function(customerId, status) {
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_update_status',
                    nonce: yoloCRM.nonce,
                    customer_id: customerId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        // Update status badge
                        var statusColors = {
                            'new': '#3b82f6',
                            'contacted': '#8b5cf6',
                            'qualified': '#f59e0b',
                            'proposal_sent': '#ec4899',
                            'negotiating': '#14b8a6',
                            'booked': '#22c55e',
                            'lost': '#ef4444'
                        };
                        var statuses = yoloCRM.statuses;
                        // Fix: statuses are strings, not objects with .label property
                        $('.crm-customer-card .crm-status-badge')
                            .text(statuses[status] || status)
                            .css('background-color', statusColors[status]);
                        
                        // Reload page to show updated timeline
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error updating status');
                    }
                },
                error: function() {
                    alert('Error updating status');
                }
            });
        },

        assignCustomer: function(customerId, userId) {
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_assign_customer',
                    nonce: yoloCRM.nonce,
                    customer_id: customerId,
                    assigned_to: userId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error assigning customer');
                    }
                },
                error: function() {
                    alert('Error assigning customer');
                }
            });
        },

        logCall: function() {
            var self = this;
            var $form = $('#crm-log-call-form');
            var $btn = $('#crm-save-call-btn');

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_log_activity',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    activity_type: $form.find('select[name="call_type"]').val(),
                    subject: $form.find('input[name="subject"]').val(),
                    content: $form.find('textarea[name="notes"]').val(),
                    duration: $form.find('input[name="duration"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#crm-log-call-modal').hide();
                        $form[0].reset();
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error logging call');
                    }
                },
                error: function() {
                    alert('Error logging call');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Log Call');
                }
            });
        },

        addNote: function() {
            var self = this;
            var $form = $('#crm-add-note-form');
            var $btn = $('#crm-save-note-btn');

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_log_activity',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    activity_type: 'note',
                    subject: $form.find('input[name="subject"]').val(),
                    content: $form.find('textarea[name="content"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#crm-add-note-modal').hide();
                        $form[0].reset();
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error adding note');
                    }
                },
                error: function() {
                    alert('Error adding note');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Add Note');
                }
            });
        },

        addReminder: function() {
            var self = this;
            var $form = $('#crm-add-reminder-form');
            var $btn = $('#crm-save-reminder-btn');

            var dueDate = $form.find('input[name="due_date"]').val();
            var dueTime = $form.find('input[name="due_time"]').val() || '09:00';

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_add_reminder',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    reminder_text: $form.find('input[name="reminder_text"]').val(),
                    due_date: dueDate + ' ' + dueTime + ':00',
                    assigned_to: $form.find('select[name="assigned_to"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#crm-add-reminder-modal').hide();
                        $form[0].reset();
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error adding reminder');
                    }
                },
                error: function() {
                    alert('Error adding reminder');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Add Reminder');
                }
            });
        },

        completeReminder: function(reminderId, completed) {
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_complete_reminder',
                    nonce: yoloCRM.nonce,
                    reminder_id: reminderId,
                    completed: completed ? 1 : 0
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error updating reminder');
                    }
                },
                error: function() {
                    alert('Error updating reminder');
                }
            });
        },

        deleteReminder: function(reminderId) {
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_delete_reminder',
                    nonce: yoloCRM.nonce,
                    reminder_id: reminderId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error deleting reminder');
                    }
                },
                error: function() {
                    alert('Error deleting reminder');
                }
            });
        },

        sendOffer: function() {
            var self = this;
            var $form = $('#crm-send-offer-form');
            var $btn = $('#crm-send-offer-submit-btn');

            // Get TinyMCE content if available, otherwise fallback to textarea
            var messageContent = '';
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('crm_offer_message')) {
                messageContent = tinyMCE.get('crm_offer_message').getContent();
            } else {
                messageContent = $('#crm_offer_message').val();
            }

            if (!messageContent || messageContent.trim() === '') {
                alert('Please enter a message');
                return;
            }

            $btn.prop('disabled', true).text('Sending...');

            // Use FormData for file upload support
            var formData = new FormData();
            formData.append('action', 'yolo_crm_send_offer');
            formData.append('nonce', yoloCRM.nonce);
            formData.append('customer_id', crmCustomerId);
            formData.append('from_email', $form.find('input[name="from_email"]').val());
            formData.append('from_name', $form.find('input[name="from_name"]').val());
            formData.append('subject', $form.find('input[name="subject"]').val());
            formData.append('yacht_name', $form.find('input[name="yacht_name"]').val());
            formData.append('offer_amount', $form.find('input[name="offer_amount"]').val());
            formData.append('message', messageContent);

            // Add file attachment if selected
            var fileInput = document.getElementById('crm-offer-attachment');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('offer_attachment', fileInput.files[0]);
            }

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Offer sent successfully!');
                        $('#crm-send-offer-modal').hide();
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error sending offer');
                    }
                },
                error: function() {
                    alert('Error sending offer');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Send Offer');
                }
            });
        },

        createManualBooking: function() {
            var self = this;
            var $form = $('#crm-manual-booking-form');
            var $btn = $('#crm-create-booking-btn');

            $btn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_create_manual_booking',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    bm_reservation_id: $form.find('input[name="bm_reservation_id"]').val(),
                    yacht_name: $form.find('input[name="yacht_name"]').val(),
                    total_price: $form.find('input[name="total_price"]').val(),
                    checkin_date: $form.find('input[name="checkin_date"]').val(),
                    checkout_date: $form.find('input[name="checkout_date"]').val(),
                    notes: $form.find('textarea[name="notes"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        var msg = 'Booking created successfully!\n\n';
                        msg += 'Booking Reference: ' + response.data.booking_reference + '\n';
                        msg += 'Guest Password: ' + response.data.password;
                        alert(msg);
                        $('#crm-manual-booking-modal').hide();
                        $form[0].reset();
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error creating booking');
                    }
                },
                error: function() {
                    alert('Error creating booking');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Create Booking');
                }
            });
        },

        sendWelcomeEmail: function(bookingId, $btn) {
            $btn.prop('disabled', true).text('Sending...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_send_welcome_email',
                    nonce: yoloCRM.nonce,
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Welcome email sent successfully!');
                    } else {
                        alert(response.data.message || 'Error sending email');
                    }
                },
                error: function() {
                    alert('Error sending email');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Send Welcome');
                }
            });
        },

        addTag: function(tagId) {
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_add_tag',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    tag_id: tagId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error adding tag');
                    }
                },
                error: function() {
                    alert('Error adding tag');
                }
            });
        },

        removeTag: function(tagId) {
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_remove_tag',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    tag_id: tagId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error removing tag');
                    }
                },
                error: function() {
                    alert('Error removing tag');
                }
            });
        },

        saveQuickNote: function() {
            var self = this;
            var $modal = $('#crm-quick-note-modal');
            var $btn = $('#crm-save-quick-note-btn');
            var noteText = $('#crm-quick-note-text').val().trim();

            if (!noteText) {
                alert('Please enter a note');
                return;
            }

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_log_activity',
                    nonce: yoloCRM.nonce,
                    customer_id: crmCustomerId,
                    activity_type: 'note',
                    subject: 'Quick Note',
                    content: noteText
                },
                success: function(response) {
                    if (response.success) {
                        $modal.hide();
                        $('#crm-quick-note-text').val('');
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error saving note');
                    }
                },
                error: function() {
                    alert('Error saving note');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Note');
                }
            });
        },
        
        checkDuplicate: function() {
            var email = $('#crm-new-customer-email').val();
            var phone = $('#crm-new-customer-phone').val();
            
            if (!email && !phone) {
                $('#crm-duplicate-warning').hide();
                return;
            }
            
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_check_duplicate',
                    nonce: yoloCRM.nonce,
                    email: email,
                    phone: phone,
                    exclude_id: 0
                },
                success: function(response) {
                    if (response.success && response.data.has_duplicates) {
                        var html = '';
                        response.data.duplicates.forEach(function(dup) {
                            html += '<div class="crm-duplicate-item" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                            html += '<strong>' + (dup.name || 'No name') + '</strong><br>';
                            html += '<small>' + dup.email + (dup.phone ? ' | ' + dup.phone : '') + '</small><br>';
                            html += '<small style="color: #666;">Matched by: ' + dup.match_type + '</small>';
                            html += '</div>';
                        });
                        $('#crm-duplicate-list').html(html);
                        $('#crm-view-duplicate-btn').data('duplicate-id', response.data.duplicates[0].id);
                        $('#crm-duplicate-warning').show();
                    } else {
                        $('#crm-duplicate-warning').hide();
                    }
                }
            });
        },
        
        exportCustomerPDF: function(customerId) {
            var $btn = $('#crm-export-pdf-btn');
            $btn.prop('disabled', true).html('<span class="spinner is-active" style="float: none; margin: 0;"></span> Generating...');
            
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_export_customer_pdf',
                    nonce: yoloCRM.nonce,
                    customer_id: customerId
                },
                success: function(response) {
                    if (response.success) {
                        // Open HTML in new window for printing/saving as PDF
                        var printWindow = window.open('', '_blank');
                        printWindow.document.write(response.data.html);
                        printWindow.document.close();
                        printWindow.onload = function() {
                            printWindow.print();
                        };
                    } else {
                        alert(response.data.message || 'Error generating PDF');
                    }
                },
                error: function() {
                    alert('Error generating PDF');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> Export PDF');
                }
            });
        },
        
        searchForMerge: function(query) {
            var currentCustomerId = $('#crm-merge-customer-btn').data('customer-id');
            
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_get_customers',
                    nonce: yoloCRM.nonce,
                    search: query,
                    limit: 10
                },
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        response.data.customers.forEach(function(customer) {
                            // Exclude current customer
                            if (customer.id != currentCustomerId) {
                                var name = (customer.first_name + ' ' + customer.last_name).trim() || 'No name';
                                html += '<div class="crm-merge-result-item" data-id="' + customer.id + '" data-name="' + name + '" data-email="' + customer.email + '" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">';
                                html += '<strong>' + name + '</strong><br>';
                                html += '<small>' + customer.email + '</small>';
                                html += '</div>';
                            }
                        });
                        if (!html) {
                            html = '<p style="padding: 10px; color: #666;">No matching customers found</p>';
                        }
                        $('#crm-merge-results').html(html);
                    }
                }
            });
        },
        
        mergeCustomers: function() {
            var keepId = $('#crm-merge-selected-id').val();
            var mergeId = $('#crm-merge-customer-btn').data('customer-id');
            var $btn = $('#crm-merge-confirm-btn');
            
            if (!confirm('Are you sure you want to merge these customers? This action cannot be undone.')) {
                return;
            }
            
            $btn.prop('disabled', true).text('Merging...');
            
            $.ajax({
                url: yoloCRM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yolo_crm_merge_customers',
                    nonce: yoloCRM.nonce,
                    keep_id: keepId,
                    merge_id: mergeId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Customers merged successfully!');
                        window.location.href = yoloCRM.adminUrl + 'admin.php?page=yolo-ys-crm&view=detail&customer=' + keepId;
                    } else {
                        alert(response.data.message || 'Error merging customers');
                    }
                },
                error: function() {
                    alert('Error merging customers');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Merge Customers');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        // Only initialize on CRM page
        if ($('.yolo-crm-wrap').length) {
            CRM.init();
        }
    });

    // Expose for debugging
    window.YoloCRM = CRM;

})(jQuery);
