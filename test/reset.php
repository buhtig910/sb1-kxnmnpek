<h1>Reset Your Password</h1>

<?php

if(isset($_POST["action"])) {
	
	require("util/userObj.php");
	
	$user = new User();
	if( $user->verifyKey($_POST["a"]) ) {
		if($_POST["newPassword"] == $_POST["newPassword2"]) {
			$success = $user->updatePassword($_POST["newPassword"]);
		}
		
		if($success) {
			echo("<p class=\"confirmMessage\">Your password has been reset successfully</p>\n");
			
			if(isset($_SESSION["brokerID42"]))
				echo("<p><a href=\"index.php?p=employee\">Login again</a></p>\n");
		}
		else
			echo("<p class=\"errorMessage\">Unable to reset your password</p>\n");
	}
	else {
		echo("<p class=\"errorMessage\">Unable to reset your password</p>\n");
	}
	
}
else if(isset($_GET["a"])) {
?>

<script language="javascript">

function checkForm() {
	if( $("#newPassword").val() == "") {
		alert("You must enter a new password");
		$("#newPassword").focus();
		return false;
	}
	else if( $("#newPassword2").val() == "") {
		alert("You must re-enter your password");
		$("#newPassword2").focus();
		return false;
	}
	else if( $("#newPassword").val() != $("#newPassword2").val()) {
		alert("The passwords you entered do not match. Please re-enter them.");
		return false;
	}
	
	return true;
}

</script>

<form name="form1" method="post" action="index.php?p=reset" onsubmit="return checkForm()">
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td class="fieldLabel">New Password:</td>
    <td>
      <input type="password" name="newPassword" id="newPassword">
    </td>
  </tr>
  <tr>
    <td class="fieldLabel">Confirm New Password:</td>
    <td>
      <input type="password" name="newPassword2" id="newPassword2">
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="button" id="button" class="button" value="Reset Password" />
    	<input type="hidden" name="action" value="request" />
        <input type="hidden" name="a" value="<?php echo($_GET["a"]); ?>" />
    </td>
  </tr>
</table>
</form>

<?php
}
else {
	echo("<p>You can not access this page directly, please follow the link that was provided via email.</p>");
}
?>