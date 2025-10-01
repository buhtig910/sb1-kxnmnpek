<?php
@session_start();

require_once("loggingObj.php");


function sendConfirmEmail($resultSet,$brokerID,$email,$resendType) {

	// Check if we can access the database
	if(!isset($_SESSION['db'])) {
		return false;
	}

	// email stuff (change data below)
	//$to = $email;
	//$from = "Greenlight Commodities <dennis@greenlightcommodities.com>";
	
	$product = translateProduct($resultSet[7]);
	
	if($_SESSION["PROD"] == true)
		$subject = "Greenlight Commodities - Trade Confirmation";
	else 
		$subject = "[Test] Greenlight Commodities - Trade Confirmation"; 
	
	// generate PDF
	require_once("genPDF.php");
	
	//Generate PDF
	$pdfdoc = generatePDFConfirm($resultSet,"",$resendType);
	//$attachment = chunk_split(base64_encode($pdfdoc));
	
	// Check if PDF was generated successfully
	if(empty($pdfdoc)) {
		$logger = new Logger();
		$logger->logAction("Send Confirm Email","ERROR: PDF generation failed for $resultSet[1]-$resultSet[2]",$resultSet[0]);
		return false;
	}
	
	// attachment name
	$filename = "Trade Confirm - ".$resultSet[1]."-".$resultSet[2].".pdf";
	$filepath = dirname(__FILE__) . "/../temp/" . $filename;
	$tempDir = dirname(__FILE__) . "/../temp/";
	
	// Check if temp directory exists and is writable
	if(!is_dir($tempDir)) {
		mkdir($tempDir, 0755, true);
	}
	
	$fp = fopen($filepath, 'w');
	if($fp === false) {
		$logger = new Logger();
		$logger->logAction("Send Confirm Email","ERROR: Cannot create PDF file $filepath for $resultSet[1]-$resultSet[2]",$resultSet[0]);
		return false;
	}
	
	$bytesWritten = fwrite($fp, $pdfdoc);
	fclose($fp);
	
	if($bytesWritten === false || $bytesWritten == 0) {
		$logger = new Logger();
		$logger->logAction("Send Confirm Email","ERROR: Failed to write PDF data to $filepath for $resultSet[1]-$resultSet[2]",$resultSet[0]);
		return false;
	}
	
	// Simple email setup - no PHPMailer needed
	$toList = "";
	if($_SESSION["PROD"] == true) {
		//prod, send to proper recipients
		$toParts = explode(",",$email);
		for($i=0; $i < count($toParts); $i++) {
			$toList.= $toParts[$i].", ";
		}
	}
	
	// generate email using PHPMailer (which was working previously)
	require_once "class.phpmailer.php";
	
	// Create PHPMailer instance
	$mail = new PHPMailer();
	
	// Basic configuration - use default mail() function as transport
	$mail->isMail(); // Use PHP's mail() function as transport
	
	// Set proper encoding and character set
	$mail->CharSet = 'UTF-8';
	$mail->Encoding = '8bit';
	
	$mail->setFrom('jake@greenlightcommodities.com', 'Greenlight Commodities');
	$toList = "";
	if($_SESSION["PROD"] == true) {
		//prod, send to proper recipients
		$toParts = explode(",",$email);
		for($i=0; $i < count($toParts); $i++) {
			$mail->addAddress($toParts[$i]);
			$toList.= $toParts[$i].", ";
		}
		$mail->addAddress("confirms@greenlightcommodities.com");
	}
	else {
		//test
		$mail->addAddress("confirms@greenlightcommodities.com");
	}
	$mail->addReplyTo('jake@greenlightcommodities.com', 'Greenlight Commodities');	
	
	// Check if PDF file exists before adding as attachment
	if(file_exists($filepath) && filesize($filepath) > 0) {
		$mail->addAttachment($filepath, $filename);    // Add attachments
		$logger = new Logger();
		$logger->logAction("Send Confirm Email","SUCCESS: PDF file created and attached - $filepath (".filesize($filepath)." bytes)",$resultSet[0]);
	} else {
		$logger = new Logger();
		$logger->logAction("Send Confirm Email","ERROR: PDF file not found or empty - $filepath",$resultSet[0]);
	}
	$mail->isHTML(true);                             // Set email format to HTML
	$mail->Subject = $subject;
	
	$message = "<p>This is an automated email from Greenlight Commodities. Your trade confirm is attached.</p>";
	$message.= "<p>Thank you,<br>Greenlight Commodities<p>";
	$message.= "<p font-size='small'>-------------------------------------------------<br>";
	$message.= "Greenlight Commodities facilitates transactions for our clients as an intermediary and is neither the issuer, guarantor, or counterparty in this transaction. Greenlight Commodities, its employees (collectively \"Greenlight Commodities\") shall have no liability in the event either party fails to comply with the intricacies of  this transaction or this transaction itself.<br><br>";
	$message.= "You acknowledge and agree that all terms and conditions of this transaction were determined and agreed to solely by you an your counterparty and compliance therewith is the responsibility of you and your counterparty. You further agree to hold Greenlight Commodities harmless from any claims, loss or damage, in connection with or arising out of any dispute regarding this transaction.<br><br>";
	$message.= "Any errors or differences must be reported immediately to Greenlight Commodities at 561-339-4032. Your failure to notify us of such errors or differences will be deemed your agreement that this confirmation of transaction is correct and ratification of the transaction reported herein. You understand that Greenlight Commodities in its sole discretion may record and retain, on tape of otherwise, any telephone conversation between Greenlight Commodities and you.<br>";
	$message.= "-------------------------------------------------";
	$message.= "<br><br><br> | Greenlight Commodities | 5535 Memorial Drive, Suite F453 | Houston, TX 77007-8009 | Tel: 561-339-4032 |</p>";
	
	$mail->Body = $message;
	
	// send message
	if($_SESSION["testScript"] == 0){
		// Send using PHPMailer - this is the ONLY way to send PDF attachments
		if($mail->send()) {
			$logger = new Logger();
			$logger->logAction("Send Confirm Email","SUCCESS: send confirm $resultSet[1]-$resultSet[2] to $email via PHPMailer",$resultSet[0]);
		} else {
			$logger = new Logger();
			$logger->logAction("Send Confirm Email","ERROR: PHPMailer failed for $resultSet[1]-$resultSet[2] to $email: " . $mail->ErrorInfo,$resultSet[0]);
		}
	}
	
	//delete the temp PDF file
	unlink($filepath);
		
}


