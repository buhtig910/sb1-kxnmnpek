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

if($_POST["action"] == "testEmail") {
    $testEmail = $_POST["testEmail"];
    
    if(empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        require_once("email_sender.php");
        $emailSender = new EmailSender();
        $result = $emailSender->testEmailConfiguration($testEmail);
        
        if($result['success']) {
            $message = "✅ Test email sent successfully to $testEmail! Check your inbox.";
            $messageType = "success";
        } else {
            $message = "❌ Test email failed: " . $result['message'];
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Test - Greenlight Commodities</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-form {
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
            padding: 12px 24px;
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
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Email Configuration Test</h1>
        <p>Use this page to test your email configuration and troubleshoot any issues.</p>
        
        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="test-section">
            <h2>Test Email Configuration</h2>
            <p>Send a test email to verify your email system is working correctly.</p>
            
            <form class="test-form" method="post">
                <input type="hidden" name="action" value="testEmail">
                
                <div class="form-group">
                    <label for="testEmail">Test Email Address:</label>
                    <input type="email" id="testEmail" name="testEmail" required 
                           placeholder="Enter email address to send test to">
                </div>
                
                <button type="submit" class="submit-btn">Send Test Email</button>
            </form>
        </div>
        
        <div class="test-section">
            <h2>🔧 Email Configuration Details</h2>
            <div class="info-box">
                <strong>Current Settings:</strong>
                <ul>
                    <li><strong>Email Method:</strong> PHP built-in mail() function</li>
                    <li><strong>From Email:</strong> jake+setuppw@greenlightcommodities.com</li>
                    <li><strong>From Name:</strong> Greenlight Commodities</li>
                    <li><strong>Server Configuration:</strong> Uses server's default mail settings</li>
                </ul>
            </div>
        </div>
        
        <div class="test-section">
            <h2>Common Email Issues & Solutions</h2>
            
            <h3>1. "Email could not be sent"</h3>
            <p><strong>Problem:</strong> Server's mail configuration issue</p>
            <p><strong>Solutions:</strong></p>
            <ul>
                <li>Check if your hosting provider allows email sending</li>
                <li>Verify server's mail configuration (sendmail, postfix, etc.)</li>
                <li>Contact your hosting provider for mail setup assistance</li>
            </ul>
            
            <h3>2. "From address rejected"</h3>
            <p><strong>Problem:</strong> Server doesn't allow custom from addresses</p>
            <p><strong>Solution:</strong> Use a verified email address from your domain</p>
            
            <h3>3. "Email sent but not received"</h3>
            <p><strong>Problem:</strong> Email might be in spam folder</p>
            <p><strong>Solutions:</strong></p>
            <ul>
                <li>Check spam/junk folder</li>
                <li>Add sender to contacts</li>
                <li>Check email server logs</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>Next Steps</h2>
            <ol>
                <li><strong>Test the email system</strong> using the form above</li>
                <li><strong>Check your email inbox</strong> (and spam folder) for the test email</li>
                <li><strong>If emails work:</strong> Try creating a new broker to test the full flow</li>
                <li><strong>If emails fail:</strong> Check the error message and contact your hosting provider</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="../index.php" class="submit-btn" style="text-decoration: none;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
