-- Fix Unit Frequency field for Natural Gas Simple trade type
-- Update the template to ensure Unit Frequency is properly enabled

-- First, let's check the current template values
SELECT 'Current Template for Trade Type 10:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 10;

-- Update the Unit Frequency field to ensure it's enabled (1)
UPDATE tblB2TTemplates 
SET fldUnitFrequency = 1 
WHERE fkTypeID = 10 AND fldTemplateName = 'Natural Gas Simple Template';

-- Verify the update
SELECT 'Updated Template for Trade Type 10:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 10;

-- Also check what the original trade type 6 has for comparison
SELECT 'Original Trade Type 6 Template for comparison:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 6;
