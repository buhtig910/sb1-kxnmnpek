<?php


class Indices {
	
	private $indexName;
	private $reportName;
	private $hubName;
	private $startDate;
	private $endDate;
	private $outputStream;
	
	
	/************************
	* setIndex
	************************/
	public function setIndex($indexName) {
		$this->indexName = $indexName;
	}
	
	/************************
	* setReport
	************************/
	public function setReport($reportName) {
		$this->reportName = $reportName;
	}
	
	/************************
	* setHubName
	************************/
	public function setHubName($hubName) {
		$this->hubName = trim(preg_replace("/&/","&amp;",$hubName));
	}
	
	/************************
	* setDateRange
	*
	* Set date range for index query
	************************/
	public function setDateRange($startDate,$endDate) {
		$this->startDate = $startDate;
		$this->endDate = $endDate;
	}
	
	/*************************
	* getIndex
	*
	* Pull all values for hubs for specified index in tab-delimited format
	*************************/
	public function getIndex() {
		
		$query = "SELECT * FROM tblB2TScrape_NA WHERE";
		if($this->hubName != "All")
			$query.= " fldHub=\"".$this->hubName."\" AND ";		
		$query.= " fldTradeDate>='".$this->startDate."' AND fldTradeDate<='".$this->endDate."' AND fldType=\"".$this->reportName."\"";
		//echo($query."<br><br>");
		$result = mysqli_query($_SESSION['db'],$query);
		
		while($indexVals = mysqli_fetch_row($result)){
			for($i=0; $i<=count($indexVals); $i++) {
				$this->outputStream .= preg_replace("/&amp;/","&",$indexVals[$i]);
				if($i < count($indexVals))
					$this->outputStream .= "\t";
			}
			$this->outputStream .= "\n";
		}
		
	}
	
	/***********************
	* getIndexList
	*
	* Return list of indexes available in database
	***********************/
	public function getReportList() {
		
		$reports = array("NA Natural Gas","NA Power");
		
		for($i=0; $i<count($reports); $i++) {
			echo("<option value=\"$reports[$i]\">$reports[$i]</option>\n");
		}
		
	}
	
	
	/****************
	* getHubList
	*
	* Get all unique hub names from db
	****************/
	public function getHubList($indexType) {
		
		$query = "SELECT DISTINCT fldHub FROM tblB2TScrape_NA WHERE fldType=\"".$indexType."\" ORDER BY fldHub ASC";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		
		echo("<option value=\"All\">All</option>\n");
		
		while($indexDetail = mysqli_fetch_row($result)) {
			echo("<option value=\"".trim($indexDetail[0])."\">$indexDetail[0]</option>\n");
		}
			
	}
	
	/******************
	( getOutputStream
	******************/
	public function getOutputStream() {
		return $this->outputStream;
	}
	
	
}

?>