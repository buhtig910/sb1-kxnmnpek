-- Remove Notes field from tblB2TCustomFields since it's redundant with fldAdditionalInfo
-- The Additional Info field already captures unique trade details

-- First, let's check the current table structure
DESCRIBE tblB2TCustomFields;

-- Remove the Notes field
ALTER TABLE tblB2TCustomFields 
DROP COLUMN fldNotes;

-- Verify the new structure
DESCRIBE tblB2TCustomFields;

-- Show sample data to confirm the field was removed
SELECT * FROM tblB2TCustomFields LIMIT 3;
