<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base Manager Database Tables
 *
 * Creates and manages database tables for base manager system
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.0
 */

class YOLO_YS_Base_Manager_Database {

    /**
     * Create base manager tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Yachts table
        $table_yachts = $wpdb->prefix . 'yolo_bm_yachts';
        $sql_yachts = "CREATE TABLE $table_yachts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_name varchar(255) NOT NULL,
            yacht_model varchar(255) NOT NULL,
            company_logo varchar(500) DEFAULT NULL,
            boat_logo varchar(500) DEFAULT NULL,
            owner_name varchar(255) NOT NULL,
            owner_surname varchar(255) NOT NULL,
            owner_mobile varchar(50) NOT NULL,
            owner_email varchar(255) NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_yachts);

        // Equipment categories table
        $table_equipment = $wpdb->prefix . 'yolo_bm_equipment_categories';
        $sql_equipment = "CREATE TABLE $table_equipment (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            category_name varchar(255) NOT NULL,
            items longtext NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        dbDelta($sql_equipment);

        // Check-ins table
        $table_checkins = $wpdb->prefix . 'yolo_bm_checkins';
        $sql_checkins = "CREATE TABLE $table_checkins (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) NOT NULL,
            yacht_id bigint(20) NOT NULL,
            checklist_data longtext NOT NULL,
            signature text DEFAULT NULL,
            guest_signature text DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'draft',
            completed_by bigint(20) NOT NULL,
            guest_signed_at datetime DEFAULT NULL,
            pdf_url varchar(500) DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        dbDelta($sql_checkins);

        // Check-outs table
        $table_checkouts = $wpdb->prefix . 'yolo_bm_checkouts';
        $sql_checkouts = "CREATE TABLE $table_checkouts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) NOT NULL,
            yacht_id bigint(20) NOT NULL,
            checklist_data longtext NOT NULL,
            signature text DEFAULT NULL,
            guest_signature text DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'draft',
            completed_by bigint(20) NOT NULL,
            guest_signed_at datetime DEFAULT NULL,
            pdf_url varchar(500) DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        dbDelta($sql_checkouts);

        // Photos table for check-in/check-out documentation
        $table_photos = $wpdb->prefix . 'yolo_bm_photos';
        $sql_photos = "CREATE TABLE $table_photos (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            document_type varchar(20) NOT NULL,
            document_id bigint(20) NOT NULL,
            category varchar(100) NOT NULL,
            file_url varchar(500) NOT NULL,
            thumbnail_url varchar(500) DEFAULT NULL,
            caption text DEFAULT NULL,
            notes text DEFAULT NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            uploaded_by bigint(20) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY document_type_id (document_type, document_id),
            KEY category (category)
        ) $charset_collate;";
        dbDelta($sql_photos);

        // Warehouse table
        $table_warehouse = $wpdb->prefix . 'yolo_bm_warehouse';
        $sql_warehouse = "CREATE TABLE $table_warehouse (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            item_name varchar(255) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 0,
            expiry_date date DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            category varchar(100) DEFAULT 'other',
            unit varchar(50) DEFAULT 'pcs',
            notification_settings longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        dbDelta($sql_warehouse);

        error_log('YOLO YS: Base Manager database tables created');
    }
}
