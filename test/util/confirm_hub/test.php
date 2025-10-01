<?php

//header("Content-Type: text/html; charset=UTF-8");

include("../../vars.inc.php");
include("confirmHub.php");

$hub = new ConfirmHub();
if(isset($_GET['date']))
	$useDate = $_GET['date'];
else {
	//$useDate = "2012-09-07";
	$useDate = date("Y-m-d");
}
	
echo("<p>Processing using date: ".$useDate."</p>");
//$hub->initConfirmHub($useDate);
//$hub->saveDeals();

?>