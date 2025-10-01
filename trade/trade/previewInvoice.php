<?php

@session_start();

require("../vars.inc.php");
require("../functions.php");

if( @validateSecurity() ) {
	
	require("../util/invoice.php");
	require("../util/genPDF.php");

	// Get the selected trade IDs from the form
	$selectedTrades = $_GET["tradeIDs"];
	$companyID = $_GET["companyID"];
	
	// Create a temporary invoice for preview
	$invoiceNum = time(); //generate unique invoice ID
	$totalValue = 0;
	
	// Process each selected trade
	$tradeIDs = explode(",", $selectedTrades);
	foreach($tradeIDs as $tradeID) {
		if($tradeID != "") {
			//get trade commission value
			$queryValue = "SELECT sum(fldBuyBrokerComm + fldSellBrokerComm) FROM tblB2TTransaction WHERE pkTransactionID=".$tradeID;
			$resultValue = mysqli_query($_SESSION['db'],$queryValue);
			@list($tradeValue) = mysqli_fetch_row($resultValue);
			
			$additionalBrokerFees = getAdditionalBrokerFees($tradeID);
			$totalValue += ($tradeValue + $additionalBrokerFees);
			
			// Also add to invoice transaction table for proper invoice generation
			$insertQuery = "INSERT INTO tblB2TInvoiceTrans VALUES ('',$invoiceNum,$tradeID,0)";
			mysqli_query($_SESSION['db'],$insertQuery);
		}
	}
	
	// Create temporary invoice record for preview
	$today = date("Y-m-d");
	$invoiceQuery = "INSERT INTO tblB2TInvoice VALUES ('',\"$today\",$invoiceNum,$totalValue,$companyID)";
	$invoiceResult = mysqli_query($_SESSION['db'],$invoiceQuery);
	
	// Generate the PDF invoice
	$_GET['invoiceID'] = $invoiceNum; // Set the invoice ID for the PDF generation
	
	try {
		// Generate PDF content first
		$pdfContent = generatePDFInvoice($invoiceNum);
		
		// Set proper headers for PDF display (must be before any output)
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="Invoice Preview.pdf"');
		header('Content-Length: ' . strlen($pdfContent));
		
		// Output the PDF content
		echo $pdfContent;
		
	} catch (Exception $e) {
		// If there's an error, show it as HTML
		echo "<p>Error generating PDF: " . htmlspecialchars($e->getMessage()) . "</p>";
	}
	
	// Clean up the temporary invoice records
	$cleanupQuery = "DELETE FROM tblB2TInvoice WHERE fldInvoiceNum=$invoiceNum";
	mysqli_query($_SESSION['db'],$cleanupQuery);
	$cleanupTransQuery = "DELETE FROM tblB2TInvoiceTrans WHERE fkInvoiceID=$invoiceNum";
	mysqli_query($_SESSION['db'],$cleanupTransQuery);

}

?>
