<?php
session_start();
require_once("../vars.inc.php");
require_once("../functions.php");

// Check if user is admin or super admin
if(!isset($_SESSION["userType"]) || ($_SESSION["userType"] != "1" && $_SESSION["userType"] != "2")) {
    die("Access denied. Administrators and Super Administrators only.");
}

$message = "";
$messageType = "";
$diagnosticResults = array();

if($_POST["action"] == "diagnoseEmail") {
    // Test 1: Check if PHPMailer class exists (after requiring files)
    try {
        require_once "Exception.php";
        require_once "PHPMailer.php";
        require_once "SMTP.php";
        
        if(class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $diagnosticResults[] = "✅ PHPMailer class is available";
        } else {
            $diagnosticResults[] = "❌ PHPMailer class is NOT available";
        }
        
        // Test 2: Check if we can create a PHPMailer instance
        $mail = new PHPMailer\PHPMailer\PHPMailer;
        $diagnosticResults[] = "✅ PHPMailer instance created successfully";
        
        // Test 3: Test SMTP connection
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jake@greenlightcommodities.com';
        $mail->Password = 'ykkk ykex jftk ortb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Test 4: Try to connect to Gmail SMTP
        if($mail->smtpConnect()) {
            $diagnosticResults[] = "✅ Gmail SMTP connection successful";
            $mail->smtpClose();
        } else {
            $diagnosticResults[] = "❌ Gmail SMTP connection failed: " . $mail->ErrorInfo;
        }
        
    } catch (Exception $e) {
        $diagnosticResults[] = "❌ PHPMailer error: " . $e->getMessage();
    }
    
    // Test 5: Check if logging system works
    try {
        require_once("loggingObj.php");
        $logger = new Logger();
        $testLogResult = $logger->logAction("Email Diagnostic Test", "Testing logging system", 0);
        if($testLogResult) {
            $diagnosticResults[] = "✅ Logging system is working";
        } else {
            $diagnosticResults[] = "❌ Logging system failed";
        }
    } catch (Exception $e) {
        $diagnosticResults[] = "❌ Logging system error: " . $e->getMessage();
    }
    
    // Test 6: Check database connection
    if(isset($_SESSION['db']) && $_SESSION['db']) {
        $diagnosticResults[] = "✅ Database connection is active";
    } else {
        $diagnosticResults[] = "❌ Database connection is not available";
    }
    
    // Test 7: Check session variables
    $diagnosticResults[] = "Session PROD: " . (isset($_SESSION["PROD"]) ? ($_SESSION["PROD"] ? "true" : "false") : "not set");
    $diagnosticResults[] = "Session testScript: " . (isset($_SESSION["testScript"]) ? $_SESSION["testScript"] : "not set");
    
    $message = "Email system diagnostic completed. Check results below.";
    $messageType = "info";
}

// Test 8: Check recent email logs
$recentLogs = array();
if(isset($_SESSION['db']) && $_SESSION['db']) {
    $query = "SELECT fldAction, fldDetails, fldTimestamp FROM tblB2TLogging 
              WHERE fldAction LIKE '%Email%' OR fldAction LIKE '%Confirm%'
              ORDER BY fldTimestamp DESC LIMIT 10";
    $result = mysqli_query($_SESSION['db'], $query);
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $recentLogs[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email System Diagnostic - Greenlight Commodities</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <style>
        .diagnostic-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .diagnostic-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .diagnostic-form {
            margin-top: 20px;
        }
        .submit-btn {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .result-item {
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
            font-family: monospace;
        }
        .result-item.success {
            background: #d4edda;
            color: #155724;
        }
        .result-item.error {
            background: #f8d7da;
            color: #721c24;
        }
        .log-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .log-table th, .log-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .log-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="diagnostic-container">
        <h1>Email System Diagnostic</h1>
        
        <div class="diagnostic-section">
            <h3>Run Email System Diagnostic</h3>
            <p>This will test various components of the email system to identify what's broken.</p>
            
            <form method="post" class="diagnostic-form">
                <input type="hidden" name="action" value="diagnoseEmail">
                <button type="submit" class="submit-btn">Run Diagnostic Tests</button>
            </form>
        </div>
        
        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($diagnosticResults)): ?>
            <div class="diagnostic-section">
                <h3>Diagnostic Results</h3>
                <?php foreach($diagnosticResults as $result): ?>
                    <div class="result-item <?php echo strpos($result, '✅') !== false ? 'success' : (strpos($result, '❌') !== false ? 'error' : ''); ?>">
                        <?php echo htmlspecialchars($result); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="diagnostic-section">
            <h3>Recent Email-Related Logs</h3>
            <?php if(!empty($recentLogs)): ?>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentLogs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['fldAction']); ?></td>
                                <td><?php echo htmlspecialchars($log['fldDetails']); ?></td>
                                <td><?php echo htmlspecialchars($log['fldTimestamp']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent email-related logs found.</p>
            <?php endif; ?>
        </div>
        
        <div class="diagnostic-section">
            <h3>Common Email Issues & Solutions</h3>
            <ul>
                <li><strong>Gmail App Password Expired:</strong> Generate a new app password in Gmail settings</li>
                <li><strong>Gmail Daily Limit:</strong> Gmail has a 500 emails/day limit for regular accounts</li>
                <li><strong>Network Blocking:</strong> Server firewall might block outbound SMTP connections</li>
                <li><strong>PHPMailer Missing:</strong> class.phpmailer.php file might be missing or corrupted</li>
                <li><strong>Database Issues:</strong> Logging system might not be working</li>
            </ul>
        </div>
    </div>
</body>
</html>
