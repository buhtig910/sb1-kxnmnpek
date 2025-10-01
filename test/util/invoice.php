<?php

require_once("loggingObj.php");

function sendInvoiceEmail($invoiceID) {
	
	// generate email
	require_once "class.phpmailer.php";
	$mail = new PHPMailer;
	
	//$queryInvoiceEmail = "SELECT c.fldEmail from tblB2TClient c, tblB2TInvoiceTrans it, tblB2TTransaction t WHERE fkInvoiceID=".$invoideID." AND it.fkTransactionID=t.TransactionID AND t.fkBuyerID t.fkSellerID
	
	$query = "SELECT * FROM tblB2TInvoice WHERE fldInvoiceNum=".$_GET['invoiceID'];
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
	
	//insert client bill info here
	//old: (changed 5/31/23) $clientQuery = "SELECT DISTINCT fldInvoiceName,fldCompanyName,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldEmail FROM tblB2TClient c, tblB2TTransaction t WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=".$resultSet[4]." AND fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID AND c.fkCompanyID=".$resultSet[4]." AND fldSellBrokerComm > 0))";
	$clientQuery = "SELECT co.fldInvoiceName,co.fldCompanyName,co.fldStreet1,co.fldStreet2,co.fldCity,co.fldState,co.fldZip,co.fldInvoiceEmail,co.fldExcelInvoice FROM tblB2TCompany co WHERE co.pkCompanyID=".$resultSet[4];
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
	8 = excel invoice flag
	*/
	
	$to = "";
	
	if($_SESSION["PROD"] == true) {
		$to = $clientInfo[7];
		$mail->addAddress($clientInfo[7]);
		$mail->addCC("jake@greenlightcommodities.com");
		$mail->addBCC("dennisddx@gmail.com");
		//$mail->addAddress("chrisbalduino@gmail.com");
		$mail->Subject = "Greenlight Commodities - Trade Invoice #".$invoiceID;
	}
	else {
			$to = "jake+invoice@greenlightcommodities.com";
	$mail->addAddress("jake+invoice@greenlightcommodities.com");
		$mail->Subject = "[TEST] Greenlight Commodities - Trade Invoice #".$invoiceID;
	}	
	
	$mail->setFrom('jake@greenlightcommodities.com', 'Greenlight Commodities');
	$mail->addReplyTo('jake@greenlightcommodities.com', 'Greenlight Commodities');
	$mail->isHTML(true);
	
	
	// generate PDF invoice always
	require_once("genPDF.php");
	
	//Generate PDF
	$pdfdoc = generatePDFInvoice($invoiceID);
	$filename = "Trade Invoice #".$invoiceID.".pdf";
	// attachment name
	$filepath = "../temp/".$filename;
	$fp = fopen($filepath, 'w');
	fwrite($fp, $pdfdoc);
	fclose($fp);
	$mail->addAttachment($filepath, $filename);
	
	
	//check if Excel invoice also needed
	if($clientInfo[8] == 1) {
		require_once("genXLS.php");
		$filename2 = "Trade Invoice #".$invoiceID.".xlsx";
		$filepath2 = "../temp/".$filename2;
		
		generateXLSInvoice($resultSet[0]);
		$mail->addAttachment($filepath2, $filename2);
	}
	
	// attachment name
	//$separator = md5(time());
	//$eol = PHP_EOL;
	
	$message = "This is an automated email from Greenlight Commodities. Your invoice is attached.<br><br>";
	$message.= "-------------------------------------------------<br>";
	$message.= "Greenlight Commodities facilitates transactions for our clients as an intermediary and is neither the issuer, guarantor, or counterparty in this transaction. Greenlight Commodities, its employees (collectively \"Greenlight Commodities\") shall have no liability in the event either party fails to comply with the intricacies of  this transaction or this transaction itself.\n\n";
	$message.= "You acknowledge and agree that all terms and conditions of this transaction were determined and agreed to solely by you an your counterparty and compliance therewith is the responsibility of you and your counterparty. You further agree to hold Greenlight Commodities harmless from any claims, loss or damage, in connection with or arising out of any dispute regarding this transaction.\n\n";
	$message.= "Any errors or differences must be reported immediately to Greenlight Commodities at 561-339-4032.   Your failure to notify us of such errors or differences will be deemed your agreement that this confirmation of transaction is correct and ratification of the transaction reported herein. You understand that Greenlight Commodities in its sole discretion may record and retain, on tape of otherwise, any telephone conversation between Greenlight Commodities and you.<br>";
	$message.= "-------------------------------------------------";

	$mail->Body = $message;

/*	// main header (multipart mandatory)
	$headers  = "From: ".$from.$eol;
	$headers .= "MIME-Version: 1.0".$eol; 
	$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"".$eol.$eol; 
	$headers .= "Content-Transfer-Encoding: 7bit".$eol;
	$headers .= "This is a MIME encoded message.".$eol.$eol;

	// message
	$headers .= "--".$separator.$eol;
	$headers .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
	$headers .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
	$headers .= $message.$eol.$eol;
	
	// attachment
	$headers .= "--".$separator.$eol;
	$headers .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol; 
	$headers .= "Content-Transfer-Encoding: base64".$eol;
	$headers .= "Content-Disposition: attachment".$eol.$eol;
	$headers .= $attachment.$eol.$eol;
	$headers .= "--".$separator."--";*/
	
	// send message
	if($_SESSION["testScript"] == 0){
		if(!$mail->send()) {
			echo("<br>Error sending email to: (list)");
				$logger = new Logger();
				$logger->logAction("Send Invoice Email","ERROR: unable to send invoice $resultSet[1]-$resultSet[2] to $to. Mailer Error: ". $mail->ErrorInfo,$resultSet[0]);
		} else {
			//echo 'Message has been sent';
			echo("<br>Email sent to: ".$to);
			$logger = new Logger();
			$logger->logAction("Send Invoice Email","SUCCESS: send confirm $resultSet[1]-$resultSet[2] to $to",$resultSet[0]);
		}
	}
	
	//delete the temp PDF file
	unlink($filepath);
	unlink($filepath2);
	
}


function sendInvoice($invoiceID) {
	echo("<script language=\"javascript\" type=\"text/javascript\">\n");
		echo("ajaxSendInvoice(\"../util/ajax.php?sendInvoice=1&invoiceID=".$invoiceID."\");");
	echo("</script>");
}


?>