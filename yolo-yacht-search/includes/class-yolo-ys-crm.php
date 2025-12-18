<?php
/**
 * YOLO Yacht Search CRM Module
 * 
 * Unified customer relationship management system that integrates
 * quote requests, contact messages, and bookings into a single view.
 *
 * @package YOLO_Yacht_Search
 * @since 71.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_CRM {

    /**
     * Instance of this class.
     */
    private static $instance = null;

    /**
     * Database table names
     */
    private $table_customers;
    private $table_activities;
    private $table_reminders;
    private $table_tags;
    private $table_customer_tags;

    /**
     * Customer statuses
     */
    public static $statuses = array(
        'new' => 'New Lead',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal_sent' => 'Proposal Sent',
        'negotiating' => 'Negotiating',
        'booked' => 'Booked',
        'lost' => 'Lost'
    );

    /**
     * Customer sources
     */
    public static $sources = array(
        'quote_request' => 'Quote Request',
        'contact_form' => 'Contact Form',
        'phone_call' => 'Phone Call',
        'walk_in' => 'Walk-in',
        'referral' => 'Referral',
        'boat_show' => 'Boat Show',
        'email' => 'Email',
        'manual' => 'Manual Entry'
    );

    /**
     * Activity types
     */
    public static $activity_types = array(
        'quote_request' => 'Quote Request',
        'contact_message' => 'Contact Message',
        'booking' => 'Booking',
        'phone_call_in' => 'Incoming Call',
        'phone_call_out' => 'Outgoing Call',
        'email_sent' => 'Email Sent',
        'email_received' => 'Email Received',
        'note' => 'Note',
        'offer_sent' => 'Offer Sent',
        'status_change' => 'Status Change',
        'assignment_change' => 'Assignment Change',
        'reminder_created' => 'Reminder Created',
        'reminder_completed' => 'Reminder Completed'
    );

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        
        $this->table_customers = $wpdb->prefix . 'yolo_crm_customers';
        $this->table_activities = $wpdb->prefix . 'yolo_crm_activities';
        $this->table_reminders = $wpdb->prefix . 'yolo_crm_reminders';
        $this->table_tags = $wpdb->prefix . 'yolo_crm_tags';
        $this->table_customer_tags = $wpdb->prefix . 'yolo_crm_customer_tags';

        // Hook into quote requests and contact messages
        add_action('yolo_quote_request_submitted', array($this, 'handle_quote_request'), 10, 2);
        add_action('yolo_contact_message_submitted', array($this, 'handle_contact_message'), 10, 2);
        add_action('yolo_booking_created', array($this, 'handle_booking_created'), 10, 2);
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_yolo_crm_get_customers', array($this, 'ajax_get_customers'));
        add_action('wp_ajax_yolo_crm_create_customer', array($this, 'ajax_create_customer'));
        add_action('wp_ajax_yolo_crm_update_status', array($this, 'ajax_update_status'));
        add_action('wp_ajax_yolo_crm_assign_customer', array($this, 'ajax_assign_customer'));
        add_action('wp_ajax_yolo_crm_log_activity', array($this, 'ajax_log_activity'));
        add_action('wp_ajax_yolo_crm_add_reminder', array($this, 'ajax_add_reminder'));
        add_action('wp_ajax_yolo_crm_complete_reminder', array($this, 'ajax_complete_reminder'));
        add_action('wp_ajax_yolo_crm_delete_reminder', array($this, 'ajax_delete_reminder'));
        add_action('wp_ajax_yolo_crm_send_offer', array($this, 'ajax_send_offer'));
        add_action('wp_ajax_yolo_crm_create_manual_booking', array($this, 'ajax_create_manual_booking'));
        add_action('wp_ajax_yolo_crm_send_welcome_email', array($this, 'ajax_send_welcome_email'));
        add_action('wp_ajax_yolo_crm_add_tag', array($this, 'ajax_add_tag'));
        add_action('wp_ajax_yolo_crm_remove_tag', array($this, 'ajax_remove_tag'));
        add_action('wp_ajax_yolo_crm_export_customers', array($this, 'ajax_export_customers'));
        add_action('wp_ajax_yolo_crm_run_migration', array($this, 'ajax_run_migration'));
        
        // Reminder cron
        add_action('yolo_crm_check_reminders', array($this, 'check_due_reminders'));
        if (!wp_next_scheduled('yolo_crm_check_reminders')) {
            wp_schedule_event(time(), 'hourly', 'yolo_crm_check_reminders');
        }
    }

    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Customers table
        $table_customers = $wpdb->prefix . 'yolo_crm_customers';
        $sql_customers = "CREATE TABLE $table_customers (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            first_name varchar(100) DEFAULT '',
            last_name varchar(100) DEFAULT '',
            phone varchar(50) DEFAULT '',
            company varchar(255) DEFAULT '',
            status varchar(50) DEFAULT 'new',
            source varchar(50) DEFAULT 'manual',
            assigned_to bigint(20) unsigned DEFAULT NULL,
            total_revenue decimal(10,2) DEFAULT 0.00,
            bookings_count int(11) DEFAULT 0,
            notes_count int(11) DEFAULT 0,
            last_activity_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY assigned_to (assigned_to),
            KEY source (source),
            KEY last_activity_at (last_activity_at)
        ) $charset_collate;";

        // Activities table
        $table_activities = $wpdb->prefix . 'yolo_crm_activities';
        $sql_activities = "CREATE TABLE $table_activities (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) unsigned NOT NULL,
            activity_type varchar(50) NOT NULL,
            reference_type varchar(50) DEFAULT NULL,
            reference_id bigint(20) unsigned DEFAULT NULL,
            subject varchar(255) DEFAULT '',
            content longtext DEFAULT NULL,
            duration int(11) DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_by bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY activity_type (activity_type),
            KEY reference_type_id (reference_type, reference_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Reminders table
        $table_reminders = $wpdb->prefix . 'yolo_crm_reminders';
        $sql_reminders = "CREATE TABLE $table_reminders (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) unsigned NOT NULL,
            assigned_to bigint(20) unsigned NOT NULL,
            reminder_text text NOT NULL,
            due_date datetime NOT NULL,
            status varchar(20) DEFAULT 'pending',
            completed_at datetime DEFAULT NULL,
            snoozed_until datetime DEFAULT NULL,
            created_by bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY assigned_to (assigned_to),
            KEY due_date (due_date),
            KEY status (status)
        ) $charset_collate;";

        // Tags table
        $table_tags = $wpdb->prefix . 'yolo_crm_tags';
        $sql_tags = "CREATE TABLE $table_tags (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            color varchar(7) DEFAULT '#6c757d',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate;";

        // Customer tags relationship table
        $table_customer_tags = $wpdb->prefix . 'yolo_crm_customer_tags';
        $sql_customer_tags = "CREATE TABLE $table_customer_tags (
            customer_id bigint(20) unsigned NOT NULL,
            tag_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (customer_id, tag_id),
            KEY tag_id (tag_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_customers);
        dbDelta($sql_activities);
        dbDelta($sql_reminders);
        dbDelta($sql_tags);
        dbDelta($sql_customer_tags);

        // Create default tags
        self::create_default_tags();

        // Log table creation
        error_log('[YOLO CRM] Database tables created/updated');
    }

    /**
     * Create default tags
     */
    public static function create_default_tags() {
        global $wpdb;
        
        $table_tags = $wpdb->prefix . 'yolo_crm_tags';
        
        $default_tags = array(
            array('name' => 'VIP', 'color' => '#ffc107'),
            array('name' => 'Repeat Customer', 'color' => '#28a745'),
            array('name' => 'Referral', 'color' => '#17a2b8'),
            array('name' => 'Price Sensitive', 'color' => '#dc3545'),
            array('name' => 'Skipper Needed', 'color' => '#6f42c1'),
            array('name' => 'Corporate', 'color' => '#343a40'),
            array('name' => 'Family', 'color' => '#fd7e14'),
            array('name' => 'First Timer', 'color' => '#20c997')
        );

        foreach ($default_tags as $tag) {
            $wpdb->query($wpdb->prepare(
                "INSERT IGNORE INTO $table_tags (name, color) VALUES (%s, %s)",
                $tag['name'],
                $tag['color']
            ));
        }
    }

    /**
     * Migrate existing data to CRM
     */
    public static function migrate_existing_data() {
        global $wpdb;
        
        $crm = self::get_instance();
        $migrated = 0;

        // Migrate quote requests
        $table_quotes = $wpdb->prefix . 'yolo_quote_requests';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_quotes'") === $table_quotes) {
            $quotes = $wpdb->get_results("SELECT * FROM $table_quotes ORDER BY created_at ASC");
            foreach ($quotes as $quote) {
                // Parse customer_name into first/last name
                $name_parts = explode(' ', $quote->customer_name ?? '', 2);
                $first_name = $name_parts[0] ?? '';
                $last_name = $name_parts[1] ?? '';
                
                // Map quote status to CRM status
                $crm_status = 'new';
                if (isset($quote->status)) {
                    $status_map = array(
                        'new' => 'new',
                        'pending' => 'contacted',
                        'in_progress' => 'qualified',
                        'quoted' => 'proposal_sent',
                        'completed' => 'booked',
                        'cancelled' => 'lost'
                    );
                    $crm_status = $status_map[$quote->status] ?? 'new';
                }
                
                $customer_id = $crm->create_or_update_customer(array(
                    'email' => $quote->customer_email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $quote->customer_phone ?? '',
                    'source' => 'quote_request',
                    'status' => $crm_status
                ));

                if ($customer_id) {
                    $crm->log_activity($customer_id, 'quote_request', array(
                        'reference_type' => 'quote_request',
                        'reference_id' => $quote->id,
                        'subject' => 'Quote Request: ' . ($quote->yacht_preference ?? 'General Inquiry'),
                        'content' => $quote->special_requests ?? '',
                        'metadata' => json_encode(array(
                            'yacht_preference' => $quote->yacht_preference ?? null,
                            'checkin_date' => $quote->checkin_date ?? null,
                            'checkout_date' => $quote->checkout_date ?? null,
                            'num_guests' => $quote->num_guests ?? null
                        )),
                        'created_at' => $quote->created_at
                    ));
                    $migrated++;
                }
            }
        }

        // Migrate contact messages
        $table_contacts = $wpdb->prefix . 'yolo_contact_messages';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_contacts'") === $table_contacts) {
            $contacts = $wpdb->get_results("SELECT * FROM $table_contacts ORDER BY created_at ASC");
            foreach ($contacts as $contact) {
                // Parse contact_name into first/last name
                $name_parts = explode(' ', $contact->contact_name ?? '', 2);
                $first_name = $name_parts[0] ?? '';
                $last_name = $name_parts[1] ?? '';
                
                // Map contact status to CRM status
                $crm_status = 'new';
                if (isset($contact->status)) {
                    $status_map = array(
                        'new' => 'new',
                        'read' => 'contacted',
                        'replied' => 'contacted',
                        'resolved' => 'qualified'
                    );
                    $crm_status = $status_map[$contact->status] ?? 'new';
                }
                
                $customer_id = $crm->create_or_update_customer(array(
                    'email' => $contact->contact_email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $contact->contact_phone ?? '',
                    'source' => 'contact_form',
                    'status' => $crm_status
                ));

                if ($customer_id) {
                    $crm->log_activity($customer_id, 'contact_message', array(
                        'reference_type' => 'contact_message',
                        'reference_id' => $contact->id,
                        'subject' => $contact->contact_subject ?? 'Contact Form Message',
                        'content' => $contact->contact_message ?? '',
                        'created_at' => $contact->created_at
                    ));
                    $migrated++;
                }
            }
        }

        // Migrate bookings
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_bookings'") === $table_bookings) {
            $bookings = $wpdb->get_results("SELECT * FROM $table_bookings ORDER BY created_at ASC");
            foreach ($bookings as $booking) {
                $name_parts = explode(' ', $booking->customer_name ?? '', 2);
                $customer_id = $crm->create_or_update_customer(array(
                    'email' => $booking->customer_email,
                    'first_name' => $name_parts[0] ?? '',
                    'last_name' => $name_parts[1] ?? '',
                    'phone' => $booking->customer_phone ?? '',
                    'source' => 'quote_request',
                    'status' => 'booked'
                ));

                if ($customer_id) {
                    // Update customer revenue
                    $crm->update_customer_stats($customer_id);

                    $crm->log_activity($customer_id, 'booking', array(
                        'reference_type' => 'booking',
                        'reference_id' => $booking->id,
                        'subject' => 'Booking: ' . ($booking->yacht_name ?? 'Unknown Yacht'),
                        'content' => sprintf(
                            'Yacht: %s | Dates: %s to %s | Total: %s %s',
                            $booking->yacht_name ?? 'Unknown',
                            $booking->date_from ?? '',
                            $booking->date_to ?? '',
                            $booking->total_price ?? '0',
                            $booking->currency ?? 'EUR'
                        ),
                        'metadata' => json_encode(array(
                            'booking_id' => $booking->id,
                            'bm_reservation_id' => $booking->bm_reservation_id ?? null,
                            'yacht_name' => $booking->yacht_name ?? null,
                            'total_price' => $booking->total_price ?? null,
                            'currency' => $booking->currency ?? 'EUR'
                        )),
                        'created_at' => $booking->created_at
                    ));
                    $migrated++;
                }
            }
        }

        error_log('[YOLO CRM] Migrated ' . $migrated . ' records to CRM');
        update_option('yolo_crm_migration_completed', current_time('mysql'));
        
        return $migrated;
    }

    /**
     * Create or update a customer
     */
    public function create_or_update_customer($data) {
        global $wpdb;

        $email = sanitize_email($data['email']);
        if (empty($email)) {
            return false;
        }

        // Check if customer exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_customers} WHERE email = %s",
            $email
        ));

        if ($existing) {
            // Update existing customer
            $update_data = array(
                'last_activity_at' => current_time('mysql')
            );

            // Only update fields if they're provided and not empty
            if (!empty($data['first_name']) && empty($existing->first_name)) {
                $update_data['first_name'] = sanitize_text_field($data['first_name']);
            }
            if (!empty($data['last_name']) && empty($existing->last_name)) {
                $update_data['last_name'] = sanitize_text_field($data['last_name']);
            }
            if (!empty($data['phone']) && empty($existing->phone)) {
                $update_data['phone'] = sanitize_text_field($data['phone']);
            }
            if (!empty($data['status']) && $data['status'] === 'booked') {
                $update_data['status'] = 'booked';
            }

            $wpdb->update($this->table_customers, $update_data, array('id' => $existing->id));
            
            return $existing->id;
        } else {
            // Create new customer
            $insert_data = array(
                'email' => $email,
                'first_name' => sanitize_text_field($data['first_name'] ?? ''),
                'last_name' => sanitize_text_field($data['last_name'] ?? ''),
                'phone' => sanitize_text_field($data['phone'] ?? ''),
                'company' => sanitize_text_field($data['company'] ?? ''),
                'status' => sanitize_text_field($data['status'] ?? 'new'),
                'source' => sanitize_text_field($data['source'] ?? 'manual'),
                'assigned_to' => isset($data['assigned_to']) ? absint($data['assigned_to']) : null,
                'last_activity_at' => current_time('mysql'),
                'created_at' => current_time('mysql')
            );

            $wpdb->insert($this->table_customers, $insert_data);
            
            return $wpdb->insert_id;
        }
    }

    /**
     * Log an activity
     */
    public function log_activity($customer_id, $activity_type, $data = array()) {
        global $wpdb;

        $insert_data = array(
            'customer_id' => absint($customer_id),
            'activity_type' => sanitize_text_field($activity_type),
            'reference_type' => isset($data['reference_type']) ? sanitize_text_field($data['reference_type']) : null,
            'reference_id' => isset($data['reference_id']) ? absint($data['reference_id']) : null,
            'subject' => isset($data['subject']) ? sanitize_text_field($data['subject']) : '',
            'content' => isset($data['content']) ? wp_kses_post($data['content']) : null,
            'duration' => isset($data['duration']) ? absint($data['duration']) : null,
            'metadata' => isset($data['metadata']) ? $data['metadata'] : null,
            'created_by' => isset($data['created_by']) ? absint($data['created_by']) : get_current_user_id(),
            'created_at' => isset($data['created_at']) ? $data['created_at'] : current_time('mysql')
        );

        $wpdb->insert($this->table_activities, $insert_data);

        // Update customer's last activity
        $wpdb->update(
            $this->table_customers,
            array('last_activity_at' => current_time('mysql')),
            array('id' => $customer_id)
        );

        // Update notes count if it's a note
        if ($activity_type === 'note') {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$this->table_customers} SET notes_count = notes_count + 1 WHERE id = %d",
                $customer_id
            ));
        }

        return $wpdb->insert_id;
    }

    /**
     * Update customer statistics
     */
    public function update_customer_stats($customer_id) {
        global $wpdb;

        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        $customer = $this->get_customer($customer_id);
        
        if (!$customer) {
            return;
        }

        // Get booking stats
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as count, COALESCE(SUM(total_price), 0) as revenue 
             FROM $table_bookings 
             WHERE customer_email = %s",
            $customer->email
        ));

        $wpdb->update(
            $this->table_customers,
            array(
                'bookings_count' => $stats->count,
                'total_revenue' => $stats->revenue
            ),
            array('id' => $customer_id)
        );
    }

    /**
     * Get a customer by ID
     */
    public function get_customer($customer_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_customers} WHERE id = %d",
            $customer_id
        ));
    }

    /**
     * Get a customer by email
     */
    public function get_customer_by_email($email) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_customers} WHERE email = %s",
            $email
        ));
    }

    /**
     * Handle quote request submission
     */
    public function handle_quote_request($quote_id, $data) {
        $customer_id = $this->create_or_update_customer(array(
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'source' => 'quote_request'
        ));

        if ($customer_id) {
            $this->log_activity($customer_id, 'quote_request', array(
                'reference_type' => 'quote_request',
                'reference_id' => $quote_id,
                'subject' => 'Quote Request: ' . ($data['yacht_name'] ?? 'General Inquiry'),
                'content' => $data['message'] ?? '',
                'metadata' => json_encode(array(
                    'yacht_name' => $data['yacht_name'] ?? null,
                    'date_from' => $data['date_from'] ?? null,
                    'date_to' => $data['date_to'] ?? null,
                    'guests' => $data['guests'] ?? null
                ))
            ));

            // Send notification to all admins/base managers
            $this->send_new_lead_notification($customer_id, 'quote_request', $data);
        }
    }

    /**
     * Handle contact message submission
     */
    public function handle_contact_message($message_id, $data) {
        $customer_id = $this->create_or_update_customer(array(
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'source' => 'contact_form'
        ));

        if ($customer_id) {
            $this->log_activity($customer_id, 'contact_message', array(
                'reference_type' => 'contact_message',
                'reference_id' => $message_id,
                'subject' => $data['subject'] ?? 'Contact Form Message',
                'content' => $data['message'] ?? ''
            ));

            // Send notification to all admins/base managers
            $this->send_new_lead_notification($customer_id, 'contact_message', $data);
        }
    }

    /**
     * Handle booking created
     */
    public function handle_booking_created($booking_id, $data) {
        $customer_id = $this->create_or_update_customer(array(
            'email' => $data['customer_email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['customer_phone'] ?? '',
            'status' => 'booked'
        ));

        if ($customer_id) {
            $this->log_activity($customer_id, 'booking', array(
                'reference_type' => 'booking',
                'reference_id' => $booking_id,
                'subject' => 'Booking: ' . ($data['yacht_name'] ?? 'Unknown Yacht'),
                'content' => sprintf(
                    'Yacht: %s | Dates: %s to %s | Total: %s %s',
                    $data['yacht_name'] ?? 'Unknown',
                    $data['date_from'] ?? '',
                    $data['date_to'] ?? '',
                    $data['total_price'] ?? '0',
                    $data['currency'] ?? 'EUR'
                ),
                'metadata' => json_encode($data)
            ));

            $this->update_customer_stats($customer_id);
        }
    }

    /**
     * Send new lead notification to all admins and base managers
     */
    public function send_new_lead_notification($customer_id, $type, $data) {
        $customer = $this->get_customer($customer_id);
        if (!$customer) {
            return;
        }

        // Get all admins and base managers
        $users = get_users(array(
            'role__in' => array('administrator', 'base_manager'),
            'fields' => array('ID', 'user_email', 'display_name')
        ));

        $type_label = $type === 'quote_request' ? 'Quote Request' : 'Contact Message';
        $subject = sprintf('[YOLO Charters] New %s from %s', $type_label, $customer->first_name . ' ' . $customer->last_name);

        $message = sprintf(
            "A new %s has been received:\n\n" .
            "Customer: %s %s\n" .
            "Email: %s\n" .
            "Phone: %s\n\n",
            $type_label,
            $customer->first_name,
            $customer->last_name,
            $customer->email,
            $customer->phone
        );

        if ($type === 'quote_request') {
            $message .= sprintf(
                "Yacht: %s\n" .
                "Dates: %s to %s\n" .
                "Guests: %s\n\n",
                $data['yacht_name'] ?? 'Not specified',
                $data['date_from'] ?? 'Not specified',
                $data['date_to'] ?? 'Not specified',
                $data['guests'] ?? 'Not specified'
            );
        }

        $message .= sprintf(
            "Message:\n%s\n\n" .
            "View in CRM: %s",
            $data['message'] ?? 'No message',
            admin_url('admin.php?page=yolo-ys-crm&customer=' . $customer_id)
        );

        foreach ($users as $user) {
            wp_mail($user->user_email, $subject, $message);
        }
    }

    /**
     * Get table names (for external access)
     */
    public function get_table_names() {
        return array(
            'customers' => $this->table_customers,
            'activities' => $this->table_activities,
            'reminders' => $this->table_reminders,
            'tags' => $this->table_tags,
            'customer_tags' => $this->table_customer_tags
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'yolo-ys-crm') === false) {
            return;
        }
        
        wp_enqueue_style(
            'yolo-crm-styles',
            YOLO_YS_PLUGIN_URL . 'admin/css/crm.css',
            array(),
            YOLO_YS_VERSION
        );
        
        wp_enqueue_script(
            'yolo-crm-scripts',
            YOLO_YS_PLUGIN_URL . 'admin/js/crm.js',
            array('jquery'),
            YOLO_YS_VERSION,
            true
        );
        
        wp_localize_script('yolo-crm-scripts', 'yoloCRM', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yolo_crm_nonce'),
            'statuses' => self::$statuses,
            'sources' => self::$sources,
            'activityTypes' => self::$activity_types
        ));
    }
    
    /**
     * AJAX: Get customers list
     */
    public function ajax_get_customers() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
        $assigned_to = isset($_POST['assigned_to']) ? sanitize_text_field($_POST['assigned_to']) : '';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        
        $where = array('1=1');
        $values = array();
        
        if ($search) {
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s OR c.phone LIKE %s)';
            $values[] = $search_like;
            $values[] = $search_like;
            $values[] = $search_like;
            $values[] = $search_like;
        }
        
        if ($status) {
            $where[] = 'c.status = %s';
            $values[] = $status;
        }
        
        if ($source) {
            $where[] = 'c.source = %s';
            $values[] = $source;
        }
        
        if ($assigned_to === 'unassigned') {
            $where[] = 'c.assigned_to IS NULL';
        } elseif ($assigned_to) {
            $where[] = 'c.assigned_to = %d';
            $values[] = intval($assigned_to);
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM {$this->table_customers} c WHERE $where_clause";
        if (!empty($values)) {
            $count_sql = $wpdb->prepare($count_sql, $values);
        }
        $total = $wpdb->get_var($count_sql);
        
        // Get customers
        $sql = "SELECT c.*, u.display_name as assigned_to_name 
                FROM {$this->table_customers} c 
                LEFT JOIN {$wpdb->users} u ON c.assigned_to = u.ID 
                WHERE $where_clause 
                ORDER BY c.last_activity_at DESC 
                LIMIT %d OFFSET %d";
        $values[] = $limit;
        $values[] = $offset;
        
        $customers = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        wp_send_json_success(array(
            'customers' => $customers,
            'total' => intval($total)
        ));
    }
    
    /**
     * AJAX: Create customer
     */
    public function ajax_create_customer() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        $email = sanitize_email($_POST['email']);
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Valid email is required'));
        }
        
        // Check for duplicate
        $existing = $this->get_customer_by_email($email);
        if ($existing) {
            wp_send_json_error(array('message' => 'Customer with this email already exists'));
        }
        
        $customer_id = $this->create_or_update_customer(array(
            'email' => $email,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'company' => sanitize_text_field($_POST['company'] ?? ''),
            'source' => 'manual'
        ));
        
        if ($customer_id) {
            // Add note if provided
            $notes = sanitize_textarea_field($_POST['notes'] ?? '');
            if ($notes) {
                $this->log_activity($customer_id, 'note', array(
                    'subject' => 'Initial Notes',
                    'content' => $notes
                ));
            }
            
            wp_send_json_success(array(
                'message' => 'Customer created',
                'customer_id' => $customer_id
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to create customer'));
        }
    }
    
    /**
     * AJAX: Update customer status
     */
    public function ajax_update_status() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        if (!isset(self::$statuses[$new_status])) {
            wp_send_json_error(array('message' => 'Invalid status'));
        }
        
        $customer = $this->get_customer($customer_id);
        if (!$customer) {
            wp_send_json_error(array('message' => 'Customer not found'));
        }
        
        $old_status = $customer->status;
        
        $wpdb->update(
            $this->table_customers,
            array('status' => $new_status),
            array('id' => $customer_id)
        );
        
        // Log activity
        $this->log_activity($customer_id, 'status_change', array(
            'subject' => sprintf('Status changed from %s to %s', 
                self::$statuses[$old_status] ?? $old_status, 
                self::$statuses[$new_status]
            ),
            'metadata' => json_encode(array(
                'old_status' => $old_status,
                'new_status' => $new_status
            ))
        ));
        
        wp_send_json_success(array('message' => 'Status updated'));
    }
    
    /**
     * AJAX: Assign customer
     */
    public function ajax_assign_customer() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
        
        $customer = $this->get_customer($customer_id);
        if (!$customer) {
            wp_send_json_error(array('message' => 'Customer not found'));
        }
        
        $old_assignee = $customer->assigned_to;
        $old_name = $old_assignee ? get_user_by('id', $old_assignee)->display_name : 'Unassigned';
        $new_name = $assigned_to ? get_user_by('id', $assigned_to)->display_name : 'Unassigned';
        
        $wpdb->update(
            $this->table_customers,
            array('assigned_to' => $assigned_to),
            array('id' => $customer_id)
        );
        
        // Log activity
        $this->log_activity($customer_id, 'assignment_change', array(
            'subject' => sprintf('Assigned from %s to %s', $old_name, $new_name),
            'metadata' => json_encode(array(
                'old_assignee' => $old_assignee,
                'new_assignee' => $assigned_to
            ))
        ));
        
        wp_send_json_success(array('message' => 'Customer assigned'));
    }
    
    /**
     * AJAX: Log activity (call or note)
     */
    public function ajax_log_activity() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        $customer_id = intval($_POST['customer_id']);
        $activity_type = sanitize_text_field($_POST['activity_type']);
        $subject = sanitize_text_field($_POST['subject']);
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : null;
        
        $allowed_types = array('note', 'phone_call_in', 'phone_call_out', 'email_sent');
        if (!in_array($activity_type, $allowed_types)) {
            wp_send_json_error(array('message' => 'Invalid activity type'));
        }
        
        $activity_id = $this->log_activity($customer_id, $activity_type, array(
            'subject' => $subject,
            'content' => $content,
            'duration' => $duration
        ));
        
        if ($activity_id) {
            wp_send_json_success(array('message' => 'Activity logged', 'activity_id' => $activity_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to log activity'));
        }
    }
    
    /**
     * AJAX: Add reminder
     */
    public function ajax_add_reminder() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $reminder_text = sanitize_text_field($_POST['reminder_text']);
        $due_date = sanitize_text_field($_POST['due_date']);
        $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : get_current_user_id();
        
        $result = $wpdb->insert($this->table_reminders, array(
            'customer_id' => $customer_id,
            'reminder_text' => $reminder_text,
            'due_date' => $due_date,
            'assigned_to' => $assigned_to,
            'created_by' => get_current_user_id(),
            'status' => 'pending'
        ));
        
        if ($result) {
            // Log activity
            $this->log_activity($customer_id, 'reminder_created', array(
                'subject' => 'Reminder created: ' . $reminder_text,
                'metadata' => json_encode(array(
                    'due_date' => $due_date,
                    'assigned_to' => $assigned_to
                ))
            ));
            
            wp_send_json_success(array('message' => 'Reminder added', 'reminder_id' => $wpdb->insert_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to add reminder'));
        }
    }
    
    /**
     * AJAX: Complete reminder
     */
    public function ajax_complete_reminder() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $reminder_id = intval($_POST['reminder_id']);
        $completed = isset($_POST['completed']) && $_POST['completed'] ? 1 : 0;
        
        $reminder = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_reminders} WHERE id = %d",
            $reminder_id
        ));
        
        if (!$reminder) {
            wp_send_json_error(array('message' => 'Reminder not found'));
        }
        
        $wpdb->update(
            $this->table_reminders,
            array(
                'status' => $completed ? 'completed' : 'pending',
                'completed_at' => $completed ? current_time('mysql') : null
            ),
            array('id' => $reminder_id)
        );
        
        if ($completed) {
            $this->log_activity($reminder->customer_id, 'reminder_completed', array(
                'subject' => 'Reminder completed: ' . $reminder->reminder_text
            ));
        }
        
        wp_send_json_success(array('message' => 'Reminder updated'));
    }
    
    /**
     * AJAX: Delete reminder
     */
    public function ajax_delete_reminder() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $reminder_id = intval($_POST['reminder_id']);
        
        $wpdb->delete($this->table_reminders, array('id' => $reminder_id));
        
        wp_send_json_success(array('message' => 'Reminder deleted'));
    }
    
    /**
     * AJAX: Send offer
     */
    public function ajax_send_offer() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $from_email = sanitize_email($_POST['from_email'] ?? get_option('admin_email'));
        $from_name = sanitize_text_field($_POST['from_name'] ?? 'YOLO Charters');
        $subject = sanitize_text_field($_POST['subject']);
        $yacht_name = sanitize_text_field($_POST['yacht_name'] ?? '');
        $offer_amount = floatval($_POST['offer_amount'] ?? 0);
        $message = wp_kses_post($_POST['message']);
        
        $customer = $this->get_customer($customer_id);
        if (!$customer) {
            wp_send_json_error(array('message' => 'Customer not found'));
        }
        
        $customer_name = trim($customer->first_name . ' ' . $customer->last_name);
        
        // Build email
        $email_body = $this->build_offer_email($customer_name, $message);
        
        // Build headers with From email and name
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email
        );
        
        // Handle file attachment
        $attachments = array();
        $attachment_name = '';
        if (!empty($_FILES['offer_attachment']) && $_FILES['offer_attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['offer_attachment'];
            
            // Validate file type
            $allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $file_type = wp_check_filetype($file['name']);
            $mime_type = $file['type'];
            
            if (!in_array($mime_type, $allowed_types)) {
                wp_send_json_error(array('message' => 'Invalid file type. Only PDF and Word documents are allowed.'));
            }
            
            // Check file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                wp_send_json_error(array('message' => 'File too large. Maximum size is 10MB.'));
            }
            
            // Upload file to WordPress uploads directory
            $upload = wp_handle_upload($file, array('test_form' => false));
            
            if (isset($upload['error'])) {
                wp_send_json_error(array('message' => 'File upload failed: ' . $upload['error']));
            }
            
            $attachments[] = $upload['file'];
            $attachment_name = $file['name'];
        }
        
        $sent = wp_mail($customer->email, $subject, $email_body, $headers, $attachments);
        
        // Clean up uploaded file after sending
        if (!empty($attachments) && file_exists($attachments[0])) {
            @unlink($attachments[0]);
        }
        
        if ($sent) {
            // Log activity
            $activity_metadata = array(
                'yacht_name' => $yacht_name,
                'offer_amount' => $offer_amount,
                'from_email' => $from_email,
                'from_name' => $from_name
            );
            if ($attachment_name) {
                $activity_metadata['attachment'] = $attachment_name;
            }
            
            $this->log_activity($customer_id, 'offer_sent', array(
                'subject' => 'Offer sent: ' . $subject,
                'content' => $message,
                'metadata' => json_encode($activity_metadata)
            ));
            
            // Update status if in early stage
            $early_statuses = array('new', 'contacted', 'qualified');
            if (in_array($customer->status, $early_statuses)) {
                $wpdb->update(
                    $this->table_customers,
                    array('status' => 'proposal_sent'),
                    array('id' => $customer_id)
                );
                
                $this->log_activity($customer_id, 'status_change', array(
                    'subject' => 'Status auto-changed to Proposal Sent'
                ));
            }
            
            wp_send_json_success(array('message' => 'Offer sent successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send offer'));
        }
    }
    
    /**
     * Build offer email HTML
     */
    private function build_offer_email($customer_name, $message) {
        // Message already contains HTML from TinyMCE, so we use it directly
        return '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e3a8a; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>YOLO Charters</h1>
                </div>
                <div class="content">
                    <p>Dear ' . esc_html($customer_name) . ',</p>
                    ' . $message . '
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' YOLO Charters. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * AJAX: Create manual booking
     */
    public function ajax_create_manual_booking() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $bm_reservation_id = sanitize_text_field($_POST['bm_reservation_id']);
        
        if (empty($bm_reservation_id)) {
            wp_send_json_error(array('message' => 'Booking Manager reservation ID is required'));
        }
        
        $customer = $this->get_customer($customer_id);
        if (!$customer) {
            wp_send_json_error(array('message' => 'Customer not found'));
        }
        
        // Create guest user account
        $email = $customer->email;
        $first_name = $customer->first_name;
        $last_name = $customer->last_name;
        $password = $bm_reservation_id . 'YoLo';
        
        // Ensure guest role exists (v71.2 fix)
        if (!get_role('guest')) {
            add_role('guest', 'Guest', array(
                'read' => true,
                'upload_files' => true
            ));
        }
        
        // Check if user already exists
        $existing_user = get_user_by('email', $email);
        
        if ($existing_user) {
            $user_id = $existing_user->ID;
            wp_set_password($password, $user_id);
            // Ensure user has guest role
            $user = new WP_User($user_id);
            if (!in_array('guest', (array) $user->roles)) {
                $user->add_role('guest');
            }
        } else {
            $username = sanitize_user(strtolower($first_name . '.' . $last_name));
            $username = $this->generate_unique_username($username);
            
            $user_id = wp_create_user($username, $password, $email);
            
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => 'Failed to create user: ' . $user_id->get_error_message()));
            }
            
            $user = new WP_User($user_id);
            $user->set_role('guest');
            
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $first_name . ' ' . $last_name
            ));
        }
        
        // Create booking record
        $bookings_table = $wpdb->prefix . 'yolo_bookings';
        $booking_reference = 'YL-' . strtoupper(substr(md5($bm_reservation_id . time()), 0, 8));
        
        $booking_data = array(
            'booking_reference' => $booking_reference,
            'bm_reservation_id' => $bm_reservation_id,
            'customer_email' => $email,
            'customer_name' => $first_name . ' ' . $last_name,
            'customer_phone' => $customer->phone,
            'wp_user_id' => $user_id,
            'yacht_name' => sanitize_text_field($_POST['yacht_name'] ?? ''),
            'date_from' => sanitize_text_field($_POST['checkin_date'] ?? ''),
            'date_to' => sanitize_text_field($_POST['checkout_date'] ?? ''),
            'total_price' => floatval($_POST['total_price'] ?? 0),
            'currency' => 'EUR',
            'status' => 'confirmed',
            'payment_status' => 'manual',
            'notes' => sanitize_textarea_field($_POST['notes'] ?? 'Manual booking created via CRM'),
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($bookings_table, $booking_data);
        
        if (!$result) {
            wp_send_json_error(array('message' => 'Failed to create booking record'));
        }
        
        $booking_id = $wpdb->insert_id;
        
        // Update customer status
        $wpdb->update(
            $this->table_customers,
            array('status' => 'booked'),
            array('id' => $customer_id)
        );
        
        // Update customer stats
        $this->update_customer_stats($customer_id);
        
        // Log activity
        $this->log_activity($customer_id, 'booking', array(
            'reference_type' => 'booking',
            'reference_id' => $booking_id,
            'subject' => 'Manual booking created: ' . $booking_reference,
            'content' => 'BM Reservation ID: ' . $bm_reservation_id,
            'metadata' => json_encode(array(
                'booking_id' => $booking_id,
                'booking_reference' => $booking_reference,
                'bm_reservation_id' => $bm_reservation_id,
                'user_id' => $user_id
            ))
        ));
        
        wp_send_json_success(array(
            'message' => 'Booking created successfully',
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'user_id' => $user_id,
            'password' => $password
        ));
    }
    
    /**
     * Generate unique username
     */
    private function generate_unique_username($base_username) {
        $username = $base_username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * AJAX: Send welcome email
     */
    public function ajax_send_welcome_email() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $booking_id = intval($_POST['booking_id']);
        $bookings_table = $wpdb->prefix . 'yolo_bookings';
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $bookings_table WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            wp_send_json_error(array('message' => 'Booking not found'));
        }
        
        // Build welcome email
        $email_body = $this->build_welcome_email($booking);
        $subject = 'Welcome to YOLO Charters - Your Booking Confirmation';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $sent = wp_mail($booking->customer_email, $subject, $email_body, $headers);
        
        if ($sent) {
            // Log activity if customer exists in CRM
            $customer = $this->get_customer_by_email($booking->customer_email);
            if ($customer) {
                $this->log_activity($customer->id, 'email_sent', array(
                    'subject' => 'Welcome email sent',
                    'content' => 'Booking: ' . $booking->booking_reference
                ));
            }
            
            wp_send_json_success(array('message' => 'Welcome email sent'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send welcome email'));
        }
    }
    
    /**
     * Build welcome email HTML
     */
    private function build_welcome_email($booking) {
        $customer_name = $booking->customer_name;
        $booking_reference = $booking->booking_reference;
        $yacht_name = $booking->yacht_name ?: 'Your Yacht';
        $checkin_date = $booking->date_from ? date('F j, Y', strtotime($booking->date_from)) : 'TBD';
        $checkout_date = $booking->date_to ? date('F j, Y', strtotime($booking->date_to)) : 'TBD';
        $login_url = wp_login_url(home_url('/my-bookings/'));
        
        return '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e3a8a; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .booking-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .booking-details h3 { margin-top: 0; color: #1e3a8a; }
                .btn { display: inline-block; background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Welcome to YOLO Charters!</h1>
                </div>
                <div class="content">
                    <p>Dear ' . esc_html($customer_name) . ',</p>
                    <p>Thank you for choosing YOLO Charters for your yacht experience. Your booking has been confirmed!</p>
                    
                    <div class="booking-details">
                        <h3>Booking Details</h3>
                        <p><strong>Booking Reference:</strong> ' . esc_html($booking_reference) . '</p>
                        <p><strong>Yacht:</strong> ' . esc_html($yacht_name) . '</p>
                        <p><strong>Check-in:</strong> ' . esc_html($checkin_date) . '</p>
                        <p><strong>Check-out:</strong> ' . esc_html($checkout_date) . '</p>
                    </div>
                    
                    <p>You can view your booking details and manage your reservation by logging into your account:</p>
                    <p><a href="' . esc_url($login_url) . '" class="btn">View My Booking</a></p>
                    
                    <p>If you have any questions or need assistance, please don\'t hesitate to contact us.</p>
                    
                    <p>We look forward to welcoming you aboard!</p>
                    <p>Best regards,<br>The YOLO Charters Team</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' YOLO Charters. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * AJAX: Add tag to customer
     */
    public function ajax_add_tag() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $tag_id = intval($_POST['tag_id']);
        
        // Check if already has tag
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_customer_tags} WHERE customer_id = %d AND tag_id = %d",
            $customer_id, $tag_id
        ));
        
        if ($existing) {
            wp_send_json_success(array('message' => 'Tag already added'));
            return;
        }
        
        $wpdb->insert($this->table_customer_tags, array(
            'customer_id' => $customer_id,
            'tag_id' => $tag_id
        ));
        
        wp_send_json_success(array('message' => 'Tag added'));
    }
    
    /**
     * AJAX: Remove tag from customer
     */
    public function ajax_remove_tag() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $customer_id = intval($_POST['customer_id']);
        $tag_id = intval($_POST['tag_id']);
        
        $wpdb->delete($this->table_customer_tags, array(
            'customer_id' => $customer_id,
            'tag_id' => $tag_id
        ));
        
        wp_send_json_success(array('message' => 'Tag removed'));
    }
    
    /**
     * AJAX: Export customers to CSV
     */
    public function ajax_export_customers() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        global $wpdb;
        
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
        $assigned_to = isset($_POST['assigned_to']) ? sanitize_text_field($_POST['assigned_to']) : '';
        
        $where = array('1=1');
        $values = array();
        
        if ($status) {
            $where[] = 'c.status = %s';
            $values[] = $status;
        }
        
        if ($source) {
            $where[] = 'c.source = %s';
            $values[] = $source;
        }
        
        if ($assigned_to === 'unassigned') {
            $where[] = 'c.assigned_to IS NULL';
        } elseif ($assigned_to) {
            $where[] = 'c.assigned_to = %d';
            $values[] = intval($assigned_to);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT c.*, u.display_name as assigned_to_name 
                FROM {$this->table_customers} c 
                LEFT JOIN {$wpdb->users} u ON c.assigned_to = u.ID 
                WHERE $where_clause 
                ORDER BY c.created_at DESC";
        
        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }
        
        $customers = $wpdb->get_results($sql);
        
        // Generate CSV
        $filename = 'crm-customers-' . date('Y-m-d-His') . '.csv';
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/' . $filename;
        
        $fp = fopen($filepath, 'w');
        
        // Header row
        fputcsv($fp, array(
            'ID', 'Email', 'First Name', 'Last Name', 'Phone', 'Company',
            'Source', 'Status', 'Assigned To', 'Bookings', 'Revenue',
            'Created At', 'Last Activity'
        ));
        
        foreach ($customers as $customer) {
            fputcsv($fp, array(
                $customer->id,
                $customer->email,
                $customer->first_name,
                $customer->last_name,
                $customer->phone,
                $customer->company,
                self::$sources[$customer->source] ?? $customer->source,
                self::$statuses[$customer->status] ?? $customer->status,
                $customer->assigned_to_name ?: '',
                $customer->bookings_count,
                $customer->total_revenue,
                $customer->created_at,
                $customer->last_activity_at
            ));
        }
        
        fclose($fp);
        
        wp_send_json_success(array(
            'message' => 'Export ready',
            'url' => $upload_dir['baseurl'] . '/' . $filename
        ));
    }
    
    /**
     * AJAX: Run migration manually
     */
    public function ajax_run_migration() {
        check_ajax_referer('yolo_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        $migrated = self::migrate_existing_data();
        
        wp_send_json_success(array(
            'message' => sprintf('Migration completed. Imported %d records.', $migrated)
        ));
    }
    
    /**
     * Check due reminders and send notifications
     */
    public function check_due_reminders() {
        global $wpdb;
        
        // Get due reminders that haven't been notified
        // Note: snoozed_until is used as "notified_at" to prevent duplicate emails
        $due_reminders = $wpdb->get_results(
            "SELECT r.*, c.email as customer_email, c.first_name, c.last_name 
             FROM {$this->table_reminders} r 
             JOIN {$this->table_customers} c ON r.customer_id = c.id 
             WHERE r.status = 'pending' 
             AND r.due_date <= NOW()
             AND r.snoozed_until IS NULL"
        );
        
        foreach ($due_reminders as $reminder) {
            // Send email to assigned user
            if ($reminder->assigned_to) {
                $user = get_user_by('id', $reminder->assigned_to);
                if ($user) {
                    $customer_name = trim($reminder->first_name . ' ' . $reminder->last_name);
                    $subject = '[YOLO CRM] Reminder Due: ' . $reminder->reminder_text;
                    
                    $message = sprintf(
                        "A reminder is now due:\n\n" .
                        "Reminder: %s\n" .
                        "Customer: %s (%s)\n" .
                        "Due: %s\n\n" .
                        "View Customer: %s",
                        $reminder->reminder_text,
                        $customer_name,
                        $reminder->customer_email,
                        date('M j, Y g:i A', strtotime($reminder->due_date)),
                        admin_url('admin.php?page=yolo-ys-crm&view=detail&customer=' . $reminder->customer_id)
                    );
                    
                    wp_mail($user->user_email, $subject, $message);
                }
            }
            
            // Mark reminder as notified by updating snoozed_until
            $wpdb->update(
                $this->table_reminders,
                array('snoozed_until' => current_time('mysql')),
                array('id' => $reminder->id)
            );
        }
    }
}

// Initialize CRM
if (!function_exists('yolo_crm')) {
    function yolo_crm() {
        return YOLO_YS_CRM::get_instance();
    }
}
