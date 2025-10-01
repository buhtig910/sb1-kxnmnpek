<?php
 @session_start();
 
	require_once("../util/popupHeader.php");
	require_once("../util/reportsObj.php");
	
	?>
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">    <style>

		#mainContentWrapperPopup {
			overflow-y: visible;
		}
	</style>

	<h2>Report Detail</h2>
	
    <p class="warningMessage">System being updated - report may not be accurate.</p>
	<?php
			
	$report = new Report();
	if(isAdmin()) 
		$report->setBroker($_GET["brokerID"]);
	else
		$report->setBroker($_SESSION["brokerID42"]);
	$report->setPeriod($_GET["period"]);
	$report->setDates($_GET["startDate"],$_GET["endDate"]);
	
	if($_GET["extended"] == "true")
		$report->getReportSummaryExtended();
	else
		$report->getReportSummary();
	
	if($_GET["brokerID"] == "")
		$brokerID = $_SESSION["brokerID42"];
		
	echo("<p><span class=\"fieldLabel\">Broker:</span> ".getBrokerName($report->getBroker())."<br/>\n");
	echo("<span class=\"fieldLabel\">Period: </span>".$report->getOutputPeriod()."<br />\n");
	echo("<span class=\"fieldLabel\">Total Commission:</span> ".formatCurrency($report->getBrokerTotal(),2,1))."</p>";
	?>
    
    <table class="dataTable" width="300" cellspacing="0" cellpadding="5" border="0">
    <tr>
        <th>Transaction Number</th>
        <th>Product Group</th>
        <th>Trade Date</th>
        <th>Commission</th>
    </tr>
    
    <?php
	
	$output = $report->getOutputData();
	
	for($i=0; $i<count($output); $i++) {
		echo("<tr><td>".$output[$i][1]."</td><td>".$output[$i][3]."</td><td>".formatDate($output[$i][2])."</td><td align=\"right\">".formatCurrency($output[$i][9],2,1)."</td></tr>\n");	
	}
	
	?>
    </table>
    
    <!--<p><a href="../util/reports.php?list=commission&brokerID=<?php echo($brokerID); ?>&period=<?php echo($period); ?>&startDate=<?php echo($startDate); ?>&endDate=<?php echo($endDate); ?>" target="_blank">Download raw data</a></p>-->
	

	<?php
	require_once("../util/popupBottom.php");
	?>