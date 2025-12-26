-- ============================================
-- YOLO Yacht Search Plugin
-- SAFE yacht_id Migration: bigint(20) → varchar(50)
-- ============================================
-- Date: November 30, 2025
-- Version: 2.5.5
-- Purpose: Fix integer overflow for large yacht IDs (19 digits)
-- 
-- This version only modifies tables that exist
-- ============================================

-- Step 1: Check which tables exist
SHOW TABLES LIKE 'wp_yolo%';

-- ============================================
-- Step 2: Modify ONLY if tables exist
-- ============================================

-- Modify wp_yolo_yachts (if exists)
ALTER TABLE wp_yolo_yachts 
MODIFY COLUMN id VARCHAR(50) NOT NULL;

-- Modify wp_yolo_yacht_images (if exists)
-- If this fails, the table doesn't exist - that's OK, skip it
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'wp_yolo_yacht_images');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE wp_yolo_yacht_images MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL',
    'SELECT "Table wp_yolo_yacht_images does not exist, skipping..." as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modify wp_yolo_yacht_extras (if exists)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'wp_yolo_yacht_extras');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE wp_yolo_yacht_extras MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL',
    'SELECT "Table wp_yolo_yacht_extras does not exist, skipping..." as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modify wp_yolo_prices (if exists)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'wp_yolo_prices');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE wp_yolo_prices MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL',
    'SELECT "Table wp_yolo_prices does not exist, skipping..." as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modify wp_yolo_bookings (if exists)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'wp_yolo_bookings');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE wp_yolo_bookings MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL',
    'SELECT "Table wp_yolo_bookings does not exist, skipping..." as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Step 3: Verify what was migrated
-- ============================================

SELECT 
    TABLE_NAME, 
    COLUMN_NAME, 
    DATA_TYPE, 
    CHARACTER_MAXIMUM_LENGTH,
    COLUMN_KEY
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE '%yolo%'
AND COLUMN_NAME IN ('id', 'yacht_id')
ORDER BY TABLE_NAME, COLUMN_NAME;

-- ============================================
-- MIGRATION COMPLETE!
-- ============================================
