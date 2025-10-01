<?php
session_start();
require_once("../vars.inc.php");
require_once("../functions.php");

// Include user activation logging
require_once("user_activation_logging.php");

// Validate setup token
$token = $_GET["token"] ?? "";
$brokerID = $_GET["id"] ?? "";

if(empty($token) || empty($brokerID)) {
    // Log invalid token attempt
    logInvalidTokenAttempt($token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Missing token or broker ID');
    die("Invalid setup link. Please contact your administrator.");
}

// Check if token is valid and not expired
$query = "SELECT pkBrokerID, fldName, fldEmail, fldUsername, fldSetupToken, fldSetupExpiry, fldAllowUserToSetUsername 
          FROM $tblBroker 
          WHERE pkBrokerID = ? AND fldSetupToken = ? AND fldSetupExpiry > NOW()";
$stmt = mysqli_prepare($_SESSION['db'], $query);
mysqli_stmt_bind_param($stmt, "ss", $brokerID, $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) != 1) {
    // Log invalid/expired token attempt
    logInvalidTokenAttempt($token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Token invalid or expired');
    die("Setup link is invalid or has expired. Please contact your administrator.");
}

$broker = mysqli_fetch_assoc($result);

// Log successful token validation
logPasswordSetupAttempt($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

// Handle account creation submission
if($_POST["action"] == "createAccount") {
    $newUsername = $_POST["username"] ?? "";
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    
    // Validate username if user is setting it
    if($broker['fldAllowUserToSetUsername'] == 1) {
        if(empty($newUsername) || strlen($newUsername) < 3) {
            $error = "Username must be at least 3 characters long.";
            logPasswordSetupFailure($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Username too short');
        } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
            $error = "Username can only contain letters, numbers, and underscores.";
            logPasswordSetupFailure($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Invalid username format');
        } else {
            // Check if username is already taken
            $checkQuery = "SELECT pkBrokerID FROM $tblBroker WHERE fldUsername = ? AND pkBrokerID != ?";
            $checkStmt = mysqli_prepare($_SESSION['db'], $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "si", $newUsername, $brokerID);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if(mysqli_num_rows($checkResult) > 0) {
                $error = "Username is already taken. Please choose a different one.";
                logPasswordSetupFailure($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Username already taken');
            }
        }
    }
    
    // Validate password
    if($password !== $confirmPassword) {
        $error = "Passwords do not match.";
        logPasswordSetupFailure($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Passwords do not match');
    } elseif(strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
        logPasswordSetupFailure($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Password too short');
    }
    
    // If no errors, proceed with account creation
    if(!isset($error)) {
        $hashedPassword = md5($password);
        $finalUsername = $broker['fldAllowUserToSetUsername'] == 1 ? $newUsername : $broker['fldUsername'];
        
        $updateQuery = "UPDATE $tblBroker 
                       SET fldUsername = ?, fldPassword = ?, fldSetupToken = '', fldSetupExpiry = NULL, fldAllowUserToSetUsername = 0 
                       WHERE pkBrokerID = ?";
        $updateStmt = mysqli_prepare($_SESSION['db'], $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "ssi", $finalUsername, $hashedPassword, $brokerID);
        
        if(mysqli_stmt_execute($updateStmt)) {
            // Log successful account creation
            logPasswordSetupSuccess($brokerID, $broker['fldName'], $finalUsername, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            $success = "Account created successfully! You can now log in.";
        } else {
            $error = "An error occurred. Please try again.";
            logPasswordSetupFailure($brokerID, $broker['fldName'], $broker['fldUsername'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], 'Database update failed');
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account - Greenlight Commodities</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <style>
        .setup-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #45a049;
        }
        .error {
            color: #f44336;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            color: #4CAF50;
            background: #e8f5e8;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>Welcome to Greenlight Commodities</h1>
            <h2>Create Your Account</h2>
            <p>Hello <strong><?php echo htmlspecialchars($broker['fldName']); ?></strong></p>
            <p>Please complete your account setup by setting your username and password.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?>
                <br><br>
                <a href="../index.php" class="submit-btn" style="text-decoration: none; text-align: center; display: inline-block;">Go to Login</a>
            </div>
        <?php else: ?>
            <form class="setup-form" method="post">
                <input type="hidden" name="action" value="createAccount">
                
                <?php if($broker['fldAllowUserToSetUsername'] == 1): ?>
                <div class="form-group">
                    <label for="username">Choose Username:</label>
                    <input type="text" id="username" name="username" required minlength="3" maxlength="20" 
                           placeholder="Enter your desired username" 
                           pattern="[a-zA-Z0-9_]+" 
                           title="Username can only contain letters, numbers, and underscores">
                    <small style="color: #666; font-size: 12px;">Username must be 3-20 characters, letters/numbers/underscores only</small>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($broker['fldUsername']); ?>" readonly>
                    <small style="color: #666; font-size: 12px;">Username has been set by administrator</small>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small style="color: #666; font-size: 12px;">Password must be at least 8 characters long</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8">
                </div>
                
                <button type="submit" class="submit-btn">Create Account</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
