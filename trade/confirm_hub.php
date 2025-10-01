<?php

if(strpos($PHP_SELF,"confirm_hub.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}

require_once("util/indicesObj.php");
$index = new Indices();


if($_POST["action"] == "updatePassword") {
	
	if($_POST["ch_new_password"] != $_POST["ch_confirm_new_password"])
		$showMessage = "<p class=\"errorMessage\">New Password and Confirm New Password fields must match. Please enter a new password.</p>";
	else {
		require("util/confirm_hub/confirmHub.php");
		
		$hub = new ConfirmHub();
		$result = $hub->updatePassword($_POST["ch_new_password"]);
		
		if($result)
			$showMessage = "<p class=\"confirmMessage\">Password updated successfully.</p>";
		else
			$showMessage = "<p class=\"errorMessage\">Unable to update password. Please contact the site administrator.</p>";
	}
}
else if($_POST["action"] == "manualTrigger") {
	if($_POST["startDate"] != "") {
		$tempDate = reverseDate($_POST["startDate"]);
		$url = "util/cron21.php?date=".$tempDate;
	}
	else
		$url = "util/cron21.php";
		
	echo("<script language=\"javascript\">
			$.get(\"".$url."\", { date:\"".$tempDate."\" }, function(output) {
					//alert(output);
					alert(\"Manual push complete\");
				} );
		</script>
	");

	/* use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'GET',
		),
	);
	$result = file_get_contents($url,false);
	if ($result === FALSE) {
		echo("<p class=\"errorMessage\">An error occurred.</p>");
	}*/
}

?>

<script language="javascript" type="text/javascript" src="scripts/ajax.js"></script>
<script language="javascript" type="text/javascript" src="scripts/scripts.js"></script>

<link rel="stylesheet" type="text/css" href="css/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="css/calendar.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>


	<div style="height: 32px;">
	</div>
    
    <?php
	
	if($showMessage != "")
		echo($showMessage);
		
	?>
	
	<div class="scrollable">
	
	<div class="fieldGroup groupHalf" style="margin-right: 15px;">
		<div class="groupHeader">
			Configuration
		</div>
		<div class="groupContent">
			<p>Use the fields below to update the Confirm Hub password for the account 'pludwig'</p>
            
			<form name="indexForm" id="indexForm" action="" method="post">
			<table cellspacing="0" cellpadding="3" border="0">
			<tr>
			  <td class="fieldLabel">New Password:</td>
			  <td><label for="new_password"></label>
		      <input type="password" name="ch_new_password" id="ch_new_password" /></td>
			  </tr>
			<tr>
			  <td class="fieldLabel">Confirm New Password:</td>
			  <td><label for="confirm_new_password"></label>
		      <input type="password" name="ch_confirm_new_password" id="ch_confirm_new_password" /></td>
			  </tr>
			<tr>
			  <td class="fieldLabel">&nbsp;</td>
			  <td><input name="updatePassword" type="submit" id="updatePassword" value="Update Password" class="buttonGreen" />
              	<input type="hidden" name="action" value="updatePassword" />
              </td>
			  </tr>
			</table>
			</form>
			
		</div>
	</div>
    
    <div class="fieldGroup groupHalf">
		<div class="groupHeader">
			Manual Push
		</div>
		<div class="groupContent yui-skin-sam">
        	<form name="manualPush" method="post" action="">
            	<p><input type="text" size="10" name="startDate" id="startDate" />
                <img src="images/calendar.jpg" height="15" width="18" alt="Cal" id="show1up" class="center" /><br /> <span class="note">(Optional. Leaving this blank will trigger typical 15 min sweep. Enter date to sweep AND push all trades for that day to CH.)</span></p>
                <div id="cal1Container"></div>
                
				<input name="trigger" type="submit" id="triggerPush" value="Trigger Manual Push" class="buttonGreen" />
                <input type="hidden" name="action" value="manualTrigger" />
            </form>
		</div>
	</div>
	<div class="clear"></div>
    
    
     <script language="javascript" type="text/javascript">
	 			
		function mySelectHandler(type,args,obj) {
			var dates = args[0]; 
			var date = dates[0]; 
			var year = date[0], month = date[1], day = date[2];
			
			if(month.toString().length == 1)
				month = "0" + month;
			if(day.toString().length == 1)
				day = "0" + day;

			var dateStr = month + "/" + day + "/" + year;								
			displayDate("startDate",dateStr);
			this.hide();
		};
		
		YAHOO.namespace("example.calendar"); 
		 
		YAHOO.example.calendar.init = function() { 
			YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar("cal1","cal1Container", { title:"Choose a date:", close:true } );
			YAHOO.example.calendar.cal1.selectEvent.subscribe(mySelectHandler, YAHOO.example.calendar.cal1, true);
			YAHOO.example.calendar.cal1.render();
			 
			// Listener to show the single page Calendar when the button is clicked 
			YAHOO.util.Event.addListener("show1up", "click", YAHOO.example.calendar.cal1.show, YAHOO.example.calendar.cal1, true); 
		}
		
		YAHOO.util.Event.onDOMReady(YAHOO.example.calendar.init);
		
		</script>