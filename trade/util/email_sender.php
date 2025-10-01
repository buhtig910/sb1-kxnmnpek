<?php
/**
 * Email Sending System for Account Creation
 * Uses PHP's built-in mail() function for reliable email delivery
 */

class EmailSender {
    private $fromEmail = "jake+setuppw@greenlightcommodities.com";
    private $fromName = "Greenlight Commodities";
    
    public function __construct() {
        // No external dependencies needed
    }
    
    /**
     * Send account creation email
     */
    public function sendAccountCreationEmail($toEmail, $toName, $username, $setupLink) {
        try {
            $subject = "Welcome to Greenlight Commodities! - Create Your Account";
            $message = $this->getAccountCreationTemplate($toName, $setupLink);
            $headers = $this->buildHeaders();
            
            $sendMailResult = mail($toEmail, $subject, $message, $headers);
            
            if($sendMailResult) {
                return array('success' => true, 'message' => 'Email sent successfully');
            } else {
                return array('success' => false, 'message' => 'Email could not be sent');
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Email error: ' . $e->getMessage());
        }
    }
    
    /**
     * Build email headers
     */
    private function buildHeaders() {
        $headers = "";
        $headers .= "From: " . $this->fromName . " <" . $this->fromEmail . ">\r\n";
        $headers .= "Reply-To: " . $this->fromEmail . "\r\n";
        $headers .= "Return-Path: " . $this->fromEmail . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        return $headers;
    }
    
    /**
     * Get HTML email template
     */
    private function getAccountCreationTemplate($brokerName, $setupLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to Greenlight Commodities</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4CAF50; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Greenlight Commodities!</h1>
                </div>
                
                <div class='content'>
                    <h2>Hello $brokerName!</h2>
                    
                    <p>🎉 <strong>Congratulations & Welcome aboard!</strong></p>
                    
                    <p>Your account has been created successfully by your administrator. To complete your setup and start trading, please click the button below to create your account credentials:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$setupLink' class='button'>Create Your Account</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Important Security Notice:</strong><br>
                        This setup link will expire in <strong>24 hours</strong> for security reasons.<br>
                        If you don't complete your setup within this time, please contact your administrator.
                    </div>
                    
                    <p><strong>What happens next?</strong></p>
                    <ol>
                        <li>Click the button above to access your account setup</li>
                        <li>Create a strong password for your account</li>
                        <li>Set up any additional security preferences</li>
                        <li>Start trading with Greenlight Commodities!</li>
                    </ol>
                    
                    <div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <strong>🔗 Employee Login:</strong><br>
                        <a href='https://trade.greenlightcommodities.com/index.php?p=employee' style='color: #4CAF50; text-decoration: none; font-weight: bold;'>trade.greenlightcommodities.com/index.php?p=employee</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                    
                    <p>Best regards,<br>
                    <strong>The Greenlight Commodities Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>© " . date('Y') . " Greenlight Commodities. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Send welcome email for new brokers with username and one-time login link
     */
    public function sendWelcomeEmail($toEmail, $toName, $username, $oneTimeLoginLink) {
        try {
            $subject = "Welcome to Greenlight Commodities!";
            $message = $this->getWelcomeTemplate($toName, $username, $oneTimeLoginLink);
            $headers = $this->buildHeaders();
            
            $sendMailResult = mail($toEmail, $subject, $message, $headers);
            
            if($sendMailResult) {
                return array('success' => true, 'message' => 'Welcome email sent successfully');
            } else {
                return array('success' => false, 'message' => 'Welcome email could not be sent');
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Welcome email error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get welcome email template
     */
    private function getWelcomeTemplate($brokerName, $username, $oneTimeLoginLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to Greenlight Commodities</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4CAF50; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Greenlight Commodities!</h1>
                </div>
                
                <div class='content'>
                    <h2>Hello $brokerName!</h2>
                    
                    <p>🎉 <strong>Congratulations & Welcome aboard!</strong></p>
                    
                    <p>Your account has been created successfully by your administrator.</p>
                    
                    <div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <strong>🔗 Employee Login:</strong><br>
                        <a href='https://trade.greenlightcommodities.com/index.php?p=employee' style='color: #4CAF50; text-decoration: none; font-weight: bold;'>trade.greenlightcommodities.com/index.php?p=employee</a>
                    </div>
                    
                                         <div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                         <strong>Account Information:</strong><br>
                         Username: <strong>$username</strong><br>
                         <strong>🔐 One-Time Login Link:</strong><br>
                         <a href='$oneTimeLoginLink' style='color: #4CAF50; text-decoration: none; font-weight: bold;'>Click here to set your password</a>
                     </div>
                     
                     <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                         <strong>⚠️ Important Security Notice:</strong><br>
                         This login link will expire in <strong>24 hours</strong> for security reasons.<br>
                         Click the link above to set your password and complete your account setup.
                     </div>
                     
                     <p><strong>What happens next?</strong></p>
                     <ol>
                         <li>Click the login link above to access your account</li>
                         <li>Set a strong password for your account</li>
                         <li>Start trading with Greenlight Commodities!</li>
                     </ol>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                    
                    <p>Best regards,<br>
                    <strong>The Greenlight Commodities Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>© " . date('Y') . " Greenlight Commodities. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Test email configuration
     */
    public function testEmailConfiguration($testEmail) {
        try {
            $subject = "Test Email - Greenlight Commodities Email System";
            $message = "
            <html>
            <body>
                <h2>Test Email</h2>
                <p>This is a test email to verify the email system is working correctly.</p>
                <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>System:</strong> Greenlight Commodities Email System</p>
                <hr>
                <p><em>If you received this email, the system is working properly.</em></p>
            </body>
            </html>";
            
            $headers = $this->buildHeaders();
            
            $sendMailResult = mail($testEmail, $subject, $message, $headers);
            
            if($sendMailResult) {
                return array('success' => true, 'message' => 'Test email sent successfully to ' . $testEmail);
            } else {
                return array('success' => false, 'message' => 'Test email could not be sent');
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Test email error: ' . $e->getMessage());
        }
    }
}
?>