//UPDATE TRADE STATUS FROM CONFIRMED TO LIVE
function moveTradeLive($tradeID){
	$query = "UPDATE tblB2TTransaction SET fldStatus=".$_SESSION['CONST_LIVE']." WHERE pkTransactionID=$tradeID";
	//echo($query);
	$result = mysqli_query($_SESSION['db'],$query);
	@$count = mysqli_affected_rows();
	
	$query = "SELECT fldTransactionNum,fldLegID FROM tblB2TTransaction WHERE pkTransactionID=$tradeID";
	$result = mysqli_query($_SESSION['db'],$query);
	list($transNum,$leg) = mysqli_fetch_row($result);
	
	$query2 = "UPDATE tblB2TTransaction SET fldStatus=".$_SESSION['CONST_LIVE']." WHERE fldTransactionNum=$transNum AND fldLegID LIKE '%".substr($leg,0,1)."%'";
	$result2 = mysqli_query($_SESSION['db'],$query2);
	@$count2 = mysqli_affected_rows();
	
	$total = $count + $count2;
	
	if($count > 0)
		echo("<br>Trades moved live: ".($total));
}


function otherLegConfirmed($tradeID,$transNum,$legID,$fkBuyerID,$fkSellerID) {
	
	if($fkBuyerID != $_SESSION["NYMEX_ID"] && $fkBuyerID != $_SESSION["ICE_ID"] && $fkBuyerID != $_SESSION["NASDAQ_ID"] && $fkSellerID != $_SESSION["NYMEX_ID"] && $fkSellerID != $_SESSION["ICE_ID"] && $fkSellerID != $_SESSION["NASDAQ_ID"]) {
		//regular 2-leg trade
		$query = "SELECT fldStatus FROM tblB2TTransaction WHERE fldTransactionNum=$transNum ORDER BY pkTransactionID DESC LIMIT 2";
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		if($count == 2){
			$resultSet = mysqli_fetch_row($result);
			$resultSet2 = mysqli_fetch_row($result);
			
			if(($resultSet[0] == $_SESSION["CONST_CONFIRMED"] || $resultSet[0] == $_SESSION["CONST_LIVE"]) && ($resultSet2[0] == $_SESSION["CONST_CONFIRMED"] || $resultSet2[0] == $_SESSION["CONST_LIVE"]))
				return true;
			else
				return false;
		}
	}
	else if($fkBuyerID != $_SESSION["NYMEX_ID"] || $fkBuyerID != $_SESSION["ICE_ID"] || $fkBuyerID != $_SESSION["NASDAQ_ID"] || $fkSellerID != $_SESSION["NYMEX_ID"] || $fkSellerID != $_SESSION["ICE_ID"] || $fkSellerID != $_SESSION["NASDAQ_ID"]) {
		//4-leg cleared trade
		$query = "SELECT fldStatus FROM tblB2TTransaction WHERE fldTransactionNum=$transNum AND (fldStatus='".$_SESSION["CONST_CONFIRMED"]."' OR fldStatus='".$_SESSION["CONST_LIVE"]."') ORDER BY pkTransactionID DESC LIMIT 4";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		if($count == 4){
			$resultSet = mysqli_fetch_row($result);
			$resultSet2 = mysqli_fetch_row($result);
			
			if(($resultSet[0] == $_SESSION["CONST_CONFIRMED"] || $resultSet[0] == $_SESSION["CONST_LIVE"]) && ($resultSet2[0] == $_SESSION["CONST_CONFIRMED"] || $resultSet2[0] == $_SESSION["CONST_LIVE"]))
				return true;
			else
				return false;
		}
	}
	
	return false;
	
}


