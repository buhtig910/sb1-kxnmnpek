<?php
	require_once("../util/popupHeader.php");
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">
    <script language="javascript" type="text/javascript" src="../scripts/cookie.js"></script>
 
	<h2>Company Information</h2>
	
	<?php
	
	if($_POST["action"] == "addNew"){
		//add new client
		$query = "INSERT INTO $tblCompany VALUES(\"\",\"".$_POST["companyName"]."\",\"\",\"".$_POST["email"]."\",
			\"".$_POST["street1"]."\",\"".$_POST["street2"]."\",\"".$_POST["city"]."\",\"".$_POST["state"]."\",
			\"".$_POST["zip"]."\",\"".$_POST["country"]."\",\"".$_POST["phone"]."\",\"".$_POST["fax"]."\",\"".$_POST["excelInvoice"]."\",'1')";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result)
			$step = "confirm";
	}
	else if($_POST["action"] == "update"){
		//add new client		
		$query = "UPDATE $tblCompany SET fldInvoiceName=\"".$_POST["invoiceName"]."\", fldInvoiceEmail=\"".$_POST["email"]."\", fldStreet1=\"".$_POST["street1"]."\"";
		$query.= ", fldStreet2=\"".$_POST["street2"]."\", fldCity=\"".$_POST["city"]."\", fldState=\"".$_POST["state"]."\", fldZip=\"".$_POST["zip"]."\"";
		$query.= ", fldCountry=\"".$_POST["country"]."\", fldPhone=\"".$_POST["phone"]."\", fldFax=\"".$_POST["fax"]."\", fldExcelInvoice=\"".$_POST["excelInvoice"]."\"";
		$query.= ", fldActive=\"".$_POST["isActive"]."\"";
		$query.= " WHERE pkCompanyID=".$_POST["companyID"];
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows($_SESSION['db']);
		if($count == 1)
			$step = "confirmUpdate";
	}
	
	if($step == "confirm" || $step == "confirmUpdate") {
		if($step == "confirm")
			echo("<p class=\"confirmMessage\">Company information has been added successfully.</p>\n");
		else if($step == "confirmUpdate")
			echo("<p class=\"confirmMessage\">Company information has been updated successfully.</p>\n");
		echo("<p align=\"center\"><input type=\"button\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");	
		echo("<script language=\"javascript\">opener.location.reload(true);this.focus();</script>\n");
	}
	
	//else {
		
		if($_GET["companyID"] != ""){
			$query = "SELECT * FROM $tblCompany WHERE pkCompanyID=".$_GET["companyID"];
			//echo($query);
			$result = mysqli_query($_SESSION['db'],$query);
			@$count = mysqli_num_rows($result);
			
			if($count == 1){
				$resultSet = mysqli_fetch_row($result);
				/*
				0 = company ID
				1 = company name
				2 = confirmHub ID
				3 = invoice contact name
				4 = invoice email
				5 = street 1
				6 = street 2
				7 = city
				8 = state
				9 = zip
				10 = country
				11= phone
				12= fax
				13= excel invoice
				14= active
				*/
				
				$action = "update";
			}
		}
		else
			$action = "addNew";
	
		?>
		<script type="text/javascript" src="../scripts/ajax.js"></script>
		
		<p>Enter the detailed company information below.</p>
	
		<form name="clientInfo" action="" method="post">
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Company Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
				    <td class="fieldLabel">Company Name: </td>
				    <td><input name="companyName" type="text" id="companyName" size="30" value="<?php echo($resultSet[1]); ?>" /></td>
					<td width="50">&nbsp;</td>
				    <td class="fieldLabel">Is Active: </td>
				    <td><input name="isActive" type="radio" value="1" id="activeYes" <?php if($resultSet[14] == 1 || $resultSet[14] == "") echo("checked=\"checked\""); ?> />
                        <label for="activeYes">Yes</label>
                        <input name="isActive" type="radio" value="0" id="activeNo" <?php if($resultSet[14] == 0 && $resultSet[14] != "") echo("checked=\"checked\""); ?> />
                        <label for="activeNo">No</label>	</td>
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
				<table border="0" cellspacing="5" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Invoice Contact Name: </td>
					<td><input name="invoiceName" type="text" id="invoiceName" size="30" value="<?php echo($resultSet[3]); ?>" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel" valign="top">Invoice Contact Email: </td>
					<td>
						<textarea name="email" rows="2" cols="28" id="email"><?php echo($resultSet[4]); ?></textarea>
						<!-- <input name="email" type="text" id="email" value="" size="30" />-->
					</td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 1: </td>
				    <td><input name="street1" type="text" id="street1" size="30" value="<?php echo($resultSet[5]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 2: </td>
				    <td><input name="street2" type="text" id="street2" size="30" value="<?php echo($resultSet[6]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">City:</td>
				    <td><input name="city" type="text" id="city" size="20" value="<?php echo($resultSet[7]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">State:</td>
				    <td><input name="state" type="text" id="state" size="10" maxlength="40" value="<?php echo($resultSet[8]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Zip:</td>
				    <td><input name="zip" type="text" id="zip" size="5" maxlength="5" value="<?php echo($resultSet[9]); ?>" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Country:</td>
				    <td><input type="text" name="country" size="20" id="country" value="<?php echo($resultSet[10]); ?>" />
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Phone:</td>
				    <td><input name="phone" type="text" id="phone" value="<?php echo($resultSet[11]); ?>" size="15" maxlength="12" /> 
				    <span class="note">XXX-XXX-XXX</span> </td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="fax" type="text" id="fax" value="<?php echo($resultSet[12]); ?>" size="15" maxlength="12" /> 
				      <span class="note">XXX-XXX-XXX</span> </td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Excel Invoice Format:</td>
				    <td><input name="excelInvoice" type="checkbox" id="excelInvoice" value="1" <?php if($resultSet[13] == 1) echo("checked=\"checked\""); ?> />
			        <label for="excelInvoice">include XLS attachment</label></td>
			      </tr>
				</table>
			</div>
		</div>
        
        <div class="fieldGroup groupHalf">
			<div class="groupHeader">
				Traders
			</div>
			<div class="groupContent">
            	<?php
					$traders = "SELECT pkClientID,fldTraderName FROM $tblClient WHERE fkCompanyID=".$_GET["companyID"]." AND fldActive=1";
					//echo($traders."<br>");
					$resultTraders = mysqli_query($_SESSION['db'],$traders);
					@$countTraders = mysqli_num_rows($resultTraders);
					
					if($countTraders > 0) {
						echo("<ul>");
						
						while($tradersSet = mysqli_fetch_row($resultTraders)){
							/*
							0 = pkClientID
							1 = trader name
							*/
							
							echo("<li><a href=\"javascript:void(0);\" onclick=\"launchClientNested($tradersSet[0]);\">".$tradersSet[1]."</a></li>");						
						}
						echo("</ul>");
					}
				?>
            </div>		
		</div>
		
		<div class="clear"></div>

		
		
		<div>
			<div class="floatLeft">
				<input type="button" name="cancel" value="Cancel" onclick="confirmCancel('cancel this transaction');" class="buttonRed" />
			</div>
			<div class="floatRight">
				<?php				
				if($_GET["companyID"] != "" && $_GET["companyID"] != "undefined"){
					echo("<input type=\"submit\" name=\"submitForm\" value=\"Update &gt;\" class=\"buttonGreen\" /> \n");
					echo("<input type=\"hidden\" name=\"companyID\" value=\"".$_GET["companyID"]."\" />\n");
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
	//}


	require_once("../util/popupBottom.php");
	?>