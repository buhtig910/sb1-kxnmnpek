<?php
session_start();

require_once("../vars.inc.php");
require_once("../functions.php");


if(isset($_POST["action"]))
	$action = $_POST["action"];
else
	$action = $_GET["action"];

if(isset($_POST["viewState"]))
	$viewState = $_POST["viewState"];
else
	$viewState = $_GET["viewState"];

if(isset($_POST["addBroker"]))
	$addBroker = $_POST["addBroker"];
else
	$addBroker = $_GET["addBroker"];

$block = $_POST["block"];
$blocks = $_POST["blocks"];

if(isset($_POST["brokerCount"]))
	$brokerCount = $_POST["brokerCount"];
else
	$brokerCount = $_GET["brokerCount"];
	
$brokerID = $_POST["brokerID"];

if(isset($_POST["brokerType"]))
	$brokerType = $_POST["brokerType"];
else
	$brokerType = $_GET["brokerType"];
	
if(isset($_POST["brokerComm"]))
	$brokerComm = $_POST["brokerComm"];
else
	$brokerComm = $_GET["brokerComm"];

if(isset($_POST["brokerRate"]))
	$brokerRate = $_POST["brokerRate"];
else
	$brokerRate = $_GET["brokerRate"];
	
$blockID = $_POST["blockID"];
$blockRegion = $_POST["blockRegion"];
$buy = $_POST["buy"];
$calculate = $_POST["calculate"];
$clientID = $_POST["clientID"];
$customHours = $_POST["customHours"];
if(isset($_GET["companyID"]))
	$companyID = $_GET["companyID"];
else
	$companyID = $_POST["companyID"];
$dayType = $_POST["dayType"];

if(isset($_GET["endDate"]))
	$endDate = $_GET["endDate"];
else
	$endDate = $_POST["endDate"];
	
$endHour = $_POST["endHour"];
$existing = $_POST["existing"];
$filter = $_POST["filter"];
$freq = $_POST["freq"];
$getBrokers = $_POST["getBrokers"];
if(isset($_GET["getClientList"]))
	$getClientList = $_GET["getClientList"];
else
	$getClientList = $_POST["getClientList"];
$geoRegion = $_POST["geoRegion"];
$hubName = $_GET["hubName"];
$indices = $_GET["indices"];
$indexName = $_POST["indexName"];
$indexType = $_POST["indexType"];
$locationID = $_POST["locationID"];
$param = $_POST["param"];
$qty = $_POST["qty"];
$region = $_POST["region"];
$reportName = $_GET["reportName"];
if(isset($_POST["resendConfirm"]))
	$resendConfirm = $_POST["resendConfirm"];
else
	$resendConfirm = $_GET["resendConfirm"];
if(isset($_POST["resendType"]))
	$resendType = $_POST["resendType"];
else
	$resendType = $_GET["resendType"];
	
if(isset($_POST["selectedBroker"]))
	$selectedBroker = $_POST["selectedBroker"];
else
	$selectedBroker = $_GET["selectedBroker"];
	
if(isset($_POST["sendInvoice"]))
	$sendInvoice = $_POST["sendInvoice"];
else if(isset($_GET["sendInvoice"]))
	$sendInvoice = $_GET["sendInvoice"];

if(isset($_GET["searchCompany"]))
	$searchCompany = $_GET["searchCompany"];
else
	$searchCompany = $_POST["searchCompany"];
if(isset($_GET["searchString"]))
	$searchString = $_GET["searchString"];
else
	$searchString = $_POST["searchString"];
$showClearID = $_POST["showClearID"];
if(isset($_GET["startDate"]))
	$startDate = $_GET["startDate"];
else
	$startDate = $_POST["startDate"];
$startHour = $_POST["startHour"];
$timezone = $_POST["timezone"];
if(isset($_POST["tradeID"]))
	$tradeID = $_POST["tradeID"];
else
	$tradeID = $_GET["tradeID"];
$transactionID = $_POST["transactionID"];
$transID = $_POST["transID"];
$type = $_POST["type"];



