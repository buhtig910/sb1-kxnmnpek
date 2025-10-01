<h1>Reset Your Password</h1>

<?php

if(isset($_POST["action"]) && $_POST["action"] == "reset") {
	
	require("util/userObj.php");
	
	$user = new User();
	$keyValid = $user->verifyKey($_POST["a"]);
	
	if( $keyValid ) {
		if($_POST["newPassword"] == $_POST["newPassword2"]) {
			$success = $user->updatePassword($_POST["newPassword"], $_POST["a"]);
		}
		
		if($success) {
			echo("<p class=\"confirmMessage\">Your password has been reset successfully</p>\n");
			echo("<p><a href=\"index.php\">Login again</a></p>\n");
		}
		else
			echo("<p class=\"errorMessage\">Unable to reset your password. Please try again.</p>\n");
	}
	else {
		echo("<p class=\"errorMessage\">This password reset link has expired or is invalid.</p>\n");
		echo("<p>Password reset links expire after 15 minutes for security reasons.</p>\n");
		echo("<p><a href=\"index.php?p=forgot\">Request a new password reset</a></p>\n");
	}
	
}
else if(isset($_GET["a"])) {
?>

<script language="javascript">

function checkForm() {
	var newPassword = document.getElementById("newPassword").value;
	var newPassword2 = document.getElementById("newPassword2").value;
	
	if(newPassword == "") {
		alert("You must enter a new password");
		document.getElementById("newPassword").focus();
		return false;
	}
	else if(newPassword2 == "") {
		alert("You must re-enter your password");
		document.getElementById("newPassword2").focus();
		return false;
	}
	else if(newPassword != newPassword2) {
		alert("The passwords you entered do not match. Please re-enter them.");
		return false;
	}
	
	return true;
}

</script>

<form name="form1" method="post" action="index.php?p=reset" onsubmit="return checkForm()">
<input type="hidden" name="action" value="reset">
<input type="hidden" name="a" value="<?php echo($_GET["a"]); ?>">
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td class="fieldLabel">New Password:</td>
    <td>
      <input type="password" name="newPassword" id="newPassword" required>
    </td>
  </tr>
  <tr>
    <td class="fieldLabel">Confirm New Password:</td>
    <td>
      <input type="password" name="newPassword2" id="newPassword2" required>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="button" id="button" class="button" value="Reset Password" />
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