<?php

// download fpdf class (http://fpdf.org)
require_once(dirname(__FILE__) . "/../fpdf/fpdf.php");	
require_once("confirmObj.php");
require_once(dirname(__FILE__) . "/../vars.inc.php");


function generatePDFConfirm($resultSet,$outputType,$resendType){

	/*
	0 = pkTransactionID
	1 = fldTransactionNum
	2 = fldLegID
	3 = fldConfirmDate
	4 = fldTradeDate
	5 = fkBuyerID
	6 = fkSellerID
	7 = fkProductGroup
	8 = fkProductID
	9 = fkProductTypeID
	10= fldRegion
	11= fldLocation
	12= fldOffsetIndex
	13= fldDelivery
	14= fldBuyerPrice
	15= fldBuyerCurrency
	16= fldSellerPrice
	17= fldSellerCurrency
	18= fkOptionStyleID
	19= fkOptionTypeID
	20= fkExerciseType
	21= fldStrike
	22= fldStrikeCurrency
	23= fldStartDate
	24= fldEndDate
	25= fldBlock
	26= fldUnitQty
	27= fldUnitFreq
	28= fldTotalQuantity
	29= fldHours
	30= fldTimeZone
	31= fkBuyingBroker
	32= fldBuyBrokerRate
	33= fldBuyBrokerComm
	34= fkSellingBroker
	35= fldSellBrokerRate
	36= fldSellBrokerComm
	37= fldStatus
	38= fkEntryUserID
	39= fldStartDay
	40= fldEndDay
	41= fldStartHour
	42= fldEndHour
	43= trade type (name)
	44= clear ID (ICE/NYMEX CLEARS)
	45= fldBuyCommTotal
	46= fldSellCommTotal
	47= fldIndexPrice
	*/
	
	/**** get custom fields *****/
	if($resultSet[7] == "8" || $resultSet[7] == "9") {
		// For OTC trades, we need to find custom fields from ANY leg of the same transaction
		// Use transaction number instead of specific transaction ID to get data from any leg
		$queryCustom = "SELECT cf.* FROM tblB2TCustomFields cf 
						WHERE cf.fkTransID IN (
						    SELECT pkTransactionID FROM tblB2TTransaction 
						    WHERE fldTransactionNum = '".$resultSet[1]."'
						) 
						LIMIT 1";
		$resultCustom = mysqli_query($_SESSION['db'],$queryCustom);
		@$customFields = mysqli_fetch_row($resultCustom);
		

	}
	
	/**** get custom commodity fields for New Product trades *****/
	$customCommodityFields = null;
	// Check if this is a New Product trade type
	$newProductQuery = "SELECT pkTypeID FROM tblB2TTradeType WHERE fldTradeName IN ('New Product 1', 'New Product 2')";
	$newProductResult = mysqli_query($_SESSION['db'], $newProductQuery);
	$newProductTypeIDs = array();
	if($newProductResult && mysqli_num_rows($newProductResult) > 0) {
		while($row = mysqli_fetch_row($newProductResult)) {
			$newProductTypeIDs[] = $row[0];
		}
	}
	
	// Custom commodity fields removed - table not being used properly
	
	/**** BUYER INFORMATION ****/
	$queryBuyer = "SELECT fldTraderName,fldCompanyName,fldConfirmStreet,fldConfirmStreet2,fldConfirmCity,fldConfirmState,fldConfirmZip,fldConfirmPhone,fldConfirmFax,fldEmail FROM tblB2TClient WHERE pkClientID=".$resultSet[5];
	//echo($queryBuyer);
	$resultBuyer = mysqli_query($_SESSION['db'],$queryBuyer);
	@$buyerInfo = mysqli_fetch_row($resultBuyer);
	/**** SELLER INFORMATION ****/
	$querySeller = "SELECT fldTraderName,fldCompanyName,fldConfirmStreet,fldConfirmStreet2,fldConfirmCity,fldConfirmState,fldConfirmZip,fldConfirmPhone,fldConfirmFax,fldEmail FROM tblB2TClient WHERE pkClientID=".$resultSet[6];
	//echo($querySeller);
	$resultSeller = mysqli_query($_SESSION['db'],$querySeller);
	@$sellerInfo = mysqli_fetch_row($resultSeller);
	

	//init confirm object
	$confirm = new Confirm();
	$confirm->setRegion($resultSet[10]);
	$confirm->setProductType($resultSet[9]);
	$confirm->setProductGroup($resultSet[7]);
	$confirm->setLocationID($resultSet[11]);	
	$confirm->setPeakText(getBlock($resultSet[25]));
	$confirm->setBuyerCo($buyerInfo[1]);
	$confirm->setSellerCo($sellerInfo[1]);
	$valid = $confirm->init($buyerInfo[1],$sellerInfo[1]); //buyer, seller
	
	if(!$valid) {
		//$confirm = null;
	}
	
	// fpdf object
	$pdf = new FPDF();
	// generate a simple PDF (for more info, see http://fpdf.org/en/tutorial/)
	$pdf->AddPage();

	/**** CONFIRM PDF HEADER ****/	
	$pdf->Image("../images/glconfirmlogo.jpg",10,8,70);
	//$pdf->Image("../images/bosworth_logo_confirm.jpg",10,8,70);
	//$pdf->SetFont("Arial","",30); //set new font
	//$pdf->Cell(100,10,"GREENLIGHT",0,1);
	//$pdf->Cell(100,10,"    COMMODITIES",0,0);
	
	$pdf->SetFont("Arial","",10); //set new font
	$pdf->Cell(0,4, "Bosworth Brokers LLC",0,1,"R");
	$pdf->Cell(0,4, "dba Greenlight Commodities",0,1,"R");
	$pdf->Cell(0,4, "5535 Memorial Drive, Suite F453",0,1,"R");
	$pdf->Cell(0,4, "Houston, TX 77007-8009",0,1,"R");
	$pdf->Cell(0,4, "Tel: 561-339-4032",0,1,"R");
	$pdf->Ln();
	$pdf->SetFont("Arial","B",12); //set new font
	/*if($outputType == "file")
		$pdf->Cell(0,8, "*** DRAFT Broker Confirmation ***",0,1,"C");
	else*/
	
	$pdf->Cell(0,8, "Broker Confirmation",0,1,"C");
	
	
	if($resendType == 2 || $resultSet[37] == $_SESSION["CONST_VOID"]) {
		//voided confirm
		$pdf->SetFont("Arial","B",12); //set new font
		$pdf->SetTextColor(255,0,0);
		$pdf->Cell(0,8, "*** VOID *** VOID *** VOID ***",0,1,"C");
	}
		
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(0,8,"Bill To:",0,1);
	$pdf->SetFont("Arial","",11); //set new font
	
	/**** GET BILL TO INFORMATION ****/
	if($resultSet[31] == 0)
		$billToID = $resultSet[6];
	else if($resultSet[34] == 0)
		$billToID = $resultSet[5];
	$queryBillTo = "SELECT fldTraderName,fldConfirmName,fldConfirmStreet,fldConfirmStreet2,fldConfirmCity,fldConfirmState,fldConfirmZip FROM tblB2TClient WHERE pkClientID=".$billToID;
	//echo($queryBillTo);
	$resultBillTo = mysqli_query($_SESSION['db'],$queryBillTo);
	@list($traderName,$confirmName,$confirmStreet,$confirmStreet2,$confirmCity,$confirmState,$confirmZip) = mysqli_fetch_row($resultBillTo);
	
	$pdf->Cell(0,4,$confirmName,0,1);
	$pdf->Cell(0,4,$confirmStreet,0,1);
	if($confirmStreet2 != "")
		$pdf->Cell(0,4,$confirmStreet2,0,1);
	$pdf->Cell(0,4,$confirmCity.", ".$confirmState." ".$confirmZip,0,1);


	/**** DISPLAY DATE AND CONFIRM NUMBER ***/
	$pdf->Ln();
	$pdf->SetFont("Arial","B",12);
	$pdf->Cell(24,5,"Trade Date: ",0);
	$pdf->SetFont("Arial","",12);
	$pdf->Cell(20,5,formatDate($resultSet[3]),0,1);
	$pdf->SetFont("Arial","B",12);
	$pdf->Cell(22,5,"Confirm #: ",0);
	$pdf->SetFont("Arial","",12);
	$pdf->Cell(20,5,$resultSet[1]."-".$resultSet[2],0,1);
	$pdf->Ln();
	$pdf->Cell(0,0,"",1,1); //draw line
	$pdf->SetFont("Arial","",11);
	$pdf->Cell(0,10,"We are pleased to confirm the following trade:",0,1);
	
	
	//Company Name
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell(34,5,"Buying Company: ",0,0);
	$pdf->SetFont("Arial","",11);
	if(strlen($buyerInfo[1]) > 30) {
		$buyArr = str_split($buyerInfo[1],30);
		$pdf->Cell(56,5,$buyArr[0],0,0); //buying co name
	}
	else{
		$pdf->Cell(56,5,$buyerInfo[1],0,0); //buying co name
	}
	
	$pdf->Cell(10,5,"",0,0); //cell spacer
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell(34,5,"Selling Company: ",0,0);
	$pdf->SetFont("Arial","",11);
	if(strlen($sellerInfo[1]) > 30){
		$sellArr = str_split($sellerInfo[1],30);
		$pdf->Cell(56,5,$sellArr[0],0,1); //selling co name
	}
	else{
		$pdf->Cell(56,5,$sellerInfo[1],0,1); //selling co name
	}
	
	//company name - line 2
	if(count(array($buyArr[1])) > 1){
		$pdf->Cell(34,5,"",0,0);
		$pdf->Cell(56,5,$buyArr[1],0,0); //buying co name line 2
	}
	if(count(array($sellArr[1])) > 1){
		$pdf->Cell(10,5,"",0,0); //cell spacer
		if(count(array($buyArr[1])) <= 1)
			$pdf->Cell(34,5,"",0,0);
		$pdf->Cell(56,5,$sellArr[1],0,1); //selling co name line 2
	}
	else {
		//$pdf->Ln();
	}
	
	//Trader Names
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell(14,5,"Trader: ",0,0);
	$pdf->SetFont("Arial","",11);
	$pdf->Cell(76,5,$buyerInfo[0],0,0);
	$pdf->Cell(10,5,"",0,0); //cell spacer
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell(14,5,"Trader: ",0,0);
	$pdf->SetFont("Arial","",11);
	$pdf->Cell(76,5,$sellerInfo[0],0,1);
	
	// Exchange ID logic is now handled above for both buyer and seller
	
	//Address Lines
	if($buyerInfo[2] != ""){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell(18,5,"Address: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(73,5,$buyerInfo[2],0,0);
	}
	else if($resultSet[44] != "") { //nymex/ice/nodal clear trade
		$pdf->Cell(90,5,"             ID#: ".$resultSet[44],0,0);
	}
	else {
		$pdf->Cell(90,5,"",0,0);
	}
	
	$pdf->Cell(10,5,"",0,0); //cell spacer
	
	if($sellerInfo[2]){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell(18,5,"Address: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(73,5,$sellerInfo[2],0,1);
	}
	else if($resultSet[44] != "") {
		$pdf->Cell(90,5,"             ID#: ".$resultSet[44],0,1);
	}
	else {
		$pdf->Cell(90,5,"",0,1);
	}
	
	// Address Detail - Line 2
	//buyer
	if($buyerInfo[3] != ""){
		$pdf->Cell(90,5,"                 ".$buyerInfo[3],0,0);
		$pdf->Cell(12,5,"",0,0); //cell spacer
	}
	else if($sellerInfo[3] != "") {
		$pdf->Cell(102,5,"",0,0);
	}
	//seller
	if($sellerInfo[3] != "")
		$pdf->Cell(90,5,"                ".$sellerInfo[3],0,1);
	else if($sellerInfo[4] != "" || $sellerInfo[5] != "" || $sellerInfo[6] != "") {
		if($buyerInfo[3] == "")
			$pdf->Cell(102,5,"",0,0);
		$pdf->Cell(90,5,"                ".$sellerInfo[4].", ".$sellerInfo[5]." ".$sellerInfo[6],0,1);
	}
	else
		$pdf->Cell(102,5,"",0,1);
	
	//buyer city, state zip
	if($buyerInfo[4] != "")
		$pdf->Cell(90,5,"                 ".$buyerInfo[4].", ".$buyerInfo[5]." ".$buyerInfo[6],0,0);
	else
		$pdf->Cell(90,5,"",0,0);
	$pdf->Cell(12,5,"",0,0); //cell spacer
	//seller city, state zip
	if($sellerInfo[4] != "" && $sellerInfo[3] != "")
		$pdf->Cell(90,5,"                ".$sellerInfo[4].", ".$sellerInfo[5]." ".$sellerInfo[6],0,1);
	else
		$pdf->Cell(90,5,"",0,1);
	
	if($buyerInfo[7] != ""){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell(7,5,"Tel: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(83,5," ".$buyerInfo[7],0,0);
	}
	else
		$pdf->Cell(90,5,"",0,0);
	$pdf->Cell(10,5,"",0,0); //cell spacer
	if($sellerInfo[7] != ""){ //buyer fax number
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell(7,5,"Tel: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(83,5," ".$sellerInfo[7],0,1);
	}
	else
		$pdf->Cell(90,5,"",0,1);
	
	if($buyerInfo[8] != ""){ //seller fax number
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell(9,5,"Fax: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(81,5,$buyerInfo[8],0,0);
	}
	else
		$pdf->Cell(90,5,"",0,0);
		
	$pdf->Cell(10,5,"",0,0); //cell spacer
	
	if($sellerInfo[8] != ""){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell(9,5,"Fax: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(81,5,$sellerInfo[8],0,1);
	}
	else
		$pdf->Cell(90,5,"",0,1);
	
	$pdf->Ln();
	
	
	/**** GET TEMPLATE PARAMS ****/
	$templateOptions = array();
	if (isset($resultSet[7]) && !empty($resultSet[7])) {
		$query2 = "SELECT * FROM tblB2TTemplates WHERE fkTypeID=".$resultSet[7];
		//echo($query2);
		$result2 = mysqli_query($_SESSION['db'],$query2);
		if ($result2) {
			$templateOptions = mysqli_fetch_row($result2);
		} else {
			error_log("Template query failed: " . mysqli_error($_SESSION['db']) . " Query: " . $query2);
		}
	}
	/*
	0 = pkTemplateID
	1 = fldTemplateName
	2 = fldOffsetIndex
	3 = fldDeliveryType
	4 = fldBuyerPrice
	5 = fldBuyerCurrency
	6 = fldSellerPrice
	7 = fldSellerCurrency
	8 = fldOptionStyle
	9 = fldOptionType
	10 = fldExerciseType
	11 = fldStrike
	12 = fldStrikeCurrency
	13 = fldBlock
	14 = fldHours
	15 = fldTimeZone
	16 = fldUnitFreq
	17 = fldIndexPrice
	18 = fkTypeID
	*/
	
	
	/*** START TRADE DATA ****/
	$productGroup = "Unknown";
	$tradeUnits = "";
	if (isset($resultSet[7]) && !empty($resultSet[7])) {
		// Special handling for RECs (trade type 9)
		if ($resultSet[7] == "9") {
			$productGroup = "RECs";
		} else {
			$query = "SELECT fldProductGroup,fldUnits FROM tblB2TTradeType WHERE pkTypeID=".$resultSet[7];
			//echo($query);
			$result = mysqli_query($_SESSION['db'],$query);
			if ($result) {
				@list($productGroup,$tradeUnits) = mysqli_fetch_row($result);
			} else {
				error_log("Product group query failed: " . mysqli_error($_SESSION['db']) . " Query: " . $query);
			}
		}
	}
	
	// Helper function to safely access template options
	if (!function_exists('safeTemplateOption')) {
		function safeTemplateOption($templateOptions, $index, $default = "") {
			return (isset($templateOptions[$index]) && !empty($templateOptions[$index])) ? $templateOptions[$index] : $default;
		}
	}
	
	//$pdf->Cell(100,10,"",0,1);
	
	$cellWidth = 31;
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell($cellWidth,5,"Product Group: ",0,0);
	$pdf->SetFont("Arial","",11);
	
	// Get product group name based on trade type
	error_log("Product Group Logic - resultSet[7]: " . $resultSet[7]);
	if($resultSet[7] == "9") {
		error_log("Setting Product Group to RECs");
		$pdf->Cell(0,5,"RECs",0,1);
	} else {
		error_log("Calling getProductGroup with: " . $resultSet[7]);
		$productGroupName = getProductGroup($resultSet[7]);
		error_log("getProductGroup returned: " . $productGroupName);
		$pdf->Cell(0,5,$productGroupName,0,1);
	}
	
	//$pdf->Cell(0,5,"Show product: ".$confirm->showProduct()." - ".$sellerInfo[1],0,1);
	
	if($resultSet[7] == "8") { //crude oil custom field output
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Product: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,$customFields[1],0,1);
		
		//price
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Price: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->MultiCell(0,5,$customFields[2],0,1);
		
		//additional info
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Additional Info: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,$customFields[4],0,1);
	}
	else if($resultSet[7] == "9") { //RECs custom field output
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Product: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,$customFields[1],0,1);
		
		//additional info
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Additional Info: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->MultiCell(0,5,$customFields[4],0,1);
	}
	else if($confirm->showProduct() == 1 || (isset($templateOptions[0]) && $templateOptions[0] == 3) || (isset($resultSet[8]) && $resultSet[8] == 4) || (isset($resultSet[8]) && $resultSet[8] ==2) ) { /* show for Heat Rate trades */
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Product: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getProduct($resultSet[8]),0,1);
	}
	


	
	// Only show Product Type for non-RECs trades
	if($resultSet[7] != "9") {
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Product Type: ",0,0);
		$pdf->SetFont("Arial","",11);
		if($confirm->showProductType() != "" || $confirm->showProductType() != false) //template customization from object
			$pdf->Cell(0,5,$confirm->showProductType(),0,1);
		else
			$pdf->Cell(0,5,$confirm->getProductTypeText($resultSet[9]),0,1); // removed on 1-15-12    getProductType($resultSet[9])
	}
	
	// Only show Location for non-RECs trades
	if($resultSet[7] != "9") {
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Location: ",0,0); //populateLocationList($resultSet[11]);
		$pdf->SetFont("Arial","",11);
		
		$locationName = getLocationList($resultSet[11]);
		$productType = getProductType($resultSet[9]);
		$locationText = $confirm->locationConfirmLanguage($locationName, $productType);
		
		$pdf->Cell(0,5,$locationText,0,1);
	}
	
	if($confirm->showRegion() != 0) {
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Region: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,$resultSet[10],0,1);
	}
	
	if($templateOptions[2] == "1"){
		$pdf->SetFont("Arial","B",11);
		if($resultSet[8] == 2 || $resultSet[8] == 4) //heat rate
			$pdf->Cell($cellWidth,5,"Index: ",0,0);
		else
			$pdf->Cell($cellWidth,5,"Offset Index: ",0,0); //populateOffsetList($resultSet[12]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getOffsetList($resultSet[12]),0,1);
	}	
	if(($templateOptions[3] == "1" && ($confirm->showDelivery() != 0 || $confirm->showDelivery == "")) || $confirm->showDelivery() == 1 ){
		if($resultSet[9] != 2 && $resultSet[9] != 3) { //hide for all Financial deals
			// Only show Delivery Point for non-RECs trades
			if($resultSet[7] != "9") {
				$pdf->SetFont("Arial","B",11);
				if($resultSet[7] == "8")
					$pdf->Cell($cellWidth,5,"Delivery Point: ",0,0);
				else
					$pdf->Cell($cellWidth,5,"Delivery: ",0,0); //populateDeliveryList($resultSet[13]);
				$pdf->SetFont("Arial","",11);
				if($resultSet[7] == "8")
					$pdf->MultiCell(0,5,$customFields[3],0,1);
				else
					$pdf->Cell(0,5,getDeliveryList($resultSet[13]),0,1);
			}
		}
	}
	if($templateOptions[4] == "1"){
		$pdf->SetFont("Arial","B",11);
		if($resultSet[8] == 3) //fix price power options
			$pdf->Cell($cellWidth,5,"Premium: ",0,0);
		else
			$pdf->Cell($cellWidth,5,"Buyer Price: ",0,0);
		$pdf->SetFont("Arial","",11);
		if($resultSet[8] == 1 || $resultSet[8] == 3) //fixed price, heat rate products
			$pdf->Cell(0,5,formatCurrency($resultSet[14],-1,1),0,1);
		else if($resultSet[8] == 2 || $resultSet[8] == 4) //heat rate
			$pdf->Cell(0,5,formatCurrency($resultSet[14],-1,0),0,1);
		else
			$pdf->Cell(0,5,formatCurrency($resultSet[14],-1,0),0,1);
	}

	if($templateOptions[5] == "1" && $confirm->showCurrency()){ //$confirm->showBuyerCurrency() &&
		$pdf->SetFont("Arial","B",11);
		//if($confirm->showBuyerCurrency())
			$pdf->Cell($cellWidth,5,"Currency: ",0,0);
		//else
			//$pdf->Cell($cellWidth,5,"Buyer Currency: ",0,0); //populateCurrencyList($resultSet[15]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getCurrencyList($resultSet[15]),0,1);
	}
	
	if($templateOptions[17] == "1"){  //add index price block
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Index Price: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,$confirm->getIndexPriceText()." Index Price +".formatIndexPrice($resultSet[47]),0,1);
	}
	
	if($templateOptions[7] == "1" || ($confirm->showSellerPrice() && $templateOptions[0] != 7) ){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Seller Price: ",0,0);
		$pdf->SetFont("Arial","",11);
		if($confirm->showSellerPrice() == 1) {
			$pdf->Cell(0,5,$confirm->getSellerPriceText(),0,1);
		}
		else
			$pdf->Cell(0,5,formatCurrency($resultSet[16],4,0),0,1);
	}
	if($confirm->showBuyerCurrency() != 0){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Currency: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getCurrencyList($resultSet[15]),0,1);
	}
	
	if($templateOptions[7] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Seller Currency: ",0,0); //populateCurrencyList($resultSet[17]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getCurrencyList($resultSet[17]),0,1);
	}
	if($templateOptions[8] == "1"){
	 	$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Option Style: ",0,0); //populateOptionStyle($resultSet[18]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getOptionStyle($resultSet[18]),0,1);
	}
	if($templateOptions[9] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Option Type: ",0,0); //populateOptionType($resultSet[19]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getOptionType($resultSet[19]),0,1);
	}
	if($templateOptions[10] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Exercise Type: ",0,0); //populateExercise($resultSet[20]);
		$pdf->SetFont("Arial","",11);
		$addlExerciseText = $confirm->getAddlExerciseTypeText(getLocationList($resultSet[11]),$resultSet[20]);
		$pdf->Cell(0,5,getExercise($resultSet[20]).$addlExerciseText,0,1);
	}
	if($templateOptions[11] == "1"){
	 	$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Strike: ",0,0);
		$pdf->SetFont("Arial","",11);
		if($resultSet[7] == 6 || ($resultSet[7] == 2 && ($resultSet[8] == 1 || $resultSet[8] == 3))) //nat gas option trades, power option fixed price
			$pdf->Cell(0,5,formatCurrency($resultSet[21],-1,1),0,1);
		else
			$pdf->Cell(0,5,formatCurrency($resultSet[21],-1,0),0,1);
	}
	if($templateOptions[12] == "1" && $resultSet[7] != 6 && !($resultSet[7] == 2 && ($resultSet[8] == 1 || $resultSet[8] == 3))){ // exclude nat gas option trades, power option fixed price
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Strike Currency: ",0,0); //populateCurrencyList($resultSet[22]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getCurrencyList($resultSet[22]),0,1);
	}
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell($cellWidth,5,"Start Date: ",0,0);
	$pdf->SetFont("Arial","",11);
	$pdf->Cell(0,5,formatDate($resultSet[23]),0,1);
	
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell($cellWidth,5,"End Date: ",0,0);
	$pdf->SetFont("Arial","",11);
	$pdf->Cell(0,5,formatDate($resultSet[24]),0,1);
	
    if($templateOptions[13] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Block: ",0,0); //populateBlock($resultSet[25]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getBlock($resultSet[25]),0,1);
	}
	if($templateOptions[14] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Hours: ",0,0);
		$pdf->SetFont("Arial","",11);
		
		if($resultSet[25] != 6) 
			$pdf->MultiCell(150,5,getBlockDisplayText($resultSet[10],$resultSet[25],$resultSet[11],$resultSet[31]),0,1);
		else{ //custom hours block
			if($resultSet[39] == 0 && $resultSet[40] == 0)
				$pdf->Cell(0,5,getDayType($resultSet[43]).", ".$resultSet[41]."-".$resultSet[42],0,1);
			else
				$pdf->Cell(0,5,getDay($resultSet[39])."-".getDay($resultSet[40]).", ".$resultSet[41]."-".$resultSet[42],0,1);
		}
	}
	if($templateOptions[15] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Time Zone: ",0,0);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,$resultSet[30],0,1);
	}
	
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell($cellWidth,5,"Unit Quantity: ",0,0);
	$pdf->SetFont("Arial","",11);
	// Add RECs suffix for RECs trades and format with commas
	$unitQuantityDisplay = number_format($resultSet[26])." ".$tradeUnits;
	if($resultSet[7] == "9" || $resultSet[7] == "10") { // RECs trade types
		$unitQuantityDisplay .= " RECs";
	}
	$pdf->Cell(0,5,$unitQuantityDisplay,0,1);
	
	if($templateOptions[16] == "1"){
		$pdf->SetFont("Arial","B",11);
		$pdf->Cell($cellWidth,5,"Unit Frequency: ",0,0);	//populateFrequency($resultSet[27]);
		$pdf->SetFont("Arial","",11);
		$pdf->Cell(0,5,getFrequency($resultSet[27]),0,1);
	}
	
	$pdf->SetFont("Arial","B",11);
	$pdf->Cell($cellWidth,5,"Total Quantity: ",0,0);
	$pdf->SetFont("Arial","",11);
	// Add RECs suffix for RECs trades and format with commas
	$totalQuantityDisplay = number_format($resultSet[28]);
	if($resultSet[7] == "9" || $resultSet[7] == "10") { // RECs trade types
		$totalQuantityDisplay .= " RECs";
	}
	$pdf->Cell(0,5,$totalQuantityDisplay,0,1);
	/**** END TRADE DATA ****/
		
	
	$pdf->Ln();
	$pdf->Ln();
	
	/**** BALANCE DUE ****/
	$additionalBrokerFees = getAdditionalBrokerFees($resultSet[0]);
	
	if($resultSet[33] > 0)
		$balanceDue = $resultSet[33] + $additionalBrokerFees;
	else if($resultSet[36] > 0)
		$balanceDue = $resultSet[36] + $additionalBrokerFees;
	
	$pdf->SetFont("Arial","B",12);
	$pdf->Cell(0,0,"",1,1); //draw line
	if($resultSet[33] > 0)
		$pdf->Cell(180,15,"Balance Due: ".formatCurrency($balanceDue,2,1),0,1,"R");
	else if($resultSet[36] > 0)
		$pdf->Cell(180,15,"Balance Due: ".formatCurrency($balanceDue,2,1),0,1,"R");
	else
		$pdf->Cell(180,15,"Balance Due: ".formatCurrency(0,2,1),0,1,"R");
	$pdf->Cell(0,0,"",1,1); //draw line
	
	$pdf->SetFont("Arial","",10); //change font
	$pdf->Cell(0,10,"If any questions, contact the Confirmation Dept at 561-339-4032",0,1);
	//$pdf->Ln();
	
	/**** DISCLAIMER ****/
	$infoText = "Greenlight Commodities facilitates transactions for our clients as an intermediary and is neither the issuer, guarantor, or counterparty in this transaction. Greenlight Commodities, its employees (collectively \"Greenlight Commodities\") shall have no liability in the event either party fails to comply with the intricacies of  this transaction or this transaction itself. ";
	$infoText.= "You acknowledge and agree that all terms and conditions of this transaction were determined and agreed to solely by you an your counterparty and compliance therewith is the responsibility of you and your counterparty . You further agree to hold Greenlight Commodities harmless from any claims, loss or damage, in connection with or arising out of any dispute regarding this transaction. ";
	$infoText.= "Any errors or differences must be reported immediately to Greenlight Commodities at 561-339-4032. Your failure to notify us of such errors or differences will be deemed your agreement that this confirmation of transaction is correct and ratification of the transaction reported herein. You understand that Greenlight Commodities in its sole discretion may record and retain, on tape of otherwise, any telephone conversation between Greenlight Commodities and you.";
	$pdf->SetFont("Arial","",6); //change font
	$pdf->MultiCell(0,3,$infoText); //paragraph of text
	
	/**** NYMEX CLEAR DISCLAIMER ****/
	if(isNymexCleared($resultSet[5]) || isNymexCleared($resultSet[6])) {
    	$pdf->Ln();
    	$nymexText = "Note: For the transactions that are to be cleared through the New York Mercantile Exchange (\"NYMEX\"), the transaction confirmed herin shall be simultaneously booked out with an opposite or contra transaction at the same price upon clearance into NYMEX. In the even the transaction is not ultimately accepted for clearance by NYMEX, the counterparties agree that the transaction will be considered null and void and of no legal effect.";
    	$pdf->MultiCell(0,3,$nymexText); //paragraph of text
	}
	
	/**** Financial Hub Deals Disclaimer *******/
	if(checkHub($resultSet[11])) { //location id's 139-146
		$pdf->Ln();
    	$nymexText = "The official published price of the weighted average of the 15-minute Real-Time Settlement Point Prices (RTSPP) for the ERCOT North 345 kV Hub (as described in section 3.5.2 of the ERCOT Nodal Protocols, as may be amended or supplemented from time to time) as published on ERCOT's website, located at www.ercot.com, or any successor thereto. Notwithstanding any other provision incorporated in the Agreement or Transaction, Section 7.3 of the Commodity Definitions shall be amended by replacing the phrase \"30 calendar days\" with the phrase \"(i) 30 calendar days or (ii) in the case of a correction by ERCOT, such longer period as is consistent with ERCOT's procedures and guidelines\" priced in USD on each applicable day within each Calculation Period.";
    	$pdf->MultiCell(0,3,$nymexText); //paragraph of text
	}
	
	// attachment name
	$filename = "Trade Confirm - ".$resultSet[1]."-".$resultSet[2].".pdf";
	
	// encode data (puts attachment in proper format)
	if($_SESSION['testScript'] == 1) {
		$pdf->Output($filename, "F");
	}
	else if($outputType == "file") {
		$pdf->Output($filename, "I");
	}
	else {
		return $pdf->Output($filename, "S");
	}
}




