<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Debug session information
if(isset($_GET["brokerID"])) {
	echo "<!-- DEBUG: Session Status -->";
	echo "<!-- Session ID: " . session_id() . " -->";
	echo "<!-- Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . " -->";
	echo "<!-- All Session Data: " . print_r($_SESSION, true) . " -->";
}

require_once("../functions.php");
require_once("../vars.inc.php");

		// Debug Super Admin detection
if(isset($_GET["brokerID"])) {
	echo "<!-- DEBUG: Super Admin Detection -->";
	echo "<!-- Session userType: " . (isset($_SESSION["userType"]) ? $_SESSION["userType"] : "Not set") . " -->";
	echo "<!-- GET superAdmin: " . (isset($_GET["superAdmin"]) ? $_GET["superAdmin"] : "Not set") . " -->";
	echo "<!-- isSuperAdmin() result: " . (isSuperAdmin() ? "Yes" : "No") . " -->";
	echo "<!-- Session username: " . (isset($_SESSION["username"]) ? $_SESSION["username"] : "Not set") . " -->";
	echo "<!-- Final isSuperAdmin check: " . (isSuperAdmin() || (isset($_GET["superAdmin"]) && $_GET["superAdmin"] == "1") ? "Yes" : "No") . " -->";
	echo "<!-- GET action: " . (isset($_GET["action"]) ? $_GET["action"] : "Not set") . " -->";
	echo "<!-- Action == update: " . ($_GET["action"] == "update" ? "Yes" : "No") . " -->";
}

