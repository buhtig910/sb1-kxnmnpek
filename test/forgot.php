<h1>Reset Your Password</h1>

<?php

if($_POST["action"] == "request") {
	
	require("util/userObj.php");
	require("util/emailObj.php");
	
	$user = new User();
	$result = $user->requestResetEmail($_POST["username"]);
	
	if($result == 1) {
		$email = new Email();
		$email->setMessageType("HTML");
		$email->setTo($user->getEmail());
		$email->setFrom("info@greenlightcommodities.com");
		$email->setSubject("Greenlight Commodities Password Reset");
		
		$validationLink = $_SESSION["baseURL"]."index.php?p=reset&a=".$user->getKey();
		
		$message = "<html><body>";
		$message.= "<p>Follow the link below to reset your password:</p>";
		$message.= "<p><a href=\"$validationLink\">$validationLink</a></p>";
		
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

<script language="javascript">

function checkForm() {
	if( $("#username").val() == "") {
		alert("You must enter a username before continuing.");
		return false;
	}
	
	return true;
}

</script>

<form name="form1" method="post" action="index.php?p=forgot" onsubmit="return checkForm()">
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td class="fieldLabel">Username:</td>
    <td>
      <input type="text" name="username" id="username">
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