<?php
/**
 * Admin Bookings Management
 *
 * @package    YOLO_Yacht_Search
 * @subpackage YOLO_Yacht_Search/admin
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class YOLO_YS_Admin_Bookings extends WP_List_Table {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'booking',
            'plural'   => 'bookings',
            'ajax'     => false
        ));
    }

    /**
     * Get columns
     */
    public function get_columns() {
        return array(
            'cb'                => '<input type="checkbox" />',
            'id'                => 'ID',
            'bm_id'             => 'BM ID',
            'created_at'        => 'Date',
            'yacht_name'        => 'Yacht',
            'customer'          => 'Customer',
            'charter_dates'     => 'Charter Dates',
            'amount'            => 'Amount',
            'payment_status'    => 'Status',
            'actions'           => 'Actions'
        );
    }

    /**
     * Get sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'id'         => array('id', true),
            'created_at' => array('created_at', true),
            'yacht_name' => array('yacht_name', false),
        );
    }

    /**
     * Column checkbox
     */
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="booking[]" value="%s" />', $item['id']);
    }

    /**
     * Column ID
     */
    public function column_id($item) {
        return '<strong>#' . $item['id'] . '</strong>';
    }

    /**
     * Column BM ID
     */
    public function column_bm_id($item) {
        if (!empty($item['bm_reservation_id'])) {
            return '<span class="yolo-bm-id">' . $item['bm_reservation_id'] . '</span>';
        }
        return '<span class="yolo-no-sync" title="Not synced with Booking Manager">—</span>';
    }

    /**
     * Column created_at
     */
    public function column_created_at($item) {
        return date('M d, Y', strtotime($item['created_at'])) . '<br>' .
               '<small>' . date('g:i A', strtotime($item['created_at'])) . '</small>';
    }

    /**
     * Column yacht_name
     */
    public function column_yacht_name($item) {
        return '<strong>' . esc_html($item['yacht_name']) . '</strong>';
    }

    /**
     * Column customer
     */
    public function column_customer($item) {
        return esc_html($item['customer_name']) . '<br>' .
               '<small><a href="mailto:' . esc_attr($item['customer_email']) . '">' . 
               esc_html($item['customer_email']) . '</a></small>';
    }

    /**
     * Column charter_dates
     */
    public function column_charter_dates($item) {
        return date('M d', strtotime($item['date_from'])) . ' - ' . 
               date('M d, Y', strtotime($item['date_to']));
    }

    /**
     * Column amount
     */
    public function column_amount($item) {
        $total = YOLO_YS_Price_Formatter::format_price($item['total_price'], $item['currency']);
        $deposit = YOLO_YS_Price_Formatter::format_price($item['deposit_paid'], $item['currency']);
        $remaining = YOLO_YS_Price_Formatter::format_price($item['remaining_balance'], $item['currency']);
        
        return '<strong>' . $total . '</strong><br>' .
               '<small>Deposit: ' . $deposit . '</small><br>' .
               '<small>Balance: ' . $remaining . '</small>';
    }

    /**
     * Column payment_status
     */
    public function column_payment_status($item) {
        $status = $item['payment_status'];
        $class = '';
        $label = '';
        
        switch ($status) {
            case 'deposit_paid':
                $class = 'yolo-status-partial';
                $label = '⚠️ Deposit Paid';
                break;
            case 'fully_paid':
                $class = 'yolo-status-paid';
                $label = '✅ Fully Paid';
                break;
            case 'cancelled':
                $class = 'yolo-status-cancelled';
                $label = '❌ Cancelled';
                break;
            default:
                $class = 'yolo-status-pending';
                $label = '⏳ Pending';
        }
        
        return '<span class="' . $class . '">' . $label . '</span>';
    }

    /**
     * Column actions
     */
    public function column_actions($item) {
        $actions = array();
        
        $actions[] = sprintf(
            '<a href="?page=yolo-ys-bookings&action=view&booking_id=%s">View</a>',
            $item['id']
        );
        
        if ($item['payment_status'] === 'deposit_paid') {
            $actions[] = sprintf(
                '<a href="?page=yolo-ys-bookings&action=send_reminder&booking_id=%s">Send Reminder</a>',
                $item['id']
            );
        }
        
        // Delete action with confirmation
        $actions[] = sprintf(
            '<a href="#" class="yolo-delete-booking" data-booking-id="%s" data-customer="%s" style="color: #dc3545;">Delete</a>',
            $item['id'],
            esc_attr($item['customer_name'])
        );
        
        return implode(' | ', $actions);
    }

    /**
     * Prepare items
     */
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bookings';

        // Pagination
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Sorting
        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'created_at';
        $order = (!empty($_GET['order'])) ? sanitize_text_field($_GET['order']) : 'DESC';

        // Filters
        $where = array('1=1');
        $where_values = array();

        if (!empty($_GET['payment_status'])) {
            $where[] = 'payment_status = %s';
            $where_values[] = sanitize_text_field($_GET['payment_status']);
        }

        if (!empty($_GET['yacht_id'])) {
            $where[] = 'yacht_id = %s';
            $where_values[] = sanitize_text_field($_GET['yacht_id']);
        }

        if (!empty($_GET['search'])) {
            $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search'])) . '%';
            $where[] = '(customer_name LIKE %s OR customer_email LIKE %s)';
            $where_values[] = $search;
            $where_values[] = $search;
        }

        $where_clause = implode(' AND ', $where);

        // Get total items
        if (!empty($where_values)) {
            $total_items = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}",
                $where_values
            ));
        } else {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}");
        }

        // Get items
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $query_values = array_merge($where_values, array($per_page, $offset));
        
        $this->items = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);

        // Set pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));

        // Set columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
    }

    /**
     * Display filters
     */
    public function extra_tablenav($which) {
        if ($which === 'top') {
            global $wpdb;
            $table_yachts = $wpdb->prefix . 'yolo_yachts';
            
            echo '<div class="alignleft actions">';
            
            // Payment status filter
            echo '<select name="payment_status">';
            echo '<option value="">All Statuses</option>';
            $statuses = array(
                'deposit_paid' => 'Deposit Paid',
                'fully_paid' => 'Fully Paid',
                'cancelled' => 'Cancelled'
            );
            foreach ($statuses as $value => $label) {
                $selected = (isset($_GET['payment_status']) && $_GET['payment_status'] === $value) ? 'selected' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            
            // Yacht filter
            $yachts = $wpdb->get_results("SELECT id, name FROM {$table_yachts} ORDER BY name");
            echo '<select name="yacht_id">';
            echo '<option value="">All Yachts</option>';
            foreach ($yachts as $yacht) {
                $selected = (isset($_GET['yacht_id']) && $_GET['yacht_id'] == $yacht->id) ? 'selected' : '';
                echo '<option value="' . esc_attr($yacht->id) . '" ' . $selected . '>' . esc_html($yacht->name) . '</option>';
            }
            echo '</select>';
            
            submit_button('Filter', 'button', 'filter_action', false);
            
            // Export button
            echo ' <a href="' . admin_url('admin.php?page=yolo-ys-bookings&action=export_csv') . '" class="button">Export to CSV</a>';
            
            echo '</div>';
        }
    }

    /**
     * Display search box
     */
    public function search_box($text, $input_id) {
        if (empty($_REQUEST['s']) && !$this->has_items()) {
            return;
        }

        $input_id = $input_id . '-search-input';

        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="' . esc_attr($input_id) . '">' . esc_html($text) . ':</label>';
        echo '<input type="search" id="' . esc_attr($input_id) . '" name="search" value="' . esc_attr(isset($_REQUEST['search']) ? $_REQUEST['search'] : '') . '" placeholder="Search by name or email" />';
        submit_button($text, '', '', false, array('id' => 'search-submit'));
        echo '</p>';
    }
}
