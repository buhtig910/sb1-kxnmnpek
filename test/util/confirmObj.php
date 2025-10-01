<?php

/****************************
* Confirm customizations object
*
***************************/

class Confirm {
	
	private $clientID;
	private $region;
	private $productType;
	private $locationID;
	private $peakText;
	
	private $displayProduct;
	private $displayBuyerCurrency;
	private $productGroup;
	private $productTypeText;
	private $displaySellerPrice;
	private $sellerPriceText;
	private $displayDelivery;
	private $displayRegion;
	private $buyerCo;
	private $sellerCo;
	
	
	/************************
	* setRegion
	*
	* Used to initialize object setup
	*************************/
	public function setRegion($region) {
		$this->region = $region;
	}
	
	/************************
	* setProductType
	*
	* Used to initialize object setup
	**************************/
	public function setProductType($prodType) {
		$this->productType = $prodType;
	}
	
	/**************************
	* setLocationID
	*
	* Used to initialize object setup
	**************************/
	public function setLocationID($locationID) {
		$this->locationID = $locationID;	
	}
	
	public function getLocationID() {
		return $this->locationID;	
	}
	
	public function setPeakText($peakText) {
		$this->peakText = $peakText;
	}
	
	public function setProductGroup($productGroupID) {
		$query = "SELECT fldProductGroup FROM tblB2TTradeType WHERE pkTypeID=".$productGroupID;
		$result = mysqli_query($_SESSION['db'],$query);
		@list($productGroupName) = mysqli_fetch_row($result);
		//echo($productGroupName);
		
		$this->productGroup = $productGroupName;
	}
	
	public function setBuyerCo($buyerCoName) {
		$this->buyerCo = $buyerCoName;
	}
	
	public function setSellerCo($SellerCoName) {
		$this->sellerCo = $sellerCoName;
	}
	
	public function getProductGroup() {
		return $this->productGroup;
	}

	/**********************
	* init
	*
	* Do initial config of object to setup and store all settings
	***********************/
	public function init($buyerName,$sellerName) {
		
		$this->setContext($clientID);
		$query = "SELECT fldCompanyName,fldShowProduct,fldShowRegion,fldShowBuyerCurrency,fldShowSellerPrice,fldSellerPriceText,fldProductTypeText,fldShowDelivery FROM tblB2TConfirmConfig WHERE ";
		
		if(isset($this->region)) {
			$query.= "fldRegion=\"".$this->region."\" AND ";	
		}
		if(isset($this->productType)) {
			$query.= "fkProductTypeID=\"".$this->productType."\" AND ";	
		}
		if(isset($this->locationID)) {
			$query.= "fkLocationID=\"".$this->locationID."\"";
		}
		
		$query.= " LIMIT 1";
		//$query.= "fkClientID=".$clientID;
		//echo($query."<br>");
		$result = mysqli_query($_SESSION['db'],$query);
		$count = mysqli_num_rows($result);
		
		//see if company name matches client name
		list($companyName,$showProduct,$showRegion,$showBuyerCurrency,$showSellerPrice,$sellerPriceText,$productTypeText,$showDelivery) = mysqli_fetch_row($result);		
		//echo("client/company: $clientName - $companyName<br>");
		$bothParties = $buyerName." - ".$sellerName;
		//$compareResult = strpos($sellerName,$companyName); //only use seller
		$compareResult = strpos($bothParties,$companyName); //use both buyer or seller names
		//echo("compareResult: ".$compareResult."<br>");
		
		if(($compareResult >= 0 && $compareResult !== false) && $count > 0) {
			//echo("show: ".$showSellerPrice);
			$this->displayProduct = $showProduct;
			$this->displayBuyerCurrency = $showBuyerCurrency;
			$this->displaySellerPrice = $showSellerPrice;
			$this->sellerPriceText = $sellerPriceText;
			$this->productTypeText = $productTypeText;
			$this->displayDelivery = $showDelivery;
			$this->displayRegion = $showRegion;
			
			return true;
		}
		else if($this->locationID == 154) { //all palo confirms
			$this->sellerPriceText = "ICE DA Index at Palo Verde";
			return true;
		}
		else if($this->locationID == 155) { //COB confirms
			$this->sellerPriceText = "ICE COB DA Index Price";
			return true;
		}
		else if($this->locationID == 163) { //NOB confirms
			$this->sellerPriceText = "ICE DA Index at NOB North to South";
			return true;
		}
		else {
			$this->displayProduct = null;
			$this->displayBuyerCurrency = null;
			$this->displaySellerPrice = null;
			$this->sellerPriceText = null;
			$this->productTypeText = null;
			$this->displayDelivery = 1;
			$this->displayRegion = null;
			
			return false;
		}
	}

	
	/***********************
	* showBuyerCurrency
	************************/
	public function showBuyerCurrency() {
		if($this->productGroup == "US Electricity")
			return false;
		else if($this->displayBuyerCurrency == 1)
			return true;
		else
			return false;
	}
	
	/***********************
	* showSellerPrice
	************************/
	public function showSellerPrice() {
		
		if($this->displaySellerPrice == 1)
			return true;
		else
			return false;

	}
	
