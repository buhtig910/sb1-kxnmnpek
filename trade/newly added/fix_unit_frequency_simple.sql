-- Simple fix to ensure Unit Frequency is enabled for Natural Gas Simple trade type
-- This updates the fldUnitFrequency field to 1 (enabled)

UPDATE tblB2TTemplates 
SET fldUnitFrequency = 1 
WHERE fkTypeID = 10;

-- Verify the update
SELECT 'Updated Template - Unit Frequency should now be 1:' as info;
SELECT pkTemplateID, fldTemplateName, fldUnitFrequency, fkTypeID 
FROM tblB2TTemplates 
WHERE fkTypeID = 10;
