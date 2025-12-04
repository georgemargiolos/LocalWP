# yacht_id Migration Instructions

**Date:** November 30, 2025  
**Plugin Version:** 2.5.5  
**Migration:** bigint(20) → varchar(50)

---

## 📋 OVERVIEW

This migration converts the `yacht_id` column from `bigint(20)` to `varchar(50)` to prevent integer overflow for large yacht IDs (19 digits).

**Affected Tables:**
1. `wp_yolo_yachts` (id column)
2. `wp_yolo_yacht_images` (yacht_id column)
3. `wp_yolo_yacht_extras` (yacht_id column)
4. `wp_yolo_prices` (yacht_id column)
5. `wp_yolo_bookings` (yacht_id column)

---

## ⚠️ BEFORE YOU START

### 1. **BACKUP YOUR DATABASE!**

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin
2. Select your WordPress database
3. Click "Export" tab
4. Click "Go" to download backup

**Option B: Using command line**
```bash
mysqldump -u root -p your_database_name > backup_before_migration.sql
```

**Option C: Using LocalWP**
1. Right-click your site in LocalWP
2. Go to "Database" → "Adminer" or "phpMyAdmin"
3. Export database

### 2. **Check Your Table Prefix**

Your WordPress might use a different table prefix than `wp_`. Check your `wp-config.php`:

```php
$table_prefix = 'wp_';  // ← This might be different!
```

If it's different (e.g., `wp_abc123_`), you'll need to replace `wp_` with your prefix in the SQL script.

---

## 🚀 MIGRATION METHODS

### **Method 1: Using phpMyAdmin (Recommended for Beginners)**

1. **Open phpMyAdmin**
   - LocalWP: Right-click site → Database → Adminer/phpMyAdmin
   - Or visit: `http://localhost/phpmyadmin`

2. **Select your WordPress database**
   - Usually named after your site

3. **Click "SQL" tab**

4. **Copy and paste** the contents of `yacht-id-migration.sql`

5. **Click "Go"** to execute

6. **Check results**
   - Scroll down to see verification queries
   - All yacht_id columns should show `varchar(50)`
   - NULL count should be 0 for all tables

---

### **Method 2: Using MySQL Command Line**

1. **Open terminal/command prompt**

2. **Navigate to migrations folder**
```bash
cd /path/to/LocalWP/yolo-yacht-search/migrations
```

3. **Run migration**
```bash
mysql -u root -p your_database_name < yacht-id-migration.sql
```

4. **Enter password** when prompted

---

### **Method 3: Using WP-CLI (Advanced)**

1. **Open terminal in LocalWP**
   - Right-click site → "Open Site Shell"

2. **Run migration**
```bash
wp db query < /path/to/yacht-id-migration.sql
```

---

## ✅ VERIFICATION

After running the migration, verify it worked:

### 1. **Check Schema**

Run this query in phpMyAdmin:

```sql
SELECT 
    TABLE_NAME, 
    COLUMN_NAME, 
    DATA_TYPE, 
    CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE '%yolo%'
AND COLUMN_NAME IN ('id', 'yacht_id')
ORDER BY TABLE_NAME;
```

**Expected result:** All `yacht_id` columns should show:
- DATA_TYPE: `varchar`
- CHARACTER_MAXIMUM_LENGTH: `50`

### 2. **Check Data Integrity**

Run this query:

```sql
SELECT COUNT(*) as total_yachts FROM wp_yolo_yachts;
SELECT COUNT(*) as total_prices FROM wp_yolo_prices;
SELECT COUNT(*) as total_bookings FROM wp_yolo_bookings;
```

**Expected result:** Counts should match your data (not 0 unless you have no data)

### 3. **Test Plugin Functionality**

1. Go to WordPress admin
2. Navigate to YOLO Yacht Search settings
3. Click "Sync Yachts" button
4. Check for any errors in the sync log
5. Visit your yacht search page
6. Search for yachts
7. Verify results display correctly

---

## 🔄 ROLLBACK (If Something Goes Wrong)

If the migration fails or causes issues:

### **Option 1: Restore from Backup**

**Using phpMyAdmin:**
1. Drop all `wp_yolo_*` tables
2. Click "Import" tab
3. Upload your backup SQL file
4. Click "Go"

