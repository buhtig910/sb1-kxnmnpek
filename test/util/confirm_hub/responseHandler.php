<?php

/***************************
* Confirm Hub error handler
***************************/

class ResponseHandler {	

	private $response;
	private $responseXML;
	private $errorCode;
	private $errorText;
	
	
	/*******************
	* logApiResponse
	*
	* write response code into to DB
	*******************/
	public function logApiResponse($function,$response,$tradeID,$action,$details) {
		
		/*if($action == "saveDealsResult")
			$temp = new SimpleXMLElement($response->saveDealsResult); //get response XML
		else if($action == "changePasswordResult")
			$temp = new SimpleXMLElement($response->changePasswordResult); //get response XML*/
		
		/*if($function != "")
			$temp = new SimpleXMLElement($response->$function); //get response XML
		else
			$temp = new SimpleXMLElement("<xml/>");
		
		echo("\n\n//// START DUMP OF API RESPONSE ///\n\n");
		print_r($temp); //dump object output
		
		$values = $temp->xpath("//CHResponses"); //parse XML for child nodes
		
		$this->responseXML = $values[0]->attributes();
		$this->errorCode = $this->getApiResponseCode();
		$this->errorText = $this->getApiResponseText();
		
		if($this->errorCode == 1)
			$this->errorText = "Success";*/
		
		$query = "INSERT INTO tblB2TConfirmHubReponse VALUES ('','".$this->errorCode."',\"".$this->errorText."\",$tradeID,\"". date( 'Y-m-d H:i:s')."\",\"".$action."\",\"".$details."\")";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_affected_rows();
		
		if($count == 1)
			return true;
		else
			return false;	
	}
		
	/****************
	* getErrorCode
	* 
	* return API error code
	****************/
	public function getErrorCode() {
		return $this->errorCode;
	}
	
	
	/*****************
	* getApiResponseCode
	******************/
	private function getApiResponseCode() {		
		return $this->responseXML->Code;
	}
	
	
	/*****************
	* getApiResponseCode
	******************/
	private function getApiResponseText() {
		return $this->responseXML->Details;
	}
	
}