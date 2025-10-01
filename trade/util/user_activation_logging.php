<?php
/**
 * User Activation & Account Management Logging System
 * Dedicated logging for user lifecycle events
 */

// Log user activation email sent
function logUserActivationEmailSent($brokerID, $brokerName, $email, $username, $adminID) {
    global $tblUserActivationLog;
    
    $logMessage = "Password setup email sent to broker: $brokerName ($email) - Username: $username";
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldAdminID, fldBrokerID, fldAction, fldDetails, fldStatus) 
                  VALUES (NOW(), ?, ?, 'ACTIVATION_EMAIL_SENT', ?, 'SUCCESS')";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "iis", $adminID, $brokerID, $logMessage);
    return mysqli_stmt_execute($stmt);
}

// Log failed email attempt
function logUserActivationEmailFailed($brokerID, $brokerName, $email, $username, $adminID, $errorReason = '') {
    global $tblUserActivationLog;
    
    $logMessage = "FAILED to send password setup email to broker: $brokerName ($email) - Username: $username";
    if($errorReason) {
        $logMessage .= " - Reason: $errorReason";
    }
    
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldAdminID, fldBrokerID, fldAction, fldDetails, fldStatus) 
                  VALUES (NOW(), ?, ?, 'ACTIVATION_EMAIL_FAILED', ?, 'FAILED')";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "iis", $adminID, $brokerID, $logMessage);
    return mysqli_stmt_execute($stmt);
}

// Log password setup attempt
function logPasswordSetupAttempt($brokerID, $brokerName, $username, $ipAddress, $userAgent) {
    global $tblUserActivationLog;
    
    $logMessage = "Password setup attempt for broker: $brokerName - Username: $username - IP: $ipAddress";
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldBrokerID, fldAction, fldDetails, fldStatus, fldIPAddress, fldUserAgent) 
                  VALUES (NOW(), ?, 'PASSWORD_SETUP_ATTEMPT', ?, 'ATTEMPT', ?, ?)";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "isss", $brokerID, $logMessage, $ipAddress, $userAgent);
    return mysqli_stmt_execute($stmt);
}

// Log password setup success
function logPasswordSetupSuccess($brokerID, $brokerName, $username, $ipAddress, $userAgent) {
    global $tblUserActivationLog;
    
    $logMessage = "Password setup completed successfully for broker: $brokerName - Username: $username - IP: $ipAddress";
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldBrokerID, fldAction, fldDetails, fldStatus, fldIPAddress, fldUserAgent) 
                  VALUES (NOW(), ?, 'PASSWORD_SETUP_SUCCESS', ?, 'SUCCESS', ?, ?)";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "isss", $brokerID, $logMessage, $ipAddress, $userAgent);
    return mysqli_stmt_execute($stmt);
}

// Log password setup failure
function logPasswordSetupFailure($brokerID, $brokerName, $username, $ipAddress, $userAgent, $failureReason) {
    global $tblUserActivationLog;
    
    $logMessage = "Password setup failed for broker: $brokerName - Username: $username - IP: $ipAddress - Reason: $failureReason";
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldBrokerID, fldAction, fldDetails, fldStatus, fldIPAddress, fldUserAgent) 
                  VALUES (NOW(), ?, 'PASSWORD_SETUP_FAILED', ?, 'FAILED', ?, ?)";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "isss", $brokerID, $logMessage, $ipAddress, $userAgent);
    return mysqli_stmt_execute($stmt);
}

// Log invalid/expired token attempt
function logInvalidTokenAttempt($token, $ipAddress, $userAgent, $reason = '') {
    global $tblUserActivationLog;
    
    $logMessage = "Invalid/expired token attempt - Token: " . substr($token, 0, 8) . "... - IP: $ipAddress";
    if($reason) {
        $logMessage .= " - Reason: $reason";
    }
    
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldAction, fldDetails, fldStatus, fldIPAddress, fldUserAgent) 
                  VALUES (NOW(), 'INVALID_TOKEN_ATTEMPT', ?, 'SECURITY_ALERT', ?, ?)";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "sss", $logMessage, $ipAddress, $userAgent);
    return mysqli_stmt_execute($stmt);
}

// Log broker status changes
function logBrokerStatusChange($brokerID, $brokerName, $oldStatus, $newStatus, $adminID, $reason = '') {
    global $tblUserActivationLog;
    
    $logMessage = "Broker status changed: $brokerName - $oldStatus → $newStatus";
    if($reason) {
        $logMessage .= " - Reason: $reason";
    }
    
    $logQuery = "INSERT INTO $tblUserActivationLog (fldTimestamp, fldAdminID, fldBrokerID, fldAction, fldDetails, fldStatus) 
                  VALUES (NOW(), ?, ?, 'BROKER_STATUS_CHANGE', ?, 'INFO')";
    
    $stmt = mysqli_prepare($_SESSION['db'], $logQuery);
    mysqli_stmt_bind_param($stmt, "iis", $adminID, $brokerID, $logMessage);
    return mysqli_stmt_execute($stmt);
}

// Get user activation logs for reporting
function getUserActivationLogs($filters = array()) {
    global $tblUserActivationLog;
    
    $whereConditions = array();
    $params = array();
    $types = '';
    
    // Filter by date range
    if(isset($filters['startDate']) && $filters['startDate']) {
        $whereConditions[] = "fldTimestamp >= ?";
        $params[] = $filters['startDate'];
        $types .= 's';
    }
    
    if(isset($filters['endDate']) && $filters['endDate']) {
        $whereConditions[] = "fldTimestamp <= ?";
        $params[] = $filters['endDate'];
        $types .= 's';
    }
    
    // Filter by action type
    if(isset($filters['action']) && $filters['action']) {
        $whereConditions[] = "fldAction = ?";
        $params[] = $filters['action'];
        $types .= 's';
    }
    
    // Filter by status
    if(isset($filters['status']) && $filters['status']) {
        $whereConditions[] = "fldStatus = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }
    
    // Filter by broker
    if(isset($filters['brokerID']) && $filters['brokerID']) {
        $whereConditions[] = "fldBrokerID = ?";
        $params[] = $filters['brokerID'];
        $types .= 'i';
    }
    
    // Build query
    $query = "SELECT * FROM $tblUserActivationLog";
    if(!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    $query .= " ORDER BY fldTimestamp DESC";
    
    // Execute with filters
    if(!empty($params)) {
        $stmt = mysqli_prepare($_SESSION['db'], $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    } else {
        return mysqli_query($_SESSION['db'], $query);
    }
}
?>