/*********************************/
/*********************************/
/*********************************
* GENERATE INVOICE
*
* input: invoideID
*********************************/
function generatePDFInvoice($invoiceID){

	$query = "SELECT * FROM tblB2TInvoice WHERE fldInvoiceNum=".$_GET['invoiceID'];
	//echo("<p>".$query."</p>");
	$result = mysqli_query($_SESSION['db'],$query);
	$resultSet = mysqli_fetch_row($result);
	/*
	0 = invoideID
	1 = date
	2 = invoice num
	3 = invoice total
	4 = fkcompanyID
	*/
	
	// download fpdf class (http://fpdf.org)
	require_once(dirname(__FILE__) . "/../fpdf/fpdf.php");
	// fpdf object
	$pdf = new FPDF('L'); //landscape
	// generate a simple PDF (for more info, see http://fpdf.org/en/tutorial/)
	$pdf->AddPage();

	/**** CONFIRM PDF HEADER ****/	
	$pdf->Image("../images/glconfirmlogo.jpg",10,8,70);
	//$pdf->Image("../images/bosworth_logo_confirm.jpg",10,8,70);
	//$pdf->SetFont("Arial","",30); //set new font
	//$pdf->Cell(100,10,"GREENLIGHT",0,1);
	//$pdf->Cell(100,10,"    COMMODITIES",0,0);
	
	$pdf->SetFont("Arial","",10); //set new font
	
	//statement date and #
	$xPos = 225;
    $pdf->SetX($xPos);
    //Colors of frame, background and text
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(230,230,230);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(30,5,"Date",1,0,'C',true);
	$pdf->Cell(30,5,"Invoice #",1,1,'C',true);
	$pdf->SetFont("Arial","",10); //set new font
	$pdf->SetX($xPos);
	$pdf->Cell(30,5, date("m/d/Y"),1,0,'C');
	$pdf->Cell(30,5, $invoiceID,1,1,'C');
	$pdf->Ln();
	
	$pdf->Cell(0,4, "Bosworth Brokers LLC",0,1,"R");
	$pdf->Cell(0,4, "dba Greenlight Commodities",0,1,"R");
	$pdf->Cell(0,4, "5535 Memorial Drive, Suite F453",0,1,"R");
	$pdf->Cell(0,4, "Houston, TX 77007-8009",0,1,"R");
	$pdf->Cell(0,4, "Tel: 561-339-4032",0,1,"R");

	
	//bill to info
	$w = 90;
	$pdf->Cell($w,5,"Bill To",1,1,'C',true);
	$pdf->Rect(10,64,$w,25);
	
	//insert client bill info here
	//old -- removed 5/31/23   $clientQuery = "SELECT DISTINCT fldInvoiceName,fldCompanyName,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldEmail FROM tblB2TClient c, tblB2TTransaction t WHERE ((t.fkBuyerID=c.pkClientID AND c.fkCompanyID=".$resultSet[4]." AND fldBuyBrokerComm > 0) OR (t.fkSellerID=c.pkClientID AND c.fkCompanyID=".$resultSet[4]." AND fldSellBrokerComm > 0))";
	$clientQuery = "SELECT fldInvoiceName,fldCompanyName,fldStreet1,fldStreet2,fldCity,fldState,fldZip,fldInvoiceEmail FROM tblB2TCompany WHERE pkCompanyID=".$resultSet[4];
	//echo("<p>".$clientQuery."</p>");
	$clientResult = mysqli_query($_SESSION['db'],$clientQuery);
	$clientInfo = mysqli_fetch_row($clientResult);
	/*
	0 = invoice name
	1 = company name
	2 = street 1
	3 = street 2
	4 = city
	5 = state
	6 = zip
	7 = email
	*/
	
	$pdf->Cell(0,4, $clientInfo[0],0,1); //invoice name
	$pdf->Cell(0,4, $clientInfo[1],0,1); //company name
	$pdf->Cell(0,4, $clientInfo[2],0,1); //street 1
	if($clientInfo[3] != "")
		$pdf->Cell(0,4, $clientInfo[3],0,1); //street 2
	$pdf->Cell(0,4, $clientInfo[4].", ".$clientInfo[5]." ".$clientInfo[6],0,1); //city, state, zip
	$pdf->Cell(0,4, $clientInfo[7],0,1); //email
	$pdf->Ln();
	$pdf->Ln();
	
	$col_1 = 27;
	$col_2 = 32;
	$col_3 = 180;
	$col_4 = 25;
	$spacer = 40;

	
	//build trade listing table headers
	$pdf->SetFont("Arial","B",10); //set new font
	$pdf->Cell($col_1,5,"Trade Date",0,0,"",true);
	$pdf->Cell($col_2,5,"Transaction #",0,0,"",true);
	$pdf->Cell($col_3,5,"Description",0,0,"",true);	
	$pdf->Cell(0,5,"Broker Fee",0,1,"",true);
	$pdf->Cell(0,0,"",1,1); //draw line
	$pdf->SetFont("Arial","",10); //set new font
	
	$queryTrades = "SELECT fldTransactionNum,fldLegID,fldConfirmDate,fkBuyerID,fkSellerID,fkProductGroup,fkProductID,fkProductTypeID,fldLocation,fldBuyerPrice,fldBuyerCurrency,fldStartDate,fldEndDate,fldUnitQty,fldTotalQuantity,fldBuyBrokerComm,fldSellBrokerComm,fldBuyBrokerRate,fldSellBrokerRate,pkTransactionID,fkBuyingBroker,fkSellingBroker FROM tblB2TTransaction t, tblB2TInvoice i, tblB2TInvoiceTrans it WHERE it.fkTransactionID=t.pkTransactionID AND it.fkInvoiceID=i.fldInvoiceNum AND i.fldInvoiceNum=".$invoiceID;
	//echo($queryTrades);
	$resultTrades = mysqli_query($_SESSION['db'],$queryTrades);
	$count = mysqli_num_rows($resultTrades);
	$total = 0;
	
	while($tradeDetails = mysqli_fetch_row($resultTrades)){
		/*
		0 = transNum
		1 = leg ID
		2 = confirm date
		3 = buyerID
		4 = sellerID
		5 = product group
		6 = product
		7 = product type
		8 = location
		9 = buyer price
		10= buyer currency
		11= start date
		12= end date
		13= unit qty
		14= total qty
		15= buy broker comm
		16= sell broker comm
		17= buy broker rate
		18= sell broker rate
		19 = transactionID
		20 = fkBuyingBroker
		21 = fkSellingBroker
		*/
				
		$queryName = "SELECT fldCompanyName FROM tblB2TClient WHERE pkClientID=".$tradeDetails[3];
		$resultName = mysqli_query($_SESSION['db'],$queryName);
		@list($buyTraderName) = mysqli_fetch_row($resultName);
		
		$queryName = "SELECT fldCompanyName FROM tblB2TClient WHERE pkClientID=".$tradeDetails[4];
		$resultName = mysqli_query($_SESSION['db'],$queryName);
		@list($sellTraderName) = mysqli_fetch_row($resultName);
		
		$pdf->Cell($col_1,5,formatDate($tradeDetails[2]),0,0); //date
		$pdf->Cell($col_2,5,$tradeDetails[0]."-".$tradeDetails[1],0,0); //trans num
		//$pdf->Cell($col_3,5,"",0,0); //empty space
		if($tradeDetails[15] != "0") { //buy broker side
			// Add RECs suffix for RECs trades
			$quantityDisplay = $tradeDetails[14];
			if($tradeDetails[5] == "9" || $tradeDetails[5] == "10") { // RECs trade types
				$quantityDisplay .= " RECs";
			}
			$pdf->Cell($col_3,5,getLocationList($tradeDetails[8])." - ".getProductType($tradeDetails[7])." | Price: ".formatCurrency($tradeDetails[9],2,1)." (".$quantityDisplay." @ $".$tradeDetails[17].") | Trader: ".getTraderName($tradeDetails[3]),0,0);
			$brokerFees = $tradeDetails[15] + getAdditionalBrokerFees($tradeDetails[19]);
			$pdf->Cell(0,5,formatCurrency($brokerFees,2,1),0,0); //buy broker fee
			$total += $tradeDetails[17];
		}
		else if($tradeDetails[16] != "0") { //sell broker side
			// Add RECs suffix for RECs trades
			$quantityDisplay = $tradeDetails[14];
			if($tradeDetails[5] == "9" || $tradeDetails[5] == "10") { // RECs trade types
				$quantityDisplay .= " RECs";
			}
			$pdf->Cell($col_3,5,getLocationList($tradeDetails[8])." - ".getProductType($tradeDetails[7])." | Price: ".formatCurrency($tradeDetails[9],2,1)." (".$quantityDisplay." @ $".$tradeDetails[18].") | Trader: ".getTraderName($tradeDetails[4]),0,0);
			$brokerFees = $tradeDetails[16] + getAdditionalBrokerFees($tradeDetails[19]);
			$pdf->Cell(0,5,formatCurrency($brokerFees,2,1),0,0); //sell broker fee
			$total += $tradeDetails[18];
		}
		$pdf->Ln();
		$pdf->Cell(0,0,"",1,1); //draw line	
	}
	
	
	$pdf->Cell(0,0,"",1,1); //draw line
	$pdf->Cell(0,5," ",0,1);
	
	//DISPLAY INVOICE TOTAL
	$pdf->SetFont("Arial","B",10);
	$xPos = 247;
	$pdf->SetX($xPos);
	$pdf->Cell(40,10,"Amount Due",1,1,'C',true);
	$pdf->SetX($xPos);
	$pdf->Cell(40,10,formatCurrency($resultSet[3],2,1),1,1,'C');
	//Banking Information is provided in the Monthly Statement email
	//$pdf->Cell(0,0,"",1,1); //draw line
	
	$col1 = 30;
	$col2 = 50;
	$height = 4;
	
	$pdf->Cell(0,5,"Thanks for your business!",0,1);
	$pdf->Ln();
	$pdf->SetFont("Arial","",8);
	$pdf->Cell(0,5,"Bosworth Brokers LLC dba Greenlight Commodities Banking Information:",0,1);
	$pdf->Cell($col1,$height,"EIN: ",0,0);
	$pdf->Cell($col2,$height,"45-3532901",0,1);

	$pdf->Cell($col1,$height,"Bank Name: ",0,0);
	$pdf->Cell($col2,$height,"JP Morgan Chase Bank, N.A.",0,1);
	
	$pdf->Cell($col1,$height,"Swift Code: ",0,0);
	$pdf->Cell($col2,$height,"CHASUS33",0,1);
	 
	$pdf->Cell($col1,$height,"Bank routing number: ",0,0);
	$pdf->Cell($col2,$height,"021000021",0,1);
	
	$pdf->Cell(0,$height,"Note: This routing number can only be used for direct deposits and ACH transactions.",0,1);
	$pdf->Ln();
	
	$pdf->Cell(0,$height,"For wire transfers, please use routing number: 021000021.",0,1);
	$pdf->Cell(0,$height,"Bank Account Number: 716533990",0,1);
	 
	// attachment name
	$filename = "Greenlight Commodities Invoice #".$invoiceID.".pdf";
	
	// encode data (puts attachment in proper format)
	if($_SESSION["testScript"] == 1)
		$pdf->Output($filename, "F");
	else
		return $pdf->Output($filename, "S");
	
}