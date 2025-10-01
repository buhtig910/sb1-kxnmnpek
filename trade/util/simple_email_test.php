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

if($_POST["action"] == "testSimpleEmail") {
    $testEmail = $_POST["testEmail"];
    
    if(empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        // Use the EXACT same structure as the working brokers email
        $subject = "Simple Test Email - Greenlight Commodities";
        $messageBody = "
        <html>
        <body>
            <h2>Simple Test Email</h2>
            <p>This is a simple test email to verify the basic email system is working.</p>
            <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p><strong>System:</strong> Greenlight Commodities Email System</p>
            <hr>
            <p><em>If you received this email, the basic system is working.</em></p>
        </body>
        </html>";
        
        $headers = "From: confirms@greenlightcommodities.com <confirms@greenlightcommodities.com>\r\n";
        $headers .= "Reply-To: confirms@greenlightcommodities.com\r\n";
        $headers .= "Return-Path: confirms@greenlightcommodities.com\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        $sendMailResult = mail($testEmail, $subject, $messageBody, $headers);
        
        if($sendMailResult) {
            $message = "✅ Simple test email sent successfully to $testEmail! Check your inbox.";
            $messageType = "success";
        } else {
            $message = "❌ Simple test email failed to send.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Email Test - Greenlight Commodities</title>
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
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Simple Email Test</h1>
        
        <div class="test-section">
            <h3>Test Basic Email Function</h3>
            <p>This tests the exact same email structure used in trade confirmations.</p>
            
            <form method="post" class="test-form">
                <input type="hidden" name="action" value="testSimpleEmail">
                
                <div class="form-group">
                    <label for="testEmail">Test Email Address:</label>
                    <input type="email" id="testEmail" name="testEmail" required placeholder="Enter your email address">
                </div>
                
                <button type="submit" class="submit-btn">Send Simple Test Email</button>
            </form>
        </div>
        
        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="test-section">
            <h3>What This Tests</h3>
            <ul>
                <li><strong>Basic PHP mail() function</strong> - Same as trade confirmations</li>
                <li><strong>Email headers</strong> - Exact same structure as working brokers email</li>
                <li><strong>HTML email formatting</strong> - Same as trade confirmations</li>
                <li><strong>No attachments</strong> - Just like this test</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h3>Next Steps</h3>
            <ol>
                <li><strong>Send this test email</strong> to your email address</li>
                <li><strong>Check your inbox</strong> (and spam folder)</li>
                <li><strong>If it works</strong> - The issue is with trade confirmation logic</li>
                <li><strong>If it fails</strong> - The issue is with the email system itself</li>
            </ol>
        </div>
    </div>
</body>
</html>
