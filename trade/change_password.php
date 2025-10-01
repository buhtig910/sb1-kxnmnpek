<?php
session_start();
require_once("functions.php");
require_once("vars.inc.php");

// Check if user is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
    header("Location: index.php?p=login");
    exit;
}

$userID = $_SESSION['userID'];
$username = $_SESSION['username'];
$userType = $_SESSION['userType'];

// Check if this is the user's first login
// If the fldFirstLogin column doesn't exist yet, treat as not first login
$isFirstLogin = false;
try {
	$query = "SELECT fldFirstLogin FROM tblB2TBroker WHERE pkBrokerID = ?";
	$stmt = mysqli_prepare($_SESSION['db'], $query);
	mysqli_stmt_bind_param($stmt, "i", $userID);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	if ($result && $userData = mysqli_fetch_assoc($result)) {
		$isFirstLogin = $userData['fldFirstLogin'] == 1;
	}
} catch (Exception $e) {
	// Column doesn't exist yet, treat as not first login
	$isFirstLogin = false;
}

// Handle password change form submission
if ($_POST['action'] == 'changePassword') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate current password
    $checkQuery = "SELECT fldPassword FROM tblB2TBroker WHERE pkBrokerID = ?";
    $checkStmt = mysqli_prepare($_SESSION['db'], $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "i", $userID);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $storedPassword = mysqli_fetch_row($checkResult)[0];
    
    if (md5($currentPassword) !== $storedPassword) {
        $errorMessage = "Current password is incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New passwords do not match.";
    } elseif (strlen($newPassword) < 8) {
        $errorMessage = "New password must be at least 8 characters long.";
    } else {
        // Update password and mark as not first login
        // Try to update fldFirstLogin if the column exists, otherwise just update password
        try {
            $updateQuery = "UPDATE tblB2TBroker SET fldPassword = ?, fldFirstLogin = 0 WHERE pkBrokerID = ?";
            $updateStmt = mysqli_prepare($_SESSION['db'], $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", md5($newPassword), $userID);
        } catch (Exception $e) {
            // Column doesn't exist, just update password
            $updateQuery = "UPDATE tblB2TBroker SET fldPassword = ? WHERE pkBrokerID = ?";
            $updateStmt = mysqli_prepare($_SESSION['db'], $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", md5($newPassword), $userID);
        }
        
        if (mysqli_stmt_execute($updateStmt)) {
            $successMessage = "Password changed successfully!";
            $isFirstLogin = false;
        } else {
            $errorMessage = "Error updating password. Please try again.";
        }
    }
}

// If not first login and no password change requested, redirect to main page
if (!$isFirstLogin && !isset($_POST['action'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - Greenlight Commodities</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <style>
        .password-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .button {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .button:hover {
            background: #45a049;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="password-form">
        <h2>Change Password</h2>
        
        <?php if ($isFirstLogin): ?>
            <div class="warning">
                <strong>Welcome!</strong> This is your first login. Please change your temporary password to a secure one.
            </div>
        <?php endif; ?>
        
        <?php if (isset($successMessage)): ?>
            <div class="message success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
            <p>You will be redirected to the main page in a few seconds...</p>
            <script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 3000);
            </script>
        <?php else: ?>
            <?php if (isset($errorMessage)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="changePassword">
                
                <div class="form-group">
                    <label for="currentPassword">Current Password:</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password:</label>
                    <input type="password" id="newPassword" name="newPassword" required 
                           pattern=".{8,}" title="Password must be at least 8 characters long">
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                
                <button type="submit" class="button">Change Password</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: #666;">
                <small>Password must be at least 8 characters long</small>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
