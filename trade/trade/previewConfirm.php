<?php

@session_start();

require("../vars.inc.php");
require("../functions.php");

if( @validateSecurity() ) {
	
	require("../util/confirm.php");
	require("../util/genPDF.php");

	// We'll be outputting a PDF
	//header('Content-type: application/pdf');
	//header('Content-Disposition: attachment; filename="downloaded.pdf"');

	$query = "SELECT t.*,b.fldProductGroup FROM $tblTransaction t, $tblTradeType b, $tblProduct p WHERE t.fkProductGroup=b.pkTypeID AND t.fkProductID=p.pkProductID AND pkTransactionID=".$_GET["tradeID"];
	//echo("<p>".$query."</p>");
	$tradeResult = mysqli_query($_SESSION['db'],$query);
	$resultSet = mysqli_fetch_row($tradeResult);
	
	generatePDFConfirm($resultSet,"file",0);

}

?>