<?php

/***************************
* company object
***************************/


class Company {

	/** private members **/
	private $traderID;
	private $traderName;	
	private $invoiceName;
	private $email;
	private $companyName;
	private $street1;
	private $street2;
	private $city;
	private $state;
	private $zip;
	private $country;
	private $phone;
	private $fax;
	private $confirmEmail;
	private $confirmName;
	private $confirmStreet;
	private $confirmStreet2;
	private $confirmCity;
	private $confirmState;
	private $confirmZip;
	private $confirmCountry;
	private $confirmPhone;
	private $confirmFax;
	private $active;
	private $shortName;
	private $compantID;
	private $parentCompanyName;
	

	/*****************
	* init
	*****************/
	public function init($traderID) {
		
		$this->traderID = $traderID;
		$query = "SELECT * FROM tblB2TClient c RIGHT JOIN tblB2TCompany co on c.fkCompanyID=co.pkCompanyID WHERE pkClientID=$traderID LIMIT 1";
		//echo($query);
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		if($count > 0) {
			$parts = mysqli_fetch_row($result);
			$this->traderID = $parts[0];
			$this->traderName = $parts[1];
			$this->invoiceName = $parts[2];
			$this->email = $parts[3];
			$this->companyName = $parts[4];
			$this->street1 = $parts[5];
			$this->street2 = $parts[6];
			$this->city = $parts[7];
			$this->state = $parts[8];
			$this->zip = $parts[9];
			$this->country = $parts[10];
			$this->phone = $parts[11];
			$this->fax = $parts[12];
			$this->confirmEmail = $parts[13];
			$this->confirmName = $parts[14];
			$this->confirmStreet = $parts[15];
			$this->confirmStreet2 = $parts[16];
			$this->confirmCity = $parts[17];
			$this->confirmState = $parts[18];
			$this->confirmZip = $parts[19];
			$this->confirmCountry = $parts[20];
			$this->confirmPhone = $parts[21];
			$this->confirmFax = $parts[22];
			$this->active = $parts[23];
			$this->shortName = $parts[24];
			$this->companyID = $parts[25];
			//$this->companyID = $parts[26];
			$this->parentCompanyName = $parts[27];
		}
		else
			return -1;
	}
	
	
	/***** getters *****/
	public function getCompanyID() {
		return $this->companyID;
	}
	
	public function getCompanyName() {
		return $this->parentCompanyName;
	}
	
	public function getTraderName() {
		return $this->traderName;
	}
	
	public function getConfirmPhone() {
		return $this->confirmPhone;
	}
	
	public function getConfirmFax() {
		return $this->confirmFax;
	}
		
	
}