<?php
session_start();

require_once("../vars.inc.php");
require_once("../functions.php");

if(!validateSecurity()) {
	die;
}

function printOutputRow($outputRow) {
	//$outputRow .= $buyTotal."\t".$sellTotal."\t".$pass."\n";
	
	echo($outputRow);
	
}

$query = "SELECT DISTINCT fldTransactionNum,fldLegID,fldClearID,fkProductGroup,fldTradeDate,IFNULL(b.fldName,0),fldBuyBrokerComm,IFNULL(bb.fldName,0),fldSellBrokerComm,d.*
	FROM tblB2TTransaction t 
	LEFT JOIN (SELECT pkBrokerID,fldName FROM tblB2TBroker) as b ON b.pkBrokerID=t.fkBuyingBroker
	LEFT JOIN (SELECT pkBrokerID,fldName FROM tblB2TBroker) as bb ON bb.pkBrokerID=t.fkSellingBroker
	LEFT JOIN (SELECT pkClientID,fldTraderName FROM tblB2TClient) as c ON c.pkClientID=t.fkBuyerID
	LEFT JOIN (SELECT pkClientID,fldTraderName FROM tblB2TClient) as cc ON c.pkClientID=t.fkSellerID
	LEFT JOIN (SELECT fldName,fldBrokerComm,fldBuyer,fkTransactionID FROM tblB2TTransaction_Brokers 
		LEFT JOIN tblB2TBroker on fkBrokerID=pkBrokerID) as d ON d.fkTransactionID=t.pkTransactionID
	WHERE fldConfirmDate LIKE '".$_POST['year']."-".$_POST['month']."-%' AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
	//echo($query."\n\n");
$result = mysqli_query($_SESSION['db'],$query);

echo("Transaction Number\t Product Group\t Trade Date\t Buying Broker 1\t Buy 1 Comm\t Buying Broker 2\t Buy 2 Comm\t Selling Broker 1\t Sell 1 Comm\t Selling Broker 2\t Sell 2 Comm\n");

$last = 0;
$loopCount = 0;
$outputRow = "";

while($data = mysqli_fetch_row($result)) {
	
	/*
	0 = transaction #
	1 = leg
	2 = clearID
	3 = prod group
	4 = trade date
	5 = buy broker
	6 = buy broker comm
	7 = sell broker
	8 = sell broker comm
	9 = add'l broker (add'l)
	10= add'l broker comm
	11= is buyer (flag)
	12= trans ID
	*/
		
	if($last != $data[0] && $last != 0) {
		printOutputRow($outputRow."\n");
		$loopCount = 0;
	}
		
	if($data[2] != "" && $data[6]== "0" && $data[8]== "0"  && $loopCount > 0) { //$data[9] == ""
		//skip it - cleared trade dummy record
		//$outputRow.= "skip: $loopCount\t";
	}
	else {
		//$outputRow.= "last:$last || data0:$data[0]\n";
		
		if($loopCount == 0) {
			//new trade, start new line
			$outputRow = $data[0]."\t".getProductGroup($data[3])."\t".formatDate($data[4])."\t";
			//buy side
			$outputRow.= $data[5]."\t".$data[6]."\t";
			if($data[11] == "1")
				$outputRow.= $data[9]."\t".$data[10]."\t"; //add'l buy broker
			//else
				//$outputRow.= "\t\t";
		}
		else {
			//same trade, add add'l brokers
			//sell side
			//$outputRow.= "sell side:";
			$outputRow.= $data[7]."\t".$data[8]."\t";
			
			if($data[11] == "0")
				$outputRow.= $data[9]."\t".$data[10]."\t"; //add'l sell broker
			else
				$outputRow.= "\t\t";		
		}
	}
	
	$last = $data[0];
	$loopCount++;
	
}

printOutputRow($outputRow);

?>