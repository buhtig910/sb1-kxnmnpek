-- Fix RECs Product and Product Type entries to address previous issues
-- This script fixes the fldActive warnings and uses a smaller product ID

-- Step 1: Update the RECs Product Type to set fldActive = 1
UPDATE tblB2TProductType 
SET fldActive = 1 
WHERE pkProductTypeID = 999;

-- Step 2: Update the RECs Product to set fldActive = 1
UPDATE tblB2TProduct 
SET fldActive = 1 
WHERE pkProductID = 999;

-- Step 3: Check what the actual range limit is for fkProductIDDefault
SELECT 'Checking fkProductIDDefault column constraints:' as info;
SHOW COLUMNS FROM tblB2TTradeType LIKE 'fkProductIDDefault';

-- Step 4: Since 999 is too large, let's use a smaller ID that fits
-- First, let's find an available smaller ID
SELECT 'Finding available product ID:' as info;
SELECT MIN(pkProductID) as next_available_id 
FROM tblB2TProduct 
WHERE pkProductID > 100 
ORDER BY pkProductID 
LIMIT 1;

-- Step 5: Create a new RECs product with a smaller ID (let's use 100)
INSERT INTO tblB2TProduct (pkProductID, fldProductName, fldActive) 
VALUES (100, 'RECs', 1) 
ON DUPLICATE KEY UPDATE fldProductName = VALUES(fldProductName), fldActive = VALUES(fldActive);

-- Step 6: Create a new RECs product type with a smaller ID (let's use 100)
INSERT INTO tblB2TProductType (pkProductTypeID, fldProductTypeName, fldActive) 
VALUES (100, 'RECs', 1) 
ON DUPLICATE KEY UPDATE fldProductTypeName = VALUES(fldProductTypeName), fldActive = VALUES(fldActive);

-- Step 7: Update RECs trade types to use the smaller product ID
UPDATE tblB2TTradeType 
SET fkProductIDDefault = 100, 
    fldCHProdID = 100 
WHERE pkTypeID IN (9, 10);

-- Step 8: Clean up the old 999 entries (optional)
DELETE FROM tblB2TProduct WHERE pkProductID = 999;
DELETE FROM tblB2TProductType WHERE pkProductTypeID = 999;

-- Verify the final changes
SELECT 'Final RECs Product Type:' as info;
SELECT * FROM tblB2TProductType WHERE pkProductTypeID = 100;

SELECT 'Final RECs Product:' as info;
SELECT * FROM tblB2TProduct WHERE pkProductID = 100;

SELECT 'Updated Trade Types:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fkProductIDDefault, fldCHProdID 
FROM tblB2TTradeType 
WHERE pkTypeID IN (9, 10);
