<?php
session_start();
require_once("functions.php");
require_once("vars.inc.php");

if(strpos($PHP_SELF,"company.php") > 0 || !validateSecurity()){
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
		// Check if user is Super Admin
		if(!isSuperAdmin()) {
			echo("<p class=\"errorMessage\">Access denied. Only Super Administrators can delete companies.</p>\n");
		} else {
			//delete company by making them inactive
			$query = "UPDATE $tblCompany SET fldActive=0 WHERE pkCompanyID=".$_POST["deleteID"];
			$result = mysqli_query($_SESSION['db'],$query);
			$count = mysqli_affected_rows($_SESSION['db']);
			
			if($count == 1){
				echo("<p class=\"confirmMessage\">Company removed successfully.</p>\n");
			}
			else {
				echo("<p class=\"errorMessage\">Error removing company.</p>\n");
			}
		}
	}
	
	
	//update sort order, field
	if($_POST["sortOrder"] != "")
		$_SESSION["sortOrder2"] = $_POST["sortOrder"];
	
	if(!isset($_POST["statusID"]))
		$statusID = 0;
			
	if($_POST["sortField"] != ""){
		if($_POST["sortField"] == "companyName")
			$_SESSION["sortBy2"] = "fldCompanyName";
	}
	else{
		$_SESSION["sortBy2"] = "fldCompanyName";
		$_SESSION["sortOrder2"] = "ASC";
		$last = "traderName";
	}
	
	
	//GET ALL FIELDS TO DISPLAY ON MAIN PAGE
	$query = "SELECT * FROM $tblCompany";
	
	if($_POST["companyName"] != "") {
		$query.= " WHERE fldCompanyName LIKE \"%".preg_replace("/\s+/","%",$_POST["companyName"])."%\"";
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
		  <h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('open');"><img src="images/bullet_triangle_closed.gif" alt="" width="10" height="10" border="0" class="tight" /> Company Filters</a></h3>
		</div>
		<div id="filtersOpen" style="display: <?php if($_SESSION["filters"] == "none") echo("none"); ?>;">
		  <h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('close');"><img src="images/bullet_triangle_open.gif" alt="" width="10" height="10" border="0" class="tight" /> Company Filters</a></h3>
		</div>
		
		<div id="filters" style="display: <?php echo($_SESSION["filters"]); ?>;">
		<table border="0" cellspacing="3" cellpadding="0">
		  <tr>
		    <td class="fieldLabel">Company Name: </td>
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
			<input type="button" name="addNew" value="Add New Company" onclick="launchCompany();" class="buttonBlue"/>
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
		<th width="200" <?php if($last == "companyName") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('companyName');">Company Name</a> <?php addSortArrow("companyName",$last); ?></th>
		<th width="150">Invoice Email</th>
		<th>Address</th>
		<th width="80">Traders</th>
	  </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = pkCompanyID
				1 = company name
				2 = confirmHub ID
				3 = invoice name
				4 = invoice email
				5 = street 1
				6 = street 2
				7 = city
				8 = state
				9 = zip
				10 = country
				11 = phone
				12 = fax
				13 = active	
				*/
				
				$queryTraderCount = "SELECT count(*) FROM tblB2TClient WHERE fkCompanyID=".$resultSet[0]." AND fldActive=1";
				//echo($queryTraderCount."<br>");
				$resultTraderCount = mysqli_query($_SESSION['db'],$queryTraderCount);
				@$traderCount = mysqli_fetch_row($resultTraderCount);	
				
			  
			  	$address = $resultSet[5];
				if($resultSet[6] != "")
					$address .= "<br />" .$resultSet[6];
				$address .= "<br />". $resultSet[7];
				$address .= ", ". $resultSet[8];
				$address .= " ".$resultSet[9];
			  
			  	$temp = $i % 2;
			 	echo("<tr class=\"$rowClass[$temp]\">\n");
				echo("<td align=\"center\">");
				if(isSuperAdmin()) {
					echo("<a href=\"javascript:void(0);\" onclick=\"confirmCompanyDelete($resultSet[0]);\">Delete</a> &nbsp; ");
				}
				echo("<a href=\"javascript:void(0);\" onclick=\"launchCompany($resultSet[0]);\">Edit</a></td>\n");
				echo("<td><a href=\"javascript:void(0);\" onclick=\"launchCompany($resultSet[0]);\">$resultSet[1]</a></td>\n");
				echo("<td>$resultSet[4]</td>\n");
				echo("<td>".$address."</td>\n");
				echo("<td align='center'>".$traderCount[0]."</td>");
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

	<script language="javascript" type="text/javascript">
		function confirmCompanyDelete(companyID) {
			if(confirm("Are you sure you want to delete this company? This action will make the company inactive and cannot be undone.")) {
				if(confirm("Final confirmation: You are about to permanently deactivate this company. Continue?")) {
					document.deleteForm.deleteID.value = companyID;
					document.deleteForm.submit();
				}
		}
	}
	</script>
	
	<?php if(isSuperAdmin()): // Super Admin only ?>
	<?php include('util/simpleCronDisplay.php'); ?>
	<?php endif; ?>