function resendTradeConfirm($tradeID,$resendType) {
	// Get trade data directly instead of making another AJAX call
	$query = "SELECT t.*, b.fldProductGroup FROM tblB2TTransaction t 
			  LEFT JOIN tblB2TTradeType b ON t.fkProductGroup=b.pkTypeID 
			  LEFT JOIN tblB2TProduct p ON t.fkProductID=p.pkProductID 
			  WHERE t.pkTransactionID=$tradeID";
	
	$result = mysqli_query($_SESSION['db'],$query);
	
	if($result && mysqli_num_rows($result) > 0) {
		$tradeData = mysqli_fetch_row($result);
		
		if($resendType == 1) {
			// Resend to me only - send to broker email
			$brokerQuery = "SELECT fldEmail FROM tblB2TBroker WHERE pkBrokerID=" . $_SESSION['brokerID42'];
			$brokerResult = mysqli_query($_SESSION['db'],$brokerQuery);
			@list($brokerEmail) = mysqli_fetch_row($brokerResult);
			
			if($brokerEmail) {
				sendConfirmEmail($tradeData, $_SESSION['brokerID42'], $brokerEmail, $resendType);
			}
		} else {
			// Resend to all parties - get proper recipients
			$queryClientEmail = "SELECT fldConfirmEmail FROM tblB2TClient WHERE pkClientID=";
			
			if($tradeData[31] > 0) { //buying
				$queryClientEmail .= $tradeData[5];
				$brokerID = $tradeData[31];
			}
			else if($tradeData[34] > 0) { //selling
				$queryClientEmail .= $tradeData[6];
				$brokerID = $tradeData[34];
			}
			
			//get email recipient for client
			$resultClientEmail = mysqli_query($_SESSION['db'],$queryClientEmail);
			@list($clientEmail) = mysqli_fetch_row($resultClientEmail);
			
			$queryBrokerEmail = "SELECT fldEmail FROM tblB2TBroker WHERE pkBrokerID=".$brokerID;
			$resultBrokerEmail = mysqli_query($_SESSION['db'],$queryBrokerEmail);
			@list($brokerEmail) = mysqli_fetch_row($resultBrokerEmail);
			
			$emailList = $brokerEmail;
			if($clientEmail != "")
				$emailList .= ", ".$clientEmail;
			
			sendConfirmEmail($tradeData, $brokerID, $emailList, $resendType);
		}
	}
}


?>