if(isset($param)){
	//get company information
	$query = "SELECT fldCompanyName,fldConfirmStreet,fldConfirmStreet2,fldConfirmCity,fldConfirmState,fldConfirmZip,fldConfirmPhone,fldConfirmFax FROM $tblClient WHERE pkClientID=$param";
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count == 1){
		$resultSet = mysqli_fetch_row($result);
		/*
		0 = company anem
		1 = street1
		2 = street2
		3 = city
		4 = state
		5 = zip
		6 = phone
		7 = fax
		*/
		
		$address = $resultSet[1];
		if($resultSet[2] != "")
			$address .= ", " .$resultSet[2];
		$address .= ", ". $resultSet[3];
		$address .= ", ". $resultSet[4];
		$address .= ", ".$resultSet[5];
		
		echo("$resultSet[0]\t\n");
		echo("$address\t\n");
		echo("$resultSet[6]\t\n");
		echo("$resultSet[7]\t\n");
		
	}
}
else if(isset($existing)) { 
	//get existing client information by client ID
	$query = "SELECT * FROM tblB2TClient WHERE pkClientID=".$clientID;
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count == 1){
		$resultSet = mysqli_fetch_row($result);
		for($i=0; $i < sizeof($resultSet); $i++){
			echo($resultSet[$i]."\t\n");
		}
	}
	else{
		echo("no records found");
	}
}
else if(isset($region)){
	
	$query = "SELECT fldRegion,fldTimezone FROM $tblLocation WHERE pkLocationID=$region";
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count == 1){
		$resultSet = mysqli_fetch_row($result);
		/*
		0 = region name
		1 = timezone
		*/
		
		echo("$resultSet[0]\t$resultSet[1]\t$geo");
		
	}
}
else if(isset($_POST["filter"])){
	echo("filter: ".$_SESSION["filters"]);

	if($_POST["filter"] == "close"){
		$_SESSION["filters"] = "none";
	}
	else if($_POST["filter"] == "open"){
		$_SESSION["filters"] = "";
	}
}
else if(isset($_POST["action"]) && $_POST["action"] == "fetchCompanyDetails") {
	// Fetch company details for populating invoice fields
	$companyName = $_POST["companyName"];
	
	if($companyName) {
		$query = "SELECT fldInvoiceName, fldInvoiceEmail, fldStreet1, fldStreet2, fldCity, fldState, fldZip, fldCountry, fldPhone, fldFax FROM tblB2TCompany WHERE fldCompanyName = '" . mysqli_real_escape_string($_SESSION['db'], $companyName) . "'";
		$result = mysqli_query($_SESSION['db'], $query);
		
		if($result && mysqli_num_rows($result) > 0) {
			$companyData = mysqli_fetch_assoc($result);
			
			$response = array(
				'success' => true,
				'invoiceName' => $companyData['fldInvoiceName'],
				'invoiceEmail' => $companyData['fldInvoiceEmail'],
				'street1' => $companyData['fldStreet1'],
				'street2' => $companyData['fldStreet2'],
				'city' => $companyData['fldCity'],
				'state' => $companyData['fldState'],
				'zip' => $companyData['fldZip'],
				'country' => $companyData['fldCountry'],
				'phone' => $companyData['fldPhone'],
				'fax' => $companyData['fldFax']
			);
			
			echo json_encode($response);
		} else {
			echo json_encode(array('success' => false, 'message' => 'Company not found'));
		}
	} else {
		echo json_encode(array('success' => false, 'message' => 'No company name provided'));
	}
}
else if(isset($_POST["action"]) && $_POST["action"] == "fetchCompanyInvoiceInfo") {
	// Fetch company invoice information for populating trade confirm fields
	$companyName = $_POST["companyName"];
	
	if($companyName) {
		$query = "SELECT fldInvoiceName, fldInvoiceEmail, fldStreet1, fldStreet2, fldCity, fldState, fldZip, fldCountry, fldPhone, fldFax FROM tblB2TCompany WHERE fldCompanyName = '" . mysqli_real_escape_string($_SESSION['db'], $companyName) . "'";
		$result = mysqli_query($_SESSION['db'], $query);
		
		if($result && mysqli_num_rows($result) > 0) {
			$companyData = mysqli_fetch_assoc($result);
			
			$response = array(
				'success' => true,
				'invoiceContactName' => $companyData['fldInvoiceName'],
				'invoiceContactEmail' => $companyData['fldInvoiceEmail'],
				'street1' => $companyData['fldStreet1'],
				'street2' => $companyData['fldStreet2'],
				'city' => $companyData['fldCity'],
				'state' => $companyData['fldState'],
				'zip' => $companyData['fldZip'],
				'country' => $companyData['fldCountry'],
				'phone' => $companyData['fldPhone'],
				'fax' => $companyData['fldFax']
			);
			
			echo json_encode($response);
		} else {
			echo json_encode(array('success' => false, 'error' => 'Company not found'));
		}
	} else {
		echo json_encode(array('success' => false, 'error' => 'No company name provided'));
	}
}
else if(isset($calculate)){
	//need to finalize logic when block is not selected
		
	if($block == 1) {
		//peak hours
		$queryColumns = "fldPeakHours";
	}
	else if($block == 2) { 
		//off-peak hours
		$queryColumns = "fldOffPeakHours";
	}
	else if($block == 3) {
		//night hours
		$queryColumns = "fldNightHours";
	}
	else if($block == 4) {
		//weekend
		$queryColumns = "fldWeekend2x16";
	}
	else if($block == 5) {
		//RTC hours
		$queryColumns = "fldRTCHours";
	}
	else if($block == 6){
		//custom hours
		$queryColumns = buildColumns($startHour,$endHour);
		//$queryColumns2 = buildColumns2($startDay,$endDay);
		$queryColumns2 = buildColumns3($dayType);
	}
	else if($block == 7) {
		//west peak - Mon-Sat 07-22 non-nerc
		//$queryColumns = buildColumns($startHour,$endHour);
		$queryColumns = buildColumns2(2,7);
		$queryColumns2 = buildColumns3($dayType);
	}
	else if($block == 8) {
		//west off-peak - Mon-Sat 01-06, 23-24, Sun 01-24 nerc
		//$queryColumns = buildColumns($startHour,$endHour);
		$queryColumns = buildColumns2a(2,7);
		$queryColumns_Sun = buildColumns2a(1,7);
		//echo($queryColumns."<br>");
		//echo($queryColumns_Sun."<br>");
		$queryColumns2 = buildColumns3(5);
		$queryColumns3 = buildColumns3(4);
	}
	
	if($block == 6){
		//$query = "SELECT ".$queryColumns." FROM $tblHours WHERE ".$queryColumns2." AND fldDate>='".reverseDate($startDate)."' AND fldDate<='".reverseDate($endDate)."'";
		$query = "SELECT count(*) FROM $tblHours WHERE ".$queryColumns2." AND fldDate>='".reverseDate($startDate)."' AND fldDate<='".reverseDate($endDate)."'";
	}
	else if($block == 7) {
		$query = "SELECT sum(".$queryColumns.") FROM $tblHours WHERE ".$queryColumns2." AND fldDate>='".reverseDate($startDate)."' AND fldDate<='".reverseDate($endDate)."'";
	}
	else if($block == 8) {
		$query = "SELECT sum(".$queryColumns."),sum(fldDLSavBeg),sum(fldDLSavEnd) FROM $tblHours WHERE ".$queryColumns2." AND fldDate>='".reverseDate($startDate)."' AND fldDate<='".reverseDate($endDate)."'";
		$query2 = "SELECT sum(".$queryColumns_Sun."),sum(fldDLSavBeg),sum(fldDLSavEnd) FROM $tblHours WHERE ".$queryColumns3." AND fldDate>='".reverseDate($startDate)."' AND fldDate<='".reverseDate($endDate)."'";
	}
	else {
		$query = "SELECT sum(".$queryColumns.") FROM $tblHours WHERE fldDate>='".reverseDate($startDate)."' AND fldDate<='".reverseDate($endDate)."'";
	}
	
	//echo($query."<br>");
	//echo($query2."<br>");
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_num_rows($result);
	
	if($count == 1){
		//power trade logic
		if($block == 6){
			$totalAmt = 0;
			$i = 0;
			
			list($dayCount) = mysqli_fetch_row($result);
			$hr = (($endHour - $startHour)/100) + 1; //plus 1 to include end hr
			$totalAmt = $hr * $dayCount;
		}
		else if($block == 7 || $block == 8){
			$totalAmt = 0;
			$i = 0;
			
			list($dayCount,$daylightBegin,$daylightEnd) = mysqli_fetch_row($result);

			//echo($startHour[0].",".$startHour[1].",".$startHour[2]."<br>");

			/*$startHr = explode(",",$startHour);
			$endHr = explode(",",$endHour);*/

			if($block == 7)
				$hr = (($endHour - $startHour)/100) + 1; //plus 1 to include end hr
			else if($block == 8)
				$hr = ((($endHour[0] - $startHour[0])+($endHour[1] - $startHour[1]))/100) + 2; //plus 1 to include end hr
			//echo("hrs: ".$hr."<br>");
			$totalAmt = $hr * $dayCount;
			//echo("weekday total: $totalAmt<br>");
			
			if($block == 8) {
				$result2 = mysqli_query($_SESSION['db'],$query2);
				list($sundayCount,$daylightBegin2,$daylightEnd2) = mysqli_fetch_row($result2);
				$totalAmt+= ($sundayCount * 24);
			}
			
			$dlBegin = $daylightBegin + $daylightBegin2; //daylight savings start adjustment
			$dlEnd = $daylightEnd + $daylightEnd2; //daylight savings end adjustment
			$totalAmt = $totalAmt - $dlBegin + $dlEnd;
			
			//echo("days: ".$dayCount." and $sundayCount<br>");
		}
		else
			list($totalAmt) = mysqli_fetch_row($result);
			
		if($qty != "")
			$displayTotal = $totalAmt * $qty;
			
		$blockHours = getBlockDisplayText($geoRegion,$block,$locationID,$timezone);
		
		echo($displayTotal."\n".$blockHours);
	}
	else {
		//natural gas trade logic
		if($freq == 2){
			//daily
			$pieces1 = explode("/",$startDate);
			$pieces2 = explode("/",$endDate);
			$date1 = gregoriantojd($pieces1[0],$pieces1[1],$pieces1[2]); //date calc
			$date2 = gregoriantojd($pieces2[0],$pieces2[1],$pieces2[2]); //date calc
			
			$dateDiff = $date2 - $date1 + 1;
			$output = $dateDiff * $qty * $NG_CONSTANT;
			echo($output);
		}
		else if($freq == 3){
			//monthly
			$startDate = reverseDate($startDate);
			$endDate = reverseDate($endDate);
			
			if($startDate > $endDate)
				return;
			
			$pieces1 = explode("-",$startDate);
			$pieces2 = explode("-",$endDate);
			$stamp1 = mktime(0,0,0,$pieces1[1],$pieces1[2],$pieces1[0]); //timestamp for monthly calc - bad format revised on 1/25/12
			$stamp2 = mktime(0,0,0,$pieces2[1],$pieces2[2],$pieces2[0]); //timestamp for monthly calc - bad format revised on 1/25/12
			//echo(idate('Y', $stamp2));
			
			$nrmonths = ((idate('Y', $stamp2) * 12) + idate('m', $stamp2)) - ((idate('Y', $stamp1) * 12) + idate('m', $stamp1));
			//echo($nrmonths+1);
			$output = (abs($nrmonths)+1) * $qty * $NG_CONSTANT;
			echo($output);
		}
	}
	
}
else if(isset($customHours)){
	
	if($type == 0){
		echo("<input name=\"hours\" type=\"text\" id=\"hours\" size=\"18\" value=\"\" class=\"readonly\" readonly=\"readonly\" />");
	}
	else if($type == 1){
		
		echo("<table cellpadding=\"1\">");
		
		/*echo("<tr><td>Start Day:</td>");  //removed in favor of new single-selection
		echo("<td><select name=\"startDay\" id=\"startDay\" onchange=\"updateQuantity();\">");
				getDaysList($startDay);
		echo("</select></td></tr>\n");
		
		echo("<tr><td>End Day:</td>");
		echo("<td><select name=\"endDay\" id=\"endDay\" onchange=\"updateQuantity();\">");
				getDaysList($endDay);
		echo("</select></td></tr>\n");*/
		
		echo("<tr><td>Day Type</td>");
		echo("<td><select name=\"dayType\" id=\"dayType\" onchange=\"updateQuantity();\">");
			getDayTypeList($dayType);
		echo("</select></td></tr>\n");
		
		echo("<tr><td>Start Hour:</td>");
		echo("<td><select name=\"startHour\" id=\"startHour\" onchange=\"updateQuantity();\">");
				getHoursList($startHour);
		echo("</select></td></tr>");
		
		echo("<tr><td>End Hour</td>");
		echo("<td><select name=\"endHour\" id=\"endHour\" onchange=\"updateQuantity();\">");
				getHoursList($endHour);
		echo("</select></td></tr>");
		echo("</table>");
		
		//add JS error check here
		echo("<script language=\"javascript\" type=\"text/javascript\">");
		//echo("tradeFields.push(\"startDay\");");
		//echo("tradeFields.push(\"endDay\");");
		echo("tradeFields.push(\"dayType\");");
		echo("tradeFields.push(\"startHour\");");
		echo("tradeFields.push(\"endHour\");");
		echo("</script>");
	}

}
else if(isset($resendConfirm)){
	
	// Force error logging for debugging
	ini_set('log_errors', 1);
	ini_set('error_log', '/www/trade/debug.log');
	
	error_log("Resend confirm requested - TradeID: $tradeID, ResendType: $resendType");

	//first, get live trade details
	$query = "SELECT t.*,COALESCE(b.fldProductGroup, 'Unknown') as fldProductGroup FROM $tblTransaction t 
			  LEFT JOIN $tblTradeType b ON t.fkProductGroup=b.pkTypeID 
			  LEFT JOIN $tblProduct p ON t.fkProductID=p.pkProductID 
			  WHERE t.pkTransactionID=".$tradeID;
	//echo("<p>".$query."</p>");
	$tradeResult = mysqli_query($_SESSION['db'],$query);
	@$tradeCount = mysqli_num_rows($tradeResult);
	
	error_log("Trade query result count: $tradeCount");
	
	if($tradeCount == 1) {
		require("confirm.php");
		$tradeResultSet = mysqli_fetch_row($tradeResult);
		
		if($resendType == 0 || $resendType == 2){
			//resend confirm normally to all parties
			//$queryClientEmail = "SELECT fldEmail FROM tblB2TClient WHERE pkClientID="; //removed on 9/10
			$queryClientEmail = "SELECT fldConfirmEmail FROM tblB2TClient WHERE pkClientID=";
			
			if($tradeResultSet[31] > 0) { //buying
				$queryClientEmail .= $tradeResultSet[5];
				$brokerID = $tradeResultSet[31];
			}
			else if($tradeResultSet[34] > 0) { //selling
				$queryClientEmail .= $tradeResultSet[6];
				$brokerID = $tradeResultSet[34];
			}
			
			//get email recipient for client
			$resultClientEmail = mysqli_query($_SESSION['db'],$queryClientEmail);
			list($clientEmail) = mysqli_fetch_row($resultClientEmail);
			
			$queryBrokerEmail = "SELECT fldEmail FROM tblB2TBroker WHERE pkBrokerID=".$brokerID;
			$resultBrokerEmail = mysqli_query($_SESSION['db'],$queryBrokerEmail);
			list($brokerEmail) = mysqli_fetch_row($resultBrokerEmail);
			
			$emailList = $brokerEmail;
			if($clientEmail != "")
				$emailList .= ", ".$clientEmail;		
		}
		else if($resendType == 1) {
			//resend to me - currently logged in user
			if($_SESSION["testScript"] == 1)
				$queryBrokerEmail = "SELECT fldEmail from tblB2TBroker WHERE pkBrokerID=1";
			else
				$queryBrokerEmail = "SELECT fldEmail from tblB2TBroker WHERE pkBrokerID=".$_SESSION["brokerID42"];
			//echo($queryBrokerEmail);
			$resultBrokerEmail = mysqli_query($_SESSION['db'],$queryBrokerEmail);
			list($brokerEmail) = mysqli_fetch_row($resultBrokerEmail);
			$emailList = $brokerEmail;
		}
	
		//send confirm emails to client and broker
		
		sendConfirmEmail($tradeResultSet,$brokerID,$emailList,$resendType);
		//echo($emailList);
		
		//$message = "<div class=\"clear\" style=\"height: 40px;\"><p class=\"confirmMessage\">Successfully resent $total trade confirm(s)</p></div>";
	}
}
else if(isset($sendInvoice)){

	if($_GET['invoiceID'] != ""){
		require("invoice.php");
		
		sendInvoiceEmail($_GET['invoiceID']);	
	}
}
else if(isset($searchCompany)){
	//dynamic company search with type-ahead on trade entry screen
	if($searchString != ""){
		$query = "SELECT * FROM tblB2TCompany WHERE fldCompanyName LIKE \"%".$searchString."%\" AND fldCompanyName!=\"NYMEX CLEARED\" AND fldCompanyName!=\"ICE CLEARED\" ORDER BY fldCompanyName ASC LIMIT 5";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_num_rows($result);
		
		if($count > 0){
			//echo("<div class=\"dynamicResult\">\n");
			while($resultSet = mysqli_fetch_row($result)){
				echo("<div onclick=\"handleCompany($resultSet[0],this)\">$resultSet[1]</div>\n");
			}
			//echo("</div>\n");
		}
		else {
			//echo("<div class=\"dynamicResult\">\n");
			echo("<div>No matches found</div>");
			//echo("</div>\n");
		}		
	}
}
else if(isset($getClientList)){
	populateTraderListFiltered($companyID);
}
else if(isset($getBrokers)) {
	getBrokers($brokerType,$transactionID,$showClearID);
}
else if($addBroker == 1 && isset($brokerCount) && isset($brokerType)) {
	/* also requires the following inputs:
	
	$brokerCount
	$brokerType
	$selectedBroker
	
	*/
	
	if($selectedBroker == "0") { }
	else {
	
	?>
    
    <div>
    <hr size="1" />
    
    <table border="0" cellspacing="3" cellpadding="0" id="<?php echo($brokerType); ?>BrokerBlock<?php echo($brokerCount); ?>">
      <tr>
        <td class="fieldLabel"><?php if($brokerType == "buy") { echo("Buying"); } else { echo("Selling"); } ?> Broker: </td>
        <td><select name="<?php echo($brokerType); ?>BrokerArr[]" id="<?php echo($brokerType); ?>Broker<?php echo($brokerCount); ?>">
           <?php
            populateBrokerList($selectedBroker);						
            ?>
          </select>
          
          <script language="javascript" type="text/javascript">
               // tradeFields.push("<?php echo($brokerType); ?>Broker<?php echo($brokerCount); ?>");
            </script>
            
            <span><a href="javascript:void(0);" onclick="removeBrokerBlock('<?php echo($brokerType); ?>BrokerBlock<?php echo($brokerCount); ?>','<?php echo($brokerType); ?>Broker<?php echo($brokerCount); ?>');">Remove</a></span>
        </td>
      </tr>
      <tr class="">
        <td class="fieldLabel">Commission Rate: </td>
        <td nowrap="nowrap"><input name="<?php echo($brokerType); ?>BrokerRateArr[]" type="text" id="<?php echo($brokerType); ?>BrokerRate<?php echo($brokerCount); ?>" size="10" value="<?php echo($brokerRate); ?>" onchange="updateCommission('<?php echo($brokerType); ?>','<?php echo($brokerCount); ?>');" />
            <input type="button" name="updateBuy" class="buttonSmall" value="Update Total" onclick="updateCommission('<?php echo($brokerType); ?>','<?php echo($brokerCount); ?>');" />
            <script language="javascript" type="text/javascript">
                //tradeFields.push("<?php echo($brokerType); ?>BrokerRate<?php echo($brokerCount); ?>");
            </script>
        </td>
      </tr>
      <tr>
        <td class="fieldLabel">Total Commission: </td>
        <td>$<input name="<?php echo($brokerType); ?>BrokerTotalArr[]" type="text" id="<?php echo($brokerType); ?>BrokerTotal<?php echo($brokerCount); ?>" size="12" value="<?php echo(formatDecimal2($brokerComm,2)); ?>" onblur="verifyManualCommission2(this);" /><!-- readonly="readonly" class="readonly--></td>
      </tr>
    </table>
    </div>
    
    <?php
	}
}
else if(isset($blocks)) {
	//dynamic rebuild of Blocks dropdown
	echo(populateBlock($blockID,$blockRegion,$locationID));		
}
else if(isset($indices)) {
	require_once("indicesObj.php");
		
	$index = new Indices();
	$index->setIndex($indexName);
	$index->setReport($reportName);
	$index->setHubName($hubName);
	$index->setDateRange(reverseDate($startDate),reverseDate($endDate));
	$index->getIndex();
		
	if($index->getOutputStream() != "") {
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/octet-stream");
		//header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"index_output.xls\"");
		
		echo($index->getOutputStream());
	}
	
}
else if(isset($indexType)) {
	//get hub list based on index type
	require_once("indicesObj.php");
	
	$index = new Indices();
	$index->getHubList($indexType);
}
else if(isset($action)) {
	
	$total = count($transID);
	//echo("<br>transID: ".$transID[0]);
	
	$query = "SELECT DISTINCT co.pkCompanyID,co.fldCompanyName FROM tblB2TTransaction t, tblB2TClient c, tblB2TCompany co WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID) OR
		(t.fkSellerID=c.pkClientID AND c.fkCompanyID=co.pkCompanyID)) AND (";
	
	if($total > 0){
		for($i=0; $i<$total; $i++){
			$query .= "t.pkTransactionID=$transID[$i] ";
			
			if($i < ($total -1))
				$query .= " OR ";
		}
	}
	
	$query .= ")";
	
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	
	while($parts = mysqli_fetch_row($result)) {
		echo("<tr><td><input type=\"checkbox\" name=\"clientID[]\" value=\"$parts[0]\" /> $parts[1]</td></tr>\n");	
	}
	
}
else if(isset($viewState)) {
	if($viewState == "true") {
		//dark mode
		setcookie("viewPref",$viewState,strtotime( '+30 days' ),"/");
		echo("css/styles-dark.css");		
	}
	else {
		//light mode
		setcookie("viewPref",$viewState, strtotime( '+30 days' ),"/");
		echo("css/styles.css");
	}
}
else if(isset($_POST["switchUserType"])) {
	$switchUserType = $_POST["switchUserType"];
	
	// Only allow administrators and super admins to switch user types (check actual user type, not temporary)
	if($_SESSION["userType"] == "1" || $_SESSION["userType"] == "2") {
		// Store the temporary user type in a session variable
		$_SESSION["tempUserType"] = $switchUserType;
		
		// Also store it in a cookie for persistence across page refreshes
		setcookie("tempUserType", $switchUserType, strtotime( '+1 hour' ),"/");
		
		echo("success");
	} else {
		echo("Access denied - only administrators and super admins can switch user types");
	}
}
else if(isset($_POST["resetUserType"])) {
	// Only allow administrators and super admins to reset user types (check actual user type, not temporary)
	if($_SESSION["userType"] == "1" || $_SESSION["userType"] == "2") {
		// Remove the temporary user type from session
		unset($_SESSION["tempUserType"]);
		
		// Remove the cookie
		setcookie("tempUserType", "", time() - 3600, "/");
		
		echo("success");
	} else {
		echo("Access denied - only administrators and super admins can reset user types");
	}
}
else {
	echo("nothing called");
}

?>