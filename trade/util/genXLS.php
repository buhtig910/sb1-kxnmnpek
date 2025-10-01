<?php
/*****  use URL param test=1 to dump file to browser download  instead of file ****/


require("../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


require_once("confirmObj.php");
require_once("../vars.inc.php");
require_once("../functions.php");


if($_GET['test'] == 1)
	generateXLSInvoice($_GET['invoiceID']);   //used for direct testing


/*********************************
* GENERATE INVOICE
*
* input: invoideID
*********************************/
function generateXLSInvoice($invoiceID){
	
	//echo("<p>Invoice ID: ".$invoiceID."</p>");

	$query1 = "SELECT * FROM tblB2TInvoice WHERE pkInvoiceID=".$invoiceID;
	//echo("<p>".$query1."</p>");
	$result1 = mysqli_query($_SESSION['db'],$query1);
	$resultSet1 = mysqli_fetch_row($result1);
	/*
	0 = invoideID
	1 = date
	2 = invoice num
	3 = invoice total
	4 = fkcompanyID
	*/
	

	$query = "SELECT * FROM tblB2TInvoice WHERE fldInvoiceNum=".$resultSet1[2];
	//echo("<p>".$query."</p>");
	$result = mysqli_query($_SESSION['db'],$query);
	$resultSet = mysqli_fetch_row($result);
	/*
	0 = invoideID
	1 = date
	2 = invoice num
	3 = invoice total
	4 = fkcompanyID
	*/
	
	$today = date("m/d/Y");
	
	$startRange = date('m/d/Y', strtotime('first day of previous month'));
	$endRange = date('m/d/Y', strtotime('last day of previous month'));
	
	$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("../template/Broker_Invoice_Template.xlsx");
	$activeWorksheet = $spreadsheet->getActiveSheet();
	
	$activeWorksheet->setCellValue("B20", "Bosworth Brokers LLC dba Greenlight Commodities"); //broker
	$activeWorksheet->setCellValue("B21", ""); //broker lei
	$activeWorksheet->setCellValue("B22", $today); //invoice date
	$activeWorksheet->setCellValue("B23", $startRange ." - ". $endRange); //invoice period
	$activeWorksheet->setCellValue("B24", ""); //account description
	$activeWorksheet->setCellValue("B25", $resultSet1[2]); //invoice number
		
	/*
	$pdf->Cell(0,4, "Bosworth Brokers LLC",0,1,"R");
	$pdf->Cell(0,4, "dba Greenlight Commodities",0,1,"R");
	$pdf->Cell(0,4, "5571 Purple Meadow Lane",0,1,"R");
	$pdf->Cell(0,4, "Fulshear, TX 77441",0,1,"R");
	$pdf->Cell(0,4, "Tel: 561-339-4032",0,1,"R");
	$pdf->Cell(0,4, "Fax:                       ",0,1,"R");  */
	
	
	//BILL TO INFO		
	//insert client bill info here
	//old -- removed 5/31/23   $clientQuery = "SELECT DISTINCT fldInvoiceName,fldCompanyName,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldEmail FROM tblB2TClient c, tblB2TTransaction t WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=".$resultSet[4]." AND fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID AND c.fkCompanyID=".$resultSet[4]." AND fldSellBrokerComm > 0))";
	$clientQuery = "SELECT fldInvoiceName,fldCompanyName,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldInvoiceEmail FROM tblB2TCompany WHERE pkCompanyID=".$resultSet[4];
	//echo("<p>".$clientQuery."</p>");
	$clientResult = mysqli_query($_SESSION['db'],$clientQuery);
	$clientInfo = mysqli_fetch_row($clientResult);
	/*
	0 = invoice name
	1 = company name
	2 = street 1
	3 = street 2
	4 = city
	5 = state
	6 = zip
	7 = email
	*/
	
	$rowPointer = 30;
	
	$activeWorksheet->setCellValue("A13", $clientInfo[1]); //company name
	$activeWorksheet->setCellValue("A14", $clientInfo[2]); //street 
	$activeWorksheet->setCellValue("A15", $clientInfo[4].", ".$clientInfo[5]." ".$clientInfo[6]); //city, state, zip
	if($clientInfo[3] != "") {
		$activeWorksheet->insertNewRowBefore(15);
		$activeWorksheet->setCellValue("A15", $clientInfo[3]); //street 2
		$rowPointer = $rowPointer + 1;
	}
	
	
	/**** MODIFY BELOW HERE ******/
	
	
	$queryTrades = "SELECT fldTransactionNum,fldLegID,fldConfirmDate,fkBuyerID,fkSellerID,fkProductGroup,fkProductID,fkProductTypeID,fldLocation,fldBuyerPrice,fldBuyerCurrency,fldStartDate,fldEndDate,fldUnitQty,fldTotalQuantity,fldBuyBrokerComm,fldSellBrokerComm,fldBuyBrokerRate,fldSellBrokerRate,pkTransactionID,fkBuyingBroker,fkSellingBroker,fldClearID,fldStrike,fkTypeID FROM tblB2TTransaction t, tblB2TInvoice i, tblB2TInvoiceTrans it WHERE it.fkTransactionID=t.pkTransactionID AND it.fkInvoiceID=i.fldInvoiceNum AND i.fldInvoiceNum=".$resultSet1[2];
	//echo($queryTrades);
	$resultTrades = mysqli_query($_SESSION['db'],$queryTrades);
	$count = mysqli_num_rows($resultTrades);
	$total = 0;
	
	while($tradeDetails = mysqli_fetch_row($resultTrades)){
		/*
		0 = transNum
		1 = leg ID
		2 = confirm date
		3 = buyerID
		4 = sellerID
		5 = product group
		6 = product
		7 = product type
		8 = location
		9 = buyer price
		10= buyer currency
		11= start date
		12= end date
		13= unit qty
		14= total qty
		15= buy broker comm
		16= sell broker comm
		17= buy broker rate
		18= sell broker rate
		19 = transactionID
		20 = fkBuyingBroker
		21 = fkSellingBroker
		22 = clearID
		*/
		
		$showUnits = getTradeUnits($tradeDetails[5]);
				
		$queryName = "SELECT fldCompanyName FROM tblB2TClient WHERE pkClientID=".$tradeDetails[3];
		$resultName = mysqli_query($_SESSION['db'],$queryName);
		@list($buyCompanyName) = mysqli_fetch_row($resultName);
		
		$queryName = "SELECT fldCompanyName FROM tblB2TClient WHERE pkClientID=".$tradeDetails[4];
		$resultName = mysqli_query($_SESSION['db'],$queryName);
		@list($sellCompanyName) = mysqli_fetch_row($resultName);
		
		$clearID = explode(" ",$tradeDetails[22]); //parse clear ID from other timestamp info manually entered into field
		
		// Get custom commodity fields for New Product trades
		$customCommodityFields = null;
		$tradeTypeID = $tradeDetails[23]; // fkTypeID from the query
		
		// Check if this is a New Product trade type
		$newProductQuery = "SELECT pkTypeID FROM tblB2TTradeType WHERE fldTradeName IN ('New Product 1', 'New Product 2')";
		$newProductResult = mysqli_query($_SESSION['db'], $newProductQuery);
		$newProductTypeIDs = array();
		if($newProductResult && mysqli_num_rows($newProductResult) > 0) {
			while($row = mysqli_fetch_row($newProductResult)) {
				$newProductTypeIDs[] = $row[0];
			}
		}
		
		// If this is a New Product trade, get the custom fields
		if(in_array($tradeTypeID, $newProductTypeIDs)) {
			// Custom commodity fields removed - table not being used properly
			$resultCustomCommodity = mysqli_query($_SESSION['db'],$queryCustomCommodity);
			@$customCommodityFields = mysqli_fetch_row($resultCustomCommodity);
		}
		
		$showCounterParty = "";
		if(intval($clearID[0]) >= 1) {
			$showCounterParty = $sellCompanyName; //if cleared trade, show cleared counter party
		}
		
		$activeWorksheet->setCellValue("A".($rowPointer), formatDate($tradeDetails[2])); //trade date
		$activeWorksheet->setCellValue("B".($rowPointer), $tradeDetails[0]."-".$tradeDetails[1]); //broker confirm number
		$activeWorksheet->setCellValue("D".($rowPointer), $clearID[0]); //clear ID
		$activeWorksheet->setCellValue("E".($rowPointer), formatDate($tradeDetails[11])); //start date
		$activeWorksheet->setCellValue("F".($rowPointer), formatDate($tradeDetails[12])); //end date
		// For New Product trades, use custom commodity fields
		if($customCommodityFields) {
			$productName = getProduct($customCommodityFields[1]); // Get product name from ID
			$locationName = getLocationList($customCommodityFields[2]); // Get location name from ID
			$offsetIndex = getOffsetList($customCommodityFields[3]); // Get offset index name from ID
			$region = $customCommodityFields[4];
			$commodityType = ucfirst($customCommodityFields[5]);
			$unitType = $customCommodityFields[9]; // MMBTU/Block
			
			$activeWorksheet->setCellValue("G".($rowPointer), $locationName." - ".$commodityType); //trade type with custom fields
			$activeWorksheet->setCellValue("H".($rowPointer), $productName." (".$unitType.")"); //product with unit type
		} else {
			$activeWorksheet->setCellValue("G".($rowPointer), getLocationList($tradeDetails[8])." - ".getProductType($tradeDetails[7])); //trade type
			$activeWorksheet->setCellValue("H".($rowPointer), getProduct($tradeDetails[6])); //product
		}
		$activeWorksheet->setCellValue("I".($rowPointer), $showCounterParty); //counterparty
		
		if($tradeDetails[23] !="") {
			//if option, then use Strike price. Premium is the cost of the option
			$activeWorksheet->setCellValue("J".($rowPointer), $tradeDetails[23]); //price/strike
			$activeWorksheet->setCellValue("K".($rowPointer), $tradeDetails[9]); //price/strike
		}
		else {
			//otherwise, use price as is
			$activeWorksheet->setCellValue("J".($rowPointer), $tradeDetails[9]); //price/strike
		}
		
		$activeWorksheet->setCellValue("L".($rowPointer), $tradeDetails[14]); //volume
		$activeWorksheet->setCellValue("N".($rowPointer), $showUnits); //units
		$activeWorksheet->setCellValue("R".($rowPointer), "???"); //invoice CCY
		$activeWorksheet->setCellValue("S".($rowPointer), ""); //VAT if applicable
		$activeWorksheet->setCellValue("T".($rowPointer), ""); //GST if applicable
		
		
		if($tradeDetails[15] != "0") { //buy broker side
			//$pdf->Cell($col_3,5,getLocationList($tradeDetails[8])." - ".getProductType($tradeDetails[7])." | Price: ".formatCurrency($tradeDetails[9],2,1)." (".$tradeDetails[14]." @ $".$tradeDetails[17].") | Trader: ".getTraderName($tradeDetails[3]),0,0);
			
			$activeWorksheet->setCellValue("M".($rowPointer), $tradeDetails[17]); //comm rate
			$activeWorksheet->setCellValue("O".($rowPointer), "Buy"); //buy/sell
			$activeWorksheet->setCellValue("P".($rowPointer), getTraderName($tradeDetails[3])); //trader
			$brokerFees = $tradeDetails[15] + getAdditionalBrokerFees($tradeDetails[19]);
			$activeWorksheet->setCellValue("Q".($rowPointer), $brokerFees); //amount due
			$total += $tradeDetails[17];
		}
		else if($tradeDetails[16] != "0") { //sell broker side
			//$pdf->Cell($col_3,5,getLocationList($tradeDetails[8])." - ".getProductType($tradeDetails[7])." | Price: ".formatCurrency($tradeDetails[9],2,1)." (".$tradeDetails[14]." @ $".$tradeDetails[18].") | Trader: ".getTraderName($tradeDetails[4]),0,0);
			
			$activeWorksheet->setCellValue("M".($rowPointer), $tradeDetails[18]); //comm rate
			$activeWorksheet->setCellValue("O".($rowPointer), "Sell"); //buy/sell
			$activeWorksheet->setCellValue("P".($rowPointer), getTraderName($tradeDetails[4])); //trader
			$brokerFees = $tradeDetails[16] + getAdditionalBrokerFees($tradeDetails[19]);
			$activeWorksheet->setCellValue("Q".($rowPointer), $brokerFees); //amount due			
			$total += $tradeDetails[18];
		}
		
		$activeWorksheet->setCellValue("R".($rowPointer), "USD"); //Invoice CCY
		
		$activeWorksheet->insertNewRowBefore($rowPointer+1);
		$rowPointer = $rowPointer + 1;
		
	}
	
	$activeWorksheet->setCellValue("Q".($rowPointer+1), $resultSet[3]); //total

	
	/*******   MODIFY ABOVE HERE ************/	
		
	$writer = new Xlsx($spreadsheet);
	
	if($_GET['test'] == 1) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
		$writer->save('php://output');
	}
	else {
		$writer->save("../temp/Trade Invoice #".$resultSet1[2].".xlsx");
		//return "../temp/Trade Invoice #".$resultSet1[2].".xlsx";
	}
}


?>