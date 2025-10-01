<?php
session_start();
require_once("functions.php");
require_once("vars.inc.php");

if(strpos($PHP_SELF,"clients.php") > 0 || !validateSecurity()){
	echo("<p>You do not have permission to view this page.</p>");
	echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
	exit;
}
?>

<script language="javascript" type="text/javascript" src="scripts/ajax.js"></script>

	<div style="height: 32px;">
	</div>
		
	
	<?php
	
	if($_POST["action"] == "delete"){
		//delete client by making them inactive
		$query = "UPDATE $tblClient SET fldActive=0 WHERE pkClientID=".$_POST["deleteID"];
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows($_SESSION['db']);
		
		if($count == 1){
			echo("<p class=\"confirmMessage\">Client removed successfully.</p>\n");
		}
	}
	
	
	//update sort order, field
	if($_POST["sortOrder"] != "")
		$_SESSION["sortOrder2"] = $_POST["sortOrder"];
	
	if(!isset($_POST["statusID"]))
		$statusID = 0;
			
	if($_POST["sortField"] != ""){
		if($_POST["sortField"] == "traderName")
			$_SESSION["sortBy2"] = "fldTraderName";
		else if($_POST["sortField"] == "companyName")
			$_SESSION["sortBy2"] = "fldCompanyName";
	}
	else{
		$_SESSION["sortBy2"] = "fldTraderName";
		$_SESSION["sortOrder2"] = "ASC";
		$last = "traderName";
	}
	
	
	//GET ALL FIELDS TO DISPLAY ON MAIN PAGE
	$query = "SELECT pkClientID,fldTraderName,fldCompanyName,fldEmail,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldPhone FROM $tblClient";
	if($_POST["traderName"] != "")
		$query.= " WHERE fldTraderName LIKE \"%".preg_replace("/\s+/","%",$_POST["traderName"])."%\"";
	if($_POST["companyName"] != "") {
		if($_POST["traderName"] == "")
			$query.= " WHERE";
		else
			$query.= " AND";
			
		$query.= " fldCompanyName LIKE \"%".preg_replace("/\s+/","%",$_POST["companyName"])."%\"";
	}
	if($_POST["emptyEmail"] != "") {
		if($traderName == "" && $companyName == "")
			$query.= " WHERE";
		else
			$query.=" AND";
		$query.= " fldConfirmEmail=\"\"";
	}
	
	if($_POST["traderName"] == "" && $_POST["companyName"] == "" && $_POST["emptyEmail"] == "")
		$query .= " WHERE fldActive=1";
	else
		$query .= " AND fldActive=1";
	$query .= " AND pkClientID!=".$_SESSION["NYMEX_ID"]." AND pkClientID!=".$_SESSION["ICE_ID"];
	$query .= " ORDER BY ".$_SESSION["sortBy2"]." ".$_SESSION["sortOrder2"];
	
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	?>
		
	
	<script language="javascript" type="text/javascript">
		filtersArray = new Array("filters","filtersClose","filtersOpen");
	</script>
	
	<?php
		if(!isset($_SESSION["filters"]))
			$_SESSION["filters"] = "none";
	?>

	
	<form name="tradeFilters" action="" method="post">
	<div class="tradeFilters">
		<div id="filtersClose" style="display: <?php if($_SESSION["filters"] == "") echo("none"); ?>;">
		  <h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('open');"><img src="images/bullet_triangle_closed.gif" alt="" width="10" height="10" border="0" class="tight" /> Trader Filters</a></h3>
		</div>
		<div id="filtersOpen" style="display: <?php if($_SESSION["filters"] == "none") echo("none"); ?>;">
		  <h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('close');"><img src="images/bullet_triangle_open.gif" alt="" width="10" height="10" border="0" class="tight" /> Trader Filters</a></h3>
		</div>
		
		<div id="filters" style="display: <?php echo($_SESSION["filters"]); ?>;">
		<table border="0" cellspacing="3" cellpadding="0">
		  <tr>
			<td class="fieldLabel">Trader Name : </td>
			<td><input name="traderName" type="text" id="traderName" size="15" value="<?php echo($_POST["traderName"]); ?>" /></td>
			<td class="spacer">&nbsp;</td>
		    <td class="fieldLabel">Company Name : </td>
		    <td><input name="companyName" type="text" id="companyName" size="15" value="<?php echo($_POST["companyName"]); ?>" /></td>
		    <td class="spacer">&nbsp;</td>
		    <td><input name="emptyEmail" type="checkbox" id="emptyEmail" <?php if($_POST["emptyEmail"] != "") echo("checked=\"checked\""); ?> /> <label for="emptyEmail">Missing Email Address</label></td>
		    <td>&nbsp;</td>
	      </tr>
	  </table>

	   	<div class="floatRight">
	      <input name="apply" type="submit" id="apply" value="Search" class="buttonSmall" />
		  <input name="remove" type="button" id="remove" value="Reset" class="buttonSmall" onclick="clearClientFilters();" />
		  
		  <input type="hidden" name="action" value="filter" />
		  <input type="hidden" name="sortField" value="<?php echo($_SESSION["sortBy2"]); ?>" />
		  <input type="hidden" name="sortOrder" value="<?php echo($_SESSION["sortOrder2"]); ?>" />
		  <input type="hidden" name="last" value="<?php echo($last); ?>" />
   	  	</div>
		
		<div class="clear"></div>
		
	  </div>
	</div>
	</form>
	
	<div class="controlBar">
		<div class="floatLeft">
			<input type="button" name="addNew" value="Add New Trader" onclick="launchClient();" class="buttonBlue"/>
		</div>
		
		<div class="floatRight">
			<span class="fieldLabel">Clients showing: <?php echo($count); ?></span> | 
			<input type="button" name="refresh" value="Refresh View" class="buttonSmall" onclick="refreshView('clients');" />
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
		<th width="60">Action</th>
		<th width="150" <?php if($last == "traderName") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('traderName');">Trader Name</a> <?php addSortArrow("traderName",$last); ?></th>
		<th width="200" <?php if($last == "companyName") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('companyName');">Company Name</a> <?php addSortArrow("companyName",$last); ?></th>
		<th width="150">Contact Email</th>
		<th>Address</th>
		<th width="80">Phone</th>
	  </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = pkClientID
				1 = trader name
				2 = company name
				3 = email
				4 = street1
				5 = street2
				6 = city
				7 = state
				8 = zip
				9 = phone
				*/
				
				$address = $resultSet[4];
				if($resultSet[5] != "")
					$address .= "<br />" .$resultSet[5];
				$address .= "<br />". $resultSet[6];
				$address .= ", ". $resultSet[7];
				$address .= " ".$resultSet[8];
			  
			  	$temp = $i % 2;
			 	echo("<tr class=\"$rowClass[$temp]\">\n");
				echo("<td align=\"center\"><a href=\"javascript:void(0);\" onclick=\"confirmClientDelete($resultSet[0]);\">Delete</a> &nbsp; <a href=\"javascript:void(0);\" onclick=\"launchClient($resultSet[0]);\">Edit</a></td>\n");
				echo("<td><a href=\"javascript:void(0);\" onclick=\"launchClient($resultSet[0]);\">$resultSet[1]</a></td>\n");
				echo("<td>$resultSet[2]</td>\n");
				echo("<td>$resultSet[3]</td>\n");
				echo("<td>$address</td>\n");
				echo("<td>$resultSet[9]</td>\n");
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
	</tbody>
	</table>
	</div>
	</form>
	
	<form name="deleteForm" action="" method="post">
		<input type="hidden" name="deleteID" value="" />
		<input type="hidden" name="action" value="delete" />
	</form>