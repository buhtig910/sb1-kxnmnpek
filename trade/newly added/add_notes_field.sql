-- Add Notes field to tblB2TCustomFields for RECs trades
-- This allows capturing unique trade details without building specific backend fields

-- First, let's check the current table structure
DESCRIBE tblB2TCustomFields;

-- Add the Notes field after the existing fields
ALTER TABLE tblB2TCustomFields 
ADD COLUMN fldNotes TEXT AFTER fldAdditionalInfo;

-- Verify the new structure
DESCRIBE tblB2TCustomFields;

-- Show sample data to confirm the field was added
SELECT * FROM tblB2TCustomFields LIMIT 3;
