<?php
$_SESSION["testScript"] = 0; //switch for testing

/*** company information ***/
$_SESSION['parentBrokerCompanyID'] = "45";
$_SESSION['brokerageCompanyId'] = "61";
$_SESSION['co_name'] = "Greenlight Commodities";
$_SESSION['co_street_address'] = "5571 Purple Meadow Lane";
$_SESSION['co_city'] = "Fulshear";
$_SESSION['co_state'] = "TX";
$_SESSION['co_zip'] = "77441";
$_SESSION['co_phone'] = "713-305-7841";
$_SESSION['co_fax'] = "";

$_SESSION["CONST_PENDING"] = 0;
$_SESSION["CONST_CONFIRMED"] = 1;
$_SESSION["CONST_LIVE"] = 2;
$_SESSION["CONST_SETTLED"] = 3;
$_SESSION["CONST_CANCELLED"] = 4;
$_SESSION["CONST_CONFIRM_CANCELLED"] = 5; //confirmed/cancelled
$_SESSION["CONST_LIVE_CANCELLED"] = 6; //live/cancelled
$_SESSION["CONST_VOID"] = 7; //void

$_SESSION["CONST_NOT_PAID"] = 0; //unsettled trade on invoice
$_SESSION["CONST_PAID"] = 1; //settled trade on invoice
//$_SESSION["CONST_VOID"] = 2; //voided trade on invoice

$_SESSION["BROKER_CONFIRMS"] = 0; //copy brokers on confirm emails

$MYSQL_HOST = "localhost";
$subdomain = str_replace('.greenlightcommodities.com','',$_SERVER['HTTP_HOST']);

if($subdomain == "test"){ //remove || $HTTP_HOST==""
	//echo("<p>using test DB...<p>");
	$MYSQL_LOGIN = "Greentest1";
	$MYSQL_PASS = "AfC2bRRjcjugeTg";
	$MYSQL_DB = "greenlight_test";	
	$_SESSION["NYMEX_ID"] = "295";
	$_SESSION["ICE_ID"] = "296";
	$_SESSION["NGX_ID"] = "747";
	$_SESSION["NODAL_ID"] = "748";
	$_SESSION["NASDAQ_ID"] = "749";
	$_SESSION["PROD"] = false;
	$_SESSION["CONFIRM_HUB_API"] = 2;
	$_SESSION["CH_LIMIT"] = array(66,207); //company IDs for Exelon & Luminant to restrict trades
	$_SESSION["baseURL"] = "http://test.greenlightcommodities.com/";
	$_SESSION["SCRAPE_DISTRO"] = "jake+scrapescript@greenlightcommodities.com"; //scrape script email distro
}
else {
	//echo("<p>using prod DB...<p>");
	$MYSQL_LOGIN = "greenlight";
	$MYSQL_PASS = "pZqjBWeM";
	$MYSQL_DB = "greenlight_prod";
	$_SESSION["NYMEX_ID"] = "295";
	$_SESSION["ICE_ID"] = "296";
	$_SESSION["NGX_ID"] = "1002";
	$_SESSION["NODAL_ID"] = "1003";
	$_SESSION["NASDAQ_ID"] = "1107";
	$_SESSION["PROD"] = true;
	$_SESSION["CONFIRM_HUB_API"] = 1;
	$_SESSION["CH_LIMIT"] = array(172,25,178,37,38,64); //company IDs for Exelon (172,25,178) & Luminant (37), Macquarie (38,64) to restrict trades 
	$_SESSION["baseURL"] = "http://trade.greenlightcommodities.com/";
	//$_SESSION["SCRAPE_DISTRO"] = "chris@baldweb.com"; //scrape script email distro
	$_SESSION["SCRAPE_DISTRO"] = "jake+scrapescript@greenlightcommodities.com"; //scrape script email distro
}


$_SESSION['db'] = mysqli_connect($MYSQL_HOST,$MYSQL_LOGIN,$MYSQL_PASS,$MYSQL_DB)
	or die ("Could not connect to the MySQL database.");

//mysql_select_db($MYSQL_DB)
//	or die ("Could not set database '$MYSQL_DB' as the active database.");

date_default_timezone_set("America/New_York");
$today = date("Y-m-d");
$_SESSION["today"] = date("Y-m-d");
//$NG_CONSTANT = 10000; //constant of 10000
$NG_CONSTANT = 1;

$tblMetaData = "tblB2TMetaData";
$tblTradeType = "tblB2TTradeType";
$tblTransaction = "tblB2TTransaction";
$tblTemplates = "tblB2TTemplates";
$tblTradeType = "tblB2TTradeType";
$tblProduct = "tblB2TProduct";
$tblProductType = "tblB2TProductType";
$tblClient = "tblB2TClient";
$tblCompany = "tblB2TCompany";
$tblBroker = "tblB2TBroker";
$tblLocation = "tblB2TLocation";
$tblHours = "tblB2THourMatrix";
$tblBlocks = "tblB2TBlocks";
$tblAuditHistory = "tblB2TAudit";
$tblInvoice = "tblB2TInvoice";
$tblInvoiceTrans = "tblB2TInvoiceTrans";
$tblTransactionBroker = "tblB2TTransaction_Brokers";


?>