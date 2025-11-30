-- ============================================
-- YOLO Yacht Search Plugin
-- yacht_id Migration: bigint(20) → varchar(50)
-- ============================================
-- Date: November 30, 2025
-- Version: 2.5.5
-- Purpose: Fix integer overflow for large yacht IDs (19 digits)
-- 
-- IMPORTANT: BACKUP YOUR DATABASE BEFORE RUNNING THIS SCRIPT!
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
-- Step 2: Modify wp_yolo_yachts table
-- ============================================

-- Drop foreign key constraints first (if any exist)
-- Note: Adjust table prefix 'wp_' if your WordPress uses a different prefix

-- Modify the primary key column
ALTER TABLE wp_yolo_yachts 
MODIFY COLUMN id VARCHAR(50) NOT NULL;

-- ============================================
-- Step 3: Modify wp_yolo_yacht_images table
-- ============================================

ALTER TABLE wp_yolo_yacht_images 
MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL;

-- ============================================
-- Step 4: Modify wp_yolo_yacht_extras table
-- ============================================

ALTER TABLE wp_yolo_yacht_extras 
MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL;

-- ============================================
-- Step 5: Modify wp_yolo_prices table
-- ============================================

ALTER TABLE wp_yolo_prices 
MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL;

-- ============================================
-- Step 6: Modify wp_yolo_bookings table
-- ============================================

ALTER TABLE wp_yolo_bookings 
MODIFY COLUMN yacht_id VARCHAR(50) NOT NULL;

-- ============================================
-- Step 7: Verify migration completed
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
-- Step 8: Verify data integrity
-- ============================================

-- Check for any NULL values (shouldn't be any)
SELECT 'wp_yolo_yachts' as table_name, COUNT(*) as null_count 
FROM wp_yolo_yachts WHERE id IS NULL
UNION ALL
SELECT 'wp_yolo_yacht_images', COUNT(*) 
FROM wp_yolo_yacht_images WHERE yacht_id IS NULL
UNION ALL
SELECT 'wp_yolo_yacht_extras', COUNT(*) 
FROM wp_yolo_yacht_extras WHERE yacht_id IS NULL
UNION ALL
SELECT 'wp_yolo_prices', COUNT(*) 
FROM wp_yolo_prices WHERE yacht_id IS NULL
UNION ALL
SELECT 'wp_yolo_bookings', COUNT(*) 
FROM wp_yolo_bookings WHERE yacht_id IS NULL;

-- Check sample data to ensure IDs are preserved
SELECT 'Sample yacht IDs:' as info;
SELECT id, name FROM wp_yolo_yachts LIMIT 5;

SELECT 'Sample price records:' as info;
SELECT yacht_id, date_from, price FROM wp_yolo_prices LIMIT 5;

-- ============================================
-- MIGRATION COMPLETE!
-- ============================================
-- 
-- All yacht_id columns have been converted from bigint(20) to varchar(50)
-- 
-- Tables modified:
-- 1. wp_yolo_yachts (id column)
-- 2. wp_yolo_yacht_images (yacht_id column)
-- 3. wp_yolo_yacht_extras (yacht_id column)
-- 4. wp_yolo_prices (yacht_id column)
-- 5. wp_yolo_bookings (yacht_id column)
-- 
-- Next steps:
-- 1. Verify data integrity (check queries above)
-- 2. Test yacht search functionality
-- 3. Test booking creation
-- 4. Monitor error logs for any issues
-- 
-- ============================================
