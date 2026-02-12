-- SQL Command to clear all data from organizations table
-- WARNING: This will delete all records from the organizations table!
-- The table structure will remain, but all data will be removed.

-- Option 1: Delete all records (keeps table structure)
DELETE FROM organizations;

-- Option 2: Truncate table (faster, resets AUTO_INCREMENT)
-- TRUNCATE TABLE organizations;

-- Option 3: Drop and recreate table (complete reset)
-- DROP TABLE IF EXISTS organizations;
-- (Then run create_organizations_table migration again)

-- To verify the table is empty after deletion:
-- SELECT COUNT(*) FROM organizations;



