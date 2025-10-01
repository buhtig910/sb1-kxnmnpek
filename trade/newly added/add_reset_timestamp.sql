-- Add reset timestamp column to tblB2TBroker table
-- This column will store when the password reset key was generated
-- Used for expiring reset links after 15 minutes

ALTER TABLE tblB2TBroker 
ADD COLUMN fldResetTimestamp INT(11) DEFAULT NULL 
COMMENT 'Unix timestamp when password reset key was generated';

-- Add index for better performance on expiration checks
CREATE INDEX idx_reset_timestamp ON tblB2TBroker(fldResetTimestamp);

-- Optional: Clean up any existing reset keys that don't have timestamps
-- UPDATE tblB2TBroker SET fldResetTimestamp = UNIX_TIMESTAMP() WHERE fldConfirmKey IS NOT NULL AND fldResetTimestamp IS NULL;
