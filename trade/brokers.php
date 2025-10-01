<?php
session_start();
require_once("functions.php");
require_once("vars.inc.php");

if(strpos($PHP_SELF,"brokers.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}
?>


	<div style="height: 32px;">
	</div>
		
	
	<?php
		// Check if current user is Super Admin
		$isSuperAdmin = isSuperAdmin();
		
		if($isSuperAdmin) {
			// Super Admin: Show all brokers (both active and inactive) with status column
			$statusLabel = 'All Brokers';
			$query = "SELECT pkBrokerID,fldName,fldEmail,fldUserType,fldActive FROM $tblBroker ORDER BY fldActive DESC, fldName ASC";
		} else {
			// Regular users and admins: Show only active brokers
			$statusLabel = 'Active Brokers';
			$query = "SELECT pkBrokerID,fldName,fldEmail,fldUserType FROM $tblBroker WHERE fldActive='1' ORDER BY fldName ASC";
		}
		
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
	?>
	
	<div class="controlBar">
		<div class="floatLeft">
			<input type="button" name="addNew" value="Add New Broker" onclick="launchBroker();" class="buttonBlue" />
			<!-- Test Email Button - Super Admin Only -->
			<?php if($isSuperAdmin): ?>
			<a href="util/test_email.php" target="_blank" style="margin-left: 10px; padding: 8px 12px; background: #ff9800; color: white; text-decoration: none; border-radius: 3px; font-size: 12px;">Test Email</a>
			<?php endif; ?>
		</div>
		
		<div class="floatRight">
			<span class="fieldLabel">Brokers showing: <?php echo($count); ?></span> | 
			<input type="button" name="refresh" value="Refresh View" class="buttonSmall" onclick="refreshView('brokers');" />
		</div>
	</div>
	
	<div class="clear"></div>
	
	<form name="tradeList" action="" method="post">
	<div class="scrollable">
	<style>
	.scrollable {
		position: relative;
		overflow: auto;
		max-height: 70vh;
	}
	.dataTable thead {
		position: sticky;
		top: 0;
		z-index: 1000;
	}
	/* Ensure sticky headers work properly */
	.dataTable thead tr {
		position: sticky;
		top: 0;
		background: #FFFFFF;
		z-index: 1000;
	}
	

	</style>
	<table border="0" cellspacing="0" cellpadding="0" class="dataTable">
	  <thead>
	  <tr>
		<th width="200">Broker Name</th>
		<th width="150">User Type</th>
		<th>Broker Email</th>
		<?php if($isSuperAdmin): ?>
		<th width="100">Status</th>
		<?php endif; ?>
	  </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = brokerID
				1 = name
				2 = email
				3 = userType
				4 = fldActive (only for Super Admin query)
				*/
				
				$temp = $i % 2;
				
				if($isSuperAdmin) {
					$status = ($resultSet[4] == '1') ? '<span style="color: #4CAF50; font-weight: bold;">Active</span>' : '<span style="color: #f44336; font-weight: bold;">Inactive</span>';
				}
				
				echo("<tr class=\"$rowClass[$temp]\">\n");
				echo("<td><a href=\"javascript:launchBroker('".$resultSet[0]."');\">".$resultSet[1]."</a></td>\n");
				echo("<td>".getCurrentUserType($resultSet[3], $resultSet[0])."</td>\n");
				echo("<td>".$resultSet[2]."</td>\n");
				if($isSuperAdmin) {
					echo("<td>".$status."</td>\n");
				}
				echo("</tr>\n");
				$i++;
			}
		}
		else {
			$colspan = $isSuperAdmin ? 4 : 3;
			echo("<tr><td colspan=\"$colspan\" align=\"center\">No brokers found.</td></tr>\n");
		}
		?>
	  
	  </tbody>
	</table>
	</div>
	</form>
	
	<script>
	function launchBroker(brokerID) {
		if(brokerID) {
			window.open("trade/brokerDetail.php?brokerID=" + brokerID + "&superAdmin=1", "brokerDetail", "width=600,height=500,scrollbars=yes,resizable=yes");
		} else {
			window.open("trade/brokerDetail.php?superAdmin=1", "brokerDetail", "width=600,height=500,scrollbars=yes,resizable=yes");
		}
	}
	
	function changeBrokerView() {
		var selectedValue = document.getElementById('brokerStatusFilter').value;
		window.location.href = 'brokers.php?showArchived=' + selectedValue;
	}
	
	function refreshView(page) {
		location.reload();
	}
	</script>
	
