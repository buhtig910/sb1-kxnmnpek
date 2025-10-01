<?php
	require_once("../util/popupHeader.php");
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">

	<h2>Trader Information</h2>
	
	<?php
	
	if($_POST["action"] == "addNew"){
		//add new client
		$query = "INSERT INTO $tblClient VALUES(\"\",\"".$_POST["traderName"]."\",\"".$_POST["invoiceName"]."\",\"".$_POST["email"]."\",
			\"".$_POST["companyName"]."\",\"".$_POST["street1"]."\",\"".$_POST["street2"]."\",\"".$_POST["city"]."\",\"".$_POST["state"]."\",
			\"".$_POST["zip"]."\",\"".$_POST["country"]."\",\"".$_POST["phone"]."\",\"".$_POST["fax"]."\",\"".$_POST["confirmEmail"]."\",\"".$_POST["confirmClientName"]."\",
			\"".$_POST["confirmStreet1"]."\",\"".$_POST["confirmStreet2"]."\",\"".$_POST["confirmCity"]."\",\"".$_POST["confirmState"]."\",\"".$_POST["confirmZip"]."\",
			\"".$_POST["confirmCountry"]."\",\"".$_POST["confirmPhone"]."\",\"".$_POST["confirmFax"]."\",'1',\"".$_POST["companyShortName"]."\",\"".$_POST["parentCo"]."\")";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result)
			$step = "confirm";
	}
	else if($_POST["action"] == "update"){
		//add new client		
		$query = "UPDATE $tblClient SET fldTraderName=\"".$_POST["traderName"]."\", fldInvoiceName=\"".$_POST["invoiceName"]."\", fldEmail=\"".$_POST["email"]."\", fldCompanyName=\"".$_POST["companyName"]."\", fldStreet1=\"".$_POST["street1"]."\"";
		$query.= ", fldStreet2=\"".$_POST["street2"]."\", fldCity=\"".$_POST["city"]."\", fldState=\"".$_POST["state"]."\", fldZip=\"".$_POST["zip"]."\", fldCountry=\"".$_POST["country"]."\", fldPhone=\"".$_POST["phone"]."\", fldFax=\"".$_POST["fax"]."\"";
		$query.= ", fldConfirmEmail=\"".$_POST["confirmEmail"]."\", fldConfirmName=\"".$_POST["confirmClientName"]."\", fldConfirmStreet=\"".$_POST["confirmStreet1"]."\", fldConfirmStreet2=\"".$_POST["confirmStreet2"]."\"";
		$query.= ", fldConfirmCity=\"".$_POST["confirmCity"]."\", fldConfirmState=\"".$_POST["confirmState"]."\", fldConfirmZip=\"".$_POST["confirmZip"]."\", fldConfirmCountry=\"".$_POST["confirmCountry"]."\", fldConfirmPhone=\"".$_POST["confirmPhone"]."\", fldConfirmFax=\"".$_POST["confirmFax"]."\"";
		$query.= ", fldActive=\"".$_POST["isActive"]."\", fldShortName=\"".$_POST["companyShortName"]."\", fkCompanyID=\"".$_POST["parentCo"]."\"";
		$query.= " WHERE pkClientID=".$_POST["clientID"];
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows($_SESSION['db']);
		if($count == 1)
			$step = "confirmUpdate";
	}
	
	if($step == "confirm" || $step == "confirmUpdate") {
		if($step == "confirm")
			echo("<p class=\"confirmMessage\">Client information has been added successfully.</p>\n");
		else if($step == "confirmUpdate")
			echo("<p class=\"confirmMessage\">Client information has been updated successfully.</p>\n");
		echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");	
		echo("<script language=\"javascript\">opener.location.reload(true);this.focus();</script>\n");
	}
	else {
		
		if($_GET["clientID"] != ""){
			$query = "SELECT * FROM $tblClient WHERE pkClientID=".$_GET["clientID"];
			//echo($query);
			$result = mysqli_query($_SESSION['db'],$query);
			@$count = mysqli_num_rows($result);
			
			if($count == 1){
				$resultSet = mysqli_fetch_row($result);
				/*
				0 = client ID
				1 = trader name
				2 = invoice name
				3 = email
				4 = company name
				5 = street 1
				6 = street 2
				7 = city
				8 = state
				9 = zip
				10 = country
				11= phone
				12= fax
				13= confirm email
				14= confirm name
				15= confirm street
				16= confirm street2
				17= confirm city
				18= confirm state
				19= confirm zip
				20= confirm country
				21= confirm phone
				22= confirm fax
				23= active
				24 = company short name
				25= fkcompanyID
				*/
				
				$action = "update";
			}
		}
		else
			$action = "addNew";
	
		?>
		<script type="text/javascript" src="../scripts/ajax.js"></script>
		
		<p>Enter the detailed trader information below.</p>
	
		<form name="clientInfo" action="" method="post">
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Trader Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Trader Name: </td>
					<td><input name="traderName" type="text" id="traderName" size="25" value="<?php echo($resultSet[1]); ?>" /></td>
				    <td width="50">&nbsp;</td>
				    <td class="fieldLabel">Is Active: </td>
				    <td><input name="isActive" type="radio" value="1" id="activeYes" <?php if($resultSet[23] == 1 || $resultSet[23] == "") echo("checked=\"checked\""); ?> />
                        <label for="activeYes">Yes</label>
                        <input name="isActive" type="radio" value="0" id="activeNo" <?php if($resultSet[23] == 0 && $resultSet[23] != "") echo("checked=\"checked\""); ?> />
                        <label for="activeNo">No</label>					</td>
				  </tr>
				 <tr>
					<td class="fieldLabel">Company Name: </td>
					<td><input name="companyName" type="text" id="companyName" size="30" value="<?php echo($resultSet[4]); ?>" /></td>
				    <td align="center">- or - </td>
				    <td class="fieldLabel">Copy From Existing: </td>
				    <td><select name="existing" id="existing" onchange="updateExistingClient(this);">
						<?php
							populateExistingClients();
						?>
				      </select>
				    </td>
				 </tr>
				 <tr>
				   <td class="fieldLabel">Short Name: </td>
				   <td><input name="companyShortName" type="text" id="companyShortName" size="30" value="<?php echo($resultSet[24]); ?>" /></td>
				   <td>&nbsp;</td>
				   <td class="fieldLabel">Parent Company: <span class="requiredField">*</span></td>
				   <td><select name="parentCo" id="parentCo">
						<?php
							populateExistingCompanies($resultSet[25]);
						?>
				      </select></td>
			      </tr>
			  </table>
			</div>
		</div>
		
		<div>
		<div class="fieldGroup groupHalf" style="margin-right: 15px;">
			<div class="groupHeader">
				Invoice Information
			</div>
			<div class="groupContent">
            	<p>This information is only editable in the Company profile.</p>
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Invoice Contact Name: </td>
					<td><input name="invoiceName" type="text" disabled="disabled" id="invoiceName" value="<?php echo($resultSet[2]); ?>" size="30" readonly="readonly" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel" valign="top">Invoice Contact Email: </td>
					<td>
						<textarea name="email" cols="28" rows="2" disabled="disabled" readonly="readonly" id="email"><?php echo($resultSet[3]); ?></textarea>
						<!-- <input name="email" type="text" id="email" value="" size="30" />-->
					</td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 1: </td>
				    <td><input name="street1" type="text" disabled="disabled" id="street1" value="<?php echo($resultSet[5]); ?>" size="30" readonly="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 2: </td>
				    <td><input name="street2" type="text" disabled="disabled" id="street2" value="<?php echo($resultSet[6]); ?>" size="30" readonly="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">City:</td>
				    <td><input name="city" type="text" disabled="disabled" id="city" value="<?php echo($resultSet[7]); ?>" size="20" readonly="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">State:</td>
				    <td><input name="state" type="text" disabled="disabled" id="state" value="<?php echo($resultSet[8]); ?>" size="10" maxlength="40" readonly="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Zip:</td>
				    <td><input name="zip" type="text" disabled="disabled" id="zip" value="<?php echo($resultSet[9]); ?>" size="5" maxlength="5" readonly="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Country:</td>
				    <td><input name="country" type="text" disabled="disabled" id="country" value="<?php echo($resultSet[10]); ?>" size="20" readonly="readonly" />
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Phone:</td>
				    <td><input name="phone" type="text" disabled="disabled" id="phone" value="<?php echo($resultSet[11]); ?>" size="15" maxlength="12" readonly="readonly" /> 
				    <span class="note">XXX-XXX-XXX</span> </td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="fax" type="text" disabled="disabled" id="fax" value="<?php echo($resultSet[12]); ?>" size="15" maxlength="12" readonly="readonly" /> 
				      <span class="note">XXX-XXX-XXX</span> </td>
			      </tr>
				</table>
			</div>
		</div>
		
		<div class="fieldGroup groupHalf">
			<div class="groupHeader">
				Trade Confirm Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
				    <td class="fieldLabel">&nbsp;</td>
				    <td><input type="checkbox" name="makeSame" value="checkbox" id="makeSame" onchange="setSame(this);" />
			        <label for="makeSame">Same as Invoice Information</label></td>
			      </tr>
				  <tr>
					<td class="fieldLabel">Confirm Contact Name: </td>
					<td><input name="confirmClientName" type="text" id="confirmClientName" size="30" value="<?php echo($resultSet[14]); ?>" /></td>
				  </tr>
				  <tr>
				    <td valign="top" class="fieldLabel">Trade Confirm Email: </td>
				    <td>
						<textarea name="confirmEmail" rows="2" cols="28" id="confirmEmail"><?php echo($resultSet[13]); ?></textarea>
						<!--<input name="confirmEmail" type="text" id="confirmEmail" value="" size="30" />-->
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 1: </td>
				    <td><input name="confirmStreet1" type="text" id="confirmStreet1" size="30" value="<?php echo($resultSet[15]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 2: </td>
				    <td><input name="confirmStreet2" type="text" id="confirmStreet2" size="30" value="<?php echo($resultSet[16]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">City:</td>
				    <td><input name="confirmCity" type="text" id="confirmCity" size="20" value="<?php echo($resultSet[17]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">State:</td>
				    <td><input name="confirmState" type="text" id="confirmState" size="10" maxlength="40" value="<?php echo($resultSet[18]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Zip:</td>
				    <td><input name="confirmZip" type="text" id="confirmZip" size="5" maxlength="5" value="<?php echo($resultSet[19]); ?>" /></td>
			      </tr>
				   <tr>
				    <td class="fieldLabel">Country:</td>
				    <td><input name="confirmCountry" type="text" id="confirmCountry" size="20" value="<?php echo($resultSet[20]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Phone:</td>
				    <td><input name="confirmPhone" type="text" id="confirmPhone" value="<?php echo($resultSet[21]); ?>" size="15" maxlength="12" /> 
				    <span class="note">XXX-XXX-XXX</span> </td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="confirmFax" type="text" id="confirmFax" value="<?php echo($resultSet[22]); ?>" size="15" maxlength="12" /> 
				      <span class="note">XXX-XXX-XXX</span> </td>
			      </tr>
				</table>
			</div>
		</div>
		</div>
		
		<div class="clear"></div>

		
		
		<div>
			<div class="floatLeft">
				<input type="button" name="cancel" value="Cancel" onclick="confirmCancel('cancel this transaction');" class="buttonRed" />
			</div>
			<div class="floatRight">
				<?php				
				if($_GET["clientID"] != "" && $_GET["clientID"] != "undefined"){
					echo("<input type=\"submit\" name=\"submitForm\" value=\"Update &gt;\" class=\"buttonGreen\" /> \n");
					echo("<input type=\"hidden\" name=\"clientID\" value=\"".$_GET["clientID"]."\" />\n");
					$action = "update";
				}
				else {
					echo("<input type=\"submit\" name=\"submitForm\" value=\"Submit &gt;\" class=\"buttonGreen\" /> \n");
					$action = "addNew";
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