require_once("../util/popupHeader.php");


	/**
	 * Generate a random 10-character temporary password
	 * Contains: a-z, A-Z, 0-9, and exactly one special character from !@#$%^&*
	 * 
	 * REVERT INSTRUCTIONS: To switch back to temporary password system:
	 * 1. Change the INSERT query to use $tempPassword instead of empty password
	 * 2. Change email sending to use $tempPassword instead of $oneTimeLoginLink
	 * 3. Update email template to show password instead of login link
	 */
	function generateTempPassword() {
		$lowercase = 'abcdefghijklmnopqrstuvwxyz';
		$uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$numbers = '0123456789';
		$special = '!@#$%^&*';
		
		// Ensure we have at least one of each type
		$password = '';
		$password .= $lowercase[rand(0, strlen($lowercase) - 1)]; // 1 lowercase
		$password .= $uppercase[rand(0, strlen($uppercase) - 1)]; // 1 uppercase
		$password .= $numbers[rand(0, strlen($numbers) - 1)]; // 1 number
		$password .= $special[rand(0, strlen($special) - 1)]; // 1 special character
		
		// Fill remaining 6 characters randomly from all character sets
		$allChars = $lowercase . $uppercase . $numbers . $special;
		for ($i = 0; $i < 6; $i++) {
			$password .= $allChars[rand(0, strlen($allChars) - 1)];
		}
		
		// Shuffle the password to make it more random
		return str_shuffle($password);
	}
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">

	<h2>Broker Information</h2>
	
	<?php
	
	$action = $_POST["action"];
	$brokerID = $_POST["brokerID"];
	$brokerName = $_POST["brokerName"];
	$username = $_POST["username"];
	$password = $_POST["password"];
	$email = $_POST["email"];
	$userType = $_POST["userType"];
	$active = isset($_POST["active"]) ? $_POST["active"] : "";
	$sendSetupEmail = isset($_POST["sendSetupEmail"]) ? true : false;
	$manualPassword = isset($_POST["manualPassword"]) ? $_POST["manualPassword"] : "";
	
	// Handle password reset email request
	if(isset($_POST["action"]) && $_POST["action"] == "sendPasswordReset") {
		$brokerID = $_POST["brokerID"];
		
		// Get broker details
		$brokerQuery = "SELECT fldUsername, fldEmail, fldName FROM $tblBroker WHERE pkBrokerID = $brokerID";
		$brokerResult = mysqli_query($_SESSION['db'], $brokerQuery);
		$brokerData = mysqli_fetch_row($brokerResult);
		
		if($brokerData) {
			$username = $brokerData[0];
			$brokerEmail = $brokerData[1];
			$brokerName = $brokerData[2];
			
			// Use the existing password reset system
			require_once("../util/userObj.php");
			$user = new User();
			$result = $user->requestResetEmail($username);
			
			if($result == 1) {
			// Send the email
			require_once("../util/emailObj.php");
			$email = new Email();
			$email->setMessageType("HTML");
			$email->setTo($brokerEmail);
			$email->setFrom("jake@greenlightcommodities.com");
			$email->setSubject("Greenlight Commodities Password Reset");
			
			$resetLink = $_SESSION["baseURL"]."index.php?p=reset&a=".$user->getKey();
			
			$message = "<html><body>";
			$message .= "<p>Hello $brokerName,</p>";
			$message .= "<p>A password reset has been requested for your account.</p>";
			$message .= "<p>Click the link below to reset your password:</p>";
			$message .= "<p><a href=\"$resetLink\">$resetLink</a></p>";
			$message .= "<p>This link will expire in 15 minutes for security reasons.</p>";
			$message .= "<p>If you did not request this reset, please ignore this email.</p>";
			$message .= "</body></html>";
			
			$email->setBody($message);
			$email->sendMessage();
				
				echo("<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Password reset email sent successfully to $brokerEmail</p></div>");
			} else {
				echo("<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Failed to send password reset email</p></div>");
			}
		} else {
			echo("<div class=\"clear\" style=\"height: 40px;\"><p class=\"errorMessage\">Broker not found</p></div>");
		}
		
		// Don't continue with normal form processing
		$step = "confirmUpdate";
	}
	
	if($action == "addNew"){
		//add new client
		
		// Check if admin wants to set password manually or use email setup
		$useManualPassword = !empty($manualPassword);
		$useManualUsername = isset($_POST["usernameChoice"]) && $_POST["usernameChoice"] == "adminSet";
		
		// Smart status logic: Active if manual setup, Inactive if email setup (until user completes setup)
		$finalActive = ($useManualPassword && $useManualUsername) ? "1" : "0";
		
		if($useManualPassword) {
			// Use manual password (hash it)
			$finalPassword = md5($manualPassword);
			$setupToken = "";
			$setupExpiry = "";
			$allowUserToSetUsername = false;
		} else {
			// Generate unique setup token for password setup
			$setupToken = bin2hex(random_bytes(32));
			$setupExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
			$finalPassword = "";
			$allowUserToSetUsername = isset($_POST["allowUserToSetUsername"]) ? true : false;
		}
		
		// Generate username: first letter of first name + last name
		$nameParts = explode(' ', $brokerName);
		if(count($nameParts) >= 2) {
			$firstName = $nameParts[0];
			$lastName = $nameParts[count($nameParts) - 1]; // Handle multiple middle names
			$finalUsername = strtolower(substr($firstName, 0, 1) . $lastName);
		} else {
			// Fallback if only one name
			$finalUsername = strtolower($brokerName);
		}
		
		// Check if username already exists and make it unique
		$checkQuery = "SELECT COUNT(*) FROM $tblBroker WHERE fldUsername = ?";
		$checkStmt = mysqli_prepare($_SESSION['db'], $checkQuery);
		mysqli_stmt_bind_param($checkStmt, "s", $finalUsername);
		mysqli_stmt_execute($checkStmt);
		$checkResult = mysqli_stmt_get_result($checkStmt);
		$usernameCount = mysqli_fetch_row($checkResult)[0];
		
		if($usernameCount > 0) {
			$finalUsername = $finalUsername . rand(100, 999);
		}
		
		// ONE-TIME LOGIN LINK SYSTEM (Current Active System)
		// Generate one-time login token (no temporary password needed)
		$oneTimeToken = bin2hex(random_bytes(32));
		$tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

		// INSERT with one-time token (password will be empty until user sets it)
		$query = "INSERT INTO $tblBroker (fldUsername, fldPassword, fldName, fldEmail, fldUserType, fldActive, fldSetupToken, fldSetupExpiry, fldFirstLogin) VALUES(\"$finalUsername\",\"\",\"$brokerName\",\"$email\",\"$userType\",\"1\",\"$oneTimeToken\",\"$tokenExpiry\",\"1\")";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result) {
			$newBrokerID = mysqli_insert_id($_SESSION['db']);
			
			// Send setup email if requested
			if($sendSetupEmail) {
				$setupLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/util/account_creation.php?token=" . $setupToken . "&id=" . $newBrokerID;
				
				require_once("../util/email_sender.php");
				$emailSender = new EmailSender();
				
							// ONE-TIME LOGIN LINK SYSTEM: Send welcome email with secure login link
			$oneTimeLoginLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/one_time_login.php?token=" . $oneTimeToken . "&id=" . $newBrokerID;
			$emailResult = $emailSender->sendWelcomeEmail($email, $brokerName, $finalUsername, $oneTimeLoginLink);
			$emailSent = $emailResult['success'];
			
			if($emailSent) {
				echo("<p class=\"confirmMessage\">Broker created successfully! Welcome email sent to $email</p>\n");
				echo("<p class=\"note\">Username: <strong>$finalUsername</strong> (auto-generated)</p>\n");
				echo("<p class=\"note\">One-time login link sent via email</p>\n");
				echo("<p class=\"note\">User will be prompted to set password on first login</p>\n");
			} else {
				echo("<p class=\"warningMessage\">Broker created but welcome email could not be sent. Please contact the user directly.</p>\n");
			}
				
				$step = "confirm";
			} else {
				$step = "confirm";
			}
		}
	}
	else if($action == "update"){
		// Check if current user is trying to modify a Super Admin account
		$targetBrokerID = $_POST["brokerID"];
		$queryCheck = "SELECT fldUserType FROM $tblBroker WHERE pkBrokerID='$targetBrokerID'";
		$resultCheck = mysqli_query($_SESSION['db'],$queryCheck);
		$targetUserType = mysqli_fetch_row($resultCheck)[0];
		
		// Regular admins cannot modify Super Admin accounts
		if($targetUserType == "2" && $_SESSION["userType"] != "2") {
			echo("<p class=\"warningMessage\">Access Denied: Regular administrators cannot modify Super Administrator accounts.</p>\n");
			echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");
			echo("<script language=\"javascript\">opener.location.reload(true);</script>\n");
			exit;
		}
		
		//add new client		
		// Get current broker data to compare with form data
		$currentBrokerQuery = "SELECT fldActive, fldUserType FROM $tblBroker WHERE pkBrokerID = $brokerID";
		$currentBrokerResult = mysqli_query($_SESSION['db'], $currentBrokerQuery);
		$currentBrokerData = mysqli_fetch_row($currentBrokerResult);
		$currentActiveStatus = $currentBrokerData[0];
		$currentUserType = $currentBrokerData[1];
		
		// If only user type is being changed, preserve the current active status
		// This prevents the active status from being accidentally changed to inactive
		if($userType != $currentUserType && $active == "0" && $currentActiveStatus == "1") {
			// User is changing user type but form submitted inactive status
			// Preserve the current active status
			$active = $currentActiveStatus;
		}
		// If active status is not provided or is empty, preserve current status
		elseif($active === "" || $active === null) {
			$active = $currentActiveStatus;
		}
		
		$query = "UPDATE $tblBroker SET fldUsername=\"$username\"";
		
		// Debug: Show what we're getting (can be removed once confirmed working)
		if(isSuperAdmin() && isset($_GET["debug"]) && $_GET["debug"] == "1") {
			$statusPreserved = ($userType != $currentUserType && $active == "0" && $currentActiveStatus == "1");
			
			echo "<div style='background: #fff3cd; border: 1px solid #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
			echo "<strong>Super Admin Debug - Broker Update:</strong><br>";
			echo "Broker ID: $brokerID<br>";
			echo "Current Active Status (DB): '" . $currentActiveStatus . "'<br>";
			echo "Current User Type (DB): '" . $currentUserType . "'<br>";
			echo "Form Active Status (raw): '" . htmlspecialchars($_POST["active"]) . "'<br>";
			echo "Final Active Status: '" . htmlspecialchars($active) . "'<br>";
			echo "Form User Type: '" . htmlspecialchars($userType) . "'<br>";
			echo "Status Preserved (User Type Change): " . ($statusPreserved ? "YES" : "NO") . "<br>";
			echo "Password field value: '" . htmlspecialchars($password) . "'<br>";
			echo "Password length: " . strlen($password) . "<br>";
			echo "Password empty check: " . (empty($password) ? "Yes" : "No") . "<br>";
			echo "</div>";
		}
		
		if($password != "")
			$query.= ", fldPassword=\"".md5($password)."\"";
		$query.= ", fldName=\"$brokerName\", fldEmail=\"$email\", fldUserType=\"$userType\", fldActive=\"$active\" WHERE pkBrokerID=\"$brokerID\"";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows($_SESSION['db']);
		if($count == 1)
			$step = "confirmUpdate";
		else {
			echo("<p class=\"warningMessage\">No new data entered. Please try again.<p>\n");
		}
	}
	
	if($step == "confirm" || $step == "confirmUpdate") {
		if($step == "confirm")
			echo("<p class=\"confirmMessage\">Broker information has been added successfully.</p>\n");
		else if($step == "confirmUpdate")
			echo("<p class=\"confirmMessage\">Broker information has been updated successfully.</p>\n");
		echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");	
		echo("<script language=\"javascript\">opener.location.reload(true);</script>\n");
	}
	else {
		
		$brokerID = $_GET["brokerID"];
		
		if($brokerID != ""){
			$query = "SELECT pkBrokerID, fldUsername, fldPassword, fldName, fldEmail, fldUserType, fldActive FROM $tblBroker WHERE pkBrokerID=$brokerID";
			$result = mysqli_query($_SESSION['db'],$query);
			@$count = mysqli_num_rows($result);
			
			if($count == 1){
				$resultSet = mysqli_fetch_row($result);
				/*
				0 = pkBrokerID (broker ID)
				1 = fldUsername (username)
				2 = fldPassword (password)
				3 = fldName (name)
				4 = fldEmail (email)
				5 = fldUserType (type)
				6 = fldActive (active)
				*/
				
				// Check if current user is trying to view/edit a Super Admin account
				$targetUserType = $resultSet[5];
				if($targetUserType == "2" && $_SESSION["userType"] != "2") {
					echo("<p class=\"warningMessage\">Access Denied: Regular administrators cannot view or modify Super Administrator accounts.</p>\n");
					echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");
					echo("<script language=\"javascript\">opener.location.reload(true);</script>\n");
					exit;
				}
				
				$action = "update";
			}
		}
		else
			$action = "addNew";
	
		?>
		
		<p>Enter the detailed broker information below.<br />
		<strong>NOTE:</strong> This does not impact email settings.</p>
	
		<form name="brokerInfo" action="" method="post" onsubmit="return validatePassword();">
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Broker Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
					<td class="fieldLabel" nowrap="nowrap">Broker Name: </td>
					<td><input name="brokerName" type="text" id="brokerName" size="30" value="<?php echo($resultSet[3]); ?>" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Broker Email: </td>
					<td><input name="email" type="text" id="email" value="<?php echo($resultSet[4]); ?>" size="30" /></td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Username: </td>
				    <td>
						<?php if($action == "addNew"): ?>
							<span class="note">Username will be auto-generated as: first letter of first name + last name</span>
							<input type="hidden" name="username" value="" />
						<?php else: ?>
							<input name="username" type="text" id="username" value="<?php echo($resultSet[1]); ?>" size="20" readonly />
						<?php endif; ?>
					</td>
			      </tr>
				  				  <tr>
					<td class="fieldLabel">Password: </td>
					<td>
						<?php
	// Debug: Show what action we're in
	echo "<!-- Action Debug: " . (isset($_GET["action"]) ? $_GET["action"] : "Not set") . " -->";
	echo "<!-- Action == update: " . ($_GET["action"] == "update" ? "Yes" : "No") . " -->";
	echo "<!-- POST action: " . (isset($_POST["action"]) ? $_POST["action"] : "Not set") . " -->";
	echo "<!-- Session Debug: userType = " . (isset($_SESSION["userType"]) ? $_SESSION["userType"] : "Not set") . " -->";
	echo "<!-- Session Debug: username = " . (isset($_SESSION["username"]) ? $_SESSION["username"] : "Not set") . " -->";
						
						// Check if this is an update operation (either via GET or POST)
						// Also check if we have a brokerID in the URL (indicating we're editing an existing broker)
						$isUpdate = (isset($_GET["action"]) && $_GET["action"] == "update") || 
								   (isset($_POST["action"]) && $_POST["action"] == "update") ||
								   (isset($_GET["brokerID"]) && $_GET["brokerID"] != "");
						echo "<!-- Is Update: " . ($isUpdate ? "Yes" : "No") . " -->";
						echo "<!-- Update Check Details: GET action=" . (isset($_GET["action"]) ? $_GET["action"] : "none") . ", POST action=" . (isset($_POST["action"]) ? $_POST["action"] : "none") . ", brokerID=" . (isset($_GET["brokerID"]) ? $_GET["brokerID"] : "none") . " -->";
						
						if($isUpdate) {
							// Check if user is Super Admin - prioritize URL parameter for popup reliability
							$isSuperAdmin = false;
							
							// Debug Super Admin detection
							$debugInfo = "<!-- Super Admin Detection Debug -->";
							$debugInfo .= "<!-- GET superAdmin: " . (isset($_GET["superAdmin"]) ? $_GET["superAdmin"] : "Not set") . " -->";
							$debugInfo .= "<!-- isSuperAdmin(): " . (isSuperAdmin() ? "Yes" : "No") . " -->";
							$debugInfo .= "<!-- Session userType: " . (isset($_SESSION["userType"]) ? $_SESSION["userType"] : "Not set") . " -->";
							
							// First check URL parameter (most reliable for popups)
							if(isset($_GET["superAdmin"]) && $_GET["superAdmin"] == "1") {
								$isSuperAdmin = true;
								$debugInfo .= "<!-- Using URL parameter -->";
							}
							// Then check session (fallback)
							elseif(isSuperAdmin()) {
								$isSuperAdmin = true;
								$debugInfo .= "<!-- Using session -->";
							}
							
							echo $debugInfo;
							
							if($isSuperAdmin) {
								echo("<div style=\"margin-bottom: 10px;\">");
								echo("<input name=\"password\" type=\"password\" id=\"password\" size=\"25\" value=\"\" placeholder=\"Enter new password (min 6 characters)\" style=\"padding: 5px; border: 1px solid #ccc; border-radius: 3px;\" /> ");
								echo("<br><span class=\"note\" style=\"font-size: 11px; color: #666;\">Super Admin: Enter new password to change, leave blank to keep current password</span>");
								echo("</div>");
								
								// Add Send Password Reset Link
								$brokerID = $_GET["brokerID"];
								echo("<div style=\"margin-top: 5px;\">");
								echo("<a href=\"#\" onclick=\"sendPasswordReset($brokerID); return false;\" style=\"color: #007bff; text-decoration: underline; font-size: 12px;\">");
								echo("Send Password Reset Email");
								echo("</a>");
								echo("</div>");
							} else {
								echo("<span class=\"note\">Password will be set by user via email setup link</span>\n");
								echo("<input type=\"hidden\" name=\"manualPassword\" value=\"\" />\n");
							}
						} else {
							echo("<span class=\"note\">Password will be set by user via email setup link</span>\n");
							echo("<input type=\"hidden\" name=\"manualPassword\" value=\"\" />\n");
						}
						?>
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">User Type:</td>
				    <td>
						<?php if(!$isUpdate): ?>
							<select name="userType">
								<?php
									// Set default to general user (usually value 0) if this is a new broker
									// Note: 0=General User, 1=Administrator, 2=Super Administrator
									$defaultUserType = "0";
									getUserTypeList($defaultUserType);
								?>
							</select>
						<?php else: ?>
							<?php
							// Check if user is Super Admin - prioritize URL parameter for popup reliability
							$isSuperAdmin = false;
							
							// First check URL parameter (most reliable for popups)
							if(isset($_GET["superAdmin"]) && $_GET["superAdmin"] == "1") {
								$isSuperAdmin = true;
							}
							// Then check session (fallback)
							elseif(isSuperAdmin()) {
								$isSuperAdmin = true;
							}
							?>
							<select name="userType" <?php echo(!$isSuperAdmin ? "disabled" : ""); ?>>
								<?php
									// Use the broker's current user type from the database
									$currentUserType = $resultSet[5];
									
									// Debug: Show what user type we're working with
									echo "<!-- Debug: Current User Type from DB: '" . htmlspecialchars($currentUserType) . "' -->";
									echo "<!-- Debug: resultSet[5] = '" . htmlspecialchars($resultSet[5]) . "' -->";
									echo "<!-- Debug: resultSet array: " . print_r($resultSet, true) . " -->";
									
									// Try to convert hash to numeric value if needed
									$numericUserType = "";
									if($currentUserType == "0" || $currentUserType == "1" || $currentUserType == "2") {
										// It's already numeric
										$numericUserType = $currentUserType;
									} else {
										// It's a hash, try to find the matching numeric value
										$hashQuery = "SELECT pkTypeID,fldName,fldValue FROM tblB2TUserType WHERE fldActive=1";
										$hashResult = mysqli_query($_SESSION['db'],$hashQuery);
										while($hashRow = mysqli_fetch_row($hashResult)){
											if(md5($hashRow[2]) == $currentUserType) {
												$numericUserType = $hashRow[2];
												break;
											}
										}
									}
									
									// Use the numeric value or fall back to the raw value
									$userTypeToPass = ($numericUserType != "") ? $numericUserType : $currentUserType;
									
									// Debug: Show what user type we're passing to the dropdown
									echo "<!-- Debug: Numeric User Type: '" . htmlspecialchars($numericUserType) . "' -->";
									echo "<!-- Debug: User Type to Pass: '" . htmlspecialchars($userTypeToPass) . "' -->";
									
									if($isSuperAdmin) {
										// Super admins can change user types
										getUserTypeList($userTypeToPass);
									} else {
										// Show current user type but don't allow changes
										$currentType = getUserType($currentUserType);
										echo("<option value=\"".$currentUserType."\" selected>".$currentType." (Cannot Change)</option>");
									}
								?>
							</select>
							<?php if(!$isSuperAdmin): ?>
							<span class="note">User type changes restricted to Super Administrators</span>
							<?php endif; ?>
						<?php endif; ?>
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Status:</td>
				    <td>
						<?php if(!$isUpdate): ?>
							<select name="active" id="activeStatus">
								<option value="1">Active</option>
								<option value="0">Inactive</option>
							</select>
							<span class="note" id="statusNote">Status will be automatically set based on creation method</span>
						<?php else: ?>
							<select name="active">
								<option value="1" <?php if($resultSet[6] == "1") echo("selected=\"selected\""); ?>>Active</option>
								<option value="0" <?php if($resultSet[6] == "0") echo("selected=\"selected\""); ?>>Inactive</option>
							</select>
						<?php endif; ?>
					</td>
			      </tr>
				  <?php /* Temporarily disabled until fldLastLogin field is added
				  if($action == "update" && isset($_SESSION["userType"]) && $_SESSION["userType"] == "2"): ?>
				  <tr>
				    <td class="fieldLabel">Last Login:</td>
				    <td>
						<?php 
						$lastLogin = isset($resultSet[7]) ? $resultSet[7] : null;
						if($lastLogin) {
							echo(date('M j, Y g:i A', strtotime($lastLogin)));
						} else {
							echo("<span style=\"color: #999;\">Never logged in</span>");
						}
						?>
					</td>
			      </tr>
				  <?php endif; */ ?>
				</table>
			</div>
		</div>
		
		<div class="clear"></div>		
		
		<div>
			<div class="floatLeft">
				<input type="button" name="cancel" value="Cancel" onclick="confirmCancel('cancel this change');" class="buttonRed" />
			</div>
					<div class="floatRight">
			<?php
			if($action == "")
				$action = "addNew";
			
			if($action == "update"){
				echo("<input type=\"submit\" name=\"submitForm\" value=\"Update &gt;\" class=\"buttonGreen\" /> \n");
				echo("<input type=\"hidden\" name=\"brokerID\" value=\"$brokerID\" />\n");
			}
			else if($action == "addNew"){
				echo("<input type=\"submit\" name=\"submitForm\" value=\"Submit &gt;\" class=\"buttonGreen\" /> \n");
				echo("<input type=\"submit\" name=\"sendSetupEmail\" value=\"Send Setup Email &gt;\" class=\"buttonBlue\" style=\"margin-left: 10px;\" /> \n");
			}		
			
			echo("<input type=\"hidden\" name=\"action\" value=\"$action\" />\n");
			?>		
		</div>
		</div>
		</form>

	<script>
	function toggleUsernameField() {
		const adminChoice = document.querySelector('input[name="usernameChoice"][value="adminSet"]');
		const userChoice = document.querySelector('input[name="usernameChoice"][value="userSets"]');
		const usernameField = document.getElementById('username');
		const allowUserToSetUsername = document.getElementById('allowUserToSetUsername');
		
		// Only run if elements exist (for add new broker form)
		if(adminChoice && userChoice && usernameField && allowUserToSetUsername) {
			if(adminChoice.checked) {
				usernameField.placeholder = "Enter username for user";
				usernameField.readOnly = false;
				allowUserToSetUsername.value = "0";
			} else if(userChoice.checked) {
				usernameField.placeholder = "User will set their own username";
				usernameField.readOnly = true;
				usernameField.value = "";
				allowUserToSetUsername.value = "1";
			}
			
			updateStatusLogic();
		}
	}

	function togglePasswordField() {
		const emailChoice = document.querySelector('input[name="passwordChoice"][value="emailSetup"]');
		const manualChoice = document.querySelector('input[name="passwordChoice"][value="manualSet"]');
		const manualPasswordField = document.getElementById('manualPassword');
		const passwordNote = document.getElementById('passwordNote');
		
		// Only run if elements exist (for add new broker form)
		if(emailChoice && manualChoice && manualPasswordField && passwordNote) {
			if(emailChoice.checked) {
				manualPasswordField.style.display = "none";
				passwordNote.textContent = "Password will be set via email setup link";
			} else if(manualChoice.checked) {
				manualPasswordField.style.display = "inline-block";
				passwordNote.textContent = "Enter the password manually";
			}
			
			updateStatusLogic();
		}
	}

	function updateStatusLogic() {
		const adminUsernameEl = document.querySelector('input[name="usernameChoice"][value="adminSet"]');
		const manualPasswordEl = document.querySelector('input[name="passwordChoice"][value="manualSet"]');
		const statusSelect = document.getElementById('activeStatus');
		const statusNote = document.getElementById('statusNote');
		
		// Only run if elements exist (for add new broker form)
		if(adminUsernameEl && manualPasswordEl && statusSelect && statusNote) {
			const adminUsername = adminUsernameEl.checked;
			const manualPassword = manualPasswordEl.checked;
			
			if(adminUsername && manualPassword) {
				// Both username and password set manually - account is ready to use
				statusSelect.value = "1";
				statusSelect.disabled = true;
				statusNote.textContent = "Status: Active (manual setup complete)";
			} else {
				// Email setup or partial manual setup - account not ready
				statusSelect.value = "0";
				statusSelect.disabled = true;
				statusNote.textContent = "Status: Inactive (pending setup completion)";
			}
		}
	}

	// Send Password Reset Email function
	function sendPasswordReset(brokerID) {
		if(confirm("Send password reset email to this broker?")) {
			// Create a form to submit the request
			var form = document.createElement('form');
			form.method = 'POST';
			form.action = 'brokerDetail.php?action=update&brokerID=' + brokerID;
			
			// Add hidden fields
			var actionField = document.createElement('input');
			actionField.type = 'hidden';
			actionField.name = 'action';
			actionField.value = 'sendPasswordReset';
			form.appendChild(actionField);
			
			var brokerIDField = document.createElement('input');
			brokerIDField.type = 'hidden';
			brokerIDField.name = 'brokerID';
			brokerIDField.value = brokerID;
			form.appendChild(brokerIDField);
			
			// Submit the form
			document.body.appendChild(form);
			form.submit();
		}
	}

	// Validate password before form submission
	function validatePassword() {
		var passwordField = document.getElementById('password');
		if(passwordField && passwordField.value !== '') {
			if(passwordField.value.length < 6) {
				alert('Password must be at least 6 characters long');
				passwordField.focus();
				return false;
			}
		}
		return true;
	}

	// Initialize on page load
	document.addEventListener('DOMContentLoaded', function() {
		toggleUsernameField();
		togglePasswordField();
		updateStatusLogic();
	});
	</script>

	<?php
	}


	require_once("../util/popupBottom.php");
	?>