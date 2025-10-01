-- SQL Script to add Custom Commodity Trade Type
-- Run this script in your database to enable the new trade type

-- First, let's check what trade types already exist
SELECT 'Current Trade Types:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fldUnits FROM tblB2TTradeType ORDER BY pkTypeID;

-- Find the next available trade type ID
SELECT 'Next Available Trade Type ID:' as info;
SELECT COALESCE(MAX(pkTypeID) + 1, 1) as nextID FROM tblB2TTradeType;

-- Based on your actual table structure from phpMyAdmin:
-- pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID

-- 1. Add new trade type to tblB2TTradeType (using next available ID)
-- Note: Replace 'NEXT_ID' with the actual number from the query above
INSERT INTO tblB2TTradeType (pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID)
SELECT 
    COALESCE(MAX(pkTypeID) + 1, 1), 
    'Custom Commodity', 
    'Custom Commodity', 
    'MMBTU/Block', 
    0, 
    0, 
    0
FROM tblB2TTradeType;

-- Get the ID we just inserted
SET @newTradeTypeID = LAST_INSERT_ID();
SELECT CONCAT('New Trade Type ID: ', @newTradeTypeID) as info;

-- 2. Create template for the new trade type in tblB2TTemplates
-- This template shows most fields but allows for custom commodity-specific fields
-- Note: Column count and names match your actual table structure
INSERT INTO tblB2TTemplates (
    pkTemplateID,
    fldTemplateName,
    fldOffsetIndex,      -- 1 = show offset index
    fldDeliveryType,     -- 1 = show delivery
    fldBuyerPrice,       -- 1 = show buyer price
    fldBuyerCurrency,    -- 1 = show buyer currency
    fldSellerPrice,      -- 1 = show seller price
    fldSellerCurrency,   -- 1 = show seller currency
    fldOptionStyle,      -- 0 = hide option style
    fldOptionType,       -- 0 = hide option type
    fldExerciseType,     -- 0 = hide exercise type
    fldStrike,           -- 0 = hide strike price
    fldStrikeCurrency,   -- 0 = hide strike currency
    fldBlock,            -- 1 = show block
    fldHours,            -- 1 = show hours
    fldTimeZone,         -- 1 = show timezone
    fldUnitFrequency,    -- 1 = show unit frequency
    fldIndexPrice,       -- 1 = show index price
    fldCustomFields,     -- 1 = show custom fields
    fkTypeID
) VALUES (
    NULL,
    'Custom Commodity Template',
    1,  -- Show offset index
    1,  -- Show delivery
    1,  -- Show buyer price
    1,  -- Show buyer currency
    1,  -- Show seller price
    1,  -- Show seller currency
    0,  -- Hide option style
    0,  -- Hide option type
    0,  -- Hide exercise type
    0,  -- Hide strike price
    0,  -- Hide strike currency
    1,  -- Show block
    1,  -- Show hours
    1,  -- Show timezone
    1,  -- Show unit frequency
    1,  -- Show index price
    1,  -- Show custom fields
    @newTradeTypeID  -- Use the ID we just created
);

-- 3. Create table for custom commodity fields
CREATE TABLE IF NOT EXISTS tblB2TCustomCommodityFields (
    pkCustomCommodityID INT AUTO_INCREMENT PRIMARY KEY,
    fkTransID INT NOT NULL,
    fldNewProduct VARCHAR(255),
    fldNewLocation VARCHAR(255),
    fldNewOffsetIndex VARCHAR(255),
    fldNewRegion VARCHAR(255),
    fldCommodityType ENUM('agricultural', 'energy', 'metals', 'softs', 'other'),
    fldPricingModel TEXT,
    fldSettlementTerms TEXT,
    fldUnitType ENUM('MMBTU', 'Block') DEFAULT 'MMBTU',
    FOREIGN KEY (fkTransID) REFERENCES tblB2TTransaction(pkTransactionID) ON DELETE CASCADE,
    INDEX idx_transID (fkTransID)
);

-- 4. Verify the new trade type was added
SELECT 'New Trade Type Details:' as info;
SELECT * FROM tblB2TTradeType WHERE fldTradeName = 'Custom Commodity';

-- 5. Verify the template was created
SELECT 'New Template Details:' as info;
SELECT * FROM tblB2TTemplates WHERE fldTemplateName = 'Custom Commodity Template';

-- 6. Verify the custom fields table was created
SELECT 'Custom Fields Table Structure:' as info;
DESCRIBE tblB2TCustomCommodityFields;
