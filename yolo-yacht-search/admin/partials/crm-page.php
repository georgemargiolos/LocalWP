<?php
/**
 * CRM Admin Page
 *
 * Customer relationship management interface with customer list,
 * filters, detail view, activities, and reminders.
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/Partials
 * @since 71.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$crm = YOLO_YS_CRM::get_instance();
$tables = $crm->get_table_names();

// Get current view
$view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'list';
$customer_id = isset($_GET['customer']) ? intval($_GET['customer']) : 0;

// Get filter values
$filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$filter_source = isset($_GET['source']) ? sanitize_text_field($_GET['source']) : '';
$filter_assigned = isset($_GET['assigned']) ? sanitize_text_field($_GET['assigned']) : '';
$filter_search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Get all admins and base managers for assignment dropdown
$assignable_users = get_users(array(
    'role__in' => array('administrator', 'base_manager'),
    'orderby' => 'display_name',
    'order' => 'ASC'
));

// Statuses and sources
$statuses = YOLO_YS_CRM::$statuses;
$sources = YOLO_YS_CRM::$sources;
$activity_types = YOLO_YS_CRM::$activity_types;

// Status colors
$status_colors = array(
    'new' => '#3b82f6',
    'contacted' => '#8b5cf6',
    'qualified' => '#f59e0b',
    'proposal_sent' => '#ec4899',
    'negotiating' => '#14b8a6',
    'booked' => '#22c55e',
    'lost' => '#ef4444'
);

// Activity icons
$activity_icons = array(
    'quote_request' => 'dashicons-clipboard',
    'contact_message' => 'dashicons-format-chat',
    'booking' => 'dashicons-calendar-alt',
    'phone_call_in' => 'dashicons-phone',
    'phone_call_out' => 'dashicons-phone',
    'email_sent' => 'dashicons-email',
    'email_received' => 'dashicons-email-alt',
    'note' => 'dashicons-edit',
    'offer_sent' => 'dashicons-media-document',
    'status_change' => 'dashicons-update',
    'assignment_change' => 'dashicons-admin-users',
    'reminder_created' => 'dashicons-bell',
    'reminder_completed' => 'dashicons-yes-alt'
);
?>

<div class="wrap yolo-crm-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups" style="font-size: 30px; margin-right: 10px;"></span>
        Customer Relationship Management
    </h1>
    
    <?php if ($view === 'list'): ?>
        <a href="#" class="page-title-action" id="crm-add-customer-btn">Add Customer</a>
        <a href="#" class="page-title-action" id="crm-export-btn">Export CSV</a>
        <?php if (!get_option('yolo_crm_migration_completed')): ?>
            <a href="#" class="page-title-action" id="crm-run-migration-btn">Run Migration</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="<?php echo admin_url('admin.php?page=yolo-ys-crm'); ?>" class="page-title-action">← Back to List</a>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <?php if ($view === 'list'): ?>
        <!-- Customer List View -->
        <div class="crm-list-view">
            <!-- Filters -->
            <div class="crm-filters">
                <form method="get" action="" class="crm-filter-form">
                    <input type="hidden" name="page" value="yolo-ys-crm">
                    
                    <div class="crm-filter-row">
                        <div class="crm-filter-item">
                            <label>Search</label>
                            <input type="text" name="search" value="<?php echo esc_attr($filter_search); ?>" placeholder="Name, email, phone...">
                        </div>
                        
                        <div class="crm-filter-item">
                            <label>Status</label>
                            <select name="status">
                                <option value="">All Statuses</option>
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_status, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="crm-filter-item">
                            <label>Source</label>
                            <select name="source">
                                <option value="">All Sources</option>
                                <?php foreach ($sources as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_source, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="crm-filter-item">
                            <label>Assigned To</label>
                            <select name="assigned">
                                <option value="">All</option>
                                <option value="unassigned" <?php selected($filter_assigned, 'unassigned'); ?>>Unassigned</option>
                                <?php foreach ($assignable_users as $user): ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($filter_assigned, $user->ID); ?>><?php echo esc_html($user->display_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="crm-filter-item crm-filter-buttons">
                            <button type="submit" class="button button-primary">Filter</button>
                            <a href="<?php echo admin_url('admin.php?page=yolo-ys-crm'); ?>" class="button">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Status Pipeline Summary -->
            <div class="crm-pipeline-summary">
                <?php
                global $wpdb;
                $pipeline_counts = $wpdb->get_results(
                    "SELECT status, COUNT(*) as count FROM {$tables['customers']} GROUP BY status",
                    OBJECT_K
                );
                ?>
                <?php foreach ($statuses as $key => $label): ?>
                    <div class="crm-pipeline-stage" style="border-top-color: <?php echo $status_colors[$key]; ?>">
                        <span class="stage-count"><?php echo isset($pipeline_counts[$key]) ? $pipeline_counts[$key]->count : 0; ?></span>
                        <span class="stage-label"><?php echo esc_html($label); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Customer Table -->
            <div class="crm-table-container">
                <table class="wp-list-table widefat fixed striped crm-customers-table">
                    <thead>
                        <tr>
                            <th class="column-name">Customer</th>
                            <th class="column-contact">Contact</th>
                            <th class="column-source">Source</th>
                            <th class="column-status">Status</th>
                            <th class="column-assigned">Assigned To</th>
                            <th class="column-value">Value</th>
                            <th class="column-activity">Last Activity</th>
                            <th class="column-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="crm-customers-tbody">
                        <!-- Loaded via AJAX -->
                        <tr class="crm-loading-row">
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <span class="spinner is-active" style="float: none;"></span>
                                Loading customers...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="crm-pagination">
                <span class="crm-pagination-info">Showing <span id="crm-showing-count">0</span> of <span id="crm-total-count">0</span> customers</span>
                <div class="crm-pagination-buttons">
                    <button type="button" class="button" id="crm-prev-page" disabled>← Previous</button>
                    <button type="button" class="button" id="crm-next-page" disabled>Next →</button>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Customer Detail View -->
        <?php
        global $wpdb;
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tables['customers']} WHERE id = %d",
            $customer_id
        ));
        
        if (!$customer) {
            echo '<div class="notice notice-error"><p>Customer not found.</p></div>';
            return;
        }
        
        // Get activities
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, u.display_name as created_by_name 
             FROM {$tables['activities']} a 
             LEFT JOIN {$wpdb->users} u ON a.created_by = u.ID 
             WHERE a.customer_id = %d 
             ORDER BY a.created_at DESC 
             LIMIT 100",
            $customer_id
        ));
        
        // Get reminders
        $reminders = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, u.display_name as assigned_to_name 
             FROM {$tables['reminders']} r 
             LEFT JOIN {$wpdb->users} u ON r.assigned_to = u.ID 
             WHERE r.customer_id = %d 
             ORDER BY r.status ASC, r.due_date ASC",
            $customer_id
        ));
        
        // Get tags
        $customer_tags = $wpdb->get_results($wpdb->prepare(
            "SELECT t.* FROM {$tables['tags']} t 
             JOIN {$tables['customer_tags']} ct ON t.id = ct.tag_id 
             WHERE ct.customer_id = %d",
            $customer_id
        ));
        
        // Get all tags for dropdown
        $all_tags = $wpdb->get_results("SELECT * FROM {$tables['tags']} ORDER BY name");
        
        // Get related bookings
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE customer_email = %s ORDER BY created_at DESC",
            $customer->email
        ));
        
        // Get assignee
        $assignee = $customer->assigned_to ? get_user_by('id', $customer->assigned_to) : null;
        ?>
        
        <div class="crm-detail-view">
            <div class="crm-detail-grid">
                <!-- Left Column: Customer Info -->
                <div class="crm-detail-left">
                    <div class="crm-card crm-customer-card">
                        <div class="crm-card-header">
                            <h2>
                                <?php echo esc_html($customer->first_name . ' ' . $customer->last_name); ?>
                                <?php if (empty(trim($customer->first_name . ' ' . $customer->last_name))): ?>
                                    <em>No name</em>
                                <?php endif; ?>
                            </h2>
                            <span class="crm-status-badge" style="background-color: <?php echo $status_colors[$customer->status]; ?>">
                                <?php echo esc_html($statuses[$customer->status] ?? $customer->status); ?>
                            </span>
                        </div>
                        
                        <div class="crm-card-body">
                            <div class="crm-customer-info">
                                <div class="crm-info-row">
                                    <span class="dashicons dashicons-email"></span>
                                    <a href="mailto:<?php echo esc_attr($customer->email); ?>"><?php echo esc_html($customer->email); ?></a>
                                </div>
                                <?php if ($customer->phone): ?>
                                    <div class="crm-info-row">
                                        <span class="dashicons dashicons-phone"></span>
                                        <a href="tel:<?php echo esc_attr($customer->phone); ?>"><?php echo esc_html($customer->phone); ?></a>
                                    </div>
                                <?php endif; ?>
                                <?php if ($customer->company): ?>
                                    <div class="crm-info-row">
                                        <span class="dashicons dashicons-building"></span>
                                        <?php echo esc_html($customer->company); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="crm-info-row">
                                    <span class="dashicons dashicons-tag"></span>
                                    Source: <?php echo esc_html($sources[$customer->source] ?? $customer->source); ?>
                                </div>
                                <div class="crm-info-row">
                                    <span class="dashicons dashicons-calendar"></span>
                                    Created: <?php echo date('M j, Y', strtotime($customer->created_at)); ?>
                                </div>
                            </div>
                            
                            <!-- Tags -->
                            <div class="crm-tags-section">
                                <h4>Tags</h4>
                                <div class="crm-tags-list" id="crm-tags-list">
                                    <?php foreach ($customer_tags as $tag): ?>
                                        <span class="crm-tag" style="background-color: <?php echo esc_attr($tag->color); ?>" data-tag-id="<?php echo $tag->id; ?>">
                                            <?php echo esc_html($tag->name); ?>
                                            <button type="button" class="crm-tag-remove" data-tag-id="<?php echo $tag->id; ?>">×</button>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="crm-add-tag">
                                    <select id="crm-add-tag-select">
                                        <option value="">Add tag...</option>
                                        <?php foreach ($all_tags as $tag): ?>
                                            <option value="<?php echo $tag->id; ?>" data-color="<?php echo esc_attr($tag->color); ?>"><?php echo esc_html($tag->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Stats -->
                            <div class="crm-stats-section">
                                <div class="crm-stat">
                                    <span class="crm-stat-value"><?php echo intval($customer->bookings_count); ?></span>
                                    <span class="crm-stat-label">Bookings</span>
                                </div>
                                <div class="crm-stat">
                                    <span class="crm-stat-value">€<?php echo number_format($customer->total_revenue, 0); ?></span>
                                    <span class="crm-stat-label">Revenue</span>
                                </div>
                                <div class="crm-stat">
                                    <span class="crm-stat-value"><?php echo intval($customer->notes_count); ?></span>
                                    <span class="crm-stat-label">Notes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Assignment & Status -->
                    <div class="crm-card">
                        <div class="crm-card-header">
                            <h3>Assignment & Status</h3>
                        </div>
                        <div class="crm-card-body">
                            <div class="crm-form-group">
                                <label>Assigned To</label>
                                <select id="crm-assign-select" data-customer-id="<?php echo $customer_id; ?>">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($assignable_users as $user): ?>
                                        <option value="<?php echo $user->ID; ?>" <?php selected($customer->assigned_to, $user->ID); ?>><?php echo esc_html($user->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="crm-form-group">
                                <label>Status</label>
                                <select id="crm-status-select" data-customer-id="<?php echo $customer_id; ?>">
                                    <?php foreach ($statuses as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($customer->status, $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reminders -->
                    <div class="crm-card">
                        <div class="crm-card-header">
                            <h3>Reminders</h3>
                            <button type="button" class="button button-small" id="crm-add-reminder-btn">+ Add</button>
                        </div>
                        <div class="crm-card-body">
                            <div class="crm-reminders-list" id="crm-reminders-list">
                                <?php if (empty($reminders)): ?>
                                    <p class="crm-no-items">No reminders</p>
                                <?php else: ?>
                                    <?php foreach ($reminders as $reminder): ?>
                                        <div class="crm-reminder-item <?php echo $reminder->status === 'completed' ? 'completed' : ''; ?> <?php echo strtotime($reminder->due_date) < time() && $reminder->status !== 'completed' ? 'overdue' : ''; ?>" data-reminder-id="<?php echo $reminder->id; ?>">
                                            <div class="crm-reminder-checkbox">
                                                <input type="checkbox" <?php checked($reminder->status, 'completed'); ?> class="crm-reminder-complete-checkbox">
                                            </div>
                                            <div class="crm-reminder-content">
                                                <div class="crm-reminder-text"><?php echo esc_html($reminder->reminder_text); ?></div>
                                                <div class="crm-reminder-meta">
                                                    <span class="crm-reminder-due">
                                                        <?php echo date('M j, Y g:i A', strtotime($reminder->due_date)); ?>
                                                    </span>
                                                    <?php if ($reminder->assigned_to_name): ?>
                                                        <span class="crm-reminder-assignee">→ <?php echo esc_html($reminder->assigned_to_name); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="crm-reminder-actions">
                                                <?php if ($reminder->status !== 'completed'): ?>
                                                <select class="crm-reminder-snooze" data-reminder-id="<?php echo $reminder->id; ?>" title="Snooze reminder">
                                                    <option value="">Snooze...</option>
                                                    <option value="1h">1 Hour</option>
                                                    <option value="1d">1 Day</option>
                                                    <option value="1w">1 Week</option>
                                                </select>
                                                <?php endif; ?>
                                                <button type="button" class="crm-reminder-delete" title="Delete">×</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bookings -->
                    <?php if (!empty($bookings)): ?>
                    <div class="crm-card">
                        <div class="crm-card-header">
                            <h3>Bookings</h3>
                        </div>
                        <div class="crm-card-body">
                            <div class="crm-bookings-list">
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="crm-booking-item">
                                        <div class="crm-booking-info">
                                            <strong><?php echo esc_html($booking->yacht_name ?: 'Unknown Yacht'); ?></strong>
                                            <div class="crm-booking-dates">
                                                <?php echo date('M j', strtotime($booking->date_from)); ?> - <?php echo date('M j, Y', strtotime($booking->date_to)); ?>
                                            </div>
                                            <div class="crm-booking-ref">
                                                Ref: <?php echo esc_html($booking->booking_reference); ?>
                                            </div>
                                        </div>
                                        <div class="crm-booking-price">
                                            €<?php echo number_format($booking->total_price, 0); ?>
                                        </div>
                                        <div class="crm-booking-actions">
                                            <a href="<?php echo admin_url('admin.php?page=yolo-ys-bookings&action=view&booking_id=' . $booking->id); ?>" class="button button-small">View</a>
                                            <button type="button" class="button button-small crm-send-welcome-email" data-booking-id="<?php echo $booking->id; ?>">Send Welcome</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quick Actions -->
                    <div class="crm-card">
                        <div class="crm-card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="crm-card-body">
                            <div class="crm-quick-actions">
                                <button type="button" class="button" id="crm-log-call-btn">
                                    <span class="dashicons dashicons-phone"></span> Log Call
                                </button>
                                <button type="button" class="button" id="crm-add-note-btn">
                                    <span class="dashicons dashicons-edit"></span> Add Note
                                </button>
                                <button type="button" class="button" id="crm-send-offer-btn">
                                    <span class="dashicons dashicons-media-document"></span> Send Offer
                                </button>
                                <button type="button" class="button" id="crm-manual-booking-btn">
                                    <span class="dashicons dashicons-calendar-alt"></span> Manual Booking
                                </button>
                                <button type="button" class="button" id="crm-export-pdf-btn" data-customer-id="<?php echo $customer_id; ?>">
                                    <span class="dashicons dashicons-pdf"></span> Export PDF
                                </button>
                                <button type="button" class="button" id="crm-merge-customer-btn" data-customer-id="<?php echo $customer_id; ?>">
                                    <span class="dashicons dashicons-randomize"></span> Merge
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Activity Timeline -->
                <div class="crm-detail-right">
                    <div class="crm-card crm-timeline-card">
                        <div class="crm-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <h3>Activity Timeline</h3>
                            <select id="crm-activity-filter" style="padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;">
                                <option value="">All Activities</option>
                                <?php foreach ($activity_types as $type_key => $type_label): ?>
                                    <option value="<?php echo esc_attr($type_key); ?>"><?php echo esc_html($type_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="crm-card-body">
                            <div class="crm-timeline" id="crm-timeline">
                                <?php if (empty($activities)): ?>
                                    <p class="crm-no-items">No activities yet</p>
                                <?php else: ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="crm-timeline-item" data-type="<?php echo esc_attr($activity->activity_type); ?>">
                                            <div class="crm-timeline-icon">
                                                <span class="dashicons <?php echo $activity_icons[$activity->activity_type] ?? 'dashicons-marker'; ?>"></span>
                                            </div>
                                            <div class="crm-timeline-content">
                                                <div class="crm-timeline-header">
                                                    <span class="crm-timeline-type"><?php echo esc_html($activity_types[$activity->activity_type] ?? $activity->activity_type); ?></span>
                                                    <span class="crm-timeline-date"><?php echo date('M j, Y g:i A', strtotime($activity->created_at)); ?></span>
                                                </div>
                                                <div class="crm-timeline-subject"><?php echo esc_html($activity->subject); ?></div>
                                                <?php if ($activity->content): ?>
                                                    <div class="crm-timeline-body"><?php echo nl2br(esc_html($activity->content)); ?></div>
                                                <?php endif; ?>
                                                <?php if ($activity->metadata): ?>
                                                    <?php $meta = json_decode($activity->metadata, true); ?>
                                                    <?php if ($meta && is_array($meta)): ?>
                                                        <div class="crm-timeline-meta">
                                                            <?php foreach ($meta as $key => $value): ?>
                                                                <?php if ($value && !in_array($key, array('message', 'content'))): ?>
                                                                    <span class="crm-meta-item">
                                                                        <strong><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?>:</strong>
                                                                        <?php echo esc_html($value); ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($activity->created_by_name): ?>
                                                    <div class="crm-timeline-author">by <?php echo esc_html($activity->created_by_name); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modals -->
<!-- Add Customer Modal -->
<div id="crm-add-customer-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content">
        <div class="crm-modal-header">
            <h2>Add New Customer</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <form id="crm-add-customer-form">
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="crm-form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name">
                    </div>
                </div>
                <div class="crm-form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="crm-new-customer-email" required>
                </div>
                <div class="crm-form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="crm-new-customer-phone">
                </div>
                <div id="crm-duplicate-warning" class="crm-duplicate-warning" style="display: none;">
                    <div class="crm-duplicate-warning-header">
                        <span class="dashicons dashicons-warning"></span>
                        <strong>Potential Duplicate Found</strong>
                    </div>
                    <div id="crm-duplicate-list"></div>
                    <div class="crm-duplicate-actions">
                        <button type="button" class="button" id="crm-view-duplicate-btn">View Existing</button>
                        <button type="button" class="button" id="crm-ignore-duplicate-btn">Create Anyway</button>
                    </div>
                </div>
                <div class="crm-form-group">
                    <label>Company</label>
                    <input type="text" name="company">
                </div>
                <div class="crm-form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-save-customer-btn">Add Customer</button>
        </div>
    </div>
</div>

<!-- Log Call Modal -->
<div id="crm-log-call-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content">
        <div class="crm-modal-header">
            <h2>Log Call</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <form id="crm-log-call-form">
                <div class="crm-form-group">
                    <label>Call Type</label>
                    <select name="call_type">
                        <option value="phone_call_out">Outgoing Call</option>
                        <option value="phone_call_in">Incoming Call</option>
                    </select>
                </div>
                <div class="crm-form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required placeholder="Brief summary of the call">
                </div>
                <div class="crm-form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration" min="1" value="5">
                </div>
                <div class="crm-form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="4" placeholder="Call details and outcomes..."></textarea>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-save-call-btn">Log Call</button>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="crm-add-note-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content">
        <div class="crm-modal-header">
            <h2>Add Note</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <form id="crm-add-note-form">
                <div class="crm-form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required>
                </div>
                <div class="crm-form-group">
                    <label>Note Content *</label>
                    <textarea name="content" rows="5" required></textarea>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-save-note-btn">Add Note</button>
        </div>
    </div>
</div>

<!-- Add Reminder Modal -->
<div id="crm-add-reminder-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content">
        <div class="crm-modal-header">
            <h2>Add Reminder</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <form id="crm-add-reminder-form">
                <div class="crm-form-group">
                    <label>Reminder *</label>
                    <input type="text" name="reminder_text" required placeholder="What needs to be done?">
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label>Due Date *</label>
                        <input type="date" name="due_date" required>
                    </div>
                    <div class="crm-form-group">
                        <label>Due Time</label>
                        <input type="time" name="due_time" value="09:00">
                    </div>
                </div>
                <div class="crm-form-group">
                    <label>Assign To</label>
                    <select name="assigned_to">
                        <?php foreach ($assignable_users as $user): ?>
                            <option value="<?php echo $user->ID; ?>" <?php selected($user->ID, get_current_user_id()); ?>><?php echo esc_html($user->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-save-reminder-btn">Add Reminder</button>
        </div>
    </div>
</div>

<!-- Send Offer Modal -->
<div id="crm-send-offer-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content crm-modal-large">
        <div class="crm-modal-header">
            <h2>Send Offer</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <form id="crm-send-offer-form" enctype="multipart/form-data">
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label>From Email *</label>
                        <input type="email" name="from_email" required value="<?php echo esc_attr(get_option('admin_email')); ?>" placeholder="sender@example.com">
                    </div>
                    <div class="crm-form-group">
                        <label>From Name</label>
                        <input type="text" name="from_name" value="YOLO Charters" placeholder="Sender Name">
                    </div>
                </div>
                <div class="crm-form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required value="Charter Offer from YOLO Charters">
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label>Yacht Name</label>
                        <input type="text" name="yacht_name" placeholder="e.g., Lagoon 450">
                    </div>
                    <div class="crm-form-group">
                        <label>Offer Amount (€)</label>
                        <input type="number" name="offer_amount" min="0" step="100" placeholder="e.g., 5000">
                    </div>
                </div>
                <div class="crm-form-group">
                    <label>Message *</label>
                    <?php
                    $default_content = 'Thank you for your interest in chartering with YOLO Charters.<br><br>Based on your requirements, we are pleased to offer you the following:<br><br>[Add your offer details here]<br><br>This offer is valid for 7 days. Please let us know if you have any questions.<br><br>Best regards,<br>YOLO Charters Team';
                    wp_editor($default_content, 'crm_offer_message', array(
                        'textarea_name' => 'message',
                        'textarea_rows' => 12,
                        'media_buttons' => false,
                        'teeny' => false,
                        'quicktags' => true,
                        'tinymce' => array(
                            'toolbar1' => 'formatselect,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,undo,redo',
                            'toolbar2' => '',
                        ),
                    ));
                    ?>
                </div>
                <div class="crm-form-group">
                    <label>Attach PDF Offer Document (optional)</label>
                    <input type="file" name="offer_attachment" accept=".pdf,.doc,.docx" id="crm-offer-attachment">
                    <p class="description">Attach a PDF or Word document with your detailed offer. Max size: 10MB</p>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-send-offer-submit-btn">Send Offer</button>
        </div>
    </div>
</div>

<!-- Manual Booking Modal -->
<div id="crm-manual-booking-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content crm-modal-large">
        <div class="crm-modal-header">
            <h2>Create Manual Booking</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <form id="crm-manual-booking-form">
                <div class="crm-form-group">
                    <label>Booking Manager Reservation ID *</label>
                    <input type="text" name="bm_reservation_id" required placeholder="Paste the BM reservation ID here">
                    <p class="description">This will be used to create the guest account password: {BM_ID}YoLo</p>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label>Yacht Name</label>
                        <input type="text" name="yacht_name" placeholder="e.g., Lagoon 450">
                    </div>
                    <div class="crm-form-group">
                        <label>Total Price (€)</label>
                        <input type="number" name="total_price" min="0" step="100">
                    </div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label>Check-in Date</label>
                        <input type="date" name="checkin_date">
                    </div>
                    <div class="crm-form-group">
                        <label>Check-out Date</label>
                        <input type="date" name="checkout_date">
                    </div>
                </div>
                <div class="crm-form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Any additional notes about this booking..."></textarea>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-create-booking-btn">Create Booking</button>
        </div>
    </div>
</div>

<!-- Quick Note Modal -->
<div id="crm-quick-note-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content" style="max-width: 500px;">
        <div class="crm-modal-header">
            <h2>Quick Note</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <div class="crm-form-group">
                <label>Note *</label>
                <textarea id="crm-quick-note-text" rows="4" placeholder="Type your note here..." style="width: 100%;"></textarea>
            </div>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-save-quick-note-btn">Save Note</button>
        </div>
    </div>
</div>

<!-- Merge Customer Modal -->
<div id="crm-merge-modal" class="crm-modal" style="display: none;">
    <div class="crm-modal-content">
        <div class="crm-modal-header">
            <h2>Merge Customer</h2>
            <button type="button" class="crm-modal-close">×</button>
        </div>
        <div class="crm-modal-body">
            <p>Merge this customer with another customer record. All activities, reminders, tags, and bookings will be transferred to the selected customer.</p>
            <div class="crm-form-group">
                <label>Search Customer to Merge With</label>
                <input type="text" id="crm-merge-search" placeholder="Search by email or name...">
            </div>
            <div id="crm-merge-results" style="max-height: 200px; overflow-y: auto; margin-bottom: 15px;"></div>
            <div id="crm-merge-selection" style="display: none; background: #f0f0f1; padding: 15px; border-radius: 4px;">
                <strong>Selected Customer:</strong>
                <div id="crm-merge-selected-info"></div>
                <input type="hidden" id="crm-merge-selected-id">
            </div>
            <div class="crm-merge-warning" style="margin-top: 15px; padding: 10px; background: #fcf0f1; border-left: 4px solid #dc3232; display: none;" id="crm-merge-warning">
                <strong>Warning:</strong> This action cannot be undone. The current customer record will be deleted after merging.
            </div>
        </div>
        <div class="crm-modal-footer">
            <button type="button" class="button crm-modal-cancel">Cancel</button>
            <button type="button" class="button button-primary" id="crm-merge-confirm-btn" disabled>Merge Customers</button>
        </div>
    </div>
</div>

<?php if ($view === 'detail' && $customer): ?>
<!-- Floating Quick Note Button -->
<button type="button" id="crm-floating-note-btn" style="
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #2271b1;
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    transition: transform 0.2s, background 0.2s;
" title="Add Quick Note">
    <span class="dashicons dashicons-edit" style="font-size: 24px; width: 24px; height: 24px;"></span>
</button>
<style>
#crm-floating-note-btn:hover {
    transform: scale(1.1);
    background: #135e96;
}
</style>
<?php endif; ?>

<script>
// Store customer ID for modals
var crmCustomerId = <?php echo $customer_id ?: 'null'; ?>;
</script>
