<h1>Reset Your Password</h1>

<?php

if($_POST["action"] == "request") {
	
	// Server-side validation
	if(empty($_POST["username"]) || trim($_POST["username"]) == "") {
		echo("<p class=\"errorMessage\">You must enter a username before continuing.</p>");
		echo("<p><a href=\"index.php?p=forgot\">Return to Password Reset</a></p>");
		exit;
	}
	
	require("util/userObj.php");
	require("util/emailObj.php");
	
	$user = new User();
	$result = $user->requestResetEmail($_POST["username"]);
	
	if($result == 1) {
		$email = new Email();
		$email->setMessageType("HTML");
		$email->setTo($user->getEmail());
		$email->setFrom("jake@greenlightcommodities.com");
		$email->setSubject("Greenlight Commodities - Password Reset Request");
		
		// Add better headers to avoid spam filters
		$headers = "X-Mailer: Greenlight Commodities System\r\n";
		$headers .= "X-Priority: 3\r\n";
		$headers .= "X-MSMail-Priority: Normal\r\n";
		$email->setHeaders($headers);
		
		$validationLink = $_SESSION["baseURL"]."index.php?p=reset&a=".$user->getKey();
		
		$message = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
		$message.= "<div style='max-width: 600px; margin: 0 auto; padding: 20px;'>";
		$message.= "<h2 style='color: #2c5530;'>Greenlight Commodities - Password Reset</h2>";
		$message.= "<p>Hello,</p>";
		$message.= "<p>We received a request to reset the password for your Greenlight Commodities account.</p>";
		$message.= "<p>To reset your password, please click the link below:</p>";
		$message.= "<p style='text-align: center; margin: 30px 0;'>";
		$message.= "<a href=\"$validationLink\" style='background-color: #2c5530; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;'>Reset Password</a>";
		$message.= "</p>";
		$message.= "<p><strong>Important:</strong> This link will expire in 15 minutes for security reasons.</p>";
		$message.= "<p>If you did not request this password reset, please ignore this email. Your password will remain unchanged.</p>";
		$message.= "<hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>";
		$message.= "<p style='font-size: 12px; color: #666;'>This email was sent from Greenlight Commodities trading system.</p>";
		$message.= "</div></body></html>";
		
		$email->setBody($message);
		$email->sendMessage();
		
		echo("<p class=\"confirmMessage\">Your password reset request was successful</p>");
		echo("<p>An email with instructions to reset your password has been sent to <strong>".$user->getEmail()."</strong></p>");
	}
	else {
		echo("<p class=\"errorMessage\">The username you entered did not match any records in the database.</p>");
		echo("<p><a href=\"index.php?p=forgot\">Return to Password Reset</a></p>");	
	}
		
}
else {
?>

<form name="form1" method="post" action="index.php?p=forgot">
<input type="hidden" name="action" value="request">
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td class="fieldLabel">Username:</td>
    <td>
      <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="button" id="button" class="button" value="Submit" />
    	<input type="hidden" name="action" value="request" />
    </td>
  </tr>
</table>
</form>

<?php
}
?>