<?php

require("../vars.inc.php");

// Log cron execution time for monitoring
function logCronExecution() {
	$logFile = '../temp/company_mapping.log';
	$tempDir = '../temp/';
	
	// Ensure temp directory exists
	if (!is_dir($tempDir)) {
		mkdir($tempDir, 0755, true);
	}
	
	file_put_contents($logFile, time());
}

// Log this execution
logCronExecution();

$query = "SELECT DISTINCT fldCompanyName FROM tblB2TClient ORDER BY fldCompanyName ASC";
//echo($query);
$result = mysqli_query($_SESSION['db'],$query);
@$count = mysqli_num_rows($result);

$totalInsert = 0;
if($count > 0) {
	
	while($resultSet = mysqli_fetch_row($result)){
		
		$queryInsert = "INSERT INTO tblB2TCompany VALUES ('',\"$resultSet[0]\",\"\")";
		//echo("<p>".$queryInsert."</p>");
		$resultInsert = mysqli_query($_SESSION['db'],$queryInsert);
		@$countInsert = mysqli_affected_rows();
		
		if($countInsert == 1)
			$totalInsert++;
		
	}
	
	echo("<p>Found <strong>$count</strong> unique names, added <strong>$totalInsert</strong> new company names to the database");
	
	//get all clients in table
	$queryClientList = "SELECT pkClientID,fldCompanyName FROM tblB2TClient";
	$resultClientList = mysqli_query($_SESSION['db'],$queryClientList);
	
	$totalUpdateCount = 0;
	
	while($clientInfo = mysqli_fetch_row($resultClientList)) {
		//update fkCompanyID
		
		$queryMaster = "SELECT pkCompanyID FROM tblB2TCompany WHERE fldCompanyName=\"$clientInfo[1]\"";
		$resultMaster = mysqli_query($_SESSION['db'],$queryMaster);
		list($newCompanyID) = mysqli_fetch_row($resultMaster);
		
		$updateClient = "UPDATE tblB2TClient SET fkCompanyID=$newCompanyID WHERE pkClientID=$clientInfo[0]";
		$resultClient = mysqli_query($_SESSION['db'],$updateClient);
		@$updateCount = mysqli_affected_rows();
		
		if($updateCount == 1)
			$totalUpdateCount++;		
		
	}
	
	echo("<p>Updated <strong>$totalUpdateCount</strong> client records with new company ID's.</p>\n");

}

?>
