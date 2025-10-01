<?php
	// Handle trader addition first to avoid HTML output
	if($_POST["action"] == "addTrader"){
		@session_start();
		require_once("../functions.php");
		require_once("../vars.inc.php");
		
		$companyID = $_POST["companyID"];
		
		// Debug: Log what we received
		error_log("Company ID received: " . $companyID);
		error_log("Company name received: " . $_POST["companyName"]);
		error_log("All POST data: " . print_r($_POST, true));
		
		// Check if company exists, if not create it first
		if(empty($companyID) || $companyID == "0" || $companyID == "" || $companyID == "null") {
			// Company doesn't exist, create it first
			$companyQuery = "INSERT INTO $tblCompany (pkCompanyID, fldCompanyName, fldInvoiceName, fldEmail, fldStreet1, fldStreet2, fldCity, fldState, fldZip, fldCountry, fldPhone, fldFax, fldActive) VALUES (\"\",\"".$_POST["companyName"]."\",\"".$_POST["invoiceName"]."\",\"".$_POST["companyEmail"]."\",\"".$_POST["street1"]."\",\"".$_POST["street2"]."\",\"".$_POST["city"]."\",\"".$_POST["state"]."\",\"".$_POST["zip"]."\",\"".$_POST["country"]."\",\"".$_POST["phone"]."\",\"".$_POST["fax"]."\",\"1\")";
			
			$companyResult = mysqli_query($_SESSION['db'], $companyQuery);
			if($companyResult) {
				$companyID = mysqli_insert_id($_SESSION['db']); // Get the new company ID
				echo("Company created and trader added successfully. Company ID: " . $companyID);
			} else {
				echo("Error creating company: " . mysqli_error($_SESSION['db']));
				exit;
			}
		}
		
		// Now add the trader to the company
		$query = "INSERT INTO $tblClient (pkClientID, fldTraderName, fldEmail, fldPhone, fkCompanyID, fldActive) VALUES (\"\",\"".$_POST["traderName"]."\",\"".$_POST["traderEmail"]."\",\"".$_POST["traderPhone"]."\",\"".$companyID."\",\"".$_POST["traderActive"]."\")";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result) {
			if(empty($_POST["companyID"]) || $_POST["companyID"] == "0" || $_POST["companyID"] == "") {
				echo("Company created and trader added successfully. Company ID: " . $companyID);
			} else {
				echo("Trader added successfully");
			}
			exit;
		} else {
			echo("Error adding trader: " . mysqli_error($_SESSION['db']));
			exit;
		}
	}
	
	require_once("../util/popupHeader.php");
	?>
	
	<link rel="stylesheet" type="text/css" href="../css/styles.css">
    <script language="javascript" type="text/javascript" src="../scripts/cookie.js"></script>
 
	<h2>Company Information</h2>
	
	<?php
	
	if($_POST["action"] == "addNew"){
		//add new client
		$query = "INSERT INTO $tblCompany VALUES(\"\",\"".$_POST["companyName"]."\",\"\",\"".$_POST["invoiceName"]."\",\"".$_POST["email"]."\",
			\"".$_POST["street1"]."\",\"".$_POST["street2"]."\",\"".$_POST["city"]."\",\"".$_POST["state"]."\",
			\"".$_POST["zip"]."\",\"".$_POST["country"]."\",\"".$_POST["phone"]."\",\"".$_POST["fax"]."\",\"".$_POST["excelInvoice"]."\",'1')";
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		if($result)
			$step = "confirm";
	}
	else if($_POST["action"] == "update"){
		//update client		
		$query = "UPDATE $tblCompany SET fldCompanyName=\"".$_POST["companyName"]."\", fldInvoiceName=\"".$_POST["invoiceName"]."\", fldInvoiceEmail=\"".$_POST["email"]."\", fldStreet1=\"".$_POST["street1"]."\"";
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
	
		<form name="clientInfo" action="" method="post" onsubmit="prepareFormData()">
		
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
				    <td class="fieldLabel">Country:</td>
				    <td>
						<select name="country" id="country" onchange="updateStateOptions()">
							<option value="">Select Country</option>
							<option value="US" <?php if($resultSet[10] == "US") echo("selected"); ?>>United States</option>
							<option value="CA" <?php if($resultSet[10] == "CA") echo("selected"); ?>>Canada</option>
							<option value="GB" <?php if($resultSet[10] == "GB") echo("selected"); ?>>United Kingdom</option>
							<option value="CH" <?php if($resultSet[10] == "CH") echo("selected"); ?>>Switzerland</option>
							<option value="SG" <?php if($resultSet[10] == "SG") echo("selected"); ?>>Singapore</option>
							<option value="HK" <?php if($resultSet[10] == "HK") echo("selected"); ?>>Hong Kong</option>
							<option value="JP" <?php if($resultSet[10] == "JP") echo("selected"); ?>>Japan</option>
							<option value="DE" <?php if($resultSet[10] == "DE") echo("selected"); ?>>Germany</option>
							<option value="FR" <?php if($resultSet[10] == "FR") echo("selected"); ?>>France</option>
							<option value="IT" <?php if($resultSet[10] == "IT") echo("selected"); ?>>Italy</option>
							<option value="ES" <?php if($resultSet[10] == "ES") echo("selected"); ?>>Spain</option>
							<option value="NL" <?php if($resultSet[10] == "NL") echo("selected"); ?>>Netherlands</option>
							<option value="BE" <?php if($resultSet[10] == "BE") echo("selected"); ?>>Belgium</option>
							<option value="AT" <?php if($resultSet[10] == "AT") echo("selected"); ?>>Austria</option>
							<option value="SE" <?php if($resultSet[10] == "SE") echo("selected"); ?>>Sweden</option>
							<option value="NO" <?php if($resultSet[10] == "NO") echo("selected"); ?>>Norway</option>
							<option value="DK" <?php if($resultSet[10] == "DK") echo("selected"); ?>>Denmark</option>
							<option value="FI" <?php if($resultSet[10] == "FI") echo("selected"); ?>>Finland</option>
							<option value="IE" <?php if($resultSet[10] == "IE") echo("selected"); ?>>Ireland</option>
							<option value="LU" <?php if($resultSet[10] == "LU") echo("selected"); ?>>Luxembourg</option>
							<option value="AU" <?php if($resultSet[10] == "AU") echo("selected"); ?>>Australia</option>
							<option value="NZ" <?php if($resultSet[10] == "NZ") echo("selected"); ?>>New Zealand</option>
							<option value="BR" <?php if($resultSet[10] == "BR") echo("selected"); ?>>Brazil</option>
							<option value="MX" <?php if($resultSet[10] == "MX") echo("selected"); ?>>Mexico</option>
							<option value="AR" <?php if($resultSet[10] == "AR") echo("selected"); ?>>Argentina</option>
							<option value="CL" <?php if($resultSet[10] == "CL") echo("selected"); ?>>Chile</option>
							<option value="CN" <?php if($resultSet[10] == "CN") echo("selected"); ?>>China</option>
							<option value="IN" <?php if($resultSet[10] == "IN") echo("selected"); ?>>India</option>
							<option value="KR" <?php if($resultSet[10] == "KR") echo("selected"); ?>>South Korea</option>
							<option value="TW" <?php if($resultSet[10] == "TW") echo("selected"); ?>>Taiwan</option>
							<option value="TH" <?php if($resultSet[10] == "TH") echo("selected"); ?>>Thailand</option>
							<option value="MY" <?php if($resultSet[10] == "MY") echo("selected"); ?>>Malaysia</option>
							<option value="ID" <?php if($resultSet[10] == "ID") echo("selected"); ?>>Indonesia</option>
							<option value="PH" <?php if($resultSet[10] == "PH") echo("selected"); ?>>Philippines</option>
							<option value="ZA" <?php if($resultSet[10] == "ZA") echo("selected"); ?>>South Africa</option>
							<option value="AE" <?php if($resultSet[10] == "AE") echo("selected"); ?>>United Arab Emirates</option>
							<option value="SA" <?php if($resultSet[10] == "SA") echo("selected"); ?>>Saudi Arabia</option>
							<option value="IL" <?php if($resultSet[10] == "IL") echo("selected"); ?>>Israel</option>
							<option value="RU" <?php if($resultSet[10] == "RU") echo("selected"); ?>>Russia</option>
							<option value="TR" <?php if($resultSet[10] == "TR") echo("selected"); ?>>Turkey</option>
							<option value="OTHER" <?php if($resultSet[10] != "" && !in_array($resultSet[10], ["US","CA","GB","CH","SG","HK","JP","DE","FR","IT","ES","NL","BE","AT","SE","NO","DK","FI","IE","LU","AU","NZ","BR","MX","AR","CL","CN","IN","KR","TW","TH","MY","ID","PH","ZA","AE","SA","IL","RU","TR"])) echo("selected"); ?>>Other</option>
						</select>
						<input name="countryText" type="text" id="countryText" size="20" value="<?php if($resultSet[10] != "" && !in_array($resultSet[10], ["US","CA","GB","DE","FR","IT","ES","NL","AU"])) echo($resultSet[10]); ?>" style="display:none;" placeholder="Enter country" />
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 1: </td>
				    <td><input name="street1" type="text" id="street1" size="30" value="<?php echo($resultSet[5]); ?>" oninput="validateCountryRequirement();" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Street Address 2: </td>
				    <td><input name="street2" type="text" id="street2" size="30" value="<?php echo($resultSet[6]); ?>" oninput="validateCountryRequirement();" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">City:</td>
				    <td><input name="city" type="text" id="city" size="20" value="<?php echo($resultSet[7]); ?>" oninput="validateCountryRequirement();" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">State/Province:</td>
				    <td>
						<select name="state" id="state" onchange="updateStateField(); validateCountryRequirement();">
							<option value="">Select State/Province</option>
						</select>
						<input name="stateText" type="text" id="stateText" size="10" maxlength="40" value="<?php echo($resultSet[8]); ?>" style="display:none;" placeholder="Enter state/province" oninput="clearStateValidation(); validateCountryRequirement();" />
					</td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Zip/Postal Code:</td>
				    <td><input name="zip" type="text" id="zip" size="10" maxlength="10" value="<?php echo($resultSet[9]); ?>" oninput="validateCountryRequirement();" /></td>
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
				
				<!-- Add New Trader Section -->
				<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
					<h4 style="margin: 0 0 10px 0; color: #333;">Add New Trader</h4>
					<?php if(empty($_GET["companyID"]) || $_GET["companyID"] == "0" || $_GET["companyID"] == ""): ?>
						<div style="padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 10px;">
							<p style="margin: 0; color: #6c757d; font-size: 14px;">
								<strong>Note:</strong> You must save the company information first before adding traders.
							</p>
						</div>
					<?php endif; ?>
					<div id="addTraderForm">
						<table border="0" cellspacing="5" cellpadding="0" style="width: 100%;">
							<tr>
								<td class="fieldLabel" style="width: 30%;">Trader Name:</td>
								<td><input name="traderName" type="text" id="traderName" size="25" /></td>
							</tr>
							<tr>
								<td class="fieldLabel">Email:</td>
								<td><input name="traderEmail" type="email" id="traderEmail" size="25" /></td>
							</tr>
							<tr>
								<td class="fieldLabel">Phone:</td>
								<td><input name="traderPhone" type="text" id="traderPhone" size="15" /></td>
							</tr>
							<tr>
								<td class="fieldLabel">Is Active:</td>
								<td>
									<input name="traderActive" type="radio" value="1" id="traderActiveYes" checked />
									<label for="traderActiveYes">Yes</label>
									<input name="traderActive" type="radio" value="0" id="traderActiveNo" />
									<label for="traderActiveNo">No</label>
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: right; padding-top: 10px;">
									<input type="button" name="cancelTrader" value="Cancel" onclick="cancelAddTrader()" class="buttonRed" style="margin-right: 5px;" />
									<?php 
									// Debug: Log the companyID value
									error_log("Company Detail - companyID from GET: " . ($_GET["companyID"] ?? 'not set'));
									error_log("Company Detail - companyID empty check: " . (empty($_GET["companyID"]) ? 'true' : 'false'));
									?>
									<?php if(empty($_GET["companyID"]) || $_GET["companyID"] == "0" || $_GET["companyID"] == ""): ?>
										<input type="button" name="submitTrader" value="Add Trader" onclick="alert('Please save the company information first before adding traders.'); return false;" class="buttonGreen" disabled style="opacity: 0.6; cursor: not-allowed;" />
									<?php else: ?>
										<input type="button" name="submitTrader" value="Add Trader" onclick="addTrader(); return false;" class="buttonGreen" />
									<?php endif; ?>
								</td>
							</tr>
						</table>
					</div>
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


		<script type="text/javascript">
		console.log('Script loaded - JavaScript is working!');
		
		// State/Province data for different countries
		const stateData = {
			'US': [
				{code: 'AL', name: 'Alabama'}, {code: 'AK', name: 'Alaska'}, {code: 'AZ', name: 'Arizona'}, {code: 'AR', name: 'Arkansas'},
				{code: 'CA', name: 'California'}, {code: 'CO', name: 'Colorado'}, {code: 'CT', name: 'Connecticut'}, {code: 'DE', name: 'Delaware'},
				{code: 'FL', name: 'Florida'}, {code: 'GA', name: 'Georgia'}, {code: 'HI', name: 'Hawaii'}, {code: 'ID', name: 'Idaho'},
				{code: 'IL', name: 'Illinois'}, {code: 'IN', name: 'Indiana'}, {code: 'IA', name: 'Iowa'}, {code: 'KS', name: 'Kansas'},
				{code: 'KY', name: 'Kentucky'}, {code: 'LA', name: 'Louisiana'}, {code: 'ME', name: 'Maine'}, {code: 'MD', name: 'Maryland'},
				{code: 'MA', name: 'Massachusetts'}, {code: 'MI', name: 'Michigan'}, {code: 'MN', name: 'Minnesota'}, {code: 'MS', name: 'Mississippi'},
				{code: 'MO', name: 'Missouri'}, {code: 'MT', name: 'Montana'}, {code: 'NE', name: 'Nebraska'}, {code: 'NV', name: 'Nevada'},
				{code: 'NH', name: 'New Hampshire'}, {code: 'NJ', name: 'New Jersey'}, {code: 'NM', name: 'New Mexico'}, {code: 'NY', name: 'New York'},
				{code: 'NC', name: 'North Carolina'}, {code: 'ND', name: 'North Dakota'}, {code: 'OH', name: 'Ohio'}, {code: 'OK', name: 'Oklahoma'},
				{code: 'OR', name: 'Oregon'}, {code: 'PA', name: 'Pennsylvania'}, {code: 'RI', name: 'Rhode Island'}, {code: 'SC', name: 'South Carolina'},
				{code: 'SD', name: 'South Dakota'}, {code: 'TN', name: 'Tennessee'}, {code: 'TX', name: 'Texas'}, {code: 'UT', name: 'Utah'},
				{code: 'VT', name: 'Vermont'}, {code: 'VA', name: 'Virginia'}, {code: 'WA', name: 'Washington'}, {code: 'WV', name: 'West Virginia'},
				{code: 'WI', name: 'Wisconsin'}, {code: 'WY', name: 'Wyoming'}, {code: 'DC', name: 'District of Columbia'}
			],
			'CA': [
				{code: 'AB', name: 'Alberta'}, {code: 'BC', name: 'British Columbia'}, {code: 'MB', name: 'Manitoba'}, {code: 'NB', name: 'New Brunswick'},
				{code: 'NL', name: 'Newfoundland and Labrador'}, {code: 'NS', name: 'Nova Scotia'}, {code: 'ON', name: 'Ontario'}, {code: 'PE', name: 'Prince Edward Island'},
				{code: 'QC', name: 'Quebec'}, {code: 'SK', name: 'Saskatchewan'}, {code: 'NT', name: 'Northwest Territories'}, {code: 'NU', name: 'Nunavut'}, {code: 'YT', name: 'Yukon'}
			],
			'GB': [
				{code: 'ENG', name: 'England'}, {code: 'SCT', name: 'Scotland'}, {code: 'WLS', name: 'Wales'}, {code: 'NIR', name: 'Northern Ireland'}
			],
			'DE': [
				{code: 'BW', name: 'Baden-Württemberg'}, {code: 'BY', name: 'Bavaria'}, {code: 'BE', name: 'Berlin'}, {code: 'BB', name: 'Brandenburg'},
				{code: 'HB', name: 'Bremen'}, {code: 'HH', name: 'Hamburg'}, {code: 'HE', name: 'Hesse'}, {code: 'MV', name: 'Mecklenburg-Vorpommern'},
				{code: 'NI', name: 'Lower Saxony'}, {code: 'NW', name: 'North Rhine-Westphalia'}, {code: 'RP', name: 'Rhineland-Palatinate'}, {code: 'SL', name: 'Saarland'},
				{code: 'SN', name: 'Saxony'}, {code: 'ST', name: 'Saxony-Anhalt'}, {code: 'SH', name: 'Schleswig-Holstein'}, {code: 'TH', name: 'Thuringia'}
			],
			'FR': [
				{code: 'ARA', name: 'Auvergne-Rhône-Alpes'}, {code: 'BFC', name: 'Bourgogne-Franche-Comté'}, {code: 'BRE', name: 'Brittany'}, {code: 'CVL', name: 'Centre-Val de Loire'},
				{code: 'COR', name: 'Corsica'}, {code: 'GES', name: 'Grand Est'}, {code: 'HDF', name: 'Hauts-de-France'}, {code: 'IDF', name: 'Île-de-France'},
				{code: 'NOR', name: 'Normandy'}, {code: 'NAQ', name: 'Nouvelle-Aquitaine'}, {code: 'OCC', name: 'Occitanie'}, {code: 'PDL', name: 'Pays de la Loire'},
				{code: 'PAC', name: 'Provence-Alpes-Côte d\'Azur'}
			],
			'IT': [
				{code: 'ABR', name: 'Abruzzo'}, {code: 'BAS', name: 'Basilicata'}, {code: 'CAL', name: 'Calabria'}, {code: 'CAM', name: 'Campania'},
				{code: 'EMR', name: 'Emilia-Romagna'}, {code: 'FVG', name: 'Friuli-Venezia Giulia'}, {code: 'LAZ', name: 'Lazio'}, {code: 'LIG', name: 'Liguria'},
				{code: 'LOM', name: 'Lombardy'}, {code: 'MAR', name: 'Marche'}, {code: 'MOL', name: 'Molise'}, {code: 'PAB', name: 'Piedmont'},
				{code: 'PUG', name: 'Apulia'}, {code: 'SAR', name: 'Sardinia'}, {code: 'SIC', name: 'Sicily'}, {code: 'TOS', name: 'Tuscany'},
				{code: 'TAA', name: 'Trentino-Alto Adige'}, {code: 'UMB', name: 'Umbria'}, {code: 'VEN', name: 'Veneto'}, {code: 'VDA', name: 'Aosta Valley'}
			],
			'ES': [
				{code: 'AN', name: 'Andalusia'}, {code: 'AR', name: 'Aragon'}, {code: 'AS', name: 'Asturias'}, {code: 'IB', name: 'Balearic Islands'},
				{code: 'CN', name: 'Canary Islands'}, {code: 'CB', name: 'Cantabria'}, {code: 'CL', name: 'Castile and León'}, {code: 'CM', name: 'Castilla-La Mancha'},
				{code: 'CT', name: 'Catalonia'}, {code: 'CE', name: 'Ceuta'}, {code: 'EX', name: 'Extremadura'}, {code: 'GA', name: 'Galicia'},
				{code: 'MD', name: 'Madrid'}, {code: 'ML', name: 'Melilla'}, {code: 'MC', name: 'Murcia'}, {code: 'NC', name: 'Navarre'},
				{code: 'PV', name: 'Basque Country'}, {code: 'RI', name: 'La Rioja'}, {code: 'VC', name: 'Valencian Community'}
			],
			'NL': [
				{code: 'DR', name: 'Drenthe'}, {code: 'FL', name: 'Flevoland'}, {code: 'FR', name: 'Friesland'}, {code: 'GE', name: 'Gelderland'},
				{code: 'GR', name: 'Groningen'}, {code: 'LI', name: 'Limburg'}, {code: 'NB', name: 'North Brabant'}, {code: 'NH', name: 'North Holland'},
				{code: 'OV', name: 'Overijssel'}, {code: 'UT', name: 'Utrecht'}, {code: 'ZE', name: 'Zeeland'}, {code: 'ZH', name: 'South Holland'}
			],
			'AU': [
				{code: 'NSW', name: 'New South Wales'}, {code: 'VIC', name: 'Victoria'}, {code: 'QLD', name: 'Queensland'}, {code: 'WA', name: 'Western Australia'},
				{code: 'SA', name: 'South Australia'}, {code: 'TAS', name: 'Tasmania'}, {code: 'ACT', name: 'Australian Capital Territory'}, {code: 'NT', name: 'Northern Territory'}
			],
			'CH': [
				{code: 'ZH', name: 'Zurich'}, {code: 'BE', name: 'Bern'}, {code: 'LU', name: 'Lucerne'}, {code: 'UR', name: 'Uri'}, {code: 'SZ', name: 'Schwyz'},
				{code: 'OW', name: 'Obwalden'}, {code: 'NW', name: 'Nidwalden'}, {code: 'GL', name: 'Glarus'}, {code: 'ZG', name: 'Zug'}, {code: 'FR', name: 'Fribourg'},
				{code: 'SO', name: 'Solothurn'}, {code: 'BS', name: 'Basel-Stadt'}, {code: 'BL', name: 'Basel-Landschaft'}, {code: 'SH', name: 'Schaffhausen'},
				{code: 'AR', name: 'Appenzell Ausserrhoden'}, {code: 'AI', name: 'Appenzell Innerrhoden'}, {code: 'SG', name: 'St. Gallen'},
				{code: 'GR', name: 'Graubünden'}, {code: 'AG', name: 'Aargau'}, {code: 'TG', name: 'Thurgau'}, {code: 'TI', name: 'Ticino'},
				{code: 'VD', name: 'Vaud'}, {code: 'VS', name: 'Valais'}, {code: 'NE', name: 'Neuchâtel'}, {code: 'GE', name: 'Geneva'}, {code: 'JU', name: 'Jura'}
			],
			'SG': [
				{code: 'SG', name: 'Singapore'}
			],
			'HK': [
				{code: 'HK', name: 'Hong Kong'}
			],
			'JP': [
				{code: 'HOK', name: 'Hokkaido'}, {code: 'TOH', name: 'Tohoku'}, {code: 'KAN', name: 'Kanto'}, {code: 'CHU', name: 'Chubu'},
				{code: 'KAN', name: 'Kansai'}, {code: 'CHU', name: 'Chugoku'}, {code: 'SHI', name: 'Shikoku'}, {code: 'KYU', name: 'Kyushu'}
			],
			'BE': [
				{code: 'BRU', name: 'Brussels'}, {code: 'VAN', name: 'Antwerp'}, {code: 'WAL', name: 'Wallonia'}, {code: 'VLA', name: 'Flanders'}
			],
			'AT': [
				{code: 'B', name: 'Burgenland'}, {code: 'K', name: 'Carinthia'}, {code: 'NÖ', name: 'Lower Austria'}, {code: 'OÖ', name: 'Upper Austria'},
				{code: 'S', name: 'Salzburg'}, {code: 'ST', name: 'Styria'}, {code: 'T', name: 'Tyrol'}, {code: 'V', name: 'Vorarlberg'}, {code: 'W', name: 'Vienna'}
			],
			'SE': [
				{code: 'AB', name: 'Stockholm'}, {code: 'AC', name: 'Västerbotten'}, {code: 'BD', name: 'Norrbotten'}, {code: 'C', name: 'Uppsala'},
				{code: 'D', name: 'Södermanland'}, {code: 'E', name: 'Östergötland'}, {code: 'F', name: 'Jönköping'}, {code: 'G', name: 'Kronoberg'},
				{code: 'H', name: 'Kalmar'}, {code: 'I', name: 'Gotland'}, {code: 'K', name: 'Blekinge'}, {code: 'M', name: 'Skåne'},
				{code: 'N', name: 'Halland'}, {code: 'O', name: 'Västra Götaland'}, {code: 'S', name: 'Värmland'}, {code: 'T', name: 'Örebro'},
				{code: 'U', name: 'Västmanland'}, {code: 'W', name: 'Dalarna'}, {code: 'X', name: 'Gävleborg'}, {code: 'Y', name: 'Västernorrland'},
				{code: 'Z', name: 'Jämtland'}
			],
			'NO': [
				{code: 'OS', name: 'Oslo'}, {code: 'AG', name: 'Agder'}, {code: 'IN', name: 'Innlandet'}, {code: 'MO', name: 'Møre og Romsdal'},
				{code: 'NO', name: 'Nordland'}, {code: 'RO', name: 'Rogaland'}, {code: 'TR', name: 'Trøndelag'}, {code: 'VE', name: 'Vestland'},
				{code: 'VI', name: 'Vestfold og Telemark'}, {code: 'TF', name: 'Troms og Finnmark'}, {code: 'VA', name: 'Viken'}
			],
			'DK': [
				{code: 'H', name: 'Capital Region'}, {code: 'M', name: 'Central Jutland'}, {code: 'N', name: 'North Jutland'}, {code: 'S', name: 'South Denmark'}, {code: 'Z', name: 'Zealand'}
			],
			'FI': [
				{code: 'FI-01', name: 'Åland'}, {code: 'FI-02', name: 'South Karelia'}, {code: 'FI-03', name: 'Southern Ostrobothnia'}, {code: 'FI-04', name: 'Southern Savonia'},
				{code: 'FI-05', name: 'Kainuu'}, {code: 'FI-06', name: 'Tavastia Proper'}, {code: 'FI-07', name: 'Central Ostrobothnia'}, {code: 'FI-08', name: 'Central Finland'},
				{code: 'FI-09', name: 'Kymenlaakso'}, {code: 'FI-10', name: 'Lapland'}, {code: 'FI-11', name: 'Pirkanmaa'}, {code: 'FI-12', name: 'Ostrobothnia'},
				{code: 'FI-13', name: 'North Karelia'}, {code: 'FI-14', name: 'Northern Ostrobothnia'}, {code: 'FI-15', name: 'Northern Savonia'}, {code: 'FI-16', name: 'Päijänne Tavastia'},
				{code: 'FI-17', name: 'Satakunta'}, {code: 'FI-18', name: 'South Ostrobothnia'}, {code: 'FI-19', name: 'Southwest Finland'}, {code: 'FI-20', name: 'Uusimaa'}
			],
			'IE': [
				{code: 'C', name: 'Connaught'}, {code: 'L', name: 'Leinster'}, {code: 'M', name: 'Munster'}, {code: 'U', name: 'Ulster'}
			],
			'LU': [
				{code: 'L', name: 'Luxembourg'}
			],
			'NZ': [
				{code: 'AUK', name: 'Auckland'}, {code: 'BOP', name: 'Bay of Plenty'}, {code: 'CAN', name: 'Canterbury'}, {code: 'GIS', name: 'Gisborne'},
				{code: 'HKB', name: 'Hawke\'s Bay'}, {code: 'MBH', name: 'Marlborough'}, {code: 'MWT', name: 'Manawatu-Wanganui'}, {code: 'NSN', name: 'Nelson'},
				{code: 'NTL', name: 'Northland'}, {code: 'OTA', name: 'Otago'}, {code: 'STL', name: 'Southland'}, {code: 'TAS', name: 'Tasman'},
				{code: 'TKI', name: 'Taranaki'}, {code: 'WGN', name: 'Wellington'}, {code: 'WTC', name: 'West Coast'}, {code: 'WAI', name: 'Waikato'}
			]
		};

		function updateStateOptions() {
			const countrySelect = document.getElementById('country');
			const stateSelect = document.getElementById('state');
			const stateText = document.getElementById('stateText');
			const countryText = document.getElementById('countryText');
			
			// Clear existing options
			stateSelect.innerHTML = '<option value="">Select State/Province</option>';
			
			// Handle country selection
			if (countrySelect.value === 'OTHER') {
				// Show text input for country
				countryText.style.display = 'inline';
				countryText.required = true;
				countrySelect.style.display = 'none';
				
				// Show text input for state
				stateText.style.display = 'inline';
				stateText.required = true;
				stateSelect.style.display = 'none';
			} else {
				// Hide text inputs
				countryText.style.display = 'none';
				countryText.required = false;
				countrySelect.style.display = 'inline';
				
				if (countrySelect.value && stateData[countrySelect.value]) {
					// Populate state dropdown
					stateData[countrySelect.value].forEach(state => {
						const option = document.createElement('option');
						option.value = state.code;
						option.textContent = state.name;
						stateSelect.appendChild(option);
					});
					stateText.style.display = 'none';
					stateText.required = false;
					stateSelect.style.display = 'inline';
				} else {
					// Show text input for state
					stateText.style.display = 'inline';
					stateText.required = true;
					stateSelect.style.display = 'none';
				}
			}
		}

		function updateStateField() {
			let stateSelect = document.getElementById('state');
			let stateText = document.getElementById('stateText');
			
			if (stateSelect.value) {
				stateText.value = stateSelect.value;
			}
		}

		function prepareFormData() {
			// Ensure the correct state value is submitted
			let stateSelect = document.getElementById('state');
			let stateText = document.getElementById('stateText');
			let countrySelect = document.getElementById('country');
			let countryText = document.getElementById('countryText');
			
			// Use dropdown value if available, otherwise use text input
			if (stateSelect.style.display !== 'none' && stateSelect.value) {
				stateText.value = stateSelect.value;
			}
			
			// Use dropdown value if available, otherwise use text input
			if (countrySelect.style.display !== 'none' && countrySelect.value) {
				countryText.value = countrySelect.value;
			}
			
			// Conditional validation: If any address field is filled, country is required
			let street1Field = document.getElementById('street1');
			let street2Field = document.getElementById('street2');
			let cityField = document.getElementById('city');
			let zipField = document.getElementById('zip');
			
			const street1Value = street1Field.value.trim();
			const street2Value = street2Field.value.trim();
			const cityValue = cityField.value.trim();
			const stateValue = (stateSelect.style.display !== 'none' && stateSelect.value) ? stateSelect.value : stateText.value.trim();
			const zipValue = zipField.value.trim();
			const countryValue = countrySelect.value || countryText.value.trim();
			
			// Check if any address field has content
			const hasAddressInfo = street1Value || street2Value || cityValue || stateValue || zipValue;
			
			// Only validate if any address info is present but country is missing
			if (hasAddressInfo && !countryValue) {
				alert('Please select a country when entering address information.');
				return false;
			}
			
			return true;
		}

		function addTrader() {
			console.log('addTrader function called');
			
			const traderName = document.getElementById('traderName').value;
			const traderEmail = document.getElementById('traderEmail').value;
			const traderPhone = document.getElementById('traderPhone').value;
			const traderActive = document.querySelector('input[name="traderActive"]:checked').value;
			const companyID = '<?php echo($_GET["companyID"]); ?>';
			
			console.log('Trader data:', {traderName, traderEmail, traderPhone, traderActive, companyID});
			console.log('CompanyID type:', typeof companyID);
			console.log('CompanyID length:', companyID.length);
			
			// Check if company exists - only show alert if companyID is truly empty
			if (!companyID || companyID === '' || companyID === '0' || companyID === 'undefined' || companyID === 'null') {
				alert('Please save the company information first before adding traders.');
				return;
			}
			
			if (!traderName.trim()) {
				alert('Please enter a trader name.');
				return;
			}
			
			// Create form data
			const formData = new FormData();
			formData.append('action', 'addTrader');
			formData.append('companyID', companyID);
			formData.append('traderName', traderName);
			formData.append('traderEmail', traderEmail);
			formData.append('traderPhone', traderPhone);
			formData.append('traderActive', traderActive);
			
			// If company doesn't exist, include company form data
			if (!companyID || companyID === '' || companyID === '0') {
				console.log('Company does not exist, collecting company form data...');
				
				const companyName = document.getElementById('companyName').value;
				const invoiceName = document.getElementById('invoiceName').value;
				const companyEmail = document.getElementById('email').value;
				const street1 = document.getElementById('street1').value;
				const street2 = document.getElementById('street2').value;
				const city = document.getElementById('city').value;
				
				// Handle state field (dropdown or text input)
				const stateSelect = document.getElementById('state');
				const stateText = document.getElementById('stateText');
				const stateValue = (stateSelect.style.display !== 'none' && stateSelect.value) ? stateSelect.value : stateText.value;
				
				const zip = document.getElementById('zip').value;
				
				// Handle country field (dropdown or text input)
				const countrySelect = document.getElementById('country');
				const countryText = document.getElementById('countryText');
				const countryValue = (countrySelect.style.display !== 'none' && countrySelect.value) ? countrySelect.value : countryText.value;
				
				const phone = document.getElementById('phone').value;
				const fax = document.getElementById('fax').value;
				
				console.log('Company data collected:', {
					companyName, invoiceName, companyEmail, street1, street2, city, 
					stateValue, zip, countryValue, phone, fax
				});
				
				formData.append('companyName', companyName);
				formData.append('invoiceName', invoiceName);
				formData.append('companyEmail', companyEmail);
				formData.append('street1', street1);
				formData.append('street2', street2);
				formData.append('city', city);
				formData.append('state', stateValue);
				formData.append('zip', zip);
				formData.append('country', countryValue);
				formData.append('phone', phone);
				formData.append('fax', fax);
			}
			
			// Submit via AJAX
			console.log('Submitting trader data...');
			fetch('', {
				method: 'POST',
				body: formData
			})
			.then(response => {
				console.log('Response received:', response);
				return response.text();
			})
			.then(data => {
				console.log('Response data:', data);
				if (data.includes('successfully')) {
					// Clear the form
					document.getElementById('traderName').value = '';
					document.getElementById('traderEmail').value = '';
					document.getElementById('traderPhone').value = '';
					document.getElementById('traderActiveYes').checked = true;
					
					// Show success message
					alert(data);
					
					// Automatically refresh the parent window after alert is dismissed
					setTimeout(() => {
						if (window.opener && !window.opener.closed) {
							window.opener.location.reload();
							// Don't close the popup - keep it open for adding more traders
						} else {
							// If not a popup, just reload the page
							window.location.reload();
						}
					}, 100); // Small delay to ensure alert is processed
				} else {
					alert('Error adding trader: ' + data);
				}
			})
			.catch(error => {
				console.log('Fetch error:', error);
				alert('Error adding trader: ' + error);
			});
		}

		function cancelAddTrader() {
			document.getElementById('addTraderForm').reset();
			document.getElementById('traderActiveYes').checked = true;
		}

		function validateCountryRequirement() {
			let street1Field = document.getElementById('street1');
			let street2Field = document.getElementById('street2');
			let cityField = document.getElementById('city');
			let stateSelect = document.getElementById('state');
			let stateText = document.getElementById('stateText');
			let countrySelect = document.getElementById('country');
			let countryText = document.getElementById('countryText');
			
			// Get current address values
			const street1Value = street1Field.value.trim();
			const street2Value = street2Field.value.trim();
			const cityValue = cityField.value.trim();
			const stateValue = (stateSelect.style.display !== 'none' && stateSelect.value) ? stateSelect.value : stateText.value.trim();
			const zipValue = zipField.value.trim();
			const countryValue = (countrySelect.style.display !== 'none' && countrySelect.value) ? countrySelect.value : countryText.value.trim();
			
			// Check if any address field has meaningful content (not just whitespace or empty)
			const hasAddressInfo = (street1Value && street1Value.length > 0) || 
								  (street2Value && street2Value.length > 0) || 
								  (cityValue && cityValue.length > 0) || 
								  (stateValue && stateValue.length > 0) || 
								  (zipValue && zipValue.length > 0);
			
			console.log('validateCountryRequirement called:', {
				street1Value, street2Value, cityValue, stateValue, zipValue, countryValue,
				hasAddressInfo
			});
			
			// If any address info is filled but country is not, make country required
			if (hasAddressInfo && (!countryValue || countryValue.length === 0)) {
				countrySelect.required = true;
				countryText.required = true;
				// Add visual indicator
				countrySelect.style.borderColor = '#ff6b6b';
				countryText.style.borderColor = '#ff6b6b';
				console.log('Setting country as required');
			} else {
				countrySelect.required = false;
				countryText.required = false;
				// Remove visual indicator
				countrySelect.style.borderColor = '';
				countryText.style.borderColor = '';
				console.log('Country not required');
			}
			
			// Ensure zip field is never required
			const zipField = document.getElementById('zip');
			if (zipField) {
				zipField.required = false;
			}
		}

		function clearStateValidation() {
			let stateText = document.getElementById('stateText');
			let stateSelect = document.getElementById('state');
			
			// Clear any validation messages
			stateText.setCustomValidity('');
			stateSelect.setCustomValidity('');
			
			// Remove any visual indicators
			stateText.style.borderColor = '';
			stateSelect.style.borderColor = '';
		}

		// Initialize on page load
		document.addEventListener('DOMContentLoaded', function() {
			console.log('DOMContentLoaded - JavaScript is running!');
			updateStateOptions();
			
			// Test validation on page load
			console.log('Testing validation on page load...');
			validateCountryRequirement();
			
			// Set initial state value if editing existing company
			const currentState = '<?php echo($resultSet[8]); ?>';
			const currentCountry = '<?php echo($resultSet[10]); ?>';
			
			if (currentState && currentCountry && stateData[currentCountry]) {
				// Find matching state in dropdown
				let stateSelect = document.getElementById('state');
				for (let option of stateSelect.options) {
					if (option.value === currentState) {
						option.selected = true;
						updateStateField();
						break;
					}
				}
			}
		});
		</script>

	<?php
	//}


	require_once("../util/popupBottom.php");
	?>