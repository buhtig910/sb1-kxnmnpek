	<?php
	require_once("../util/popupHeader.php");
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">

	<h2>Location Information</h2>
	
	<?php
	
	if($action == "addNew"){
		//add new client
		$query = "INSERT INTO $tblLocation VALUES('',\"$locationName\",\"$unit\",\"$currency\",\"$productGroup\",\"$globalRegion\",\"$geoRegion\",\"$subRegion\",\"$region\",\"$timezone\",\"$hourTemplate\",\"$isActive\")";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result)
			$step = "confirm";
	}
	else if($action == "update"){
		//add new client		
		$query = "UPDATE $tblLocation SET fldLocationName=\"$locationName\",fldActive=\"$isActive\"";
		//$query.= ", fldUnit=\"$unit\",fldCurrency=\"$currency\",fldProductGroup=\"$productGroup\",fldGlobalRegion=\"$globalRegion\"";
		//$query.= ", fldGeoRegion=\"$geoRegion\",fldGeoSubRegion=\"$subRegion\",fldRegion=\"$region\",fldTimezone=\"$timezone\",fldHourTemplate=\"$hourTemplate\"";
		$query.= " WHERE pkLocationID=$locationID";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows();
		if($count == 1)
			$step = "confirmUpdate";
	}
	
	if($step == "confirm" || $step == "confirmUpdate") {
		if($step == "confirm")
			echo("<p class=\"confirmMessage\">Location information has been added successfully.</p>\n");
		else if($step == "confirmUpdate")
			echo("<p class=\"confirmMessage\">Location information has been updated successfully.</p>\n");
		echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");	
		echo("<script language=\"javascript\">opener.location.reload(true);this.focus();</script>\n");
	}
	else {
		
		if($locationID != ""){
			$query = "SELECT * FROM $tblLocation WHERE pkLocationID=$locationID";
			$result = mysqli_query($_SESSION['db'],$query);
			@$count = mysqli_num_rows($result);
			
			if($count == 1){
				$resultSet = mysqli_fetch_row($result);
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
				
				$action = "update";
			}
		}
		else
			$action = "addNew";
	
		?>
		<script type="text/javascript" src="../scripts/ajax.js"></script>
		
		<p>Enter the detailed location information below.</p>
	
		<form name="clientInfo" action="" method="post">
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Trader Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
					<td nowrap="nowrap" class="fieldLabel">Trader Name: </td>
					<td colspan="4"><input name="locationName" type="text" id="locationName" size="45" value="<?php echo($resultSet[1]); ?>" /></td>
			      </tr>
				  <tr>
				    <td nowrap="nowrap" class="fieldLabel">Unit:</td>
				    <td><select name="unit" id="unit">
			        </select></td>
				    <td width="50">&nbsp;</td>
				    <td nowrap="nowrap" class="fieldLabel">Is Active: </td>
				    <td><input name="isActive" type="radio" value="1" id="activeYes" <?php if($resultSet[11] == 1 || $resultSet[11] == "") echo("checked=\"checked\""); ?> />
				      <label for="activeYes">Yes</label>
				      <input name="isActive" type="radio" value="0" id="activeNo" <?php if($resultSet[11] == 0 && $resultSet[11] != "") echo("checked=\"checked\""); ?> />
				      <label for="activeNo">No</label></td>
			      </tr>
				  <tr>
				    <td nowrap="nowrap" class="fieldLabel">Product Group:</td>
				    <td><select name="productGroup" id="productGroup">
			        </select></td>
				    <td>&nbsp;</td>
				    <td nowrap="nowrap" class="fieldLabel">Currency:</td>
				    <td><select name="currency" id="currency">
				      </select></td>
			      </tr>
				  <tr>
				    <td nowrap="nowrap" class="fieldLabel">Global Region:</td>
				    <td><select name="globalRegion" id="globalRegion">
			        </select></td>
				    <td>&nbsp;</td>
				    <td nowrap="nowrap" class="fieldLabel">Timezone:</td>
				    <td><select name="timezone" id="timezone">
				      </select></td>
			      </tr>
				  <tr>
				    <td nowrap="nowrap" class="fieldLabel">Geo Region:</td>
				    <td><select name="geoRegion" id="geoRegion">
			        </select></td>
				    <td>&nbsp;</td>
				    <td nowrap="nowrap" class="fieldLabel">Hours Template:</td>
				    <td><select name="hourTemplate" id="hourTemplate">
				      </select></td>
			      </tr>
				  <tr>
				    <td nowrap="nowrap" class="fieldLabel">Geo Sub-Region:</td>
				    <td><select name="subRegion" id="subRegion">
			        </select></td>
				    <td>&nbsp;</td>
				    <td nowrap="nowrap" class="fieldLabel">&nbsp;</td>
				    <td>&nbsp;</td>
			      </tr>
				  <tr>
				    <td nowrap="nowrap" class="fieldLabel">Region:</td>
				    <td><select name="region" id="region">
			        </select></td>
				    <td>&nbsp;</td>
				    <td class="fieldLabel">&nbsp;</td>
				    <td>&nbsp;</td>
			      </tr>
			  </table>
			</div>
		</div>
				
		<div class="clear"></div>
		
		<div>
			<div class="floatLeft">
				<input type="button" name="cancel" value="Cancel" onclick="confirmCancel('cancel this transaction');" class="buttonRed" />
			</div>
			<div class="floatRight">
				<?php
				if($action == "")
					$action = "addNew";
				
				if($action == "update"){
					echo("<input type=\"submit\" name=\"submitForm\" value=\"Update &gt;\" class=\"buttonGreen\" /> \n");
					echo("<input type=\"hidden\" name=\"locationID\" value=\"$locationID\" />\n");
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