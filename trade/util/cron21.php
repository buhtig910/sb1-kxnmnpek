#!/usr/local/bin/php.cli
<?php

/*  line below removed, which appeared before php start
#!/usr/local/bin/php.cli
*/

//SCHEDULED CRON SCRIPT THAT RUNS EVERY 15 MINUTES
echo("<html><h2>/// TRADE CONFIRM SWEEP ///</h2>");

// Log cron execution time for dashboard monitoring
function logCronExecution() {
	$logFile = '../temp/cron_execution.log';
	$tempDir = '../temp/';
	
	// Ensure temp directory exists
	if (!is_dir($tempDir)) {
		mkdir($tempDir, 0755, true);
	}
	
	file_put_contents($logFile, time());
}

// Log this execution
logCronExecution();

require("../vars.inc.php");
require("../functions.php");
require("confirm.php");

$_SESSION["sortBy"] = "fldTradeDate,fldTransactionNum";
$_SESSION["sortOrder2"] = "ASC";

//get each broker
$queryUser = "SELECT pkBrokerID,fldEmail,fldUsername FROM $tblBroker";
$resultUser = mysqli_query($_SESSION['db'],$queryUser);
@$count = mysqli_num_rows($resultUser);

if($count > 0){

	while($resultSet = mysqli_fetch_row($resultUser)){
		/*
		0 = brokerID
		1 = email
		2 = username
		*/
		
		//first, get all confirmed trades
		$query = "SELECT t.*,b.fldProductGroup FROM $tblTransaction t, $tblTradeType b, $tblProduct p WHERE t.fkProductGroup=b.pkTypeID AND t.fkProductID=p.pkProductID AND fldStatus=".$_SESSION["CONST_CONFIRMED"]." AND (fkBuyingBroker=$resultSet[0] OR fkSellingBroker=$resultSet[0]) ORDER BY ".$_SESSION["sortBy"]." ".$_SESSION["sortOrder2"]; //GROUP BY fldTransactionNum
		//echo("<p>".$query."</p>");
		$tradeResult = mysqli_query($_SESSION['db'],$query);
		@$tradeCount = mysqli_num_rows($tradeResult);
		
		if($tradeCount > 0) {
				
			echo("<p><strong>$tradeCount</strong> trades found awaiting confirmation for $resultSet[2] (ID: $resultSet[0]).</p><ol>");
				
			while($tradeResultSet = mysqli_fetch_row($tradeResult)){
				
				if(otherLegConfirmed($tradeResultSet[0],$tradeResultSet[1],$tradeResultSet[2],$tradeResultSet[5],$tradeResultSet[6])){
					//send confirm if both legs have been confirmed
					$queryClientEmail = "SELECT fldConfirmEmail FROM tblB2TClient WHERE pkClientID=";
					
					if(isCleared($tradeResultSet[5]) || isCleared($tradeResultSet[6])){
						//cleared trade logic
						echo("<p>Cleared: $tradeResultSet[5] - $tradeResultSet[6]</p>\n");			
						$length = strlen($tradeResultSet[2]);
						
						if($length > 1)
							$length = $length - 1;
							
						//echo("<p>leg str length: $length</p>");
						if($tradeResultSet[31] > 0 && $tradeResultSet[34] == 0 && ((intval(substr($tradeResultSet[2],0,$length)) % 2) == 1)) { //buying, removed broker check on 2/8/11
							$queryClientEmail .= $tradeResultSet[5];
							echo("<li>Sending trade ID: $tradeResultSet[1]-$tradeResultSet[2] (transID: $tradeResultSet[0], clientID: $tradeResultSet[5], leg: $tradeResultSet[2])<br/>");
						}
						else if($tradeResultSet[34] > 0 && $tradeResultSet[31] == 0 && ((intval(substr($tradeResultSet[2],0,$length)) % 2) == 0)) { //selling, removed broker check on 2/8/11
							$queryClientEmail .= $tradeResultSet[6];
							echo("<li>Sending trade ID: $tradeResultSet[1]-$tradeResultSet[2] (transID: $tradeResultSet[0], clientID: $tradeResultSet[6], leg: $tradeResultSet[2])<br/>");
						}
						else
							echo("NO MATCH (".(substr($tradeResultSet[2],0,$length) % 2)."), transID: $tradeResultSet[0], clientID: $tradeResultSet[5], leg: $tradeResultSet[2] <br/>");
					}
					else if(isOTC($tradeResultSet[5]) || isOTC($tradeResultSet[6])){
						//OTC trade logic - treat similar to cleared trades
						echo("<p>OTC Trade: $tradeResultSet[5] - $tradeResultSet[6]</p>\n");
						
						// For OTC trades, determine which leg this is (buy or sell)
						if($tradeResultSet[31] > 0) { //buying leg
							$queryClientEmail .= $tradeResultSet[5];
							echo("<li>Sending OTC trade ID: $tradeResultSet[1]-$tradeResultSet[2] (transID: $tradeResultSet[0], clientID: $tradeResultSet[5], leg: $tradeResultSet[2])<br/>");
						}
						else if($tradeResultSet[34] > 0) { //selling leg
							$queryClientEmail .= $tradeResultSet[6];
							echo("<li>Sending OTC trade ID: $tradeResultSet[1]-$tradeResultSet[2] (transID: $tradeResultSet[0], clientID: $tradeResultSet[6], leg: $tradeResultSet[2])<br/>");
						}
					}
					else {
						//traditional trade logic		
						if($tradeResultSet[31] > 0) { //buying
							$queryClientEmail .= $tradeResultSet[5];
							echo("<li>Sending trade ID: $tradeResultSet[1]-$tradeResultSet[2] (transID: $tradeResultSet[0], clientID: $tradeResultSet[5], leg: $tradeResultSet[2])<br/>");
						}
						else if($tradeResultSet[34] > 0) { //selling
							$queryClientEmail .= $tradeResultSet[6];
							echo("<li>Sending trade ID: $tradeResultSet[1]-$tradeResultSet[2] (transID: $tradeResultSet[0], clientID: $tradeResultSet[6], leg: $tradeResultSet[2])<br/>");
						}
						else
							echo("NO Match");
					}
	
					//echo($queryClientEmail." <br/>");
					$resultClientEmail = mysqli_query($_SESSION['db'],$queryClientEmail);
					@list($clientEmail) = mysqli_fetch_row($resultClientEmail);
					
					$emailList = "";
		
					if($_SESSION["BROKER_CONFIRMS"] == 1)
						$emailList = $resultSet[1];
					
					if($clientEmail != "") {
						if($emailList == "")
							$emailList = $clientEmail;
						else
							$emailList .= ", ".$clientEmail;
					}
					
					if($emailList != "") {
						//if email list exists, build and send PDF
						echo("Recipient list: ".$emailList." with attachment: ");
				
						//send confirm emails to client and broker
						sendConfirmEmail($tradeResultSet,$resultSet[0],$emailList,0);
						if($_SESSION["testScript"] == 0) {
							moveTradeLive($tradeResultSet[0]); //move trade live
						}
						echo("</li>");
					}
					else {
						echo(" - NO EMAIL LIST FOUND! <br/>");
					}
				
				}
				else {
					echo("<li>Other leg not confirmed: $tradeResultSet[1]</li>");
				}
			}
			echo("</ol>");
		}
		else {
			//echo("<p>No trades found awaiting confirmation for $resultSet[2] (ID: $resultSet[0]).</p>");
		}
	}
}


//update company mapping
echo("<h2>/// UPDATING COMPANY MAPPING ///</h2>");
require("update_company_mapping.php");


//ConfirmHub integration - trade submission
$query = "SELECT fldActive FROM tblB2TConfirmHub WHERE pkConfigID=".$_SESSION["CONFIRM_HUB_API"];
$result = mysqli_query($_SESSION['db'],$query);
@list($chActive) = mysqli_fetch_row($result);

if($chActive == 1) {

	echo("<h2>///// RUNNING CONFIRM HUB SUBMISSION ///</h2>");
	
	include("confirm_hub/confirmHub.php");
	
	$hub = new ConfirmHub();
	if(isset($_GET['date'])) {
		$useDate = $_GET['date'];
		echo("<p>Processing using date: ".$useDate."</p>");
	}
	else {
		//$useDate = date("Y-m-d");
		$useTimestamp = time() - (60 * 15); //15 mins before current time
		echo("<p>Processing using timestamp: ".$useTimestamp."</p>");
	}
	
	/* commented out on 01-11-2021 during testing, assumed out-dated integration, expired credentials	
	$hub->initConfirmHub($useDate,$useTimestamp);
	$hub->saveDeals();
	*/
}

echo("</html>");
	
?>