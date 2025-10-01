-- User Activation & Account Management Logging Table
-- This table tracks all user lifecycle events for security and audit purposes

CREATE TABLE IF NOT EXISTS tblB2TUserActivationLog (
    pkLogID INT AUTO_INCREMENT PRIMARY KEY,
    fldTimestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fldAdminID INT NULL COMMENT 'ID of admin who performed the action',
    fldBrokerID INT NULL COMMENT 'ID of broker affected by the action',
    fldAction VARCHAR(50) NOT NULL COMMENT 'Type of action performed',
    fldDetails TEXT NOT NULL COMMENT 'Detailed description of the action',
    fldStatus VARCHAR(20) NOT NULL COMMENT 'SUCCESS, FAILED, ATTEMPT, INFO, SECURITY_ALERT',
    fldIPAddress VARCHAR(45) NULL COMMENT 'IP address of the user (supports IPv6)',
    fldUserAgent TEXT NULL COMMENT 'User agent string for security tracking',
    fldAdditionalData JSON NULL COMMENT 'Additional structured data if needed',
    
    -- Indexes for efficient querying
    INDEX idx_timestamp (fldTimestamp),
    INDEX idx_action (fldAction),
    INDEX idx_status (fldStatus),
    INDEX idx_broker (fldBrokerID),
    INDEX idx_admin (fldAdminID),
    INDEX idx_action_status (fldAction, fldStatus),
    INDEX idx_broker_timestamp (fldBrokerID, fldTimestamp),
    
    -- Foreign key constraints
    FOREIGN KEY (fldAdminID) REFERENCES tblB2TBroker(pkBrokerID) ON DELETE SET NULL,
    FOREIGN KEY (fldBrokerID) REFERENCES tblB2TBroker(pkBrokerID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data to show the structure
INSERT INTO tblB2TUserActivationLog (fldAction, fldDetails, fldStatus) VALUES 
('SYSTEM_SETUP', 'User activation logging system initialized', 'INFO');

-- Create view for common reporting queries
CREATE OR REPLACE VIEW vwUserActivationSummary AS
SELECT 
    DATE(fldTimestamp) as LogDate,
    fldAction,
    fldStatus,
    COUNT(*) as Count,
    COUNT(DISTINCT fldBrokerID) as UniqueBrokers,
    COUNT(DISTINCT fldAdminID) as UniqueAdmins
FROM tblB2TUserActivationLog 
GROUP BY DATE(fldTimestamp), fldAction, fldStatus
ORDER BY LogDate DESC, fldAction, fldStatus;

-- Create view for security alerts
CREATE OR REPLACE VIEW vwSecurityAlerts AS
SELECT 
    fldTimestamp,
    fldAction,
    fldDetails,
    fldIPAddress,
    fldUserAgent,
    fldBrokerID
FROM tblB2TUserActivationLog 
WHERE fldStatus = 'SECURITY_ALERT' 
   OR fldAction IN ('INVALID_TOKEN_ATTEMPT', 'PASSWORD_SETUP_FAILED')
ORDER BY fldTimestamp DESC;
