<?php

/***************************
* Confirm Hub error handler
***************************/

class Logger {	

	private $response;
	private $responseXML;
	private $errorCode;
	private $errorText;
	
	
	/*******************
	* logApiResponse
	*
	* write response code into to DB
	*******************/
	public function logAction($action,$details,$tradeID) {
		
		if($this->errorCode == 1)
			$this->errorText = "Success";
		
		$query = "INSERT INTO tblB2TLogging VALUES ('',\"".$action."\",\"".$details."\",$tradeID,\"".date( 'Y-m-d H:i:s')."\")";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_affected_rows();
		
		if($count == 1)
			return true;
		else
			return false;	
	}

}