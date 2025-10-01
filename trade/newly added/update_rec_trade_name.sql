-- Create a comprehensive RECs category for all renewable energy credits
-- This will encompass wind, solar, hydro, biomass, geothermal, etc.
-- A single category that all REC types can fall into

-- Step 1: Update the existing trade type 9 to have a comprehensive RECs category
UPDATE tblB2TTradeType 
SET fldProductGroup = 'RECs',
    fldTradeName = 'RECs',
    fldUnits = 'RECs',
    fkProductIDDefault = 0,
    fldCHUnitsID = 0,
    fldCHProdID = 0
WHERE pkTypeID = 9;

-- Step 2: Update any other trade types that might reference "Custom Commodity"
UPDATE tblB2TTradeType 
SET fldTradeName = 'RECs', 
    fldProductGroup = 'RECs'
WHERE fldTradeName = 'New Product 1';

-- Step 3: Update any other trade types that might reference "New Product 2" to be more descriptive
UPDATE tblB2TTradeType 
SET fldTradeName = 'RECs Option',
    fldProductGroup = 'RECs',
    fldUnits = 'RECs'
WHERE fldTradeName = 'New Product 2';

-- Step 4: Verify the changes
SELECT 'Updated Trade Types:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID 
FROM tblB2TTradeType 
WHERE pkTypeID = 9 OR fldTradeName IN ('RECs', 'RECs Option');

-- Step 5: Show all current product groups for reference
SELECT 'All Current Product Groups:' as info;
SELECT DISTINCT fldProductGroup, COUNT(*) as trade_type_count
FROM tblB2TTradeType 
GROUP BY fldProductGroup 
ORDER BY fldProductGroup;