**Using command line:**
```bash
mysql -u root -p your_database_name < backup_before_migration.sql
```

### **Option 2: Run Rollback Script**

1. Open phpMyAdmin
2. Click "SQL" tab
3. Copy and paste contents of `yacht-id-rollback.sql`
4. Click "Go"

**WARNING:** Rollback may corrupt large yacht IDs (19 digits). Use backup restore instead!

---

## 📊 WHAT THE MIGRATION DOES

### Before Migration:
```sql
CREATE TABLE wp_yolo_yachts (
    id bigint(20) NOT NULL AUTO_INCREMENT,  -- ❌ Overflows at 19 digits
    ...
);

CREATE TABLE wp_yolo_prices (
    yacht_id bigint(20) NOT NULL,  -- ❌ Can't store large IDs
    ...
);
```

### After Migration:
```sql
CREATE TABLE wp_yolo_yachts (
    id varchar(50) NOT NULL,  -- ✅ Handles any length ID
    ...
);

CREATE TABLE wp_yolo_prices (
    yacht_id varchar(50) NOT NULL,  -- ✅ Matches parent table
    ...
);
```

---

## 🐛 TROUBLESHOOTING

### **Error: "Table doesn't exist"**

**Cause:** Wrong table prefix or tables not created yet

**Solution:** 
1. Check your table prefix in `wp-config.php`
2. Replace `wp_` in SQL script with your prefix
3. Or run plugin activation to create tables first

### **Error: "Cannot change column 'id': used in a foreign key constraint"**

**Cause:** Foreign key relationships exist

**Solution:** The script should handle this, but if not:
1. Drop foreign keys first
2. Run migration
3. Recreate foreign keys

### **Error: "Data too long for column"**

**Cause:** Existing data longer than 50 characters (very unlikely)

**Solution:** 
1. Check your data: `SELECT id, LENGTH(id) FROM wp_yolo_yachts ORDER BY LENGTH(id) DESC LIMIT 10;`
2. Increase varchar length if needed: Change `VARCHAR(50)` to `VARCHAR(100)`

### **Migration runs but yacht search doesn't work**

**Cause:** Cache or code mismatch

**Solution:**
1. Clear WordPress cache (if using caching plugin)
2. Deactivate and reactivate YOLO Yacht Search plugin
3. Run "Sync Yachts" from plugin settings
4. Check WordPress error log for specific errors

---

## 📝 POST-MIGRATION CHECKLIST

- [ ] Database backup created
- [ ] Migration SQL executed successfully
- [ ] Schema verification passed (all varchar(50))
- [ ] Data integrity check passed (no NULL values)
- [ ] Plugin still activated in WordPress
- [ ] Yacht sync works without errors
- [ ] Search functionality works
- [ ] Yacht details page displays correctly
- [ ] Booking flow works (if testing)
- [ ] No errors in WordPress debug log

---

## 🆘 NEED HELP?

If you encounter issues:

1. **Check WordPress debug log**
   - Enable: `define('WP_DEBUG_LOG', true);` in `wp-config.php`
   - View: `wp-content/debug.log`

2. **Check database error log**
   - LocalWP: Right-click site → "Open Site Logs"
   - Look for MySQL errors

3. **Restore from backup**
   - Don't panic! You have a backup
   - Restore and try again

4. **Contact support**
   - Provide error messages
   - Provide WordPress version
   - Provide PHP version
   - Provide MySQL version

---

## ✅ SUCCESS!

After successful migration:

1. ✅ Large yacht IDs (19 digits) will work correctly
2. ✅ No more integer overflow errors
3. ✅ API sync will work with all yacht IDs
4. ✅ Booking system will handle any yacht ID
5. ✅ Future-proof for even larger IDs

**Your plugin is now fully production-ready!** 🚀

---

## 📁 FILES IN THIS FOLDER

- `yacht-id-migration.sql` - Main migration script (run this)
- `yacht-id-rollback.sql` - Rollback script (emergency only)
- `MIGRATION-INSTRUCTIONS.md` - This file
- `migrate-yacht-id-to-varchar.php` - PHP migration script (alternative method)

---

**Estimated Time:** 2-5 minutes  
**Difficulty:** Easy (with backup)  
**Risk:** Low (if you have backup)

**Good luck!** 🍀