	/***********************
	* getSellerPriceText
	************************/
	public function getSellerPriceText() {
				
		if($this->peakText == "RTC")
			return $this->sellerPriceText." On-Peak and Off-Peak";
		else if($this->peakText == "Peak")
			return $this->sellerPriceText." On ".$this->peakText;
		else
			return $this->sellerPriceText." ".$this->peakText;
	}
	
	/***********************
	* getIndexPriceText
	************************/
	public function getIndexPriceText() {
		
		if($this->locationID == 154) //palo verdo modified language - set up above
			return $this->getSellerPriceText();
		
		return $this->sellerPriceText;
	}
	
	/***********************
	* getProductTypeText
	************************/
	public function getProductTypeText($productTypeID) {
		$queryProduct = "SELECT fldProductTypeName FROM tblB2TProductType WHERE pkProductTypeID=".$productTypeID;
		$resultProduct = mysqli_query($_SESSION['db'],$queryProduct);
		@list($productType) = mysqli_fetch_row($resultProduct);
			
		if( strpos(strtoupper($productType),"DAY AHEAD") === false) {
			if( strpos($this->buyerCo,"Calpine") !== false || strpos($this->sellerCo,"Calpine") !== false)
				return $productType;
			else if($this->productGroup == "US Electricity")
				return $productType." RT";
		}

		return $productType;
	}
	
	/********************
	* showProductType
	*********************/	
	public function showProductType() {		
		return $this->productTypeText;
	}
	
	/***********************
	* showDelivery
	************************/
	public function showDelivery() {
		return $this->displayDelivery;
	}
	
	/***********************
	* showProduct
	************************/
	public function showProduct() {
		
		/*if($this->displayProduct == null)
			$this->displayProduct = 1;*/
			
		return $this->displayProduct;
	}
	
	/***********************
	* showRegion
	************************/
	public function showRegion() {
		return $this->displayRegion;
	}
	
	/**********************
	* showCurrency
	**********************/
	public function showCurrency() {
		
		/*if($this->productGroup == "US Electricity") { //power trades
			$query = "SELECT fldGeoRegion FROM tblB2TLocation WHERE pkLocationID=".$this->locationID;
			$result = mysqli_query($_SESSION['db'],$query);
			@list($region) = mysqli_fetch_row($result);
			
			if(strtoupper($region) == "WEST" || strtoupper($region) == "ERCOT") {
				return false;
			}
		}*/
		
		if($this->productGroup == "US Electricity")
			return false;
		
		return true;
	}
	
	function locationConfirmLanguage($baseText,$productType) {
		
		if(!$this->isWest()) {
			if(($this->locationID == 13 || $this->locationID == 165) && ($productType == "Physical" || $productType == "Financial")) //PJM West Hub RT LMP, Indiana Hub made on 1/30/12
				return $baseText." RT LMP";
			else if( strpos($baseText,"PJM") !== false ) //PJM trades should not include extra msg
				return $baseText;
			else if( strpos($this->buyerCo,"Calpine") !== false || strpos($this->sellerCo,"Calpine") !== false)
				return $baseText;
			else if($this->productGroup == "Natural Gas") //nat gas trades return as is
				return $baseText;
			else if($productType == "Physical" || $productType == "Financial") {
				$baseText = $baseText." RT";
				
				if( (strpos($this->buyerCo,"AEP Energy") !== false || strpos($this->sellerCo,"AEP Energy") !== false ) && strpos($baseText,"ERCOT") !== false )
					$baseText = $baseText." (SPP)";
				
				return $baseText;
			}
			else if( strpos(strtoupper($productType),"DAY AHEAD") === false )
				return $baseText." Day Ahead";
			else
				return $baseText;
		}
		else
			return $baseText;
		
	}
	
	public function isWest() {
		$query = "SELECT fldGeoRegion FROM tblB2TLocation WHERE pkLocationID=".$this->locationID;
		$result = mysqli_query($_SESSION['db'],$query);
		@list($region) = mysqli_fetch_row($result);
		
		if(strtoupper($region) == "WEST") {
			return true;
		}
		
		return false;
	}
	
	public function isEast() {
		$query = "SELECT fldGeoRegion FROM tblB2TLocation WHERE pkLocationID=".$this->locationID;
		$result = mysqli_query($_SESSION['db'],$query);
		@list($region) = mysqli_fetch_row($result);
		
		if(strtoupper($region) == "EAST") {
			return true;
		}
		
		return false;
	}
	
	public function getAddlExerciseTypeText($baseText,$exerciseType) {
	
		//echo("b: ".$baseTe
		if($exerciseType == 1 && strpos($baseText,"ERCOT") !== false) {
			//ERCOT, use Day Ahead Exercise by 9 AM CPT
			return ", Day Ahead Exercise by 9 AM CPT";
		}
		else if($exerciseType == 1 && $this->isEast()) {
			//Day Ahead Exercise by 10 AM EPT
			return ", Day Ahead Exercise by 10 AM EPT";
		}
		
		return;
	}
	
	
	/**** private members ****/

	private function setContext($clientID) {
		$this->clientID = $clientID;
	}
	
	
}


?>