<?php
session_start();
require_once("functions.php");
require_once("vars.inc.php");

if(strpos($PHP_SELF,"locations.php") > 0 || !validateSecurity()){
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
		$query = "UPDATE $tblLocation SET fldActive=0 WHERE pkLocationID=$deleteID";
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows();
		
		if($count == 1){
			echo("<p class=\"confirmMessage\">Location removed successfully.</p>\n");
		}
	}
	
	
	//update sort order, field
	if($_POST["sortOrder"] != "")
		$_SESSION["sortOrder2"] = $_POST["sortOrder"];
	
	if(!isset($_POST["locationStatus"]))
		$_POST["locationStatus"] = 1;
			
	if($_POST["sortField"] != ""){
		if($_POST["sortField"] == "traderName")
			$_SESSION["sortBy2"] = "fldTraderName";
		else if($_POST["sortField"] == "companyName")
			$_SESSION["sortBy2"] = "fldCompanyName";
	}
	else{
		$_SESSION["sortBy2"] = "fldLocationName";
		$_SESSION["sortOrder2"] = "ASC";
		$last = "locationName";
	}
	
	
	//GET ALL FIELDS TO DISPLAY ON MAIN PAGE
	$query = "SELECT * FROM $tblLocation";
	if($_POST["locationName"] != "")
		$query.= " WHERE fldLocationName LIKE \"%".preg_replace("/ /","%",$_POST["locationName"])."%\"";
	
	if($_POST["locationName"] == "")
		$query .= " WHERE fldActive=".$_POST["locationStatus"];
	else
		$query .= " AND fldActive=".$_POST["locationStatus"];
	//$query .= " AND pkClientID!=".$_SESSION["NYMEX_ID"]." AND pkClientID!=".$_SESSION["ICE_ID"];
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
		  <h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('open');"><img src="images/bullet_triangle_closed.gif" alt="" width="10" height="10" border="0" class="tight" /> Location Filters</a></h3>
		</div>
		<div id="filtersOpen" style="display: <?php if($_SESSION["filters"] == "none") echo("none"); ?>;">
		  <h3><a href="javascript:void(0);" onclick="showHide(filtersArray,'inline');toggleFilter('close');"><img src="images/bullet_triangle_open.gif" alt="" width="10" height="10" border="0" class="tight" /> Location Filters</a></h3>
		</div>
		
		<div id="filters" style="display: <?php echo($_SESSION["filters"]); ?>;">
		<table border="0" cellspacing="3" cellpadding="0">
		  <tr>
			<td class="fieldLabel">Location Name: </td>
			<td><input name="locationName" type="text" id="locationName" size="15" value="<?php echo($_POST["locationName"]); ?>" /></td>
			<td class="spacer">&nbsp;</td>
		    <td class="fieldLabel">Location Status: </td>
		    <td><select name="locationStatus" id="locationStatus">
		      <option value="1" selected="selected" <?php if($_POST["locationStatus"] == 1) echo("selected=\"selected\""); ?>>Active</option>
		      <option value="0" <?php if($_POST["locationStatus"] == 0) echo("selected=\"selected\""); ?>>Inactive</option>
	        </select></td>
		    <td class="spacer">&nbsp;</td>
	      </tr>
	  </table>

	   	<div class="floatRight">
	      <input name="apply" type="submit" id="apply" value="Search" class="buttonSmall" />
		  <input name="remove" type="button" id="remove" value="Reset" class="buttonSmall" onclick="clearLocationFilters();" />
		  
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
			<input type="button" name="addNew" value="Add New Location" onclick="launchLocation();" class="buttonBlue"/>
		</div>
		
		<div class="floatRight">
			<span class="fieldLabel">Locations: <?php echo($count); ?></span> | 
			<input type="button" name="refresh" value="Refresh View" class="buttonSmall" onclick="refreshView('locations');" />
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
		<th width="200" <?php if($last == "locationName") echo("class=\"sort\""); ?>><a href="javascript:void(0);" onclick="sorter('locationName');">Location Name</a> <?php addSortArrow("locationName",$last); ?></th>
		<th width="100">Geo. Region</th>
		<th width="100">Timezone</th>
		<th width="150">Product Group</th>
		<th width="80">Status</th>
	  </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	  
		if($count >=1){
			$i=0;
			$rowClass = Array("","evenRow");
			
			while($resultSet = mysqli_fetch_row($result)){
				/*
				0 = pkLocationID
				1 = location name
				2 = unit
				3 = currency
				4 = product group
				5 = global region
				6 = geo region
				7 = geo sub region
				8 = region
				9 = timezone
				10= hour template
				11= active
				*/
							  
			  	$temp = $i % 2;
			 	echo("<tr class=\"$rowClass[$temp]\">\n");
				echo("<td align=\"center\">");
					//echo("<a href=\"javascript:void(0);\" onclick=\"confirmClientDelete($resultSet[0]);\">Delete</a> &nbsp; ");
					echo("<a href=\"javascript:void(0);\" onclick=\"launchLocation($resultSet[0]);\">Edit</a></td>\n");
				echo("<td><a href=\"javascript:void(0);\" onclick=\"launchLocation($resultSet[0]);\">$resultSet[1]</a></td>\n");
				echo("<td>$resultSet[6]</td>\n");
				echo("<td>$resultSet[9]</td>\n");
				echo("<td>$resultSet[4]</td>\n");
				echo("<td>".locationStatus($resultSet[11])."</td>\n");
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