<?php

/********************
* Confirm Hub API integration
*
* http://www2.confirmhub.com/resources.aspx
********************/


if(file_exists("../../vars.inc.php")) {
	require_once("../../vars.inc.php");
	require_once("../../functions.php");
	require_once("../confirmObj.php");
	require_once("../companyObj.php");
	require_once("responseHandler.php");
}
else if(file_exists("../vars.inc.php")) {
	require_once("../vars.inc.php");
	require_once("../functions.php");
	require_once("confirmObj.php");
	require_once("companyObj.php");
	require_once("confirm_hub/responseHandler.php");
}
else {
	require_once("vars.inc.php");
	require_once("functions.php");
	require_once("util/confirmObj.php");
	require_once("util/companyObj.php");
	require_once("util/confirm_hub/responseHandler.php");
}


class ConfirmHub {

	//private members
	private $useSoap = false;
	private $runDate;
	private $useTimestamp;
	private $username = ""; //confirmHub username
	private $password = ""; //confirmHub password (updated every 90 days)
	private $xmlDealString = "";
	private $requestUri = "";
	private $ftpUri = "";
	private $passwordResetDate = "";
	private $dealsCount = 0;
	private $confirm;
	private $buyCo;
	private $sellCo;
	private $tradeDetails;
	private $soapObj; //soap client object
	private $responseHandler; //response code handler
	private $tradeIDs = array(); //array of trade IDs submitted
	private $submitterBidFlag;
	private $counterPartyBidFlag;
	private $tradeTypeID; //mapping value for CH
	private $dealType;
	
	
	/*********************
	* initConfirmHub
	*********************/
	public function initConfirmHub($datePeriod,$useTimestamp) {
		
		if(isset($datePeriod))
			$this->runDate = $datePeriod; //use defined date for manual override
		else
			$this->useTimestamp = $useTimestamp; //use timestamp for scheduled cron
		
		$query = "SELECT fldUsername,fldPassword,fldRequestUri,fldPasswordResetDate,fldFtpAddress FROM tblB2TConfirmHub WHERE ";
		
		if($_SESSION["PROD"] == false)
			$query.= "pkConfigID=2";
		else
			$query.= "pkConfigID=1";
		
		//echo($query."<br />");
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		if($count == 1) {
			@list($username,$password,$uri,$resetDate,$ftpUri) = mysqli_fetch_row($result);
			$this->username = $username;
			$this->password = $password;
			$this->requestUri = $uri;
			$this->passwordResetDate = $resetDate;
			$this->ftpUri = $ftpUri;
			
			$this->confirm = new Confirm(); //create confirm object for methods
			$this->responseHandler = new ResponseHandler(); //create response handler object
			
			//create soap client
			try {
				/*$this->soapObj = new SoapClient($this->requestUri,
							array('trace' => 1,
        						'exceptions' => true,
								'username' => $this->username,
								'password' => $this->password) );*/
								
				$this->soapObj = new SoapClient($this->requestUri);

			} catch (Exception $e) {
				echo("Soap init error: ".$e->getMessage());
			} 
			
			//change password every 90 days
			if( $this->passwordResetDate <= $_SESSION["today"] ) {
				//$this->updatePassword();
			}
						
		}
		else {
			return -1;
		}
	}
	
	
	/****************
	* saveDeals
	*
	* send trade data to API
	****************/
	public function saveDeals() {
		
		$this->buildXML();
		
		if(count($this->tradeIDs) > 0) {
			$this->sendTradeData(); //send the request
		}
	}
	
	
	/*************************
	* sendTradeData
	*
	* send formatted XML to API, call response handler
	*************************/
	private function sendTradeData() {
		//print_r( $this->soapObj->__getFunctions() );
		
		try {
			$submitString = $this->xmlDealString;
			
			//echo("////// START DUMP OF REQUEST XML //////\n\n");
			//print_r($submitString);
			$dealCount = count($this->tradeIDs);

			if($submitString != "") {
				$params = array("username" => $this->username,
								"password" => $this->password,
								"requestXML" => $submitString);
				/// SOAP SUBMISSION ///
				//$response = $this->soapObj->saveDeals( $params );
				//print_r($response);
							
				/// TEST CURL APPROACH ///
				/*$ch = curl_init("http://www.confirmhub.com/ns/ch/v6.3/ch.api/IAPIContract/saveDeals");
				//curl_setopt($ch, CURLOPT_HTTPHEADER, array("login: $this->username; password: $this->password;") );
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_USERPWD, "staging_bosworth_api:conf1rm23$");
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $submitString);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				curl_close($ch);
				print_r($output);  */
				
				echo("<p>FTP address: ".$this->ftpUri."</p>");
				echo("<p>Username: ".$this->username."</p>");
				
				/// FTP METHOD ///
				/*$conn_id = ftp_connect($this->ftpUri); // set up basic ssl connection
				
				if($conn_id != FALSE) {	
					$login_result = ftp_login($conn_id, $this->username, $this->password); // login with username and password
				}
				else {
					echo("<p>FTP connection failed</p>");
					exit;
				}*/
				
				//SFTP method //
				//alt uri could be: confirmhub.cmegroup.com
				$connection = ssh2_connect($this->ftpUri, 22);
				
				if(!ssh2_auth_password($connection, $this->username, $this->password)) {
					echo("<p>SFTP & FTP connection failed</p>");
					
					if(file_exists("../emailObj.php"))
						require("../emailObj.php");
					else
						require("emailObj.php");
						
					$email = new Email();
					if($_SESSION["PROD"] == false)
						$email->setTo("jake+tradeconfirm@greenlightcommodities.com");
					else
						$email->setTo("jake+tradeconfirm@greenlightcommodities.com");					
					$email->setFrom("\"Greenlight Commodities\" <jake+tradeconfirm@greenlightcommodities.com>");
					$email->setSubject("[Bosworth-ConfirmHub] FAILURE - ConfirmHub connection issue");
					$email->setMessageType("HTML");
					
					$tradeSummary = "<ul>";
					for($i=0; $i < count($this->tradeIDs); $i++)
						$tradeSummary .= "<li>".$this->tradeIDs[$i]."</li>";
					$tradeSummary .= "</ul>";
					
					$email->setBody("<p><b>Unable to connect to ConfirmHub.</b> An error occured trying to send <b>".$dealCount." trade legs</b> to ConfirmHub for the trade ID's below:</p>".$tradeSummary."<p>
						<i>Note: This is an automated email.</i></p>");
					if($_GET['test'] != 1)
						$email->sendMessage();
						
					exit;
				}
				$sftp = ssh2_sftp($connection);
				
				//echo ftp_pwd($conn_id);
				$xmlFilename = "confirm_hub_temp_".time().".xml";
				if(file_exists("../../temp/confirm_hub/"))
					$file = "../../temp/confirm_hub/".$xmlFilename;
				else
					$file = "../temp/confirm_hub/".$xmlFilename;
					
				$destination_file = "/usr/".$this->username."/".$xmlFilename;
				
				echo("<p>Total trades: ".$dealCount."</p>");				
				
				//$temp = preg_replace(array("/\n/")," ",$submitString);
				//$temp = preg_replace(array("/\t/"),"",$temp);
				//$temp = preg_replace(array("/>/"),">\n",$temp);
				//echo("XML: \n\n".$temp);
				
				//echo("XML: \n\n".$submitString);
				if($dealCount > 0)
					file_put_contents($file,$submitString); //write XML to file
					
				if(!isset($_GET['test']) && $dealCount > 0) {
					//$upload = ftp_put($conn_id, $destination_file, $file, FTP_ASCII);  // upload the file   ---- FTP_BINARY
					echo("<p>Source file: ".$file."<br>");
					echo("<p>Destination file: ".$destination_file."</p>");
					//$upload = ssh2_scp_send($connection, $file, $destination_file);  // upload the file   ---- FTP_BINARY
					//echo("<p>SERVER PATH: ssh2.sftp://$sftp$destination_file</p>");
					$resFile = fopen("ssh2.sftp://$sftp$destination_file", 'w');
					$srcFile = fopen($file, 'r');
					$writtenBytes = stream_copy_to_stream($srcFile, $resFile);
					fclose($resFile);
					fclose($srcFile);
					if($writtenBytes > 0)
						$upload = true;
					else
						$upload = false;
				}
				else {
					echo("<p>Source file: ".$file."<br>");
					echo("<p>IN TEST MODE, NO FILES UPLOADED</p>");
				}
				
				if (!$upload) {
					//ERROR
					echo("<h2 style=\"color: red;\">FTP upload of $file has failed!</h2> <br />");
					if(file_exists("../emailObj.php"))
						require("../emailObj.php");
					else
						require("emailObj.php");
						
					$email = new Email();
					if($_SESSION["PROD"] == false)
						$email->setTo("jake+tradeconfirm@greenlightcommodities.com");
					else
						$email->setTo("jake+tradeconfirm@greenlightcommodities.com");					
					$email->setFrom("\"Greenlight Commodities\" <jake+tradeconfirm@greenlightcommodities.com>");
					$email->setSubject("[Bosworth-ConfirmHub] FAILURE to send data to ConfirmHub");
					$email->setMessageType("HTML");
					
					$tradeSummary = "<ul>";
					for($i=0; $i < count($this->tradeIDs); $i++)
						$tradeSummary .= "<li>".$this->tradeIDs[$i]."</li>";
					$tradeSummary .= "</ul>";
					
					$email->setBody("<p>An error occured trying to send <b>".$dealCount." trade legs</b> to ConfirmHub for the trade ID's below:</p>".$tradeSummary."<p>
						<i>Note: This is an automated email.</i></p>");
					if($_GET['test'] != 1)
						$email->sendMessage();
				}
				else {
					//SUCCESS
					echo("<h2 style=\"color: green;\">SFTP upload successful!</h2>");
					if(file_exists("../emailObj.php"))
						require("../emailObj.php");
					else
						require("emailObj.php");
						
					$email = new Email();
					if($_SESSION["PROD"] == false)
						$email->setTo("jake+tradeconfirm@greenlightcommodities.com");
					else
						$email->setTo("jake+tradeconfirm@greenlightcommodities.com");					
					$email->setFrom("\"Greenlight Commodities\" <jake+tradeconfirm@greenlightcommodities.com>");
					$email->setSubject("[Bosworth-ConfirmHub] Trades sent to ConfirmHub");
					$email->setMessageType("HTML");
					
					$tradeSummary = "<ul>";
					for($i=0; $i < count($this->tradeIDs); $i++)
						$tradeSummary .= "<li>".$this->tradeIDs[$i]."</li>";
					$tradeSummary .= "</ul>";
					
					$email->setBody("<p>Successfully sent <b>".$dealCount." trade legs</b> to ConfirmHub for the trade ID's below:</p>".$tradeSummary."<p>
						<i>Note: This is an automated email.</i></p>");
					if($_GET['test'] != 1)
						$email->sendMessage();
				}
				//ftp_close($conn_id);// close the ssl connection
				
				
				//log API response
				$details = "Total Trades: ".$dealCount."\nTrade ID's: ".$this->listTradeIDs()."\n\nFTP Post: ".$file;
				$this->responseHandler->logApiResponse("saveDealsResult",$upload,0,"saveDeals",$details);
			}
			
		} catch(Exception $e) {	
			//log the exception thrown		
			$errorMsg = "Soap send error: ".$e."\t".$e->getMessage();
			echo("\n\n//// START DUMP OF API ERROR RESPONSE ///\n\n");
			echo($errorMsg);
			
			$details = "Trade ID's: ".$this->listTradeIDs();
			$this->responseHandler->logApiResponse("",$errorMsg ,0,"saveDeals_Exception",$details);
		}
		
	}
	

	/*****************
	* resetPassword
	*
	* store password in db, update every 90 days
	*****************/
	public function updatePassword($newPassword) {
		
		/*$newPw = "ConF1rm$".rand()."@";
		echo($newPw."<br><br>");
		
		try {
			$params = array('username' => $this->username,
							'password' => $this->password,
							'newPassword' => $newPw);
			$response = $this->soapObj->changePassword( $params );
			$details = "Username: ".$this->username."\nOld password: ".$this->password."\nNew password: ".$newPw;
			$this->responseHandler->logApiResponse("changePasswordResult",$response,0,"changePassword",$details);
			
			if( $this->responseHandler->getErrorCode() > 0 ) { //no errors, update db
				$newDate = date("Y-m-d",strtotime("+90 days"));
				$this->password = $newPw;
				$query = "UPDATE tblB2TConfirmHub SET fldPassword=\"$newPw\", fldPasswordResetDate=\"$newDate\" WHERE pkConfigID=".$_SESSION["CONFIRM_HUB_API"];
				//echo($query);
				$result = mysqli_query($_SESSION['db'],$query);
			}
			else {
				
			}
		} catch(Exception $e) {
			echo("Soap password error: ".$e->getMessage());
		}*/
		
		$newDate = date("Y-m-d",strtotime("+90 days"));
		$query = "UPDATE tblB2TConfirmHub SET fldPassword=\"".$newPassword."\", fldPasswordResetDate=\"$newDate\" WHERE pkConfigID=".$_SESSION["CONFIRM_HUB_API"];
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_affected_rows();
		if($count == 1)
			return true;
		else
			return false;		
		
	}
	
	
	/***************
	* updateDeals
	****************/
	public function updateDeals($requestXML) {
		
	}
	
	
	/****************
	* buildXML
	*
	* build XML request structure
	****************/
	private function buildXML() {
		
		$this->xmlDealString = "";
		
		if($this->useSoap) {
			$this->xmlDealString = "<?xml version=\"1.0\"?>
			<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ch=\"http://www.confirmhub.com/ns/ch/v6.3/ch.api\">
			<soapenv:Header/>
			<soapenv:Body>
				<ch:saveDeals>
					<ch:username>".$this->username."</ch:username>
					<ch:password>".$this->password."</ch:password>
					<ch:deals>";
		}
				/*$this->xmlDealString.= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";*/
				$this->xmlDealString.= $this->prepStr("<CHML SchemaVersion=\"6.3\">\n");
				$this->xmlDealString.= $this->prepStr("<Deals>\n");
				$this->xmlDealString.= $this->prepStr($this->getDeals());
				$this->xmlDealString.= $this->prepStr("</Deals>\n");
				$this->xmlDealString.= $this->prepStr("</CHML>");
				
			if($this->useSoap) {	
				$this->xmlDealString.= "</ch:deals>
				</ch:saveDeals>
				</soapenv:Body>
				</soapenv:Envelope>";
			}

	}
	
	private function prepStr($str) {
		
		if($this->useSoap)
			$str = htmlentities($str, ENT_QUOTES);
		//$temp = str_replace(array("\t","\n"),"",$temp);
		return $str;
		
	}
	
	
	/*****************
	* getDeals
	*
	* get deals for time period (TBD)
	*****************/
	private function getDeals() {
		//0 = by date
		//1 = by tradeID

		//$query = "SELECT * FROM tblB2TTransaction WHERE fldTradeDate='".$_SESSION["today"]."'";
		
		if(isset($this->runDate))
			$query = "SELECT * FROM tblB2TTransaction WHERE fldTradeDate='".$this->runDate."' AND fldStatus='".$_SESSION["CONST_LIVE"]."'";
		else
			$query = "SELECT * FROM tblB2TTransaction WHERE (fldTransactionNum>='".$this->useTimestamp."' OR fldLastModified>='".formatTimestamp2($this->useTimestamp)."') AND fldStatus='".$_SESSION["CONST_LIVE"]."'";
		echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		$dealsXML = "";
		
		if($count > 0) {
			echo("<p>Trades found, processing trades...</p>");
			
			$this->dealsCount = $count;
			while($tradeDetails = mysqli_fetch_row($result)){
				//loop over each trade and build XML
				$this->tradeDetails = $tradeDetails;				
				$this->submitterBidFlag = isBuyLeg($tradeDetails[2]);
				
				$query2 = "SELECT DISTINCT fkCompanyID FROM tblB2TClient WHERE (pkClientID=".$tradeDetails[5]." OR pkClientID=".$tradeDetails[6].")";
				//echo($query2."<br>");
				$result2 = mysqli_query($_SESSION['db'],$query2);
				@list($buyCo) = mysqli_fetch_row($result2);
				@list($sellCo) = mysqli_fetch_row($result2);
				
				if( in_array($buyCo,$_SESSION["CH_LIMIT"]) || in_array($sellCo,$_SESSION["CH_LIMIT"]) ) {
					//only submit for Exelon (66) or Luminant (207) for now - REMOVE EVENTUALLY
					//if($buyCo == "66" || $buyCo == "207" || $sellCo == "66" || $sellCo == "207") {
						
					if( $tradeDetails[9] == "2" || $tradeDetails[9] == "3" ) {
						//skip financial trades
					}
					else {					
						if($this->submitterBidFlag == 1)
							$this->counterPartyBidFlag = 0;
						else
							$this->counterPartyBidFlag = 1;
							
						if(translateProduct($tradeDetails[8]) == "Power")
							$commID = 2;
						else
							$commID = 1;
						
						$this->getTradeTypeID($commID,$tradeDetails[9],$tradeDetails[8],$tradeDetails[1]); //$commodityType,$dealType,$productID   $tradeDetails[7]
		
						$dealsXML.= $this->getDealXML($tradeDetails); //build deal XML
						array_push($this->tradeIDs,$tradeDetails[1]."-".$tradeDetails[2]); //add to trade ID's array for logging
					}
					
				} //can be removed once Exelon & Luminant aren't only co's
			}
		}
		else {
			//no deals to send, set count
			echo("<p>No live trades to submit to Confirm Hub for this date/time</p>");
			$this->dealsCount = 0;
		}
		
		return $dealsXML;
	}
	
	
	/***********************
	* getDealXML
	*
	* build XML for each deal
	***********************/
	private function getDealXML($tradeDetails) {
		
		$dealXml .= $this->buildDeal($tradeDetails);		
		$dealXml .= $this->buildSubmitter($tradeDetails);
		$dealXml .= $this->buildDeals($tradeDetails);
		$dealXml .= $this->buildSellBroker($tradeDetails);
		//$dealXml .= $this->buildExecutionSystem();
		$dealXml .= $this->buildBuyBroker($tradeDetails);
		$dealXml .= $this->buildSellingCo($tradeDetails);
		$dealXml .= $this->buildBuyingCo($tradeDetails);
		$dealXml .= $this->buildSeller($tradeDetails);
		$dealXml .= $this->buildBuyer($tradeDetails);
		$dealXml .= $this->buildCommodityType($tradeDetails);
		$dealXml .= "</Deal>";
			
		return $dealXml;

	}
	
	
	private function buildDeal($tradeDetails) {
		//DeliveryPoint for Physical trades only
		//TradeType is a lookup value
		//CommodityID is a lookup value, 2=Power
		//Timezone is lookup value NEED TO FINISH
		
		$cleared = 0;
		if(isCleared($tradeDetails[5]) || isCleared($tradeDetails[6]))
			$cleared = 1;
		
		if(isBuyLeg($tradeDetails[2])) { //check buy leg, get proper co info
			$coInfo = getCompanyInfo($tradeDetails[5]);
		}
		else {
			$coInfo = getCompanyInfo($tradeDetails[6]);
		}
		
		$pieces = explode(" ",getProductType($tradeDetails[9]));
		$this->dealType = $pieces[0];
		
		//TotalQuantityTypeID=\"".getTradeUnitsID($tradeDetails[7])."\"   removed 7/23/14, changed to -2
		
		$tempString = "<Deal Active=\"".tradeIsActive($tradeDetails[37])."\" 
			CommodityID=\"".getCommodityTypeID($tradeDetails[7])."\"
			Commodity=\"".getProductGroup($tradeDetails[7])."\"
			DealType=\"".$this->dealType."\"
			TradeTypeID=\"".$this->tradeTypeID."\"
			TradeType=\"".getProductGroup($tradeDetails[7])." ".getProductType($tradeDetails[9])." ".getProduct($tradeDetails[8])."\"
			CompanyAttn=\"".$coInfo[1]."\"
			CompanyContact=\"".$coInfo[0]."\"
			CompanyContactNumbers=\"Telephone ".$coInfo[7]."; Fax ".$coInfo[8]."\"
			TotalQuantity=\"".$tradeDetails[28]."\"
			TotalQuantityTypeID=\"-2\"
			TotalQuantityType=\"".getTradeUnits($tradeDetails[7])."\"
			TimeZoneID=\"2\"
			TimeZone=\"".$tradeDetails[30]."\"
			TradeDate=\"".formatTimestamp($tradeDetails[1])."\" \n";
			
		if(isPhysicalProductType($tradeDetails[9])) {
			$tempString.= " DeliveryPointCode=\"\"
				DeliveryPoint=\"".getLocationList($tradeDetails[11])."\"
				DeliveryPointID=\"-2\"
				AdditionalInfo=\"".$this->confirm->locationConfirmLanguage(getLocationList($tradeDetails[11]),getProductType($tradeDetails[9]))."\" "; //".$tradeDetails[11]."
		}
		
		if($tradeDetails[13] == "1") {
			$tempString .= " DeliveryQualityID=\"14\" 
				DeliveryQuality=\"EEI, Firm (LD)\" ";
		}
		
		$tempString.= " Cleared=\"$cleared\">";
		
		return $tempString;
	}
	
	/************************
	* buildSubmitter
	*
	************************/
	private function buildSubmitter($tradeDetails) {
		//bid flag: sell (0), buy (1)
		
		
		$tempString = "<Submitter
			SubmitterID=\"61\"
			SubmitterTypeID=\"1\"
			VersionID=\"1\"
			BidFlag=\"".$this->submitterBidFlag."\"
			SubmitterDealID=\"".$tradeDetails[1]."-".$tradeDetails[2]."\"
			LastModifiedDate=\"".formatTimestamp($tradeDetails[48])."\"
			CreatedDate=\"".formatTimestamp($tradeDetails[1])."\" />";
			
		return $tempString;
	}
	
	/***********************
	* buildDeals
	*
	***********************/
	private function buildDeals($tradeDetails) {
		
		/*$query = "SELECT fldCHUnitsID FROM tblB2TTradeType WHERE pkTypeID=".$tradeDetails[7];
		$result = mysqli_query($_SESSION['db'],$query);
		@list($unitsID) = mysqli_fetch_row($result);
		
		//UnitQuantityTypeID=\"".$unitsID."\"   removed 7/23/14, changed to -2
		*/
		
		$tempString = "<Periods>
					<Period 
						PeriodOrder=\"1\" 
						UnitQuantity=\"".$tradeDetails[26]."\" 
						UnitQuantityTypeID=\"-2\" 
						UnitQuantityType=\"".getTradeUnits($tradeDetails[7])."\" 
						StartDate=\"".$tradeDetails[23]."\" 
						EndDate=\"".$tradeDetails[24]."\" />
				</Periods>\n";
		
		return $tempString;
	}
	
	
	/******************
	* buildSellBroker
	*
	********************/
	private function buildSellBroker($tradeDetails) {
		//CompanyCommUnitID = mapped value for unit type, 7=MWh
		//CompanyCommCurrencyID = mapped value for currency, 2=USD
		
		$tempString = "<Broker BrokerageCompanyID=\"".$_SESSION['brokerageCompanyId']."\"";
		
		if($this->submitterBidFlag == 1) { //buy side
			$tempString.= " CompanyTotalAmountDue=\"".$tradeDetails[33]."\"
			CompanyComm=\"".$tradeDetails[33]."\"
			CompanyCommRate=\"".$tradeDetails[32]."\" "; //buy broker comm
		}
		else { //sell side
			$tempString.= " CompanyTotalAmountDue=\"".$tradeDetails[36]."\"
			CompanyComm=\"".$tradeDetails[36]."\"
			CompanyCommRate=\"".$tradeDetails[35]."\" "; //sell broker comm
		}
		
		$tempString.= " CompanyCommUnitID=\"7\"
			CompanyCommCurrencyID=\"2\"
			CompanyCommFlatRateFlag=\"0\"
			BrokerName=\"".getBrokerName($tradeDetails[34])."\"
			BrokerageCompanyAddress=\"".$_SESSION['co_street_address'].", ".$_SESSION['co_city'].", ".$_SESSION['co_state']." ".$_SESSION['co_zip']."\"
			BrokerageCompanyNumbers=\"Telephone ".$_SESSION['co_phone']."; Fax ".$_SESSION['co_fax']."\" />";
					
		return $tempString;
	}
	
	/**********************
	* executeSystem
	* 
	* build execute system XML
	**********************/
	private function buildExecutionSystem() {
		/*$tempString = "<ExecutionSystem SystemName=\"Greenlight Commodities\" 
			SystemTradeID=\"0\" 
			SystemTradeSubID=\"0\" 
			ExecutorRole=\"Aggressor\" />\n";*/
		
		//return $tempString;
	}
	
	
	/******************
	* buildBuyBroker
	*
	********************/
	private function buildBuyBroker($tradeDetails) {
		$tempString = "<CounterpartyBroker ParentBrokerageCompanyID=\"".$_SESSION['parentBrokerCompanyID']."\"
					ParentBrokerageCompany=\"".$_SESSION['co_name']."\"
					BrokerageCompanyID=\"".$_SESSION['brokerageCompanyId']."\"
					BrokerageCompany=\"".$_SESSION['co_name']."\" 
					BrokerageCompanyNumbers=\"Telephone ".$_SESSION['co_phone']."; Fax ".$_SESSION['co_fax']."\" 
					BrokerageCompanyAddress=\"".$_SESSION['co_street_address'].", ".$_SESSION['co_city'].", ".$_SESSION['co_state']." ".$_SESSION['co_zip']."\"
					BrokerDealID=\"1\"
					BrokerName=\"".getBrokerName($tradeDetails[31])."\" />\n";
					
		return $tempString;
	}
	
	
	/**********************
	* buildSellingCo (trading company)
	*
	* build buying company trade info for XML
	**********************/
	private function buildSellingCo($tradeDetails) {
		//companyID = $this->sellCo->getCompanyID()
				
		$tempString = "<TradingCompany ReferenceNumber=\"\" 
			BidFlag=\"".$this->submitterBidFlag."\"
			CompanyID=\"-2\"";
			
			if($this->submitterBidFlag == 1) { //buy side
				$this->buyCo = new Company();
				$this->buyCo->init($tradeDetails[5]);
				
				$tempString.= " CompanyCode=\"".$this->buyCo->getCompanyName()."\"
				Company=\"".$this->buyCo->getCompanyName()."\"
				CompanyTrader=\"".$this->buyCo->getTraderName()."\" 
				CompanyTraderNumbers=\"Telephone ".$this->buyCo->getConfirmPhone()."; Fax ".$this->buyCo->getConfirmFax()."\" />\n";
			}
			else { //sell side
				$this->sellCo = new Company();
				$this->sellCo->init($tradeDetails[6]);
		
				$tempString.= " CompanyCode=\"".$this->sellCo->getCompanyName()."\"
				Company=\"".$this->sellCo->getCompanyName()."\"
				CompanyTrader=\"".$this->sellCo->getTraderName()."\" 
				CompanyTraderNumbers=\"Telephone ".$this->sellCo->getConfirmPhone()."; Fax ".$this->sellCo->getConfirmFax()."\" />\n";
			}
			
		return $tempString;
	}
	
	/**********************
	* buildBuyingCo (counter party)
	*
	* build buying company trade info for XML
	**********************/
	private function buildBuyingCo($tradeDetails) {
		//$this->buyCo->getCompanyID()
				
		$tempString = "<Counterparty ReferenceNumber=\"\" 
			BidFlag=\"".$this->counterPartyBidFlag."\"
			CompanyID=\"-2\" ";
			
			if($this->submitterBidFlag == 0) { //sell side, so use buy as counter party
				$this->buyCo = new Company();
				$this->buyCo->init($tradeDetails[5]);
				
				$tempString.= " CompanyCode=\"".$this->buyCo->getCompanyName()."\"
				Company=\"".$this->buyCo->getCompanyName()."\"
				CompanyTrader=\"".$this->buyCo->getTraderName()."\" 
				CompanyTraderNumbers=\"Telephone ".$this->buyCo->getConfirmPhone()."; Fax ".$this->buyCo->getConfirmFax()."\" />\n";
			}
			else { //buy side, so use sell as counter party
				$this->sellCo = new Company();
				$this->sellCo->init($tradeDetails[6]);
				
				$tempString.= " CompanyCode=\"".$this->sellCo->getCompanyName()."\"
				Company=\"".$this->sellCo->getCompanyName()."\"
				CompanyTrader=\"".$this->sellCo->getTraderName()."\" 
				CompanyTraderNumbers=\"Telephone ".$this->sellCo->getConfirmPhone()."; Fax ".$this->sellCo->getConfirmFax()."\" />\n";
			}
			
		return $tempString;
	}
	
	/***********************
	* buildSeller
	*
	* build seller element of XML
	***********************/
	private function buildSeller($tradeDetails) {
		//index maps to reference table for power/nat gas index
		
		//old IndexLocation=\"'.$this->confirm->locationConfirmLanguage(getLocationList($tradeDetails[11]),getProductType($tradeDetails[9]))."\"
		
		$tempString = "<SellerPrice  
			IndexID=\"-2\"
			Index=\"".getLocationList($tradeDetails[11])." ".$this->trimProductType(getProductType($tradeDetails[9]))."\"
			IndexLocationID=\"-2\"
			IndexLocation=\"".getLocationList($tradeDetails[11])."\"  
			SellerPriceDisplay=\"\" />\n";
			
		return $tempString;
	}
	
	
	/***********************
	* getBuyer
	*
	* build getBuyer element of XML
	***********************/
	private function buildBuyer($tradeDetails) {
		//currency was $tradeDetails[15]		
		
		//removed on 7/23/14 per email from Janice
		//IndexID=\"-2\" 
		//IndexLocation=\"".$this->confirm->locationConfirmLanguage(getLocationList($tradeDetails[11]),getProductType($tradeDetails[9]))."\" 
		//IndexLocationID=\"-2\" 
		
		if($tradeDetails[14] == "" && $tradeDetails[47] > 0)
			$usePrice = $tradeDetails[47];
		else
			$usePrice = $tradeDetails[14];
		
		if( strpos(getProductGroup($tradeDetails[7])." ".getProductType($tradeDetails[9])." ".getProduct($tradeDetails[8]),"Index") !== FALSE) //Index trade
			$isIndex = 1;
		else
			$isIndex = 0;
		
		$tempString = "<BuyerPrice Price=\"".$usePrice."\" 
			PriceUnitID=\"-2\"
			PriceUnit=\"".getTradeUnits($tradeDetails[7])."\" 
			PriceCurrencyID=\"2\" 
			BuyerPriceDisplay=\"$".formatCurrency($tradeDetails[14],2,"$")." ".getCurrencyList($tradeDetails[15])."/".getTradeUnits($tradeDetails[7])."\" ";
		
		if($isIndex == 1) {
			$tempString.= "IndexID=\"-2\" 
							Index=\"".$this->confirm->locationConfirmLanguage(getLocationList($tradeDetails[11]),getProductType($tradeDetails[9]))."\" 
							IndexPrice=\"".formatCurrency($tradeDetails[14],2,"$")."\"
							IndexLocation=\"".$this->confirm->locationConfirmLanguage(getLocationList($tradeDetails[11]),getProductType($tradeDetails[9]))."\" 
							IndexLocationID=\"-2\" ";
		}
			
		$tempString.= "/>\n";
			
		return $tempString;
	}
	
	
	/**********************
	* buildCommodityType
	*
	* build commoditytype element of XML
	**********************/
	private function buildCommodityType($tradeDetails) {
		//ProductID is lookup value, Power = 91
		//PowerScheduleTypeID is lookup value
		//".getBlockDisplayText($tradeDetails[10],$tradeDetails[25],$tradeDetails[11],$tradeDetails[31])."
		
		//PowerPeakTypeID=\"".$tradeDetails[25]."\"   removed 7/23/14, changed to -2
		
		if(translateProduct($tradeDetails[9]) == "Power") {
			//power
			$tempString = "<CommodityType ProductID=\"91\" 
				Product=\"".translateProduct($tradeDetails[9])."\"
				PowerPeakTypeID=\"-2\" 
				PowerPeakType=\"".getBlock($tradeDetails[25])."\"
				PowerScheduleTypeID=\"-2\"
				PowerScheduleType=\"".$tradeDetails[29]."\" />\n";
		}
		else {
			//nat gas
			$tempString = "<CommodityType ProductID=\"90\" 
				Product=\"".translateProduct($tradeDetails[9])."\"
				PowerPeakTypeID=\"-2\" 
				PowerPeakType=\"".getBlock($tradeDetails[25])."\"
				PowerScheduleTypeID=\"-2\"
				PowerScheduleType=\"".$tradeDetails[29]."\" />\n";
		}
				
		return $tempString;
		
	}
		
	
	/*********************
	* listTradeIds
	**********************/
	private function listTradeIDs() {
		$returnStr = "";
				
		while( $temp = array_pop($this->tradeIDs) ) {
			$returnStr .= $temp."\n";
		}
		
		return $returnStr;
		
	}
	
	
	/******************
	* getTradeTypeID
	*
	* special mapping function for ConfirmHub integration
	******************/
	private function getTradeTypeID($commType,$dealType,$productID,$tradeNum) {
		$query = "SELECT fldTradeTypeID FROM tblB2TConfirmHub_Map_TradeType WHERE fldCommodityID=\"$commType\" AND fldDealTypeID=\"$dealType\" AND fldProductID=\"$productID\"";
		//echo($tradeNum." - ".$query."<br/>");
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		if($count > 0) {
			list($tradeTypeID) = mysqli_fetch_row($result);
			$this->tradeTypeID = $tradeTypeID;
			
			if($_GET['test'] == "1")
				echo("<p>TradeTypeID: ".$tradeTypeID."</p>");
		}
		else
			$this->tradeTypeID = -2;
			
		return;
	}
	
	/****************
	* trimProductType
	****************/
	private function trimProductType($prodType) {
		
		if(strpos($prodType,"Day Ahead") !== FALSE)
			return "Day-Ahead";
	
		return;
		
	}

}