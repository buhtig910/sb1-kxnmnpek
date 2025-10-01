<div id="pageGraphic">

	<!--<div class="imageMaskTall"></div>
	<img src="images/asdf.jpg" height="330" width="278" alt="" />-->
	
</div>
		
<div id="contentContainer">
		  
	<h1>Employee Login </h1>
	
	<?php 
	
	require("util/login.php"); 
	
	if(@validateSecurity()){
				
		echo("<br/><ul><li><a href=\"index.php?p=dashboard\">View My Dashboard</a></li>\n");
		
		$query = "SELECT fldEmail FROM $tblBroker WHERE pkBrokerID=".$_SESSION["brokerID42"];
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@list($email) = mysqli_fetch_row($result);
		$pieces = explode("@",$email);
		$uname = $pieces[0];
		//echo($username);
		
		//echo("<li><a href=\"http://mbox.bosworthbrokers.com/?username=".$uname."&password=".$_SESSION["pass78"]."\" target=\"_blank\">Access My Email</a></li>\n");
		
		require("util/userObj.php");		
		$user = new User();
		$user->requestResetEmail($_SESSION["username"]);
		
		echo("<li><a href=\"index.php?p=reset&a=".$user->getKey()."\">Change Password</a></li>\n");
		
		echo("</ul>\n");
	}	
	
	?>

</div>
