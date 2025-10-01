-- SQL Script to create Natural Gas Simple Trade Type (without option fields)
-- This is based on trade type 6 but removes Option Style, Option Type, Exercise Type, Strike, and Strike Currency fields

-- First, let's check what trade types already exist
SELECT 'Current Trade Types:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fldUnits FROM tblB2TTradeType ORDER BY pkTypeID;

-- Find the next available trade type ID
SELECT 'Next Available Trade Type ID:' as info;
SELECT COALESCE(MAX(pkTypeID) + 1, 1) as nextID FROM tblB2TTradeType;

-- 1. Add new trade type to tblB2TTradeType (using next available ID)
-- This will be a copy of trade type 6 (Natural Gas) but simplified
INSERT INTO tblB2TTradeType (pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID)
SELECT 
    COALESCE(MAX(pkTypeID) + 1, 1), 
    'Natural Gas Simple', 
    'Natural Gas', 
    'MMBTU', 
    0, 
    0, 
    0
FROM tblB2TTradeType;

-- Get the ID we just inserted
SET @newTradeTypeID = LAST_INSERT_ID();
SELECT CONCAT('New Trade Type ID: ', @newTradeTypeID) as info;

-- 2. Create template for the new trade type in tblB2TTemplates
-- This template is based on trade type 6 but with option fields disabled
INSERT INTO tblB2TTemplates (
    pkTemplateID,
    fldTemplateName,
    fldOffsetIndex,      -- 1 = show offset index
    fldDeliveryType,     -- 1 = show delivery
    fldBuyerPrice,       -- 1 = show buyer price
    fldBuyerCurrency,    -- 1 = show buyer currency
    fldSellerPrice,      -- 1 = show seller price
    fldSellerCurrency,   -- 1 = show seller currency
    fldOptionStyle,      -- 0 = hide option style (CHANGED FROM 1)
    fldOptionType,       -- 0 = hide option type (CHANGED FROM 1)
    fldExerciseType,     -- 0 = hide exercise type (CHANGED FROM 1)
    fldStrike,           -- 0 = hide strike price (CHANGED FROM 1)
    fldStrikeCurrency,   -- 0 = hide strike currency (CHANGED FROM 1)
    fldBlock,            -- 1 = show block
    fldHours,            -- 1 = show hours
    fldTimeZone,         -- 1 = show timezone
    fldUnitFrequency,    -- 1 = show unit frequency
    fldIndexPrice,       -- 1 = show index price
    fldCustomFields,     -- 0 = no custom fields
    fkTypeID
) VALUES (
    NULL,
    'Natural Gas Simple Template',
    1,  -- Show offset index
    1,  -- Show delivery
    1,  -- Show buyer price
    1,  -- Show buyer currency
    1,  -- Show seller price
    1,  -- Show seller currency
    0,  -- Hide option style (REMOVED)
    0,  -- Hide option type (REMOVED)
    0,  -- Hide exercise type (REMOVED)
    0,  -- Hide strike price (REMOVED)
    0,  -- Hide strike currency (REMOVED)
    1,  -- Show block
    1,  -- Show hours
    1,  -- Show timezone
    1,  -- Show unit frequency
    1,  -- Show index price
    0,  -- No custom fields
    @newTradeTypeID  -- Use the ID we just created
);

-- 3. Verify the new trade type was added
SELECT 'New Trade Type Details:' as info;
SELECT * FROM tblB2TTradeType WHERE fldTradeName = 'Natural Gas Simple';

-- 4. Verify the template was created
SELECT 'New Template Details:' as info;
SELECT * FROM tblB2TTemplates WHERE fldTemplateName = 'Natural Gas Simple Template';

-- 5. Show the differences between original and new template
SELECT 'Comparison - Original Trade Type 6 Template:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 6;

SELECT 'Comparison - New Natural Gas Simple Template:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = @newTradeTypeID;
