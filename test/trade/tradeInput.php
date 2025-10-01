<?php
session_start();
	
	require_once("../functions.php");
	
	if(!validateSecurity()){
		echo("<p>You do not have permission to view this page.</p>");
		echo("<p><a href=\"index.php\">&gt; Return home</a></p>\n");
		exit;
	}
	
	
	require_once("../util/popupHeader.php");
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css" />
	<script type="text/javascript" src="../scripts/ajax.js"></script>
	<script type="text/javascript" src="../scripts/trade.js"></script>
    <script type="text/javascript" src="../scripts/jquery.js"></script>
    <script type="text/javascript" src="../scripts/cookie.js"></script>
    
	<noscript>
		<p><strong>WARNING:</strong> This site relies HEAVILY on JavaScript and it appears your browser does not support it.</p>
		<p>Please contact your support desk for assistance to use this application.</p>
	</noscript>
	
	<?php
			
	/************************
	* SUBMIT NEW TRANSACTION
	*
	* Enter a new trade logic
	************************/
	if($_POST["action"] == "submitTrans"){
		
		$transID = time(); //$_SESSION['userID']."-".
		$legID = 1;
		if($_POST["clearedTrade"] != "0")
			$loopLimit = 4; //cleared trade contra parties
		else
			$loopLimit = 2;
		//echo($transID);
		for($i=$legID; $i<=$loopLimit; $i++){
			/*if($buyerClearID != "")
				$clearID = $buyerClearID;
			else
				$clearID = $sellerClearID;*/

			if($_POST["clearedTrade"] != "0") {
				//cleared trade, add cleared leg counter parties
				$clearLeg = array("1","2","1b","2b");
				$tradeLegID = $clearLeg[$i-1];
			}
			else
				$tradeLegID	= $i;			

			//trade entry
			$query = "INSERT INTO tblB2TTransaction VALUES('',\"$transID\",\"$tradeLegID\",\"".reverseDate($_POST["confirmFor"])."\",\"$today\"";
			if($_POST["clearedTrade"] != "0" && $i==1)
				$query.= ",\"".$_POST["buyerID"]."\",\"".getContraParty($_POST["clearedTrade"])."\""; //buy leg
			else if($_POST["clearedTrade"] !="0" && $i==2)
				$query.= ",\"".$_POST["buyerID"]."\",\"".getContraParty($_POST["clearedTrade"])."\""; //sell leg
			else if($_POST["clearedTrade"] != "0" && $i==3)
				$query.= ",\"".getContraParty($_POST["clearedTrade"])."\",\"".$_POST["sellerID"]."\""; //buy leg b
			else if($_POST["clearedTrade"] != "0" && $i==4)
				$query.= ",\"".getContraParty($_POST["clearedTrade"])."\",\"".$_POST["sellerID"]."\""; //sell leg b
			else
				$query.= ",\"".$_POST["buyerID"]."\",\"".$_POST["sellerID"]."\"";
				
			$query.= ",\"".$_POST["tradeType"]."\",\"".$_POST["productID"]."\",\"".$_POST["productTypeID"]."\",\"".$_POST["region"]."\",\"".$_POST["location"]."\"".
				",\"".$_POST["offsetIndex"]."\",\"".$_POST["delivery"]."\",\"".$_POST["buyerPrice"]."\",\"".$_POST["buyerCurr"]."\",\"".$_POST["sellerPrice"]."\"".
				",\"".$_POST["sellerCurr"]."\",\"".$_POST["optionStyle"]."\",\"".$_POST["optionType"]."\",\"".$_POST["exerciseType"]."\",\"".$_POST["strikePrice"]."\"".
				",\"".$_POST["strikeCurr"]."\",\"".reverseDate($_POST["startDate"])."\",\"".reverseDate($_POST["endDate"])."\",\"".$_POST["block"]."\"".
				",\"".$_POST["qty"]."\",\"".$_POST["unitFreq"]."\",\"".$_POST["totalQty"]."\",\"".$_POST["hours"]."\",\"".$_POST["timezone"]."\"";
			//buy leg
			if($i % 2 == 1 && $_POST["clearedTrade"] == "0")
				$query.= ",\"".$_POST["buyBroker"]."\",\"".$_POST["buyBrokerRate"]."\",\"".removeSpecialChars($_POST["buyBrokerTotal"])."\",\"\",\"\",\"\"";
			//sell leg
			else if($i % 2 == 0 && $_POST["clearedTrade"] == "0")
				$query.= ",\"\",\"\",\"\",\"".$_POST["sellBroker"]."\",\"".$_POST["sellBrokerRate"]."\",\"".removeSpecialChars($_POST["sellBrokerTotal"])."\"";
			//buy leg of cleared trade
			else if($i == 1 && $_POST["clearedTrade"] != "0")
				$query.= ",\"".$_POST["buyBroker"]."\",\"".$_POST["buyBrokerRate"]."\",\"".removeSpecialChars($_POST["buyBrokerTotal"])."\",\"\",\"\",\"\"";
			//sell leg of cleared trade
			else if($i == 4 && $_POST["clearedTrade"] != "0")
				$query.= ",\"\",\"\",\"\",\"".$_POST["sellBroker"]."\",\"".$_POST["sellBrokerRate"]."\",\"".removeSpecialChars($_POST["sellBrokerTotal"])."\"";
			//cleared legs of cleared trade
			else
				$query.= ",\"".$_POST["buyBroker"]."\",\"\",\"\",\"".$_POST["sellBroker"]."\",\"\",\"\"";
				
			if(($i==2 || $i==3) && $_POST["clearedTrade"] != "0")
				$query.= ",1";
			else
				$query.= ",0";
			
			$query.= ",\"".$_SESSION["brokerID42"]."\""; //status default to 0
			$query.= ",\"".$_POST["startDay"]."\",\"".$_POST["endDay"]."\",\"".$_POST["startHour"]."\",\"".$_POST["endHour"]."\",\"".$_POST["dayType"]."\",\"".$_POST["clearID"]."\",\"".removeSpecialChars($_POST["buyCommTotal"])."\",\"".removeSpecialChars($_POST["sellCommTotal"])."\",\"".$_POST["indexPrice"]."\",\"\")"; //custom trade input
			//echo($query."<br>");
			$result = mysqli_query($_SESSION['db'],$query);	
			
			$transRefID = mysqli_insert_id($_SESSION['db']);
			if(count(array($_POST["buyBrokerArr"])) > 0 || count(array($_POST["sellBrokerArr"])) > 0) { //multiple brokers added to trade
				//buy leg
				if($i % 2 == 1 && $_POST["clearedTrade"] == "0")
					addBrokers($transRefID,$_POST["buyBrokerArr"], $_POST["buyBrokerRateArr"], $_POST["buyBrokerTotalArr"],1);
				//sell leg
				else if($i % 2 == 0 && $_POST["clearedTrade"] == "0")
					addBrokers($transRefID,$_POST["sellBrokerArr"], $_POST["sellBrokerRateArr"], $_POST["sellBrokerTotalArr"],0);
				//buy leg of cleared trade
				else if($i == 1 && $_POST["clearedTrade"] != "0")
					addBrokers($transRefID,$_POST["buyBrokerArr"], $_POST["buyBrokerRateArr"], $_POST["buyBrokerTotalArr"],1);
				//sell leg of cleared trade
				else if($i == 4 && $_POST["clearedTrade"] != "0")
					addBrokers($transRefID,$_POST["sellBrokerArr"], $_POST["sellBrokerRateArr"], $_POST["sellBrokerTotalArr"],0);
			}
			
			if($_POST["tradeType"] == 8) { //crude oil custom fields
				populateCustomFields($_POST,$transRefID);
			}
		}
		
		if($result){
			$step = "confirm";
		}
		else{
			//$tradeType = "";
			echo("<p class=\"errorMessage\">An error occurred trying to submit your trade. Please review the details or contact the site administrator.</p>\n");
		}
	}
	/****************************
	* ADMIN UPDATE TRADE
	*
	* Update trade information but don't issue new trade legs
	*****************************/
	else if($_POST["action"] == "updateTrade" && $_POST["adminAction"] == "overrideUpdate"){
		//get transaction number
		$queryTrans = "SELECT fldTransactionNum,fldClearID FROM tblB2TTransaction WHERE pkTransactionID=".$_GET["tradeID"];
		//echo($queryTrans);
		$resultTrans = mysqli_query($_SESSION['db'],$queryTrans);
		list($transactionNum,$currClearID) = mysqli_fetch_row($resultTrans);
		
		if($currClearID != "")
			$limit = 4; //cleared trade, get all 4 legs
		else
			$limit = 2;
		
		//get latest trade IDs
		$queryTrades = "SELECT * FROM (SELECT pkTransactionID,fldTransactionNum,fldLegID FROM tblB2TTransaction WHERE fldTransactionNum=$transactionNum ORDER BY fldLegID DESC LIMIT $limit) t ORDER BY pkTransactionID ASC";
		//echo($queryTrades);
		$resultTrades = mysqli_query($_SESSION['db'],$queryTrades);
		
		
		$parts = explode(",",$addlDeleteIDs);
		$types = explode(",",$addlDeleteTypes);		
		
		$loopCount == 0;
		while(list($updateID,$transactionID,$leg) = mysqli_fetch_row($resultTrades)){
			$query = "UPDATE tblB2TTransaction SET fldConfirmDate=\"".reverseDate($_POST["confirmFor"])."\"";
			
			/*if((substr($leg,0) % 2) == 1 ) { //buy leg
				echo("<p>cleared</p>\n");
				$query .= "fkBuyerID=\"$buyerID\", fkSellerID=\"".getContraParty($_POST["clearedTrade"])."\"";
			}
			else if((substr($leg,0) % 2) == 0) { // sell leg
				echo("<p>cleared 2</p>\n");
				$query .= "fkBuyerID=\"".getContraParty($_POST["clearedTrade"])."\", fkSellerID=\"$sellerID\"";
			}
			else
				$query.= "fkBuyerID=\"$buyerID\", fkSellerID=\"$sellerID\"";*/
			if($_POST["clearedTrade"] != "0" && ($loopCount+1)==1)
				$query.= ",fkBuyerID=\"".$_POST["buyerID"]."\",fkSellerID=\"".getContraParty($_POST["clearedTrade"])."\""; //buy leg
			else if($_POST["clearedTrade"]!="0" && ($loopCount+1)==2)
				$query.= ",fkBuyerID=\"".$_POST["buyerID"]."\",fkSellerID=\"".getContraParty($_POST["clearedTrade"])."\""; //sell leg
			else if($_POST["clearedTrade"] != "0" && ($loopCount+1)==3)
				$query.= ",fkBuyerID=\"".getContraParty($_POST["clearedTrade"])."\",fkSellerID=\"".$_POST["sellerID"]."\""; //buy leg b
			else if($_POST["clearedTrade"] != "0" && ($loopCount+1)==4)
				$query.= ",fkBuyerID=\"".getContraParty($_POST["clearedTrade"])."\",fkSellerID=\"".$_POST["sellerID"]."\""; //sell leg b
			else
				$query.= ",fkBuyerID=\"".$_POST["buyerID"]."\",fkSellerID=\"".$_POST["sellerID"]."\"";
			
			$query.= ", fkProductGroup=\"".$_POST["tradeType"]."\", fkProductID=\"".$_POST["productID"]."\", fkProductTypeID=\"".$_POST["productTypeID"]."\"".
					", fldRegion=\"".$_POST["region"]."\", fldLocation=\"".$_POST["location"]."\", fldOffsetIndex=\"".$_POST["offsetIndex"]."\", fldDelivery=\"".$_POST["delivery"]."\"".
					", fldBuyerPrice=\"".$_POST["buyerPrice"]."\", fldBuyerCurrency=\"".$_POST["buyerCurr"]."\", fldSellerPrice=\"".$_POST["sellerPrice"]."\", fldSellerCurrency=\"".$_POST["sellerCurr"]."\"".
					", fkOptionStyleID=\"".$_POST["optionStyle"]."\", fkOptionTypeID=\"".$_POST["optionType"]."\", fkExerciseType=\"".$_POST["exerciseType"]."\", fldStrike=\"".$_POST["strikePrice"]."\"".
					", fldStrikeCurrency=\"".$_POST["strikeCurr"]."\", fldStartDate=\"".reverseDate($_POST["startDate"])."\", fldEndDate=\"".reverseDate($_POST["endDate"])."\"".
					", fldBlock=\"".$_POST["block"]."\", fldUnitQty=\"".$_POST["qty"]."\", fldUnitFreq=\"".$_POST["unitFreq"]."\", fldTotalQuantity=\"".$_POST["totalQty"]."\", fldHours=\"".$_POST["hours"]."\", fldTimeZone=\"".$_POST["timezone"]."\"";

			//buy leg
			if($leg % 2 == 1 && $_POST["clearedTrade"] == "0")
				$query.= ", fkBuyingBroker=\"".$_POST["buyBroker"]."\", fldBuyBrokerRate=\"".$_POST["buyBrokerRate"]."\", fldBuyBrokerComm=\"".removeSpecialChars($_POST["buyBrokerTotal"])."\"";
			//sell leg
			else if($leg % 2 == 0 && $_POST["clearedTrade"] == "0")
				$query.= ", fkSellingBroker=\"".$_POST["sellBroker"]."\", fldSellBrokerRate=\"".$_POST["sellBrokerRate"]."\", fldSellBrokerComm=\"".removeSpecialChars($_POST["sellBrokerTotal"])."\"";
			//buy leg of cleared trade
			else if(($loopCount+1) == 1 && $_POST["clearedTrade"] != "0")
				$query.= ", fkBuyingBroker=\"".$_POST["buyBroker"]."\", fldBuyBrokerRate=\"".$_POST["buyBrokerRate"]."\", fldBuyBrokerComm=\"".removeSpecialChars($_POST["buyBrokerTotal"])."\"";
			//sell leg of cleared trade
			else if(($loopCount+1) == 4 && $_POST["clearedTrade"] != "0")
				$query.= ", fkSellingBroker=\"".$_POST["sellBroker"]."\", fldSellBrokerRate=\"".$_POST["sellBrokerRate"]."\", fldSellBrokerComm=\"".removeSpecialChars($_POST["sellBrokerTotal"])."\"";
				
			$query.= ", fkEntryUserID=\"".$_SESSION["brokerID42"]."\""; 
			$query.= ", fldStartDay=\"".$_POST["startDay"]."\", fldEndDay=\"".$_POST["endDay"]."\", fldStartHour=\"".$_POST["startHour"]."\", fldEndHour=\"".$_POST["endHour"]."\"".
					", fkDayType=\"".$_POST["dayType"]."\", fldClearID=\"".$_POST["clearID"]."\", fldBuyCommTotal=\"".removeSpecialChars($_POST["buyCommTotal"])."\", fldSellCommTotal=\"".removeSpecialChars($_POST["sellCommTotal"])."\", fldIndexPrice=\"".$_POST["indexPrice"]."\"".
					" WHERE pkTransactionID=".$updateID;
			
			//echo($query."<br />");
			$result = mysqli_query($_SESSION['db'],$query);
			$updateCount = mysqli_affected_rows($_SESSION['db']);
		
			/* removed dropped brokers */	
			for($i=0; $i<count($parts); $i++) {
				if($parts[$i] > 0) {
					//echo("delete called <br>");
					deleteAddlBroker($updateID,$parts[$i],$types[$i]);
				}
			}
			
			if($_POST["tradeType"] == 8) { //crude oil custom fields
				updateCustomFields($_POST,$updateID);
			}
						
			/* need to setup logic for this one on admin update 
			if(count($_POST["buyBrokerArr"]) > 0 || count($_POST["sellBrokerArr"]) > 0) { //multiple brokers added to trade
				//$transRefID = mysqli_insert_id($_SESSION['db']);
				$transRefID = $_GET['tradeID'];
				if((substr($leg,0)) == 1) {
					echo("update broker");
					updateBrokers($updateID,$_POST["buyBrokerArr"], $_POST["buyBrokerRateArr"], $_POST["buyBrokerTotalArr"],1);
				}
				else if((substr($leg,0)) == 0) {
					updateBrokers($updateID,$_POST["sellBrokerArr"], $_POST["sellBrokerRateArr"], $_POST["sellBrokerTotalArr"],0);
				}
			}*/
			
			if(count($_POST["buyBrokerArr"]) > 0 || count($_POST["sellBrokerArr"]) > 0) { //multiple brokers added to trade
				//echo($updateID."<br>");
				//buy leg
				if($leg % 2 == 1 && $_POST["clearedTrade"] == "0")
					updateBrokers($updateID,$_POST["buyBrokerArr"], $_POST["buyBrokerRateArr"], $_POST["buyBrokerTotalArr"],1);
				//sell leg
				else if($leg % 2 == 0 && $_POST["clearedTrade"] == "0")
					updateBrokers($updateID,$_POST["sellBrokerArr"], $_POST["sellBrokerRateArr"], $_POST["sellBrokerTotalArr"],0);
				//buy leg of cleared trade
				else if($leg == 1 && $_POST["clearedTrade"] != "0")
					updateBrokers($updateID,$_POST["buyBrokerArr"], $_POST["buyBrokerRateArr"], $_POST["buyBrokerTotalArr"],1);
				//sell leg of cleared trade
				else if($leg == 4 && $_POST["clearedTrade"] != "0")
					updateBrokers($updateID,$_POST["sellBrokerArr"], $_POST["sellBrokerRateArr"], $_POST["sellBrokerTotalArr"],0);
			}
			
			if($updateCount > 0){
				$step = "confirm";
				$transID = $transactionID;
				$now = date("Y-m-d H:i:s");
				$auditQuery = "INSERT INTO tblB2TAudit VALUES ('','$updateID','$transactionID','$now')";
				//echo($auditQuery);
				$auditResult = mysqli_query($_SESSION['db'],$auditQuery);
			}
			
			$loopCount++;
		}
		
		if($updateCount == 0){
			echo("<p class=\"errorMessage\">You did not modify any trade information, so no updates were made to the database.</p>\n");
		}
		
	}
	
	/*************************
	* UPDATE TRADE
	*
	* Update an existing trade, void the previous legs
	*************************/
	else if($_POST["action"] == "updateTrade" && $_POST["adminAction"] != "overrideUpdate"){
		//get transaction number
		$queryTrans = "SELECT fldTransactionNum FROM tblB2TTransaction WHERE pkTransactionID=".$_GET["tradeID"];
		//echo($queryTrans);
		$resultTrans = mysqli_query($_SESSION['db'],$queryTrans);
		list($transactionNum) = mysqli_fetch_row($resultTrans);
		
		//cancel current trades
		$queryCancel = "UPDATE tblB2TTransaction SET fldStatus=".$_SESSION["CONST_CANCELLED"]." WHERE fldTransactionNum=".$transactionNum;
		$resultCancel = mysqli_query($_SESSION['db'],$queryCancel);
		
		//get latest leg #
		$queryLeg = "SELECT fldLegID FROM tblB2TTransaction WHERE fldTransactionNum=".$transactionNum." ORDER BY pkTransactionID DESC LIMIT 1";
		//echo($queryLeg);
		$resultLeg = mysqli_query($_SESSION['db'],$queryLeg);
		list($legID) = mysqli_fetch_row($resultLeg);
		
		$legID = $legID + 1;
		if($_POST["clearedTrade"] != "0")
			$loopLimit = 4; //cleared trade contra parties
		else
			$loopLimit = 2;
			
		$transID = $transactionNum;
		$loopCount = 0;
		//echo($transID);
		for($i=$legID; $i<=(($legID+$loopLimit)-1); $i++){
			//echo("loop".$i."<br />\n");
			/*if($buyerClearID != "")
				$clearID = $buyerClearID;
			else
				$clearID = $sellerClearID;*/
				
			if($_POST["clearedTrade"] != "0") {
				//cleared trade, add cleared leg counter parties
				$clearLeg = array($legID,($legID+1),$legID."b",($legID+1)."b");
				$tradeLegID = $clearLeg[$loopCount];
			}
			else
				$tradeLegID	= $i;
		
			$query = "INSERT INTO tblB2TTransaction VALUES('',\"$transID\",\"$tradeLegID\",\"".reverseDate($_POST["confirmFor"])."\",\"$today\"";
			if($_POST["clearedTrade"] != "0" && ($loopCount+1)==1)
				$query.= ",\"".$_POST["buyerID"]."\",\"".getContraParty($_POST["clearedTrade"])."\""; //buy leg
			else if($_POST["clearedTrade"]!="0" && ($loopCount+1)==2)
				$query.= ",\"".$_POST["buyerID"]."\",\"".getContraParty($_POST["clearedTrade"])."\""; //sell leg
			else if($_POST["clearedTrade"] != "0" && ($loopCount+1)==3)
				$query.= ",\"".getContraParty($_POST["clearedTrade"])."\",\"".$_POST["sellerID"]."\""; //buy leg b
			else if($_POST["clearedTrade"] != "0" && ($loopCount+1)==4)
				$query.= ",\"".getContraParty($_POST["clearedTrade"])."\",\"".$_POST["sellerID"]."\""; //sell leg b
			else
				$query.= ",\"".$_POST["buyerID"]."\",\"".$_POST["sellerID"]."\"";
			$query.= ",\"".$_POST["tradeType"]."\",\"".$_POST["productID"]."\",\"".$_POST["productTypeID"]."\"".
				",\"".$_POST["region"]."\",\"".$_POST["location"]."\",\"".$_POST["offsetIndex"]."\",\"".$_POST["delivery"]."\"".
				",\"".$_POST["buyerPrice"]."\",\"".$_POST["buyerCurr"]."\",\"".$_POST["sellerPrice"]."\",\"".$_POST["sellerCurr"]."\",\"".$_POST["optionStyle"]."\"".
				",\"".$_POST["optionType"]."\",\"".$_POST["exerciseType"]."\",\"".$_POST["strikePrice"]."\",\"".$_POST["strikeCurr"]."\"".
				",\"".reverseDate($_POST["startDate"])."\",\"".reverseDate($_POST["endDate"])."\",\"".$_POST["block"]."\",\"".$_POST["qty"]."\"".
				",\"".$_POST["unitFreq"]."\",\"".$_POST["totalQty"]."\",\"".$_POST["hours"]."\",\"".$_POST["timezone"]."\"";
			//buy leg
			if($i % 2 == 1 && $_POST["clearedTrade"] == "0")
				$query.=",\"".$_POST["buyBroker"]."\",\"".$_POST["buyBrokerRate"]."\",\"".removeSpecialChars($_POST["buyBrokerTotal"])."\",\"\",\"\",\"\"";
			//sell leg
			else if($i % 2 == 0 && $_POST["clearedTrade"] == "0")
				$query.=",\"\",\"\",\"\",\"".$_POST["sellBroker"]."\",\"".$_POST["sellBrokerRate"]."\",\"".removeSpecialChars($_POST["sellBrokerTotal"])."\"";
			//buy leg of cleared trade
			else if(($loopCount+1) == 1 && $_POST["clearedTrade"] != "0")
				$query.= ",\"".$_POST["buyBroker"]."\",\"".$_POST["buyBrokerRate"]."\",\"".removeSpecialChars($_POST["buyBrokerTotal"])."\",\"\",\"\",\"\"";
			//sell leg of cleared trade
			else if(($loopCount+1) == 4 && $_POST["clearedTrade"] != "0")
				$query.= ",\"\",\"\",\"\",\"".$_POST["sellBroker"]."\",\"".$_POST["sellBrokerRate"]."\",\"".removeSpecialChars($_POST["sellBrokerTotal"])."\"";
			//cleared legs of cleared trade
			else
				$query.= ",\"".$_POST["buyBroker"]."\",\"\",\"\",\"".$_POST["sellBroker"]."\",\"\",\"\"";
				
			if((($loopCount+1)==2 || ($loopCount+1)==3) && $_POST["clearedTrade"] != "0")
				$query.= ",1";
			else
				$query.= ",0";
			
			$query.=",\"".$_SESSION["brokerID42"]."\""; //status default to 0
			$query.= ",\"".$_POST["startDay"]."\",\"".$_POST["endDay"]."\",\"".$_POST["startHour"]."\",\"".$_POST["endHour"]."\",\"".$_POST["dayType"]."\",\"".$_POST["clearID"]."\",\"".removeSpecialChars($_POST["buyCommTotal"])."\",\"".removeSpecialChars($_POST["sellCommTotal"])."\",\"".$_POST["indexPrice"]."\",\"\")";
			echo($query."<br>");
			$result = mysqli_query($_SESSION['db'],$query);
			$transRefID = mysqli_insert_id($_SESSION['db']);
			
			if(@count($_POST["buyBrokerArr"]) > 0 || @count($_POST["sellBrokerArr"]) > 0) {
				
				if($i % 2 == 1)
					addBrokers($transRefID,$_POST["buyBrokerArr"], $_POST["buyBrokerRateArr"], $_POST["buyBrokerTotalArr"],1);
				else if($i % 2 == 0)
					addBrokers($transRefID,$_POST["sellBrokerArr"], $_POST["sellBrokerRateArr"], $_POST["sellBrokerTotalArr"],0);
			}
			
			if($_POST["tradeType"] == 8) { //crude oil custom fields
				populateCustomFields($_POST,$transRefID);
			}
			
			$loopCount++;			
		}
		
		$rowID = mysqli_insert_id($_SESSION['db']);			
		$now = date("Y-m-d H:i:s");
		$auditQuery = "INSERT INTO tblB2TAudit VALUES ('','$rowID','$transID','$now')";
		//echo($auditQuery);
		$auditResult = mysqli_query($_SESSION['db'],$auditQuery);
		
		if($result){
			$step = "confirm";
		}
		else{
			//$tradeType = "";
		}
		
	}
	/*****************
	* CANCEL TRADE
	*
	* Cancel a trade, including all legs
	*****************/
	else if($_POST["action"] == "cancelTrade"){
		//need to send notification??
		$cancelQuery = "UPDATE tblB2TTransaction SET fldStatus=".$_SESSION["CONST_CANCELLED"]." WHERE fldTransactionNum=".$_GET["transactionID"];
		$cancelResult = mysqli_query($_SESSION['db'],$cancelQuery);
		$rowCount = mysqli_affected_rows($_SESSION['db']);
		
		if($rowCount>0){
			echo("<p class=\"confirmMessage\">Transaction cancelled successfully.</p>\n");
		}
		else {
			echo("<p class=\"errorMessage\">Unable to delete transactions, an error occurred.</p>\n");
		}
		
	}
	
	
	if($step == "confirm") {
		echo("<p class=\"confirmMessage\" id=\"confirmMessage\">Your transaction has been submitted successfully.</p><p>Your transaction ID is: <strong>$transID</strong>.</p>\n");
		echo("<p align=\"center\"><input type=\"button\" id=\"close\" name=\"close\" value=\"Close Window\" class=\"buttonSmall\" onclick=\"window.close();\" />\n");
		
		if($_GET["copyTrade"] == 1)
			echo("<script language=\"javascript\" type=\"text/javascript\">opener.opener.location.reload(true);</script>\n");
		else
			echo("<script language=\"javascript\" type=\"text/javascript\">opener.location.reload(true);</script>\n");
		
		//$queryConfirm = "SELECT fldEmail FROM $tblBroker b LEFT JOIN $tblTransaction t ON t.fkBuyingBroker=b.pkBrokerID WHERE b.pkBrokerID=$buyBroker";
		//$queryConfirm = "SELECT fldEmail FROM $tblBroker WHERE pkBrokerID=$buyBroker OR pkBrokerID=$sellBroker";
		//echo($queryConfirm);
		//$resultConfirm = mysqli_query($_SESSION['db'],$queryConfirm);
		//@$count = mysqli_num_rows($resultConfirm);
		
		//if($count > 0){
		//	while($resultSet = mysqli_fetch_row($resultConfirm)){
				/*
				0 = email address
				*/
				
				//sendConfirmation($resultSet[0]);
		//	}
		//}
	
	}
	else if($_GET["tradeType"] != "" && $_POST["action"] != "cancelTrade"){
		//$_SESSION["myTradeType"] = $tradeType;
		//$tradeType = $tradeType;
			
			$query = "SELECT fldProductGroup,fldUnits FROM ".$tblTradeType." WHERE pkTypeID=".$_GET["tradeType"];
			//echo($query);
			$result = mysqli_query($_SESSION['db'],$query);
			@list($productGroup,$tradeUnits) = mysqli_fetch_row($result);
			
			echo("<h2>$productGroup</h2>\n");
			
			$query2 = "SELECT * FROM tblB2TTemplates WHERE fkTypeID=".$_GET["tradeType"];
			//echo($query2);
			$result2 = mysqli_query($_SESSION['db'],$query2);
			$templateOptions = mysqli_fetch_row($result2);
			/*
			0 = pkTemplateID
			1 = fldTemplateName
			2 = fldOffsetIndex
			3 = fldDeliveryType
			4 = fldBuyerPrice
			5 = fldBuyerCurrency
			6 = fldSellerPrice
			7 = fldSellerCurrency
			8 = fldOptionStyle
			9 = fldOptionType
			10 = fldExerciseType
			11 = fldStrike
			12 = fldStrikeCurrency
			13 = fldBlock
			14 = fldHours
			15 = fldTimeZone
			16 = fldUnitFreq
			17 = fldIndexPrice
			18 = fkTypeID
			*/			
			
			//IF TRADE ID, GET TRADE DETAILS FOR DISPLAY
			if($_GET["tradeID"] != ""){
				$queryTrade = "SELECT * FROM $tblTransaction WHERE pkTransactionID=".$_GET["tradeID"];
				//echo($queryTrade);
				$resultTrade = mysqli_query($_SESSION['db'],$queryTrade);
				@$count = mysqli_num_rows($resultTrade);
				
				if($count == 1){
					list($tradeID,$transactionID,$leg,$confirmFor,$tradeDate,$buyerID,$sellerID,$productGroup2,$productID,$productType,$region,$location,$offsetIndex,$delivery,$buyerPrice,$buyerCurrency,$sellerPrice,$sellerCurrency,$optionStyle,$optionType,$exerciseType,$strike,$strikeCurrency,$startDate,$endDate,$block,$qty,$freq,$totalQty,$hours,$timezone,$buyBroker,$buyBrokerRate,$buyBrokerComm,$sellBroker,$sellBrokerRate,$sellBrokerComm,$status,$entryUserID,$startDay,$endDay,$startHour,$endHour,$dayType,$clearID,$totalBuyComm,$totalSellComm,$indexPrice) = mysqli_fetch_row($resultTrade);
					
					//echo("buy: ".$buyBroker.", sell: ".$sellBroker);
					//get buy/sell broker ID's if missing
					if($buyBroker == 0){
						$limit = verifyLimit($leg,$buyerID,$sellerID);
						$query2 = "SELECT fkBuyingBroker,fldBuyBrokerRate,fldBuyBrokerComm FROM $tblTransaction WHERE fldTransactionNum=$transactionID AND pkTransactionID!=$tradeID AND pkTransactionID<$tradeID ORDER BY pkTransactionID DESC LIMIT $limit";
						//echo($query2);
						$result2 = mysqli_query($_SESSION['db'],$query2);
						@list($buyBroker,$buyBrokerRate,$buyBrokerComm) = mysqli_fetch_row($result2);
					}
					else if($sellBroker == 0){
						$limit = verifyLimit($leg,$buyerID,$sellerID);
						$query2 = "SELECT fkSellingBroker,fldSellBrokerRate,fldSellBrokerComm FROM $tblTransaction WHERE fldTransactionNum=$transactionID AND pkTransactionID!=$tradeID ORDER BY pkTransactionID DESC LIMIT $limit";
						//echo($query2);
						$result2 = mysqli_query($_SESSION['db'],$query2);
						@list($sellBroker,$sellBrokerRate,$sellBrokerComm) = mysqli_fetch_row($result2);
					}
				}
				
				//GET AUDIT HISTORY IS AVAILABLE
				$auditQuery = "SELECT * FROM $tblAuditHistory WHERE fldTransactionNum=$transactionID";
				//echo($auditQuery);
				$auditResult = mysqli_query($_SESSION['db'],$auditQuery);
				@$auditCount = mysqli_num_rows($auditResult);
				
			}
			
		?>
		
		<link rel="stylesheet" type="text/css" href="../css/fonts-min.css" />
		<link rel="stylesheet" type="text/css" href="../css/calendar.css" />
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/calendar/calendar-min.js"></script>
		
		<p>Enter the trade details below.</p>
		
		<?php
		if($auditCount > 0){
			//show trade history tab
		?>
		<ul class="tabs">
			<li><a href="javascript:void(0);" onclick="toggleTab(this);" id="tab0" class="selected">Trade Information</a></li>
			<li><a href="javascript:void(0);" onclick="toggleTab(this);" id="tab1" class="">Trade History</a></li>
		</ul>

		<div id="tabsContent">
			<div id="tab0_content" style="display: block;">
			
		<?php
		}
		?>
		
		<form name="tradeInfo" action="" method="post" onsubmit="return confirmSubmit();" autocomplete="off">
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Trade Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td class="fieldLabel">Transaction Number: </td>
					<td><?php if($_GET["copyTrade"] != 1) echo($transactionID); ?></td>
					<td nowrap="nowrap" class="fieldLabel">&nbsp;</td>
					<td nowrap="nowrap" class="fieldLabel">Cleared Trade:</td>
					<td>
                    
                    <?php
						$showClearID = showClearedTrade($clearID,$buyerID,$sellerID);
                    ?>
                    
                    </td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Leg ID: </td>
					<td><?php if($_GET["copyTrade"] != 1) echo($leg); ?></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
                    	<div id="clearIDField" style="display: <? if($clearID == "" && !$showClearID) echo("none"); ?>;">
                            ID: 
                            <input name="clearID" type="text" id="clearID" size="10" value="<?php echo($clearID); ?>" />
                       	</div>
                     </td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Trade Date:</td>
					<td class="yui-skin-sam">
						<?php
						
						if($confirmFor != "" && $_GET["copyTrade"] != 1)
							$showDate = $confirmFor;
						else
							$showDate = $today;
						?>
						<input name="confirmFor" type="text" id="confirmFor" size="15" value="<?php echo(formatDate($showDate)); ?>" />
						<script language="javascript" type="text/javascript">
							tradeFields.push("confirmFor");
						</script>
						
						<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show1up" class="center" />
						<div id="cal1Container"></div>
						
						<script language="javascript" type="text/javascript">
	 			
							function mySelectHandler(type,args,obj) {
								var dates = args[0]; 
								var date = dates[0]; 
								var year = date[0], month = date[1], day = date[2];
								
								if(month.toString().length == 1)
									month = "0" + month;
								if(day.toString().length == 1)
									day = "0" + day;

								var dateStr = month + "/" + day + "/" + year;								
								displayDate("confirmFor",dateStr);
								this.hide();
							};

							
							YAHOO.namespace("example.calendar"); 
							 
							YAHOO.example.calendar.init = function() { 
								YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar("cal1","cal1Container", { title:"Choose a date:", close:true } );
								YAHOO.example.calendar.cal1.selectEvent.subscribe(mySelectHandler, YAHOO.example.calendar.cal1, true);
								YAHOO.example.calendar.cal1.render();
								 
								// Listener to show the single page Calendar when the button is clicked 
								YAHOO.util.Event.addListener("show1up", "click", YAHOO.example.calendar.cal1.show, YAHOO.example.calendar.cal1, true); 
							} 
							
							YAHOO.util.Event.onDOMReady(YAHOO.example.calendar.init);
						
						</script>
						
					</td>
					<td class="yui-skin-sam">&nbsp;</td>
					<td class="yui-skin-sam">&nbsp;</td>
					<td class="yui-skin-sam">&nbsp;</td>
				  </tr>
				</table>
			</div>
		</div>
		
		<p align="center"><input type="button" name="swapOrder" id="swapOrder" value="Swap Buyer/Seller" onclick="swapBuyerSeller();" class="button" /></p>
		
		<div class="fieldGroup groupHalf" style="margin-right: 15px;">
			<div class="groupHeader">
				Buyer Information
			</div>
		  <div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
				    <td class="fieldLabel" valign="top">Company Search:</td>
				    <td>
			        	<input name="filterBuyer" type="text" id="filterBuyer" onblur="hide('buyResult');" onkeyup="showResults(this,'buyResult');" size="40" />
						<div><div class="dynamicResult" id="buyResult" style="display:none;"></div></div>
					</td>
				  </tr>
				  <tr>
					<td class="fieldLabel" valign="top">Buying Trader: </td>
					<td><select name="buyerID" id="buyer" onchange="showDetails(this);">
                      <?php
					  	//if(isset($buyerID))
						if($buyerID == $_SESSION["NYMEX_ID"] || $buyerID == $_SESSION["ICE_ID"] || $buyerID == $_SESSION["NGX_ID"] || $buyerID == $_SESSION["NODAL_ID"] || $buyerID == $_SESSION["NASDAQ_ID"]){
							$buyerID = verifyParty($tradeID,"buyer");
						}
						
						populateTraderList($buyerID);						
					  ?>
                    </select>
					<script language="javascript" type="text/javascript">
						tradeFields.push("buyer");
					</script>
					
					<!--<div id="buyerClearField" style="display: none;">
				  		ID: <input name="buyerClearID" type="text" id="buyerClearID" size="10" value="<?php echo($clearID) ?>" />
				  		</div>-->
                  </tr>
				  <tr>
					<td class="fieldLabel">Company: </td>
					<td><input name="buyerCompany" type="text" id="buyerCompany" size="30" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Address: </td>
					<td><input name="buyerAddress" type="text" id="buyerAddress" size="38" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Telephone:</td>
				    <td><input name="buyerPhone" type="text" id="buyerPhone" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="buyerFax" type="text" id="buyerFax" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				</table>
		  </div>
		</div>
		
		<div class="fieldGroup groupHalf">
			<div class="groupHeader">
				Seller Information
			</div>
			<div class="groupContent">
				<table border="0" cellspacing="3" cellpadding="0">
				  <tr>
				    <td class="fieldLabel" valign="top">Company Search:</td>
				    <td>
			        	<input name="filterSeller" type="text" id="filterSeller" onblur="hide('sellResult');" onkeyup="showResults(this,'sellResult');" size="40" />
						<div><div class="dynamicResult" id="sellResult" style="display:none;"></div></div>
					</td>
			      </tr>
				  <tr>
					<td class="fieldLabel" valign="top">Selling Trader: </td>
					<td><select name="sellerID" id="seller" onchange="showDetails(this);">
					  <?php
					  	//if(isset($sellerID))
						if($sellerID == $_SESSION["NYMEX_ID"] || $sellerID == $_SESSION["ICE_ID"]  || $sellerID == $_SESSION["NGX_ID"] || $sellerID == $_SESSION["NODAL_ID"] || $sellerID == $_SESSION["NASDAQ_ID"]){
							$sellerID = verifyParty($tradeID,"seller");
						}
							
						populateTraderList($sellerID);						
					  ?>
					  </select>
					  
					  <script language="javascript" type="text/javascript">
							tradeFields.push("seller");
					  </script>
					  
					 <!--<div id="sellerClearField" style="display: none;">
						ID: <input name="sellerClearID" type="text" id="sellerClearID" size="10" value="<?php echo($clearID) ?>" />
					  </div>-->
                     </td>
				  </tr>				  
				  <tr>
					<td class="fieldLabel">Company: </td>
					<td><input name="sellerCompany" type="text" id="sellerCompany" size="30" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Address: </td>
					<td><input name="sellerAddress" type="text" id="sellerAddress" size="38" readonly="readonly" class="readonly" /></td>
				  </tr>
				  <tr>
				    <td class="fieldLabel">Telephone:</td>
				    <td><input name="sellerPhone" type="text" id="sellerPhone" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				  <tr>
				    <td class="fieldLabel">Fax:</td>
				    <td><input name="sellerFax" type="text" id="sellerFax" size="30" readonly="readonly" class="readonly" /></td>
			      </tr>
				</table>
			</div>
		</div>
		
		<div class="clear"></div>
		
		<div class="fieldGroup">
			<div class="groupHeader">
				Trade Details
			</div>
		  <div class="groupContent">
		    <table border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td colspan="2" valign="top"><table border="0" cellspacing="3" cellpadding="0">
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Product Group: </td>
					    <td><input name="productGroup" id="productGroup" type="text" class="readonly" value="<?php echo($productGroup); ?>" size="20" readonly="readonly" />
						
							<script language="javascript" type="text/javascript">
								tradeFields.push("productGroup");
							</script>
						</td>
				      </tr>
                      
                      <?php
					  //hide for Crude Oil trades
					  //if($templateOptions[18] != "1"){
					  ?>
					  <tr>
					    <td nowrap="nowrap" class="fieldLabel">Product:</td>
					    <td><select name="productID" id="productID">
					      <?php
							populateProductList($productID,$_GET["tradeType"]);						
						  ?>
						  </select>
						  <script language="javascript" type="text/javascript">
								tradeFields.push("productID");
							</script>
						</td>
				      </tr>
                      <?php
					  //}
					  ?>
                      
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Product Type: </td>
					    <td><select name="productTypeID" id="productTypeID">
                          <?php
							populateProductType($productType);						
						  ?>
                          </select>
						  
						  <script language="javascript" type="text/javascript">
								tradeFields.push("productTypeID");
							</script>
						  </td>
				      </tr>
                      
                      <?php
					  //hide for Crude Oil trades
					  if($templateOptions[18] != "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Location: </td>
					    <td style="height: 20px; width: 170px;"><select name="location" id="location" style="width: 170px;" onfocus="toggleWidth(this);" onblur="resetWidth(this);" onchange="updateRegion();this.blur();">
                              <?php
								populateLocationList($location,$productGroup);
							  ?>
                          </select>
						  
						  <script language="javascript" type="text/javascript">
								tradeFields.push("location");
							</script>
						</td>
				      </tr>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Region: </td>
					    <td>
							<input type="text" name="region" id="region" size="25" value="<?php echo($region); ?>" readonly="readonly" class="readonly" />
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("region");
							</script>
						</td>
				      </tr>
                      <?php
					  }
					  ?>
					  
					  
					  <?php
					  if($templateOptions[2] == "1"){
					  ?>
					  <tr>
					    <td nowrap="nowrap" class="fieldLabel">Offset Index: </td>
					    <td style="height: 20px; width: 170px;">
							<select name="offsetIndex" id="offsetIndex" style="width: 170px;" onfocus="toggleWidth(this);" onblur="resetWidth(this);">
					     	<?php
								populateOffsetList($offsetIndex);
							?>
						 	</select>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("offsetIndex");
							</script>
						</td>
				      </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[3] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Delivery:</td>
					    <td><select name="delivery" id="delivery">
							<?php
								populateDeliveryList($delivery);						
							 ?>
							 </select>
							 
							 <script language="javascript" type="text/javascript">
								tradeFields.push("delivery");
							</script>
						</td>
				      </tr>
					  <?php
					  }
					  ?>
                      
                      <?php
					  //custom fields for Crude Oil trades
					  if($templateOptions[18] == "1"){
						  
						  //if editing, get the custom field values
						  if(isset($_GET["tradeID"])) {
							  $queryCustom = "SELECT * FROM tblB2TCustomFields WHERE fkTransID=".$_GET["tradeID"];
							  //echo("<p>".$queryCustom."</p>");
							  $resultCustom = mysqli_query($_SESSION['db'],$queryCustom);
							  list($recordID,$productCustom,$deliveryPoint,$additionalInfo,$priceCustom) = mysqli_fetch_row($resultCustom);
						  }
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Product: </td>
				        <td>
							<input type="text" name="productCustom" id="productCustom" value="<?php echo($productCustom); ?>">
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("productCustom");
							</script>
						</td>
			          </tr>
                      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Delivery Point: </td>
				        <td>
							<input type="text" name="deliveryPoint" id="deliveryPoint" value="<?php echo($deliveryPoint); ?>">
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("deliveryPoint");
							</script>
						</td>
			          </tr>
                      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Additional Info: </td>
				        <td>
							<textarea name="additionalInfo" id="additionalInfo"><?php echo($additionalInfo); ?></textarea>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("additionalInfo");
							</script>
						</td>
			          </tr>
                      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Price: </td>
				        <td>
							<textarea name="priceCustom" id="priceCustom"><?php echo($priceCustom); ?></textarea>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("priceCustom");
							</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
                    </table></td>
					<td class="spacer">&nbsp;</td>
				    <td colspan="2" valign="top"><table border="0" cellspacing="3" cellpadding="0">
				      
                      <?php
					  if($templateOptions[17] == "1"){
					  ?>
				     <tr>
                        <td nowrap="nowrap" class="fieldLabel">Index Price: </td>
				        <td>
                          <input name="indexPrice" type="text" id="indexPrice" size="12" value="<?php echo(formatIndexPrice($indexPrice)); ?>" />
						  
						  <script language="javascript" type="text/javascript">
								tradeFields.push("indexPrice");
							</script>
						 </td>
			          </tr>
					  <?php
					  }
					  ?>
                      
					  <?php
					  if($templateOptions[4] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Buyer Price: </td>
				        <td>
                          <input name="buyerPrice" type="text" id="buyerPrice" size="12" value="<?php echo($buyerPrice); ?>" />
						  
						  <script language="javascript" type="text/javascript">
								tradeFields.push("buyerPrice");
							</script>
						 </td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[5] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Buyer Currency: </td>
				        <td>
							<select name="buyerCurr" id="buyerCurr">
								 <?php
									populateCurrencyList("");						
								 ?>
							</select>
							
							<script language="javascript" type="text/javascript">
							tradeFields.push("buyerCurr");
						</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[6] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Seller Price: </td>
				        <td>
                          <input name="sellerPrice" type="text" id="sellerPrice" size="12" value="<?php echo($sellerPrice); ?>" />
						  <script language="javascript" type="text/javascript">
								tradeFields.push("sellerPrice");
							</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[7] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Seller Currency: </td>
				        <td><select name="sellerCurr" id="sellerCurr">
								 <?php
									populateCurrencyList($sellerCurrency);						
								  ?>
							</select>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("sellerCurrency");
							</script>
						</td>
			          </tr>
				      <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[8] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Option Style: </td>
				        <td>
							<select name="optionStyle" id="optionStyle">
                            <?php
								populateOptionStyle($optionStyle);						
							?>
                            </select>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("optionStyle");
							</script>
                        </td>
			          </tr>
				      <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[9] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Option Type: </td>
				        <td><select name="optionType" id="optionType">
                            <?php
								populateOptionType($optionType);						
							  ?>
                            </select>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("optionType");
							</script>
                        </td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[10] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Exercise Type: </td>
				        <td>
							<select name="exerciseType" id="exerciseType">
                              <?php
								populateExercise($exerciseType);						
							  ?>
                          </select>
						  
						  <script language="javascript" type="text/javascript">
							tradeFields.push("exerciseType");
						</script>
                        </td>
			          </tr>
				      <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[11] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Strike:</td>
				        <td>
                          <input name="strikePrice" type="text" id="strikePrice" size="12" value="<?php echo($strike); ?>" />
						  
						  <script language="javascript" type="text/javascript">
								tradeFields.push("strikePrice");
							</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[12] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Strike Currency: </td>
				        <td>
							<select name="strikeCurr" id="strikeCurr">
								 <?php
									populateCurrencyList($strikeCurrency);						
								  ?>
							</select>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("strikeCurr");
							</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>

                    </table></td>
				    <td valign="top"  class="spacer">&nbsp;</td>
				    <td valign="top"><table border="0" cellspacing="3" cellpadding="0">
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Start Date: </td>
				        <td class="yui-skin-sam"><input name="startDate" type="text" id="startDate" size="10" value="<?php echo(formatDate($startDate)); ?>" onchange="updateQuantity();" />
							<script language="javascript" type="text/javascript">
								tradeFields.push("startDate");
							</script>
							
							<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show2up" class="center" />
							<div id="cal2Container" class="adjust"></div>
						
							<script language="javascript" type="text/javascript">
					
								function mySelectHandler2(type,args,obj) {
									var dates = args[0]; 
									var date = dates[0]; 
									var year = date[0], month = date[1], day = date[2];
									
									if(month.toString().length == 1)
										month = "0" + month;
									if(day.toString().length == 1)
										day = "0" + day;
	
									var dateStr = month + "/" + day + "/" + year;								
									displayDate("startDate",dateStr);
									this.hide();
								};
	
								
								YAHOO.namespace("example.calendar2"); 
								 
								YAHOO.example.calendar2.init = function() { 
									YAHOO.example.calendar2.cal2 = new YAHOO.widget.Calendar("cal2","cal2Container", { title:"Choose a date:", close:true } );
									YAHOO.example.calendar2.cal2.selectEvent.subscribe(mySelectHandler2, YAHOO.example.calendar2.cal2, true);
									YAHOO.example.calendar2.cal2.render();
									 
									// Listener to show the single page Calendar when the button is clicked 
									YAHOO.util.Event.addListener("show2up", "click", YAHOO.example.calendar2.cal2.show, YAHOO.example.calendar2.cal2, true); 
								} 
								
								YAHOO.util.Event.onDOMReady(YAHOO.example.calendar2.init);
							
							</script>
						</td>
			          </tr>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">End Date: </td>
				        <td class="yui-skin-sam"><input name="endDate" type="text" id="endDate" size="10" value="<?php echo(formatDate($endDate)); ?>" onchange="updateQuantity();" />
							<script language="javascript" type="text/javascript">
								tradeFields.push("endDate");
							</script>
							
							<img src="../images/calendar.jpg" height="15" width="18" alt="Cal" id="show3up" class="center" />
							<div id="cal3Container" class="adjust"></div>
						
							<script language="javascript" type="text/javascript">
					
								function mySelectHandler3(type,args,obj) {
									var dates = args[0]; 
									var date = dates[0]; 
									var year = date[0], month = date[1], day = date[2];
									
									if(month.toString().length == 1)
										month = "0" + month;
									if(day.toString().length == 1)
										day = "0" + day;
	
									var dateStr = month + "/" + day + "/" + year;								
									displayDate("endDate",dateStr);
									this.hide();
								};
	
								
								YAHOO.namespace("example.calendar3"); 
								 
								YAHOO.example.calendar3.init = function() { 
									YAHOO.example.calendar3.cal3 = new YAHOO.widget.Calendar("cal3","cal3Container", { title:"Choose a date:", close:true } );
									YAHOO.example.calendar3.cal3.selectEvent.subscribe(mySelectHandler3, YAHOO.example.calendar3.cal3, true);
									YAHOO.example.calendar3.cal3.render();
									 
									// Listener to show the single page Calendar when the button is clicked 
									YAHOO.util.Event.addListener("show3up", "click", YAHOO.example.calendar3.cal3.show, YAHOO.example.calendar3.cal3, true); 
								}
								
								YAHOO.util.Event.onDOMReady(YAHOO.example.calendar3.init);
							
							</script>
						</td>
			          </tr>
				      
					  <?php
					  if($templateOptions[13] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel">Block:</td>
				        <td><select name="block" id="block" onchange="updateQuantity();">
							<?php
								populateBlock($block,$region,$location);
							  ?>
				          </select>
						  
						  <script language="javascript" type="text/javascript">						  
								tradeFields.push("block");
							</script>
				        </td>
					  </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[14] == "1"){
					  ?>
					  <tr>
                        <td nowrap="nowrap" class="fieldLabel" valign="top">Hours:</td>
				        <td id="hoursBlock">
							
							<?php if($block != 6) { ?>
							<input name="hours" type="text" id="hours" size="18" value="<?php echo($hours); ?>" readonly="readonly" class="readonly" />
							<?php
							}
							else {
								//echo("<script language=\"javascript\"> updateHours(1,".".$_POST["startDay"].".",".".$_POST["endDay"].".",".".$_POST["startHour"].".",".$endHour."); </script>");
								echo("<script language=\"javascript\"> updateHours(1,".$dayType.",".$startHour.",".$endHour."); </script>");
							}
							?>
							<script language="javascript" type="text/javascript">
								//tradeFields.push("hours");
							</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
					  <?php
					  if($templateOptions[15] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Time Zone: </td>
				        <td><input type="text" name="timezone" id="timezone" size="10" value="<?php echo($timezone); ?>" readonly="readonly" class="readonly" />
							<script language="javascript" type="text/javascript">
								//tradeFields.push("timezone");
							</script>						
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Unit Quantity: </td>
				        <td><input name="qty" type="text" id="qty" size="10" value="<?php echo($qty); ?>" onchange="updateQuantity();" />
							<?php echo($tradeUnits); ?>
							<script language="javascript" type="text/javascript">
								tradeFields.push("qty");
							</script>													
						</td>
			          </tr>
					  
					   <?php
					  if($templateOptions[16] == "1"){
					  ?>
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Unit Frequency: </td>
				        <td>
                            <?php
								populateFrequency($freq);						
							?>
							
							<script language="javascript" type="text/javascript">
								tradeFields.push("unitFreq");
							</script>
						</td>
			          </tr>
					  <?php
					  }
					  ?>
					  
				      <tr>
                        <td nowrap="nowrap" class="fieldLabel">Total Quantity: </td>
				        <td><input name="totalQty" type="text" id="totalQty" value="<?php echo($totalQty); ?>" size="12" onchange="updateQuantity();" /> <!-- readonly="readonly" class="readonly" -->
							<script language="javascript" type="text/javascript">
								//tradeFields.push("totalQty");
							</script>
						</td>
			          </tr>

                    </table></td>
			      </tr>
				</table>
			</div>
		</div>
		
		
		<div class="fieldGroup groupHalf" style="margin-right: 15px;">
			<div class="groupHeader">
				Buying Broker Information
			</div>
			<div class="groupContent">
            	<span class="fieldLabel">Total Commission:</span> $<input name="buyCommTotal" id="buyCommTotal" type="text" value="<?php echo($totalBuyComm); ?>" size="12" readonly="readonly" class="readonly" /> 
            	<?php // removed  onblur="splitCommission(this,'buy');" ?>
                 <input type="checkbox" name="evenSplitBuy" id="evenSplitBuy" onclick="splitEven('buy')" />
                 <label for="evenSplitBuy">Split Evenly</label>
               <br />
                <!--<span class="note">Amount will be calculated automatically for all brokers below.</span>-->
                <hr size="1" />
				
				<script language="javascript" type="text/javascript">						  
    				tradeFields.push("buyCommTotal");
    			</script>
            
        	    <table border="0" cellspacing="3" cellpadding="0" id="buyBrokerBlock">
				  <tr>
					<td class="fieldLabel">Buying Broker:</td>
					<td><select name="buyBroker" id="buyBroker">
					   <?php
					  	if($showClearID) {
							
							$buyBrokerSet = verifyBroker($tradeID,"buyer");
							$buyBroker = $buyBrokerSet[0];
							$buyBrokerRate = $buyBrokerSet[1];
							$buyBrokerComm = $buyBrokerSet[2];
						}
						
						populateBrokerList($buyBroker);						
					  	?>
					  </select>
					  
					  <script language="javascript" type="text/javascript">
							tradeFields.push("buyBroker");
						</script>
					</td>
				  </tr>
				  <tr class="">
					<td class="fieldLabel">Commission Rate: </td>
					<td nowrap="nowrap"><input name="buyBrokerRate" type="text" id="buyBrokerRate" size="10" value="<?php echo($buyBrokerRate); ?>" onchange="updateCommission('buy','');" />
						<input type="button" name="updateBuy" class="buttonSmall" value="Update Total" onclick="updateCommission('buy','');" />
						<script language="javascript" type="text/javascript">
							tradeFields.push("buyBrokerRate");
						</script>
					</td>
				  </tr>
				  <tr>
					<td class="fieldLabel">Total Commission: </td>
					<td>$<input name="buyBrokerTotal" type="text" id="buyBrokerTotal" size="12" value="<?php echo(formatDecimal2($buyBrokerComm,2)); ?>" onblur="verifyManualCommission2(this);" /><!--readonly="readonly" class="readonly"--></td>
				  </tr>
				</table>
                
                <div id="moreBuyBrokers"><?php if($tradeID != "") { getBrokers("buy",$transactionID,$showClearID,$tradeID,$status); } ?></div>
                
               <a id="addBuyBroker" href="javascript:void(0);" onclick="addBroker('moreBuyBrokers','');">+ Add Broker</a>
			</div>
		</div>
		
		<div class="fieldGroup groupHalf">
			<div class="groupHeader">
				Selling Broker Information
			</div>
		  <div class="groupContent">
          		<span class="fieldLabel">Total Commission:</span> $<input name="sellCommTotal" id="sellCommTotal" type="text" class="readonly" value="<?php echo($totalSellComm); ?>" size="12" readonly="readonly" /> 
          		<?php //removed    onblur="splitCommission(this,'sell');"  ?>
                 <input type="checkbox" name="evenSplitSell" id="evenSplitSell" onclick="splitEven('sell')" />
                 <label for="evenSplitSell">Split Evenly</label>
             <br />
                <!--<span class="note">Amount will be calculated automatically for all brokers below.</span>-->
                <hr size="1" />
				
				<script language="javascript" type="text/javascript">						  
    				tradeFields.push("sellCommTotal");
    			</script>
          
        		<table border="0" cellspacing="3" cellpadding="0" id="sellBrokerBlock">
				  <tr>
                    <td class="fieldLabel">Selling Broker:</td>
				    <td><select name="sellBroker" id="sellBroker">
                         <?php
						 	if($showClearID) {
						 		$sellBrokerSet = verifyBroker($tradeID,"seller");
								$sellBroker = $sellBrokerSet[0];
								$sellBrokerRate = $sellBrokerSet[1];
								$sellBrokerComm = $sellBrokerSet[2];
							}
						 
							populateBrokerList($sellBroker);						
						  ?>
                      </select>
					  
					  <script language="javascript" type="text/javascript">
						tradeFields.push("sellBroker");
					</script>
                    </td>
			      </tr>
				  <tr class="">
                    <td class="fieldLabel">Commission Rate: </td>
				    <td nowrap="nowrap"><input name="sellBrokerRate" type="text" id="sellBrokerRate" size="10" value="<?php echo($sellBrokerRate); ?>" onchange="updateCommission('sell','');" />
						<input type="button" name="updateBuy" class="buttonSmall" value="Update Total" onclick="updateCommission('sell','');" />
						
						<script language="javascript" type="text/javascript">
							tradeFields.push("sellBrokerRate");
						</script>
					</td>
			      </tr>
				  <tr>
                    <td class="fieldLabel">Total Commission: </td>
				    <td>$<input name="sellBrokerTotal" type="text" id="sellBrokerTotal" size="12" value="<?php echo(formatDecimal2($sellBrokerComm,2)); ?>" onblur="verifyManualCommission2(this);" /><!--readonly="readonly" class="readonly"--></td>
			      </tr>
				</table>
                
                <div id="moreSellBrokers"><?php if($tradeID != "") { getBrokers("sell",$transactionID,$showClearID,$tradeID,$status); } ?></div>
                
                <a id="addSellBroker" href="javascript:void(0);" onclick="addBroker('moreSellBrokers','');">+ Add Broker</a>
			</div>
		</div>
		
		<div class="clear"></div>
		
		
		<div>
			<div class="floatLeft">
				<?php
				if($tradeID != "" && $status != 3){
					?>
					<input type="button" name="cancel" value="Cancel Changes" onclick="confirmCancel('cancel these changes');" class="buttonRed" />
					<?php
				}
				else if($status == 3){
					//cancelled trade, show nothing
				}
				else{
					?>
					<input type="button" name="cancel" value="Cancel" onclick="confirmCancel('cancel this trade');" class="buttonRed" />
					<?php
				} 
				?>
			</div>
			<div class="floatRight">
				<?php				
				if($_GET["tradeID"] != "" && $status != 3 && $_GET["copyTrade"] != 1){
					//modify an existing trade
					//if(isAdmin() && $status < 2){
						?>
						<input type="button" id="adminUpdate" value="Admin Update &gt;" onclick="submitAdminUpdate();" class="buttonRed" />
                        <input type="hidden" name="addlDeleteIDs" id="addlDeleteIDs" value="" />
                        <input type="hidden" name="addlDeleteTypes" id="addlDeleteTypes" value="" />
                        
						<!--<input type="hidden" name="adminAction" value="overrideUpdate" />-->
						<?php
					//}
					?>
					<input type="button" name="copyTrade" value="Create Copy" class="buttonGreen" onclick="createCopy(<?php echo($_GET["tradeType"].",".$_GET["tradeID"]); ?>);" />
					<input type="submit" id="updateTrade" value="Update Trade &gt;" class="buttonGreen" />
					<input type="hidden" name="action" value="updateTrade" />
					<?php
				}
				else if($status == 3 && $_GET["copyTrade"] != 1){
					//cancelled trade, show close window button
					?>
					<input type="button" name="cancel" value="Close Window" onclick="confirmCancel('close this window');" class="buttonBlue" />
					<?php
				}
				else {
					//enter new trade
					?>
					<input type="submit" id="enterTrade" value="Enter Trade &gt;" class="buttonGreen" />
					<input type="hidden" name="action" value="submitTrans" />
					<?php
				}
				?>
			</div>
		</div>
		
		<div class="clear"></div>
		
		
		<input type="hidden" name="tradeType" value="<?php echo($_GET["tradeType"]); ?>" />
		
		</form>
		
		<?php
		if($auditCount > 0){
		?>
			</div>
			<div id="tab1_content" style="display: none;">
			
				<?php showAuditHistory($auditResult,$transactionID); ?>	
			
			</div>
		</div>
		<script language="javascript" type="text/javascript">
			activeTab = $("#tab0");
		</script>
		<?php
		}
		?>
		


	<?php
		if($_GET["tradeID"] != ""){
			//ajax call to show buyer/seller address info
			echo("<script language=\"javascript\" type=\"text/javascript\">\n");
			echo("showDetails($(\"#buyer\"));\n");
			echo("showDetails($(\"#seller\"));\n");
			if($block != 6)
				//echo("updateQuantity();\n"); //removed on 2-7-12
			echo("</script>\n");
		}	
	}
	else {
		
	?>

		<p class="errorMessage">No trade type found.</p>
		
	<?php
	}
	?>
	
	
	<?php
	require_once("../util/popupBottom.php");
	?>