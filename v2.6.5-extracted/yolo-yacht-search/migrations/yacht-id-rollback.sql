-- ============================================
-- YOLO Yacht Search Plugin
-- yacht_id ROLLBACK: varchar(50) → bigint(20)
-- ============================================
-- Date: November 30, 2025
-- Version: 2.5.5
-- Purpose: Rollback yacht_id migration if needed
-- 
-- WARNING: Only run this if the migration failed!
-- This will convert yacht_id back to bigint(20)
-- ============================================

-- Step 1: Verify current schema
SELECT 
    TABLE_NAME, 
    COLUMN_NAME, 
    DATA_TYPE, 
    CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE '%yolo%'
AND COLUMN_NAME IN ('id', 'yacht_id');

-- ============================================
-- Step 2: Rollback wp_yolo_bookings table
-- ============================================

ALTER TABLE wp_yolo_bookings 
MODIFY COLUMN yacht_id BIGINT(20) NOT NULL;

-- ============================================
-- Step 3: Rollback wp_yolo_prices table
-- ============================================

ALTER TABLE wp_yolo_prices 
MODIFY COLUMN yacht_id BIGINT(20) NOT NULL;

-- ============================================
-- Step 4: Rollback wp_yolo_yacht_extras table
-- ============================================

ALTER TABLE wp_yolo_yacht_extras 
MODIFY COLUMN yacht_id BIGINT(20) NOT NULL;

-- ============================================
-- Step 5: Rollback wp_yolo_yacht_images table
-- ============================================

ALTER TABLE wp_yolo_yacht_images 
MODIFY COLUMN yacht_id BIGINT(20) NOT NULL;

-- ============================================
-- Step 6: Rollback wp_yolo_yachts table
-- ============================================

ALTER TABLE wp_yolo_yachts 
MODIFY COLUMN id BIGINT(20) NOT NULL AUTO_INCREMENT;

-- ============================================
-- Step 7: Verify rollback completed
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
-- ROLLBACK COMPLETE!
-- ============================================
-- 
-- All yacht_id columns have been reverted to bigint(20)
-- 
-- WARNING: If you had large yacht IDs (19 digits), they may be
-- corrupted after this rollback. Restore from backup instead!
-- 
-- ============================================
