#!/usr/local/bin/php.cli

<?php

require("scrapeObj.php");
require("../vars.inc.php");
require("../functions.php");
require("emailObj.php");

// Log cron execution time for monitoring
function logCronExecution() {
	$logFile = '../temp/ice_scrape.log';
	$tempDir = '../temp/';
	
	// Ensure temp directory exists
	if (!is_dir($tempDir)) {
		mkdir($tempDir, 0755, true);
	}
	
	file_put_contents($logFile, time());
}

// Log this execution
logCronExecution();


/******************************
* ICE Data Scrape
*******************************/
//base URL - NA Power
$baseURL1 = "https://www.theice.com/marketdata/indices/powerResults.do?categoryId=9&marketName=OTC&reportId=54";
//base URL - NA Nat Gas
$baseURL2 = "https://www.theice.com/marketdata/indices/gasResults.do?categoryId=9&marketName=OTC&reportId=76";

//create scrape obj
$scraper = new Scrape();
$scraper2 = new Scrape();

//set the date
//$start = $today;
//$start = "2005-05-01";
$query = "SELECT fldTradeDate FROM tblB2TScrape_NA ORDER BY fldTradeDate DESC LIMIT 1";
$result = mysqli_query($_SESSION['db'],$query);
list($startDate) = mysqli_fetch_row($result);
$start = $startDate;

echo("start: ".$start." - end: ".$today."<br><br>");

while($start <= $today) { //$today

	$parts = explode("-",$start);
	$day = $parts[2];
	$month = intval($parts[1])-1;
	$year = $parts[0];
	
	//date string
	$dateStr = "&hubChoice=All&startDay=".$day."&startMonth=".$month."&startYear=".$year."&endDay=".$day."&endMonth=".$month."&endYear=".$year;
	
	$report1 = $baseURL1.$dateStr;
	$report2 = $baseURL2.$dateStr;
	
	//echo($report1."<br />");
	//echo($report2."<br />");
	
	//NA Power
	$scraper->setURL($report1);
	$scraper->setType("NA Power");
	$powerArr = $scraper->getPage();
	
	//NA Nat Gas
	$scraper2->setURL($report2);
	$scraper2->setType("NA Natural Gas");
	$gasArr = $scraper2->getPage();

	$start = date("Y-m-d",strtotime("+1 day",strtotime($start)));
	//echo($start."<br>");
}

echo("Power Rows Inserted: ".$scraper->getRowCount()."<br>");
echo("Nat Gas Rows Inserted: ".$scraper2->getRowCount()."<br>");

if($scraper->getRowCount() > 0 || $scraper2->getRowCount() > 0) {

	//generate report email
	$email = new Email();
	$email->setMessageType("HTML");
	$email->setTo($GLOBALS["SCRAPE_DISTRO"]);
	
	if($GLOBALS["PROD"]) {
		//$email->setBcc("chris@baldweb.com");
		$email->setSubject("Bosworth Scraping Report");
	}
	else {
		$email->setBcc("jake+cronjob@greenlightcommodities.com");
		$email->setSubject("[Test] Bosworth Scraping Report");
	}
			$email->setFrom("jake+cronjob@greenlightcommodities.com");
	
	$cellHeader = "style=\"background: #FFF6BD; border-right: 1px solid #AAAAAA; color: #333333; font-size: 12px; padding: 5px;\"";
	$cellStyleOdd = "style=\"background: #FFFFFF; border: 1px solid #AAAAAA; padding: 3px 5px; font-size: 12px;\"";
	$cellStyleEven = "style=\"background: #DDDDDD; border: 1px solid #AAAAAA; padding: 3px 5px; font-size: 12px;\"";
	
	//build email
	$bodyContent = "<html><body>";
	
	$bodyContent.= "<table cellspacing=\"0\" cellpadding=\"5\" style=\"border: 1px solid #CCC; border-collapse: collapse;\">";
	$bodyContent.= "<tr><th $cellHeader>Hub</th><th $cellHeader>TradeDate</th $cellHeader><th $cellHeader>Begin Date</th><th $cellHeader>End Date</th><th $cellHeader>High</th><th $cellHeader>Low</th><th $cellHeader>Average</th><th $cellHeader>Change</th><th $cellHeader>Volume</th><th $cellHeader>Deals</th><th $cellHeader>CParties</th><th $cellHeader>Type</th></tr>";
	
	//generate table rows
	$bodyContent.= $scraper->parseRows($powerArr,$cellStyleOdd,$cellStyleEven);
	$bodyContent.= $scraper2->parseRows($gasArr,$cellStyleOdd,$cellStyleEven);
	
	$bodyContent.= "</table>";
	$bodyContent.= "</body></html>";
	
	$email->setBody($bodyContent);
	$result = $email->sendMessage(); //send message
	
	if(!$result)
		echo("email: there was an error<br>");
	
	echo("done<br>");
}

?>