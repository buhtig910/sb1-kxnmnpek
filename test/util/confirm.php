<?php
@session_start();

require_once("loggingObj.php");


function sendConfirmEmail($resultSet,$brokerID,$email,$resendType) {

	// email stuff (change data below)
	//$to = $email;
	//$from = "Greenlight Commodities <jake@greenlightcommodities.com>";
	
	$product = translateProduct($resultSet[7]);
	
	if($_SESSION["PROD"] == true)
		$subject = "Greenlight Commodities - ".$product." Trade Confirmation";
	else 
		$subject = "[Test] Greenlight Commodities - ".$product." Trade Confirmation"; 
	
	// generate PDF
	require_once("genPDF.php");
	
	//Generate PDF
	$pdfdoc = generatePDFConfirm($resultSet,"",$resendType);
	//$attachment = chunk_split(base64_encode($pdfdoc));
	
	// attachment name
	$filename = "Trade Confirm - ".$resultSet[1]."-".$resultSet[2].".pdf";
	$filepath = "../temp/".$filename;
	$fp = fopen($filepath, 'w');
	fwrite($fp, $pdfdoc);
	fclose($fp);
	
	// generate email
	require_once "class.phpmailer.php";
	$mail = new PHPMailer;
	
	/*$mail->SMTPDebug = 3;                               // Enable verbose debug output
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'user@example.com';                 // SMTP username
	$mail->Password = 'secret';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;*/                                    // TCP port to connect to
	
	$mail->setFrom('jake@greenlightcommodities.com');
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
		//$mail->addAddress('chris@baldweb.com');     // Add a recipient
		$mail->addAddress("confirms@greenlightcommodities.com");
	}
	$mail->addReplyTo('jake@greenlightcommodities.com');	
	$mail->addAttachment($filepath, $filename);    // Add attachments
	$mail->isHTML(true);                             // Set email format to HTML
	$mail->Subject = $subject;
	
	$message = "<p>This is an automated email from Greenlight Commodities. Your trade confirm is attached.</p>";
	$message.= "<p>Thank you,<br>Greenlight Commodities<p>";
	$message.= "<p font-size='small'>-------------------------------------------------<br>";
	$message.= "Greenlight Commodities facilitates transactions for our clients as an intermediary and is neither the issuer, guarantor, or counterparty in this transaction. Greenlight Commodities, its employees (collectively \"Greenlight Commodities\") shall have no liability in the event either party fails to comply with the intricacies of  this transaction or this transaction itself.<br><br>";
	$message.= "You acknowledge and agree that all terms and conditions of this transaction were determined and agreed to solely by you an your counterparty and compliance therewith is the responsibility of you and your counterparty. You further agree to hold Greenlight Commodities harmless from any claims, loss or damage, in connection with or arising out of any dispute regarding this transaction.<br><br>";
	$message.= "Any errors or differences must be reported immediately to Greenlight Commodities at 561-339-4032. Your failure to notify us of such errors or differences will be deemed your agreement that this confirmation of transaction is correct and ratification of the transaction reported herein. You understand that Greenlight Commodities in its sole discretion may record and retain, on tape of otherwise, any telephone conversation between Greenlight Commodities and you.<br>";
	$message.= "-------------------------------------------------";
	$message.= "<br><br><br> Greenlight Commodities | 5535 Memorial Drive, Suite F453 | Houston, TX 77007-8009 | Tel: 561-339-4032 |</p>";
	
	$mail->Body = $message;
	//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	
	// send message
	if($_SESSION["testScript"] == 0){
		if(!$mail->send()) {
			echo("<br>Error sending email to: $email");
				$logger = new Logger();
				$logger->logAction("Send Confirm Email","ERROR: unable to send confirm $resultSet[1]-$resultSet[2] to $email. Mailer Error: ". $mail->ErrorInfo,$resultSet[0]);
		} else {
			//echo 'Message has been sent';
			echo("<br>Email sent to: $email");
			$logger = new Logger();
			$logger->logAction("Send Confirm Email","SUCCESS: send confirm $resultSet[1]-$resultSet[2] to $email",$resultSet[0]);
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
	echo("<script language=\"javascript\" type=\"text/javascript\">\n");
		echo("ajaxResendConfirm(\"util/ajax.php?resendConfirm=1&tradeID=".$tradeID."&resendType=".$resendType."\");");
	echo("</script>");
}


?>