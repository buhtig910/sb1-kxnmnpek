<?php
session_start();

require_once("../vars.inc.php");
require_once("../functions.php");

if(!validateSecurity()) {
	die;
}

function printOutputRow($buyTotal,$buy,$sellTotal,$sell,$outputRow) {

	if( (($buyTotal - $buy) == 0) && (($sellTotal - $sell) == 0) )
		$pass = "";
	else
		$pass = "TOTALS DO NOT MATCH";
	
	//echo("\t\t\t\t\t\t\t\t\t\t\t".$buyTotal."\t".$sellTotal."\t".$pass);
	//$outputRow .= $buyTotal."\t".$sellTotal."\t".$pass."\n";
	
	echo($outputRow);
	
}

$query = "SELECT DISTINCT fldTransactionNum,fldLegID,fldClearID,fldTradeDate,c.fldTraderName,c.fldTraderName,IFNULL(b.fldName,0),fldBuyBrokerRate,fldBuyBrokerComm,IFNULL(bb.fldName,0),fldSellBrokerRate,fldSellBrokerComm,fldBuyCommTotal,fldSellCommTotal,pkTransactionID
	FROM tblB2TTransaction t 
	LEFT JOIN (SELECT pkBrokerID,fldName FROM tblB2TBroker) as b ON b.pkBrokerID=t.fkBuyingBroker
	LEFT JOIN (SELECT pkBrokerID,fldName FROM tblB2TBroker) as bb ON bb.pkBrokerID=t.fkSellingBroker
	LEFT JOIN (SELECT pkClientID,fldTraderName FROM tblB2TClient) as c ON c.pkClientID=t.fkBuyerID
	LEFT JOIN (SELECT pkClientID,fldTraderName FROM tblB2TClient) as cc ON c.pkClientID=t.fkSellerID
	WHERE fldTradeDate LIKE '".$_POST['year']."-".$_POST['month']."-%' AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")
	AND (fldBuyBrokerComm > 0 OR fldSellBrokerComm > 0)";
//echo($query."\n");
$result = mysqli_query($_SESSION['db'],$query);

//echo("Transaction\t Leg\t Trade Date\t Buyer\t Seller\t Buy Broker\t Buy Broker Comm\t Sell Broker\t Sell Broker Comm\t Buy TOTAL Comm\t Sell TOTAL Comm\t Calc. Buy Total\t Calc. Sell Total\t RESULT\n");
echo("Trade Date\t Transaction #\t Buy TOTAL Comm\t Sell TOTAL Comm\t Notes\n");

$last = 0;
$buyTotal = 0;
$sellTotal = 0;
$buy = 0;
$sell = 0;
$loopCount = 0;


while($data = mysqli_fetch_row($result)) {
	
	/*
	0 = transaction #
	1 = leg ID
	2 = clear ID
	3 = trade date
	4 = buy client name
	5 = sell client name
	6 = buy broker name
	7 = buy broker rate
	8 = buy broker comm
	9 = sell broker name
	10= sell broker rate
	11= sell broker comm
	12= buy comm total
	13= sell comm total
	14= transaction ID
	*/
	
	if($data[2] != "" && ($data[6] != "0" && $data[9] != "0")) {
		//cleared leg, ignore
	}
	else {

		if($data[0] != $last) { //$i > 0 &&   - removed on 5/3/12
			//reset everything, new trade
			
			printOutputRow($buyTotal,$buy,$sellTotal,$sell,$outputRow);
			
			$buyTotal = 0;
			$sellTotal = 0;
			$buy = 0;
			$sell = 0;
			$outputRow = "";
			$addlNote = "";
		}
		
		$buyTotal = 0;
		
		if($loopCount % 2 == 1) {
			//echo("reset buy to 0: ");
			
			$addlNote = "";
		}
		
		if($data[6] != "0")
			$buyTotal = $buyTotal + $data[8]; //calc running buy side total
		if($data[9] != "0")
			$sellTotal = $sellTotal + $data[11]; //calc running sell side total
		
		if($buy == 0)
			$buy = $data[12]; //set totals from db
		if($sell == 0)
			$sell = $data[13]; //set total from db
		
		//loop over individual trade data
		/*for($i=0; $i<count($data); $i++) {
			if($i == 2 || $i == 14 || $i==7 || $i==10) {} //ignore clearID
			else 
				echo($data[$i]."\t");
			
			if($i == (count($data)-1))
				echo("\n");
		}*/
		
		$outputRow .= formatDate($data[3])."\t".$data[0]."-".$data[1]."\t";
			
		//get addl brokers on specific trade
		$query2 = "SELECT fldName,fldBrokerComm,fldBuyer FROM tblB2TTransaction_Brokers tb, tblB2TBroker b WHERE tb.fkBrokerID=b.pkBrokerID AND tb.fkTransactionID=".$data[14];
		//echo($query2."\n\n");
		$result2 = mysqli_query($_SESSION['db'],$query2);
		
		//loop over addl brokers
		while($addlBroker = mysqli_fetch_row($result2)) {
			if($addlBroker[2] == 1) { //is addl buyer
				$buyTotal = $buyTotal + $addlBroker[1];
				//echo("  Additional Buy Broker --> \t\t\t\t\t");
				$addlNote = "(includes split buyer)";
			}
			else {
				$sellTotal = $sellTotal + $addlBroker[1];
				$buyTotal = 0;
				//echo("  Additional Sell Broker --> \t\t\t\t\t\t\t");
				$addlNote = "(includes split seller)";
			}
			
			//display addl broker parts
			/*for($j=0; $j<count($addlBroker); $j++) {				
				if($j == 2) {} //ignore some columns
				else
					echo($addlBroker[$j]."\t");
				
				if($j == (count($addlBroker)-1))
					echo("\n");
			}*/
				
		}
		
		$outputRow .= $buyTotal."\t".$sellTotal."\t".$addlNote."\n";
		
		$last = $data[0];
		$loopCount++;
		
	}
	
}

printOutputRow($buyTotal,$buy,$sellTotal,$sell,$outputRow);

?>