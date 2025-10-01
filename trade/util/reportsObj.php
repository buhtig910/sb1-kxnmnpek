<?php

class Report {

	private $brokerID;
	private $period;
	private $periodOutput;
	private $commTotal = 0;
	private $outputArr = array();
	private $startDate;
	private $endDate;
	
	public function getReportSummary() {	
			/*
			pkTransactionID,fldTransactionNum,fldConfirmDate,tt.fldProductGroup,sum(fldBuyBrokerComm),sum(fldSellBrokerComm),IFNULL(fldBrokerComm,0),
			(fldBuyCommTotal+fldSellCommTotal+IFNULL(fldBrokerComm,0)) as total,
			IFNULL(brokerCount,0),(fldBuyCommTotal+fldSellCommTotal+IFNULL(fldBrokerComm,0))/( IF(fkBuyingBroker=fkSellingBroker,1,2)+ IFNULL(brokerCount,0)) as comm,
			IF(sum(fkBuyingBroker)=sum(fkSellingBroker),1,0) as same
			*/
		
		//echo("<p>ID: ".$this->brokerID."</p>");
		
		//added sum() for addlBuyBrokerComm, addlSellBrokerComm, isAddlSeller, isAddlBuyer in line 3 on 4.2.12
		$root = "SELECT 
			pkTransactionID,fldTransactionNum,fldConfirmDate,tt.fldProductGroup,sum(fldBuyBrokerComm),sum(fldSellBrokerComm),
			IFNULL(sum(fldAddlBuyBrokerComm),0) as addlBuyBrokerTotal,IFNULL(sum(fldAddlSellBrokerComm),0) as addlSellBrokerTotal,
			fldBuyCommTotal, fldSellCommTotal, (fldBuyCommTotal+fldSellCommTotal) as total,
			IFNULL(sum(addlBuyBrokerCount),0),IFNULL(sum(addlSellBrokerCount),0),
			IFNULL(sum(isAddlBuyer),0),IFNULL(sum(isAddlSeller),0),
			(fldBuyCommTotal+fldSellCommTotal+IFNULL(fldAddlBuyBrokerComm,0))/( IF(fkBuyingBroker=fkSellingBroker,1,2) + IFNULL(addlBuyBrokerCount,0)) as netComm,
			IF(sum(fkBuyingBroker)=sum(fkSellingBroker),1,0) as same";
		
		//added 2.23.12 for additional broker calc
		if(isAdmin()) {			
			if($this->brokerID != "all") {
				//$root.= ", IF(fkBuyingBroker=".$this->brokerID.",1,0) AS isBuyer,IF((sum(fkSellingBroker)/(count(*)-1))=".$this->brokerID.",1,0) as isSeller";
				$root.= ", IF(fkBuyingBroker=".$this->brokerID.",1,0) AS isBuyer, IF(sum(fkSellingBroker)=".$this->brokerID.",1,0) as isSeller, IF((sum(fkSellingBroker)/".$this->brokerID.")=3,1,0) as isClearedSeller"; //changed on 3.15.12
			}
		}
		else {
			//$root.= ", IF(fkBuyingBroker=".$this->brokerID.",1,0) AS isBuyer,IF((sum(fkSellingBroker)/(count(*)-1))=".$this->brokerID.",1,0) as isSeller";
			$root.= ", IF(fkBuyingBroker=".$this->brokerID.",1,0) AS isBuyer, IF(sum(fkSellingBroker)=".$this->brokerID.",1,0) as isSeller, IF((sum(fkSellingBroker)/".$this->brokerID.")=3,1,0) as isClearedSeller"; //changed on 3.15.12
		}
		
		if(isAdmin()) {
			if($this->brokerID == "all")
				$tempBrokerRef = -1;
			else
				$tempBrokerRef = $this->brokerID;
		}
		else
			$tempBrokerRef = $this->brokerID;
		
		
		$root.= " FROM 
			tblB2TTransaction t
		LEFT JOIN 
			tblB2TTradeType tt ON t.fkProductGroup=tt.pkTypeID";
			
		//GET ADDL BUY BROKER INFO
		$root.= " LEFT JOIN
			(SELECT fkTransactionID,b.fkBrokerID as addlBuyBrokerID,fldBrokerComm as fldAddlBuyBrokerComm,count(*) as addlBuyBrokerCount,IF(b.fkBrokerID=".$tempBrokerRef.",count(*),0) as isAddlBuyer FROM tblB2TTransaction ti, tblB2TTransaction_Brokers b 
			WHERE b.fkTransactionID=ti.pkTransactionID AND fldBuyer=1 AND fkSellingBroker=0 AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].") "; 
		
		/*if(!isAdmin())
			$root.= " AND b.fkBrokerID=".$this->brokerID;*/
		
		$root.= " GROUP BY b.fkTransactionID) bt ON t.pkTransactionID=bt.fkTransactionID";
		
		//GET ADDL SELL BROKER INFO
		$root.= " LEFT JOIN
			(SELECT fkTransactionID,bb.fkBrokerID as addlSellBrokerID,fldBrokerComm as fldAddlSellBrokerComm,count(*) as addlSellBrokerCount,IF(bb.fkBrokerID=".$tempBrokerRef.",count(*),0) as isAddlSeller FROM tblB2TTransaction tti, tblB2TTransaction_Brokers bb 
			WHERE bb.fkTransactionID=tti.pkTransactionID AND fldBuyer=0 AND fkBuyingBroker=0 AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].") "; 
		
		/*if(!isAdmin())
			$root.= " AND bb.fkBrokerID=".$this->brokerID;*/
		
		$root.= " GROUP BY bb.fkTransactionID) bt2 ON t.pkTransactionID=bt2.fkTransactionID";		
		
		if(isAdmin()) {
			if($this->brokerID == "all")
				$query = $root." WHERE (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
			else {
				//$query = "SELECT sum(fldBuyBrokerComm),sum(fldSellBrokerComm) FROM tblB2TTransaction WHERE (fkBuyingBroker=".$this->brokerID." OR fkSellingBroker=".$this->brokerID.") AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
				$query = $root." WHERE (fkBuyingBroker=".$this->brokerID." OR fkSellingBroker=".$this->brokerID." OR addlBuyBrokerID=".$this->brokerID." OR addlSellBrokerID=".$this->brokerID.") AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
			}
		}
		else 
			$query = $root." WHERE (fkBuyingBroker=".$this->brokerID." OR fkSellingBroker=".$this->brokerID." OR addlBuyBrokerID=".$this->brokerID." OR addlSellBrokerID=".$this->brokerID.") AND (fldStatus=".$_SESSION["CONST_LIVE"]." OR fldStatus=".$_SESSION["CONST_SETTLED"].")";
		
		if($this->period == "YTD") {
			$this->periodOutput = "YTD";
			$query.= " AND fldConfirmDate LIKE '".date('Y')."-%'";
		}
		else if($this->period == "YTM") {
			$this->periodOutput = "YTM";
			$query.= " AND fldConfirmDate < '".date('Y-m')."-01' AND fldConfirmDate >= '".date('Y')."-01-01'";
		}
		else if($this->period == "current") {
			$this->periodOutput = "Current Month";
			$query.= " AND fldConfirmDate LIKE '".date('Y-m')."%'";
		}
		else if($this->period == "last") {
			$this->periodOutput = "Last Month";
			if(date("m") == "01")
				$lastmonth = (date("Y")-1)."-".date("m",mktime(0,0,0,date("m")-1));
			else
				$lastmonth = date("Y")."-".date("m",mktime(0,0,0,date("m")-1));
			$query.= " AND fldConfirmDate LIKE '".$lastmonth."%'";
		}
		else if($this->period == "custom") {
			$this->periodOutput = "Custom (".$this->startDate." - ".$this->endDate.")";
			$query.= " AND (fldConfirmDate >= '".reverseDate($this->startDate)."' AND fldConfirmDate <= '".reverseDate($this->endDate)."')";
		}
		
		$query.= " AND (fldBuyBrokerComm+fldSellBrokerComm>=0) GROUP BY fldTransactionNum"; //pkTransactionID


		//echo("<p>ID: ".$this->brokerID."</p>");
		//echo($query."<br /><br />");
		$result = mysqli_query($_SESSION['db'],$query);
		@$count = mysqli_num_rows($result);
		
		if($count > 0){
			$subtotal = 0;
			$output = array();
			while(list($transID,$transNum,$tradeDate,$prodType,$buyBrokerComm,$sellBrokerComm,$addlBuyBrokerTotal,$addlSellBrokerTotal,$buyCommTotal,$sellCommTotal,$fullComm,$addlBuyBrokerCount,$addlSellBrokerCount,$isAddlBuyer,$isAddlSeller,$netBrokerComm,$same,$isBuyer,$isSeller,$isClearedSeller) = mysqli_fetch_row($result)) {
											
				$netBrokerComm = 0;
				
				if($this->brokerID == "all") {
					//echo("All<br>"); //updated on 2.8.12 and 'else' logic added for next 'if' block to exclude it
					$netBrokerComm = $buyCommTotal + $sellCommTotal;
					$temp = array($transID,$transNum,$tradeDate,$prodType,$buyBrokerComm,$sellBrokerComm,$additionalBrokerComm,$fullComm,$otherBrokers,$netBrokerComm);
				}				
				else {
					
					if($same == 1) {
						$base = 1;
						$netBrokerComm = $fullComm;
					}
					else if($isBuyer == 1)
						$netBrokerComm = $fullComm / 2;
					else if($isSeller == 1 || ($isSeller == 0 && $isClearedSeller == 1))
						$netBrokerComm = $fullComm / 2;
					else {
						$base = 2 + $addlBuyBrokerCount + $addlSellBrokerCount - $same - 1;
						//echo("Base: ".$base);
						$netBrokerComm = $fullComm / $base;
					}
					$temp = array($transID,$transNum,$tradeDate,$prodType,$buyBrokerComm,$sellBrokerComm,$additionalBrokerComm,$fullComm,$otherBrokers,$netBrokerComm);
					
					
					
					
					
					/*****************
					*  this section removed on 12.18.23 to simplify split logic to 50/50 on all trade types - previously a bug, this should have always been the case
					*
					*
					
					if($same == 1) {
						$base = 1;
						$netBrokerComm = $fullComm;
					}
					else if($isBuyer == 1)
						if(strpos($prodType,"Electricity") > 0) //added 7.11.23 to correct for even split on power trades
							$netBrokerComm = $fullComm / 2;
						else
							$netBrokerComm = $buyCommTotal; //added 7.5.23 to fix cleared trade issue
					else if($isSeller == 1 || ($isSeller == 0 && $isClearedSeller == 1))
						if(strpos($prodType,"Electricity") > 0) //added 7.11.23 to correct for even split on power trades
							$netBrokerComm = $fullComm / 2;
						else
							$netBrokerComm = $sellCommTotal; //added 7.5.23 to fix cleared trade issue
					else {
						$base = 2 + $addlBuyBrokerCount + $addlSellBrokerCount - $same - 1;
						//echo("Base: ".$base);
						$netBrokerComm = $fullComm / $base;
					}
					$temp = array($transID,$transNum,$tradeDate,$prodType,$buyBrokerComm,$sellBrokerComm,$additionalBrokerComm,$fullComm,$otherBrokers,$netBrokerComm);
					
					*************/
					
				}
								
				
				/*
				logic removed on 9.30.21 to evenly split across all brokers - replaced with ELSE block above
				
				
				else if( strpos($prodType,"Electricity") > 0 ) {
					//echo("Electric<br>");
					//echo("same: ".$same);
					//echo($addlBuyer);
					
					// For all power trades, the total commission is split 50/50 by buy/sell sides,
					// then split evenly among brokers on respective sides (2.25.12)
					$startComm = $fullComm / 2;
					
					if($buyBrokerComm > 0 || $isBuyer == 1) {
						if($buyCommTotal == 0 && ($addlBuyBrokerCount > 0 && $addlSellBrokerCount > 0)) { //added on 3.3.12 for 0 brokerage case
							//echo("ok");
							$buyBrokerComm = 0;
						}
						else if($sellCommTotal == 0 && $same == 1 && ($addlBuyBrokerCount == 0 && $addlSellBrokerCount == 0)){
							//echo("<p>total: ".$fullComm."</p>");
							$buyBrokerComm = $fullComm;
						}
						else if($same == 1 && ($addlBuyBrokerCount == 0 && $addlSellBrokerCount == 0)) { //added 5.26.21 for trade where buy broker = sell broker
							//echo("<p>total: ".$fullComm."</p>");
							$buyBrokerComm = $fullComm;
						}
						else
							$buyBrokerComm = $startComm;
					}
					else
						$buyBrokerComm = $startComm; //added on 3.13.12
					//echo("<p>buy ".$buyBrokerComm."</p>");
					
					if($sellBrokerComm > 0 || $isSeller == 1) {
						if($sellCommTotal == 0 && ($addlBuyBrokerCount > 0 && $addlSellBrokerCount > 0)) { //added on 3.3.12 for 0 brokerage case
							$sellBrokerComm = 0;
							$buyBrokerComm = $fullComm;
						}
						else {
							if($buyCommTotal == 0 && $same == 1) { //buyer & seller same, with 0 buy side commission
								//$sellBrokerComm = $fullComm;
								$sellBrokerComm = $startComm; //modified on 3.14.12
							}
							else if($same == 1 && $buyBrokerComm == $fullComm) { } //do nothing if buy broker comm already = full comm
							else {
								//echo("ok now");
								$sellBrokerComm = $startComm; //+= instead?
							}
						}
					}
					//echo("<p>sell: ".$sellBrokerComm."</p>");
										
					//buy side additional brokers
					if($addlBuyBrokerCount > 0) {
						//echo("ok");
						//if($sellBrokerComm == 0 || $isSeller == 1) //added on 3.3.12 for 0 brokerage case
							$netBrokerComm += ($buyBrokerComm / ($addlBuyBrokerCount + 1));
					}
					else {
						//if($sellCommTotal == 0 && $isBuyer == 1) //added on 3.3.12 for 0 brokerage case
						//echo("<p>is buyer: ".$isBuyer."</p>");
						if($isBuyer == 1) { //added 3.14.12
							//echo("<p>yeah</p>");
							$netBrokerComm += $buyBrokerComm;
						}
					}
					//echo($netBrokerComm."<br>");
					
					//sell side additional brokers
					if($addlSellBrokerCount > 0 && $addlSellBrokerTotal > 0) {   //added addlSellBrokerTotal>0  on 6.24.21
						//echo("yes ".$addlSellBrokerCount." | ");
						$netBrokerComm += ($sellBrokerComm / ($addlSellBrokerCount + 1));
					}
					else if($sellCommTotal == 0 && $isSeller == 1) {
						if($same == 0) //added 06.02.12 for 0 brokerage case
							$netBrokerComm += $sellBrokerComm;
						else if($addlBuyBrokerCount > 0)
							$netBrokerComm += $sellBrokerComm;
					}
					else if($buyCommTotal == 0 && $same == 1) { //added 3.14.12
						//echo("asdf");
						$netBrokerComm += $sellBrokerComm; //changed to += on 3.14.12
					}
					else if($same == 1 && $buyBrokerComm == $fullComm) { //net broker comm = full comm - added 5.26.21
						$netBrokerComm += $buyBrokerComm;
					}
					else {
						//if($buyCommTotal == 0 && $isSeller == 1) //added on 3.3.12 for 0 brokerage case
							//$netBrokerComm += $sellBrokerComm;  //removed 6.24.21
							$netBrokerComm = $fullComm;  //added 6.24.21
					}
					//echo("<p>net: ".$netBrokerComm."</p>");
											
					//power trade, use netBrokerComm amount from query which is even split across all brokers on trade
					$temp = array($transID,$transNum,$tradeDate,$prodType,$buyBrokerComm,$sellBrokerComm,$additionalBrokerComm,$fullComm,$otherBrokers,$netBrokerComm);
				}
				else {
					//gas trades, don't use an even split across all brokers
					$netBrokerComm = 0;
					
					//echo("<p>gas...</p>");
					
					if($isBuyer == 1)
						$netBrokerComm = $buyBrokerComm;
					//echo($netBrokerComm."<br>");
					if($isSeller == 1 || $isClearedSeller == 1 || $same == 1) //accounts for query flaw for getting selling broker
						$netBrokerComm += $sellBrokerComm;	
					//echo($netBrokerComm."<br>");
					
					if($addlBuyBrokerCount > 0 && $isAddlBuyer)
						$netBrokerComm += $addlBuyBrokerTotal;
					//echo($netBrokerComm."<br>");
					
					if($addlSellBrokerCount > 0 && $isAddlSeller)
						$netBrokerComm += $addlSellBrokerTotal;
					//echo($netBrokerComm."<br><br>");
					
					$temp = array($transID,$transNum,$tradeDate,$prodType,$buyBrokerComm,$sellBrokerComm,$additionalBrokerComm,$fullComm,$otherBrokers,$netBrokerComm);
				}*/
				array_push($output,$temp);
				$subtotal+= $netBrokerComm;
			}
			
			$this->outputArr = $output;
			$this->commTotal = $subtotal;
		}
		else {
			$this->commTotal = 0;
		}
		
	}
	
	public function getReportSummaryExtended() {
		echo("<p class='errorMessage'>Not yet implemented, use Raw Data Extrat for now</p>");
	}

	public function getOutputPeriod() {
		return $this->periodOutput;
	}
	
	public function setPeriod($val) {
		$this->period = $val;
	}
	
	public function setBroker($broker) {
		$this->brokerID = $broker;
	}
	
	public function getBroker() {
		return $this->brokerID;	
	}
	
	public function getBrokerTotal() {
		return $this->commTotal;	
	}
	
	public function getOutputData() {
		return $this->outputArr;
	}
	
	public function setDates($start,$end) {
		$this->startDate = $start;
		$this->endDate = $end;	
	}

}

?>