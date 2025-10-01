-- Create RECs Product and Product Type entries to avoid INNER JOIN issues
-- This ensures RECs trades can use standard product relationships

-- First, let's check what columns exist in these tables
SELECT 'Checking tblB2TProductType columns:' as info;
DESCRIBE tblB2TProductType;

SELECT 'Checking tblB2TProduct columns:' as info;
DESCRIBE tblB2TProduct;

-- Step 1: Add RECs Product Type (using only essential columns)
INSERT INTO tblB2TProductType (pkProductTypeID, fldProductTypeName) 
VALUES (999, 'RECs') 
ON DUPLICATE KEY UPDATE fldProductTypeName = VALUES(fldProductTypeName);

-- Step 2: Add RECs Product (using only essential columns - removed fkProductTypeID)
INSERT INTO tblB2TProduct (pkProductID, fldProductName) 
VALUES (999, 'RECs') 
ON DUPLICATE KEY UPDATE fldProductName = VALUES(fldProductName);

-- Step 3: Update RECs trade type to use the new product entries
UPDATE tblB2TTradeType 
SET fkProductIDDefault = 999, 
    fldCHProdID = 999 
WHERE pkTypeID = 9;

-- Step 4: Update RECs Option trade type to use the new product entries
UPDATE tblB2TTradeType 
SET fkProductIDDefault = 999, 
    fldCHProdID = 999 
WHERE pkTypeID = 10;

-- Verify the changes
SELECT 'RECs Product Type:' as info;
SELECT * FROM tblB2TProductType WHERE pkProductTypeID = 999;

SELECT 'RECs Product:' as info;
SELECT * FROM tblB2TProduct WHERE pkProductID = 999;

SELECT 'Updated Trade Types:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fkProductIDDefault, fldCHProdID 
FROM tblB2TTradeType 
WHERE pkTypeID IN (9, 10);
