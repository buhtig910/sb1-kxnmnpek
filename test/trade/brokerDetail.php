	<?php
	require_once("../util/popupHeader.php");
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
	
	if($action == "addNew"){
		//add new client
		
		$query = "INSERT INTO $tblBroker VALUES('',\"$username\",\"".md5($password)."\",\"$brokerName\",\"$email\",md5(CONCAT(\"$username\",\"$email\")),\"$userType\",\"1\")";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result)
			$step = "confirm";
	}
	else if($action == "update"){
		//add new client		
		$query = "UPDATE $tblBroker SET fldUsername=\"$username\"";
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
			$query = "SELECT * FROM $tblBroker WHERE pkBrokerID=$brokerID";
			$result = mysqli_query($_SESSION['db'],$query);
			@$count = mysqli_num_rows($result);
			
			if($count == 1){
				$resultSet = mysqli_fetch_row($result);
				/*
				0 = broker ID
				1 = username
				2 = password
				3 = name
				4 = email
				5 = type
				6 = active
				*/
				
				$action = "update";
			}
		}
		else
			$action = "addNew";
	
		?>
		
		<p>Enter the detailed broker information below.<br />
		<strong>NOTE:</strong> This does not impact email settings.</p>
	
		<form name="brokerInfo" action="" method="post">
		
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
				    <td><input name="username" type="text" id="username" value="<?php echo($resultSet[1]); ?>" size="20" /></td>
			      </tr>
				  <tr>
					<td class="fieldLabel">Password: </td>
					<td>
						<?php
						if($_GET["action"] == "update")
							echo("<input name=\"password\" type=\"password\" id=\"password\" size=\"20\" value=\"\" /> <span class=\"note\">enter a new password to reset</span>\n");
						else
							echo("<input name=\"password\" type=\"password\" id=\"password\" size=\"20\" value=\"\" />\n");
						?>
					</td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">User Type:</td>
				    <td><select name="userType">
						<?php
							getUserTypeList($resultSet[6]);
						?>
					</select>
					</td>
			      </tr>
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
				}		
				
				echo("<input type=\"hidden\" name=\"action\" value=\"$action\" />\n");
				?>		
			</div>
		</div>
		</form>

	<?php
	}


	require_once("../util/popupBottom.php");
	?>