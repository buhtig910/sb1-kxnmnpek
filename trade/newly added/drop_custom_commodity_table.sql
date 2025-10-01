-- Drop the tblB2TCustomCommodityFields table since it's not being used properly
-- This table was created accidentally and is causing confusion

-- Step 1: Drop the table
DROP TABLE IF EXISTS tblB2TCustomCommodityFields;

-- Step 2: Verify the table is gone
SELECT 'Table dropped successfully' as status;

-- Step 3: Show remaining tables for reference
SHOW TABLES LIKE 'tblB2T%';
