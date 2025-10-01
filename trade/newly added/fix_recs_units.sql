-- Fix the units field for trade type 10 to be consistent with RECs category
-- This trade type should have "RECs" as units, not "MMBTU/Bloc"

UPDATE tblB2TTradeType 
SET fldUnits = 'RECs'
WHERE pkTypeID = 10;

-- Verify the fix
SELECT 'Fixed Trade Type 10:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID 
FROM tblB2TTradeType 
WHERE pkTypeID = 10;

-- Show all RECs trade types to confirm consistency
SELECT 'All RECs Trade Types:' as info;
SELECT pkTypeID, fldTradeName, fldProductGroup, fldUnits, fkProductIDDefault, fldCHUnitsID, fldCHProdID 
FROM tblB2TTradeType 
WHERE fldProductGroup = 'RECs'
ORDER BY pkTypeID;
