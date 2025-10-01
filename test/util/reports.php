<?php
	session_start();
	header('Content-type: application/text');
	//header("Content-type: text/plain");  -- delete	
	
	// The PDF source is in original.pdf
	//readfile('original.pdf');
	
	require("../functions.php");
	require("../vars.inc.php");
	
	
	if($_GET["list"] == "client"){
		$query = "SELECT * FROM $tblClient";
		$query2 = "DESCRIBE $tblClient";
		header('Content-Disposition: attachment; filename="clients.txt"');
	}
	else if($_GET["list"] == "location"){
		$query = "SELECT * FROM $tblLocation";
		$query2 = "DESCRIBE $tblLocation";
		header('Content-Disposition: attachment; filename="locations.txt"');
	}
	else if($_GET["list"] == "broker"){
		$query  ="SELECT * FROM $tblBroker";
		$query2 = "DESCRIBE $tblBroker";
		header('Content-Disposition: attachment; filename="brokers.txt"');
	}
	else if($_GET["list"] == "trades" && $_POST["reportType"] == "old"){
		
		if($_POST["period2"] == "custom") {
			$query = "SELECT * FROM $tblTransaction WHERE fldConfirmDate >= '".reverseDate($_POST["startDate2"])."' AND fldConfirmDate <= '".reverseDate($_POST["endDate2"])."'";
		}
		else
			$query = "SELECT * FROM $tblTransaction WHERE fldConfirmDate LIKE '".$_POST["year"]."-".$_POST["month"]."-%'";
		
		//echo($query."<br><br>");
		$query2 = "DESCRIBE $tblTransaction";
		header('Content-Disposition: attachment; filename="transactions.txt"');
	}
	else if($_GET["list"] == "trades" && $_POST["reportType"] == "live") {
		header('Content-Disposition: attachment; filename="live_transactions.txt"');
		require("live_trade_pull.php");
		return;
	}
	else if($_GET["list"] == "trades" && $_POST["reportType"] == "splits") {
		header('Content-Disposition: attachment; filename="splits_transactions.txt"');
		require("splits_trade_pull.php");
		return;
	}
	else if($_GET["list"] == "prod"){
		$query = "SELECT * FROM $tblProduct";
		$query2 = "DESCRIBE $tblProduct";
		header('Content-Disposition: attachment; filename="products.txt"');
	}
	else if($_GET["list"] == "prodType"){
		$query = "SELECT * FROM $tblProductType";
		$query2 = "DESCRIBE $tblProductType";
		header('Content-Disposition: attachment; filename="product_types.txt"');
	}
	else if($_GET["list"] == "tradeType"){
		$query = "SELECT * FROM $tblTradeType";
		$query2 = "DESCRIBE $tblTradeType";
		header('Content-Disposition: attachment; filename="trade_types.txt"');
	}
	else if($_GET["list"] == "commission") {
		if(isAdmin()) {
			if($brokerID == "all")
				$query = "SELECT * FROM $tblTransaction WHERE (fkBuyingBroker=$brokerID OR fkSellingBroker=$brokerID) AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
			else
				$query = "SELECT * FROM $tblTransaction WHERE (fkBuyingBroker=$brokerID OR fkSellingBroker=$brokerID) AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].") AND (fkBuyingBroker=$brokerID OR fkSellingBroker=$brokerID)";
		}
		else 
			$query = "SELECT * FROM $tblTransaction WHERE (fkBuyingBroker=".$_SESSION['brokerID42']." OR fkSellingBroker=".$_SESSION["brokerID42"].") AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
		
		if($period == "YTD")
			$query.= " AND fldConfirmDate LIKE '".date('Y')."%'";
		else if($period == "YTM")
			$query.= " AND fldConfirmDate < '".date('Y-m')."-01'";
		else if($period == "current")
			$query.= " AND fldConfirmDate LIKE '".date('Y-m')."%'";
		else if($period == "last")
			$query.= " AND fldConfirmDate LIKE '".(date('Y-m')-1)."%'";
		else if($period == "custom")
			$query.= " AND (fldConfirmDate >= '".reverseDate($startDate)."' AND fldConfirmDate <= '".reverseDate($endDate)."')";
			
		$query2 = "DESCRIBE $tblTransaction";
		$cols = array("Buy Commission","Sell Commission");
		header('Content-Disposition: attachment; filename="commission_report.txt"');
	}
	
	
	$result = mysqli_query($_SESSION['db'],$query);
	$result2 = mysqli_query($_SESSION['db'],$query2);
	
	while($resultSet2 = mysqli_fetch_row($result2)){
		echo($resultSet2[0]."\t");
		//if($i < ($total-1))
		//	echo("\t");
	}
	
	echo("\n");
	
	while($resultSet = mysqli_fetch_row($result)){
		$total = count($resultSet);
		for($i=0; $i<$total; $i++){
			$output = str_replace("\r","",$resultSet[$i]);
			$output = str_replace("\n","",$output);
			$output = str_replace("\t","",$output);
			echo($output."\t");
			//if($i < ($total-1))
			//	echo("\t");
		}
		echo("\n");
	}

	//echo($query);
?>