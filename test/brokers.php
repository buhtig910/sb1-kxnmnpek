<?php

if(strpos($PHP_SELF,"brokers.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}
?>


	<div style="height: 32px;">
		<h1>Brokers</h1>
	</div>
		
	
	<?php
	if($_POST["action"] == "delete"){
		$query = "UPDATE $tblBroker SET fldActive=0 WHERE pkBrokerID=".$_POST["deleteID"];
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows();
		
		if($count == 1){
			echo("<p class=\"confirmMessage\">Broker removed successfully.</p>\n");
		}
	}
	
		$query = "SELECT pkBrokerID,fldName,fldEmail,fldUserType FROM $tblBroker WHERE fldActive=1 ORDER BY fldName ASC";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
	?>
	
	<div class="controlBar">
		<div class="floatLeft">
			<input type="button" name="addNew" value="Add New Broker" onclick="launchBroker();" class="buttonBlue" />
		</div>
		
		<div class="floatRight">
			<span class="fieldLabel">Brokers showing: <?php echo($count); ?></span> | 
			<input type="button" name="refresh" value="Refresh View" class="buttonSmall" onclick="refreshView('brokers');" />
		</div>
	</div>
	
	<div class="clear"></div>
	
	<form name="tradeList" action="" method="post">
	<div class="scrollable" style="height: 290px;">
	<table border="0" cellspacing="0" cellpadding="0" class="dataTable">
	  <tr>
		<th width="50">Action</th>
		<th width="100">Broker Name</th>
		<th width="100">User Type</th>
		<th>Broker Email</th>
	  </tr>
	  
	  <?php
	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = brokerID
				1 = brokerName
				2 = email
				3 = user type
				*/
				
			  	$temp = $i % 2;
			 	echo("<tr class=\"$rowClass[$temp]\">\n");
				echo("<td align=\"center\"><a href=\"javascript:void(0);\" onclick=\"confirmBrokerDelete($resultSet[0]);\">Delete</a></td>\n");
				echo("<td><a href=\"javascript:void(0);\" onclick=\"launchBroker($resultSet[0]);\">$resultSet[1]</a></td>\n");
				echo("<td>".getUserType($resultSet[3])."</td>\n");
				echo("<td>$resultSet[2]</td>\n");
			 	echo("</tr>\n");
				
				$i++;
			}
	  
		}
		else {
		?>
			<tr>
				<td colspan="6">No records found</td>
			</tr>
		<?php		
		}
		?>
	</table>
	</div>
	</form>
	
	<form name="deleteForm" action="" method="post">
		<input type="hidden" name="deleteID" value="" />
		<input type="hidden" name="action" value="delete" />
	</form>